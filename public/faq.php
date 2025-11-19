<?php
require_once __DIR__ . '/includes/config.php';
$pageTitle = 'FAQ';
$pageDescription = 'Frequently asked questions about Wadadli Flare Catering services, booking, and policies.';
include __DIR__ . '/includes/header.php';
?>

<section class="section">
    <div class="container">
        <h1 class="section-title">Frequently Asked Questions</h1>
        
        <div style="max-width: 900px; margin: 2rem auto;">
            <div class="card" style="margin-bottom: 2rem;">
                <h2 class="card-title">How far in advance should I book?</h2>
                <p>We recommend booking at least 7-10 days before your event, depending on the size. For larger events, we suggest booking as early as possible to ensure availability.</p>
            </div>
            
            <div class="card" style="margin-bottom: 2rem;">
                <h2 class="card-title">What areas do you serve?</h2>
                <p>We're based in Elverson, Pennsylvania, and serve Twin Valley, West Chester, the Main Line (west of Philadelphia), and surrounding areas.</p>
            </div>
            
            <div class="card" style="margin-bottom: 2rem;">
                <h2 class="card-title">What types of cuisine do you offer?</h2>
                <p>We offer a diverse range of cuisines including American, French, Italian, Caribbean, Asian, and more. We specialize in custom menus tailored to your preferences. While we're known for Caribbean flavors, we're a full-service caterer with extensive culinary expertise.</p>
            </div>
            
            <div class="card" style="margin-bottom: 2rem;">
                <h2 class="card-title">Do you offer vegetarian or vegan options?</h2>
                <p>Yes! We offer vegetarian and vegan options. Please let us know about any dietary restrictions when you request a quote, and we'll work with you to create a menu that accommodates everyone.</p>
            </div>
            
            <div class="card" style="margin-bottom: 2rem;">
                <h2 class="card-title">What service options do you provide?</h2>
                <p>We offer pick up, drop off, drop off with set-up, and full service (delivery, set-up, serving, and clean-up). A 10% service fee is added for full service that includes serving and breakdown.</p>
            </div>
            
            <div class="card" style="margin-bottom: 2rem;">
                <h2 class="card-title">Can you work with my venue?</h2>
                <p>Yes! We work with venues in our service area that allow outside catering. We need running water access and permission for outside catering. We're flexible and can coordinate with your venue.</p>
            </div>
            
            <div class="card" style="margin-bottom: 2rem;">
                <h2 class="card-title">What payment methods do you accept?</h2>
                <p>We accept Visa and MasterCard (with a 3.25% processing fee) or Venmo. A 30% deposit is required at the time of booking.</p>
            </div>
            
            <div class="card" style="margin-bottom: 2rem;">
                <h2 class="card-title">Can you customize the menu?</h2>
                <p>Absolutely! We specialize in custom menus. If you don't see something you'd like on our menu, please feel free to ask. We'll work with you to create the perfect menu for your event.</p>
            </div>
            
            <div class="card" style="margin-bottom: 2rem;">
                <h2 class="card-title">Do you provide equipment?</h2>
                <p>Yes, we can provide disposable tableware (plates, bowls, cups, napkins, utensils) and chafing dish kits with sternos and serving utensils. Equipment kits are available for $25 per set.</p>
            </div>
            
            <div style="margin-top: 3rem; padding: 2rem; background-color: var(--light-gray); border-radius: 8px; text-align: center;">
                <h2 style="margin-bottom: 1rem;">Still Have Questions?</h2>
                <p style="margin-bottom: 2rem;">
                    We're here to help! Contact us and we'll be happy to answer any questions you have.
                </p>
                <a href="<?php echo BASE_URL; ?>contact.php" class="btn">Contact Us</a>
                <a href="<?php echo BASE_URL; ?>quote-request.php" class="btn btn-secondary" style="margin-left: 1rem;">Request a Quote</a>
            </div>
        </div>
    </div>
</section>

<?php include __DIR__ . '/includes/footer.php'; ?>


