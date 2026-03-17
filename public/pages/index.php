<?php
// Set page meta BEFORE header loads (header uses these variables)
$page_title       = 'Home';
$page_description = 'Ria Pet Store - Your one-stop shop for pets, supplies, and services.';

require_once __DIR__ . '/../../backend/includes/header.php';

// Now asset() is available — add page-specific CSS
$page_styles = [asset('css/index.css')];

// ── Featured products ──────────────────────────────────────
$featured_products = Cache::get('home_featured_products');
if ($featured_products === null) {
    $r = $conn->query(
        "SELECT id, product_name, category, price, sale_price, on_sale, featured
         FROM products WHERE featured = 1 ORDER BY id DESC LIMIT 4"
    );
    $featured_products = $r->fetch_all(MYSQLI_ASSOC);
    Cache::put('home_featured_products', $featured_products, 86400);
}

// ── Available pets ─────────────────────────────────────────
$featured_pets = Cache::get('home_featured_pets');
if ($featured_pets === null) {
    $r = $conn->query(
        "SELECT id, name, species, breed, age
         FROM pets WHERE pet_status = 'available' ORDER BY id DESC LIMIT 4"
    );
    $featured_pets = $r->fetch_all(MYSQLI_ASSOC);
    Cache::put('home_featured_pets', $featured_pets, 86400);
}

// ── Featured services ──────────────────────────────────────
$featured_services = Cache::get('home_featured_services');
if ($featured_services === null) {
    $r = $conn->query(
        "SELECT id, service_name, category, description, price, duration_minutes
         FROM services WHERE featured = 1 LIMIT 3"
    );
    $featured_services = $r->fetch_all(MYSQLI_ASSOC);
    Cache::put('home_featured_services', $featured_services, 3600);
}

// ── Latest approved reviews ────────────────────────────────
$reviews = Cache::get('home_reviews');
if ($reviews === null) {
    $r = $conn->query(
        "SELECT pr.rating, pr.review_text,
                p.product_name,
                CONCAT(c.first_name, ' ', LEFT(c.last_name, 1), '.') AS reviewer
         FROM product_reviews pr
         JOIN products  p ON pr.product_id  = p.id
         JOIN customers c ON pr.customer_id = c.id
         WHERE pr.status = 'approved'
         ORDER BY pr.created_at DESC LIMIT 3"
    );
    $reviews = $r->fetch_all(MYSQLI_ASSOC);
    Cache::put('home_reviews', $reviews, 3600);
}

// Inject index.css into the page now that header has already output <head>
// We do this via inline style injection since header already closed <head>
echo '<link rel="stylesheet" href="' . asset('css/index.css') . '?v=' . ASSET_VERSION . '">';
?>

<!-- HERO -->
<section class="hero">
    <div class="hero-content">
        <h1>Find Your Perfect Pet Companion</h1>
        <p>Quality pets, premium supplies, and professional care — all in one place.</p>
        <div class="hero-buttons">
            <a href="<?php echo url('pets'); ?>" class="btn btn-primary">
                <?php echo icon('paw', 18); ?> Browse Pets
            </a>
            <a href="<?php echo url('products'); ?>" class="btn btn-outline-white">
                <?php echo icon('package', 18); ?> Shop Products
            </a>
        </div>
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
                    <?php if (!empty($pet['breed'])): ?>
                        <p class="pet-breed"><?php echo e($pet['breed']); ?></p>
                    <?php endif; ?>
                    <p class="pet-age"><?php echo (int)$pet['age']; ?> <?php echo $pet['age'] == 1 ? 'year' : 'years'; ?> old</p>
                </div>
                <div class="pet-card-footer">
                    <a href="<?php echo url('pet_details?id=' . (int)$pet['id']); ?>" class="btn btn-primary btn-small">
                        Meet <?php echo e($pet['name']); ?> <?php echo icon('arrow-right', 14); ?>
                    </a>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
    <div class="section-footer">
        <a href="<?php echo url('pets'); ?>" class="btn btn-outline">
            View All Pets <?php echo icon('arrow-right', 16); ?>
        </a>
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
                    <!-- Badges removed -->
                    <h3>
                        <a href="<?php echo url('product_details?id=' . (int)$product['id']); ?>">
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
                    <a href="<?php echo url('product_details?id=' . (int)$product['id']); ?>" class="btn btn-outline btn-small">
                        <?php echo icon('eye', 14); ?> View
                    </a>
                    <button data-add-to-cart="<?php echo (int)$product['id']; ?>" class="btn btn-primary btn-small">
                        <?php echo icon('cart', 14); ?> Add
                    </button>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
    <div class="section-footer">
        <a href="<?php echo url('products'); ?>" class="btn btn-outline">
            View All Products <?php echo icon('arrow-right', 16); ?>
        </a>
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
                <a href="<?php echo url('book_appointment'); ?>" class="btn btn-outline btn-small">
                    <?php echo icon('calendar', 14); ?> Book Now
                </a>
            </div>
        <?php endforeach; ?>
    </div>
    <div class="section-footer">
        <a href="<?php echo url('services'); ?>" class="btn btn-outline">
            All Services <?php echo icon('arrow-right', 16); ?>
        </a>
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
                    <?php echo star_rating($review['rating'], 16); ?>
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
            <a href="<?php echo url('pets'); ?>" class="btn btn-primary">
                <?php echo icon('paw', 18); ?> Adopt a Pet
            </a>
            <a href="<?php echo url('services'); ?>" class="btn btn-outline">
                <?php echo icon('heart', 18); ?> Our Services
            </a>
        </div>
    </div>
</section>

<?php require_once __DIR__ . '/../../backend/includes/footer.php'; ?>