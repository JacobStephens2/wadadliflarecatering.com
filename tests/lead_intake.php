<?php
require __DIR__ . '/../private/includes/LeadIntake.php';

use WadadliFlare\LeadIntake;

final class InMemoryLeadPdoStatement extends PDOStatement {
    private array $bound = [];

    public function __construct(
        private readonly InMemoryLeadPdo $pdo,
        private readonly string $query,
    ) {}

    public function execute(?array $params = null): bool {
        $this->bound = $params ?? [];
        $this->pdo->queries[] = ['query' => $this->query, 'params' => $this->bound];
        if (str_starts_with($this->query, 'INSERT INTO ')) {
            $this->pdo->inserts[] = ['query' => $this->query, 'params' => $this->bound];
        }
        return true;
    }

    public function fetchColumn(int $column = 0): mixed {
        if (str_starts_with($this->query, 'SELECT GET_LOCK(')) {
            return 1;
        }
        if (str_starts_with($this->query, 'SELECT id FROM ')) {
            return $this->pdo->duplicateResult;
        }
        return false;
    }
}

final class InMemoryLeadPdo extends PDO {
    public array $queries = [];
    public array $inserts = [];
    public mixed $duplicateResult = false;

    public function __construct() {}

    #[ReturnTypeWillChange]
    public function prepare(string $query, array $options = []): PDOStatement|false {
        return new InMemoryLeadPdoStatement($this, $query);
    }
}

function expect(bool $condition, string $message): void {
    if (!$condition) {
        throw new RuntimeException($message);
    }
}

if (($argv[1] ?? '') === 'rate-worker') {
    $rateDir = $argv[2];
    $ip = $argv[3];
    $intake = new LeadIntake(
        static fn(): PDO => new InMemoryLeadPdo(),
        static fn(string $token, string $clientIp): bool => true,
        static fn(array $message): bool => true,
        'owner@example.com',
        $rateDir
    );
    $result = $intake->submit('contact', [
        'name' => 'Worker',
        'email' => 'worker-' . bin2hex(random_bytes(4)) . '@example.com',
        'phone' => '',
        'message' => 'Concurrent test',
        'cf-turnstile-response' => 'valid',
    ], $ip);
    echo json_encode($result);
    exit(0);
}

$rateDir = sys_get_temp_dir() . '/wadadli-lead-intake-test-' . bin2hex(random_bytes(6));
mkdir($rateDir, 0700);
$logFile = $rateDir . '/php-error.log';
$previousLog = ini_set('error_log', $logFile);

$readLogs = static function () use ($logFile): string {
    return file_exists($logFile) ? (string)file_get_contents($logFile) : '';
};
$clearLogs = static function () use ($logFile): void {
    if (file_exists($logFile)) {
        file_put_contents($logFile, '');
    }
};

$contact = [
    'name' => 'Lynch',
    'email' => 'lynch@example.com',
    'phone' => '',
    'message' => 'Do you offer a discount for 200 guests?',
    'cf-turnstile-response' => 'valid',
];
$quote = array_merge($contact, [
    'event_type' => 'Wedding',
    'event_date' => '2026-10-14',
    'guest_count' => '200',
    'location' => 'Elverson',
]);

try {
    // 1. Unsupported form throws InvalidArgumentException before any I/O.
    $dbCalls = 0;
    $turnstileCalls = 0;
    $mailCalls = 0;
    $intake = new LeadIntake(
        static function () use (&$dbCalls): PDO {
            $dbCalls++;
            return new InMemoryLeadPdo();
        },
        static function (string $token, string $ip) use (&$turnstileCalls): bool {
            $turnstileCalls++;
            return true;
        },
        static function (array $message) use (&$mailCalls): bool {
            $mailCalls++;
            return true;
        },
        'owner@example.com',
        $rateDir
    );
    foreach (['', 'newsletter', 'contact '] as $badForm) {
        $threw = false;
        try {
            $intake->submit($badForm, $contact, '198.51.100.1');
        } catch (InvalidArgumentException) {
            $threw = true;
        }
        expect($threw, "Unsupported form '$badForm' must throw InvalidArgumentException in submit()");

        $threwPage = false;
        try {
            $intake->handlePageRequest($badForm, [], []);
        } catch (InvalidArgumentException) {
            $threwPage = true;
        }
        expect($threwPage, "Unsupported form '$badForm' must throw InvalidArgumentException in handlePageRequest()");
    }
    expect($dbCalls === 0 && $turnstileCalls === 0 && $mailCalls === 0 && glob($rateDir . '/*.json') === [], 'Unsupported form runs zero I/O');
    echo "PASS unsupported form throws before any I/O\n";

    // 2. GET / unset REQUEST_METHOD no-op and guard markup escaping.
    $warnings = [];
    set_error_handler(static function (int $severity, string $message) use (&$warnings): bool {
        $warnings[] = $message;
        return true;
    });
    try {
        $getState = $intake->handlePageRequest('quote', [], ['name' => 'Ignored']);
        $unsetState = $intake->handlePageRequest('contact', [], []);
    } finally {
        restore_error_handler();
    }
    expect($warnings === [], 'Unset REQUEST_METHOD must emit no warnings');
    expect($getState['success'] === false && $getState['error'] === '' && $getState['values']['name'] === '', 'GET request is a safe no-op');
    expect($unsetState['success'] === false && $unsetState['error'] === '' && $unsetState['values']['message'] === '', 'Unset REQUEST_METHOD is a safe no-op');

    $guards = $intake->renderSpamGuards('key" onclick="evil()&<>');
    expect(str_contains($guards, 'name="website_url"'), 'Guard markup renders honeypot');
    expect(str_contains($guards, 'data-sitekey="key&quot; onclick=&quot;evil()&amp;&lt;&gt;"'), 'Guard markup escapes data-sitekey');
    expect($dbCalls === 0 && $turnstileCalls === 0 && $mailCalls === 0, 'GET and guard rendering do zero I/O');
    echo "PASS GET no-op, unset REQUEST_METHOD, and guard markup escaping\n";

    // 3. Honeypot returns fake success with zero DB, rate-limit, Turnstile, or mail calls.
    $clearLogs();
    $honeypotResult = $intake->submit('contact', array_merge($contact, ['website_url' => 'http://bot.example']), '198.51.100.2');
    expect($honeypotResult === ['success' => true, 'error' => ''], 'Honeypot returns fake success');
    expect($dbCalls === 0 && $turnstileCalls === 0 && $mailCalls === 0 && glob($rateDir . '/*.json') === [], 'Honeypot triggers zero DB/rate-limit/Turnstile/mail work');
    $log = $readLogs();
    expect(substr_count($log, 'Form rejection') === 1, 'Honeypot emits one rejection log');
    expect(str_contains($log, '"form":"contact"') && str_contains($log, '"reason":"honeypot"') && str_contains($log, '"ip":"198.51.100.2"'), 'Honeypot log has form, reason, and IP');
    expect(!str_contains($log, '200 guests'), 'Honeypot log never includes submitted content');
    echo "PASS honeypot short-circuit\n";

    // 4. Turnstile gating (missing token and failed verifier) precedes validation error return.
    $clearLogs();
    $failingVerifier = new LeadIntake(
        static function () use (&$dbCalls): PDO {
            $dbCalls++;
            return new InMemoryLeadPdo();
        },
        static function (string $token, string $ip) use (&$turnstileCalls): bool {
            $turnstileCalls++;
            return false;
        },
        static function (array $message) use (&$mailCalls): bool {
            $mailCalls++;
            return true;
        },
        'owner@example.com',
        $rateDir
    );
    $missingToken = $failingVerifier->submit('contact', ['name' => '', 'email' => 'bad', 'message' => ''], '198.51.100.3');
    expect($missingToken === ['success' => false, 'error' => 'Verification failed. Please try again.'], 'Missing Turnstile token fails verification before validation');
    expect($turnstileCalls === 0 && $dbCalls === 0 && $mailCalls === 0, 'Empty Turnstile token short-circuits verifier and DB');
    expect(str_contains($readLogs(), '"reason":"turnstile"'), 'Missing token logs turnstile');

    $clearLogs();
    $failedToken = $failingVerifier->submit('contact', array_merge($contact, ['email' => 'bad']), '198.51.100.3');
    expect($failedToken === ['success' => false, 'error' => 'Verification failed. Please try again.'], 'Failed Turnstile returns verification error');
    expect($turnstileCalls === 1 && $dbCalls === 0 && $mailCalls === 0, 'Invalid inputs do not open DB when Turnstile fails');
    expect(str_contains($readLogs(), '"reason":"turnstile"'), 'Failed verifier logs turnstile');
    echo "PASS Turnstile gating\n";

    // 5. Validation checks and handlePageRequest escaped redisplay (no DB open on invalid input).
    $invalidCases = [
        ['contact', array_merge($contact, ['name' => '   ']), 'Please fill in all required fields.'],
        ['contact', array_merge($contact, ['email' => 'not-an-email']), 'Please enter a valid email address.'],
        ['contact', array_merge($contact, ['name' => str_repeat('a', 256)]), 'Please shorten the name field.'],
        ['contact', array_merge($contact, ['phone' => str_repeat('1', 51)]), 'Please shorten the phone field.'],
        ['contact', array_merge($contact, ['message' => str_repeat('é', 32768)]), 'Please shorten the message field.'],
        ['contact', array_merge($contact, ['message' => "\xFF"]), 'Please enter valid text in each field.'],
        ['contact', array_merge($contact, ['name' => ['nested']]), 'Please enter valid text in each field.'],
        ['quote', array_merge($quote, ['event_type' => 'Brunch']), 'Please select a valid event type.'],
        ['quote', array_merge($quote, ['event_date' => '2026-02-30']), 'Please enter a valid event date.'],
        ['quote', array_merge($quote, ['event_date' => '0999-12-31']), 'Please enter a valid event date.'],
        ['quote', array_merge($quote, ['event_date' => "2026-10-14\0junk"]), 'Please enter a valid event date.'],
        ['quote', array_merge($quote, ['guest_count' => '0']), 'Please enter a valid number of guests.'],
        ['quote', array_merge($quote, ['guest_count' => '2147483648']), 'Please enter a valid number of guests.'],
        ['quote', array_merge($quote, ['location' => str_repeat('x', 256)]), 'Please shorten the location field.'],
    ];
    foreach ($invalidCases as $index => [$form, $payload, $expectedError]) {
        $clearLogs();
        $ip = '198.51.101.' . ($index + 1);
        $res = $intake->submit($form, $payload, $ip);
        expect($res === ['success' => false, 'error' => $expectedError], "Validation case $index expected '$expectedError', got " . json_encode($res));
        expect(str_contains($readLogs(), '"reason":"validation"'), "Validation case $index must log validation");
    }
    $pageInvalid = $intake->handlePageRequest(
        'contact',
        ['REQUEST_METHOD' => 'POST', 'REMOTE_ADDR' => '198.51.101.99'],
        ['name' => '<script>alert(1)</script>', 'email' => 'bad', 'phone' => '555-1234', 'message' => 'Hi', 'cf-turnstile-response' => 'ok']
    );
    expect($pageInvalid['success'] === false && $pageInvalid['error'] === 'Please enter a valid email address.', 'handlePageRequest surfaces validation error');
    expect($pageInvalid['values']['name'] === '&lt;script&gt;alert(1)&lt;/script&gt;', 'handlePageRequest escapes redisplayed strings');
    $pageMalformed = $intake->handlePageRequest(
        'contact',
        ['REQUEST_METHOD' => 'POST', 'REMOTE_ADDR' => '198.51.101.100'],
        ['name' => 'Test', 'email' => 'test@example.com', 'phone' => ['arr'], 'message' => 'Hi', 'cf-turnstile-response' => 'ok']
    );
    expect($pageMalformed['success'] === false && $pageMalformed['error'] === 'Please enter valid text in each field.', 'handlePageRequest rejects non-string input');
    expect($pageMalformed['values']['phone'] === '', 'handlePageRequest safely blanks non-string redisplay');
    expect($dbCalls === 0 && $mailCalls === 0, 'Validation failures never open DB or send mail');
    echo "PASS validation rules and escaped redisplay\n";

    // 6. Locked self-purging rate limit (3/hr per IP/form, concurrent flock, >2h stale cleanup).
    $staleFile = $rateDir . '/stale-entry.json';
    $freshOtherFile = $rateDir . '/fresh-entry.json';
    file_put_contents($staleFile, '[1]');
    file_put_contents($freshOtherFile, '[1]');
    touch($staleFile, time() - 7201);
    touch($freshOtherFile, time() - 60);

    $pdo = new InMemoryLeadPdo();
    $rateIntake = new LeadIntake(
        $pdo,
        static fn(string $token, string $ip): bool => true,
        static fn(array $message): bool => true,
        'owner@example.com',
        $rateDir
    );
    $rateIp = '198.51.102.10';
    for ($i = 0; $i < 3; $i++) {
        $ok = $rateIntake->submit('contact', array_merge($contact, ['email' => "ok$i@example.com"]), $rateIp);
        expect($ok['success'] === true, "Rate limit slot $i must succeed");
    }
    expect(!file_exists($staleFile), 'Rate limiting purges >2h stale files internally');
    expect(file_exists($freshOtherFile), 'Rate limiting keeps fresh files');
    $clearLogs();
    $blocked = $rateIntake->submit('contact', array_merge($contact, ['email' => 'ok4@example.com']), $rateIp);
    expect($blocked === ['success' => false, 'error' => 'Please wait before submitting again.'], '4th request within hour is rate limited');
    expect(str_contains($readLogs(), '"reason":"rate limit"'), 'Rate limit rejection is logged');

    // Concurrent flock verification across 4 parallel subprocesses on a fresh IP.
    $concurrentIp = '198.51.102.20';
    $workers = [];
    $workerPipes = [];
    for ($w = 0; $w < 4; $w++) {
        $workers[] = proc_open(
            [PHP_BINARY, __FILE__, 'rate-worker', $rateDir, $concurrentIp],
            [1 => ['pipe', 'w'], 2 => ['pipe', 'w']],
            $pipes
        );
        $workerPipes[] = $pipes;
    }
    $allowedCount = 0;
    $limitedCount = 0;
    foreach ($workers as $idx => $proc) {
        $out = stream_get_contents($workerPipes[$idx][1]);
        $err = stream_get_contents($workerPipes[$idx][2]);
        fclose($workerPipes[$idx][1]);
        fclose($workerPipes[$idx][2]);
        expect(proc_close($proc) === 0, "Rate worker crashed: $err");
        $decoded = json_decode((string)$out, true);
        if (($decoded['success'] ?? false) === true) {
            $allowedCount++;
        } elseif (($decoded['error'] ?? '') === 'Please wait before submitting again.') {
            $limitedCount++;
        }
    }
    expect($allowedCount === 3 && $limitedCount === 1, "Concurrent flock rate limit must allow 3 and block 1, got allowed=$allowedCount limited=$limitedCount");
    echo "PASS locked self-purging rate limit (sequential + concurrent flock)\n";

    // 7. Combined spam flagging (never rejects), email escaping, subject sanitization, formatted event_date, Reply-To CR/LF stripping, lock release before send.
    $pdo = new InMemoryLeadPdo();
    $events = [];
    $sentMessages = [];
    $mailIntake = new LeadIntake(
        static function () use ($pdo, &$events): PDO {
            $events[] = 'open_pdo';
            return $pdo;
        },
        static fn(string $token, string $ip): bool => true,
        static function (array $message) use ($pdo, &$events, &$sentMessages): bool {
            // Confirm RELEASE_LOCK ran before email delivery!
            $lastQuery = end($pdo->queries)['query'] ?? '';
            expect(str_starts_with($lastQuery, 'SELECT RELEASE_LOCK('), 'Advisory lock must be released before sending mail');
            $events[] = 'send_mail';
            $sentMessages[] = $message;
            return true;
        },
        'owner@example.com',
        $rateDir
    );

    $spamCases = [
        ['message', 'Visit https://spam.example/offer', 'message: Contains a URL'],
        ['location', 'Best seo services in town', 'location: Unrelated advertising'],
        ['message', 'Send your ad to 5 million sites today', 'message: Mass advertising solicitation'],
        ['name', 'contactpageads.com promo', 'name: Mass advertising solicitation'],
    ];
    foreach ($spamCases as $idx => [$field, $val, $expectedFlag]) {
        $payload = $quote;
        $payload['email'] = "spam$idx@example.com";
        $payload[$field] = $val;
        $res = $mailIntake->submit('quote', $payload, '198.51.103.' . ($idx + 1));
        expect($res === ['success' => true, 'error' => ''], "Flagged submission '$expectedFlag' must still succeed");
        $insert = end($pdo->inserts);
        expect(end($insert['params']) === $expectedFlag, "Expected flag_reason '$expectedFlag', got " . json_encode(end($insert['params'])));
        $sent = end($sentMessages);
        expect(str_starts_with($sent['subject'], '[Possible spam] '), 'Flagged email subject must carry [Possible spam] prefix');
    }

    $escapePayload = [
        'name' => '<a href="https://evil.example">Chef</a>' . " > \r\nBcc: injected",
        'email' => "o'connor@example.com",
        'phone' => '<b>555</b>',
        'event_type' => 'Wedding',
        'event_date' => '2026-10-14',
        'guest_count' => '42',
        'location' => '<img src=x onerror=alert(1)>',
        'message' => '<script>x</script>',
        'cf-turnstile-response' => 'valid',
    ];
    $escRes = $mailIntake->submit('quote', $escapePayload, '198.51.104.1');
    expect($escRes === ['success' => true, 'error' => ''], 'Escaping test submission succeeds');
    $escMsg = end($sentMessages);
    expect($escMsg['to'] === 'owner@example.com', 'Recipient comes from injected config');
    expect(!preg_match('/[<>\r\n]/', $escMsg['subject']), 'Subject strips tags, <>, and CR/LF: ' . $escMsg['subject']);
    expect(!str_contains($escMsg['reply_to_name'], "\r") && !str_contains($escMsg['reply_to_name'], "\n"), 'Reply-To name strips CR/LF');
    expect(str_contains($escMsg['body'], "href='mailto:o&#039;connor@example.com'"), 'mailto attribute escapes apostrophes');
    expect(str_contains($escMsg['body'], 'October 14, 2026'), 'Formatted event_date appears in Quote email');
    expect(!str_contains($escMsg['body'], '<script>') && !str_contains($escMsg['body'], '<img '), 'HTML markup in fields is escaped');

    $emptyDateQuote = array_merge($quote, ['email' => 'nodate@example.com', 'event_date' => '']);
    expect($mailIntake->submit('quote', $emptyDateQuote, '198.51.104.2')['success'] === true, 'Quote without event_date succeeds');
    expect(str_contains(end($sentMessages)['body'], 'Not specified'), 'Empty Quote event_date renders Not specified');

    $contactMsgRes = $mailIntake->submit('contact', array_merge($contact, ['email' => 'plain-contact@example.com']), '198.51.104.3');
    expect($contactMsgRes['success'] === true, 'Contact submission succeeds');
    $contactMsg = end($sentMessages);
    expect(str_contains($contactMsg['subject'], 'New Contact Form Submission from Lynch'), 'Contact email subject prefix');
    expect(str_contains($contactMsg['body'], '<h2>New Contact Form Submission</h2>') && !str_contains($contactMsg['body'], 'Event Type:'), 'Contact email omits Quote fields');
    echo "PASS combined spam flagging, escaping, subject sanitization, formatted event_date, and lock release before mail\n";

    // 8. Partial mail failure (both returning false and throwing exception).
    foreach ([false, 'throw'] as $idx => $mode) {
        $partialIntake = new LeadIntake(
            new InMemoryLeadPdo(),
            static fn(string $token, string $ip): bool => true,
            static function (array $message) use ($mode): bool {
                if ($mode === 'throw') {
                    throw new RuntimeException('SMTP connection dropped');
                }
                return false;
            },
            'owner@example.com',
            $rateDir
        );
        $partial = $partialIntake->submit('contact', $contact, '198.51.105.' . ($idx + 1));
        expect(
            $partial === ['success' => false, 'error' => 'Your request was saved but we had trouble sending the email. We will still receive your request.'],
            "Partial mail failure mode '$mode' must return partial-failure notice"
        );
    }
    echo "PASS partial mail failure (false return and thrown exception)\n";

    // 9. Graceful DB-outage handling (logs 'processing', never leaks exception message or user input).
    $clearLogs();
    $outageIntake = new LeadIntake(
        static fn(): PDO => throw new PDOException('SECRET_DSN_LEAK PRIVATE_USER_TEXT'),
        static fn(string $token, string $ip): bool => true,
        static fn(array $message): bool => true,
        'owner@example.com',
        $rateDir
    );
    $outage = $outageIntake->submit('contact', array_merge($contact, ['message' => 'PRIVATE_USER_TEXT']), '198.51.106.1');
    expect(
        $outage === ['success' => false, 'error' => 'Sorry, there was an error processing your request. Please try again or call us directly.'],
        'DB outage returns standard processing error'
    );
    $outageLog = $readLogs();
    expect(substr_count($outageLog, 'Form rejection') === 1 && str_contains($outageLog, '"reason":"processing"'), 'DB outage logs one processing rejection');
    expect(!str_contains($outageLog, 'SECRET_DSN_LEAK') && !str_contains($outageLog, 'PRIVATE_USER_TEXT'), 'DB outage never logs exception text or submitted values');
    echo "PASS graceful DB outage handling\n";

    // 10. Duplicate suppression bypasses rate limit, Turnstile, INSERT, and mail.
    $dupPdo = new InMemoryLeadPdo();
    $dupPdo->duplicateResult = 99;
    $dupTurnstile = 0;
    $dupMail = 0;
    $dupIntake = new LeadIntake(
        $dupPdo,
        static function (string $token, string $ip) use (&$dupTurnstile): bool {
            $dupTurnstile++;
            return true;
        },
        static function (array $message) use (&$dupMail): bool {
            $dupMail++;
            return true;
        },
        'owner@example.com',
        $rateDir
    );
    $clearLogs();
    $dupRes = $dupIntake->submit('contact', $contact, '198.51.107.1');
    expect($dupRes === ['success' => true, 'error' => ''], 'Duplicate returns fake success');
    expect($dupTurnstile === 0 && $dupMail === 0 && $dupPdo->inserts === [], 'Duplicate bypasses Turnstile, insert, and mail');
    expect(str_contains($readLogs(), '"reason":"duplicate"'), 'Duplicate logs duplicate rejection');
    echo "PASS duplicate suppression and lock release\n";

    // 11. Public Contact and Quote pages render cleanly with REQUEST_METHOD unset and zero PHP warnings.
    foreach (['contact.php', 'quote-request.php'] as $page) {
        $proc = proc_open(
            [
                PHP_BINARY,
                '-d', 'allow_url_fopen=0',
                '-d', 'allow_url_include=0',
                '-d', 'disable_functions=mail,curl_exec,curl_multi_exec,fsockopen,pfsockopen,stream_socket_client',
                '-r', 'define("ENV_LOADED", true); require $argv[1];',
                __DIR__ . '/../public/' . $page,
            ],
            [1 => ['pipe', 'w'], 2 => ['pipe', 'w']],
            $pipes,
            null,
            []
        );
        $html = (string)stream_get_contents($pipes[1]);
        $stderr = (string)stream_get_contents($pipes[2]);
        fclose($pipes[1]);
        fclose($pipes[2]);
        expect(proc_close($proc) === 0 && $stderr === '', "$page GET render with unset REQUEST_METHOD failed: $stderr");
        expect(str_contains($html, 'name="website_url"') && str_contains($html, 'class="cf-turnstile"'), "$page renders shared spam guards");
        expect(str_contains($html, '</html>'), "$page renders complete HTML");
    }
    echo "PASS Contact and Quote pages render cleanly with unset REQUEST_METHOD\n";
} finally {
    ini_set('error_log', $previousLog === false ? '' : $previousLog);
    array_map('unlink', glob($rateDir . '/*') ?: []);
    @rmdir($rateDir);
}
