<?php
if (($argv[1] ?? '') === 'mail-fallback') {
    require __DIR__ . '/../private/includes/email.php';
    if (class_exists('PHPMailer\PHPMailer\PHPMailer', false)) {
        fwrite(STDERR, "PHPMailer is loaded; mail() fallback was not exercised\n");
        exit(3);
    }
    $sent = send_email(
        [
            'to' => 'owner@example.com',
            'subject' => 'Quote test',
            'body' => '<p>Hello</p>',
            'reply_to' => 'submitter@example.com',
            'reply_to_name' => 'attacker@example.com,',
        ],
        [
            'from_email' => 'noreply@example.com',
            'from_name' => 'Wadadli, "Flare" \\ Catering',
        ]
    );
    exit($sent ? 0 : 1);
}

require __DIR__ . '/../private/includes/email.php';

function expect(bool $condition, string $message): void {
    if (!$condition) {
        throw new RuntimeException($message);
    }
}

// 1. mail() fallback quotes both From and Reply-To display names without Composer on disk.
$directory = sys_get_temp_dir() . '/wadadli-mail-fallback-' . bin2hex(random_bytes(6));
mkdir($directory, 0700);
$capture = $directory . '/capture.sh';
$stdin = $directory . '/mail-stdin.txt';
file_put_contents($capture, "#!/bin/sh\ncat >> " . escapeshellarg($stdin) . "\nexit 0\n");
chmod($capture, 0700);
try {
    $process = proc_open(
        [PHP_BINARY, '-d', 'sendmail_path=' . $capture, '-d', 'mail.add_x_header=0', __FILE__, 'mail-fallback'],
        [1 => ['pipe', 'w'], 2 => ['pipe', 'w']],
        $pipes
    );
    $stdout = stream_get_contents($pipes[1]);
    $stderr = stream_get_contents($pipes[2]);
    fclose($pipes[1]);
    fclose($pipes[2]);
    expect(proc_close($process) === 0, 'mail() fallback failed: ' . $stderr . $stdout);
    $raw = str_replace("\r\n", "\n", (string)file_get_contents($stdin));
    preg_match_all('/^From: .*$/m', $raw, $fromMatches);
    preg_match_all('/^Reply-To: .*$/m', $raw, $replyToMatches);
    expect(
        $fromMatches[0] === ['From: "Wadadli, \"Flare\" \\\\ Catering" <noreply@example.com>'],
        'From display name must be RFC-quoted: ' . json_encode($fromMatches[0])
    );
    expect(
        $replyToMatches[0] === ['Reply-To: "attacker@example.com," <submitter@example.com>'],
        'Reply-To display name must stay one address phrase: ' . json_encode($replyToMatches[0])
    );
    echo "PASS mail() From and Reply-To display names are RFC-quoted\n";
} finally {
    if (is_file($capture)) {
        unlink($capture);
    }
    if (is_file($stdin)) {
        unlink($stdin);
    }
    if (is_dir($directory)) {
        rmdir($directory);
    }
}

// 2. Injected SMTP mailer path runs without Composer autoloader on disk and isolates failures.
$injectedMailer = new class {
    public bool $smtpConfigured = false;
    public string $Host = '';
    public bool $SMTPAuth = false;
    public string $Username = '';
    public string $Password = '';
    public string $SMTPSecure = '';
    public int $Port = 0;
    public array $from = [];
    public array $addresses = [];
    public array $replyTos = [];
    public bool $html = false;
    public string $Subject = '';
    public string $Body = '';
    public string $AltBody = '';
    public string $ErrorInfo = '';
    public bool $shouldThrow = false;

    public function isSMTP(): void { $this->smtpConfigured = true; }
    public function setFrom(string $address, string $name = ''): void { $this->from = [$address, $name]; }
    public function addAddress(string $address): void { $this->addresses[] = $address; }
    public function addReplyTo(string $address, string $name = ''): void { $this->replyTos[] = [$address, $name]; }
    public function isHTML(bool $isHtml): void { $this->html = $isHtml; }
    public function send(): bool {
        if ($this->shouldThrow) {
            $this->ErrorInfo = 'SMTP unreachable';
            throw new RuntimeException('SMTP error');
        }
        return true;
    }
};

$sent = send_email(
    [
        'to' => 'owner@example.com',
        'subject' => 'Lead notification',
        'body' => '<p>Hello <b>Chef</b></p>',
        'reply_to' => 'guest@example.com',
        'reply_to_name' => 'Guest Name',
    ],
    [
        'host' => 'smtp.example.com',
        'port' => 465,
        'user' => 'resend',
        'pass' => 'secret-pass',
        'encryption' => 'ssl',
        'from_email' => 'noreply@example.com',
        'from_name' => 'Wadadli Flare Catering',
    ],
    $injectedMailer
);
expect($sent === true, 'Injected SMTP mailer must return true on success');
expect($injectedMailer->smtpConfigured && $injectedMailer->Host === 'smtp.example.com' && $injectedMailer->Port === 465, 'SMTP settings configured');
expect($injectedMailer->from === ['noreply@example.com', 'Wadadli Flare Catering'], 'From configured on mailer');
expect($injectedMailer->addresses === ['owner@example.com'], 'Recipient configured on mailer');
expect($injectedMailer->replyTos === [['guest@example.com', 'Guest Name']], 'Reply-To configured on mailer');
expect($injectedMailer->html && $injectedMailer->AltBody === 'Hello Chef', 'HTML and plain AltBody set');

$errorLog = tempnam(sys_get_temp_dir(), 'wadadli-email-err-');
$previousLog = ini_set('error_log', $errorLog);
try {
    $injectedMailer->shouldThrow = true;
    $failed = send_email(
        ['to' => 'owner@example.com', 'subject' => 'Test', 'body' => '<p>Hi</p>'],
        ['host' => 'smtp.example.com', 'port' => 587, 'user' => 'resend'],
        $injectedMailer
    );
    expect($failed === false, 'Injected SMTP failure must return false');
    $logged = (string)file_get_contents($errorLog);
    expect(str_contains($logged, 'SMTP unreachable'), 'SMTP failure logs ErrorInfo');
} finally {
    ini_set('error_log', $previousLog === false ? '' : $previousLog);
    @unlink($errorLog);
}
echo "PASS injected SMTP mailer works without Composer and returns false on failure\n";
