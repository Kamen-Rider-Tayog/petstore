<?php
/**
 * Contact Form Processing
 * Handles contact form submissions and email sending
 */

require_once '../../backend/config/config.php';
require_once '../../backend/functions/helpers.php';

// Set headers for JSON response
header('Content-Type: application/json');
header('X-Content-Type-Options: nosniff');

// Only allow POST requests
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode([
        'success' => false,
        'message' => 'Method not allowed'
    ]);
    exit;
}

// Get and sanitize form data
$name = trim($_POST['name'] ?? '');
$email = trim($_POST['email'] ?? '');
$subject = trim($_POST['subject'] ?? '');
$message = trim($_POST['message'] ?? '');

// Validation
$errors = [];
$honeypot = trim($_POST['website'] ?? ''); // Honeypot field

// Check honeypot (should be empty)
if (!empty($honeypot)) {
    $errors[] = 'Spam detected';
}

// Validate required fields
if (empty($name)) {
    $errors[] = 'Name is required';
} elseif (strlen($name) < 2) {
    $errors[] = 'Name must be at least 2 characters long';
} elseif (strlen($name) > 100) {
    $errors[] = 'Name must not exceed 100 characters';
}

if (empty($email)) {
    $errors[] = 'Email is required';
} elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $errors[] = 'Please enter a valid email address';
} elseif (strlen($email) > 100) {
    $errors[] = 'Email must not exceed 100 characters';
}

if (empty($subject)) {
    $errors[] = 'Subject is required';
} elseif (strlen($subject) > 200) {
    $errors[] = 'Subject must not exceed 200 characters';
}

if (empty($message)) {
    $errors[] = 'Message is required';
} elseif (strlen($message) < 10) {
    $errors[] = 'Message must be at least 10 characters long';
} elseif (strlen($message) > 2000) {
    $errors[] = 'Message must not exceed 2000 characters';
}

// Check for suspicious content
$suspicious_patterns = [
    '/<script/i',
    '/javascript:/i',
    '/on\w+\s*=/i',
    '/<iframe/i',
    '/<object/i',
    '/<embed/i'
];

foreach ([$name, $email, $subject, $message] as $field) {
    foreach ($suspicious_patterns as $pattern) {
        if (preg_match($pattern, $field)) {
            $errors[] = 'Invalid content detected';
            break 2;
        }
    }
}

// Rate limiting - check if user has submitted recently
$ip = $_SERVER['REMOTE_ADDR'];
$rate_limit_key = "contact_form_$ip";
$last_submission = $_SESSION[$rate_limit_key] ?? 0;
$current_time = time();

if ($current_time - $last_submission < 60) { // 60 seconds between submissions
    $errors[] = 'Please wait before submitting another message';
}

// If validation fails, return errors
if (!empty($errors)) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => 'Please correct the following errors:',
        'errors' => $errors
    ]);
    exit;
}

// Prepare email content
$to = 'contact@petstore.com'; // Change this to your actual contact email
$email_subject = "PetStore Contact: $subject";

$email_body = "New contact form submission from PetStore website\n\n";
$email_body .= "================================\n";
$email_body .= "Name: " . htmlspecialchars($name) . "\n";
$email_body .= "Email: " . htmlspecialchars($email) . "\n";
$email_body .= "Subject: " . htmlspecialchars($subject) . "\n";
$email_body .= "Date: " . date('Y-m-d H:i:s') . "\n";
$email_body .= "IP Address: " . $ip . "\n";
$email_body .= "================================\n\n";
$email_body .= "Message:\n" . htmlspecialchars($message) . "\n\n";
$email_body .= "================================\n";
$email_body .= "This message was sent from the PetStore contact form.\n";

// Email headers
$headers = "From: " . htmlspecialchars($name) . " <noreply@petstore.com>\r\n";
$headers .= "Reply-To: " . htmlspecialchars($email) . "\r\n";
$headers .= "X-Mailer: PetStore Contact Form\r\n";
$headers .= "Content-Type: text/plain; charset=UTF-8\r\n";

// Send email
$email_sent = false;
try {
    $email_sent = mail($to, $email_subject, $email_body, $headers);
} catch (Exception $e) {
    error_log("Email sending failed: " . $e->getMessage());
}

// Store submission in database for tracking (optional)
try {
    $stmt = $conn->prepare("
        INSERT INTO contact_messages (name, email, subject, message, ip_address, created_at)
        VALUES (?, ?, ?, ?, ?, NOW())
    ");
    $stmt->bind_param("sssss", $name, $email, $subject, $message, $ip);
    $stmt->execute();
} catch (Exception $e) {
    // Log database error but don't fail the request
    error_log("Database error: " . $e->getMessage());
}

// Set rate limiting
$_SESSION[$rate_limit_key] = $current_time;

// Return response
if ($email_sent) {
    echo json_encode([
        'success' => true,
        'message' => 'Thank you for your message! We\'ll get back to you within 24 hours.'
    ]);
} else {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Sorry, there was an error sending your message. Please try again later or contact us directly.'
    ]);
}
?>
<link rel="stylesheet" href="../../assets/css/contact_process.css">
