<?php
require_once __DIR__ . '/includes/config.php';
$pageTitle = 'Caribbean Experience';
$pageDescription = 'Authentic Caribbean catering with jerk specialties, curry dishes, and traditional island flavors.';
include __DIR__ . '/includes/header.php';
?>

<section class="section">
    <div class="container">
        <h1 class="section-title">Caribbean Experience</h1>
        
        <div style="max-width: 800px; margin: 2rem auto; text-align: center;">
            <p style="font-size: 1.1rem;">
                Experience the vibrant flavors of the Caribbean with our authentic jerk specialties, curry dishes, and traditional island cuisine. While we offer diverse cuisines, our Caribbean roots shine through in these signature dishes.
            </p>
        </div>
        
        <div style="margin-top: 3rem;">
            <h2 class="section-subtitle">Caribbean Entrees</h2>
            <div class="grid grid-2">
                <div class="menu-section">
                    <div class="menu-item">
                        <div class="menu-item-name">Jerk Chicken</div>
                        <div class="menu-item-description">Authentic jerk-marinated chicken with traditional spices</div>
                    </div>
                    <div class="menu-item">
                        <div class="menu-item-name">Jerk Pork</div>
                        <div class="menu-item-description">Tender pork with our signature jerk seasoning</div>
                    </div>
                    <div class="menu-item">
                        <div class="menu-item-name">Jerk Ribs</div>
                        <div class="menu-item-description">Fall-off-the-bone ribs with Caribbean jerk flavor</div>
                    </div>
                    <div class="menu-item">
                        <div class="menu-item-name">Curry Goat</div>
                        <div class="menu-item-description">Traditional Caribbean curry with tender goat meat</div>
                    </div>
                    <div class="menu-item">
                        <div class="menu-item-name">Oxtail</div>
                        <div class="menu-item-description">Slow-cooked oxtail in rich, flavorful sauce</div>
                    </div>
                    <div class="menu-item">
                        <div class="menu-item-name">Wadadli Tilapia</div>
                        <div class="menu-item-description">Fresh tilapia with onions, peppers, herbs, tomatoes, olive oil & vinegar</div>
                    </div>
                    <div class="menu-item">
                        <div class="menu-item-name">Jerk Chicken Egg Rolls</div>
                        <div class="menu-item-description">Unique fusion appetizer with jerk chicken</div>
                    </div>
                </div>
            </div>
        </div>
        
        <div style="margin-top: 3rem;">
            <h2 class="section-subtitle">Caribbean Sides</h2>
            <div class="grid grid-2">
                <div class="menu-section">
                    <div class="menu-item">
                        <div class="menu-item-name">Rice & Beans</div>
                        <div class="menu-item-description">Traditional Caribbean rice and beans</div>
                    </div>
                    <div class="menu-item">
                        <div class="menu-item-name">Mac & Cheese</div>
                        <div class="menu-item-description">Creamy Caribbean-style macaroni and cheese</div>
                    </div>
                    <div class="menu-item">
                        <div class="menu-item-name">Fried Plantains</div>
                        <div class="menu-item-description">Sweet, caramelized plantains</div>
                    </div>
                    <div class="menu-item">
                        <div class="menu-item-name">Sautéed Cabbage & Carrot</div>
                        <div class="menu-item-description">Fresh, flavorful vegetables</div>
                    </div>
                    <div class="menu-item">
                        <div class="menu-item-name">And More</div>
                        <div class="menu-item-description">Ask about our other Caribbean side dishes</div>
                    </div>
                </div>
            </div>
        </div>
        
        <div style="margin-top: 3rem; padding: 2rem; background-color: var(--light-gray); border-radius: 8px;">
            <h2 class="section-subtitle">Pricing Information</h2>
            <p style="margin-bottom: 1.5rem;">
                Our Caribbean Experience is available for pick up, drop off, or full service. Pricing varies based on quantities and selections. Please <a href="<?php echo BASE_URL; ?>contact.php">contact us</a> for specific pricing or see our <a href="<?php echo BASE_URL; ?>pickup-dropoff.php">Pick Up & Drop Off</a> page for more details.
            </p>
        </div>
        
        <div style="margin-top: 3rem; text-align: center;">
            <a href="<?php echo BASE_URL; ?>quote-request.php" class="btn">Request a Quote</a>
            <a href="<?php echo BASE_URL; ?>pickup-dropoff.php" class="btn btn-secondary" style="margin-left: 1rem;">Pick Up & Drop Off Info</a>
        </div>
    </div>
</section>

<?php include __DIR__ . '/includes/footer.php'; ?>


