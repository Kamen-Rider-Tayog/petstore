<?php
// Get cart count for logged-in users
$cart_count = 0;
if (isset($_SESSION['customer_id'])) {
    $stmt = $conn->prepare("SELECT SUM(quantity) as total FROM cart WHERE customer_id = ?");
    $stmt->bind_param("i", $_SESSION['customer_id']);
    $stmt->execute();
    $result = $stmt->get_result();
    $cart_data = $result->fetch_assoc();
    $cart_count = $cart_data['total'] ?? 0;
    $stmt->close();
}

// Get categories for mega menu
$categories = $conn->query("SELECT * FROM categories WHERE parent_id IS NULL ORDER BY category_name");
?>

<nav class="main-nav">
    <div class="nav-container">
        <!-- Logo -->
        <div class="nav-logo">
            <a href="/petstore/">
                <img src="<?php echo asset('images/logo.png'); ?>" alt="Ria Pet Store" onerror="this.onerror=null; this.src='<?php echo asset('images/logo-placeholder.png'); ?>'">
                <span class="logo-text">Ria Pet Store</span>
            </a>
        </div>

        <!-- Desktop Navigation -->
        <div class="nav-menu">
            <ul class="nav-list">
                <li class="nav-item">
                    <a href="/petstore/" class="nav-link">Home</a>
                </li>

                <!-- Products Mega Menu -->
                <li class="nav-item has-mega-menu">
                    <a href="/petstore/products" class="nav-link">Products</a>
                    <div class="mega-menu">
                        <div class="mega-menu-content">
                            <?php while ($category = $categories->fetch_assoc()): ?>
                                <div class="mega-menu-column">
                                    <h3><?php echo htmlspecialchars($category['category_name']); ?></h3>
                                    <ul>
                                        <?php
                                        $sub_stmt = $conn->prepare("SELECT * FROM categories WHERE parent_id = ? ORDER BY category_name");
                                        $sub_stmt->bind_param("i", $category['id']);
                                        $sub_stmt->execute();
                                        $subcategories = $sub_stmt->get_result();
                                        ?>
                                        <?php while ($sub = $subcategories->fetch_assoc()): ?>
                                            <li><a href="/petstore/products?category=<?php echo urlencode($sub['category_name']); ?>"><?php echo htmlspecialchars($sub['category_name']); ?></a></li>
                                        <?php endwhile; ?>
                                        <?php if ($subcategories->num_rows === 0): ?>
                                            <li><a href="/petstore/products?category=<?php echo urlencode($category['category_name']); ?>">All <?php echo htmlspecialchars($category['category_name']); ?></a></li>
                                        <?php endif; ?>
                                    </ul>
                                </div>
                            <?php endwhile; ?>
                            <div class="mega-menu-column">
                                <h3>Quick Links</h3>
                                <ul>
                                    <li><a href="/petstore/products?featured=1">Featured Products</a></li>
                                    <li><a href="/petstore/products?on_sale=1">On Sale</a></li>
                                    <li><a href="/petstore/products?sort=rating">Top Rated</a></li>
                                    <li><a href="/petstore/search">Search All Products</a></li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </li>

                <li class="nav-item">
                    <a href="/petstore/pets" class="nav-link">Pets</a>
                </li>

                <li class="nav-item">
                    <a href="/petstore/services" class="nav-link">Services</a>
                </li>

                <li class="nav-item">
                    <a href="/petstore/book_appointment" class="nav-link">Appointments</a>
                </li>

                <li class="nav-item">
                    <a href="/petstore/about" class="nav-link">About</a>
                </li>

                <li class="nav-item">
                    <a href="/petstore/contact" class="nav-link">Contact</a>
                </li>
            </ul>
        </div>

        <!-- User Actions -->
        <div class="nav-actions">
            <!-- Search -->
            <div class="nav-search">
                <form action="/petstore/search" method="get" class="search-form">
                    <input type="text" name="q" placeholder="Search products..." required>
                    <button type="submit"><i class="icon-search"></i></button>
                </form>
            </div>

            <!-- Cart -->
            <a href="/petstore/cart" class="nav-cart">
                <i class="icon-cart"></i>
                <span class="cart-count"><?php echo $cart_count; ?></span>
            </a>

            <!-- User Menu -->
            <?php if (isset($_SESSION['customer_id'])): ?>
                <div class="nav-user has-dropdown">
                    <button class="user-menu-toggle">
                        <i class="icon-user"></i>
                        <span><?php echo htmlspecialchars($_SESSION['customer_name'] ?? 'Account'); ?></span>
                    </button>
                    <div class="dropdown-menu">
                        <a href="/petstore/user_profile">My Profile</a>
                        <a href="/petstore/order_history">Order History</a>
                        <a href="/petstore/my_appointments">My Appointments</a>
                        <a href="/petstore/logout">Logout</a>
                    </div>
                </div>
            <?php else: ?>
                <div class="nav-auth">
                    <a href="/petstore/login" class="btn btn-outline">Login</a>
                    <a href="/petstore/register" class="btn btn-primary">Register</a>
                </div>
            <?php endif; ?>

            <!-- Mobile Menu Toggle -->
            <button class="mobile-nav-toggle" aria-label="Toggle mobile menu">
                <i class="icon-menu"></i>
            </button>
        </div>
    </div>

    <!-- Mobile Navigation -->
    <div class="mobile-nav">
        <div class="mobile-nav-header">
            <span class="mobile-nav-title">Menu</span>
            <button class="mobile-nav-close">
                <i class="icon-close"></i>
            </button>
        </div>

        <ul class="mobile-nav-list">
            <li><a href="index.php">Home</a></li>
            <li class="has-submenu">
                <a href="products.php">Products</a>
                <ul class="submenu">
                    <?php
                    $categories->data_seek(0); // Reset pointer
                    while ($category = $categories->fetch_assoc()):
                    ?>
                        <li><a href="products.php?category=<?php echo urlencode($category['category_name']); ?>"><?php echo htmlspecialchars($category['category_name']); ?></a></li>
                    <?php endwhile; ?>
                </ul>
            </li>
            <li><a href="pets.php">Pets</a></li>
            <li><a href="services.php">Services</a></li>
            <li><a href="book_appointment.php">Appointments</a></li>
            <li><a href="about.php">About</a></li>
            <li><a href="contact.php">Contact</a></li>
        </ul>

        <?php if (isset($_SESSION['customer_id'])): ?>
            <div class="mobile-nav-user">
                <div class="user-info">
                    <i class="icon-user"></i>
                    <span><?php echo htmlspecialchars($_SESSION['customer_name'] ?? 'Account'); ?></span>
                </div>
                <ul class="mobile-nav-user-menu">
                    <li><a href="profile.php">My Profile</a></li>
                    <li><a href="order_history.php">Order History</a></li>
                    <li><a href="my_appointments.php">My Appointments</a></li>
                    <li><a href="logout.php">Logout</a></li>
                </ul>
            </div>
        <?php else: ?>
            <div class="mobile-nav-auth">
                <a href="login.php" class="btn btn-block">Login</a>
                <a href="register.php" class="btn btn-primary btn-block">Register</a>
            </div>
        <?php endif; ?>
    </div>
</nav>

<link rel="stylesheet" href="../assets/css/navigation.css">

<script>
// Mobile navigation functionality
document.addEventListener('DOMContentLoaded', function() {
    const mobileToggle = document.querySelector('.mobile-nav-toggle');
    const mobileNav = document.querySelector('.mobile-nav');
    const mobileClose = document.querySelector('.mobile-nav-close');
    const submenuToggles = document.querySelectorAll('.has-submenu > a');

    // Toggle mobile menu
    if (mobileToggle) {
        mobileToggle.addEventListener('click', function() {
            mobileNav.classList.toggle('active');
            document.body.classList.toggle('mobile-menu-open');
        });
    }

    // Close mobile menu
    if (mobileClose) {
        mobileClose.addEventListener('click', function() {
            mobileNav.classList.remove('active');
            document.body.classList.remove('mobile-menu-open');
        });
    }

    // Toggle submenus
    submenuToggles.forEach(toggle => {
        toggle.addEventListener('click', function(e) {
            e.preventDefault();
            const li = this.parentElement;
            li.classList.toggle('active');
            const submenu = li.querySelector('.submenu');
            if (submenu) {
                submenu.style.display = li.classList.contains('active') ? 'block' : 'none';
            }
        });
    });

    // Close menu when clicking outside
    document.addEventListener('click', function(e) {
        if (!mobileNav.contains(e.target) && !mobileToggle.contains(e.target)) {
            mobileNav.classList.remove('active');
            document.body.classList.remove('mobile-menu-open');
        }
    });
});
</script>