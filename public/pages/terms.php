<?php
require_once __DIR__ . '/../../backend/config/config.php';
require_once __DIR__ . '/../../backend/functions/helpers.php';

// No authentication required - this is a public page

$page_title = "Terms of Service";
include '../../backend/includes/header.php';
?>

<link rel="stylesheet" href="<?php echo asset('css/pages/terms.css'); ?>">

<!-- Terms of Service Content -->
<section class="terms-content">
    <div class="container">
        <div class="terms-container">
            <div class="terms-header">
                <h1>Terms of Service</h1>
                <p class="last-updated">Last updated: <?php echo date('F j, Y'); ?></p>
            </div>

            <div class="terms-body">
                <div class="terms-section">
                    <h2>1. Acceptance of Terms</h2>
                    <p>Welcome to <?php echo APP_NAME; ?>. These Terms of Service ("Terms") govern your use of our website, services, and products. By accessing or using our services, you agree to be bound by these Terms. If you do not agree to these Terms, please do not use our services.</p>
                    <p>These Terms apply to all visitors, users, and others who access or use our services.</p>
                </div>

                <div class="terms-section">
                    <h2>2. Description of Service</h2>
                    <p><?php echo APP_NAME; ?> provides an online platform for purchasing pet supplies, scheduling veterinary appointments, grooming services, and pet boarding. Our services include:</p>
                    <ul>
                        <li>Online retail of pet products and supplies</li>
                        <li>Veterinary consultation and appointment scheduling</li>
                        <li>Professional grooming services</li>
                        <li>Pet boarding and daycare services</li>
                        <li>Customer support and educational resources</li>
                    </ul>
                </div>

                <div class="terms-section">
                    <h2>3. User Accounts</h2>

                    <h3>Account Creation</h3>
                    <p>To access certain features of our service, you must create an account. You agree to:</p>
                    <ul>
                        <li>Provide accurate and complete information</li>
                        <li>Maintain the security of your password</li>
                        <li>Accept responsibility for all activities under your account</li>
                        <li>Notify us immediately of any unauthorized use</li>
                    </ul>

                    <h3>Account Termination</h3>
                    <p>We reserve the right to terminate or suspend your account at our discretion, with or without cause, and with or without notice.</p>
                </div>

                <div class="terms-section">
                    <h2>4. Orders and Payment</h2>

                    <h3>Product Orders</h3>
                    <p>All orders are subject to availability and acceptance. We reserve the right to refuse or cancel orders for any reason, including but not limited to:</p>
                    <ul>
                        <li>Product unavailability</li>
                        <li>Payment issues</li>
                        <li>Suspicious activity</li>
                        <li>Violation of these Terms</li>
                    </ul>

                    <h3>Payment Terms</h3>
                    <p>Payment is due at the time of order placement. We accept major credit cards, debit cards, and other payment methods as indicated on our website. All payments are processed securely through third-party payment processors.</p>

                    <h3>Pricing and Taxes</h3>
                    <p>All prices are subject to change without notice. Applicable taxes will be added to your order total as required by law.</p>
                </div>

                <div class="terms-section">
                    <h2>5. Shipping and Delivery</h2>
                    <p>We strive to deliver products within the estimated timeframe provided at checkout. However, delivery dates are estimates only and we are not liable for delays caused by factors beyond our control.</p>
                    <p>Risk of loss passes to the buyer upon delivery to the carrier. We recommend insurance for high-value shipments.</p>
                </div>

                <div class="terms-section">
                    <h2>6. Returns and Refunds</h2>
                    <p>We offer a 30-day return policy for most products, subject to the following conditions:</p>
                    <ul>
                        <li>Items must be unused and in original packaging</li>
                        <li>Return authorization required</li>
                        <li>Customer responsible for return shipping costs (unless defective)</li>
                        <li>Refunds processed within 5-7 business days of receipt</li>
                    </ul>
                    <p>Certain items, such as perishable goods and personalized products, are not eligible for return.</p>
                </div>

                <div class="terms-section">
                    <h2>7. Veterinary and Grooming Services</h2>

                    <h3>Service Appointments</h3>
                    <p>Veterinary and grooming appointments are subject to availability. Cancellations must be made at least 24 hours in advance. Late cancellations or no-shows may be subject to fees.</p>

                    <h3>Medical Services</h3>
                    <p>Our veterinary services are provided by licensed professionals. We are not responsible for outcomes of veterinary care, which are at the discretion of our veterinary staff.</p>

                    <h3>Service Guarantees</h3>
                    <p>We stand behind our services but cannot guarantee specific outcomes. Customer satisfaction is our priority, and we will work to resolve any concerns.</p>
                </div>

                <div class="terms-section">
                    <h2>8. Pet Boarding Services</h2>
                    <p>Pet boarding services require current vaccination records and health certificates. We reserve the right to refuse service based on the health or behavior of your pet. All pets must be picked up by the agreed-upon time, or additional fees may apply.</p>
                </div>

                <div class="terms-section">
                    <h2>9. User Conduct</h2>
                    <p>You agree not to use our services to:</p>
                    <ul>
                        <li>Violate any applicable laws or regulations</li>
                        <li>Infringe on the rights of others</li>
                        <li>Transmit harmful or malicious code</li>
                        <li>Attempt to gain unauthorized access to our systems</li>
                        <li>Submit false or misleading information</li>
                        <li>Harass, abuse, or harm animals</li>
                    </ul>
                </div>

                <div class="terms-section">
                    <h2>10. Intellectual Property</h2>
                    <p>All content on our website, including text, graphics, logos, and software, is owned by <?php echo APP_NAME; ?> or our licensors and is protected by copyright and trademark laws. You may not reproduce, distribute, or create derivative works without our express written permission.</p>
                </div>

                <div class="terms-section">
                    <h2>11. Disclaimers and Limitations</h2>

                    <div class="important-note">
                        <h4>Important Disclaimers</h4>
                        <p>Our services are provided "as is" without warranties of any kind. We disclaim all warranties, express or implied, including merchantability and fitness for a particular purpose.</p>
                    </div>

                    <p>We are not liable for:</p>
                    <ul>
                        <li>Indirect, incidental, or consequential damages</li>
                        <li>Loss of profits, data, or business opportunities</li>
                        <li>Service interruptions or technical issues</li>
                        <li>Third-party actions or content</li>
                    </ul>

                    <p>Our total liability shall not exceed the amount paid for the specific service or product in question.</p>
                </div>

                <div class="terms-section">
                    <h2>12. Indemnification</h2>
                    <p>You agree to indemnify and hold <?php echo APP_NAME; ?> harmless from any claims, damages, losses, or expenses arising from your use of our services or violation of these Terms.</p>
                </div>

                <div class="terms-section">
                    <h2>13. Governing Law</h2>
                    <p>These Terms shall be governed by and construed in accordance with the laws of the State of [Your State], without regard to conflict of law principles. Any disputes shall be resolved in the courts of [Your County], [Your State].</p>
                </div>

                <div class="terms-section">
                    <h2>14. Changes to Terms</h2>
                    <p>We reserve the right to modify these Terms at any time. Changes will be effective immediately upon posting on our website. Your continued use of our services constitutes acceptance of the modified Terms.</p>
                </div>

                <div class="terms-section">
                    <h2>15. Contact Information</h2>
                    <p>If you have questions about these Terms, please contact us:</p>

                    <div class="contact-info">
                        <h3>Contact Information</h3>
                        <p><?php echo icon('mail', 16); ?> <strong>Email:</strong> legal@riapetstore.com</p>
                        <p><?php echo icon('phone', 16); ?> <strong>Phone:</strong> (555) 123-4567</p>
                        <p><?php echo icon('marker', 16); ?> <strong>Address:</strong> 123 Pet Street, Animal City, AC 12345</p>
                        <p><?php echo icon('user', 16); ?> <strong>Legal Department:</strong> legal@riapetstore.com</p>
                    </div>
                </div>

                <div class="terms-section">
                    <p><em>By using <?php echo APP_NAME; ?> services, you acknowledge that you have read, understood, and agree to be bound by these Terms of Service.</em></p>
                </div>
            </div>
        </div>
    </div>
</section>

<?php include '../../backend/includes/footer.php'; ?>