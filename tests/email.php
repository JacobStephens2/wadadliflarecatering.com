<?php
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
