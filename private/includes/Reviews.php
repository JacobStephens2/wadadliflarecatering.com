<?php
namespace WadadliFlare;

/**
 * Reviews: the site's review list and Google rating summary behind one seam.
 *
 * Pages ask for display-ready review records and the rating summary; the
 * refresh script asks for one refresh cycle and prints the report it gets
 * back. Normalization, identity, the curated/captured/live precedence, the
 * atomic live-cache write, the harvest store, and the unseen-review gap are
 * implementation inside this module.
 *
 * Page requests only ever read the cache file, so a slow or failing Google
 * API can never slow down or break the site. The refresh script refreshes
 * the cache on a timer through the injected Places fetch.
 *
 * Known limit: the Places API returns at most 5 reviews and ranks them by
 * relevance, not recency. That is why the merge layers the live feed and the
 * harvest store over the curated archive instead of replacing it, and why
 * every live review ever seen is captured before it drops out of that window.
 *
 * Identity: author display name plus source is the dedupe key, because it is
 * the only field every source reliably shares. Two different reviewers who
 * share a display name on the same source therefore count as one review.
 * That collision is today's behaviour and stays pinned by tests; changing
 * what counts as the same review is a product decision with exactly one
 * place to land.
 */
final class Reviews {
    private const JSON_FLAGS = JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE;

    private ?array $cache = null;
    private bool $cacheLoaded = false;
    private ?array $captured = null;
    private ?array $merged = null;

    /**
     * @param \Closure $fetchPlace Places fetch port: returns the decoded place
     *                             payload, throwing \RuntimeException on failure.
     * @param array    $curated    The curated archive (the hand-maintained floor).
     */
    public function __construct(
        private readonly \Closure $fetchPlace,
        private readonly string $cachePath,
        private readonly string $capturedPath,
        private readonly array $curated,
    ) {}

    /**
     * The merged review list, newest first and undated last, as display-ready
     * records. Every record carries the same fields: source, author,
     * author_url, photo_url, rating, text, time, precision, live, date_label.
     *
     * @param string|null $source 'google', 'facebook', or null for everything.
     * @return list<array{source: string, author: string, author_url: string,
     *                    photo_url: string, rating: int, text: string, time: int,
     *                    precision: string, live: bool, date_label: string}>
     */
    public function list(?string $source = null): array {
        $records = array_map(
            static fn(array $review): array => self::displayRecord($review),
            $this->merged()
        );
        if ($source === null) {
            return $records;
        }
        return array_values(array_filter(
            $records,
            static fn(array $review): bool => $review['source'] === $source
        ));
    }

    /**
     * Aggregate Google rating, or null when the cache has never been populated.
     *
     * @return array{rating: float, count: int}|null
     */
    public function googleRatingSummary(): ?array {
        $cache = $this->cache();
        if (!$cache || empty($cache['user_rating_count'])) {
            return null;
        }
        return [
            'rating' => (float) ($cache['rating'] ?? null),
            'count' => (int) $cache['user_rating_count'],
        ];
    }

    /**
     * Run one whole refresh cycle: fetch, normalize, drop empty-text reviews,
     * write the live cache atomically, capture unseen reviews into the harvest
     * store, and compute the shown-versus-total gap.
     *
     * Fetch and cache-write failures throw, leaving the previous cache
     * untouched. A harvest-store failure is reported in the report instead,
     * so the caller can print the cached-count line first and then fail.
     *
     * @return array{cached_count: int, rating: int|float|null,
     *               user_rating_count: int|null,
     *               added: list<array{source: string, author: string,
     *                                 author_url: string, photo_url: string,
     *                                 rating: int, text: string, time: int,
     *                                 precision: string, live: bool,
     *                                 date_label: string}>,
     *               captured_total: int, capture_error: ?string,
     *               shown_google: int, total_ratings: int}
     * @throws \RuntimeException if the fetch fails or the cache cannot be written.
     */
    public function refresh(): array {
        $place = ($this->fetchPlace)();

        $reviews = [];
        foreach ($place['reviews'] ?? [] as $review) {
            $normalized = self::normalizeGoogleReview($review);
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

        self::ensureDirectory(dirname($this->cachePath), 'cache');

        // Write to a temp file and rename so a page request never reads a half-written file.
        $tmp = $this->cachePath . '.tmp';
        $json = json_encode($payload, self::JSON_FLAGS);
        if ($json === false || file_put_contents($tmp, $json) === false) {
            throw new \RuntimeException('Could not write cache file ' . $tmp);
        }
        if (!rename($tmp, $this->cachePath)) {
            throw new \RuntimeException('Could not move cache into place at ' . $this->cachePath);
        }

        // This instance now serves the payload it just wrote, so the gap
        // below counts the reviews this cycle is about to capture.
        $this->cache = $payload;
        $this->cacheLoaded = true;
        $this->merged = null;

        $added = [];
        $captureError = null;
        try {
            $added = $this->captureNew($reviews);
        } catch (\RuntimeException $error) {
            $captureError = $error->getMessage();
        }

        return [
            'cached_count' => count($reviews),
            'rating' => $payload['rating'],
            'user_rating_count' => $payload['user_rating_count'],
            'added' => array_map(
                static fn(array $review): array => self::displayRecord($review),
                $added
            ),
            'captured_total' => count($this->readCaptured()),
            'capture_error' => $captureError,
            'shown_google' => count($this->list('google')),
            'total_ratings' => (int) ($payload['user_rating_count'] ?? 0),
        ];
    }

    /**
     * The merged raw list, computed once per instance: three layers, each
     * overriding the one before it (curated floor, then every review the API
     * has ever returned, then this hour's fetch with the most current wording).
     */
    private function merged(): array {
        if ($this->merged === null) {
            $cache = $this->cache();
            $all = self::merge($this->curated, $this->readCaptured());
            $this->merged = self::merge($all, $cache['reviews'] ?? []);
        }
        return $this->merged;
    }

    /**
     * The one cache read this instance ever does, so the merged list and the
     * rating summary always describe the same fetch. Null when the cache is
     * missing or unreadable.
     */
    private function cache(): ?array {
        if (!$this->cacheLoaded) {
            $this->cacheLoaded = true;
            if (is_readable($this->cachePath)) {
                $decoded = json_decode((string) file_get_contents($this->cachePath), true);
                $this->cache = is_array($decoded) ? $decoded : null;
            }
        }
        return $this->cache;
    }

    /**
     * The harvest store: every Google review the API has ever handed us.
     * Recording each one the first time it is seen means the site accumulates
     * reviews instead of only ever showing whichever 5 Google favours.
     */
    private function readCaptured(): array {
        // Read once per instance, like the live cache: the merged list, the
        // capture step, and the refresh report all describe the same store,
        // and a failed capture must not re-read (and re-warn on) a broken file.
        if ($this->captured === null) {
            $this->captured = [];
            if (is_readable($this->capturedPath)) {
                $decoded = json_decode((string) file_get_contents($this->capturedPath), true);
                $this->captured = is_array($decoded['reviews'] ?? null) ? $decoded['reviews'] : [];
            }
        }
        return $this->captured;
    }

    /**
     * Add any reviews not seen before to the harvest store. Edited wording
     * replaces the stored text without counting as new. Only writes when
     * something actually changed, so the file's mtime is a true record of when
     * a review was last discovered. Returns the newly captured reviews.
     *
     * @throws \RuntimeException if the store cannot be written.
     */
    private function captureNew(array $live): array {
        $existing = $this->readCaptured();
        $byIdentity = [];
        foreach ($existing as $review) {
            $byIdentity[self::identity($review)] = $review;
        }

        $added = [];
        foreach ($live as $review) {
            $key = self::identity($review);
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

        $reviews = self::sortNewestFirst(array_values($byIdentity));

        $payload = [
            'note' => 'Machine-maintained by refresh-google-reviews.php. Every Google review ever returned by the Places API, kept so none is lost when it drops out of the API\'s 5-review window.',
            'updated_at' => time(),
            'reviews' => $reviews,
        ];

        self::ensureDirectory(dirname($this->capturedPath), 'data');

        $json = json_encode($payload, self::JSON_FLAGS);
        if ($json === false || file_put_contents($this->capturedPath, $json, LOCK_EX) === false) {
            throw new \RuntimeException('Could not write ' . $this->capturedPath);
        }

        $this->captured = $reviews;

        return $added;
    }

    /**
     * Merge $live over $archive, newest first. Live entries win on collision,
     * so a review that Google has since edited shows its current text.
     * Archive entries with no live counterpart are kept, which is what stops
     * the 5-review API cap from shrinking the page.
     */
    private static function merge(array $archive, array $live): array {
        $merged = [];
        foreach ($live as $review) {
            $merged[self::identity($review)] = $review;
        }
        foreach ($archive as $review) {
            $key = self::identity($review);
            if (!isset($merged[$key])) {
                $merged[$key] = $review;
            }
        }

        return self::sortNewestFirst(array_values($merged));
    }

    private static function sortNewestFirst(array $reviews): array {
        usort($reviews, static fn(array $a, array $b): int => ($b['time'] ?? 0) <=> ($a['time'] ?? 0));
        return $reviews;
    }

    /**
     * @throws \RuntimeException if the directory cannot be created.
     */
    private static function ensureDirectory(string $dir, string $label): void {
        if (!is_dir($dir) && !mkdir($dir, 0775, true) && !is_dir($dir)) {
            throw new \RuntimeException('Could not create ' . $label . ' directory ' . $dir);
        }
    }

    /**
     * Key used to decide whether two records are the same review: the
     * reviewer's display name plus the source. Author name is the only field
     * both sources reliably share, so two reviewers sharing a display name
     * on one source collide into one record (see the class note).
     */
    private static function identity(array $review): string {
        return strtolower(trim($review['author'] ?? '')) . '|' . ($review['source'] ?? '');
    }

    /**
     * Reshape a Places API review into the storage structure the cache and
     * the harvest store have always used.
     */
    private static function normalizeGoogleReview(array $review): array {
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
     * Fill in the fields a stored record may lack (curated entries carry no
     * author_url, photo_url, or live) and attach the date label, so every
     * record leaving the module has one consistent display shape.
     */
    private static function displayRecord(array $review): array {
        $time = (int) ($review['time'] ?? 0);
        $precision = (string) ($review['precision'] ?? 'day');

        return [
            'source' => (string) ($review['source'] ?? ''),
            'author' => (string) ($review['author'] ?? ''),
            'author_url' => (string) ($review['author_url'] ?? ''),
            'photo_url' => (string) ($review['photo_url'] ?? ''),
            'rating' => (int) ($review['rating'] ?? 5),
            'text' => (string) ($review['text'] ?? ''),
            'time' => $time,
            'precision' => $precision,
            'live' => (bool) ($review['live'] ?? false),
            'date_label' => self::dateLabel($time, $precision),
        ];
    }

    /**
     * Human-readable review date. Approximate (month-precision) timestamps
     * show month and year; undated reviews show nothing.
     */
    private static function dateLabel(int $time, string $precision): string {
        if (!$time) {
            return '';
        }
        return $precision === 'month'
            ? date('F Y', $time)
            : date('F j, Y', $time);
    }
}
