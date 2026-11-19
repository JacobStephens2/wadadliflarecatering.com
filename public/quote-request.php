<?php
use WadadliFlare\LeadIntake;

require_once __DIR__ . '/includes/config.php';
require_once __DIR__ . '/includes/lead_intake.php';

$intake = lead_intake();
$formState = $intake->handlePageRequest('quote', $_SERVER, $_POST);
$success = $formState['success'];
$error = $formState['error'];
$values = $formState['values'];

$pageTitle = 'Request a Quote';
$pageDescription = 'Request a catering quote for your event. Fill out our form and we will get back to you with pricing and details.';
include __DIR__ . '/includes/header.php';
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
                <?php echo htmlspecialchars($error, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'); ?>
            </div>
            <?php endif; ?>
            
            <form method="POST" action="" onsubmit="return validateForm(this);" class="card" style="padding: 2rem;">
                <div class="form-group">
                    <label for="name">Your Name *</label>
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
                    <label for="event_type">Event Type *</label>
                    <select id="event_type" name="event_type" required>
                        <option value="">Select an event type</option>
                        <?php foreach (LeadIntake::QUOTE_EVENT_TYPES as $eventType): ?>
                        <?php $escapedType = htmlspecialchars($eventType, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'); ?>
                        <option value="<?php echo $escapedType; ?>" <?php echo $values['event_type'] === $escapedType ? 'selected' : ''; ?>><?php echo $escapedType; ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <div class="form-group">
                    <label for="event_date">Event Date</label>
                    <input type="date" id="event_date" name="event_date" value="<?php echo $values['event_date']; ?>">
                </div>
                
                <div class="form-group">
                    <label for="guest_count">Number of Guests</label>
                    <input type="number" id="guest_count" name="guest_count" min="1" value="<?php echo $values['guest_count']; ?>">
                </div>
                
                <div class="form-group">
                    <label for="location">Event Location</label>
                    <input type="text" id="location" name="location" maxlength="255" placeholder="City, venue name, or address" value="<?php echo $values['location']; ?>">
                </div>
                
                <div class="form-group">
                    <label for="message">Additional Details</label>
                    <textarea id="message" name="message" maxlength="65535" rows="5" placeholder="Tell us about your event, dietary restrictions, preferred cuisine styles, or any special requests..."><?php echo $values['message']; ?></textarea>
                </div>
                
                <?php echo $intake->renderSpamGuards(defined('TURNSTILE_SITE_KEY') ? (string)TURNSTILE_SITE_KEY : ''); ?>

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

