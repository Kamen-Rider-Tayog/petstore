<?php
require_once __DIR__ . '/../../backend/includes/header.php';

$page_title  = 'Products';
$page_styles = [asset('css/products.css')];

// ── Filters ──
$categoryFilter = trim($_GET['category'] ?? '');
$searchTerm     = trim($_GET['search']   ?? '');
if ($categoryFilter === 'all') $categoryFilter = '';

// ── Query (products: id, product_name, category, price, quantity_in_stock, on_sale, sale_price) ──
$where  = [];
$params = [];
$types  = '';

if ($categoryFilter !== '') {
    $where[]  = 'category = ?';
    $types   .= 's';
    $params[] = $categoryFilter;
}
if ($searchTerm !== '') {
    $where[]  = 'product_name LIKE ?';
    $types   .= 's';
    $params[] = '%' . $searchTerm . '%';
}

$sql = "SELECT id, product_name, category, price, quantity_in_stock, on_sale, sale_price
        FROM products"
     . (!empty($where) ? ' WHERE ' . implode(' AND ', $where) : '')
     . " ORDER BY category, product_name";

$stmt = $conn->prepare($sql);
if (!empty($params)) {
    $stmt->bind_param($types, ...$params);
}
$stmt->execute();
$products = $stmt->get_result();

// ── Category list (cached, uses DISTINCT category from products) ──
$cat_options = Cache::get('product_categories_list');
if ($cat_options === null) {
    $r           = $conn->query("SELECT DISTINCT category FROM products ORDER BY category");
    $cat_options = $r->fetch_all(MYSQLI_ASSOC);
    $cat_options = array_column($cat_options, 'category');
    Cache::put('product_categories_list', $cat_options, 3600);
}
?>

<div class="page-header">
    <h1>Product Catalog</h1>
</div>

<form id="productFilterForm" class="filter-bar" method="get">
    <div class="filter-group">
        <label for="search">Search</label>
        <input id="search" name="search" type="text" class="form-control"
               placeholder="Search products..."
               value="<?php echo e($searchTerm); ?>">
    </div>
    <div class="filter-group">
        <label for="category">Category</label>
        <select name="category" id="category" class="form-control">
            <option value="all">All Categories</option>
            <?php foreach ($cat_options as $cat): ?>
                <option value="<?php echo e($cat); ?>"
                    <?php echo ($categoryFilter === $cat) ? 'selected' : ''; ?>>
                    <?php echo ucfirst(e($cat)); ?>
                </option>
            <?php endforeach; ?>
        </select>
    </div>
    <div class="filter-actions">
        <button type="submit" class="btn btn-primary btn-small">Filter</button>
        <?php if ($categoryFilter || $searchTerm): ?>
            <a href="/petstore/products" class="btn btn-outline btn-small">Clear</a>
        <?php endif; ?>
    </div>
</form>

<div id="productGrid" class="products-grid">
    <?php if ($products->num_rows === 0): ?>
        <p class="no-results">No products found.</p>
    <?php else: ?>
        <?php while ($product = $products->fetch_assoc()):
            $inStock = (int)$product['quantity_in_stock'] > 0;
        ?>
            <div class="product-card">
                <div class="product-info">
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
                    <p class="<?php echo $inStock ? 'in-stock' : 'out-of-stock'; ?>">
                        <?php echo $inStock
                            ? 'In Stock (' . (int)$product['quantity_in_stock'] . ')'
                            : 'Out of Stock'; ?>
                    </p>
                </div>
                <div class="product-actions">
                    <a href="/petstore/product_details?id=<?php echo (int)$product['id']; ?>"
                       class="btn btn-outline btn-small">View</a>
                    <?php if ($inStock): ?>
                        <select id="qty_<?php echo $product['id']; ?>" class="qty-select" aria-label="Quantity">
                            <?php for ($i = 1; $i <= min(10, (int)$product['quantity_in_stock']); $i++): ?>
                                <option value="<?php echo $i; ?>"><?php echo $i; ?></option>
                            <?php endfor; ?>
                        </select>
                        <button data-add-to-cart="<?php echo (int)$product['id']; ?>"
                                class="btn btn-primary btn-small">Add to Cart</button>
                    <?php endif; ?>
                </div>
            </div>
        <?php endwhile; ?>
    <?php endif; ?>
</div>

<?php
$stmt->close();
require_once __DIR__ . '/../../backend/includes/footer.php';
?>