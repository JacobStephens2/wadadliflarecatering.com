<?php
require_once __DIR__ . '/includes/config.php';
$pageTitle = 'Corporate Catering';
$pageDescription = 'Professional corporate catering services for meetings, holiday parties, and company events in Pennsylvania.';
include __DIR__ . '/includes/header.php';
?>

<section class="section">
    <div class="container">
        <h1 class="section-title">Corporate Catering</h1>
        
        <div style="max-width: 800px; margin: 2rem auto; text-align: center;">
            <p style="font-size: 1.1rem;">
                Impress your clients and colleagues with professional catering from Wadadli Flare. We provide reliable, high-quality service for all your corporate needs.
            </p>
        </div>
        
        <div class="grid grid-2" style="margin-top: 3rem;">
            <div class="card">
                <h2 class="card-title">Company Holiday Parties</h2>
                <p>Make your holiday celebration memorable with elegant charcuterie stations and custom buffet menus that will delight your team.</p>
            </div>
            
            <div class="card">
                <h2 class="card-title">Meetings & Conferences</h2>
                <p>Keep your team focused and energized with professional catering that fits your schedule and budget.</p>
            </div>
            
            <div class="card">
                <h2 class="card-title">Company Events</h2>
                <p>From team building to client appreciation events, we provide the perfect catering solution.</p>
            </div>
            
            <div class="card">
                <h2 class="card-title">Charcuterie Stations</h2>
                <p>Elegant displays of artisan cheeses, meats, breads, crackers, preserves, dried fruits, hummus, and more. Perfect for networking events.</p>
            </div>
        </div>
        
        <div style="margin-top: 3rem; padding: 2rem; background-color: var(--light-gray); border-radius: 8px;">
            <h2 class="section-subtitle">Custom Buffet Menus</h2>
            <p style="margin-bottom: 1.5rem;">
                We work with you to create menus that fit your corporate culture and budget. Our diverse culinary expertise includes:
            </p>
            <div class="grid grid-3">
                <div>
                    <h3 style="color: var(--primary-gold); margin-bottom: 0.5rem;">American Classics</h3>
                    <p>Familiar favorites that appeal to everyone</p>
                </div>
                <div>
                    <h3 style="color: var(--primary-gold); margin-bottom: 0.5rem;">International Options</h3>
                    <p>French, Italian, Asian, and Caribbean selections</p>
                </div>
                <div>
                    <h3 style="color: var(--primary-gold); margin-bottom: 0.5rem;">Healthy Choices</h3>
                    <p>Fresh salads, grilled vegetables, and lighter options</p>
                </div>
            </div>
        </div>
        
        <div style="margin-top: 3rem;">
            <h2 class="section-subtitle">Flexible Service Options</h2>
            <div class="grid grid-2" style="max-width: 900px; margin: 2rem auto;">
                <div class="card">
                    <h3 class="card-title">Pick Up</h3>
                    <p>Order in advance and pick up at our location. Perfect for smaller meetings.</p>
                </div>
                <div class="card">
                    <h3 class="card-title">Drop Off</h3>
                    <p>We deliver to your office. Food is ready to serve.</p>
                </div>
                <div class="card">
                    <h3 class="card-title">Set Up Service</h3>
                    <p>We deliver and set up your buffet. You handle service.</p>
                </div>
                <div class="card">
                    <h3 class="card-title">Full Service</h3>
                    <p>We deliver, set up, serve, and clean up. (10% service fee applies)</p>
                </div>
            </div>
        </div>
        
        <div style="margin-top: 3rem; padding: 2rem; background-color: var(--primary-gold); color: var(--white); border-radius: 8px; text-align: center;">
            <h2 style="margin-bottom: 1rem;">Ready to Cater Your Next Corporate Event?</h2>
            <p style="margin-bottom: 2rem; font-size: 1.1rem;">
                Contact us to discuss your corporate catering needs and receive a custom quote.
            </p>
            <a href="<?php echo BASE_URL; ?>quote-request.php" class="btn" style="background-color: var(--white); color: var(--primary-gold);">Request a Quote</a>
            <a href="<?php echo BASE_URL; ?>contact.php" class="btn btn-secondary" style="margin-left: 1rem; background-color: rgba(255,255,255,0.2); color: var(--white); border: 2px solid var(--white);">Contact Us</a>
        </div>
    </div>
</section>

<?php include __DIR__ . '/includes/footer.php'; ?>


