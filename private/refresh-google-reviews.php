<?php
/**
 * Refresh the cached Google reviews. Run from the systemd timer, or by hand:
 *
 *   sudo -u www-data php /var/www/wadadliflarecatering.com/private/refresh-google-reviews.php
 *
 * Exits non-zero and leaves the previous cache untouched when the fetch fails,
 * so a Google outage or an expired key degrades to stale reviews, never to none.
 * The Reviews module runs the whole cycle; this script only prints its report.
 */

if (PHP_SAPI !== 'cli') {
    http_response_code(404);
    exit;
}

require_once __DIR__ . '/includes/reviews_factory.php';

try {
    $report = reviews()->refresh();
} catch (RuntimeException $e) {
    fwrite(STDERR, 'Google reviews refresh failed: ' . $e->getMessage() . PHP_EOL);
    exit(1);
}

$count = $report['cached_count'];
printf(
    "Cached %d Google review%s (place rating %s from %s ratings)%s",
    $count,
    $count === 1 ? '' : 's',
    $report['rating'] ?? '?',
    $report['user_rating_count'] ?? '?',
    PHP_EOL
);

// The harvest store records anything new before it drops back out of the
// API's 5-review window; a failure there is reported after the cached count.
if ($report['capture_error'] !== null) {
    fwrite(STDERR, 'Could not update the captured-review store: ' . $report['capture_error'] . PHP_EOL);
    exit(1);
}

if (empty($report['added'])) {
    printf("No new reviews. %d held in the capture store.%s", $report['captured_total'], PHP_EOL);
} else {
    printf("NEW: captured %d review%s not seen before:%s", count($report['added']), count($report['added']) === 1 ? '' : 's', PHP_EOL);
    foreach ($report['added'] as $review) {
        printf(
            "  %s, %s, %d stars: %s%s",
            $review['author'],
            $review['time'] ? date('Y-m-d', $review['time']) : 'undated',
            $review['rating'],
            substr(preg_replace('/\s+/', ' ', $review['text']), 0, 70) . '…',
            PHP_EOL
        );
    }
}

// A gap between Google's rating count and what we can actually show means
// reviews exist that the API has never handed us. Those still need adding by
// hand, so make the gap visible rather than letting it pass silently.
$shown = $report['shown_google'];
$total = $report['total_ratings'];
if ($total > $shown) {
    printf(
        "Note: Google reports %d ratings but the site can show %d. %d review%s has never appeared in the API.%s",
        $total,
        $shown,
        $total - $shown,
        $total - $shown === 1 ? '' : 's',
        PHP_EOL
    );
}
