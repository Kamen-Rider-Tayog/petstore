<?php
require_once '../../backend/includes/header.php';

// Get featured pets
$featured_pets = $conn->query("SELECT * FROM pets WHERE featured = 1 LIMIT 4");

// Get featured products
$featured_products = $conn->query("SELECT * FROM products WHERE featured = 1 LIMIT 4");

// Get popular services (simplified - using service_type from appointments)
$popular_services = $conn->query("SELECT DISTINCT service_type FROM appointments WHERE status = 'completed' LIMIT 3");

// Get testimonials
$testimonials = $conn->query("SELECT r.*, c.first_name, c.last_name, p.name as product_name FROM product_reviews r JOIN customers c ON r.customer_id = c.id JOIN products p ON r.product_id = p.id WHERE r.status = 'approved' ORDER BY r.created_at DESC LIMIT 3");
?>
<!-- Hero Section -->
<section class="hero">
        <div class="hero-content">
            <h1>Welcome to Pet Paradise</h1>
            <p>Your one-stop destination for all your pet needs. From adorable pets to premium products, we have everything your furry friends need to live their best life.</p>
            <div class="hero-buttons">
                <a href="/petstore/pets" class="btn btn-primary">Shop Pets</a>
                <a href="/petstore/products" class="btn btn-secondary">Shop Products</a>
                <a href="/petstore/book_appointment" class="btn btn-outline">Book Service</a>
            </div>
        </div>
        <div class="hero-image">
            <img src="../../assets/images/hero-pets.jpg" alt="Happy pets" onerror="this.onerror=null; this.src='../../assets/images/placeholder-hero.jpg'">
        </div>
    </section>

    <!-- Featured Pets Section -->
    <section class="featured-section">
        <div class="container">
            <h2>Featured Pets</h2>
            <p>Meet some of our most beloved pets waiting for their forever homes</p>

            <div class="pets-grid">
                <?php while ($pet = $featured_pets->fetch_assoc()): ?>
                    <div class="pet-card">
                        <div class="pet-image">
                            <img src="../../assets/images/pets/<?php echo $pet['id']; ?>.jpg" alt="<?php echo htmlspecialchars($pet['name']); ?>" onerror="this.onerror=null; this.src='../../assets/images/placeholder-pet.jpg'">
                            <?php if ($pet['featured']): ?>
                                <span class="badge featured">Featured</span>
                            <?php endif; ?>
                        </div>
                        <div class="pet-info">
                            <h3><?php echo htmlspecialchars($pet['name']); ?></h3>
                            <p class="pet-details"><?php echo htmlspecialchars($pet['species']); ?> • <?php echo htmlspecialchars($pet['breed']); ?> • <?php echo $pet['age']; ?> years old</p>
                            <p class="pet-description"><?php echo htmlspecialchars(substr($pet['description'], 0, 100)); ?>...</p>
                            <div class="pet-price">$<?php echo number_format($pet['price'], 2); ?></div>
                            <a href="/petstore/pet_details?id=<?php echo $pet['id']; ?>" class="btn btn-small">View Details</a>
                        </div>
                    </div>
                <?php endwhile; ?>
            </div>

            <div class="section-footer">
                <a href="/petstore/pets" class="btn btn-outline">View All Pets</a>
            </div>
        </div>
    </section>

    <!-- Featured Products Section -->
    <section class="featured-section products-section">
        <div class="container">
            <h2>Featured Products</h2>
            <p>Premium products for your pet's health and happiness</p>

            <div class="products-grid">
                <?php while ($product = $featured_products->fetch_assoc()): ?>
                    <div class="product-card">
                        <div class="product-image">
                            <img src="../../assets/images/products/<?php echo $product['id']; ?>.jpg" alt="<?php echo htmlspecialchars($product['name']); ?>" onerror="this.onerror=null; this.src='../../assets/images/placeholder-product.jpg'">
                            <?php if ($product['featured']): ?>
                                <span class="badge featured">Featured</span>
                            <?php endif; ?>
                            <?php if ($product['on_sale']): ?>
                                <span class="badge sale">Sale</span>
                            <?php endif; ?>
                        </div>
                        <div class="product-info">
                            <h3><?php echo htmlspecialchars($product['name']); ?></h3>
                            <p class="product-category"><?php echo htmlspecialchars($product['category']); ?></p>
                            <div class="product-price">
                                <?php if ($product['on_sale'] && $product['sale_price']): ?>
                                    <span class="original-price">$<?php echo number_format($product['price'], 2); ?></span>
                                    <span class="sale-price">$<?php echo number_format($product['sale_price'], 2); ?></span>
                                <?php else: ?>
                                    <span class="regular-price">$<?php echo number_format($product['price'], 2); ?></span>
                                <?php endif; ?>
                            </div>
                            <div class="product-rating">
                                <?php
                                $rating = $product['rating'] ?? 0;
                                for ($i = 1; $i <= 5; $i++) {
                                    echo $i <= $rating ? '★' : '☆';
                                }
                                ?>
                                <span class="review-count">(<?php echo $product['review_count'] ?? 0; ?>)</span>
                            </div>
                        </div>
                        <div class="product-actions">
                            <a href="/petstore/product_details?id=<?php echo $product['id']; ?>" class="btn btn-small">View Details</a>
                            <button onclick="addToCart(<?php echo $product['id']; ?>)" class="btn btn-small btn-primary">Add to Cart</button>
                        </div>
                    </div>
                <?php endwhile; ?>
            </div>

            <div class="section-footer">
                <a href="/petstore/products" class="btn btn-outline">View All Products</a>
            </div>
        </div>
    </section>

    <!-- Services Section -->
    <section class="services-section">
        <div class="container">
            <h2>Our Services</h2>
            <p>Professional care for your beloved pets</p>

            <div class="services-grid">
                <?php if ($popular_services && $popular_services->num_rows > 0): ?>
                    <?php while ($service = $popular_services->fetch_assoc()): ?>
                        <div class="service-card">
                            <div class="service-icon">
                                <i class="icon-<?php echo strtolower(str_replace(' ', '-', $service['service_type'])); ?>"></i>
                            </div>
                            <h3><?php echo ucfirst(htmlspecialchars($service['service_type'])); ?></h3>
                            <p>Professional <?php echo htmlspecialchars($service['service_type']); ?> services for your pets</p>
                            <a href="/petstore/book_appointment" class="btn btn-small">Book Now</a>
                        </div>
                    <?php endwhile; ?>
                <?php else: ?>
                    <div class="service-card">
                        <div class="service-icon"><i class="icon-services"></i></div>
                        <h3>Professional Services</h3>
                        <p>Quality pet care services available</p>
                        <a href="/petstore/book_appointment" class="btn btn-small">Book Now</a>
                    </div>
                <?php endif; ?>
            </div>

            <div class="section-footer">
                <a href="/petstore/products" class="btn btn-outline">View All Services</a>
            </div>
        </div>
    </section>

    <!-- Testimonials Section -->
    <section class="testimonials-section">
        <div class="container">
            <h2>What Our Customers Say</h2>
            <p>Don't just take our word for it - hear from our happy customers</p>

            <div class="testimonials-grid">
                <?php while ($testimonial = $testimonials->fetch_assoc()): ?>
                    <div class="testimonial-card">
                        <div class="testimonial-rating">
                            <?php
                            for ($i = 1; $i <= 5; $i++) {
                                echo $i <= $testimonial['rating'] ? '★' : '☆';
                            }
                            ?>
                        </div>
                        <blockquote><?php echo htmlspecialchars($testimonial['review_text']); ?></blockquote>
                        <cite>
                            <strong><?php echo htmlspecialchars($testimonial['first_name'] . ' ' . $testimonial['last_name']); ?></strong>
                            <br>
                            <small>About <?php echo htmlspecialchars($testimonial['product_name']); ?></small>
                        </cite>
                    </div>
                <?php endwhile; ?>
            </div>
        </div>
    </section>

    <!-- Newsletter Section -->
    <section class="newsletter-section">
        <div class="container">
            <div class="newsletter-content">
                <h2>Stay Updated</h2>
                <p>Subscribe to our newsletter for the latest pet care tips, special offers, and new arrivals.</p>
                <form class="newsletter-form" method="post" action="newsletter_signup.php">
                    <input type="email" name="email" placeholder="Enter your email address" required>
                    <button type="submit" class="btn btn-primary">Subscribe</button>
                </form>
            </div>
        </div>
    </section>

<?php include '../../backend/includes/footer.php'; ?>
