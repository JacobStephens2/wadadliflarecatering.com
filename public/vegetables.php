<?php
require_once __DIR__ . '/includes/config.php';
$pageTitle = 'Vegetables';
$pageDescription = 'Vegetable catering from Wadadli Flare: garlic asparagus, ratatouille, eggplant parmesan, stir fry vegetables, grilled vegetable platters, and more. Pan pricing for pick up and drop off.';
include __DIR__ . '/includes/header.php';
?>

<section class="section">
    <div class="container">
        <h1 class="section-title">Vegetables</h1>

        <div style="max-width: 800px; margin: 2rem auto; text-align: center;">
            <p style="font-size: 1.1rem;">
                Fresh vegetable sides and vegetarian dishes for buffets, pick up, and drop off. Choose a pan size that fits your guest count, or add a grilled vegetable platter to any event.
            </p>
        </div>

        <div style="max-width: 800px; margin: 2rem auto; border-radius: 8px; overflow: hidden;">
            <img src="<?php echo GALLERY_URL; ?>grilled_vegetables.webp" alt="Grilled vegetables prepared by Wadadli Flare Catering">
        </div>

        <div style="margin-top: 3rem; padding: 2rem; background-color: var(--light-gray); border-radius: 8px;">
            <h2 class="section-subtitle">Serving Information</h2>
            <div class="grid grid-3" style="margin-top: 1.5rem;">
                <div>
                    <p><strong>Half Pan:</strong> 10 - 12 people</p>
                </div>
                <div>
                    <p><strong>Full Shallow Pan:</strong> 20 - 25 people</p>
                </div>
                <div>
                    <p><strong>Full Deep Pan:</strong> 30 - 35 people</p>
                </div>
            </div>
        </div>

        <div style="margin-top: 3rem;">
            <h2 class="section-subtitle">Vegetable Dishes</h2>
            <div class="grid grid-2">
                <div class="card">
                    <h3 class="card-title">Garlic Asparagus with Lemon Zest</h3>
                    <p>Half Pan - $60<br>Full Shallow Pan - $120<br>Full Deep Pan - $150</p>
                </div>
                <div class="card">
                    <h3 class="card-title">Roasted Seasonal Veg Medley</h3>
                    <p>Half Pan - $50<br>Full Shallow Pan - $120</p>
                </div>
                <div class="card">
                    <h3 class="card-title">Eggplant Parmesan</h3>
                    <p>Full Shallow Pan - $120<br>Full Deep Pan - $160</p>
                </div>
                <div class="card">
                    <h3 class="card-title">Ratatouille</h3>
                    <p style="margin-bottom: 0.75rem; font-size: 0.95rem; color: var(--medium-gray);">Zucchini, yellow squash, eggplant, onion, tomato, garlic, herbs, and olive oil</p>
                    <p>Half Pan - $60<br>Full Shallow Pan - $120<br>Full Deep Pan - $150</p>
                </div>
                <div class="card">
                    <h3 class="card-title">Green Beans Almondine</h3>
                    <p>Half Pan - $55<br>Full Shallow Pan - $115<br>Full Deep Pan - $140</p>
                </div>
                <div class="card">
                    <h3 class="card-title">Citrus Glazed Carrots with Fresh Thyme</h3>
                    <p>Half Pan - $50<br>Full Shallow Pan - $110<br>Full Deep Pan - $140</p>
                </div>
                <div class="card">
                    <h3 class="card-title">Sautéed Broccoli, Carrots, Red Onion &amp; Peppers</h3>
                    <p>Half Pan - $50<br>Full Shallow Pan - $100<br>Full Deep Pan - $140</p>
                </div>
                <div class="card">
                    <h3 class="card-title">Stir Fry Vegetables</h3>
                    <p style="margin-bottom: 0.75rem; font-size: 0.95rem; color: var(--medium-gray);">Broccoli, bell peppers, and mushrooms with soy, ginger, garlic, and sesame oil</p>
                    <p>Half Pan - $55<br>Full Shallow Pan - $120</p>
                </div>
            </div>
        </div>

        <div style="margin-top: 3rem;">
            <h2 class="section-subtitle">Grilled Vegetable Platter</h2>
            <div class="card" style="max-width: 700px; margin: 0 auto;">
                <p>A colorful grilled vegetable platter priced by the guest. Price depends on the vegetable selection.</p>
                <p style="font-size: 1.1rem; font-weight: bold; color: var(--primary-red); margin-top: 1rem;">$4.50 - $5.00 per person</p>
            </div>
        </div>

        <div style="margin-top: 3rem;">
            <h2 class="section-subtitle">Charcuterie Station</h2>
            <div class="card" style="max-width: 700px; margin: 0 auto;">
                <p>Beautifully arranged assortment of 4 cheeses and 4 meats, plus breads, crackers, preserves &amp; jams, dried fruits, hummus, and crisps.</p>
                <ul style="margin-top: 1rem; padding-left: 1.5rem; line-height: 1.8;">
                    <li><strong>$16 per person</strong></li>
                    <li>25 person minimum</li>
                    <li>$50 setup fee</li>
                </ul>
            </div>
        </div>

        <div style="margin-top: 3rem; text-align: center;">
            <a href="<?php echo BASE_URL; ?>quote-request.php" class="btn">Request a Quote</a>
            <a href="<?php echo BASE_URL; ?>contact.php" class="btn btn-secondary" style="margin-left: 1rem;">Contact Us</a>
        </div>
    </div>
</section>

<?php include __DIR__ . '/includes/footer.php'; ?>
