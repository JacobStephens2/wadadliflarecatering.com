<?php
require_once __DIR__ . '/includes/config.php';
$pageTitle = 'Policies';
$pageDescription = 'Booking, payment, and cancellation policies for Wadadli Flare Catering.';
include __DIR__ . '/includes/header.php';
?>

<section class="section">
    <div class="container">
        <h1 class="section-title">Policies</h1>
        
        <div style="max-width: 900px; margin: 2rem auto;">
            <div class="card" style="margin-bottom: 2rem;">
                <h2 class="card-title">Booking</h2>
                <p>Please book at least <strong>7-10 days</strong> before your event, depending on the size of the event. For larger events, we recommend booking as early as possible to ensure availability.</p>
            </div>
            
            <div class="card" style="margin-bottom: 2rem;">
                <h2 class="card-title">Payment</h2>
                <ul style="padding-left: 1.5rem; margin-top: 1rem;">
                    <li>A <strong>50% deposit</strong> of the total is required at the time of booking.</li>
                    <li>A <strong>6% Pennsylvania tax</strong> is added to all orders.</li>
                    <li>We accept:
                        <ul style="margin-top: 0.5rem;">
                            <li>Visa & MasterCard Credit Cards (additional 3.25% charge applies)</li>
                            <li>Venmo</li>
                        </ul>
                    </li>
                </ul>
            </div>
            
            <div class="card" style="margin-bottom: 2rem;">
                <h2 class="card-title">Cancellation Policy</h2>
                <ul style="padding-left: 1.5rem; margin-top: 1rem;">
                    <li><strong>30 days or more before event:</strong> No fee will be charged</li>
                    <li><strong>29 days to 3 days before event:</strong> 50% of the total invoice order will be charged</li>
                    <li><strong>48 hours or less before event:</strong> 100% of the invoice will be charged for orders cancelled within 48 hours of the event</li>
                </ul>
            </div>
            
            <div class="card" style="margin-bottom: 2rem;">
                <h2 class="card-title">Pricing</h2>
                <p><strong>Prices may change based on market prices.</strong> Final pricing will be confirmed at the time of booking and will be reflected in your invoice.</p>
            </div>
            
            <div style="margin-top: 3rem; padding: 2rem; background-color: var(--light-gray); border-radius: 8px; text-align: center;">
                <h2 style="margin-bottom: 1rem;">Questions About Our Policies?</h2>
                <p style="margin-bottom: 2rem;">
                    If you have any questions about our booking, payment, or cancellation policies, please don't hesitate to contact us.
                </p>
                <a href="<?php echo BASE_URL; ?>contact.php" class="btn">Contact Us</a>
            </div>
        </div>
    </div>
</section>

<?php include __DIR__ . '/includes/footer.php'; ?>












