<?php
require_once __DIR__ . '/includes/config.php';
$pageTitle = 'BBQ Experience';
$pageDescription = 'Authentic BBQ catering with smoked meats, traditional sides, and classic barbecue flavors. Perfect for summer BBQs and special events.';
include __DIR__ . '/includes/header.php';
?>

<section class="section">
    <div class="container">
        <h1 class="section-title">BBQ Experience</h1>
        
        <div style="max-width: 800px; margin: 2rem auto; text-align: center;">
            <p style="font-size: 1.1rem;">
                All our meats are slow-smoked to perfection, creating that authentic BBQ flavor your guests will remember. Perfect for summer BBQs, family gatherings, and any occasion that calls for great barbecue.
            </p>
        </div>
        
        <div style="margin-top: 3rem;">
            <h2 class="section-subtitle">Smoked Meats</h2>
            <div class="grid grid-2">
                <div class="menu-section">
                    <div class="menu-item">
                        <div class="menu-item-name">BBQ Chicken</div>
                        <div class="menu-item-description">Tender, juicy chicken slow-smoked and finished with our signature BBQ sauce</div>
                    </div>
                    <div class="menu-item">
                        <div class="menu-item-name">Pork Ribs</div>
                        <div class="menu-item-description">Fall-off-the-bone tender ribs, perfectly seasoned and smoked</div>
                    </div>
                    <div class="menu-item">
                        <div class="menu-item-name">Beef Ribs</div>
                        <div class="menu-item-description">Rich, flavorful beef ribs with a perfect smoke ring</div>
                    </div>
                    <div class="menu-item">
                        <div class="menu-item-name">Short Ribs</div>
                        <div class="menu-item-description">Tender, marbled short ribs that melt in your mouth</div>
                    </div>
                    <div class="menu-item">
                        <div class="menu-item-name">Brisket</div>
                        <div class="menu-item-description">Slow-smoked brisket, sliced thin and full of flavor</div>
                    </div>
                    <div class="menu-item">
                        <div class="menu-item-name">Pulled Pork</div>
                        <div class="menu-item-description">Hand-pulled pork shoulder, tender and juicy</div>
                    </div>
                </div>
            </div>
        </div>
        
        <div style="margin-top: 3rem;">
            <h2 class="section-subtitle">BBQ Sides</h2>
            <div class="grid grid-2">
                <div class="menu-section">
                    <div class="menu-item">
                        <div class="menu-item-name">Mac & Cheese</div>
                        <div class="menu-item-description">Creamy, cheesy macaroni - the perfect BBQ side</div>
                    </div>
                    <div class="menu-item">
                        <div class="menu-item-name">Collard Greens</div>
                        <div class="menu-item-description">Traditional Southern-style collard greens</div>
                    </div>
                    <div class="menu-item">
                        <div class="menu-item-name">Rice & Beans</div>
                        <div class="menu-item-description">Hearty and flavorful</div>
                    </div>
                    <div class="menu-item">
                        <div class="menu-item-name">Jasmine Rice</div>
                        <div class="menu-item-description">Fragrant, fluffy jasmine rice</div>
                    </div>
                    <div class="menu-item">
                        <div class="menu-item-name">BBQ Beans</div>
                        <div class="menu-item-description">Slow-cooked beans with BBQ flavor</div>
                    </div>
                    <div class="menu-item">
                        <div class="menu-item-name">And More</div>
                        <div class="menu-item-description">Ask about our other delicious side options</div>
                    </div>
                </div>
            </div>
        </div>
        
        <div style="margin-top: 3rem; padding: 2rem; background-color: var(--light-gray); border-radius: 8px;">
            <h2 class="section-subtitle">Pricing Information</h2>
            <p style="margin-bottom: 1.5rem;">
                Our BBQ Experience is available for pick up, drop off, or full service. Pricing varies based on quantities and selections. Please <a href="<?php echo BASE_URL; ?>contact.php">contact us</a> for specific pricing or see our <a href="<?php echo BASE_URL; ?>pickup-dropoff.php">Pick Up & Drop Off</a> page for more details.
            </p>
            <p style="font-style: italic; color: var(--medium-gray);">
                "Let us take some work out of your summer BBQ so you have more time to chill."
            </p>
        </div>
        
        <div style="margin-top: 3rem; text-align: center;">
            <a href="<?php echo BASE_URL; ?>quote-request.php" class="btn">Request a Quote</a>
            <a href="<?php echo BASE_URL; ?>pickup-dropoff.php" class="btn btn-secondary" style="margin-left: 1rem;">Pick Up & Drop Off Info</a>
        </div>
    </div>
</section>

<?php include __DIR__ . '/includes/footer.php'; ?>


