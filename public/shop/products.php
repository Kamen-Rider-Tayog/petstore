<?php
$page_title  = 'Products';
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
    <h1>Product Catalog</h1>
</div>

<!-- Filter Section -->
<div class="filter-section">
    <div class="filter-bar">
        <!-- Search -->
        <div class="filter-group">
            <label for="search">Search</label>
            <input id="search" type="text" class="form-control filter-input"
                   placeholder="Search products...">
        </div>
        
        <!-- Category Filter - Custom Dropdown -->
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
        
        <!-- Clear Button -->
        <div class="filter-actions">
            <button id="clearFilters" class="btn btn-outline btn-small">Clear</button>
        </div>
    </div>
    
    <!-- Results Count -->
    <div id="product-count" class="results-count"></div>
</div>

<!-- Results Area -->
<div id="product-results-area" class="products-grid">
    <!-- Results loaded via AJAX -->
</div>

<!-- Load More Container -->
<div id="loadMoreContainer" class="load-more-container">
    <button id="loadMoreBtn" class="btn btn-outline btn-large">Load More</button>
</div>

<script>
// Clear filters
document.getElementById('clearFilters')?.addEventListener('click', function() {
    // Reset category dropdown
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
    
    // Clear search
    document.getElementById('search').value = '';
    
    // Trigger filter update
    if (window.filterManager) {
        window.filterManager.updateResults();
    }
});
</script>

<?php require_once __DIR__ . '/../../backend/includes/footer.php'; ?>