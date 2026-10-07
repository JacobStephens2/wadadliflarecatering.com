<?php
require_once __DIR__ . '/includes/config.php';
$pageTitle = 'Starches';
$pageDescription = 'Catering starch options from Wadadli Flare: rice, potatoes, mac and cheese, and baked ziti, with half pan, full shallow pan, and full deep pan pricing.';
include __DIR__ . '/includes/header.php';

$starchOptions = [
    ['Jasmine Rice', 30, 60, 120],
    ['Yellow Rice', 30, 60, 120],
    ['Spinach Rice', 30, 60, 120],
    ['Brown Rice', 35, 70, 140],
    ['Blended Rice', 40, 75, 150],
    ['Herb Roasted Potatoes', 30, 55, 140],
    ['Creamy Garlic Potatoes', 40, 60, 120],
    ['Rustic Mashed Potatoes', 40, 60, 120],
    ['Scalloped Potatoes', 40, 60, 120],
    ['Mac & Cheese', 50, 100, 140],
    ['Baked Ziti', 50, 100, 140],
];
?>

<section class="section">
    <div class="container">
        <h1 class="section-title">Starches</h1>

        <p style="max-width: 800px; margin: 2rem auto; text-align: center; font-size: 1.1rem;">
            Round out your catering menu with rice, potatoes, or pasta. Choose a half pan, full shallow pan, or full deep pan for your buffet, pick up, or drop off order.
        </p>

        <div class="grid grid-2" style="margin-top: 3rem;">
            <?php foreach ($starchOptions as [$name, $halfPan, $fullShallowPan, $fullDeepPan]): ?>
            <div class="card">
                <h2 class="card-title"><?php echo htmlspecialchars($name); ?></h2>
                <p>Half Pan - $<?php echo $halfPan; ?><br>Full Shallow Pan - $<?php echo $fullShallowPan; ?><br>Full Deep Pan - $<?php echo $fullDeepPan; ?></p>
            </div>
            <?php endforeach; ?>
        </div>

        <div style="margin-top: 3rem; text-align: center;">
            <a href="<?php echo BASE_URL; ?>quote-request.php" class="btn">Request a Quote</a>
            <a href="<?php echo BASE_URL; ?>contact.php" class="btn btn-secondary" style="margin-left: 1rem;">Contact Us</a>
        </div>
    </div>
</section>

<?php include __DIR__ . '/includes/footer.php'; ?>
