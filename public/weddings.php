<?php
require_once __DIR__ . '/includes/config.php';
$pageTitle = 'Wedding Catering';
$pageDescription = 'Elegant wedding catering services with charcuterie stations, custom buffets, and beautifully presented dishes for your special day.';
include __DIR__ . '/includes/header.php';
?>

<section class="section">
    <div class="container">
        <h1 class="section-title">Wedding Catering</h1>
        
        <div style="max-width: 800px; margin: 2rem auto; text-align: center;">
            <p style="font-size: 1.1rem;">
                Make your special day unforgettable with elegant catering from Wadadli Flare. We create beautiful, delicious spreads that will impress your guests and let you focus on celebrating.
            </p>
        </div>
        
        <div class="grid grid-2" style="margin-top: 3rem;">
            <div class="card">
                <h2 class="card-title">Charcuterie Stations</h2>
                <p>Beautifully arranged selection of:</p>
                <ul style="margin-top: 1rem; padding-left: 1.5rem;">
                    <li>Artisan Cheeses & Meats</li>
                    <li>Breads & Crackers</li>
                    <li>Preserves & Jams</li>
                    <li>Dried Fruits</li>
                    <li>Hummus and Crisps</li>
                </ul>
            </div>
            
            <div class="card">
                <h2 class="card-title">Grilled Vegetable Platters</h2>
                <p>Fresh, colorful, and beautifully presented grilled vegetables that add elegance to any wedding buffet.</p>
            </div>
        </div>
        
        <div style="margin-top: 3rem; padding: 2rem; background-color: var(--light-gray); border-radius: 8px;">
            <h2 class="section-subtitle">Custom Wedding Menus</h2>
            <p style="margin-bottom: 1.5rem;">
                We specialize in creating custom menus tailored to your wedding style and preferences. Our diverse culinary expertise allows us to offer:
            </p>
            <div class="grid grid-3">
                <div>
                    <h3 style="color: var(--primary-gold); margin-bottom: 0.5rem;">American Favorites</h3>
                    <p>Classic dishes that everyone loves</p>
                </div>
                <div>
                    <h3 style="color: var(--primary-gold); margin-bottom: 0.5rem;">French Cuisine</h3>
                    <p>Elegant and sophisticated options</p>
                </div>
                <div>
                    <h3 style="color: var(--primary-gold); margin-bottom: 0.5rem;">Italian Specialties</h3>
                    <p>Timeless favorites for any celebration</p>
                </div>
                <div>
                    <h3 style="color: var(--primary-gold); margin-bottom: 0.5rem;">Caribbean Flavors</h3>
                    <p>Unique and vibrant options</p>
                </div>
                <div>
                    <h3 style="color: var(--primary-gold); margin-bottom: 0.5rem;">Asian Fusion</h3>
                    <p>Modern and exciting choices</p>
                </div>
                <div>
                    <h3 style="color: var(--primary-gold); margin-bottom: 0.5rem;">Custom Creations</h3>
                    <p>We'll work with you to create something unique</p>
                </div>
            </div>
        </div>
        
        <div style="margin-top: 3rem;">
            <h2 class="section-subtitle">Menu Options</h2>
            <p style="text-align: center; margin-bottom: 2rem;">
                We offer flexible menu packages. Sample pricing (prices may vary based on selections and market prices):
            </p>
            <div class="grid grid-2" style="max-width: 900px; margin: 0 auto;">
                <div class="card">
                    <h3 class="card-title">$20 - $22 per person</h3>
                    <ul style="margin-top: 1rem; padding-left: 1.5rem;">
                        <li>One Salad</li>
                        <li>One Vegetable</li>
                        <li>Two Starches</li>
                        <li>One Protein</li>
                    </ul>
                </div>
                <div class="card">
                    <h3 class="card-title">$25 - $27 per person</h3>
                    <ul style="margin-top: 1rem; padding-left: 1.5rem;">
                        <li>One Salad</li>
                        <li>Two Vegetables</li>
                        <li>Two Starches</li>
                        <li>Two Proteins</li>
                    </ul>
                </div>
            </div>
            <p style="text-align: center; margin-top: 2rem; font-style: italic; color: var(--medium-gray);">
                * Fish, Steak and Veal may cost more depending on market price<br>
                * Large events may receive lower pricing per person
            </p>
        </div>
        
        <div style="margin-top: 3rem; padding: 2rem; background-color: var(--primary-gold); color: var(--white); border-radius: 8px; text-align: center;">
            <h2 style="margin-bottom: 1rem;">Ready to Plan Your Wedding Menu?</h2>
            <p style="margin-bottom: 2rem; font-size: 1.1rem;">
                Contact us to discuss your vision and receive a custom quote for your special day.
            </p>
            <a href="<?php echo BASE_URL; ?>quote-request.php" class="btn" style="background-color: var(--white); color: var(--primary-gold);">Request a Quote</a>
            <a href="<?php echo BASE_URL; ?>contact.php" class="btn btn-secondary" style="margin-left: 1rem; background-color: rgba(255,255,255,0.2); color: var(--white); border: 2px solid var(--white);">Contact Us</a>
        </div>
    </div>
</section>

<?php include __DIR__ . '/includes/footer.php'; ?>


