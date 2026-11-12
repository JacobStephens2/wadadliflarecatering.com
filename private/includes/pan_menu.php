<?php
/**
 * Return a Pan menu's cards; the site bootstrap supplies BASE_URL.
 * Dish facts, selections and presentation stay private to this function.
 *
 * @throws InvalidArgumentException for an unsupported menu key.
 */
function render_pan_menu(string $menuKey): string {
    if ($menuKey !== 'starches' && $menuKey !== 'hot-sides') {
        throw new InvalidArgumentException('Unsupported pan menu: ' . $menuKey);
    }

    $dishes = [
        'jasmine-rice' => [
            'name' => 'Jasmine Rice',
            'prices' => ['Half Pan' => 30, 'Full Shallow Pan' => 60, 'Full Deep Pan' => 120],
        ],
        'yellow-rice' => [
            'name' => 'Yellow Rice',
            'prices' => ['Half Pan' => 30, 'Full Shallow Pan' => 60, 'Full Deep Pan' => 120],
        ],
        'spinach-rice' => [
            'name' => 'Spinach Rice',
            'prices' => ['Half Pan' => 30, 'Full Shallow Pan' => 60, 'Full Deep Pan' => 120],
        ],
        'brown-rice' => [
            'name' => 'Brown Rice',
            'prices' => ['Half Pan' => 35, 'Full Shallow Pan' => 70, 'Full Deep Pan' => 140],
        ],
        'blended-rice' => [
            'name' => 'Blended Rice',
            'prices' => ['Half Pan' => 40, 'Full Shallow Pan' => 75, 'Full Deep Pan' => 150],
        ],
        'herb-roasted-potatoes' => [
            'name' => 'Herb Roasted Potatoes',
            'prices' => ['Half Pan' => 25, 'Full Shallow Pan' => 45, 'Full Deep Pan' => 100],
        ],
        'creamy-garlic-potatoes' => [
            'name' => 'Creamy Garlic Potatoes',
            'prices' => ['Half Pan' => 40, 'Full Shallow Pan' => 60, 'Full Deep Pan' => 120],
        ],
        'rustic-mashed-potatoes' => [
            'name' => 'Rustic Mashed Potatoes',
            'prices' => ['Half Pan' => 40, 'Full Shallow Pan' => 60, 'Full Deep Pan' => 120],
        ],
        'scalloped-potatoes' => [
            'name' => 'Scalloped Potatoes',
            'prices' => ['Half Pan' => 40, 'Full Shallow Pan' => 60, 'Full Deep Pan' => 120],
        ],
        'mac-and-cheese' => [
            'name' => 'Mac & Cheese',
            'prices' => ['Half Pan' => 50, 'Full Shallow Pan' => 100, 'Full Deep Pan' => 140],
        ],
        'baked-ziti' => [
            'name' => 'Baked Ziti',
            'prices' => ['Half Pan' => 50, 'Full Shallow Pan' => 100, 'Full Deep Pan' => 140],
        ],
        'hot-sides-rice' => [
            'name' => 'Rice (Jasmine / Bean / Yellow)',
            'prices' => ['Half Pan' => 30, 'Full Shallow Pan' => 60, 'Full Deep Pan' => 120],
        ],
        'bbq-beans' => [
            'name' => 'BBQ Beans',
            'prices' => ['Full Shallow Pan' => 70, 'Full Deep Pan' => 100],
        ],
        'fried-plantain' => [
            'name' => 'Fried Plantain',
            'prices' => ['Half Pan' => 50, 'Full Shallow Pan' => 100, 'Full Deep Pan' => 150],
        ],
        'cabbage-and-carrot' => [
            'name' => 'Cabbage & Carrot',
            'prices' => ['Half Pan' => 45, 'Full Shallow Pan' => 80, 'Full Deep Pan' => 120],
        ],
        'vegetable-medley' => [
            'name' => 'Vegetable Medley',
            'prices' => ['Half Pan' => 40, 'Full Shallow Pan' => 85, 'Full Deep Pan' => 140],
        ],
    ];
    $menus = [
        'starches' => [
            'heading' => 'h2',
            'dishes' => [
                'jasmine-rice', 'yellow-rice', 'spinach-rice', 'brown-rice',
                'blended-rice', 'herb-roasted-potatoes', 'creamy-garlic-potatoes',
                'rustic-mashed-potatoes', 'scalloped-potatoes', 'mac-and-cheese',
                'baked-ziti',
            ],
        ],
        'hot-sides' => [
            'heading' => 'h3',
            'dishes' => [
                'mac-and-cheese', 'hot-sides-rice', 'herb-roasted-potatoes',
                'bbq-beans', 'fried-plantain', 'cabbage-and-carrot',
                'vegetable-medley',
            ],
            'links' => [
                'herb-roasted-potatoes' => ['starches.php', 'See all starch options'],
                'vegetable-medley' => ['vegetables.php', 'See all vegetable options'],
            ],
        ],
    ];

    $menu = $menus[$menuKey];
    $heading = $menu['heading'];
    $escape = fn(string $text): string => htmlspecialchars($text, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
    $html = '';
    foreach ($menu['dishes'] as $dishKey) {
        $dish = $dishes[$dishKey];
        $prices = [];
        foreach ($dish['prices'] as $label => $price) {
            $prices[] = $escape($label) . ' - $' . $price;
        }
        $html .= '<div class="card">' . "\n"
            . '<' . $heading . ' class="card-title">' . $escape($dish['name']) . '</' . $heading . '>' . "\n"
            . '<p>' . implode('<br>', $prices) . '</p>' . "\n";
        if (isset($menu['links'][$dishKey])) {
            [$destination, $text] = $menu['links'][$dishKey];
            $html .= '<p style="margin-top: 0.75rem;"><a href="' . $escape(BASE_URL . $destination)
                . '">' . $escape($text) . '</a></p>' . "\n";
        }
        $html .= '</div>' . "\n";
    }
    return $html;
}
