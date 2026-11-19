<?php
/**
 * Outbound Mail Transport
 * Sends HTML email via PHPMailer SMTP when available, falling back to PHP mail().
 */

/**
 * Quote a display name so reserved address-list characters stay one RFC 5322 phrase.
 */
function quoted_address_phrase(string $name): string {
    $name = preg_replace('/[\r\n]+/', ' ', $name) ?? '';
    return '"' . str_replace(['\\', '"'], ['\\\\', '\\"'], $name) . '"';
}

/**
 * Strip CR and LF from single-value email header components.
 */
function strip_header_line_breaks(string $value): string {
    return preg_replace('/[\r\n]+/', '', $value) ?? '';
}

/**
 * Send an outbound email message using injected SMTP configuration.
 *
 * @param array{
 *     to: string,
 *     subject: string,
 *     body: string,
 *     from_email?: ?string,
 *     from_name?: ?string,
 *     reply_to?: ?string,
 *     reply_to_name?: ?string,
 * } $message
 * @param array{
 *     host?: string,
 *     port?: int|string,
 *     user?: string,
 *     pass?: string,
 *     encryption?: string,
 *     from_email?: string,
 *     from_name?: string,
 * } $smtp
 */
function send_email(array $message, array $smtp = [], ?object $mailer = null): bool {
    $defaultFromEmail = (string)($smtp['from_email'] ?? 'wadadliflare.catering@gmail.com');
    $defaultFromName = (string)($smtp['from_name'] ?? 'Wadadli Flare Catering');

    $to = strip_header_line_breaks((string)($message['to'] ?? ''));
    $subject = strip_header_line_breaks((string)($message['subject'] ?? ''));
    $body = (string)($message['body'] ?? '');
    $fromEmail = strip_header_line_breaks((string)($message['from_email'] ?? $defaultFromEmail));
    $fromName = (string)($message['from_name'] ?? $defaultFromName);
    $replyTo = strip_header_line_breaks((string)($message['reply_to'] ?? $fromEmail));
    $replyToName = (string)($message['reply_to_name'] ?? $fromName);

    if ($mailer === null) {
        if (!class_exists('PHPMailer\\PHPMailer\\PHPMailer', false)) {
            $autoload = __DIR__ . '/../../vendor/autoload.php';
            if (is_file($autoload)) {
                require_once $autoload;
            }
        }
        if (class_exists('PHPMailer\\PHPMailer\\PHPMailer')) {
            $mailer = new \PHPMailer\PHPMailer\PHPMailer(true);
        }
    }

    if ($mailer !== null) {
        return send_email_smtp(
            $mailer,
            $to,
            $subject,
            $body,
            $fromEmail,
            $fromName,
            $replyTo,
            $replyToName,
            $smtp
        );
    }

    $headers = 'From: ' . quoted_address_phrase($fromName) . " <$fromEmail>\r\n";
    $headers .= 'Reply-To: ' . quoted_address_phrase($replyToName) . " <$replyTo>\r\n";
    $headers .= "Content-Type: text/html; charset=UTF-8\r\n";

    return mail($to, $subject, $body, $headers);
}

/**
 * Deliver an email through a PHPMailer-compatible SMTP client.
 */
function send_email_smtp(
    object $mailer,
    string $to,
    string $subject,
    string $body,
    string $fromEmail,
    string $fromName,
    string $replyTo,
    string $replyToName,
    array $smtp
): bool {
    $smtpHost = (string)($smtp['host'] ?? 'smtp.resend.com');
    $smtpPort = (int)($smtp['port'] ?? 587);
    $smtpUser = (string)($smtp['user'] ?? '');
    $smtpPass = (string)($smtp['pass'] ?? '');
    $smtpEncryption = (string)($smtp['encryption'] ?? 'tls');

    try {
        $mailer->isSMTP();
        $mailer->Host = $smtpHost;
        $mailer->SMTPAuth = true;
        $mailer->Username = $smtpUser;
        $mailer->Password = $smtpPass;
        $mailer->SMTPSecure = $smtpEncryption;
        $mailer->Port = $smtpPort;

        $mailer->setFrom($fromEmail, $fromName);
        $mailer->addAddress($to);
        $mailer->addReplyTo($replyTo, $replyToName);

        $mailer->isHTML(true);
        $mailer->Subject = $subject;
        $mailer->Body = $body;
        $mailer->AltBody = strip_tags($body);

        return (bool)$mailer->send();
    } catch (\Throwable $e) {
        $detail = property_exists($mailer, 'ErrorInfo') && $mailer->ErrorInfo !== ''
            ? $mailer->ErrorInfo
            : $e->getMessage();
        error_log('Email sending failed: ' . $detail);
        error_log("SMTP Debug: Host=$smtpHost, Port=$smtpPort, User=" . substr($smtpUser, 0, 10) . '...');
        return false;
    }
}
