<?php
require_once __DIR__ . '/../../backend/config/config.php';
require_once __DIR__ . '/../../backend/config/database.php';
require_once __DIR__ . '/../../backend/functions/helpers.php';

// No authentication required - this is a public page

// Get categories and some sample data for the sitemap
$petCategories = [];
$productCategories = [];

try {
    // Check if tables exist
    $petsTable = $conn->query("SHOW TABLES LIKE 'pets'");
    if ($petsTable && $petsTable->num_rows > 0) {
        $stmt = $conn->prepare("SELECT DISTINCT category FROM pets WHERE status = 'available' ORDER BY category");
        $stmt->execute();
        $result = $stmt->get_result();
        $petCategories = array_column($result->fetch_all(MYSQLI_ASSOC), 'category');
    }

    $productsTable = $conn->query("SHOW TABLES LIKE 'products'");
    if ($productsTable && $productsTable->num_rows > 0) {
        $stmt = $conn->prepare("SELECT DISTINCT category FROM products WHERE status = 'active' ORDER BY category");
        $stmt->execute();
        $result = $stmt->get_result();
        $productCategories = array_column($result->fetch_all(MYSQLI_ASSOC), 'category');
    }
} catch (Exception $e) {
    error_log("Sitemap Error: " . $e->getMessage());
}

$page_title = "Sitemap";
include '../../backend/includes/header.php';
?>

<link rel="stylesheet" href="<?php echo asset('css/sitemap.css'); ?>">


<!-- Quick Navigation -->
<section class="quick-nav">
    <div class="container">
        <h2>Quick Navigation</h2>
        <div class="nav-buttons">
            <a href="<?php echo url(''); ?>" class="nav-btn">
                <?php echo icon('home', 18); ?> Home
            </a>
            <a href="<?php echo url('pets'); ?>" class="nav-btn">
                <?php echo icon('paw', 18); ?> Browse Pets
            </a>
            <a href="<?php echo url('products'); ?>" class="nav-btn">
                <?php echo icon('package', 18); ?> Shop Products
            </a>
            <a href="<?php echo url('contact'); ?>" class="nav-btn">
                <?php echo icon('message', 18); ?> Contact Us
            </a>
        </div>
    </div>
</section>

<!-- Sitemap Content -->
<section class="sitemap-content">
    <div class="container">
        <div class="sitemap-grid">
            <!-- Main Pages -->
            <div class="sitemap-section">
                <div class="section-header">
                    <?php echo icon('home', 20); ?>
                    <h2>Main Pages</h2>
                </div>
                <div class="section-content">
                    <ul class="sitemap-list">
                        <li class="sitemap-item">
                            <a href="<?php echo url(''); ?>" class="sitemap-link">
                                <?php echo icon('home', 16); ?> Home
                            </a>
                        </li>
                        <li class="sitemap-item">
                            <a href="<?php echo url('about'); ?>" class="sitemap-link">
                                <?php echo icon('info', 16); ?> About Us
                            </a>
                        </li>
                        <li class="sitemap-item">
                            <a href="<?php echo url('contact'); ?>" class="sitemap-link">
                                <?php echo icon('message', 16); ?> Contact Us
                            </a>
                        </li>
                        <li class="sitemap-item">
                            <a href="<?php echo url('faq'); ?>" class="sitemap-link">
                                <?php echo icon('help', 16); ?> FAQ
                            </a>
                        </li>
                    </ul>
                </div>
            </div>

            <!-- Pet Services -->
            <div class="sitemap-section">
                <div class="section-header">
                    <?php echo icon('paw', 20); ?>
                    <h2>Pet Services</h2>
                </div>
                <div class="section-content">
                    <ul class="sitemap-list">
                        <li class="sitemap-item">
                            <a href="<?php echo url('pets'); ?>" class="sitemap-link">
                                <?php echo icon('paw', 16); ?> Available Pets
                            </a>
                            <?php if (!empty($petCategories)): ?>
                            <ul class="sitemap-submenu">
                                <?php foreach (array_slice($petCategories, 0, 5) as $category): ?>
                                <li class="sitemap-item">
                                    <a href="<?php echo url('pets?category=' . urlencode($category)); ?>" class="sitemap-link">
                                        <?php echo icon('circle', 8); ?> <?php echo htmlspecialchars($category); ?> Pets
                                    </a>
                                </li>
                                <?php endforeach; ?>
                            </ul>
                            <?php endif; ?>
                        </li>
                        <li class="sitemap-item">
                            <a href="<?php echo url('book_appointment'); ?>" class="sitemap-link">
                                <?php echo icon('calendar', 16); ?> Book Appointment
                            </a>
                        </li>
                        <li class="sitemap-item">
                            <a href="<?php echo url('services'); ?>" class="sitemap-link">
                                <?php echo icon('heart', 16); ?> Grooming Services
                            </a>
                        </li>
                    </ul>
                </div>
            </div>

            <!-- Products -->
            <div class="sitemap-section">
                <div class="section-header">
                    <?php echo icon('package', 20); ?>
                    <h2>Products & Shopping</h2>
                </div>
                <div class="section-content">
                    <ul class="sitemap-list">
                        <li class="sitemap-item">
                            <a href="<?php echo url('products'); ?>" class="sitemap-link">
                                <?php echo icon('package', 16); ?> All Products
                            </a>
                            <?php if (!empty($productCategories)): ?>
                            <ul class="sitemap-submenu">
                                <?php foreach (array_slice($productCategories, 0, 5) as $category): ?>
                                <li class="sitemap-item">
                                    <a href="<?php echo url('products?category=' . urlencode($category)); ?>" class="sitemap-link">
                                        <?php echo icon('circle', 8); ?> <?php echo htmlspecialchars($category); ?>
                                    </a>
                                </li>
                                <?php endforeach; ?>
                            </ul>
                            <?php endif; ?>
                        </li>
                        <li class="sitemap-item">
                            <a href="<?php echo url('cart'); ?>" class="sitemap-link">
                                <?php echo icon('cart', 16); ?> Shopping Cart
                            </a>
                        </li>
                        <li class="sitemap-item">
                            <a href="<?php echo url('checkout'); ?>" class="sitemap-link">
                                <?php echo icon('credit-card', 16); ?> Checkout
                            </a>
                        </li>
                        <li class="sitemap-item">
                            <a href="<?php echo url('featured'); ?>" class="sitemap-link">
                                <?php echo icon('star', 16); ?> Featured Products
                            </a>
                        </li>
                        <li class="sitemap-item">
                            <a href="<?php echo url('on_sale'); ?>" class="sitemap-link">
                                <?php echo icon('tag', 16); ?> On Sale
                            </a>
                        </li>
                    </ul>
                </div>
            </div>

            <!-- Customer Account -->
            <div class="sitemap-section">
                <div class="section-header">
                    <?php echo icon('user', 20); ?>
                    <h2>Customer Account</h2>
                </div>
                <div class="section-content">
                    <ul class="sitemap-list">
                        <li class="sitemap-item">
                            <a href="<?php echo url('login'); ?>" class="sitemap-link">
                                <?php echo icon('user', 16); ?> Login
                            </a>
                        </li>
                        <li class="sitemap-item">
                            <a href="<?php echo url('register'); ?>" class="sitemap-link">
                                <?php echo icon('user-plus', 16); ?> Register
                            </a>
                        </li>
                        <li class="sitemap-item">
                            <a href="<?php echo url('user_profile'); ?>" class="sitemap-link">
                                <?php echo icon('user', 16); ?> My Profile
                            </a>
                        </li>
                        <li class="sitemap-item">
                            <a href="<?php echo url('order_history'); ?>" class="sitemap-link">
                                <?php echo icon('package', 16); ?> My Orders
                            </a>
                        </li>
                        <li class="sitemap-item">
                            <a href="<?php echo url('my_appointments'); ?>" class="sitemap-link">
                                <?php echo icon('calendar', 16); ?> My Appointments
                            </a>
                        </li>
                    </ul>
                </div>
            </div>

            <!-- Information -->
            <div class="sitemap-section">
                <div class="section-header">
                    <?php echo icon('info', 20); ?>
                    <h2>Information</h2>
                </div>
                <div class="section-content">
                    <ul class="sitemap-list">
                        <li class="sitemap-item">
                            <a href="<?php echo url('privacy'); ?>" class="sitemap-link">
                                <?php echo icon('lock', 16); ?> Privacy Policy
                            </a>
                        </li>
                        <li class="sitemap-item">
                            <a href="<?php echo url('terms'); ?>" class="sitemap-link">
                                <?php echo icon('file', 16); ?> Terms of Service
                            </a>
                        </li>
                        <li class="sitemap-item">
                            <a href="<?php echo url('faq'); ?>" class="sitemap-link">
                                <?php echo icon('help', 16); ?> FAQ
                            </a>
                        </li>
                        <li class="sitemap-item">
                            <a href="<?php echo url('contact'); ?>" class="sitemap-link">
                                <?php echo icon('message', 16); ?> Contact Us
                            </a>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Last Updated -->
<div class="last-updated">
    <p>Sitemap last updated: <?php echo date('F j, Y \a\t g:i A'); ?></p>
</div>

<?php include '../../backend/includes/footer.php'; ?>