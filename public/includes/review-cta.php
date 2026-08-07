<?php
/**
 * "Leave us a review" call to action.
 *
 * Optional variables, set before the include:
 *   $reviewCtaId      - element id, so the block can be linked to (use once per page)
 *   $reviewCtaHeading - heading text, defaults to "Leave Us a Review"
 */
$reviewCtaHeading = $reviewCtaHeading ?? 'Leave Us a Review';
?>
<div class="review-cta"<?php echo !empty($reviewCtaId) ? ' id="' . htmlspecialchars($reviewCtaId) . '"' : ''; ?>>
    <h2><?php echo htmlspecialchars($reviewCtaHeading); ?></h2>
    <p>Have we catered your event? Telling people how it went takes about a minute and it is the main way new customers find us.</p>
    <div class="review-cta-buttons">
        <a href="<?php echo GOOGLE_REVIEW_URL; ?>" class="btn btn-google" target="_blank" rel="noopener noreferrer">★ Review us on Google</a>
        <a href="<?php echo FACEBOOK_REVIEW_URL; ?>" class="btn btn-facebook" target="_blank" rel="noopener noreferrer">★ Review us on Facebook</a>
    </div>
    <p class="review-cta-note">
        The Google button opens the review form straight away. On Facebook, open the <strong>Reviews</strong> tab on our page and choose <strong>Yes</strong> to recommend us.
    </p>
</div>
<?php
// Don't leak these into a second include on the same page.
unset($reviewCtaId, $reviewCtaHeading);
