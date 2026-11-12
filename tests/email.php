<?php
if (($argv[1] ?? '') === 'mail-fallback') {
    define('ENV_LOADED', true); // Never load production credentials in tests.
    require __DIR__ . '/../private/includes/email.php';
    if (class_exists('PHPMailer\PHPMailer\PHPMailer', false)) {
        fwrite(STDERR, "PHPMailer is loaded; mail() fallback was not exercised\n");
        exit(3);
    }
    $name = 'attacker@example.com,';
    $address = 'submitter@example.com';
    $sent = sendContactEmail($name, $address, '', '');
    $sent = sendQuoteRequestEmail([
        'name' => $name, 'email' => $address, 'phone' => '', 'event_type' => 'Wedding',
        'event_date' => '', 'guest_count' => '', 'location' => '', 'message' => '',
    ]) && $sent;
    exit($sent ? 0 : 1);
}

require __DIR__ . '/support/mail_transport.php';
define('ENV_LOADED', true); // Never load production credentials in tests.
require __DIR__ . '/../private/includes/email.php';

function expect($condition, $message) {
    if (!$condition) {
        throw new RuntimeException($message);
    }
}

$file = tempnam(sys_get_temp_dir(), 'wadadli-mail-');
putenv("TEST_MAIL_FILE=$file");
try {
    $markup = '<a href="https://example.com">x</a>';
    $address = "o'connor@example.com";
    expect(filter_var($address, FILTER_VALIDATE_EMAIL), 'Fixture must be a valid email');
    sendContactEmail($markup . "\r\nBcc: injected", $address, $markup, $markup);
    sendQuoteRequestEmail([
        'name' => $markup . "\r\nBcc: injected", 'email' => $address,
        'phone' => $markup, 'event_type' => $markup, 'event_date' => '',
        'guest_count' => $markup, 'location' => $markup, 'message' => $markup,
    ], 'URL in message');
    $emails = array_map(fn($line) => json_decode($line, true), file($file));
    foreach ($emails as $email) {
        expect(!str_contains($email['body'], $markup), 'Submitted markup must be escaped in notification');
        expect(str_contains($email['body'], '&lt;a href=&quot;https://example.com&quot;&gt;x&lt;/a&gt;'), 'Escaped text must remain visible');
        expect(str_contains($email['body'], "href='mailto:o&#039;connor@example.com'"), 'Apostrophe must stay inside mailto attribute');
        expect(!preg_match('/[<>\r\n]/', $email['subject']), 'Subject must contain no markup or line breaks');
    }
    expect(!str_starts_with($emails[0]['subject'], '[Possible spam]'), 'Normal email subject');
    expect(str_starts_with($emails[1]['subject'], '[Possible spam]'), 'Flagged email subject');
    echo "PASS email escaping and subjects\n";
} finally {
    unlink($file);
}

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
    preg_match_all('/^Reply-To: .*$/m', $raw, $matches);
    $expected = 'Reply-To: "attacker@example.com," <submitter@example.com>';
    expect($matches[0] === [$expected, $expected], 'Reply-To display name must stay one address phrase: ' . json_encode($matches[0]));
    echo "PASS mail() Reply-To display name stays one address phrase\n";
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
