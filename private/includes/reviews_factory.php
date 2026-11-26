<?php
/**
 * Production wiring for Reviews.
 * Supplies the Google Places curl adapter, the documented cache and harvest
 * paths (see DEPLOYMENT.md), and the curated archive. Lives beside the
 * module so both the public pages and the command-line refresh script reach
 * the same memoized instance.
 */
require_once __DIR__ . '/env.php';
require_once __DIR__ . '/Reviews.php';

use WadadliFlare\Reviews;

/**
 * Call the Places API and return the decoded place payload. The API key is
 * read at call time, so page requests never touch it.
 *
 * @throws RuntimeException on missing key, transport failure, or API error.
 */
function fetch_google_place_http(): array {
    $apiKey = $_ENV['GOOGLE_PLACES_API_KEY'] ?? getenv('GOOGLE_PLACES_API_KEY') ?: '';
    if ($apiKey === '') {
        throw new RuntimeException('GOOGLE_PLACES_API_KEY is not set in private/.env');
    }

    $placeId = 'ChIJKfbIeN9nxokR6Jbbm66babk';
    $url = 'https://places.googleapis.com/v1/places/' . $placeId;

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
 * Return the wired Reviews instance, or install an override for tests.
 */
function reviews(?Reviews $override = null): Reviews {
    static $instance = null;
    if ($override !== null) {
        $instance = $override;
        return $instance;
    }
    if ($instance !== null) {
        return $instance;
    }

    $instance = new Reviews(
        static fn(): array => fetch_google_place_http(),
        dirname(__DIR__) . '/cache/google-reviews.json',
        dirname(__DIR__) . '/data/captured-reviews.json',
        require dirname(__DIR__) . '/reviews-data.php'
    );
    return $instance;
}
