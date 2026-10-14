<?php
/**
 * Anti-Spam Protection Functions
 * Honeypot, rate limiting, content filtering, and Turnstile CAPTCHA verification.
 */

require_once __DIR__ . '/../../public/includes/config.php';

/**
 * Check honeypot field — bots auto-fill hidden fields that real users never see.
 * Returns true if spam detected.
 */
function check_honeypot(): bool {
    return !empty($_POST['website_url']);
}

/**
 * Server-side rate limiting by IP address.
 * Returns true if the request is allowed, false if rate limited.
 */
function check_rate_limit(string $form_name, int $max_per_hour = 3): bool {
    $ip = $_SERVER['REMOTE_ADDR'];
    $rate_limit_dir = PRIVATE_PATH . '/rate_limits';
    if (!is_dir($rate_limit_dir)) {
        mkdir($rate_limit_dir, 0750, true);
    }

    $file = $rate_limit_dir . '/' . md5($ip . $form_name) . '.json';

    $timestamps = [];
    if (file_exists($file)) {
        $timestamps = json_decode(file_get_contents($file), true) ?: [];
    }

    $one_hour_ago = time() - 3600;
    $timestamps = array_filter($timestamps, fn($t) => $t > $one_hour_ago);

    if (count($timestamps) >= $max_per_hour) {
        return false;
    }

    $timestamps[] = time();
    file_put_contents($file, json_encode(array_values($timestamps)));
    return true;
}

/**
 * Content heuristics flag a lead for review; they never reject it.
 */
function is_spam_content(string $message, string $name): ?string {
    if (preg_match('/https?:\/\//i', $message . ' ' . $name)) {
        return 'Contains a URL';
    }
    if (preg_match('/\b(?:seo services|buy backlinks|viagra|posture corrector)\b/i', $message . ' ' . $name)) {
        return 'Unrelated advertising';
    }
    return null;
}

/**
 * One structured line per rejection; never include submitted content.
 */
function log_form_rejection(string $form, string $reason): void {
    error_log('Form rejection ' . json_encode([
        'form' => $form, 'reason' => $reason,
        'ip' => $_SERVER['REMOTE_ADDR'] ?? 'unknown',
    ], JSON_UNESCAPED_SLASHES));
}

/**
 * Verify Cloudflare Turnstile token.
 * Returns true if verification passes.
 */
function verify_turnstile(string $token): bool {
    $secret_key = defined('TURNSTILE_SECRET') ? TURNSTILE_SECRET : '';
    if (empty($secret_key) || empty($token)) {
        return false;
    }

    $response = @file_get_contents('https://challenges.cloudflare.com/turnstile/v0/siteverify', false, stream_context_create([
        'http' => [
            'method' => 'POST',
            'timeout' => 10,
            'header' => 'Content-Type: application/x-www-form-urlencoded',
            'content' => http_build_query([
                'secret' => $secret_key,
                'response' => $token,
                'remoteip' => $_SERVER['REMOTE_ADDR'],
            ]),
        ],
    ]));

    if ($response === false) {
        return false;
    }

    $result = json_decode($response, true);
    return ($result['success'] ?? false) === true;
}

/**
 * Purge stale rate limit files older than 2 hours.
 * Call this periodically via cron or at the end of form processing.
 */
function purge_stale_rate_limits(): void {
    $rate_limit_dir = PRIVATE_PATH . '/rate_limits';
    if (!is_dir($rate_limit_dir)) {
        return;
    }

    $two_hours_ago = time() - 7200;
    foreach (glob($rate_limit_dir . '/*.json') as $file) {
        if (filemtime($file) < $two_hours_ago) {
            unlink($file);
        }
    }
}
