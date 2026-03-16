<?php
require_once '../../backend/config/database.php';
require_once '../../backend/includes/auth.php';
require_once '../../backend/includes/header.php';

// Get categories and some sample data for the sitemap
try {
    $stmt = $conn->prepare("SELECT DISTINCT category FROM pets WHERE status = 'available' ORDER BY category");
    $stmt->execute();
    $result = $stmt->get_result();
    $petCategories = array_column($result->fetch_all(MYSQLI_ASSOC), 'category');

    $stmt = $conn->prepare("SELECT DISTINCT category FROM products WHERE status = 'active' ORDER BY category");
    $stmt->execute();
    $result = $stmt->get_result();
    $productCategories = array_column($result->fetch_all(MYSQLI_ASSOC), 'category');
} catch (Exception $e) {
    $petCategories = [];
    $productCategories = [];
    error_log("Sitemap Error: " . $e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sitemap - Pet Store</title>
    <link rel="stylesheet" href="../../assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="../../assets/css/sitemap.css">
</head>
<body>
    <?php include '../../backend/includes/header.php'; ?>

    <!-- Sitemap Hero -->
    <section class="sitemap-hero">
        <div class="container">
            <h1>Website Sitemap</h1>
            <p>Find everything you need on our website</p>
        </div>
    </section>

    <!-- Quick Navigation -->
    <section class="quick-nav">
        <div class="container">
            <h2>Quick Navigation</h2>
            <div class="nav-buttons">
                <a href="../index.php" class="nav-btn">
                    <i class="fas fa-home"></i> Home
                </a>
                <a href="pets.php" class="nav-btn">
                    <i class="fas fa-paw"></i> Browse Pets
                </a>
                <a href="products.php" class="nav-btn">
                    <i class="fas fa-shopping-bag"></i> Shop Products
                </a>
                <a href="contact.php" class="nav-btn">
                    <i class="fas fa-envelope"></i> Contact Us
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
                        <i class="fas fa-home"></i>
                        <h2>Main Pages</h2>
                    </div>
                    <div class="section-content">
                        <ul class="sitemap-list">
                            <li class="sitemap-item">
                                <a href="../index.php" class="sitemap-link">
                                    <i class="fas fa-home"></i> Home
                                </a>
                            </li>
                            <li class="sitemap-item">
                                <a href="about.php" class="sitemap-link">
                                    <i class="fas fa-info-circle"></i> About Us
                                </a>
                            </li>
                            <li class="sitemap-item">
                                <a href="contact.php" class="sitemap-link">
                                    <i class="fas fa-envelope"></i> Contact Us
                                </a>
                            </li>
                            <li class="sitemap-item">
                                <a href="faq.php" class="sitemap-link">
                                    <i class="fas fa-question-circle"></i> FAQ
                                </a>
                            </li>
                        </ul>
                    </div>
                </div>

                <!-- Pet Services -->
                <div class="sitemap-section">
                    <div class="section-header">
                        <i class="fas fa-paw"></i>
                        <h2>Pet Services</h2>
                    </div>
                    <div class="section-content">
                        <ul class="sitemap-list">
                            <li class="sitemap-item">
                                <a href="pets.php" class="sitemap-link">
                                    <i class="fas fa-paw"></i> Available Pets
                                </a>
                                <?php if (!empty($petCategories)): ?>
                                <ul class="sitemap-submenu">
                                    <?php foreach (array_slice($petCategories, 0, 5) as $category): ?>
                                    <li class="sitemap-item">
                                        <a href="pets.php?category=<?php echo urlencode($category); ?>" class="sitemap-link">
                                            <i class="fas fa-circle"></i> <?php echo htmlspecialchars($category); ?> Pets
                                        </a>
                                    </li>
                                    <?php endforeach; ?>
                                </ul>
                                <?php endif; ?>
                            </li>
                            <li class="sitemap-item">
                                <a href="appointments.php" class="sitemap-link">
                                    <i class="fas fa-calendar-alt"></i> Book Appointment
                                </a>
                            </li>
                            <li class="sitemap-item">
                                <a href="services.php" class="sitemap-link">
                                    <i class="fas fa-cut"></i> Grooming Services
                                </a>
                            </li>
                        </ul>
                    </div>
                </div>

                <!-- Products -->
                <div class="sitemap-section">
                    <div class="section-header">
                        <i class="fas fa-shopping-bag"></i>
                        <h2>Products & Shopping</h2>
                    </div>
                    <div class="section-content">
                        <ul class="sitemap-list">
                            <li class="sitemap-item">
                                <a href="products.php" class="sitemap-link">
                                    <i class="fas fa-shopping-bag"></i> All Products
                                </a>
                                <?php if (!empty($productCategories)): ?>
                                <ul class="sitemap-submenu">
                                    <?php foreach (array_slice($productCategories, 0, 5) as $category): ?>
                                    <li class="sitemap-item">
                                        <a href="products.php?category=<?php echo urlencode($category); ?>" class="sitemap-link">
                                            <i class="fas fa-circle"></i> <?php echo htmlspecialchars($category); ?>
                                        </a>
                                    </li>
                                    <?php endforeach; ?>
                                </ul>
                                <?php endif; ?>
                            </li>
                            <li class="sitemap-item">
                                <a href="cart.php" class="sitemap-link">
                                    <i class="fas fa-shopping-cart"></i> Shopping Cart
                                </a>
                            </li>
                            <li class="sitemap-item">
                                <a href="checkout.php" class="sitemap-link">
                                    <i class="fas fa-credit-card"></i> Checkout
                                </a>
                            </li>
                        </ul>
                    </div>
                </div>

                <!-- Customer Account -->
                <div class="sitemap-section">
                    <div class="section-header">
                        <i class="fas fa-user"></i>
                        <h2>Customer Account</h2>
                    </div>
                    <div class="section-content">
                        <ul class="sitemap-list">
                            <li class="sitemap-item">
                                <a href="login.php" class="sitemap-link">
                                    <i class="fas fa-sign-in-alt"></i> Login
                                </a>
                            </li>
                            <li class="sitemap-item">
                                <a href="register.php" class="sitemap-link">
                                    <i class="fas fa-user-plus"></i> Register
                                </a>
                            </li>
                            <li class="sitemap-item">
                                <a href="dashboard.php" class="sitemap-link">
                                    <i class="fas fa-tachometer-alt"></i> My Dashboard
                                </a>
                            </li>
                            <li class="sitemap-item">
                                <a href="customer_order.php" class="sitemap-link">
                                    <i class="fas fa-list"></i> My Orders
                                </a>
                            </li>
                        </ul>
                    </div>
                </div>

                <!-- Information -->
                <div class="sitemap-section">
                    <div class="section-header">
                        <i class="fas fa-info-circle"></i>
                        <h2>Information</h2>
                    </div>
                    <div class="section-content">
                        <ul class="sitemap-list">
                            <li class="sitemap-item">
                                <a href="privacy.php" class="sitemap-link">
                                    <i class="fas fa-shield-alt"></i> Privacy Policy
                                </a>
                            </li>
                            <li class="sitemap-item">
                                <a href="terms.php" class="sitemap-link">
                                    <i class="fas fa-file-contract"></i> Terms of Service
                                </a>
                            </li>
                            <li class="sitemap-item">
                                <a href="low_stock.php" class="sitemap-link">
                                    <i class="fas fa-exclamation-triangle"></i> Low Stock Alerts
                                </a>
                            </li>
                            <li class="sitemap-item">
                                <a href="supplier_products.php" class="sitemap-link">
                                    <i class="fas fa-truck"></i> Supplier Products
                                </a>
                            </li>
                        </ul>
                    </div>
                </div>

                <!-- Admin Panel -->
                <?php if (isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'admin'): ?>
                <div class="sitemap-section">
                    <div class="section-header">
                        <i class="fas fa-cog"></i>
                        <h2>Admin Panel</h2>
                    </div>
                    <div class="section-content">
                        <ul class="sitemap-list">
                            <li class="sitemap-item">
                                <a href="../admin/dashboard.php" class="sitemap-link">
                                    <i class="fas fa-tachometer-alt"></i> Admin Dashboard
                                </a>
                            </li>
                            <li class="sitemap-item">
                                <a href="../admin/manage_pets.php" class="sitemap-link">
                                    <i class="fas fa-paw"></i> Manage Pets
                                </a>
                            </li>
                            <li class="sitemap-item">
                                <a href="../admin/manage_orders.php" class="sitemap-link">
                                    <i class="fas fa-shopping-cart"></i> Manage Orders
                                </a>
                            </li>
                        </ul>
                    </div>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </section>

    <!-- Last Updated -->
    <div class="last-updated">
        <p>Sitemap last updated: <?php echo date('F j, Y \a\t g:i A'); ?></p>
    </div>

    <?php include '../../backend/includes/footer.php'; ?>
</body>
</html>