<?php
require_once __DIR__ . '/includes/config.php';
require_once __DIR__ . '/../private/includes/pan_menu.php';
$pageTitle = 'Starches';
$pageDescription = 'Catering starch options from Wadadli Flare: rice, potatoes, mac and cheese, and baked ziti, with half pan, full shallow pan, and full deep pan pricing.';
include __DIR__ . '/includes/header.php';
?>

<section class="section">
    <div class="container">
        <h1 class="section-title">Starches</h1>

        <p style="max-width: 800px; margin: 2rem auto; text-align: center; font-size: 1.1rem;">
            Round out your catering menu with rice, potatoes, or pasta. Choose a half pan, full shallow pan, or full deep pan for your buffet, pick up, or drop off order.
        </p>

        <div class="grid grid-2" style="margin-top: 3rem;">
            <?php echo render_pan_menu('starches'); ?>
        </div>

        <div style="margin-top: 3rem; text-align: center;">
            <a href="<?php echo BASE_URL; ?>quote-request.php" class="btn">Request a Quote</a>
            <a href="<?php echo BASE_URL; ?>contact.php" class="btn btn-secondary" style="margin-left: 1rem;">Contact Us</a>
        </div>
    </div>
</section>

<?php include __DIR__ . '/includes/footer.php'; ?>
