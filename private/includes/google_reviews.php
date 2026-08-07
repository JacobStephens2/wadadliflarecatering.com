<?php
/**
 * Google Reviews: fetch, cache, and merge with the curated archive.
 *
 * Page requests only ever read the cache file, so a slow or failing Google API
 * can never slow down or break the site. refresh-google-reviews.php refreshes
 * the cache on a timer.
 *
 * Known limit: the Places API returns at most 5 reviews and ranks them by
 * relevance, not recency. That is why merge_reviews() layers the live feed over
 * the archive instead of replacing it.
 */

require_once __DIR__ . '/env.php';

define('GOOGLE_REVIEWS_CACHE', dirname(__DIR__) . '/cache/google-reviews.json');
define('CAPTURED_REVIEWS_FILE', dirname(__DIR__) . '/data/captured-reviews.json');
define('GOOGLE_REVIEWS_PLACE_ID', 'ChIJKfbIeN9nxokR6Jbbm66babk');

/**
 * Call the Places API and return the decoded place payload.
 *
 * @throws RuntimeException on missing key, transport failure, or API error.
 */
function fetch_google_place()
{
    $apiKey = $_ENV['GOOGLE_PLACES_API_KEY'] ?? getenv('GOOGLE_PLACES_API_KEY') ?: '';
    if ($apiKey === '') {
        throw new RuntimeException('GOOGLE_PLACES_API_KEY is not set in private/.env');
    }

    $url = 'https://places.googleapis.com/v1/places/' . GOOGLE_REVIEWS_PLACE_ID;

    $ch = curl_init($url);
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_TIMEOUT => 15,
        CURLOPT_HTTPHEADER => [
            'X-Goog-Api-Key: ' . $apiKey,
            // Field mask is required by the Places API and is what you get billed on.
            'X-Goog-FieldMask: id,displayName,rating,userRatingCount,googleMapsUri,reviews',
        ],
    ]);

    $body = curl_exec($ch);
    $status = curl_getinfo($ch, CURLINFO_RESPONSE_CODE);
    $curlError = curl_error($ch);
    curl_close($ch);

    if ($body === false) {
        throw new RuntimeException('Request to Places API failed: ' . $curlError);
    }

    $data = json_decode($body, true);
    if (!is_array($data)) {
        throw new RuntimeException('Places API returned invalid JSON (HTTP ' . $status . ')');
    }
    if ($status !== 200) {
        $message = $data['error']['message'] ?? 'unknown error';
        throw new RuntimeException('Places API returned HTTP ' . $status . ': ' . $message);
    }

    return $data;
}

/**
 * Reshape a Places API review into the same structure the archive uses.
 */
function normalize_google_review(array $review)
{
    $text = $review['originalText']['text'] ?? $review['text']['text'] ?? '';
    $publishTime = $review['publishTime'] ?? '';
    $timestamp = $publishTime !== '' ? strtotime($publishTime) : false;

    return [
        'source' => 'google',
        'author' => $review['authorAttribution']['displayName'] ?? 'Google user',
        'author_url' => $review['authorAttribution']['uri'] ?? '',
        'photo_url' => $review['authorAttribution']['photoUri'] ?? '',
        'rating' => (int) ($review['rating'] ?? 5),
        'text' => trim($text),
        'time' => $timestamp !== false ? $timestamp : 0,
        'precision' => 'day',
        'live' => true,
    ];
}

/**
 * Fetch from Google and write the cache. Returns the cache payload.
 *
 * @throws RuntimeException if the fetch fails or the cache cannot be written.
 */
function refresh_google_reviews_cache()
{
    $place = fetch_google_place();

    $reviews = [];
    foreach ($place['reviews'] ?? [] as $review) {
        $normalized = normalize_google_review($review);
        if ($normalized['text'] !== '') {
            $reviews[] = $normalized;
        }
    }

    $payload = [
        'fetched_at' => time(),
        'rating' => $place['rating'] ?? null,
        'user_rating_count' => $place['userRatingCount'] ?? null,
        'maps_uri' => $place['googleMapsUri'] ?? '',
        'reviews' => $reviews,
    ];

    $dir = dirname(GOOGLE_REVIEWS_CACHE);
    if (!is_dir($dir) && !mkdir($dir, 0775, true) && !is_dir($dir)) {
        throw new RuntimeException('Could not create cache directory ' . $dir);
    }

    // Write to a temp file and rename so a page request never reads a half-written file.
    $tmp = GOOGLE_REVIEWS_CACHE . '.tmp';
    $json = json_encode($payload, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
    if ($json === false || file_put_contents($tmp, $json) === false) {
        throw new RuntimeException('Could not write cache file ' . $tmp);
    }
    if (!rename($tmp, GOOGLE_REVIEWS_CACHE)) {
        throw new RuntimeException('Could not move cache into place at ' . GOOGLE_REVIEWS_CACHE);
    }

    return $payload;
}

/**
 * Read the harvest store: every Google review the API has ever handed us.
 *
 * The API only ever returns 5 reviews, ranked by relevance, so a review can
 * appear one week and be gone the next. Recording each one the first time it is
 * seen means the site accumulates reviews instead of only ever showing whichever
 * 5 Google currently favours.
 */
function read_captured_reviews()
{
    if (!is_readable(CAPTURED_REVIEWS_FILE)) {
        return [];
    }
    $decoded = json_decode((string) file_get_contents(CAPTURED_REVIEWS_FILE), true);
    return is_array($decoded['reviews'] ?? null) ? $decoded['reviews'] : [];
}

/**
 * Add any reviews we have not seen before to the harvest store.
 *
 * Only writes when something actually changed, so the file's mtime is a true
 * record of when a review was last discovered. Returns the newly captured
 * reviews so the caller can report them.
 *
 * @throws RuntimeException if the store cannot be written.
 */
function capture_new_reviews(array $live)
{
    $existing = read_captured_reviews();
    $byIdentity = [];
    foreach ($existing as $review) {
        $byIdentity[review_identity($review)] = $review;
    }

    $added = [];
    foreach ($live as $review) {
        $key = review_identity($review);
        if (!isset($byIdentity[$key])) {
            $byIdentity[$key] = $review;
            $added[] = $review;
        } elseif (($byIdentity[$key]['text'] ?? '') !== ($review['text'] ?? '')) {
            // Reviewer edited their review; keep the current wording.
            $byIdentity[$key] = $review;
        }
    }

    if (empty($added) && $existing === array_values($byIdentity)) {
        return [];
    }

    $reviews = array_values($byIdentity);
    usort($reviews, function ($a, $b) {
        return ($b['time'] ?? 0) <=> ($a['time'] ?? 0);
    });

    $payload = [
        'note' => 'Machine-maintained by refresh-google-reviews.php. Every Google review ever returned by the Places API, kept so none is lost when it drops out of the API\'s 5-review window.',
        'updated_at' => time(),
        'reviews' => $reviews,
    ];

    $dir = dirname(CAPTURED_REVIEWS_FILE);
    if (!is_dir($dir) && !mkdir($dir, 0775, true) && !is_dir($dir)) {
        throw new RuntimeException('Could not create data directory ' . $dir);
    }

    $json = json_encode($payload, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
    if ($json === false || file_put_contents(CAPTURED_REVIEWS_FILE, $json, LOCK_EX) === false) {
        throw new RuntimeException('Could not write ' . CAPTURED_REVIEWS_FILE);
    }

    return $added;
}

/**
 * Read the cache. Returns null when it is missing or unreadable.
 */
function read_google_reviews_cache()
{
    if (!is_readable(GOOGLE_REVIEWS_CACHE)) {
        return null;
    }
    $decoded = json_decode((string) file_get_contents(GOOGLE_REVIEWS_CACHE), true);
    return is_array($decoded) ? $decoded : null;
}

/**
 * Key used to decide whether a live review and an archive review are the same
 * one. Author name is the only field both sources reliably share.
 */
function review_identity(array $review)
{
    return strtolower(trim($review['author'] ?? '')) . '|' . ($review['source'] ?? '');
}

/**
 * Merge live Google reviews over the archive, newest first.
 *
 * Live entries win on collision, so a review that Google has since edited shows
 * its current text. Archive entries with no live counterpart are kept, which is
 * what stops the 5-review API cap from shrinking the page.
 */
function merge_reviews(array $archive, array $live)
{
    $merged = [];
    foreach ($live as $review) {
        $merged[review_identity($review)] = $review;
    }
    foreach ($archive as $review) {
        $key = review_identity($review);
        if (!isset($merged[$key])) {
            $merged[$key] = $review;
        }
    }

    $merged = array_values($merged);
    usort($merged, function ($a, $b) {
        return ($b['time'] ?? 0) <=> ($a['time'] ?? 0);
    });

    return $merged;
}

/**
 * The site's review list: live Google reviews merged over the archive.
 *
 * @param string|null $source 'google', 'facebook', or null for everything.
 */
function get_reviews($source = null)
{
    static $all = null;

    if ($all === null) {
        // Three layers, each overriding the one before it:
        //   curated  - hand-maintained floor, includes Facebook and anything
        //              typed in by hand before Google's API ever surfaced it
        //   captured - every review the API has returned at any point
        //   live     - this hour's fetch, the most current wording and ratings
        $curated = require dirname(__DIR__) . '/reviews-data.php';
        $cache = read_google_reviews_cache();

        $all = merge_reviews($curated, read_captured_reviews());
        $all = merge_reviews($all, $cache['reviews'] ?? []);
    }

    if ($source === null) {
        return $all;
    }

    return array_values(array_filter($all, function ($review) use ($source) {
        return ($review['source'] ?? '') === $source;
    }));
}

/**
 * Aggregate Google rating, or null when the cache has never been populated.
 */
function get_google_rating_summary()
{
    $cache = read_google_reviews_cache();
    if (!$cache || empty($cache['user_rating_count'])) {
        return null;
    }
    return [
        'rating' => (float) $cache['rating'],
        'count' => (int) $cache['user_rating_count'],
    ];
}

/**
 * Human-readable review date. Archive entries with approximate timestamps show
 * month and year; everything else shows the exact day.
 */
function format_review_date(array $review)
{
    $time = $review['time'] ?? 0;
    if (!$time) {
        return '';
    }
    return ($review['precision'] ?? 'day') === 'month'
        ? date('F Y', $time)
        : date('F j, Y', $time);
}
