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
 * Content-based spam filtering.
 * Returns true if the content looks like spam.
 */
function is_spam_content(string $message, string $name): bool {
    $message_lower = strtolower($message);
    $name_lower = strtolower($name);

    // Check for URLs in message (legitimate catering inquiries almost never include URLs)
    if (preg_match('/https?:\/\//i', $message)) {
        return true;
    }

    // Check for common spam phrases
    $spam_phrases = [
        '% off', 'percent off', 'free shipping', 'limited time',
        'act now', 'click here', 'buy now', 'order now',
        'special offer', 'discount', 'unsubscribe',
        'bitcoin', 'crypto', 'viagra', 'casino',
        'seo', 'web traffic', 'backlink', 'ranking',
        'posture corrector', 'weight loss',
    ];
    foreach ($spam_phrases as $phrase) {
        if (str_contains($message_lower, $phrase)) {
            return true;
        }
    }

    // Gibberish name detection (consonant clusters with no vowels)
    if (preg_match('/[bcdfghjklmnpqrstvwxyz]{5,}/i', $name)) {
        return true;
    }

    return false;
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

    $response = file_get_contents('https://challenges.cloudflare.com/turnstile/v0/siteverify', false, stream_context_create([
        'http' => [
            'method' => 'POST',
            'header' => 'Content-Type: application/x-www-form-urlencoded',
            'content' => http_build_query([
                'secret' => $secret_key,
                'response' => $token,
                'remoteip' => $_SERVER['REMOTE_ADDR'],
            ]),
        ],
    ]));

    if ($response === false) {
        error_log('Turnstile verification request failed');
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
