<?php
$cart_count = 0;
if (isset($_SESSION['customer_id'])) {
    $stmt = $conn->prepare("SELECT COALESCE(SUM(quantity), 0) AS total FROM cart WHERE customer_id = ?");
    $stmt->bind_param("i", $_SESSION['customer_id']);
    $stmt->execute();
    $cart_count = (int) $stmt->get_result()->fetch_assoc()['total'];
    $stmt->close();
}

$cache_key  = 'categories_menu';
$categories = Cache::get($cache_key);
if ($categories === null) {
    $result      = $conn->query("SELECT id, parent_id, category_name FROM categories ORDER BY parent_id ASC, category_name ASC");
    $parent_cats = [];
    $child_cats  = [];
    while ($cat = $result->fetch_assoc()) {
        if (is_null($cat['parent_id'])) { $cat['children'] = []; $parent_cats[$cat['id']] = $cat; }
        else { $child_cats[$cat['parent_id']][] = $cat; }
    }
    foreach ($child_cats as $pid => $children) {
        if (isset($parent_cats[$pid])) $parent_cats[$pid]['children'] = $children;
    }
    $categories = array_values($parent_cats);
    Cache::put($cache_key, $categories, 3600);
}
?>

<nav class="main-nav">
    <div class="nav-container">

        <!-- Logo -->
        <div class="nav-logo">
            <a href="/Ria-Pet-Store/">
                <span class="logo-text"><?php echo APP_NAME; ?></span>
            </a>
        </div>

        <!-- Centered links -->
        <div class="nav-menu">
            <ul class="nav-list">

                <!-- Products — click dropdown -->
                <li class="nav-item nav-has-dropdown" id="navProducts">
                    <button class="nav-link nav-dropdown-btn" aria-expanded="false" aria-haspopup="true">
                        Products
                        <svg class="nav-chevron" xmlns="http://www.w3.org/2000/svg" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><polyline points="6 9 12 15 18 9"/></svg>
                    </button>
                    <div class="nav-dropdown" role="menu">
                        <div class="nav-dropdown-inner">
                            <div class="nav-dropdown-col">
                                <p class="nav-dropdown-heading">Accessories</p>
                                <ul>
                                    <li><a href="/Ria-Pet-Store/products?category=Accessories">All Accessories</a></li>
                                    <li><a href="/Ria-Pet-Store/products?category=Beds">Beds</a></li>
                                    <li><a href="/Ria-Pet-Store/products?category=Collars">Collars</a></li>
                                    <li><a href="/Ria-Pet-Store/products?category=Leashes">Leashes</a></li>
                                </ul>
                            </div>
                            <div class="nav-dropdown-col">
                                <p class="nav-dropdown-heading">Food</p>
                                <ul>
                                    <li><a href="/Ria-Pet-Store/products?category=Food">All Food</a></li>
                                    <li><a href="/Ria-Pet-Store/products?category=Bird+Food">Bird Food</a></li>
                                    <li><a href="/Ria-Pet-Store/products?category=Cat+Food">Cat Food</a></li>
                                    <li><a href="/Ria-Pet-Store/products?category=Dog+Food">Dog Food</a></li>
                                    <li><a href="/Ria-Pet-Store/products?category=Fish+Food">Fish Food</a></li>
                                </ul>
                            </div>
                            <div class="nav-dropdown-col">
                                <p class="nav-dropdown-heading">Health</p>
                                <ul>
                                    <li><a href="/Ria-Pet-Store/products?category=Health">All Health</a></li>
                                    <li><a href="/Ria-Pet-Store/products?category=Medications">Medications</a></li>
                                    <li><a href="/Ria-Pet-Store/products?category=Supplements">Supplements</a></li>
                                </ul>
                            </div>
                            <div class="nav-dropdown-col">
                                <p class="nav-dropdown-heading">Housing</p>
                                <ul>
                                    <li><a href="/Ria-Pet-Store/products?category=Housing">All Housing</a></li>
                                    <li><a href="/Ria-Pet-Store/products?category=Bowls">Bowls</a></li>
                                    <li><a href="/Ria-Pet-Store/products?category=Cages">Cages</a></li>
                                    <li><a href="/Ria-Pet-Store/products?category=Tanks">Tanks</a></li>
                                </ul>
                            </div>
                            <div class="nav-dropdown-col">
                                <p class="nav-dropdown-heading">Toys</p>
                                <ul>
                                    <li><a href="/Ria-Pet-Store/products?category=Toys">All Toys</a></li>
                                    <li><a href="/Ria-Pet-Store/products?category=Chew+Toys">Chew Toys</a></li>
                                    <li><a href="/Ria-Pet-Store/products?category=Interactive+Toys">Interactive Toys</a></li>
                                    <li><a href="/Ria-Pet-Store/products?category=Plush+Toys">Plush Toys</a></li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </li>

                <li class="nav-item"><a href="/Ria-Pet-Store/pets"     class="nav-link">Pets</a></li>
                <li class="nav-item"><a href="/Ria-Pet-Store/services" class="nav-link">Services</a></li>
            </ul>
        </div>

        <!-- Right actions -->
        <div class="nav-actions">
            <a href="/Ria-Pet-Store/search" class="nav-icon-btn" aria-label="Search">
                <svg xmlns="http://www.w3.org/2000/svg" width="19" height="19" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
            </a>

            <?php if (isset($_SESSION['customer_id'])): ?>
                <a href="/Ria-Pet-Store/user_profile" class="nav-icon-btn" aria-label="My Account">
                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="8" r="4"/><path d="M4 20c0-4 3.6-7 8-7s8 3 8 7"/></svg>
                </a>
            <?php else: ?>
                <a href="/Ria-Pet-Store/login" class="nav-icon-btn" aria-label="Login">
                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="8" r="4"/><path d="M4 20c0-4 3.6-7 8-7s8 3 8 7"/></svg>
                </a>
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
            <li><a href="/Ria-Pet-Store/products">Products</a></li>
            <li><a href="/Ria-Pet-Store/pets">Pets</a></li>
            <li><a href="/Ria-Pet-Store/services">Services</a></li>
            <li><a href="/Ria-Pet-Store/search">Search</a></li>
        </ul>
        <?php if (isset($_SESSION['customer_id'])): ?>
            <div class="mobile-nav-user">
                <div class="user-info"><?php echo e($_SESSION['customer_name'] ?? 'Account'); ?></div>
                <ul class="mobile-nav-user-menu">
                    <li><a href="/Ria-Pet-Store/user_profile">My Profile</a></li>
                    <li><a href="/Ria-Pet-Store/order_history">Order History</a></li>
                    <li><a href="/Ria-Pet-Store/my_appointments">My Appointments</a></li>
                    <li><a href="/Ria-Pet-Store/logout">Logout</a></li>
                </ul>
            </div>
        <?php else: ?>
            <div class="mobile-nav-auth">
                <a href="/Ria-Pet-Store/login"    class="btn btn-block">Login</a>
                <a href="/Ria-Pet-Store/register" class="btn btn-primary btn-block">Register</a>
            </div>
        <?php endif; ?>
    </div>
</nav>

<script>
(function () {
    /* ── Mobile nav ── */
    var toggle   = document.querySelector('.mobile-nav-toggle');
    var nav      = document.getElementById('mobileNav');
    var closeBtn = document.getElementById('mobileNavClose');

    function openNav()  { nav.classList.add('active');    nav.setAttribute('aria-hidden','false'); document.body.style.overflow='hidden'; }
    function closeNav() { nav.classList.remove('active'); nav.setAttribute('aria-hidden','true');  document.body.style.overflow=''; }

    if (toggle)   toggle.addEventListener('click', openNav);
    if (closeBtn) closeBtn.addEventListener('click', closeNav);

    /* ── Products click dropdown ── */
    var productsItem = document.getElementById('navProducts');
    if (productsItem) {
        var btn      = productsItem.querySelector('.nav-dropdown-btn');
        var dropdown = productsItem.querySelector('.nav-dropdown');

        btn.addEventListener('click', function (e) {
            e.stopPropagation();
            var isOpen = productsItem.classList.contains('open');
            closeAllDropdowns();
            if (!isOpen) {
                productsItem.classList.add('open');
                btn.setAttribute('aria-expanded', 'true');
            }
        });
    }

    function closeAllDropdowns() {
        document.querySelectorAll('.nav-has-dropdown').forEach(function (el) {
            el.classList.remove('open');
            var b = el.querySelector('[aria-expanded]');
            if (b) b.setAttribute('aria-expanded', 'false');
        });
    }

    /* Close on outside click */
    document.addEventListener('click', function () { closeAllDropdowns(); });

    /* Close mobile on outside click */
    document.addEventListener('click', function (e) {
        if (nav && nav.classList.contains('active') && !nav.contains(e.target) && e.target !== toggle) closeNav();
    });
}());
</script>