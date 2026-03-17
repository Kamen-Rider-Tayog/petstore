</main><!-- /.container -->

    <footer class="main-footer">

        <!-- Top: tagline + newsletter -->
        <div class="footer-top">
            <div class="footer-tagline">
                <h2><?php echo APP_NAME; ?></h2>
                <h4>Your pet's happiness is our priority.</h4>
            </div>
            <div class="footer-newsletter-wrap">
                <p class="footer-newsletter-label">Subscribe to our newsletter</p>
                <?php if (defined('ENABLE_NEWSLETTER') && ENABLE_NEWSLETTER): ?>
                <form class="newsletter-form" method="post" action="<?php echo url('newsletter_signup'); ?>">
                    <div class="newsletter-input-wrap">
                        <input type="email" name="email" placeholder="Enter your email address" required>
                        <button type="submit" class="btn btn-primary" aria-label="Subscribe">
                            <?php echo icon('mail', 18); ?>
                        </button>
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
                    <?php echo icon('mail', 16, '', true); ?>
                    <span><?php echo SITE_EMAIL; ?></span>
                </div>
                <div class="contact-item">
                    <?php echo icon('phone', 16, '', true); ?>
                    <span><?php echo SITE_PHONE; ?></span>
                </div>
                <div class="contact-item">
                    <?php echo icon('marker', 16, '', true); ?>
                    <span>123 Pet Street, City 12345</span>
                </div>
            </div>

            <div class="footer-section">
                <h4>Shop</h4>
                <ul>
                    <li><a href="<?php echo url('products'); ?>"><?php echo icon('package', 16); ?> All Products</a></li>
                    <li><a href="<?php echo url('pets'); ?>"><?php echo icon('paw', 16); ?> Pets</a></li>
                    <li><a href="<?php echo url('services'); ?>"><?php echo icon('heart', 16); ?> Services</a></li>
                    <li><a href="<?php echo url('featured'); ?>"><?php echo icon('star', 16); ?> Featured</a></li>
                </ul>
            </div>

            <div class="footer-section">
                <h4>Customer Service</h4>
                <ul>
                    <li><a href="<?php echo url('faq'); ?>"><?php echo icon('help', 16); ?> FAQ</a></li>
                    <li><a href="<?php echo url('contact'); ?>"><?php echo icon('message', 16); ?> Contact Us</a></li>
                </ul>
            </div>

            <div class="footer-section footer-social">
                <h4>Follow Us</h4>
                <div class="social-links">
                    <a href="<?php echo FACEBOOK_URL; ?>" class="social-link" aria-label="Facebook" target="_blank" rel="noopener">
                        <?php echo icon('facebook', 20); ?>
                    </a>
                    <a href="<?php echo INSTAGRAM_URL; ?>" class="social-link" aria-label="Instagram" target="_blank" rel="noopener">
                        <?php echo icon('instagram', 20); ?>
                    </a>
                    <a href="#" class="social-link" aria-label="TikTok" target="_blank" rel="noopener">
                        <?php echo icon('tiktok', 20); ?>
                    </a>
                </div>
            </div>
        </div>

        <div class="footer-divider"></div>

        <!-- Bottom bar -->
        <div class="footer-bottom-wrap">
            <div class="footer-bottom">
                <p class="footer-made">Made with <?php echo icon('heart-filled', 14, 'heart', true); ?> by Kagame</p>
                <p class="footer-copy">&copy; <?php echo date('Y'); ?> <?php echo APP_NAME; ?>. All rights reserved.</p>
                <div class="footer-legal">
                    <a href="<?php echo url('privacy'); ?>">Privacy</a>
                    <a href="<?php echo url('terms'); ?>">Terms</a>
                    <a href="<?php echo url('sitemap'); ?>">Sitemap</a>
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