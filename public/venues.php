<?php
require_once __DIR__ . '/includes/config.php';
$pageTitle = 'Venues';
$pageDescription = 'Wadadli Flare Catering works with venues in our service area. We provide outside catering services for events at various locations.';
include __DIR__ . '/includes/header.php';
?>

<section class="section">
    <div class="container">
        <h1 class="section-title">Working with Venues</h1>
        
        <div style="max-width: 800px; margin: 2rem auto; text-align: center;">
            <p style="font-size: 1.1rem;">
                We're happy to work with venues in our service area that allow outside catering. We bring our culinary expertise to your chosen location, making your event seamless and delicious.
            </p>
        </div>
        
        <div class="grid grid-2" style="margin-top: 3rem;">
            <div class="card">
                <h2 class="card-title">Venue Requirements</h2>
                <p>To provide the best service, we need:</p>
                <ul style="margin-top: 1rem; padding-left: 1.5rem;">
                    <li>Running water access</li>
                    <li>Permission for outside catering</li>
                    <li>Reasonable access for delivery and setup</li>
                </ul>
                <p style="margin-top: 1rem;">
                    That's it! We're flexible and can work with most venue setups.
                </p>
            </div>
            
            <div class="card">
                <h2 class="card-title">Service Area</h2>
                <p>We serve venues throughout:</p>
                <ul style="margin-top: 1rem; padding-left: 1.5rem;">
                    <li>Twin Valley, Pennsylvania</li>
                    <li>West Chester, Pennsylvania</li>
                    <li>Main Line (west of Philadelphia)</li>
                    <li>Elverson and surrounding areas</li>
                </ul>
                <p style="margin-top: 1rem;">
                    Based in Elverson, we're centrally located to serve your venue.
                </p>
            </div>
        </div>
        
        <div style="margin-top: 3rem; padding: 2rem; background-color: var(--light-gray); border-radius: 8px;">
            <h2 class="section-subtitle">What We Provide</h2>
            <div class="grid grid-2" style="margin-top: 2rem;">
                <div>
                    <h3 style="color: var(--primary-gold); margin-bottom: 1rem;">Food & Service</h3>
                    <ul style="padding-left: 1.5rem;">
                        <li>Custom menu tailored to your event</li>
                        <li>Professional food preparation</li>
                        <li>Beautiful presentation</li>
                        <li>Delivery to your venue</li>
                        <li>Setup and service (if requested)</li>
                    </ul>
                </div>
                <div>
                    <h3 style="color: var(--primary-gold); margin-bottom: 1rem;">Equipment (Available)</h3>
                    <ul style="padding-left: 1.5rem;">
                        <li>Disposable tableware (plates, bowls, cups, napkins, utensils)</li>
                        <li>Chafing dishes with sternos</li>
                        <li>Serving utensils</li>
                        <li>Equipment kits available ($25 per set)</li>
                    </ul>
                </div>
            </div>
        </div>
        
        <div style="margin-top: 3rem;">
            <h2 class="section-subtitle">Working Together</h2>
            <p style="text-align: center; max-width: 800px; margin: 1rem auto;">
                We understand that each venue has its own policies and requirements. We're experienced in working with various venues and can coordinate with your venue's staff to ensure everything runs smoothly. Whether it's a wedding venue, event space, or private location, we'll make it work.
            </p>
        </div>
        
        <div style="margin-top: 3rem; padding: 2rem; background-color: var(--primary-gold); color: var(--white); border-radius: 8px; text-align: center;">
            <h2 style="margin-bottom: 1rem;">Planning an Event at a Venue?</h2>
            <p style="margin-bottom: 2rem; font-size: 1.1rem;">
                Contact us to discuss your venue and event needs. We'll work with you and your venue to create a seamless catering experience.
            </p>
            <a href="<?php echo BASE_URL; ?>quote-request.php" class="btn" style="background-color: var(--white); color: var(--primary-gold);">Request a Quote</a>
            <a href="<?php echo BASE_URL; ?>contact.php" class="btn btn-secondary" style="margin-left: 1rem; background-color: rgba(255,255,255,0.2); color: var(--white); border: 2px solid var(--white);">Contact Us</a>
        </div>
    </div>
</section>

<?php include __DIR__ . '/includes/footer.php'; ?>


