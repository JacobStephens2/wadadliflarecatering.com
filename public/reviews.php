<?php
require_once __DIR__ . '/includes/config.php';
require_once PRIVATE_PATH . '/includes/google_reviews.php';

$pageTitle = 'Reviews';
$pageDescription = 'Read reviews from our satisfied customers. See what people are saying about Wadadli Flare Catering.';

$googleReviews = get_reviews('google');
$facebookReviews = get_reviews('facebook');
$googleSummary = get_google_rating_summary();

include __DIR__ . '/includes/header.php';
?>

<section class="section">
    <div class="container">
        <h1 class="section-title">Customer Reviews</h1>
        <p style="text-align: center; max-width: 800px; margin: 2rem auto;">
            We're proud of the relationships we've built with our customers. Here's what they have to say about their experience with Wadadli Flare Catering.
        </p>

        <?php
        $reviewCtaId = 'leave-a-review';
        include __DIR__ . '/includes/review-cta.php';
        ?>

        <?php if (!empty($googleReviews)): ?>
        <div style="margin-top: 3rem;">
            <h2 class="section-subtitle">Google Reviews</h2>
            <?php if ($googleSummary): ?>
                <p class="review-summary">
                    <span class="stars"><?php echo str_repeat('★', (int) round($googleSummary['rating'])); ?></span>
                    <strong><?php echo number_format($googleSummary['rating'], 1); ?></strong>
                    out of 5, from <?php echo number_format($googleSummary['count']); ?> Google ratings.
                    <a href="<?php echo GOOGLE_MAPS_URL; ?>" target="_blank" rel="noopener noreferrer">See them all on Google</a>.
                </p>
            <?php endif; ?>

            <?php foreach ($googleReviews as $review): ?>
                <?php include __DIR__ . '/includes/review-card.php'; ?>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>

        <?php if (!empty($facebookReviews)): ?>
        <div style="margin-top: 3rem;">
            <h2 class="section-subtitle">Facebook Reviews</h2>
            <?php foreach ($facebookReviews as $review): ?>
                <?php include __DIR__ . '/includes/review-card.php'; ?>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>

        <?php
        $reviewCtaHeading = 'Share Your Experience';
        include __DIR__ . '/includes/review-cta.php';
        ?>

        <p style="text-align: center; margin-top: 2rem;">
            Planning something of your own? <a href="<?php echo BASE_URL; ?>quote-request.php">Request a quote</a>.
        </p>
    </div>
</section>

<?php include __DIR__ . '/includes/footer.php'; ?>
