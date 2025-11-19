<?php
require_once __DIR__ . '/includes/config.php';
$pageTitle = 'Reviews';
$pageDescription = 'Read reviews from our satisfied customers. See what people are saying about Wadadli Flare Catering.';
include __DIR__ . '/includes/header.php';
?>

<section class="section">
    <div class="container">
        <h1 class="section-title">Customer Reviews</h1>
        <p style="text-align: center; max-width: 800px; margin: 2rem auto;">
            We're proud of the relationships we've built with our customers. Here's what they have to say about their experience with Wadadli Flare Catering.
        </p>
        
        <div style="margin-top: 3rem;">
            <h2 class="section-subtitle">Facebook Reviews</h2>
            
            <div class="review-card">
                <div class="stars">★★★★★</div>
                <p>"We hosted a large party this past weekend to celebrate our son's graduation. Everything about our experience with Wadadli Flare Catering was exceptional. The food and service was outstanding!"</p>
                <div class="review-author">- Shana Kennedy</div>
                <div class="review-date">July 1, 2024</div>
            </div>
            
            <div class="review-card">
                <div class="stars">★★★★★</div>
                <p>"Chef Jamie crafts mouth watering smoked ribs that you can't stop eating. The spices the Jamie creates are so unique that it keeps you coming back for more. The smoked Mac n cheese is the perfect side along with his other wonderful creations. I would most definitely use Wadadli Flare Catering again and would most definitely recommend to anyone looking for deliciously prepared handcrafted food."</p>
                <div class="review-author">- Fred Rife</div>
                <div class="review-date">August 5, 2023</div>
            </div>
            
            <div class="review-card">
                <div class="stars">★★★★★</div>
                <p>"Chef Jamie Francis has elevated events he catered to the next level. Recommended."</p>
                <div class="review-author">- Jacob C Stephens</div>
                <div class="review-date">August 4, 2023</div>
            </div>
        </div>
        
        <div style="margin-top: 3rem;">
            <h2 class="section-subtitle">Google Reviews</h2>
            
            <div class="review-card">
                <div class="stars">★★★★★</div>
                <p>"I had Wadadli Flare cater a work event on short notice and the food and service were amazing!!!! The employees loved the food and there were absolutely no left overs. The food was very well packed and was hot on delivery. So many people asked about the food so they could have them come back and cater another event! I highly recommend them for any and all catering needs, you will not be disappointed. Thank you so much for coming through for us on such short notice!"</p>
                <div class="review-author">- Taylore Harris</div>
                <div class="review-date">5 months ago</div>
            </div>
            
            <div class="review-card">
                <div class="stars">★★★★★</div>
                <p>"We hosted a large party this past weekend to celebrate our son's high school graduation. Everything about our experience with Wadadli Flare Catering was exceptional. The food was outstanding and we received many compliments on the variety of smoked meats and delicious sides. The service and attention to detail was equally as impressive. Do not hesitate to contact them for your next event. You will not be disappointed and your guests will thank you!"</p>
                <div class="review-author">- Todd Kennedy</div>
                <div class="review-date">a year ago</div>
            </div>
            
            <div class="review-card">
                <div class="stars">★★★★★</div>
                <p>"Wadadli Flare catered our memorial service. Everyone was complimenting us on how delicious the food was and how beautiful it was presented. My niece said it was the best catered meal she'd ever eaten. Highly recommended. The cold grilled veggies are amazing."</p>
                <div class="review-author">- Lisa Betz</div>
                <div class="review-date">2 years ago</div>
            </div>
            
            <div class="review-card">
                <div class="stars">★★★★★</div>
                <p>"I was invited to an event catered by Wadadli Flare Catering today… The baked salmon was tender, flaky, and flavorful. The best salmon I ever had! Airline Chicken Breast was new to me… what a delectable treat! The serving consisted of the wing portion and the breast portion. The wing portion included the drumette that made a convincing challenge to be picked up and eaten, while the breast portion was content to be carved and eaten with a fork. Both were absolutely scrumptious! The meal was rounded out for me by a grilled vegetable medley, with an intriguing variety colors, flavors, and textures. Wadadli Flare Catering receives my highest recommendation!"</p>
                <div class="review-author">- Scott Caley</div>
                <div class="review-date">2 years ago</div>
            </div>
            
            <div class="review-card">
                <div class="stars">★★★★★</div>
                <p>"We just had the pleasure of using Wadadli Flare to cater my sons birthday party. We couldn't be happier. Everything was amazing and our guests are still raving about the food weeks after. We highly recommend and will be using them again for all family gatherings."</p>
                <div class="review-author">- Katie D</div>
                <div class="review-date">2 years ago</div>
            </div>
            
            <div class="review-card">
                <div class="stars">★★★★★</div>
                <p>"The food was delicious n so beautifully prepared n presented in celebrating a friend's life who loved parties n great food..she would have loved it too! What a great tribute..God bless you as u bless others with the joy of great food n a wonderful experience! I will recommend with pleasure!"</p>
                <div class="review-author">- deborah Hargreaves</div>
                <div class="review-date">2 years ago</div>
            </div>
            
            <div class="review-card">
                <div class="stars">★★★★★</div>
                <p>"Literally the best catering I've ever had! The salmon was sublime and I never knew potatoes could taste so good. Wonderful!"</p>
                <div class="review-author">- Anna Carter</div>
                <div class="review-date">2 years ago</div>
            </div>
            
            <div class="review-card">
                <div class="stars">★★★★★</div>
                <p>"I was recently at an event that they catered. Their food is delicious, their service is excellent, and their staff is professional and friendly. I highly recommend them!"</p>
                <div class="review-author">- Gretchen Geyer</div>
                <div class="review-date">2 years ago</div>
            </div>
        </div>
        
        <div style="text-align: center; margin-top: 3rem; padding: 2rem; background-color: var(--light-gray); border-radius: 8px;">
            <h2 style="margin-bottom: 1rem;">Share Your Experience</h2>
            <p style="margin-bottom: 2rem;">
                Have you worked with us? We'd love to hear about your experience! Leave us a review on <a href="<?php echo FACEBOOK_URL; ?>" target="_blank">Facebook</a> or <a href="https://www.google.com/maps" target="_blank">Google</a>.
            </p>
            <a href="<?php echo BASE_URL; ?>quote-request.php" class="btn">Request a Quote</a>
        </div>
    </div>
</section>

<?php include __DIR__ . '/includes/footer.php'; ?>



