<?php
date_default_timezone_set('America/New_York');
require __DIR__ . '/../private/includes/Reviews.php';

use WadadliFlare\Reviews;

function expect(bool $condition, string $message): void {
    if (!$condition) {
        throw new RuntimeException($message);
    }
}

function stored_review(string $author, string $source, int $time, string $text, array $extra = []): array {
    return array_merge([
        'source' => $source,
        'author' => $author,
        'author_url' => '',
        'photo_url' => '',
        'rating' => 5,
        'text' => $text,
        'time' => $time,
        'precision' => 'day',
        'live' => true,
    ], $extra);
}

function curated_review(string $author, string $source, int $time, string $text, array $extra = []): array {
    return array_merge([
        'source' => $source,
        'author' => $author,
        'rating' => 5,
        'text' => $text,
        'time' => $time,
        'precision' => 'day',
    ], $extra);
}

function api_review(string $author, string $text, string $publishTime, array $extra = []): array {
    return array_merge([
        'authorAttribution' => ['displayName' => $author, 'uri' => 'https://example.com/' . $author, 'photoUri' => ''],
        'rating' => 5,
        'originalText' => ['text' => $text],
        'publishTime' => $publishTime,
    ], $extra);
}

function write_json(string $path, array $payload): void {
    $dir = dirname($path);
    if (!is_dir($dir)) {
        mkdir($dir, 0775, true);
    }
    file_put_contents($path, json_encode($payload, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE));
}

function read_json(string $path): array {
    $decoded = json_decode((string) file_get_contents($path), true);
    expect(is_array($decoded), "JSON file must decode: $path");
    return $decoded;
}

function failing_fetch(): Closure {
    return static fn(): array => throw new RuntimeException('fetch must not run on the read side');
}

function remove_tree(string $path): void {
    if (is_dir($path)) {
        foreach (scandir($path) ?: [] as $entry) {
            if ($entry !== '.' && $entry !== '..') {
                remove_tree($path . '/' . $entry);
            }
        }
        rmdir($path);
    } elseif (file_exists($path)) {
        unlink($path);
    }
}

function authors(array $reviews): array {
    return array_map(static fn(array $review): string => $review['author'], $reviews);
}

/** @return array{0: int, 1: string, 2: string} Exit code, stdout, stderr. */
function php_run(string $code, array $arguments): array {
    $process = proc_open([
        PHP_BINARY, '-d', 'allow_url_fopen=0', '-d', 'allow_url_include=0',
        '-d', 'disable_functions=mail,curl_exec,curl_multi_exec,fsockopen,pfsockopen,stream_socket_client',
        '-r', $code, ...$arguments,
    ], [1 => ['pipe', 'w'], 2 => ['pipe', 'w']], $pipes, null, []);
    expect(is_resource($process), 'PHP subprocess must start');
    $output = (string) stream_get_contents($pipes[1]);
    $errors = (string) stream_get_contents($pipes[2]);
    fclose($pipes[1]);
    fclose($pipes[2]);
    return [proc_close($process), $output, $errors];
}

function php_page_output(string $code, array $arguments): string {
    [$exit, $output, $errors] = php_run($code, $arguments);
    expect($exit === 0 && $errors === '', 'PHP page subprocess must succeed without warnings: ' . $errors);
    return $output;
}

function page_xpath(string $html): DOMXPath {
    $document = new DOMDocument();
    $previous = libxml_use_internal_errors(true);
    try {
        expect($document->loadHTML('<meta charset="UTF-8">' . $html, LIBXML_NONET), 'Page HTML must parse');
    } finally {
        libxml_clear_errors();
        libxml_use_internal_errors($previous);
    }
    return new DOMXPath($document);
}

function card_authors(DOMXPath $xpath): array {
    $authors = [];
    foreach ($xpath->query('//div[@class="review-author"]') as $node) {
        $authors[] = trim($node->textContent);
    }
    return $authors;
}

$root = sys_get_temp_dir() . '/wadadli-reviews-test-' . bin2hex(random_bytes(6));
mkdir($root, 0700);
$scenario = 0;
$paths = static function () use ($root, &$scenario): array {
    $scenario++;
    $dir = $root . '/scenario-' . $scenario;
    return [$dir . '/cache/google-reviews.json', $dir . '/data/captured-reviews.json'];
};

try {
    // 1. Three-set precedence (curated < captured < live), newest first, undated last.
    [$cachePath, $capturedPath] = $paths();
    write_json($capturedPath, ['reviews' => [
        stored_review('Alice', 'google', 150, 'captured Alice'),
        stored_review('Dave', 'google', 400, 'captured Dave'),
    ]]);
    write_json($cachePath, ['fetched_at' => 1, 'rating' => 4.9, 'user_rating_count' => 9, 'maps_uri' => '', 'reviews' => [
        stored_review('Alice', 'google', 160, 'live Alice'),
        stored_review('Erin', 'google', 50, 'live Erin'),
    ]]);
    $reviews = new Reviews(failing_fetch(), $cachePath, $capturedPath, [
        curated_review('Alice', 'google', 100, 'curated Alice'),
        curated_review('Bob', 'facebook', 300, 'curated Bob'),
        curated_review('Carol', 'google', 200, 'curated Carol'),
        curated_review('Frank', 'google', 0, 'undated Frank', ['precision' => 'month']),
    ]);
    $list = $reviews->list();
    expect(authors($list) === ['Dave', 'Bob', 'Carol', 'Alice', 'Erin', 'Frank'], 'Merged list is newest-first with undated last: ' . json_encode(authors($list)));
    $alice = $list[3];
    expect($alice['text'] === 'live Alice' && $alice['time'] === 160, 'Live wording wins over captured and curated for the same author+source');
    expect(count(array_filter($list, static fn($r) => $r['author'] === 'Alice')) === 1, 'Same author+source collapses to one record');
    echo "PASS three-set precedence and ordering\n";

    // 2. Source filtering through the same merged list.
    expect(authors($reviews->list('google')) === ['Dave', 'Carol', 'Alice', 'Erin', 'Frank'], 'Google filter keeps only Google in order');
    expect(authors($reviews->list('facebook')) === ['Bob'], 'Facebook filter keeps only Facebook');
    expect($reviews->list('yelp') === [], 'Unknown source filters to an empty list');
    echo "PASS source filtering\n";

    // 3. Identity is author+source: same display name on another source is a different review.
    [$cachePath, $capturedPath] = $paths();
    $reviews = new Reviews(failing_fetch(), $cachePath, $capturedPath, [
        curated_review('Jo', 'google', 200, 'Jo on Google'),
        curated_review('Jo', 'facebook', 100, 'Jo on Facebook'),
        curated_review('Sam Smith', 'google', 300, 'first Sam'),
        curated_review('Sam Smith', 'google', 250, 'second Sam (same display name)'),
    ]);
    $list = $reviews->list();
    expect(count($list) === 3, 'Same-name same-source reviews collapse; different source does not');
    expect($list[0]['text'] === 'first Sam', 'First same-identity curated record is kept');
    echo "PASS identity rule (author plus source)\n";

    // 4. Consistent record shape and date labels (month, day, undated).
    [$cachePath, $capturedPath] = $paths();
    $dayTime = mktime(12, 0, 0, 7, 15, 2024);
    $monthTime = mktime(12, 0, 0, 3, 10, 2023);
    write_json($cachePath, ['rating' => null, 'user_rating_count' => null, 'reviews' => [
        stored_review('Live', 'google', $dayTime, 'live day', ['author_url' => 'https://example.com/live', 'photo_url' => 'https://example.com/p.jpg']),
    ]]);
    $reviews = new Reviews(failing_fetch(), $cachePath, $capturedPath, [
        curated_review('Monthly', 'google', $monthTime, 'curated month', ['precision' => 'month']),
        curated_review('Undated', 'facebook', 0, 'curated undated', ['precision' => 'month']),
    ]);
    $list = $reviews->list();
    $expectedKeys = ['source', 'author', 'author_url', 'photo_url', 'rating', 'text', 'time', 'precision', 'live', 'date_label'];
    foreach ($list as $record) {
        expect(array_keys($record) === $expectedKeys, 'Every record has the same fields in order: ' . json_encode(array_keys($record)));
    }
    $byAuthor = [];
    foreach ($list as $record) {
        $byAuthor[$record['author']] = $record;
    }
    expect($byAuthor['Monthly']['author_url'] === '' && $byAuthor['Monthly']['photo_url'] === '' && $byAuthor['Monthly']['live'] === false, 'Curated records gain empty URL/photo and live=false defaults');
    expect($byAuthor['Live']['live'] === true && $byAuthor['Live']['author_url'] === 'https://example.com/live', 'Live records keep their URL and live flag');
    expect($byAuthor['Live']['date_label'] === 'July 15, 2024', 'Day precision label, got: ' . $byAuthor['Live']['date_label']);
    expect($byAuthor['Monthly']['date_label'] === 'March 2023', 'Month precision label, got: ' . $byAuthor['Monthly']['date_label']);
    expect($byAuthor['Undated']['date_label'] === '', 'Undated records have an empty date label');
    echo "PASS consistent record shape and date labels\n";

    // 5. Rating summary present, absent (missing cache) and absent (empty count).
    [$cachePath, $capturedPath] = $paths();
    write_json($cachePath, ['rating' => 4.8, 'user_rating_count' => 27, 'reviews' => []]);
    $reviews = new Reviews(failing_fetch(), $cachePath, $capturedPath, []);
    expect($reviews->googleRatingSummary() === ['rating' => 4.8, 'count' => 27], 'Summary returns float rating and int count');
    [$cachePath, $capturedPath] = $paths();
    $reviews = new Reviews(failing_fetch(), $cachePath, $capturedPath, []);
    expect($reviews->googleRatingSummary() === null, 'Missing cache yields a null summary');
    [$cachePath, $capturedPath] = $paths();
    write_json($cachePath, ['rating' => 5.0, 'user_rating_count' => 0, 'reviews' => []]);
    $reviews = new Reviews(failing_fetch(), $cachePath, $capturedPath, []);
    expect($reviews->googleRatingSummary() === null, 'Empty rating count yields a null summary');
    echo "PASS rating summary present and absent\n";

    // 6. Missing cache and harvest files: curated archive alone, still normalized.
    [$cachePath, $capturedPath] = $paths();
    $reviews = new Reviews(failing_fetch(), $cachePath, $capturedPath, [
        curated_review('Only', 'google', 10, 'only curated'),
    ]);
    $list = $reviews->list();
    expect(authors($list) === ['Only'] && $list[0]['live'] === false && $list[0]['author_url'] === '', 'Missing files leave the curated floor');
    expect($reviews->googleRatingSummary() === null, 'Missing files leave no summary');
    echo "PASS missing cache and harvest files\n";

    // 7. One cache read per instance, until refresh replaces it.
    [$cachePath, $capturedPath] = $paths();
    write_json($cachePath, ['rating' => 4.0, 'user_rating_count' => 3, 'reviews' => [stored_review('First', 'google', 100, 'v1')]]);
    $reviews = new Reviews(static fn(): array => [
        'rating' => 4.5, 'userRatingCount' => 4, 'googleMapsUri' => '',
        'reviews' => [api_review('Third', 'v3', '2025-01-01T12:00:00Z')],
    ], $cachePath, $capturedPath, []);
    expect(authors($reviews->list()) === ['First'], 'First read sees the first cache');
    write_json($cachePath, ['rating' => 9.9, 'user_rating_count' => 99, 'reviews' => [stored_review('Second', 'google', 200, 'v2')]]);
    expect(authors($reviews->list()) === ['First'], 'External cache rewrite is not re-read by this instance');
    expect($reviews->googleRatingSummary() === ['rating' => 4.0, 'count' => 3], 'Summary describes the same read as the list');
    $reviews->refresh();
    expect(authors($reviews->list()) === ['Third'], 'After refresh the instance serves the new payload');
    expect($reviews->googleRatingSummary() === ['rating' => 4.5, 'count' => 4], 'After refresh the summary follows');
    echo "PASS one cache read per instance, refreshed after refresh()\n";

    // 8. Refresh success: cache contents, harvest contents, and the full report including the gap.
    [$cachePath, $capturedPath] = $paths();
    write_json($capturedPath, ['note' => 'old note', 'updated_at' => 1, 'reviews' => [
        stored_review('CapturedOld', 'google', mktime(12, 0, 0, 1, 1, 2024), 'old captured'),
    ]]);
    $timeA = strtotime('2025-06-17T14:33:48Z');
    $timeB = strtotime('2025-06-10T09:00:00Z');
    $reviews = new Reviews(static fn(): array => [
        'rating' => 4.9, 'userRatingCount' => 10, 'googleMapsUri' => 'https://maps.example/place',
        'reviews' => [
            api_review('NewA', '  fresh A  ', '2025-06-17T14:33:48Z'),
            api_review('NewB', 'fresh B', '2025-06-10T09:00:00Z'),
        ],
    ], $cachePath, $capturedPath, [curated_review('CuratedGoogle', 'google', 10, 'curated google')]);
    $report = $reviews->refresh();
    $expectedA = stored_review('NewA', 'google', $timeA, 'fresh A', ['author_url' => 'https://example.com/NewA']);
    $expectedB = stored_review('NewB', 'google', $timeB, 'fresh B', ['author_url' => 'https://example.com/NewB']);
    expect($report['cached_count'] === 2, 'Report cached_count');
    expect($report['rating'] === 4.9 && $report['user_rating_count'] === 10, 'Report rating and count');
    expect(authors($report['added']) === ['NewA', 'NewB'], 'Report lists newly captured reviews');
    expect(array_keys($report['added'][0]) === $expectedKeys, 'Report added records carry the display shape');
    expect($report['captured_total'] === 3, 'Report captured_total counts the whole harvest store');
    expect($report['capture_error'] === null, 'Report has no capture error on success');
    expect($report['shown_google'] === 4 && $report['total_ratings'] === 10, 'Report gap counts curated+captured+live Google reviews against the total');
    $cache = read_json($cachePath);
    expect(is_int($cache['fetched_at']) && $cache['fetched_at'] > 0, 'Cache fetched_at is a timestamp');
    expect($cache['rating'] === 4.9 && $cache['user_rating_count'] === 10 && $cache['maps_uri'] === 'https://maps.example/place', 'Cache envelope is unchanged');
    expect($cache['reviews'] === [$expectedA, $expectedB], 'Cache reviews are normalized storage records without date_label: ' . json_encode($cache['reviews']));
    $harvest = read_json($capturedPath);
    expect(is_int($harvest['updated_at']) && str_contains((string) $harvest['note'], 'Machine-maintained'), 'Harvest envelope is unchanged');
    expect(authors($harvest['reviews']) === ['NewA', 'NewB', 'CapturedOld'], 'Harvest keeps every seen review newest-first');
    expect($harvest['reviews'][0] === $expectedA, 'Harvest records keep the storage shape');
    expect(authors($reviews->list('google')) === ['NewA', 'NewB', 'CapturedOld', 'CuratedGoogle'], 'Refreshed instance shows the just-captured reviews');
    echo "PASS refresh success (cache, harvest, report with gap)\n";

    // 9. Capture of a new review, and edited wording replaces without counting as new.
    [$cachePath, $capturedPath] = $paths();
    write_json($capturedPath, ['reviews' => [stored_review('Editor', 'google', $timeA, 'old wording', ['author_url' => 'https://example.com/Editor'])]]);
    $reviews = new Reviews(static fn(): array => [
        'rating' => 5.0, 'userRatingCount' => 2, 'googleMapsUri' => '',
        'reviews' => [
            api_review('Editor', 'new wording', '2025-06-17T14:33:48Z'),
            api_review('BrandNew', 'never seen', '2025-06-10T09:00:00Z'),
        ],
    ], $cachePath, $capturedPath, []);
    $report = $reviews->refresh();
    expect(authors($report['added']) === ['BrandNew'], 'Edited review is not counted as new');
    expect($report['captured_total'] === 2, 'Harvest total after one capture and one edit');
    $harvest = read_json($capturedPath);
    $editor = null;
    foreach ($harvest['reviews'] as $record) {
        if ($record['author'] === 'Editor') {
            $editor = $record;
        }
    }
    expect($editor !== null && $editor['text'] === 'new wording', 'Edited wording replaces the stored text');
    echo "PASS capture of a new review and edited-wording replacement\n";

    // 10. No harvest write when nothing changed (mtime is the last-discovery record).
    [$cachePath, $capturedPath] = $paths();
    $same = stored_review('Same', 'google', $timeA, 'same text', ['author_url' => 'https://example.com/Same']);
    write_json($capturedPath, ['note' => 'keep me', 'updated_at' => 42, 'reviews' => [$same]]);
    $past = time() - 3600;
    touch($capturedPath, $past);
    $beforeBytes = file_get_contents($capturedPath);
    $reviews = new Reviews(static fn(): array => [
        'rating' => 5.0, 'userRatingCount' => 1, 'googleMapsUri' => '',
        'reviews' => [api_review('Same', 'same text', '2025-06-17T14:33:48Z')],
    ], $cachePath, $capturedPath, []);
    $report = $reviews->refresh();
    clearstatcache(true, $capturedPath);
    expect($report['added'] === [] && $report['capture_error'] === null, 'Unchanged harvest reports no additions');
    expect(file_get_contents($capturedPath) === $beforeBytes && filemtime($capturedPath) === $past, 'Unchanged harvest is not rewritten');
    echo "PASS no harvest write when nothing changed\n";

    // 11. Fetch failure leaves the previous cache (and harvest) untouched and throws.
    [$cachePath, $capturedPath] = $paths();
    write_json($cachePath, ['rating' => 1.0, 'user_rating_count' => 1, 'reviews' => [stored_review('Old', 'google', 5, 'old cache')]]);
    $cacheBytes = file_get_contents($cachePath);
    $reviews = new Reviews(static fn(): array => throw new RuntimeException('Places is down'), $cachePath, $capturedPath, []);
    $threw = false;
    try {
        $reviews->refresh();
    } catch (RuntimeException $error) {
        $threw = $error->getMessage() === 'Places is down';
    }
    expect($threw, 'Fetch failure throws the fetch error');
    expect(file_get_contents($cachePath) === $cacheBytes, 'Fetch failure leaves the cache byte-identical');
    expect(!file_exists($capturedPath), 'Fetch failure writes no harvest file');
    echo "PASS fetch failure leaving the cache untouched\n";

    // 12. Cache-write failure throws and leaves the previous cache target untouched.
    [$cachePath, $capturedPath] = $paths();
    mkdir(dirname($cachePath), 0775, true);
    mkdir($cachePath, 0775, true);
    file_put_contents($cachePath . '/sentinel', 'previous');
    $reviews = new Reviews(static fn(): array => [
        'rating' => 5.0, 'userRatingCount' => 1, 'googleMapsUri' => '',
        'reviews' => [api_review('New', 'text', '2025-06-17T14:33:48Z')],
    ], $cachePath, $capturedPath, []);
    $threw = false;
    set_error_handler(static fn(): bool => true);
    try {
        $reviews->refresh();
    } catch (RuntimeException) {
        $threw = true;
    } finally {
        restore_error_handler();
    }
    expect($threw, 'Cache-write failure throws');
    expect(file_get_contents($cachePath . '/sentinel') === 'previous', 'Cache-write failure leaves the previous cache in place');
    expect(!file_exists($capturedPath), 'Cache-write failure runs no harvest capture');
    echo "PASS cache-write failure leaving the cache untouched\n";

    // 13. Empty-text reviews are dropped before caching and capture.
    [$cachePath, $capturedPath] = $paths();
    $reviews = new Reviews(static fn(): array => [
        'rating' => 5.0, 'userRatingCount' => 3, 'googleMapsUri' => '',
        'reviews' => [
            api_review('Blank', '   ', '2025-06-17T14:33:48Z'),
            ['authorAttribution' => ['displayName' => 'NoText'], 'rating' => 4, 'publishTime' => '2025-06-16T10:00:00Z'],
            api_review('Good', 'real words', '2025-06-15T10:00:00Z'),
        ],
    ], $cachePath, $capturedPath, []);
    $report = $reviews->refresh();
    expect($report['cached_count'] === 1, 'Only the review with text is cached');
    expect(authors(read_json($cachePath)['reviews']) === ['Good'], 'Cache drops empty-text reviews');
    expect(authors(read_json($capturedPath)['reviews']) === ['Good'], 'Harvest drops empty-text reviews');
    echo "PASS empty-text reviews dropped\n";

    // 14. Harvest failure is reported in the report, not thrown, after the cache is written.
    [$cachePath, $capturedPath] = $paths();
    mkdir(dirname($capturedPath), 0775, true);
    mkdir($capturedPath, 0775, true);
    $reviews = new Reviews(static fn(): array => [
        'rating' => 5.0, 'userRatingCount' => 1, 'googleMapsUri' => '',
        'reviews' => [api_review('Fresh', 'fresh text', '2025-06-17T14:33:48Z')],
    ], $cachePath, $capturedPath, []);
    set_error_handler(static fn(): bool => true);
    try {
        $report = $reviews->refresh();
    } finally {
        restore_error_handler();
    }
    expect($report['cached_count'] === 1, 'Cache is written before the harvest step');
    expect(is_string($report['capture_error']) && $report['capture_error'] !== '', 'Harvest failure is reported, not thrown');
    expect($report['added'] === [], 'Failed capture adds nothing');
    expect(authors(read_json($cachePath)['reviews']) === ['Fresh'], 'Cache holds the fetched review despite the harvest failure');
    echo "PASS harvest failure reported after caching\n";

    // 15. The module itself reads no environment and carries no path constants or data requires.
    $moduleSource = (string) file_get_contents(__DIR__ . '/../private/includes/Reviews.php');
    foreach (['getenv', '$_ENV', '$_SERVER', 'define(', 'reviews-data.php', 'env.php'] as $needle) {
        expect(!str_contains($moduleSource, $needle), "Reviews module must not reference $needle");
    }
    echo "PASS module reads no environment, constants or data files\n";

    // 16. Page checks: home and reviews pages render curated reviews in order,
    // with no warnings and no network or mail access, through the factory override.
    [$cachePath, $capturedPath] = $paths();
    $pageRunner = <<<'PHP'
define('ENV_LOADED', true);
[, $config, $wiring, $page, $cache, $captured, $curated] = $argv;
require $config;
require $wiring;
\reviews(new \WadadliFlare\Reviews(
    static fn(): array => throw new \RuntimeException('pages must not fetch'),
    $cache,
    $captured,
    require $curated
));
require $page;
PHP;
    $pageArgs = [
        __DIR__ . '/../public/includes/config.php',
        __DIR__ . '/../private/includes/reviews_factory.php',
    ];
    $curatedPath = __DIR__ . '/../private/reviews-data.php';
    $homeHtml = php_page_output($pageRunner, [...$pageArgs, __DIR__ . '/../public/index.php', $cachePath, $capturedPath, $curatedPath]);
    expect(card_authors(page_xpath($homeHtml)) === ['Darlene Edlund', 'Hes'], 'Home page shows the two newest curated reviews in order');
    echo "PASS index.php page render\n";
    $reviewsHtml = php_page_output($pageRunner, [...$pageArgs, __DIR__ . '/../public/reviews.php', $cachePath, $capturedPath, $curatedPath]);
    $xpath = page_xpath($reviewsHtml);
    expect(card_authors($xpath) === [
        'Darlene Edlund', 'Hes', 'Don Menya', 'Taylore Harris', 'Todd Kennedy',
        'Lisa Betz', 'Scott Caley', 'Katie D', 'deborah Hargreaves', 'Anna Carter',
        'Gretchen Geyer', 'Shana Kennedy', 'Fred Rife', 'Jacob C Stephens',
    ], 'Reviews page renders curated Google then Facebook reviews newest-first, undated last within Google');
    $dates = [];
    foreach ($xpath->query('//div[@class="review-date"]') as $node) {
        $dates[] = trim($node->textContent);
    }
    expect($dates[0] === 'September 7, 2026', 'First review date label renders unchanged, got: ' . ($dates[0] ?? 'none'));
    expect(count($dates) === 11, 'The three undated reviews render no date element');
    expect(!str_contains($reviewsHtml, 'out of 5, from'), 'No rating summary renders without a cache');
    echo "PASS reviews.php page render\n";

    // 17. Refresh script: same stdout lines, stderr lines and exit codes as before,
    // driven end to end through the production wiring file with a fake fetch.
    $scriptRunner = <<<'PHP'
define('ENV_LOADED', true);
set_error_handler(static fn(): bool => true);
[, $wiring, $script, $cache, $captured, $mode] = $argv;
require $wiring;
$fetch = match ($mode) {
    'fail' => static fn(): array => throw new \RuntimeException('Places is down'),
    default => static fn(): array => [
        'rating' => 4.7, 'userRatingCount' => $mode === 'nonew' ? 1 : 5, 'googleMapsUri' => '',
        'reviews' => [[
            'authorAttribution' => ['displayName' => 'Fresh', 'uri' => '', 'photoUri' => ''],
            'rating' => 5, 'originalText' => ['text' => 'fresh text'], 'publishTime' => '2025-06-17T14:33:48Z',
        ]],
    ],
};
\reviews(new \WadadliFlare\Reviews($fetch, $cache, $captured, []));
require $script;
PHP;
    $scriptArgs = [__DIR__ . '/../private/includes/reviews_factory.php', __DIR__ . '/../private/refresh-google-reviews.php'];
    [$cachePath, $capturedPath] = $paths();
    [$exit, $out, $err] = php_run($scriptRunner, [...$scriptArgs, $cachePath, $capturedPath, 'new']);
    expect($exit === 0 && $err === '', 'Refresh script success exits 0 without stderr: ' . $err);
    expect($out === "Cached 1 Google review (place rating 4.7 from 5 ratings)\n"
        . "NEW: captured 1 review not seen before:\n"
        . "  Fresh, 2025-06-17, 5 stars: fresh text…\n"
        . "Note: Google reports 5 ratings but the site can show 1. 4 reviews has never appeared in the API.\n", 'Refresh script success stdout is unchanged: ' . $out);
    [$cachePath, $capturedPath] = $paths();
    write_json($capturedPath, ['reviews' => [stored_review('Fresh', 'google', strtotime('2025-06-17T14:33:48Z'), 'fresh text')]]);
    [$exit, $out, $err] = php_run($scriptRunner, [...$scriptArgs, $cachePath, $capturedPath, 'nonew']);
    expect($exit === 0 && $err === '', 'Refresh script no-new exits 0');
    expect($out === "Cached 1 Google review (place rating 4.7 from 1 ratings)\nNo new reviews. 1 held in the capture store.\n", 'Refresh script no-new stdout is unchanged: ' . $out);
    [$cachePath, $capturedPath] = $paths();
    [$exit, $out, $err] = php_run($scriptRunner, [...$scriptArgs, $cachePath, $capturedPath, 'fail']);
    expect($exit === 1 && $out === '' && $err === "Google reviews refresh failed: Places is down\n", 'Refresh script fetch failure stderr and exit are unchanged: ' . $err);
    [$cachePath, $capturedPath] = $paths();
    mkdir(dirname($capturedPath), 0775, true);
    mkdir($capturedPath, 0775, true);
    [$exit, $out, $err] = php_run($scriptRunner, [...$scriptArgs, $cachePath, $capturedPath, 'new']);
    expect($exit === 1, 'Refresh script harvest failure exits 1');
    expect($out === "Cached 1 Google review (place rating 4.7 from 5 ratings)\n", 'Cached-count line prints before the harvest failure');
    expect($err === 'Could not update the captured-review store: Could not write ' . $capturedPath . "\n", 'Refresh script harvest failure stderr is unchanged: ' . $err);
    echo "PASS refresh script stdout, stderr and exit codes\n";

    // 18. A harvest failure does not re-read the broken store for the report:
    // the failed capture's own read is the only read (and the only warning).
    [$cachePath, $capturedPath] = $paths();
    mkdir(dirname($capturedPath), 0775, true);
    mkdir($capturedPath, 0775, true);
    $harvestReads = 0;
    set_error_handler(static function (int $severity, string $message) use (&$harvestReads): bool {
        if (str_contains($message, 'file_get_contents()')) {
            $harvestReads++;
        }
        return true;
    });
    try {
        $reviews = new Reviews(static fn(): array => [
            'rating' => 5.0, 'userRatingCount' => 1, 'googleMapsUri' => '',
            'reviews' => [api_review('Fresh', 'fresh text', '2025-06-17T14:33:48Z')],
        ], $cachePath, $capturedPath, []);
        $report = $reviews->refresh();
    } finally {
        restore_error_handler();
    }
    expect($report['capture_error'] !== null, 'Directory harvest path still reports a capture error');
    expect($harvestReads === 1, "Harvest store is read exactly once across a failed refresh, got $harvestReads");
    expect($report['captured_total'] === 0, 'Unreadable harvest store totals zero from the single read');
    echo "PASS harvest failure reads the store once\n";

    echo "ALL PASS\n";
} finally {
    remove_tree($root);
}
