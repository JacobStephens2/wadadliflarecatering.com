<?php
/**
 * Public composition wiring for Lead intake.
 * Supplies site configuration, lazy database access, Turnstile verification, and Mail transport.
 */
require_once __DIR__ . '/config.php';
require_once PRIVATE_PATH . '/includes/db.php';
require_once PRIVATE_PATH . '/includes/email.php';
require_once PRIVATE_PATH . '/includes/LeadIntake.php';

use WadadliFlare\LeadIntake;

/**
 * Verify a Cloudflare Turnstile token against Cloudflare's siteverify endpoint.
 */
function verify_turnstile_http(string $secretKey, string $token, string $clientIp): bool {
    if ($secretKey === '' || $token === '') {
        return false;
    }

    $response = @file_get_contents('https://challenges.cloudflare.com/turnstile/v0/siteverify', false, stream_context_create([
        'http' => [
            'method' => 'POST',
            'timeout' => 10,
            'header' => 'Content-Type: application/x-www-form-urlencoded',
            'content' => http_build_query([
                'secret' => $secretKey,
                'response' => $token,
                'remoteip' => $clientIp,
            ]),
        ],
    ]));

    if ($response === false) {
        return false;
    }

    $result = json_decode($response, true);
    return is_array($result) && ($result['success'] ?? false) === true;
}

/**
 * Return the wired LeadIntake instance, or install an override for end-to-end tests.
 */
function lead_intake(?LeadIntake $override = null): LeadIntake {
    static $instance = null;
    if ($override !== null) {
        $instance = $override;
        return $instance;
    }
    if ($instance !== null) {
        return $instance;
    }

    $dbConfig = [
        'host' => $_ENV['DB_HOST'] ?? getenv('DB_HOST') ?: 'localhost',
        'name' => $_ENV['DB_NAME'] ?? getenv('DB_NAME') ?: 'wadadli_flare',
        'user' => $_ENV['DB_USER'] ?? getenv('DB_USER') ?: 'root',
        'pass' => $_ENV['DB_PASS'] ?? getenv('DB_PASS') ?: '',
    ];
    $smtpConfig = [
        'host' => $_ENV['SMTP_HOST'] ?? getenv('SMTP_HOST') ?: 'smtp.resend.com',
        'port' => $_ENV['SMTP_PORT'] ?? getenv('SMTP_PORT') ?: 587,
        'user' => $_ENV['SMTP_USER'] ?? getenv('SMTP_USER') ?: '',
        'pass' => $_ENV['SMTP_PASS'] ?? getenv('SMTP_PASS') ?: '',
        'encryption' => $_ENV['SMTP_ENCRYPTION'] ?? getenv('SMTP_ENCRYPTION') ?: 'tls',
        'from_email' => $_ENV['SMTP_FROM_EMAIL'] ?? getenv('SMTP_FROM_EMAIL') ?: 'wadadliflare.catering@gmail.com',
        'from_name' => $_ENV['SMTP_FROM_NAME'] ?? getenv('SMTP_FROM_NAME') ?: 'Wadadli Flare Catering',
    ];
    $turnstileSecret = defined('TURNSTILE_SECRET') ? TURNSTILE_SECRET : '';

    $instance = new LeadIntake(
        static fn(): PDO => open_database($dbConfig),
        static fn(string $token, string $clientIp): bool => verify_turnstile_http($turnstileSecret, $token, $clientIp),
        static fn(array $message): bool => send_email($message, $smtpConfig),
        CONTACT_EMAIL,
        PRIVATE_PATH . '/rate_limits'
    );
    return $instance;
}
