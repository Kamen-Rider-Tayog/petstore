    </main>

    <!-- Loading Spinner -->
    <div id="loadingSpinner" class="loading-spinner" style="display: none;">
        <div class="spinner"></div>
    </div>

    <footer class="main-footer">
        <div class="footer-content">
            <div class="footer-section about-section">
                <h3>About Ria Pet Store</h3>
                <p>Your trusted partner in pet care since 2010. We provide quality pets, premium products, and professional grooming services to ensure your furry friends live their best lives.</p>
                <div class="social-links">
                    <a href="#" class="social-link"><i class="icon-facebook"></i></a>
                    <a href="#" class="social-link"><i class="icon-twitter"></i></a>
                    <a href="#" class="social-link"><i class="icon-instagram"></i></a>
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
                    $categories = $conn->query("SELECT category_name FROM categories WHERE parent_id IS NULL ORDER BY category_name LIMIT 8");
                    while ($category = $categories->fetch_assoc()):
                    ?>
                        <li><a href="/petstore/products?category=<?php echo urlencode($category['category_name']); ?>"><?php echo htmlspecialchars($category['category_name']); ?></a></li>
                    <?php endwhile; ?>
                </ul>
            </div>

            <div class="footer-section contact-info">
                <h3>Contact Info</h3>
                <div class="contact-item">
                    <i class="icon-phone"></i>
                    <span>555-PET-STORE</span>
                </div>
                <div class="contact-item">
                    <i class="icon-email"></i>
                    <span>info@petstore.com</span>
                </div>
                <div class="contact-item">
                    <i class="icon-location"></i>
                    <span>123 Pet Street, City, State 12345</span>
                </div>
                <div class="contact-item">
                    <i class="icon-clock"></i>
                    <span>Mon-Fri: 9AM-7PM, Sat: 9AM-5PM</span>
                </div>
            </div>

            <div class="footer-section newsletter">
                <h3>Newsletter</h3>
                <p>Subscribe to get special offers and updates.</p>
                <form class="newsletter-form" method="post" action="/petstore/newsletter_signup">
                    <input type="email" name="email" placeholder="Your email address" required>
                    <button type="submit" class="btn btn-primary">Subscribe</button>
                </form>
            </div>
        </div>

        <div class="footer-bottom">
            <div class="footer-bottom-content">
                <p>&copy; <a href="/petstore/admin/login" style="color: inherit; text-decoration: none; cursor: default;"><?php echo date('Y'); ?></a> Ria Pet Store. All rights reserved.</p>
                <div class="footer-links">
                    <a href="/petstore/privacy">Privacy</a>
                    <a href="/petstore/terms">Terms</a>
                    <a href="/petstore/sitemap">Sitemap</a>
                </div>
            </div>
        </div>
    </footer>

    <link rel="stylesheet" href="<?php echo asset('css/footer.css'); ?>">

    <!-- Scripts -->
    <script src="<?php echo asset('js/main.js'); ?>"></script>
    <script src="<?php echo asset('js/navigation.js'); ?>"></script>
    <script src="<?php echo asset('js/forms.js'); ?>"></script>
    <script src="<?php echo asset('js/animations.js'); ?>"></script>

    <!-- Page-specific scripts can be added here -->
    <?php if (isset($page_scripts)): ?>
        <?php foreach ($page_scripts as $script): ?>
            <script src="<?php echo $script; ?>"></script>
        <?php endforeach; ?>
    <?php endif; ?>
</body>
</html>