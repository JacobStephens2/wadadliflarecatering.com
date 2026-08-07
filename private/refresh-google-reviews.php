<?php
/**
 * Refresh the cached Google reviews. Run from the systemd timer, or by hand:
 *
 *   sudo -u www-data php /var/www/wadadliflarecatering.com/private/refresh-google-reviews.php
 *
 * Exits non-zero and leaves the previous cache untouched when the fetch fails,
 * so a Google outage or an expired key degrades to stale reviews, never to none.
 */

if (PHP_SAPI !== 'cli') {
    http_response_code(404);
    exit;
}

require_once __DIR__ . '/includes/google_reviews.php';

try {
    $payload = refresh_google_reviews_cache();
} catch (RuntimeException $e) {
    fwrite(STDERR, 'Google reviews refresh failed: ' . $e->getMessage() . PHP_EOL);
    exit(1);
}

$count = count($payload['reviews']);
printf(
    "Cached %d Google review%s (place rating %s from %s ratings)%s",
    $count,
    $count === 1 ? '' : 's',
    $payload['rating'] ?? '?',
    $payload['user_rating_count'] ?? '?',
    PHP_EOL
);

if ($count > 0) {
    $newest = $payload['reviews'][0];
    foreach ($payload['reviews'] as $review) {
        if ($review['time'] > $newest['time']) {
            $newest = $review;
        }
    }
    printf("Newest: %s, %s%s", $newest['author'], date('Y-m-d', $newest['time']), PHP_EOL);
}
