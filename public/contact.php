<?php
require_once __DIR__ . '/includes/config.php';
$pageTitle = 'Contact';
$pageDescription = 'Contact Wadadli Flare Catering. Phone, email, and contact form.';
include __DIR__ . '/includes/header.php';

$success = false;
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    require_once PRIVATE_PATH . '/includes/db.php';
    require_once PRIVATE_PATH . '/includes/email.php';
    
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $message = trim($_POST['message'] ?? '');
    
    // Validation
    if (empty($name) || empty($email) || empty($message)) {
        $error = 'Please fill in all required fields.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Please enter a valid email address.';
    } else {
        try {
            // Save to database
            $stmt = $pdo->prepare("INSERT INTO contact_submissions (name, email, phone, message) VALUES (?, ?, ?, ?)");
            $stmt->execute([$name, $email, $phone, $message]);
            
            // Send email
            if (sendContactEmail($name, $email, $phone, $message)) {
                $success = true;
            } else {
                $error = 'Your message was saved but we had trouble sending the email. We will still receive your message.';
            }
        } catch (Exception $e) {
            error_log("Contact form error: " . $e->getMessage());
            $error = 'Sorry, there was an error processing your message. Please try again or call us directly.';
        }
    }
}
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
            <?php echo htmlspecialchars($error); ?>
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
                        <input type="text" id="name" name="name" required value="<?php echo isset($_POST['name']) ? htmlspecialchars($_POST['name']) : ''; ?>">
                    </div>
                    
                    <div class="form-group">
                        <label for="email">Email *</label>
                        <input type="email" id="email" name="email" required value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>">
                    </div>
                    
                    <div class="form-group">
                        <label for="phone">Phone</label>
                        <input type="tel" id="phone" name="phone" value="<?php echo isset($_POST['phone']) ? htmlspecialchars($_POST['phone']) : ''; ?>">
                    </div>
                    
                    <div class="form-group">
                        <label for="message">Message *</label>
                        <textarea id="message" name="message" required><?php echo isset($_POST['message']) ? htmlspecialchars($_POST['message']) : ''; ?></textarea>
                    </div>
                    
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


