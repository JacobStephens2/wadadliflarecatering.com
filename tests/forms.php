<?php
// Starts and stops its own disposable MySQL/MariaDB server. Never uses production credentials.
function expect($condition, $message) {
    if (!$condition) { throw new RuntimeException($message); }
}
function command(array $command): string {
    $process = proc_open($command, [1 => ['pipe', 'w'], 2 => ['pipe', 'w']], $pipes);
    $output = stream_get_contents($pipes[1]);
    $errors = stream_get_contents($pipes[2]);
    fclose($pipes[1]); fclose($pipes[2]);
    expect(proc_close($process) === 0, implode(' ', $command) . ': ' . $errors);
    return $output;
}
$directory = sys_get_temp_dir() . '/wadadli-forms-' . bin2hex(random_bytes(6));
mkdir($directory, 0700);
$rateDir = $directory . '/rate_limits';
mkdir($rateDir, 0700);
$server = null;
try {
    $isMariaDb = str_contains(command(['/usr/sbin/mysqld', '--version']), 'MariaDB');
    if ($isMariaDb) {
        mkdir($directory . '/data', 0700);
        command(['mysql_install_db', '--no-defaults', '--datadir=' . $directory . '/data', '--auth-root-authentication-method=normal']);
    } else {
        command(['/usr/sbin/mysqld', '--no-defaults', '--initialize-insecure', '--datadir=' . $directory . '/data', '--log-error=' . $directory . '/init.log']);
    }
    $socket = $directory . '/mysql.sock';
    $serverCommand = [
        '/usr/sbin/mysqld', '--no-defaults', '--datadir=' . $directory . '/data',
        '--socket=' . $socket, '--skip-networking',
        '--pid-file=' . $directory . '/mysql.pid', '--log-error=' . $directory . '/mysql.log',
    ];
    if (!$isMariaDb) {
        $serverCommand[] = '--mysqlx=0';
    }
    $server = proc_open($serverCommand, [1 => ['file', $directory . '/stdout', 'w'], 2 => ['file', $directory . '/stderr', 'w']], $pipes);
    $pdo = null;
    for ($attempt = 0; $attempt < 100; $attempt++) {
        try { $pdo = new PDO("mysql:unix_socket=$socket", 'root', '', [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]); break; }
        catch (PDOException $error) { usleep(100000); }
    }
    expect($pdo !== null, 'Disposable MySQL did not start');
    $schema = command(['git', 'show', 'master:private/schema.sql']);
    $pdo->exec(str_replace('wadadli_flare', 'wadadli_forms_test', $schema));
    $pdo->exec("INSERT INTO contact_submissions (name, email, message) VALUES ('Existing', 'existing@example.com', 'Existing lead')");
    if ($pdo->query("SHOW COLUMNS FROM contact_submissions LIKE 'flag_reason'")->fetchColumn() === false && file_exists(__DIR__ . '/../private/migrations/007-form-correctness.sql')) {
        $pdo->exec(file_get_contents(__DIR__ . '/../private/migrations/007-form-correctness.sql'));
    }
    $mailFile = $directory . '/mail.jsonl';
    $wiringPath = __DIR__ . '/../public/includes/lead_intake.php';
    $runnerCode = <<<'PHP'
define('ENV_LOADED', true);
[, $wiring, $page, $tempRateDir, $mailLog, $turnstileMode] = $argv;
require $wiring;
\lead_intake(new \WadadliFlare\LeadIntake(
    static fn(): PDO => \open_database(),
    static fn(string $token, string $clientIp): bool => $turnstileMode === 'pass',
    static function (array $message) use ($mailLog): bool {
        file_put_contents($mailLog, json_encode([
            'subject' => $message['subject'],
            'body' => $message['body'],
        ]) . "\n", FILE_APPEND);
        return true;
    },
    CONTACT_EMAIL,
    $tempRateDir
));
$_POST = json_decode(stream_get_contents(STDIN), true);
$_SERVER['REQUEST_METHOD'] = 'POST';
$_SERVER['REMOTE_ADDR'] = getenv('TEST_IP') ?: 'unknown';
require $page;
PHP;
    $ipCounter = 0;
    $startPost = function ($form, $data, $turnstile = 'pass', $ip = null, $dbHostOverride = null) use ($socket, $rateDir, $mailFile, $wiringPath, $runnerCode, &$ipCounter) {
        $ip = $ip ?? '198.18.' . intdiv(++$ipCounter, 250) . '.' . ($ipCounter % 250);
        $pagePath = __DIR__ . '/../public/' . ($form === 'contact' ? 'contact' : 'quote-request') . '.php';
        $environment = array_merge(getenv(), [
            'DB_HOST' => $dbHostOverride ?? "localhost;unix_socket=$socket", 'DB_NAME' => 'wadadli_forms_test', 'DB_USER' => 'root', 'DB_PASS' => '',
            'TEST_IP' => $ip,
        ]);
        $process = proc_open(
            [PHP_BINARY, '-r', $runnerCode, $wiringPath, $pagePath, $rateDir, $mailFile, $turnstile],
            [0 => ['pipe', 'r'], 1 => ['pipe', 'w'], 2 => ['pipe', 'w']],
            $pipes,
            null,
            $environment
        );
        fwrite($pipes[0], json_encode($data)); fclose($pipes[0]);
        return [$process, $pipes, $ip];
    };
    $finishPost = function ($task) {
        [$process, $pipes, $ip] = $task;
        $html = stream_get_contents($pipes[1]); $log = stream_get_contents($pipes[2]);
        fclose($pipes[1]); fclose($pipes[2]);
        expect(proc_close($process) === 0, 'Form POST crashed: ' . $log);
        return ['html' => $html, 'log' => $log, 'ip' => $ip];
    };
    $post = fn($form, $data, $turnstile = 'pass', $ip = null, $dbHostOverride = null) => $finishPost($startPost($form, $data, $turnstile, $ip, $dbHostOverride));
    $emails = fn() => file_exists($mailFile) ? array_map(fn($line) => json_decode($line, true), file($mailFile)) : [];
    $contact = ['name' => 'Lynch', 'email' => 'lynch@example.com', 'phone' => '', 'message' => 'Do you offer a discount for 200 guests?', 'cf-turnstile-response' => 'valid'];
    $quote = array_merge($contact, ['event_type' => 'Wedding', 'event_date' => '', 'guest_count' => '200', 'location' => 'Elverson']);
    $tests = [];
    $tests['legitimate'] = function () use ($pdo, $post, $contact, $emails) {
        $result = $post('contact', $contact);
        expect(str_contains($result['html'], 'Thank you for your message!'), 'Normal contact success');
        expect($pdo->query("SELECT COUNT(*) FROM contact_submissions WHERE email='lynch@example.com'")->fetchColumn() == 1, 'Lynch lead must be saved');
        expect(count($emails()) === 1, 'Lynch lead must be emailed');
        expect(!str_starts_with($emails()[0]['subject'], '[Possible spam]'), 'Catering discount enquiry must not be flagged');
    };
    $tests['migration'] = function () use ($pdo) {
        expect($pdo->query("SELECT message FROM contact_submissions WHERE email='existing@example.com'")->fetchColumn() === 'Existing lead', 'Migration preserves existing lead');
        foreach (['contact_submissions', 'quote_requests'] as $table) {
            expect($pdo->query("SHOW COLUMNS FROM $table LIKE 'flag_reason'")->fetchColumn() === 'flag_reason', 'Migration adds nullable flag reason');
        }
    };
    $tests['flagged'] = function () use ($pdo, $post, $contact, $quote, $emails) {
        foreach (['contact' => $contact, 'quote' => $quote] as $form => $input) {
            $input['email'] = "url-$form@example.com";
            $input['message'] = 'Venue details: https://example.com';
            $result = $post($form, $input);
            expect(str_contains($result['html'], 'alert-success'), 'Suspicious lead sees normal success');
            $table = $form === 'quote' ? 'quote_requests' : 'contact_submissions';
            $reason = $pdo->query("SELECT flag_reason FROM $table WHERE email='url-$form@example.com'")->fetchColumn();
            expect(is_string($reason) && $reason !== '', 'Suspicious lead is saved with reason');
            $messages = $emails();
            expect(str_starts_with(end($messages)['subject'], '[Possible spam]'), 'Suspicious lead is emailed with prefix');
        }
        foreach (['name', 'phone', 'location'] as $field) {
            $input = $quote;
            $input['email'] = "$field-url@example.com";
            $input[$field] = '<a href="https://example.com">x</a>';
            $post('quote', $input);
            expect($pdo->query("SELECT flag_reason FROM quote_requests WHERE email='$field-url@example.com'")->fetchColumn() !== null, "$field content is checked");
            $messages = $emails();
            expect(!str_contains(end($messages)['body'], '<a href="https://example.com">'), "$field markup is escaped");
        }
    };
    $tests['rejections'] = function () use ($pdo, $post, $contact, $quote, $emails) {
        foreach (['contact' => $contact, 'quote' => $quote] as $form => $input) {
            foreach (['honeypot', 'turnstile', 'missing-token', 'validation', 'rate limit'] as $reason) {
                $data = $input;
                $data['email'] = str_replace(' ', '-', "$form-$reason@example.com");
                $data['message'] = 'PRIVATE MESSAGE NEVER LOG';
                $ip = null;
                if ($reason === 'honeypot') { $data['website_url'] = 'bot'; }
                if ($reason === 'missing-token') { unset($data['cf-turnstile-response']); }
                if ($reason === 'validation') { $data['email'] = 'invalid'; }
                if ($reason === 'rate limit') {
                    $ip = $form === 'contact' ? '198.19.1.1' : '198.19.1.2';
                    for ($i = 0; $i < 3; $i++) { $post($form, $data, 'fail', $ip); }
                }
                $before = count($emails());
                $result = $post($form, $data, $reason === 'turnstile' ? 'fail' : 'pass', $ip);
                $expectedReason = $reason === 'missing-token' ? 'turnstile' : $reason;
                expect(substr_count($result['log'], 'Form rejection') === 1, 'Exactly one rejection log line');
                foreach ([$form, $expectedReason, $result['ip']] as $text) {
                    expect(str_contains($result['log'], $text), 'Rejection log contains form, reason and IP');
                }
                expect(!str_contains($result['log'], 'PRIVATE MESSAGE NEVER LOG'), 'No submitted message is logged');
                expect(count($emails()) === $before, 'Rejected POST sends no email');
                expect(!str_contains($result['html'], 'alert-success') || $reason === 'honeypot', 'Only honeypot rejection has fake success');
                foreach (['contact_submissions', 'quote_requests'] as $table) {
                    expect($pdo->query("SELECT COUNT(*) FROM $table WHERE message='PRIVATE MESSAGE NEVER LOG'")->fetchColumn() == 0, 'Rejected POST saves no row');
                }
            }
        }
    };
    $tests['validation'] = function () use ($post, $quote) {
        $invalid = [
            ['event_type', 'Invented'], ['name', str_repeat('n', 256)],
            ['phone', str_repeat('p', 51)], ['location', str_repeat('l', 256)],
            ['message', str_repeat('é', 32768)], ['name', ['array']],
            ['event_date', '2026-02-30'], ['guest_count', '2147483648'],
        ];
        foreach ($invalid as [$field, $value]) {
            $input = $quote; $input[$field] = $value;
            $result = $post('quote', $input);
            expect(str_contains($result['html'], 'alert-error'), "$field invalid input is rejected");
            expect(str_contains($result['log'], 'validation'), "$field validation is logged");
        }
    };
    $tests['duplicates'] = function () use ($pdo, $post, $startPost, $finishPost, $contact, $quote, $emails) {
        foreach (['contact' => $contact, 'quote' => $quote] as $form => $input) {
            $input['email'] = "duplicate-$form@example.com";
            $before = count($emails());
            $first = $post($form, $input);
            $second = $post($form, $input);
            expect(str_contains($first['html'], 'alert-success') && str_contains($second['html'], 'alert-success'), 'Repeated POSTs both see success');
            $table = $form === 'quote' ? 'quote_requests' : 'contact_submissions';
            expect($pdo->query("SELECT COUNT(*) FROM $table WHERE email='duplicate-$form@example.com'")->fetchColumn() == 1, 'Repeated POST saves only one row');
            expect(count($emails()) === $before + 1, 'Repeated POST sends only one email');
            expect(str_contains($second['log'], 'duplicate'), 'Duplicate is logged');
            $changed = $input; $changed['message'] .= ' A new enquiry';
            $post($form, $changed);
            expect(count($emails()) === $before + 2, 'Different content is accepted');
            $pdo->exec("UPDATE $table SET submitted_at = NOW() - INTERVAL 11 MINUTE WHERE email='duplicate-$form@example.com'");
            $post($form, $input);
            expect(count($emails()) === $before + 3, 'Identical lead after ten minutes is accepted');
            $input['email'] = "concurrent-$form@example.com";
            $before = count($emails());
            $tasks = [$startPost($form, $input), $startPost($form, $input)];
            foreach ($tasks as $task) { $finishPost($task); }
            expect($pdo->query("SELECT COUNT(*) FROM $table WHERE email='concurrent-$form@example.com'")->fetchColumn() == 1, 'Concurrent duplicate saves one row');
            expect(count($emails()) === $before + 1, 'Concurrent duplicate sends one email');
        }
    };
    $tests['nul-date'] = function () use ($post, $quote) {
        $quote['event_date'] = '2026-10-14' . chr(0) . 'junk';
        $result = $post('quote', $quote);
        expect(str_contains($result['html'], 'alert-error'), 'Malformed date needs validation feedback');
        expect(substr_count($result['log'], 'Form rejection') === 1, 'Malformed date has one rejection log');
        foreach (['quote', 'validation', $result['ip']] as $text) {
            expect(str_contains($result['log'], $text), 'Malformed date needs form, validation reason and IP');
        }
    };
    $tests['replayed-token'] = function () use ($post, $contact, $quote, $pdo, $emails) {
        foreach (['contact' => $contact, 'quote' => $quote] as $form => $input) {
            $input['email'] = "replay-$form@example.com";
            $before = count($emails());
            $first = $post($form, $input, 'pass');
            $second = $post($form, $input, 'fail');
            expect(str_contains($first['html'], 'alert-success'), 'First POST succeeds');
            expect(str_contains($second['html'], 'alert-error'), 'Consumed-token retry does not take the duplicate success path');
            expect(str_contains($second['html'], 'Verification failed. Please try again.'), 'Consumed-token retry shows the Turnstile error');
            expect(str_contains($second['log'], 'turnstile'), 'Consumed-token retry is logged as turnstile');
            expect(!str_contains($second['log'], 'duplicate'), 'Consumed-token retry is not logged as a duplicate');
            $table = $form === 'quote' ? 'quote_requests' : 'contact_submissions';
            expect($pdo->query("SELECT COUNT(*) FROM $table WHERE email='replay-$form@example.com'")->fetchColumn() == 1, 'Retry keeps one saved row');
            expect(count($emails()) === $before + 1, 'Retry keeps one notification');
            $input['message'] .= ' changed content';
            $third = $post($form, $input, 'fail');
            expect(str_contains($third['html'], 'alert-error') && str_contains($third['log'], 'turnstile'), 'Changed content still requires Turnstile');
            expect($pdo->query("SELECT COUNT(*) FROM $table WHERE email='replay-$form@example.com'")->fetchColumn() == 1, 'Rejected changed content adds no row');
        }
    };
    $tests['db-outage'] = function () use ($directory, $post, $contact, $quote, $emails) {
        $missingSocket = "localhost;unix_socket=$directory/missing-mysql.sock";
        foreach (['contact' => $contact, 'quote' => $quote] as $form => $input) {
            $input['name'] = 'Pat <Chef>';
            $input['email'] = "outage-$form@example.com";
            $input['message'] = 'Hold "my" spot';
            $before = count($emails());
            $result = $post($form, $input, 'pass', null, $missingSocket);
            expect(str_contains($result['html'], '<!DOCTYPE html>') && str_contains($result['html'], '</html>'), "$form DB outage renders complete HTML page");
            expect(str_contains($result['html'], '<header>') && str_contains($result['html'], '<footer>'), "$form DB outage includes header and footer");
            expect(str_contains($result['html'], 'alert-error') && str_contains($result['html'], 'Sorry, there was an error processing your request.'), "$form DB outage shows standard processing error");
            expect(str_contains($result['html'], 'Pat &lt;Chef&gt;') && str_contains($result['html'], 'Hold &quot;my&quot; spot'), "$form DB outage preserves escaped inputs");
            expect(substr_count($result['log'], 'Form rejection') === 1 && str_contains($result['log'], '"reason":"processing"'), "$form DB outage logs processing rejection");
            expect(!str_contains($result['log'], 'Hold "my" spot'), "$form DB outage never logs user content");
            expect(count($emails()) === $before, "$form DB outage sends no email");
        }
    };
    $selected = $argv[1] ?? null;
    expect($selected === null || isset($tests[$selected]), 'Unknown test selection');
    foreach ($tests as $name => $test) {
        if ($selected !== null && $selected !== $name) { continue; }
        $test(); echo "PASS $name\n";
    }
} finally {
    if (is_resource($server)) { proc_terminate($server); proc_close($server); }
    command(['rm', '-rf', $directory]);
}
