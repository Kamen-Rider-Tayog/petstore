<?php
$page_title = 'Products';
$page_description = 'Browse our wide selection of pet products';
$filter_type = isset($_GET['filter']) ? $_GET['filter'] : 'all';

if ($filter_type === 'featured') {
    $page_title = 'Featured Products';
    $page_description = 'Shop our hand-picked featured products';
} elseif ($filter_type === 'new') {
    $page_title = 'New Arrivals';
    $page_description = 'Check out our latest products';
} elseif ($filter_type === 'sale') {
    $page_title = 'On Sale';
    $page_description = 'Great deals on pet supplies';
}

require_once __DIR__ . '/../../backend/includes/header.php';

// Get categories for filter
$cat_options = Cache::get('product_categories_list');
if ($cat_options === null) {
    $r = $conn->query("SELECT DISTINCT category FROM products ORDER BY category");
    $cat_options = $r->fetch_all(MYSQLI_ASSOC);
    $cat_options = array_column($cat_options, 'category');
    Cache::put('product_categories_list', $cat_options, 3600);
}
?>

<link rel="stylesheet" href="http://localhost/Ria-Pet-Store/assets/css/shop/products.css?v=<?php echo ASSET_VERSION; ?>">

<div class="page-header">
    <h1><?php echo $page_title; ?></h1>
    <p><?php echo $page_description; ?></p>
</div>

<!-- Filter Section -->
<div class="filter-section">
    <!-- Product Type Tabs -->
    <div class="product-type-tabs">
        <a href="<?php echo url('products'); ?>" class="type-tab <?php echo $filter_type === 'all' ? 'active' : ''; ?>">
            <?php echo icon('package', 14); ?> All
        </a>
        <a href="<?php echo url('products?filter=featured'); ?>" class="type-tab <?php echo $filter_type === 'featured' ? 'active' : ''; ?>">
            <?php echo icon('star', 14); ?> Featured
        </a>
        <a href="<?php echo url('products?filter=new'); ?>" class="type-tab <?php echo $filter_type === 'new' ? 'active' : ''; ?>">
            <?php echo icon('clock', 14); ?> New
        </a>
        <a href="<?php echo url('products?filter=sale'); ?>" class="type-tab <?php echo $filter_type === 'sale' ? 'active' : ''; ?>">
            <?php echo icon('tag', 14); ?> Sale
        </a>
    </div>
    
    <div class="filter-bar">
        <div class="filter-group">
            <label for="search">Search</label>
            <input id="search" type="text" class="form-control filter-input" placeholder="Search products...">
        </div>
        
        <div class="filter-group">
            <label for="category">Category</label>
            <div class="custom-dropdown" id="categoryDropdown">
                <div class="selected" data-value="all">
                    All Categories
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <polyline points="6 9 12 15 18 9"/>
                    </svg>
                </div>
                <div class="options">
                    <div class="option selected" data-value="all">All Categories</div>
                    <?php foreach ($cat_options as $cat): ?>
                        <div class="option" data-value="<?php echo e($cat); ?>">
                            <?php echo ucfirst(e($cat)); ?>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
        
        <div class="filter-actions">
            <button id="clearFilters" class="btn btn-outline btn-small">Clear</button>
        </div>
    </div>
    
    <div id="product-count" class="results-count"></div>
</div>

<div id="product-results-area" class="products-grid"></div>
<div id="loadMoreContainer" class="load-more-container">
    <button id="loadMoreBtn" class="btn btn-outline btn-large">Load More</button>
</div>

<script>
// Override filter manager to add filter type
document.addEventListener('DOMContentLoaded', function() {
    setTimeout(function() {
        if (window.filterManager) {
            const originalCollectFilters = window.filterManager.collectFilters;
            window.filterManager.collectFilters = function() {
                const filters = originalCollectFilters.call(this);
                <?php if ($filter_type === 'featured'): ?>
                    filters.featured = 1;
                <?php elseif ($filter_type === 'new'): ?>
                    filters.new_arrivals = 1;
                <?php elseif ($filter_type === 'sale'): ?>
                    filters.on_sale = 1;
                <?php endif; ?>
                return filters;
            };
            window.filterManager.updateResults();
        }
    }, 100);
});

document.getElementById('clearFilters')?.addEventListener('click', function() {
    const dropdownSelected = document.querySelector('#categoryDropdown .selected');
    const allOption = document.querySelector('#categoryDropdown .option[data-value="all"]');
    
    if (dropdownSelected && allOption) {
        dropdownSelected.innerHTML = 'All Categories <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="6 9 12 15 18 9"/></svg>';
        dropdownSelected.dataset.value = 'all';
        
        document.querySelectorAll('#categoryDropdown .option').forEach(opt => {
            opt.classList.remove('selected');
        });
        allOption.classList.add('selected');
    }
    
    document.getElementById('search').value = '';
    
    if (window.filterManager) {
        window.filterManager.updateResults();
    }
});
</script>

<?php require_once __DIR__ . '/../../backend/includes/footer.php'; ?>