<?php
require_once __DIR__ . '/includes/config.php';
$pageTitle = 'Home';
$pageDescription = 'Wadadli Flare Catering - Professional catering services in Pennsylvania. Caribbean, BBQ, American, French, Italian cuisine. Serving Twin Valley, West Chester, and Main Line.';
$bodyClass = 'home-page';
include __DIR__ . '/includes/header.php';
?>

<div class="hero">
    <div class="hero-slideshow">
        <?php
        // Select diverse food images to showcase different cuisines
        $heroImages = [
            '20251110_jerk_chicken_legs.jpg', // Caribbean
            '20250614_smoked_pork_ribs.jpg', // BBQ
            '20251003_smoked_beef_short_ribs.jpg', // BBQ
            '20251003_vegan_curried_veggie_stew.jpg', // Diverse/Vegan
            '20250921_charcuterie_spread_wedding.jpg', // Elegant/Weddings
            '20250819_egg_rolls.jpg', // Diverse
            '20250506_charcuterie_board.jpg', // Elegant
        ];
        foreach ($heroImages as $index => $image):
        ?>
        <div class="hero-slide<?php echo $index === 0 ? ' active' : ''; ?>" style="background-image: url('<?php echo GALLERY_URL . htmlspecialchars($image); ?>');"></div>
        <?php endforeach; ?>
        <div class="hero-overlay"></div>
        <button class="hero-nav hero-nav-prev" aria-label="Previous image">
            <span>&#8249;</span>
        </button>
        <button class="hero-nav hero-nav-next" aria-label="Next image">
            <span>&#8250;</span>
        </button>
    </div>
    <div class="hero-content">
        <h1>Wadadli Flare Catering</h1>
        <p>Bringing diverse culinary excellence to Southeastern Pennsylvania. From American favorites to Caribbean flavors, French, Italian, and more - we create custom menus for your special events.</p>
        <a href="<?php echo BASE_URL; ?>quote-request.php" class="cta-button">Request a Quote</a>
    </div>
</div>

<div class="color-accent-bar">
    <div class="color-accent color-accent-blue"></div>
    <div class="color-accent color-accent-red"></div>
    <div class="color-accent color-accent-green"></div>
</div>

<section class="section">
    <div class="container">
        <h2 class="section-title">Why Choose Wadadli Flare Catering?</h2>
        <div class="grid grid-3">
            <div class="card">
                <h3 class="card-title">34 Years of Experience</h3>
                <p>Chef Jamie brings decades of culinary expertise from 5-star resorts, including the Ritz Carlton, to every event.</p>
            </div>
            <div class="card">
                <h3 class="card-title">Diverse Cuisines</h3>
                <p>We specialize in American, French, Italian, Asian, and Caribbean cuisine. Custom menus tailored to your taste.</p>
            </div>
            <div class="card">
                <h3 class="card-title">Flexible Service</h3>
                <p>Pick up, drop off, or full service options. We work with your venue and your needs to make your event perfect.</p>
            </div>
        </div>
    </div>
</section>

<section class="section" style="background-color: var(--light-gray);">
    <div class="container">
        <h2 class="section-title">Our Services</h2>
        <div class="grid grid-2">
            <div class="card">
                <h3 class="card-title">Weddings</h3>
                <p>Elegant charcuterie stations, custom buffets, and beautifully presented dishes to make your special day unforgettable.</p>
                <a href="<?php echo BASE_URL; ?>weddings.php" class="btn">Learn More</a>
            </div>
            <div class="card">
                <h3 class="card-title">Corporate Events</h3>
                <p>Professional catering for meetings, holiday parties, and company events. Impress your clients and colleagues.</p>
                <a href="<?php echo BASE_URL; ?>corporate.php" class="btn">Learn More</a>
            </div>
            <div class="card">
                <h3 class="card-title">Private Events</h3>
                <p>Graduations, birthdays, memorials, and more. We handle events of all sizes with care and attention to detail.</p>
                <a href="<?php echo BASE_URL; ?>private-events.php" class="btn">Learn More</a>
            </div>
            <div class="card">
                <h3 class="card-title">BBQ & Caribbean Experiences</h3>
                <p>Smoked meats, jerk specialties, and authentic Caribbean flavors. Perfect for summer BBQs and special occasions.</p>
                <div class="button-group">
                    <a href="<?php echo BASE_URL; ?>bbq-experience.php" class="btn">BBQ Experience</a>
                    <a href="<?php echo BASE_URL; ?>caribbean-experience.php" class="btn">Caribbean Experience</a>
                </div>
            </div>
        </div>
    </div>
</section>

<section class="section">
    <div class="container">
        <h2 class="section-title">What Our Customers Say</h2>
        <div class="grid grid-2">
            <div class="review-card">
                <div class="stars">★★★★★</div>
                <p>"We hosted a large party this past weekend to celebrate our son's graduation. Everything about our experience with Wadadli Flare Catering was exceptional. The food and service was outstanding!"</p>
                <div class="review-author">- Shana Kennedy</div>
            </div>
            <div class="review-card">
                <div class="stars">★★★★★</div>
                <p>"Chef Jamie crafts mouth watering fall off the bone smoked ribs that you can't stop eating. The spices the Jamie creates are so unique that it keeps you coming back for more."</p>
                <div class="review-author">- Fred Rife</div>
            </div>
        </div>
        <div style="text-align: center; margin-top: 2rem;">
            <a href="<?php echo BASE_URL; ?>reviews.php" class="btn">Read More Reviews</a>
        </div>
    </div>
</section>

<section class="section" style="background-color: var(--light-gray);">
    <div class="container">
        <h2 class="section-title">Service Areas</h2>
        <p style="text-align: center; font-size: 1.1rem; max-width: 800px; margin: 0 auto;">
            Based in Elverson, Pennsylvania, we proudly serve Twin Valley, West Chester, the Main Line west of Philadelphia, and surrounding areas. Let us bring our culinary expertise to your next event.
        </p>
        <div style="text-align: center; margin-top: 2rem;">
            <p style="font-style: italic; color: var(--primary-gold); font-size: 1.2rem;">
                "Let us take some work out of your summer BBQ so you have more time to chill."
            </p>
            <p style="margin-top: 1rem;">
                If you don't see something you'd like on the menu, please feel free to ask. We love our customers, so feel free to contact us at <a href="tel:<?php echo str_replace([' ', '(', ')', '-'], '', CONTACT_PHONE); ?>"><?php echo CONTACT_PHONE; ?></a>.
            </p>
        </div>
    </div>
</section>

<?php include __DIR__ . '/includes/footer.php'; ?>

