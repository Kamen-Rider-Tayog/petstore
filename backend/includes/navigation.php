<?php
// Cart count
$cart_count = 0;
if (isset($_SESSION['customer_id'])) {
    $stmt = $conn->prepare("SELECT COALESCE(SUM(quantity), 0) AS total FROM cart WHERE customer_id = ?");
    $stmt->bind_param("i", $_SESSION['customer_id']);
    $stmt->execute();
    $cart_count = (int) $stmt->get_result()->fetch_assoc()['total'];
    $stmt->close();
}

// Time-based greeting
$hour = (int) date('G');
if ($hour < 12) {
    $greeting = 'Good Morning';
    $icon = 'sun';
} elseif ($hour < 18) {
    $greeting = 'Good Afternoon';
    $icon = 'sunset';
} else {
    $greeting = 'Good Evening';
    $icon = 'moon';
}

$greeting_text = isset($_SESSION['customer_name'])
    ? $greeting . ', ' . e(explode(' ', $_SESSION['customer_name'])[0])
    : $greeting;
?>

<nav class="main-nav">

    <!-- ── TOP ROW ── -->
    <div class="nav-top">

        <!-- Left: greeting -->
        <div class="nav-greeting">
            <?php echo icon($icon, 16, '', true); ?>
            <span><?php echo $greeting_text; ?></span>
        </div>

        <!-- Center: logo (absolute) -->
        <div class="nav-logo">
            <a href="<?php echo url(''); ?>">
                <span class="logo-text"><?php echo APP_NAME; ?></span>
            </a>
        </div>

        <!-- Right: search, cart, user/login, mobile toggle -->
        <div class="nav-top-actions">

            <!-- Search -->
            <a href="<?php echo url('search'); ?>" class="nav-icon-btn" aria-label="Search">
                <?php echo icon('search', 20); ?>
            </a>

            <!-- Cart with count -->
            <a href="<?php echo url('cart'); ?>" class="nav-icon-btn" aria-label="Cart">
                <?php echo icon('cart', 20); ?>
                <?php if ($cart_count > 0): ?>
                    <span class="cart-count-badge"><?php echo $cart_count; ?></span>
                <?php endif; ?>
            </a>

            <!-- User / Login -->
            <?php if (isset($_SESSION['customer_id'])): ?>
                <a href="<?php echo url('user_profile'); ?>" class="nav-icon-btn" aria-label="My Profile">
                    <?php echo icon('user', 20); ?>
                </a>
            <?php else: ?>
                <a href="<?php echo url('login'); ?>" class="nav-icon-btn" aria-label="Login">
                    <?php echo icon('user', 20); ?>
                </a>
            <?php endif; ?>

            <!-- Mobile hamburger -->
            <button class="mobile-nav-toggle" aria-label="Toggle menu">
                <?php echo icon('menu', 22); ?>
            </button>
        </div>
    </div>

    <!-- ── BOTTOM ROW: plain nav links ── -->
    <div class="nav-bottom">
        <ul class="nav-list">
            <li><a href="<?php echo url('products'); ?>" class="nav-link">Products</a></li>
            <li><a href="<?php echo url('pets'); ?>" class="nav-link">Pets</a></li>
            <li><a href="<?php echo url('services'); ?>" class="nav-link">Services</a></li>
            <li><a href="<?php echo url('appointments'); ?>" class="nav-link">Appointments</a></li>
        </ul>
    </div>

    <!-- ── Mobile panel ── -->
    <div class="mobile-nav" id="mobileNav" aria-hidden="true">
        <div class="mobile-nav-header">
            <span class="mobile-nav-title"><?php echo APP_NAME; ?></span>
            <button class="mobile-nav-close" id="mobileNavClose" aria-label="Close">
                <?php echo icon('close', 20); ?>
            </button>
        </div>
        <div class="mobile-greeting">
            <?php echo icon($icon, 16, '', true); ?>
            <span><?php echo $greeting_text; ?></span>
        </div>
        <ul class="mobile-nav-list">
            <li><a href="<?php echo url('products'); ?>"><?php echo icon('package', 18); ?> Products</a></li>
            <li><a href="<?php echo url('pets'); ?>"><?php echo icon('paw', 18); ?> Pets</a></li>
            <li><a href="<?php echo url('services'); ?>"><?php echo icon('heart', 18); ?> Services</a></li>
            <li><a href="<?php echo url('appointments'); ?>"><?php echo icon('calendar', 18); ?> Appointments</a></li>
            <li><a href="<?php echo url('search'); ?>"><?php echo icon('search', 18); ?> Search</a></li>
            <li><a href="<?php echo url('categories'); ?>"><?php echo icon('tag', 18); ?> Categories</a></li>
            <li><a href="<?php echo url('featured'); ?>"><?php echo icon('star', 18); ?> Featured</a></li>
            <li><a href="<?php echo url('on_sale'); ?>"><?php echo icon('tag', 18); ?> On Sale</a></li>
        </ul>
        
        <?php if (isset($_SESSION['customer_id'])): ?>
            <div class="mobile-nav-user">
                <div class="mobile-nav-user-header">
                    <?php echo icon('user', 20); ?>
                    <span><?php echo e($_SESSION['customer_name']); ?></span>
                </div>
                <ul class="mobile-nav-user-menu">
                    <li><a href="<?php echo url('user_profile'); ?>"><?php echo icon('user', 16); ?> My Profile</a></li>
                    <li><a href="<?php echo url('order_history'); ?>"><?php echo icon('package', 16); ?> Order History</a></li>
                    <li><a href="<?php echo url('my_appointments'); ?>"><?php echo icon('calendar', 16); ?> My Appointments</a></li>
                    <li><a href="<?php echo url('recently_viewed'); ?>"><?php echo icon('eye', 16); ?> Recently Viewed</a></li>
                    <li><a href="<?php echo url('logout'); ?>"><?php echo icon('x', 16); ?> Logout</a></li>
                </ul>
            </div>
        <?php else: ?>
            <div class="mobile-nav-auth">
                <a href="<?php echo url('login'); ?>" class="btn btn-secondary btn-block">
                    <?php echo icon('user', 16); ?> Login
                </a>
                <a href="<?php echo url('register'); ?>" class="btn btn-primary btn-block">
                    <?php echo icon('user', 16); ?> Register
                </a>
            </div>
        <?php endif; ?>
    </div>

</nav>

<script>
(function () {
    var toggle   = document.querySelector('.mobile-nav-toggle');
    var nav      = document.getElementById('mobileNav');
    var closeBtn = document.getElementById('mobileNavClose');

    function openNav()  { nav.classList.add('active');    nav.setAttribute('aria-hidden','false'); document.body.style.overflow='hidden'; }
    function closeNav() { nav.classList.remove('active'); nav.setAttribute('aria-hidden','true');  document.body.style.overflow=''; }

    if (toggle)   toggle.addEventListener('click', openNav);
    if (closeBtn) closeBtn.addEventListener('click', closeNav);
    document.addEventListener('click', function (e) {
        if (nav && nav.classList.contains('active') && !nav.contains(e.target) && e.target !== toggle) closeNav();
    });
}());
</script>