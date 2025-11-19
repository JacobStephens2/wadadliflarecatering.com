<?php
require_once __DIR__ . '/includes/config.php';
$pageTitle = 'Pick Up & Drop Off';
$pageDescription = 'Convenient pick up and drop off catering services. Caribbean Experience, BBQ Experience, and International Buffet items available.';
include __DIR__ . '/includes/header.php';
?>

<section class="section">
    <div class="container">
        <h1 class="section-title">Pick Up & Drop Off</h1>
        
        <div style="max-width: 800px; margin: 2rem auto; text-align: center;">
            <p style="font-size: 1.1rem;">
                Convenient catering options for when you want great food without the Partial Service. Perfect for smaller events, family gatherings, or when you prefer to handle the setup yourself.
            </p>
        </div>
        
        <div class="grid grid-2" style="margin-top: 3rem;">
            <div class="card">
                <h2 class="card-title">Pick Up Service</h2>
                <p>Order in advance and pick up your catering at our location. Food is ready to serve when you arrive. Perfect for smaller gatherings or when you want maximum flexibility.</p>
                <ul style="margin-top: 1rem; padding-left: 1.5rem;">
                    <li>Order ahead by phone or email</li>
                    <li>Pick up at scheduled time</li>
                    <li>Food ready to serve</li>
                    <li>Great for smaller events</li>
                </ul>
            </div>
            
            <div class="card">
                <h2 class="card-title">Drop Off Service</h2>
                <p>We deliver your order directly to your location. Food arrives ready to serve, and you handle the setup. Ideal for events at your home or venue.</p>
                <ul style="margin-top: 1rem; padding-left: 1.5rem;">
                    <li>We deliver to your location</li>
                    <li>Food ready to serve</li>
                    <li>You handle setup</li>
                    <li>Flexible delivery times</li>
                </ul>
            </div>
        </div>
        
        <div style="margin-top: 3rem;">
            <h2 class="section-subtitle">Available Options</h2>
            <div class="grid grid-3">
                <div class="card">
                    <h3 class="card-title">Caribbean Experience</h3>
                    <p>Jerk specialties, curry dishes, oxtail, and traditional Caribbean sides. Perfect for adding island flavor to your event.</p>
                    <a href="<?php echo BASE_URL; ?>caribbean-experience.php" class="btn" style="margin-top: 1rem;">View Menu</a>
                </div>
                
                <div class="card">
                    <h3 class="card-title">BBQ Experience</h3>
                    <p>Slow-smoked meats, traditional BBQ sides, and all the classics. Great for summer gatherings and casual events.</p>
                    <a href="<?php echo BASE_URL; ?>bbq-experience.php" class="btn" style="margin-top: 1rem;">View Menu</a>
                </div>
                
                <div class="card">
                    <h3 class="card-title">International Buffet Items</h3>
                    <p>American, French, Italian, Asian, and other international options. Mix and match to create your perfect menu.</p>
                    <a href="<?php echo BASE_URL; ?>services.php" class="btn" style="margin-top: 1rem;">Learn More</a>
                </div>
            </div>
        </div>
        
        <div style="margin-top: 3rem; padding: 2rem; background-color: var(--light-gray); border-radius: 8px;">
            <h2 class="section-subtitle">Pricing Information</h2>
            <p style="margin-bottom: 1.5rem;">
                Pricing is based on quantities and selections. We offer full pans, half pans, and can accommodate various serving sizes. Contact us for specific pricing based on your needs.
            </p>
            <div class="grid grid-2" style="max-width: 800px; margin: 0 auto;">
                <div>
                    <h3 style="color: var(--primary-gold-text); margin-bottom: 1rem;">Serving Estimates</h3>
                    <ul style="padding-left: 1.5rem;">
                        <li>Full half pan: 10-12 people</li>
                        <li>Full shallow pan: 18-20 people</li>
                        <li>Full deep pan: 30-35 people</li>
                    </ul>
                </div>
                <div>
                    <h3 style="color: var(--primary-gold-text); margin-bottom: 1rem;">Protein Serving</h3>
                    <ul style="padding-left: 1.5rem;">
                        <li>Single protein: 6-8 oz per person</li>
                        <li>Multiple proteins: 4-5 oz per person each</li>
                    </ul>
                </div>
            </div>
            <p style="text-align: center; margin-top: 2rem; font-style: italic; color: var(--medium-gray);">
                Prices may vary based on market prices and specific selections. Contact us for a detailed quote.
            </p>
        </div>
        
        <div style="margin-top: 3rem; text-align: center;">
            <a href="<?php echo BASE_URL; ?>quote-request.php" class="btn">Request a Quote</a>
            <a href="<?php echo BASE_URL; ?>contact.php" class="btn btn-secondary" style="margin-left: 1rem;">Contact Us</a>
        </div>
    </div>
</section>

<?php include __DIR__ . '/includes/footer.php'; ?>


