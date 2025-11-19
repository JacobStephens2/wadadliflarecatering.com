<?php
/**
 * Email Functions using Mandrill/Mailchimp SMTP
 */

// Load config for constants
require_once __DIR__ . '/../../public/includes/config.php';

// Load environment variables using phpdotenv
require_once __DIR__ . '/env.php';

/**
 * Send email using SMTP (Mandrill/Mailchimp)
 */
function sendEmail($to, $subject, $message, $fromEmail = null, $fromName = null, $replyTo = null, $replyToName = null) {
    $smtpHost = $_ENV['SMTP_HOST'] ?? getenv('SMTP_HOST') ?: 'smtp.mandrillapp.com';
    $smtpPort = $_ENV['SMTP_PORT'] ?? getenv('SMTP_PORT') ?: 587;
    $smtpUser = $_ENV['SMTP_USER'] ?? getenv('SMTP_USER') ?: '';
    $smtpPass = $_ENV['SMTP_PASS'] ?? getenv('SMTP_PASS') ?: '';
    $smtpEncryption = $_ENV['SMTP_ENCRYPTION'] ?? getenv('SMTP_ENCRYPTION') ?: 'tls';
    $defaultFromEmail = $_ENV['SMTP_FROM_EMAIL'] ?? getenv('SMTP_FROM_EMAIL') ?: 'wadadliflare.catering@gmail.com';
    $defaultFromName = $_ENV['SMTP_FROM_NAME'] ?? getenv('SMTP_FROM_NAME') ?: 'Wadadli Flare Catering';
    
    $fromEmail = $fromEmail ?? $defaultFromEmail;
    $fromName = $fromName ?? $defaultFromName;
    $replyTo = $replyTo ?? $fromEmail;
    $replyToName = $replyToName ?? $fromName;
    
    // Use PHPMailer if available, otherwise fall back to basic mail()
    if (class_exists('PHPMailer\PHPMailer\PHPMailer')) {
        return sendEmailPHPMailer($to, $subject, $message, $fromEmail, $fromName, $smtpHost, $smtpPort, $smtpUser, $smtpPass, $smtpEncryption, $replyTo, $replyToName);
    } else {
        // Fallback to basic mail() function
        $headers = "From: $fromName <$fromEmail>\r\n";
        $headers .= "Reply-To: $replyToName <$replyTo>\r\n";
        $headers .= "Content-Type: text/html; charset=UTF-8\r\n";
        return mail($to, $subject, $message, $headers);
    }
}

/**
 * Send email using PHPMailer
 */
function sendEmailPHPMailer($to, $subject, $message, $fromEmail, $fromName, $smtpHost, $smtpPort, $smtpUser, $smtpPass, $smtpEncryption, $replyTo = null, $replyToName = null) {
    require_once __DIR__ . '/../../vendor/autoload.php';
    
    $mail = new PHPMailer\PHPMailer\PHPMailer(true);
    
    try {
        // Server settings
        $mail->isSMTP();
        $mail->Host = $smtpHost;
        $mail->SMTPAuth = true;
        $mail->Username = $smtpUser;
        $mail->Password = $smtpPass;
        $mail->SMTPSecure = $smtpEncryption;
        $mail->Port = $smtpPort;
        
        // Recipients
        $mail->setFrom($fromEmail, $fromName);
        $mail->addAddress($to);
        // Set reply-to to the provided address, or default to fromEmail
        $replyTo = $replyTo ?? $fromEmail;
        $replyToName = $replyToName ?? $fromName;
        $mail->addReplyTo($replyTo, $replyToName);
        
        // Content
        $mail->isHTML(true);
        $mail->Subject = $subject;
        $mail->Body = $message;
        $mail->AltBody = strip_tags($message);
        
        $mail->send();
        return true;
    } catch (Exception $e) {
        error_log("Email sending failed: " . $mail->ErrorInfo);
        error_log("SMTP Debug: Host=$smtpHost, Port=$smtpPort, User=" . substr($smtpUser, 0, 10) . "...");
        return false;
    }
}

/**
 * Send contact form email
 */
function sendContactEmail($name, $email, $phone, $message) {
    $subject = "New Contact Form Submission from $name";
    $emailBody = "
    <html>
    <head>
        <style>
            body { font-family: Arial, sans-serif; line-height: 1.6; }
            .container { max-width: 600px; margin: 0 auto; padding: 20px; }
            .header { background-color: #d4af37; color: white; padding: 20px; text-align: center; }
            .content { padding: 20px; background-color: #f9f9f9; }
            .field { margin-bottom: 15px; }
            .label { font-weight: bold; color: #333; }
        </style>
    </head>
    <body>
        <div class='container'>
            <div class='header'>
                <h2>New Contact Form Submission</h2>
            </div>
            <div class='content'>
                <div class='field'>
                    <span class='label'>Name:</span> $name
                </div>
                <div class='field'>
                    <span class='label'>Email:</span> <a href='mailto:$email'>$email</a>
                </div>
                <div class='field'>
                    <span class='label'>Phone:</span> $phone
                </div>
                <div class='field'>
                    <span class='label'>Message:</span><br>
                    " . nl2br(htmlspecialchars($message)) . "
                </div>
            </div>
        </div>
    </body>
    </html>
    ";
    
    // Set reply-to to the submitter's email so replies go directly to them
    return sendEmail(CONTACT_EMAIL, $subject, $emailBody, null, null, $email, $name);
}

/**
 * Send quote request email
 */
function sendQuoteRequestEmail($data) {
    $subject = "New Quote Request from {$data['name']}";
    $eventDate = $data['event_date'] ? date('F j, Y', strtotime($data['event_date'])) : 'Not specified';
    
    $emailBody = "
    <html>
    <head>
        <style>
            body { font-family: Arial, sans-serif; line-height: 1.6; }
            .container { max-width: 600px; margin: 0 auto; padding: 20px; }
            .header { background-color: #d4af37; color: white; padding: 20px; text-align: center; }
            .content { padding: 20px; background-color: #f9f9f9; }
            .field { margin-bottom: 15px; }
            .label { font-weight: bold; color: #333; }
        </style>
    </head>
    <body>
        <div class='container'>
            <div class='header'>
                <h2>New Quote Request</h2>
            </div>
            <div class='content'>
                <div class='field'>
                    <span class='label'>Name:</span> {$data['name']}
                </div>
                <div class='field'>
                    <span class='label'>Email:</span> <a href='mailto:{$data['email']}'>{$data['email']}</a>
                </div>
                <div class='field'>
                    <span class='label'>Phone:</span> {$data['phone']}
                </div>
                <div class='field'>
                    <span class='label'>Event Type:</span> {$data['event_type']}
                </div>
                <div class='field'>
                    <span class='label'>Event Date:</span> $eventDate
                </div>
                <div class='field'>
                    <span class='label'>Guest Count:</span> {$data['guest_count']}
                </div>
                <div class='field'>
                    <span class='label'>Location:</span> {$data['location']}
                </div>
                <div class='field'>
                    <span class='label'>Message:</span><br>
                    " . nl2br(htmlspecialchars($data['message'])) . "
                </div>
            </div>
        </div>
    </body>
    </html>
    ";
    
    // Set reply-to to the submitter's email so replies go directly to them
    return sendEmail(CONTACT_EMAIL, $subject, $emailBody, null, null, $data['email'], $data['name']);
}

