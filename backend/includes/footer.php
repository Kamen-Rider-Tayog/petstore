    </main><!-- /.container -->

    <footer class="main-footer">
        <div class="footer-content">

            <div class="footer-section about-section">
                <h3>About Ria Pet Store</h3>
                <p>Your trusted partner in pet care since 2010. Quality pets, premium products, and professional grooming services.</p>
                <div class="social-links">
                    <a href="<?php echo FACEBOOK_URL; ?>"  class="social-link" aria-label="Facebook">f</a>
                    <a href="<?php echo TWITTER_URL; ?>"   class="social-link" aria-label="Twitter">t</a>
                    <a href="<?php echo INSTAGRAM_URL; ?>" class="social-link" aria-label="Instagram">in</a>
                </div>
            </div>

            <div class="footer-section quick-links">
                <h3>Quick Links</h3>
                <ul>
                    <li><a href="/petstore/about">About Us</a></li>
                    <li><a href="/petstore/contact">Contact</a></li>
                    <li><a href="/petstore/faq">FAQ</a></li>
                    <li><a href="/petstore/sitemap">Sitemap</a></li>
                    <li><a href="/petstore/privacy">Privacy Policy</a></li>
                    <li><a href="/petstore/terms">Terms of Service</a></li>
                </ul>
            </div>

            <div class="footer-section categories">
                <h3>Categories</h3>
                <ul>
                    <?php
                    // Re-use cached categories – no extra DB query
                    $footer_cats = Cache::get('categories_menu') ?? [];
                    foreach (array_slice($footer_cats, 0, 8) as $cat):
                    ?>
                        <li>
                            <a href="/petstore/products?category=<?php echo urlencode($cat['category_name']); ?>">
                                <?php echo e($cat['category_name']); ?>
                            </a>
                        </li>
                    <?php endforeach; ?>
                </ul>
            </div>

            <div class="footer-section contact-info">
                <h3>Contact Info</h3>
                <div class="contact-item"><span><?php echo SITE_PHONE; ?></span></div>
                <div class="contact-item"><span><?php echo SITE_EMAIL; ?></span></div>
                <div class="contact-item"><span>123 Pet Street, City 12345</span></div>
                <div class="contact-item"><span>Mon-Fri: 9AM-7PM &bull; Sat: 9AM-5PM</span></div>
            </div>

            <?php if (defined('ENABLE_NEWSLETTER') && ENABLE_NEWSLETTER): ?>
            <div class="footer-section newsletter">
                <h3>Newsletter</h3>
                <p>Subscribe for special offers and updates.</p>
                <form class="newsletter-form" method="post" action="/petstore/newsletter_signup">
                    <input type="email" name="email" placeholder="Your email" required>
                    <button type="submit" class="btn btn-primary btn-small">Subscribe</button>
                </form>
            </div>
            <?php endif; ?>

        </div>

        <div class="footer-bottom">
            <div class="footer-bottom-content">
                <p>&copy; <?php echo date('Y'); ?> Ria Pet Store. All rights reserved.</p>
                <div class="footer-links">
                    <a href="/petstore/privacy">Privacy</a>
                    <a href="/petstore/terms">Terms</a>
                    <a href="/petstore/sitemap">Sitemap</a>
                </div>
            </div>
        </div>
    </footer>

    <!-- Page loading overlay (used by AJAX calls) -->
    <div id="loadingSpinner" class="loading-spinner" style="display:none;" aria-hidden="true">
        <div class="spinner"></div>
    </div>

    <!-- Scripts: non-blocking, at end of body -->
    <script src="<?php echo asset('js/cart.js'); ?>"></script>
    <script src="<?php echo asset('js/main.js'); ?>"></script>

    <?php if (isset($page_scripts)): ?>
        <?php foreach ($page_scripts as $script): ?>
            <script src="<?php echo $script; ?>"></script>
        <?php endforeach; ?>
    <?php endif; ?>

    <script>
    // Init cart UI once scripts are loaded
    if (typeof renderMiniCart  === 'function') renderMiniCart();
    if (typeof updateCartCount === 'function') updateCartCount();
    </script>
</body>
</html>
