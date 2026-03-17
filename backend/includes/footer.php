</main><!-- /.container -->

    <footer class="main-footer">

        <!-- Top: tagline + newsletter -->
        <div class="footer-top">
            <div class="footer-tagline">
                <h2>Ria Pet Store</h2>
                <h4>Your pet's happiness is our priority.</h4>
            </div>
            <div class="footer-newsletter-wrap">
                <p class="footer-newsletter-label">Subscribe to our newsletter</p>
                <?php if (defined('ENABLE_NEWSLETTER') && ENABLE_NEWSLETTER): ?>
                <form class="newsletter-form" method="post" action="/Ria-Pet-Store/newsletter_signup">
                    <div class="newsletter-input-wrap">
                        <input type="email" name="email" placeholder="Enter your email address" required>
                        <button type="submit" class="btn btn-primary">Subscribe</button>
                    </div>
                </form>
                <?php endif; ?>
            </div>
        </div>

        <!-- Divider -->
        <div class="footer-divider"></div>

        <!-- Middle: links grid -->
        <div class="footer-content">

            <div class="footer-section footer-about">
                <h4>Contact Information</h4>
                <div class="contact-item">
                    <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"/><polyline points="22,6 12,13 2,6"/></svg>
                    <span><?php echo SITE_EMAIL; ?></span>
                </div>
                <div class="contact-item">
                    <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07A19.5 19.5 0 0 1 4.69 13a19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 3.62 2h3a2 2 0 0 1 2 1.72c.127.96.361 1.903.7 2.81a2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45c.907.339 1.85.573 2.81.7A2 2 0 0 1 22 16.92z"/></svg>
                    <span><?php echo SITE_PHONE; ?></span>
                </div>
                <div class="contact-item">
                    <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"/><circle cx="12" cy="10" r="3"/></svg>
                    <span>123 Pet Street, City 12345</span>
                </div>
            </div>

            <div class="footer-section">
                <h4>Company</h4>
                <ul>
                    <li><a href="/Ria-Pet-Store/products">Products</a></li>
                    <li><a href="/Ria-Pet-Store/pets">Pets</a></li>
                    <li><a href="/Ria-Pet-Store/services">Services</a></li>
                    <li><a href="/Ria-Pet-Store/book_appointment">Book Appointment</a></li>
                </ul>
            </div>

            <div class="footer-section">
                <h4>Help</h4>
                <ul>
                    <li><a href="/Ria-Pet-Store/faq">FAQ</a></li>
                    <li><a href="/Ria-Pet-Store/contact">Contact Us</a></li>
                </ul>
            </div>

            <div class="footer-section">
                <h4>Follow Us</h4>
                <div class="social-links">
                    <a href="<?php echo FACEBOOK_URL; ?>" class="social-link" aria-label="Facebook">
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M18 2h-3a5 5 0 0 0-5 5v3H7v4h3v8h4v-8h3l1-4h-4V7a1 1 0 0 1 1-1h3z"/></svg>
                    </a>
                    <a href="<?php echo INSTAGRAM_URL; ?>" class="social-link" aria-label="Instagram">
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="2" y="2" width="20" height="20" rx="5" ry="5"/><circle cx="12" cy="12" r="4"/><circle cx="17.5" cy="6.5" r="1" fill="currentColor" stroke="none"/></svg>
                    </a>
                    <a href="#" class="social-link" aria-label="TikTok">
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M9 12a4 4 0 1 0 4 4V4a5 5 0 0 0 5 5"/></svg>
                    </a>
                </div>
            </div>

        </div>

        <!-- Bottom bar -->
        <div class="footer-bottom-wrap">
            <div class="footer-bottom">
                <p class="footer-made">Made with <span class="heart">&#10084;</span> by Kagame</p>
                <p class="footer-copy">&copy; <?php echo date('Y'); ?> Ria Pet Store. All rights reserved.</p>
                <div class="footer-legal">
                    <a href="/Ria-Pet-Store/privacy">Privacy</a>
                    <a href="/Ria-Pet-Store/terms">Terms</a>
                    <a href="/Ria-Pet-Store/sitemap">Sitemap</a>
                </div>
            </div>
        </div>

    </footer>

    <div id="loadingSpinner" class="loading-spinner" style="display:none;" aria-hidden="true">
        <div class="spinner"></div>
    </div>

    <script src="<?php echo asset('js/cart.js'); ?>?v=<?php echo ASSET_VERSION; ?>"></script>
    <script src="<?php echo asset('js/main.js'); ?>?v=<?php echo ASSET_VERSION; ?>"></script>

    <?php if (!empty($page_scripts)): ?>
        <?php foreach ($page_scripts as $script): ?>
            <script src="<?php echo $script; ?>?v=<?php echo ASSET_VERSION; ?>"></script>
        <?php endforeach; ?>
    <?php endif; ?>

    <script>
    if (typeof renderMiniCart  === 'function') renderMiniCart();
    if (typeof updateCartCount === 'function') updateCartCount();
    </script>
</body>
</html>