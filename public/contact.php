<?php
require_once __DIR__ . '/includes/config.php';
require_once __DIR__ . '/includes/lead_intake.php';

$intake = lead_intake();
$formState = $intake->handlePageRequest('contact', $_SERVER, $_POST);
$success = $formState['success'];
$error = $formState['error'];
$values = $formState['values'];

$pageTitle = 'Contact';
$pageDescription = 'Contact Wadadli Flare Catering. Phone, email, and contact form.';
include __DIR__ . '/includes/header.php';
?>

<section class="section">
    <div class="container">
        <h1 class="section-title">Contact Us</h1>
        
        <?php if ($success): ?>
        <div class="alert alert-success" style="margin-top: 2rem;">
            Thank you for your message! We'll get back to you as soon as possible.
        </div>
        <?php elseif ($error): ?>
        <div class="alert alert-error" style="margin-top: 2rem;">
            <?php echo htmlspecialchars($error, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'); ?>
        </div>
        <?php endif; ?>
        
        <div class="grid grid-2" style="margin-top: 3rem; gap: 3rem;">
            <div>
                <h2 class="section-subtitle">Get in Touch</h2>
                <p style="margin-bottom: 2rem;">
                    We'd love to hear from you! Whether you have questions about our services, want to discuss your event, or need a quote, we're here to help.
                </p>
                
                <div style="margin-bottom: 2rem;">
                    <h3 style="color: var(--dark-ocean-blue); margin-bottom: 0.5rem;">Phone / Text</h3>
                    <p style="font-size: 1.1rem;">
                        <a href="tel:<?php echo str_replace([' ', '(', ')', '-'], '', CONTACT_PHONE); ?>"><?php echo CONTACT_PHONE; ?></a>
                    </p>
                </div>
                
                <div style="margin-bottom: 2rem;">
                    <h3 style="color: var(--dark-ocean-blue); margin-bottom: 0.5rem;">Email</h3>
                    <p style="font-size: 1.1rem;">
                        <a href="mailto:<?php echo CONTACT_EMAIL; ?>"><?php echo CONTACT_EMAIL; ?></a>
                    </p>
                </div>
                
                <div style="margin-bottom: 2rem;">
                    <h3 style="color: var(--dark-ocean-blue); margin-bottom: 0.5rem;">Location</h3>
                    <p>Based in Elverson, Pennsylvania</p>
                    <p style="margin-top: 0.5rem;">
                        <a href="<?php echo GOOGLE_MAPS_URL; ?>" target="_blank" rel="noopener noreferrer">Find us on Google Maps</a>
                    </p>
                </div>
                
                <div style="margin-bottom: 2rem;">
                    <h3 style="color: var(--dark-ocean-blue); margin-bottom: 0.5rem;">Follow Us</h3>
                    <div class="social-links">
                        <a href="<?php echo FACEBOOK_URL; ?>" target="_blank" rel="noopener noreferrer">Facebook</a>
                        <a href="<?php echo INSTAGRAM_URL; ?>" target="_blank" rel="noopener noreferrer">Instagram</a>
                    </div>
                </div>
                
                <div style="margin-top: 2rem; padding: 1.5rem; background-color: var(--light-gray); border-radius: 8px;">
                    <p style="font-style: italic; margin: 0;">
                        "We love our customers, so feel free to contact us at <?php echo CONTACT_PHONE; ?>."
                    </p>
                </div>
            </div>
            
            <div>
                <h2 class="section-subtitle">Send Us a Message</h2>
                
                <form method="POST" action="" onsubmit="return validateForm(this);">
                    <div class="form-group">
                        <label for="name">Name *</label>
                        <input type="text" id="name" name="name" maxlength="255" required value="<?php echo $values['name']; ?>">
                    </div>
                    
                    <div class="form-group">
                        <label for="email">Email *</label>
                        <input type="email" id="email" name="email" maxlength="255" required value="<?php echo $values['email']; ?>">
                    </div>
                    
                    <div class="form-group">
                        <label for="phone">Phone</label>
                        <input type="tel" id="phone" name="phone" maxlength="50" value="<?php echo $values['phone']; ?>">
                    </div>
                    
                    <div class="form-group">
                        <label for="message">Message *</label>
                        <textarea id="message" name="message" maxlength="65535" required><?php echo $values['message']; ?></textarea>
                    </div>
                    
                    <?php echo $intake->renderSpamGuards(defined('TURNSTILE_SITE_KEY') ? (string)TURNSTILE_SITE_KEY : ''); ?>

                    <button type="submit" class="btn">Send Message</button>
                </form>
            </div>
        </div>
    </div>
</section>

<?php if ($success || $error): ?>
<script>
    // Scroll to top when status message is displayed
    window.addEventListener('DOMContentLoaded', function() {
        window.scrollTo({ top: 0, behavior: 'smooth' });
    });
</script>
<?php endif; ?>

<?php include __DIR__ . '/includes/footer.php'; ?>


