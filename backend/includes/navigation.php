<?php
// Cart count for logged-in users
$cart_count = 0;
if (isset($_SESSION['customer_id'])) {
    $stmt = $conn->prepare("SELECT COALESCE(SUM(quantity), 0) AS total FROM cart WHERE customer_id = ?");
    $stmt->bind_param("i", $_SESSION['customer_id']);
    $stmt->execute();
    $cart_count = (int) $stmt->get_result()->fetch_assoc()['total'];
    $stmt->close();
}

// Categories from cache
$cache_key  = 'categories_menu';
$categories = Cache::get($cache_key);

if ($categories === null) {
    $result      = $conn->query("SELECT id, parent_id, category_name FROM categories ORDER BY parent_id ASC, category_name ASC");
    $parent_cats = [];
    $child_cats  = [];

    while ($cat = $result->fetch_assoc()) {
        if (is_null($cat['parent_id'])) {
            $cat['children'] = [];
            $parent_cats[$cat['id']] = $cat;
        } else {
            $child_cats[$cat['parent_id']][] = $cat;
        }
    }
    foreach ($child_cats as $pid => $children) {
        if (isset($parent_cats[$pid])) {
            $parent_cats[$pid]['children'] = $children;
        }
    }
    $categories = array_values($parent_cats);
    Cache::put($cache_key, $categories, 3600);
}
?>

<nav class="main-nav">
    <div class="nav-container">

        <!-- Logo -->
        <div class="nav-logo">
            <a href="/petstore/">
                <span class="logo-text"><?php echo APP_NAME; ?></span>
            </a>
        </div>

        <!-- Desktop links -->
        <div class="nav-menu">
            <ul class="nav-list">
                <li class="nav-item"><a href="/petstore/" class="nav-link">Home</a></li>

                <li class="nav-item has-mega-menu">
                    <a href="/petstore/products" class="nav-link">Products</a>
                    <div class="mega-menu">
                        <div class="mega-menu-content">
                            <?php foreach ($categories as $category): ?>
                                <div class="mega-menu-column">
                                    <h3><?php echo e($category['category_name']); ?></h3>
                                    <ul>
                                        <?php if (!empty($category['children'])): ?>
                                            <?php foreach ($category['children'] as $sub): ?>
                                                <li><a href="/petstore/products?category=<?php echo urlencode($sub['category_name']); ?>"><?php echo e($sub['category_name']); ?></a></li>
                                            <?php endforeach; ?>
                                        <?php else: ?>
                                            <li><a href="/petstore/products?category=<?php echo urlencode($category['category_name']); ?>">All <?php echo e($category['category_name']); ?></a></li>
                                        <?php endif; ?>
                                    </ul>
                                </div>
                            <?php endforeach; ?>
                            <div class="mega-menu-column">
                                <h3>Quick Links</h3>
                                <ul>
                                    <li><a href="/petstore/products?featured=1">Featured</a></li>
                                    <li><a href="/petstore/products?on_sale=1">On Sale</a></li>
                                    <li><a href="/petstore/search">Search All</a></li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </li>

                <li class="nav-item"><a href="/petstore/pets"     class="nav-link">Pets</a></li>
                <li class="nav-item"><a href="/petstore/services" class="nav-link">Services</a></li>
            </ul>
        </div>

        <!-- Actions: search + user icon only -->
        <div class="nav-actions">
            <div class="nav-search">
                <form action="/petstore/search" method="get" class="search-form">
                    <input type="text" name="q" placeholder="Search..." aria-label="Search">
                    <button type="submit" aria-label="Search">&#128269;</button>
                </form>
            </div>

            <?php if (isset($_SESSION['customer_id'])): ?>
                <div class="nav-user has-dropdown">
                    <button class="user-icon-btn" aria-label="User menu">
                        <svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="8" r="4"/><path d="M4 20c0-4 3.6-7 8-7s8 3 8 7"/></svg>
                    </button>
                    <div class="dropdown-menu">
                        <div class="dropdown-name"><?php echo e($_SESSION['customer_name'] ?? 'Account'); ?></div>
                        <a href="/petstore/user_profile">My Profile</a>
                        <a href="/petstore/order_history">Order History</a>
                        <a href="/petstore/my_appointments">My Appointments</a>
                        <a href="/petstore/logout">Logout</a>
                    </div>
                </div>
            <?php else: ?>
                <div class="nav-user has-dropdown">
                    <button class="user-icon-btn" aria-label="Account">
                        <svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="8" r="4"/><path d="M4 20c0-4 3.6-7 8-7s8 3 8 7"/></svg>
                    </button>
                    <div class="dropdown-menu">
                        <a href="/petstore/login"    class="dropdown-btn">Login</a>
                        <a href="/petstore/register" class="dropdown-btn dropdown-btn-primary">Register</a>
                    </div>
                </div>
            <?php endif; ?>

            <button class="mobile-nav-toggle" aria-label="Toggle menu">&#9776;</button>
        </div>
    </div>

    <!-- Mobile Nav -->
    <div class="mobile-nav" id="mobileNav" aria-hidden="true">
        <div class="mobile-nav-header">
            <span class="mobile-nav-title">Menu</span>
            <button class="mobile-nav-close" id="mobileNavClose" aria-label="Close">&#10005;</button>
        </div>
        <ul class="mobile-nav-list">
            <li><a href="/petstore/">Home</a></li>
            <li class="has-submenu">
                <a href="/petstore/products">Products</a>
                <ul class="submenu">
                    <?php foreach ($categories as $category): ?>
                        <li><a href="/petstore/products?category=<?php echo urlencode($category['category_name']); ?>"><?php echo e($category['category_name']); ?></a></li>
                    <?php endforeach; ?>
                </ul>
            </li>
            <li><a href="/petstore/pets">Pets</a></li>
            <li><a href="/petstore/services">Services</a></li>
        </ul>
        <?php if (isset($_SESSION['customer_id'])): ?>
            <div class="mobile-nav-user">
                <div class="user-info"><?php echo e($_SESSION['customer_name'] ?? 'Account'); ?></div>
                <ul class="mobile-nav-user-menu">
                    <li><a href="/petstore/user_profile">My Profile</a></li>
                    <li><a href="/petstore/order_history">Order History</a></li>
                    <li><a href="/petstore/my_appointments">My Appointments</a></li>
                    <li><a href="/petstore/logout">Logout</a></li>
                </ul>
            </div>
        <?php else: ?>
            <div class="mobile-nav-auth">
                <a href="/petstore/login"    class="btn btn-block">Login</a>
                <a href="/petstore/register" class="btn btn-primary btn-block">Register</a>
            </div>
        <?php endif; ?>
    </div>
</nav>

<script>
(function () {
    var toggle   = document.querySelector('.mobile-nav-toggle');
    var nav      = document.getElementById('mobileNav');
    var closeBtn = document.getElementById('mobileNavClose');

    function open()  { nav.classList.add('active');    nav.setAttribute('aria-hidden','false'); document.body.style.overflow='hidden'; }
    function close() { nav.classList.remove('active'); nav.setAttribute('aria-hidden','true');  document.body.style.overflow=''; }

    if (toggle)   toggle.addEventListener('click', open);
    if (closeBtn) closeBtn.addEventListener('click', close);
    document.addEventListener('click', function(e) {
        if (nav && nav.classList.contains('active') && !nav.contains(e.target) && e.target !== toggle) close();
    });

    document.querySelectorAll('.has-submenu > a').forEach(function(link) {
        link.addEventListener('click', function(e) {
            e.preventDefault();
            var li = this.parentElement;
            var sm = li.querySelector('.submenu');
            var open = li.classList.contains('active');
            li.parentElement.querySelectorAll('.has-submenu').forEach(function(s) {
                s.classList.remove('active');
                var m = s.querySelector('.submenu');
                if (m) m.style.display = 'none';
            });
            if (!open) { li.classList.add('active'); if (sm) sm.style.display = 'block'; }
        });
    });
}());
</script>