<?php
require_once __DIR__ . '/includes/config.php';
$pageTitle = 'Private Events';
$pageDescription = 'Catering services for private events including graduations, funerals, birthdays, and other personal celebrations in Pennsylvania.';
include __DIR__ . '/includes/header.php';
?>

<section class="section">
    <div class="container">
        <h1 class="section-title">Private Events</h1>
        
        <div style="max-width: 800px; margin: 2rem auto; text-align: center;">
            <p style="font-size: 1.1rem;">
                We understand that private events are deeply personal. Whether you're celebrating a milestone or honoring a loved one, we provide thoughtful, respectful catering services tailored to your needs.
            </p>
        </div>
        
        <div class="grid grid-3" style="margin-top: 3rem;">
            <div class="card">
                <h2 class="card-title">Graduations</h2>
                <p>Celebrate your graduate's achievement with a memorable gathering. We offer flexible options from casual BBQs to elegant buffets.</p>
            </div>
            
            <div class="card">
                <h2 class="card-title">Memorial Services</h2>
                <p>We provide respectful, beautifully presented catering for memorial services and celebrations of life. Our team understands the importance of this occasion.</p>
            </div>
            
            <div class="card">
                <h2 class="card-title">Birthdays</h2>
                <p>Make birthdays special with custom menus that reflect the guest of honor's preferences. From intimate gatherings to large parties.</p>
            </div>
            
            <div class="card">
                <h2 class="card-title">Anniversaries</h2>
                <p>Celebrate milestones with elegant catering that honors the occasion.</p>
            </div>
            
            <div class="card">
                <h2 class="card-title">Family Reunions</h2>
                <p>Bring the family together with delicious food that accommodates various tastes and dietary needs.</p>
            </div>
            
            <div class="card">
                <h2 class="card-title">Other Celebrations</h2>
                <p>Baby showers, housewarmings, retirement parties, and more. We cater to all your special moments.</p>
            </div>
        </div>
        
        <div style="margin-top: 3rem; padding: 2rem; background-color: var(--light-gray); border-radius: 8px;">
            <h2 class="section-subtitle">Menu Options</h2>
            <p style="margin-bottom: 1.5rem;">
                We offer flexible menu packages to fit your event and budget. Sample pricing (prices may vary based on selections and market prices):
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
        
        <div style="margin-top: 3rem;">
            <h2 class="section-subtitle">Cuisine Options</h2>
            <p style="text-align: center; margin-bottom: 2rem;">
                Our diverse culinary expertise allows us to create menus featuring:
            </p>
            <div class="grid grid-3">
                <div>
                    <h3 style="color: var(--primary-gold); margin-bottom: 0.5rem;">American Favorites</h3>
                    <p>Classic comfort foods</p>
                </div>
                <div>
                    <h3 style="color: var(--primary-gold); margin-bottom: 0.5rem;">Caribbean Flavors</h3>
                    <p>Jerk, curry, and island specialties</p>
                </div>
                <div>
                    <h3 style="color: var(--primary-gold); margin-bottom: 0.5rem;">BBQ Experience</h3>
                    <p>Smoked meats and traditional sides</p>
                </div>
                <div>
                    <h3 style="color: var(--primary-gold); margin-bottom: 0.5rem;">International Cuisine</h3>
                    <p>French, Italian, Asian options</p>
                </div>
                <div>
                    <h3 style="color: var(--primary-gold); margin-bottom: 0.5rem;">Vegetarian Options</h3>
                    <p>Delicious plant-based choices</p>
                </div>
                <div>
                    <h3 style="color: var(--primary-gold); margin-bottom: 0.5rem;">Custom Creations</h3>
                    <p>We'll work with you to create something special</p>
                </div>
            </div>
        </div>
        
        <div style="margin-top: 3rem; padding: 2rem; background-color: var(--primary-gold); color: var(--white); border-radius: 8px; text-align: center;">
            <h2 style="margin-bottom: 1rem;">Planning a Private Event?</h2>
            <p style="margin-bottom: 2rem; font-size: 1.1rem;">
                Contact us to discuss your event and receive a custom quote. We're here to help make your celebration memorable.
            </p>
            <a href="<?php echo BASE_URL; ?>quote-request.php" class="btn" style="background-color: var(--white); color: var(--primary-gold);">Request a Quote</a>
            <a href="<?php echo BASE_URL; ?>contact.php" class="btn btn-secondary" style="margin-left: 1rem; background-color: rgba(255,255,255,0.2); color: var(--white); border: 2px solid var(--white);">Contact Us</a>
        </div>
    </div>
</section>

<?php include __DIR__ . '/includes/footer.php'; ?>


