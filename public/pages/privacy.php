<?php
require_once __DIR__ . '/../../backend/config/config.php';
require_once __DIR__ . '/../../backend/functions/helpers.php';

// No authentication required - this is a public page

$page_title = "Privacy Policy";
include '../../backend/includes/header.php';
?>

<link rel="stylesheet" href="<?php echo asset('css/pages/privacy.css'); ?>">

<!-- Privacy Policy Content -->
<section class="privacy-content">
    <div class="container">
        <div class="privacy-container">
            <div class="privacy-header">
                <h1>Privacy Policy</h1>
                <p class="last-updated">Last updated: <?php echo date('F j, Y'); ?></p>
            </div>

            <div class="privacy-body">
                <div class="privacy-section">
                    <h2>1. Introduction</h2>
                    <p>Welcome to <?php echo APP_NAME; ?> ("we," "our," or "us"). We are committed to protecting your privacy and ensuring the security of your personal information. This Privacy Policy explains how we collect, use, disclose, and safeguard your information when you visit our website or use our services.</p>
                    <p>By using our website or services, you agree to the collection and use of information in accordance with this policy.</p>
                </div>

                <div class="privacy-section">
                    <h2>2. Information We Collect</h2>

                    <h3>Personal Information</h3>
                    <p>We may collect the following types of personal information:</p>
                    <ul>
                        <li>Name, email address, phone number, and mailing address</li>
                        <li>Payment information (processed securely through third-party providers)</li>
                        <li>Account credentials and preferences</li>
                        <li>Pet information (name, breed, age, medical history)</li>
                        <li>Communication history with our customer service</li>
                    </ul>

                    <h3>Automatically Collected Information</h3>
                    <p>When you visit our website, we automatically collect certain information:</p>
                    <ul>
                        <li>IP address and location information</li>
                        <li>Browser type and version</li>
                        <li>Pages visited and time spent on our site</li>
                        <li>Device information and screen resolution</li>
                        <li>Referral sources</li>
                    </ul>
                </div>

                <div class="privacy-section">
                    <h2>3. How We Use Your Information</h2>
                    <p>We use the information we collect for various purposes:</p>
                    <ul>
                        <li>To provide and maintain our services</li>
                        <li>To process transactions and send related information</li>
                        <li>To communicate with you about orders, services, and promotions</li>
                        <li>To personalize your experience and improve our website</li>
                        <li>To provide customer support and respond to inquiries</li>
                        <li>To send newsletters and marketing communications (with your consent)</li>
                        <li>To comply with legal obligations and protect our rights</li>
                    </ul>
                </div>

                <div class="privacy-section">
                    <h2>4. Information Sharing and Disclosure</h2>
                    <p>We do not sell, trade, or rent your personal information to third parties. We may share your information in the following circumstances:</p>

                    <h3>Service Providers</h3>
                    <p>We may share information with trusted third-party service providers who assist us in operating our website and conducting our business, such as payment processors, shipping companies, and email service providers.</p>

                    <h3>Legal Requirements</h3>
                    <p>We may disclose your information if required by law or in response to legal processes, such as subpoenas or court orders.</p>

                    <h3>Business Transfers</h3>
                    <p>In the event of a merger, acquisition, or sale of assets, your information may be transferred to the new entity.</p>
                </div>

                <div class="privacy-section">
                    <h2>5. Data Security</h2>
                    <p>We implement appropriate technical and organizational measures to protect your personal information against unauthorized access, alteration, disclosure, or destruction. These measures include:</p>
                    <ul>
                        <li>SSL/TLS encryption for data transmission</li>
                        <li>Secure password hashing and storage</li>
                        <li>Regular security audits and updates</li>
                        <li>Limited access to personal information on a need-to-know basis</li>
                        <li>Regular backups and disaster recovery procedures</li>
                    </ul>

                    <div class="highlight-box">
                        <h4>Important Security Note</h4>
                        <p>While we strive to protect your information, no method of transmission over the internet or electronic storage is 100% secure. We cannot guarantee absolute security but are committed to protecting your data to the best of our ability.</p>
                    </div>
                </div>

                <div class="privacy-section">
                    <h2>6. Cookies and Tracking Technologies</h2>
                    <p>We use cookies and similar technologies to enhance your browsing experience:</p>

                    <h3>Essential Cookies</h3>
                    <p>Required for basic website functionality, such as maintaining your shopping cart and login session.</p>

                    <h3>Analytics Cookies</h3>
                    <p>Help us understand how visitors use our website to improve our services.</p>

                    <h3>Marketing Cookies</h3>
                    <p>Used to show relevant advertisements and track campaign effectiveness.</p>

                    <p>You can control cookie settings through your browser preferences. However, disabling certain cookies may affect website functionality.</p>
                </div>

                <div class="privacy-section">
                    <h2>7. Your Rights and Choices</h2>
                    <p>You have the following rights regarding your personal information:</p>
                    <ul>
                        <li><strong>Access:</strong> Request a copy of the personal information we hold about you</li>
                        <li><strong>Correction:</strong> Request correction of inaccurate or incomplete information</li>
                        <li><strong>Deletion:</strong> Request deletion of your personal information (subject to legal requirements)</li>
                        <li><strong>Portability:</strong> Request transfer of your data in a structured format</li>
                        <li><strong>Opt-out:</strong> Unsubscribe from marketing communications at any time</li>
                        <li><strong>Restriction:</strong> Request limitation of how we process your information</li>
                    </ul>
                </div>

                <div class="privacy-section">
                    <h2>8. Children's Privacy</h2>
                    <p>Our services are not intended for children under 13 years of age. We do not knowingly collect personal information from children under 13. If we become aware that we have collected personal information from a child under 13, we will take steps to delete such information.</p>
                </div>

                <div class="privacy-section">
                    <h2>9. International Data Transfers</h2>
                    <p>Your information may be transferred to and processed in countries other than your own. We ensure that such transfers comply with applicable data protection laws and implement appropriate safeguards.</p>
                </div>

                <div class="privacy-section">
                    <h2>10. Changes to This Privacy Policy</h2>
                    <p>We may update this Privacy Policy from time to time. We will notify you of any changes by posting the new policy on this page and updating the "Last updated" date. We encourage you to review this policy periodically.</p>
                </div>

                <div class="privacy-section">
                    <h2>11. Contact Us</h2>
                    <p>If you have any questions about this Privacy Policy or our privacy practices, please contact us:</p>

                    <div class="contact-info">
                        <h3>Contact Information</h3>
                        <p><?php echo icon('mail', 16); ?> <strong>Email:</strong> privacy@riapetstore.com</p>
                        <p><?php echo icon('phone', 16); ?> <strong>Phone:</strong> (555) 123-4567</p>
                        <p><?php echo icon('marker', 16); ?> <strong>Address:</strong> 123 Pet Street, Animal City, AC 12345</p>
                        <p><?php echo icon('user', 16); ?> <strong>Data Protection Officer:</strong> privacy@riapetstore.com</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<?php include '../../backend/includes/footer.php'; ?>