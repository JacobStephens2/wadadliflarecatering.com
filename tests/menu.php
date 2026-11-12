<?php
define('BASE_URL', '/menu-test/');

function expect(bool $condition, string $message): void {
    if (!$condition) {
        throw new RuntimeException($message);
    }
}

function menu_document(string $html): DOMXPath {
    $document = new DOMDocument();
    $previous = libxml_use_internal_errors(true);
    try {
        expect($document->loadHTML('<meta charset="UTF-8">' . $html, LIBXML_NONET), 'Menu HTML must parse');
    } finally {
        libxml_clear_errors();
        libxml_use_internal_errors($previous);
    }
    return new DOMXPath($document);
}

function php_output(string $code, array $arguments = []): string {
    $process = proc_open([
        PHP_BINARY, '-d', 'allow_url_fopen=0', '-d', 'allow_url_include=0',
        '-d', 'disable_functions=mail,curl_exec,curl_multi_exec,fsockopen,pfsockopen,stream_socket_client',
        '-r', $code, ...$arguments,
    ], [1 => ['pipe', 'w'], 2 => ['pipe', 'w']], $pipes, null, []);
    expect(is_resource($process), 'PHP subprocess must start');
    $output = stream_get_contents($pipes[1]);
    $errors = stream_get_contents($pipes[2]);
    fclose($pipes[1]);
    fclose($pipes[2]);
    expect(proc_close($process) === 0 && $errors === '', 'PHP subprocess must succeed without warnings: ' . $errors);
    return $output;
}

function expect_menu(DOMXPath $xpath, DOMElement $grid, array $expected, string $heading, array $expectedLinks = []): void {
    $cards = $xpath->query('./div', $grid);
    expect($cards->length === count($expected), 'Complete card count');
    $links = [];
    foreach ($expected as $index => [$name, $prices]) {
        $card = $cards->item($index);
        expect($card->getAttribute('class') === 'card', "$name card class");
        $titles = $xpath->query('./h1 | ./h2 | ./h3 | ./h4 | ./h5 | ./h6', $card);
        expect($titles->length === 1, "$name has one heading");
        $title = $titles->item(0);
        expect($title->tagName === $heading && $title->getAttribute('class') === 'card-title', "$name heading hierarchy and class");
        expect(trim($title->textContent) === $name, "Card $index must be $name");
        $actualPrices = [];
        foreach ($xpath->query('./p[1]/text()', $card) as $price) {
            $line = trim(preg_replace('/\s+/', ' ', $price->textContent));
            if ($line !== '') {
                $actualPrices[] = $line;
            }
        }
        expect($actualPrices === $prices, "$name offered pan sizes and prices");
        foreach ($xpath->query('.//a', $card) as $link) {
            expect($link->parentNode->getAttribute('style') === 'margin-top: 0.75rem;', "$name related link spacing");
            $links[] = [$name, trim($link->textContent), $link->getAttribute('href')];
        }
    }
    expect($links === $expectedLinks, 'Related menu link cards, order, text and destinations');
}

$starches = [
    ['Jasmine Rice', ['Half Pan - $30', 'Full Shallow Pan - $60', 'Full Deep Pan - $120']],
    ['Yellow Rice', ['Half Pan - $30', 'Full Shallow Pan - $60', 'Full Deep Pan - $120']],
    ['Spinach Rice', ['Half Pan - $30', 'Full Shallow Pan - $60', 'Full Deep Pan - $120']],
    ['Brown Rice', ['Half Pan - $35', 'Full Shallow Pan - $70', 'Full Deep Pan - $140']],
    ['Blended Rice', ['Half Pan - $40', 'Full Shallow Pan - $75', 'Full Deep Pan - $150']],
    ['Herb Roasted Potatoes', ['Half Pan - $25', 'Full Shallow Pan - $45', 'Full Deep Pan - $100']],
    ['Creamy Garlic Potatoes', ['Half Pan - $40', 'Full Shallow Pan - $60', 'Full Deep Pan - $120']],
    ['Rustic Mashed Potatoes', ['Half Pan - $40', 'Full Shallow Pan - $60', 'Full Deep Pan - $120']],
    ['Scalloped Potatoes', ['Half Pan - $40', 'Full Shallow Pan - $60', 'Full Deep Pan - $120']],
    ['Mac & Cheese', ['Half Pan - $50', 'Full Shallow Pan - $100', 'Full Deep Pan - $140']],
    ['Baked Ziti', ['Half Pan - $50', 'Full Shallow Pan - $100', 'Full Deep Pan - $140']],
];
$hotSides = [
    ['Mac & Cheese', ['Half Pan - $50', 'Full Shallow Pan - $100', 'Full Deep Pan - $140']],
    ['Rice (Jasmine / Bean / Yellow)', ['Half Pan - $30', 'Full Shallow Pan - $60', 'Full Deep Pan - $120']],
    ['Herb Roasted Potatoes', ['Half Pan - $25', 'Full Shallow Pan - $45', 'Full Deep Pan - $100']],
    ['BBQ Beans', ['Full Shallow Pan - $70', 'Full Deep Pan - $100']],
    ['Fried Plantain', ['Half Pan - $50', 'Full Shallow Pan - $100', 'Full Deep Pan - $150']],
    ['Cabbage & Carrot', ['Half Pan - $45', 'Full Shallow Pan - $80', 'Full Deep Pan - $120']],
    ['Vegetable Medley', ['Half Pan - $40', 'Full Shallow Pan - $85', 'Full Deep Pan - $140']],
];
$relatedLinks = [
    ['Herb Roasted Potatoes', 'See all starch options', BASE_URL . 'starches.php'],
    ['Vegetable Medley', 'See all vegetable options', BASE_URL . 'vegetables.php'],
];

expect(is_file(__DIR__ . '/../private/includes/pan_menu.php'), 'Pan menu rendering module must exist');
require __DIR__ . '/../private/includes/pan_menu.php';
foreach (['starches' => [$starches, 'h2', []], 'hot-sides' => [$hotSides, 'h3', $relatedLinks]] as $menu => [$expected, $heading, $links]) {
    ob_start();
    try {
        $html = render_pan_menu($menu);
    } finally {
        $output = ob_get_clean();
    }
    expect($output === '', "$menu rendering returns HTML without output");
    $xpath = menu_document('<div id="pan-menu">' . $html . '</div>');
    expect_menu($xpath, $xpath->query('//*[@id="pan-menu"]')->item(0), $expected, $heading, $links);
    expect(str_contains($html, 'Mac &amp; Cheese') && !str_contains($html, 'Mac & Cheese'), "$menu ampersand escaping");
    if ($menu === 'hot-sides') {
        expect(str_contains($html, 'Cabbage &amp; Carrot'), 'Hot Sides ampersand escaping');
    }
    echo "PASS $menu rendering: complete order, headings, pan sizes, prices, escaping and links\n";
}

foreach (['', 'vegetables', 'Hot Sides', 'starches '] as $menu) {
    $rejected = false;
    ob_start();
    try {
        render_pan_menu($menu);
    } catch (InvalidArgumentException $error) {
        $rejected = true;
    } finally {
        $output = ob_get_clean();
    }
    expect($rejected && $output === '', 'Unsupported menu keys must throw before any output');
}
echo "PASS unsupported menu keys throw before output\n";

$escapedHtml = php_output(
    'define("BASE_URL", "/menu-test/&\"\'<\xFF>/"); require $argv[1]; echo render_pan_menu("hot-sides");',
    [__DIR__ . '/../private/includes/pan_menu.php']
);
expect(str_contains($escapedHtml, 'href="/menu-test/&amp;&quot;&#039;&lt;�&gt;/starches.php"'), 'Link attributes escape quotes, ampersands and invalid UTF-8');
$xpath = menu_document($escapedHtml);
expect($xpath->query('//a')->length === 2, 'Escaping must preserve exactly two related links');
foreach ($xpath->query('//a') as $link) {
    expect($link->attributes->length === 1, 'BASE_URL must remain inside the href attribute');
}
echo "PASS related link attributes use UTF-8 quote and invalid-character escaping\n";

foreach (['starches.php', 'bbq-experience.php', 'caribbean-experience.php'] as $page) {
    $html = php_output('define("ENV_LOADED", true); require $argv[1];', [__DIR__ . '/../public/' . $page]);
    $xpath = menu_document($html);
    if ($page === 'starches.php') {
        $grids = $xpath->query('//main//div[@class="grid grid-2"]');
        expect($grids->length === 1, 'Starches keeps its enclosing grid');
        $grid = $grids->item(0);
        expect($grid->getAttribute('style') === 'margin-top: 3rem;', 'Starches grid spacing');
        expect_menu($xpath, $grid, $starches, 'h2');
    } else {
        $headings = $xpath->query('//main//h2[@class="section-subtitle" and normalize-space(.)="Hot Sides"]');
        expect($headings->length === 1, "$page retains the Hot Sides section heading");
        $heading = $headings->item(0);
        expect($heading->parentNode->getAttribute('style') === 'margin-top: 3rem;', "$page Hot Sides section spacing");
        $grid = $xpath->query('following-sibling::*[1]', $heading)->item(0);
        expect($grid instanceof DOMElement && $grid->getAttribute('class') === 'grid grid-2', "$page keeps its enclosing grid");
        expect_menu($xpath, $grid, $hotSides, 'h3', [
            ['Herb Roasted Potatoes', 'See all starch options', '/starches.php'],
            ['Vegetable Medley', 'See all vegetable options', '/vegetables.php'],
        ]);
        expect(str_contains($html, 'Cabbage &amp; Carrot'), "$page escapes Hot Sides dish names");
    }
    expect(str_contains($html, 'Mac &amp; Cheese'), "$page escapes the shared dish name");
    echo "PASS $page: credential-free output, complete Pan menu and shared offerings\n";
}
