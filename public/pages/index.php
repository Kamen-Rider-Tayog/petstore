<?php
require_once __DIR__ . '/../../backend/includes/header.php';

$page_title       = 'Home';
$page_description = 'Ria Pet Store - Your one-stop shop for pets, supplies, and services.';
$page_styles      = [asset('css/index.css')];

// ── Featured products (products: id, product_name, category, price, sale_price, on_sale, featured) ──
$featured_products = Cache::get('home_featured_products');
if ($featured_products === null) {
    $r = $conn->query(
        "SELECT id, product_name, category, price, sale_price, on_sale, featured
         FROM products
         WHERE featured = 1
         ORDER BY id DESC
         LIMIT 4"
    );
    $featured_products = $r->fetch_all(MYSQLI_ASSOC);
    Cache::put('home_featured_products', $featured_products, 86400);
}

// ── Available pets (pets: id, name, species, breed, age, price, pet_status) ──
$featured_pets = Cache::get('home_featured_pets');
if ($featured_pets === null) {
    $r = $conn->query(
        "SELECT id, name, species, breed, age, price
         FROM pets
         WHERE pet_status = 'available'
         ORDER BY id DESC
         LIMIT 4"
    );
    $featured_pets = $r->fetch_all(MYSQLI_ASSOC);
    Cache::put('home_featured_pets', $featured_pets, 86400);
}

// ── Featured services (services: id, service_name, category, description, price, duration_minutes, featured) ──
$featured_services = Cache::get('home_featured_services');
if ($featured_services === null) {
    $r = $conn->query(
        "SELECT id, service_name, category, description, price, duration_minutes
         FROM services
         WHERE featured = 1
         LIMIT 3"
    );
    $featured_services = $r->fetch_all(MYSQLI_ASSOC);
    Cache::put('home_featured_services', $featured_services, 3600);
}

// ── Latest approved reviews (product_reviews: id, product_id, customer_id, rating, review_text, status) ──
$reviews = Cache::get('home_reviews');
if ($reviews === null) {
    $r = $conn->query(
        "SELECT pr.rating, pr.review_text, pr.created_at,
                p.product_name,
                CONCAT(c.first_name, ' ', LEFT(c.last_name, 1), '.') AS reviewer
         FROM product_reviews pr
         JOIN products  p ON pr.product_id  = p.id
         JOIN customers c ON pr.customer_id = c.id
         WHERE pr.status = 'approved'
         ORDER BY pr.created_at DESC
         LIMIT 3"
    );
    $reviews = $r->fetch_all(MYSQLI_ASSOC);
    Cache::put('home_reviews', $reviews, 3600);
}

// ── Stats ──
$stats = Cache::get('home_stats');
if ($stats === null) {
    $stats = [
        'pets'     => $conn->query("SELECT COUNT(*) FROM pets WHERE pet_status = 'available'")->fetch_row()[0],
        'products' => $conn->query("SELECT COUNT(*) FROM products")->fetch_row()[0],
        'services' => $conn->query("SELECT COUNT(*) FROM services")->fetch_row()[0],
    ];
    Cache::put('home_stats', $stats, 3600);
}
?>

<!-- HERO -->
<section class="hero">
    <div class="hero-content">
        <h1>Find Your Perfect Pet Companion</h1>
        <p>Quality pets, premium supplies, and professional care — all in one place.</p>
        <div class="hero-buttons">
            <a href="/petstore/pets"     class="btn btn-primary">Browse Pets</a>
            <a href="/petstore/products" class="btn btn-outline-white">Shop Products</a>
        </div>
    </div>
</section>

<!-- STATS BAR -->
<section class="stats-bar">
    <div class="stat-item">
        <span class="stat-number"><?php echo $stats['pets']; ?>+</span>
        <span class="stat-label">Pets Available</span>
    </div>
    <div class="stat-item">
        <span class="stat-number"><?php echo $stats['products']; ?>+</span>
        <span class="stat-label">Products</span>
    </div>
    <div class="stat-item">
        <span class="stat-number"><?php echo $stats['services']; ?></span>
        <span class="stat-label">Services</span>
    </div>
    <div class="stat-item">
        <span class="stat-number">10+</span>
        <span class="stat-label">Years of Care</span>
    </div>
</section>

<!-- AVAILABLE PETS -->
<?php if (!empty($featured_pets)): ?>
<section class="home-section">
    <div class="section-header">
        <h2>Available Pets</h2>
        <p>Find your new best friend</p>
    </div>
    <div class="pets-grid">
        <?php foreach ($featured_pets as $pet): ?>
            <div class="pet-card">
                <div class="pet-card-image">
                    <span class="pet-species-badge"><?php echo ucfirst(e($pet['species'])); ?></span>
                </div>
                <div class="pet-card-body">
                    <h3><?php echo e($pet['name']); ?></h3>
                    <?php if ($pet['breed']): ?>
                        <p class="pet-breed"><?php echo e($pet['breed']); ?></p>
                    <?php endif; ?>
                    <p class="pet-age"><?php echo (int)$pet['age']; ?> <?php echo $pet['age'] == 1 ? 'year' : 'years'; ?> old</p>
                    <p class="pet-price"><?php echo CURRENCY_SYMBOL . number_format($pet['price'], 2); ?></p>
                </div>
                <div class="pet-card-footer">
                    <a href="/petstore/pet_details?id=<?php echo (int)$pet['id']; ?>"
                       class="btn btn-primary btn-small">
                        Meet <?php echo e($pet['name']); ?>
                    </a>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
    <div class="section-footer">
        <a href="/petstore/pets" class="btn btn-outline">View All Pets</a>
    </div>
</section>
<?php endif; ?>

<!-- FEATURED PRODUCTS -->
<?php if (!empty($featured_products)): ?>
<section class="home-section home-section-alt">
    <div class="section-header">
        <h2>Featured Products</h2>
        <p>Premium supplies for your pet</p>
    </div>
    <div class="products-grid">
        <?php foreach ($featured_products as $product): ?>
            <div class="product-card">
                <div class="product-info">
                    <?php if ($product['on_sale']): ?>
                        <span class="badge sale">Sale</span>
                    <?php elseif ($product['featured']): ?>
                        <span class="badge featured">Featured</span>
                    <?php endif; ?>
                    <h3>
                        <a href="/petstore/product_details?id=<?php echo (int)$product['id']; ?>">
                            <?php echo e($product['product_name']); ?>
                        </a>
                    </h3>
                    <p class="product-category"><?php echo ucfirst(e($product['category'])); ?></p>
                    <div class="product-price">
                        <?php if ($product['on_sale'] && $product['sale_price']): ?>
                            <span class="original-price"><?php echo CURRENCY_SYMBOL . number_format($product['price'], 2); ?></span>
                            <span class="sale-price"><?php echo CURRENCY_SYMBOL . number_format($product['sale_price'], 2); ?></span>
                        <?php else: ?>
                            <span class="regular-price"><?php echo CURRENCY_SYMBOL . number_format($product['price'], 2); ?></span>
                        <?php endif; ?>
                    </div>
                </div>
                <div class="product-actions">
                    <a href="/petstore/product_details?id=<?php echo (int)$product['id']; ?>"
                       class="btn btn-outline btn-small">View</a>
                    <button data-add-to-cart="<?php echo (int)$product['id']; ?>"
                            class="btn btn-primary btn-small">Add to Cart</button>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
    <div class="section-footer">
        <a href="/petstore/products" class="btn btn-outline">View All Products</a>
    </div>
</section>
<?php endif; ?>

<!-- SERVICES -->
<?php if (!empty($featured_services)): ?>
<section class="home-section">
    <div class="section-header">
        <h2>Our Services</h2>
        <p>Professional care for your pet</p>
    </div>
    <div class="services-grid">
        <?php foreach ($featured_services as $svc): ?>
            <div class="service-card">
                <div class="service-card-body">
                    <h3><?php echo e($svc['service_name']); ?></h3>
                    <p><?php echo e($svc['description']); ?></p>
                    <div class="service-meta">
                        <span class="service-price"><?php echo CURRENCY_SYMBOL . number_format($svc['price'], 2); ?></span>
                        <span class="service-duration"><?php echo (int)$svc['duration_minutes']; ?> min</span>
                    </div>
                </div>
                <a href="/petstore/book_appointment" class="btn btn-outline btn-small">Book Now</a>
            </div>
        <?php endforeach; ?>
    </div>
    <div class="section-footer">
        <a href="/petstore/services" class="btn btn-outline">All Services</a>
    </div>
</section>
<?php endif; ?>

<!-- REVIEWS -->
<?php if (!empty($reviews)): ?>
<section class="home-section home-section-alt">
    <div class="section-header">
        <h2>What Our Customers Say</h2>
        <p>Real reviews from real pet owners</p>
    </div>
    <div class="reviews-grid">
        <?php foreach ($reviews as $review): ?>
            <div class="review-card">
                <div class="review-stars">
                    <?php for ($i = 1; $i <= 5; $i++): ?>
                        <span class="star <?php echo $i <= (int)$review['rating'] ? 'filled' : ''; ?>">&#9733;</span>
                    <?php endfor; ?>
                </div>
                <p class="review-text">"<?php echo e($review['review_text']); ?>"</p>
                <div class="review-meta">
                    <span class="reviewer-name"><?php echo e($review['reviewer']); ?></span>
                    <span class="review-product">on <?php echo e($review['product_name']); ?></span>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
</section>
<?php endif; ?>

<!-- ABOUT / CTA -->
<section class="home-section cta-section">
    <div class="cta-content">
        <h2>About Ria Pet Store</h2>
        <p>We've been caring for pets and their owners since 2010. From finding the perfect companion to keeping them healthy and happy, we're here every step of the way.</p>
        <div class="cta-actions">
            <a href="/petstore/pets"     class="btn btn-primary">Adopt a Pet</a>
            <a href="/petstore/services" class="btn btn-outline">Our Services</a>
        </div>
    </div>
</section>

<?php require_once __DIR__ . '/../../backend/includes/footer.php'; ?>