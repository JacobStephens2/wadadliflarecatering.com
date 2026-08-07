<?php
/**
 * One review card. Expects $review in the shape produced by
 * private/includes/google_reviews.php.
 *
 * Google's Places API terms require the reviewer's attribution to be shown, so
 * the author name links back to their Google profile when the API supplies one.
 */
$rating = max(0, min(5, (int) ($review['rating'] ?? 5)));
$reviewDate = format_review_date($review);
$authorUrl = $review['author_url'] ?? '';
$photoUrl = $review['photo_url'] ?? '';
?>
<div class="review-card">
    <div class="stars" aria-label="<?php echo $rating; ?> out of 5 stars"><?php echo str_repeat('★', $rating) . str_repeat('☆', 5 - $rating); ?></div>
    <p><?php echo nl2br(htmlspecialchars($review['text'] ?? '')); ?></p>
    <div class="review-byline">
        <?php if ($photoUrl !== ''): ?>
            <img src="<?php echo htmlspecialchars($photoUrl); ?>" alt="" class="review-avatar" loading="lazy" width="36" height="36" referrerpolicy="no-referrer">
        <?php endif; ?>
        <div>
            <div class="review-author">
                <?php if ($authorUrl !== ''): ?>
                    <a href="<?php echo htmlspecialchars($authorUrl); ?>" target="_blank" rel="noopener noreferrer nofollow"><?php echo htmlspecialchars($review['author'] ?? ''); ?></a>
                <?php else: ?>
                    <?php echo htmlspecialchars($review['author'] ?? ''); ?>
                <?php endif; ?>
            </div>
            <?php if ($reviewDate !== ''): ?>
                <div class="review-date"><?php echo htmlspecialchars($reviewDate); ?></div>
            <?php endif; ?>
        </div>
    </div>
</div>
