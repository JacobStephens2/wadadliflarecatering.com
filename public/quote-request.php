<?php
require_once __DIR__ . '/includes/config.php';
$pageTitle = 'Request a Quote';
$pageDescription = 'Request a catering quote for your event. Fill out our form and we will get back to you with pricing and details.';
include __DIR__ . '/includes/header.php';

$success = false;
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    require_once PRIVATE_PATH . '/includes/db.php';
    require_once PRIVATE_PATH . '/includes/email.php';
    
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $event_type = trim($_POST['event_type'] ?? '');
    $event_date = trim($_POST['event_date'] ?? '');
    $guest_count = trim($_POST['guest_count'] ?? '');
    $location = trim($_POST['location'] ?? '');
    $message = trim($_POST['message'] ?? '');
    
    // Validation
    if (empty($name) || empty($email) || empty($event_type)) {
        $error = 'Please fill in all required fields.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Please enter a valid email address.';
    } else {
        try {
            // Convert event_date to proper format or null
            $event_date_db = !empty($event_date) ? date('Y-m-d', strtotime($event_date)) : null;
            $guest_count_db = !empty($guest_count) ? (int)$guest_count : null;
            
            // Save to database
            $stmt = $pdo->prepare("INSERT INTO quote_requests (name, email, phone, event_type, event_date, guest_count, location, message) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
            $stmt->execute([$name, $email, $phone, $event_type, $event_date_db, $guest_count_db, $location, $message]);
            
            // Prepare data for email
            $emailData = [
                'name' => $name,
                'email' => $email,
                'phone' => $phone,
                'event_type' => $event_type,
                'event_date' => $event_date,
                'guest_count' => $guest_count,
                'location' => $location,
                'message' => $message
            ];
            
            // Send email
            if (sendQuoteRequestEmail($emailData)) {
                $success = true;
            } else {
                $error = 'Your quote request was saved but we had trouble sending the email. We will still receive your request.';
            }
        } catch (Exception $e) {
            error_log("Quote request error: " . $e->getMessage());
            $error = 'Sorry, there was an error processing your request. Please try again or call us directly.';
        }
    }
}
?>

<section class="section">
    <div class="container">
        <h1 class="section-title">Request a Quote</h1>
        <p style="text-align: center; max-width: 800px; margin: 2rem auto;">
            Tell us about your event and we'll provide you with a custom quote. We'll work with you to create the perfect menu for your occasion.
        </p>
        
        <div style="max-width: 800px; margin: 2rem auto;">
            <?php if ($success): ?>
            <div class="alert alert-success">
                Thank you for your quote request! We'll review your information and get back to you as soon as possible, typically within 24-48 hours.
            </div>
            <?php elseif ($error): ?>
            <div class="alert alert-error">
                <?php echo htmlspecialchars($error); ?>
            </div>
            <?php endif; ?>
            
            <form method="POST" action="" onsubmit="return validateForm(this);" class="card" style="padding: 2rem;">
                <div class="form-group">
                    <label for="name">Your Name *</label>
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
                    <label for="event_type">Event Type *</label>
                    <select id="event_type" name="event_type" required>
                        <option value="">Select an event type</option>
                        <option value="Wedding" <?php echo (isset($_POST['event_type']) && $_POST['event_type'] === 'Wedding') ? 'selected' : ''; ?>>Wedding</option>
                        <option value="Corporate Event" <?php echo (isset($_POST['event_type']) && $_POST['event_type'] === 'Corporate Event') ? 'selected' : ''; ?>>Corporate Event</option>
                        <option value="Private Event" <?php echo (isset($_POST['event_type']) && $_POST['event_type'] === 'Private Event') ? 'selected' : ''; ?>>Private Event</option>
                        <option value="Graduation" <?php echo (isset($_POST['event_type']) && $_POST['event_type'] === 'Graduation') ? 'selected' : ''; ?>>Graduation</option>
                        <option value="Birthday" <?php echo (isset($_POST['event_type']) && $_POST['event_type'] === 'Birthday') ? 'selected' : ''; ?>>Birthday</option>
                        <option value="Memorial Service" <?php echo (isset($_POST['event_type']) && $_POST['event_type'] === 'Memorial Service') ? 'selected' : ''; ?>>Memorial Service</option>
                        <option value="BBQ" <?php echo (isset($_POST['event_type']) && $_POST['event_type'] === 'BBQ') ? 'selected' : ''; ?>>BBQ</option>
                        <option value="Other" <?php echo (isset($_POST['event_type']) && $_POST['event_type'] === 'Other') ? 'selected' : ''; ?>>Other</option>
                    </select>
                </div>
                
                <div class="form-group">
                    <label for="event_date">Event Date</label>
                    <input type="date" id="event_date" name="event_date" value="<?php echo isset($_POST['event_date']) ? htmlspecialchars($_POST['event_date']) : ''; ?>">
                </div>
                
                <div class="form-group">
                    <label for="guest_count">Number of Guests</label>
                    <input type="number" id="guest_count" name="guest_count" min="1" value="<?php echo isset($_POST['guest_count']) ? htmlspecialchars($_POST['guest_count']) : ''; ?>">
                </div>
                
                <div class="form-group">
                    <label for="location">Event Location</label>
                    <input type="text" id="location" name="location" placeholder="City, venue name, or address" value="<?php echo isset($_POST['location']) ? htmlspecialchars($_POST['location']) : ''; ?>">
                </div>
                
                <div class="form-group">
                    <label for="message">Additional Details</label>
                    <textarea id="message" name="message" rows="5" placeholder="Tell us about your event, dietary restrictions, preferred cuisine styles, or any special requests..."><?php echo isset($_POST['message']) ? htmlspecialchars($_POST['message']) : ''; ?></textarea>
                </div>
                
                <button type="submit" class="btn">Submit Quote Request</button>
            </form>
            
            <div style="margin-top: 2rem; padding: 1.5rem; background-color: var(--light-gray); border-radius: 8px; text-align: center;">
                <p style="margin-bottom: 1rem;">
                    <strong>Prefer to talk?</strong> Give us a call at <a href="tel:<?php echo str_replace([' ', '(', ')', '-'], '', CONTACT_PHONE); ?>"><?php echo CONTACT_PHONE; ?></a>
                </p>
                <p style="font-style: italic; color: var(--medium-gray);">
                    If you don't see something you'd like on the menu, please feel free to ask. We specialize in custom menus!
                </p>
            </div>
        </div>
    </div>
</section>

<?php include __DIR__ . '/includes/footer.php'; ?>

