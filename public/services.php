<?php
require_once __DIR__ . '/includes/config.php';
$pageTitle = 'Services';
$pageDescription = 'Wadadli Flare Catering offers partial catering services including pick up, drop off, and full service options for weddings, corporate events, and private parties.';
include __DIR__ . '/includes/header.php';
?>

<section class="section">
    <div class="container">
        <h1 class="section-title">Our Catering Services</h1>
        
        <div style="max-width: 800px; margin: 2rem auto; text-align: center;">
            <p style="font-size: 1.1rem; margin-bottom: 2rem;">
                Wadadli Flare Catering offers <strong>partial catering services</strong> - we bring the food and can provide service, but we do not offer full labor for large events that require extensive staffing. We work flexibly with your needs and can collaborate with other workers at your event if needed.
            </p>
        </div>
        
        <div class="grid grid-2" style="margin-top: 3rem;">
            <div class="card">
                <h2 class="card-title">Pick Up</h2>
                <p>Order your catering in advance and pick it up at our location. Perfect for smaller events or when you want to handle the setup yourself.</p>
            </div>
            
            <div class="card">
                <h2 class="card-title">Drop Off</h2>
                <p>We'll deliver your order to your location. Food is ready to serve, and you handle the setup and service.</p>
            </div>
            
            <div class="card">
                <h2 class="card-title">Drop Off with Set-Up</h2>
                <p>We deliver and set up your buffet. You handle the service during the event.</p>
            </div>
            
            <div class="card">
                <h2 class="card-title">Full Service</h2>
                <p>We deliver, set up, serve during the event, and clean up the buffet. A 10% service fee is added for serving and breakdown of the buffet.</p>
            </div>
        </div>
        
        <div style="margin-top: 3rem; padding: 2rem; background-color: var(--light-gray); border-radius: 8px;">
            <h2 class="section-subtitle">Service Types</h2>
            <div class="grid grid-3" style="margin-top: 2rem;">
                <div>
                    <h3 style="color: var(--primary-gold); margin-bottom: 1rem;">Weddings</h3>
                    <p>Elegant charcuterie stations, custom buffets, and beautifully presented dishes for your special day.</p>
                    <a href="<?php echo BASE_URL; ?>weddings.php" class="btn" style="margin-top: 1rem;">Learn More</a>
                </div>
                
                <div>
                    <h3 style="color: var(--primary-gold); margin-bottom: 1rem;">Corporate Events</h3>
                    <p>Professional catering for meetings, holiday parties, and company events.</p>
                    <a href="<?php echo BASE_URL; ?>corporate.php" class="btn" style="margin-top: 1rem;">Learn More</a>
                </div>
                
                <div>
                    <h3 style="color: var(--primary-gold); margin-bottom: 1rem;">Private Events</h3>
                    <p>Graduations, funerals, birthdays, and other personal celebrations.</p>
                    <a href="<?php echo BASE_URL; ?>private-events.php" class="btn" style="margin-top: 1rem;">Learn More</a>
                </div>
            </div>
        </div>
        
        <div style="margin-top: 3rem; text-align: center;">
            <h2 class="section-subtitle">Customize Your Menu</h2>
            <p style="max-width: 800px; margin: 1rem auto;">
                We specialize in custom menus. If you don't see something you'd like, please feel free to ask. Our menu options include:
            </p>
            <ul style="list-style: none; max-width: 600px; margin: 2rem auto; text-align: left;">
                <li style="padding: 0.5rem 0;">✓ A salad, Rolls & Butter & Dressing</li>
                <li style="padding: 0.5rem 0;">✓ One or Two Protein Options</li>
                <li style="padding: 0.5rem 0;">✓ One or Two Starch Options</li>
                <li style="padding: 0.5rem 0;">✓ One or Two Vegetable Options</li>
            </ul>
        </div>
        
        <div style="text-align: center; margin-top: 3rem;">
            <a href="<?php echo BASE_URL; ?>quote-request.php" class="btn">Request a Quote</a>
        </div>
    </div>
</section>

<?php include __DIR__ . '/includes/footer.php'; ?>


