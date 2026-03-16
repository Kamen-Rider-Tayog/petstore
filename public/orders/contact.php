<?php
require_once '../../backend/config/config.php';
require_once '../../backend/functions/helpers.php';

$page_title = "Contact Us - PetStore";
include '../../backend/includes/header.php';
<link rel="stylesheet" href="../../assets/css/contact.css">


// Handle form submission
$message = '';
$message_type = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Process contact form
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $subject = trim($_POST['subject'] ?? '');
    $message_text = trim($_POST['message'] ?? '');

    $errors = [];

    // Validation
    if (empty($name)) {
        $errors[] = 'Name is required';
    }

    if (empty($email)) {
        $errors[] = 'Email is required';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'Please enter a valid email address';
    }

    if (empty($subject)) {
        $errors[] = 'Subject is required';
    }

    if (empty($message_text)) {
        $errors[] = 'Message is required';
    }

    if (empty($errors)) {
        // Send email
        $to = 'contact@petstore.com'; // Change this to your actual email
        $email_subject = "PetStore Contact: $subject";
        $email_body = "Name: $name\n";
        $email_body .= "Email: $email\n";
        $email_body .= "Subject: $subject\n\n";
        $email_body .= "Message:\n$message_text\n";

        $headers = "From: $email\r\n";
        $headers .= "Reply-To: $email\r\n";

        if (mail($to, $email_subject, $email_body, $headers)) {
            $message = 'Thank you for your message! We\'ll get back to you within 24 hours.';
            $message_type = 'success';
        } else {
            $message = 'Sorry, there was an error sending your message. Please try again later.';
            $message_type = 'error';
        }
    } else {
        $message = implode('<br>', $errors);
        $message_type = 'error';
    }
}
?>

<div class="contact-page">
    <!-- Hero Section -->
    <section class="contact-hero">
        <div class="container">
            <div class="hero-content">
                <h1>Contact Us</h1>
                <p class="hero-subtitle">We're here to help with all your pet care needs</p>
            </div>
        </div>
    </section>

    <!-- Contact Section -->
    <section class="contact-section">
        <div class="container">
            <div class="contact-grid">
                <!-- Contact Form -->
                <div class="contact-form-container">
                    <h2>Send us a Message</h2>
                    <p>Have a question about our products or services? We'd love to hear from you!</p>

                    <?php if ($message): ?>
                    <div class="message <?php echo $message_type; ?>">
                        <?php echo $message; ?>
                    </div>
                    <?php endif; ?>

                    <form id="contact-form" method="POST" action="contact_process.php">
                        <div class="form-group">
                            <label for="name">Full Name *</label>
                            <input type="text" id="name" name="name" required
                                   value="<?php echo htmlspecialchars($_POST['name'] ?? ''); ?>">
                        </div>

                        <div class="form-group">
                            <label for="email">Email Address *</label>
                            <input type="email" id="email" name="email" required
                                   value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>">
                        </div>

                        <div class="form-group">
                            <label for="subject">Subject *</label>
                            <select id="subject" name="subject" required>
                                <option value="">Select a subject</option>
                                <option value="General Inquiry" <?php echo ($_POST['subject'] ?? '') === 'General Inquiry' ? 'selected' : ''; ?>>General Inquiry</option>
                                <option value="Product Question" <?php echo ($_POST['subject'] ?? '') === 'Product Question' ? 'selected' : ''; ?>>Product Question</option>
                                <option value="Order Support" <?php echo ($_POST['subject'] ?? '') === 'Order Support' ? 'selected' : ''; ?>>Order Support</option>
                                <option value="Veterinary Advice" <?php echo ($_POST['subject'] ?? '') === 'Veterinary Advice' ? 'selected' : ''; ?>>Veterinary Advice</option>
                                <option value="Partnership" <?php echo ($_POST['subject'] ?? '') === 'Partnership' ? 'selected' : ''; ?>>Partnership</option>
                                <option value="Other" <?php echo ($_POST['subject'] ?? '') === 'Other' ? 'selected' : ''; ?>>Other</option>
                            </select>
                        </div>

                        <div class="form-group">
                            <label for="message">Message *</label>
                            <textarea id="message" name="message" rows="6" required><?php echo htmlspecialchars($_POST['message'] ?? ''); ?></textarea>
                        </div>

                        <!-- Honeypot field for spam protection -->
                        <div class="form-group" style="display: none;">
                            <label for="website">Website (leave blank)</label>
                            <input type="text" id="website" name="website" autocomplete="off">
                        </div>

                        <button type="submit" class="btn btn-primary">Send Message</button>
                    </form>
                </div>

                <!-- Contact Information -->
                <div class="contact-info">
                    <h2>Get in Touch</h2>

                    <div class="info-section">
                        <div class="info-item">
                            <div class="info-icon">📍</div>
                            <div class="info-content">
                                <h3>Visit Our Store</h3>
                                <p>123 Pet Street<br>Animal City, AC 12345<br>United States</p>
                            </div>
                        </div>

                        <div class="info-item">
                            <div class="info-icon">📞</div>
                            <div class="info-content">
                                <h3>Call Us</h3>
                                <p><a href="tel:+1234567890">(123) 456-7890</a><br>
                                <small>Mon-Fri: 9AM-7PM<br>Sat-Sun: 10AM-5PM</small></p>
                            </div>
                        </div>

                        <div class="info-item">
                            <div class="info-icon">✉️</div>
                            <div class="info-content">
                                <h3>Email Us</h3>
                                <p><a href="mailto:contact@petstore.com">contact@petstore.com</a><br>
                                <a href="mailto:support@petstore.com">support@petstore.com</a><br>
                                <small>We respond within 24 hours</small></p>
                            </div>
                        </div>

                        <div class="info-item">
                            <div class="info-icon">🕒</div>
                            <div class="info-content">
                                <h3>Business Hours</h3>
                                <p><strong>Monday - Friday:</strong> 9:00 AM - 7:00 PM<br>
                                <strong>Saturday:</strong> 10:00 AM - 5:00 PM<br>
                                <strong>Sunday:</strong> 12:00 PM - 4:00 PM</p>
                            </div>
                        </div>
                    </div>

                    <!-- Map Placeholder -->
                    <div class="map-container">
                        <div class="map-placeholder">
                            <div class="map-icon">🗺️</div>
                            <p>Interactive Map Coming Soon</p>
                            <small>123 Pet Street, Animal City, AC 12345</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- FAQ Section -->
    <section class="faq-section">
        <div class="container">
            <div class="faq-content">
                <h2>Frequently Asked Questions</h2>
                <div class="faq-grid">
                    <div class="faq-item">
                        <h3>Do you offer grooming services?</h3>
                        <p>Yes! We provide professional grooming services for dogs and cats. Our certified groomers use only the highest quality products and techniques.</p>
                    </div>

                    <div class="faq-item">
                        <h3>Can I return products?</h3>
                        <p>We offer a 30-day return policy on most products. Items must be unused and in their original packaging. Please contact us for return instructions.</p>
                    </div>

                    <div class="faq-item">
                        <h3>Do you have a veterinary clinic?</h3>
                        <p>Yes, our on-site veterinary clinic is staffed by licensed veterinarians. We offer wellness exams, vaccinations, and emergency care.</p>
                    </div>

                    <div class="faq-item">
                        <h3>Do you deliver?</h3>
                        <p>Absolutely! We offer free delivery on orders over $50 within our local area. For online orders, we ship nationwide with various shipping options.</p>
                    </div>

                    <div class="faq-item">
                        <h3>Can I schedule an appointment online?</h3>
                        <p>Yes, you can book grooming and veterinary appointments through our website. Simply create an account and use our online booking system.</p>
                    </div>

                    <div class="faq-item">
                        <h3>Do you accept pet insurance?</h3>
                        <p>We accept most major pet insurance plans. Please contact our office with your insurance information, and we'll be happy to help with the claims process.</p>
                    </div>
                </div>

                <div class="faq-cta">
                    <p>Can't find what you're looking for?</p>
                    <a href="#contact-form" class="btn btn-secondary">Contact Us Directly</a>
                </div>
            </div>
        </div>
    </section>
</div>

<link rel=" stylesheet\ href=\../../assets/css/contact.css\>

<script src=\../../assets/js/contact.js\></script>

<?php include '../../backend/includes/footer.php'; ?>

