<?php
// Get product ID from URL
$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($id <= 0) {
    header('Location: ' . url('products'));
    exit;
}

require_once __DIR__ . '/../../backend/config/database.php';

// Fetch product details
$stmt = $conn->prepare("SELECT * FROM products WHERE id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();
$product = $result->fetch_assoc();
$stmt->close();

if (!$product) {
    header('Location: ' . url('products'));
    exit;
}

// Set page title and meta
$page_title = $product['product_name'] . ' - Product Details';
$page_description = "View details for {$product['product_name']}. " . substr(strip_tags($product['description'] ?? ''), 0, 150);

require_once __DIR__ . '/../../backend/includes/header.php';

// Track recently viewed products
if (!isset($_SESSION['recently_viewed']) || !is_array($_SESSION['recently_viewed'])) {
    $_SESSION['recently_viewed'] = [];
}

// Remove existing entry if already viewed
foreach ($_SESSION['recently_viewed'] as $key => $item) {
    if ($item['id'] === $id) {
        unset($_SESSION['recently_viewed'][$key]);
        break;
    }
}

// Add current product to recently viewed
array_unshift($_SESSION['recently_viewed'], [
    'id' => $id,
    'name' => $product['product_name'],
    'price' => $product['price'],
    'image' => $product['image'] ?? ''
]);

// Keep only last 5
$_SESSION['recently_viewed'] = array_slice($_SESSION['recently_viewed'], 0, 5);

// Get related products (same category)
$relatedStmt = $conn->prepare("SELECT id, product_name, price, product_image FROM products WHERE category = ? AND id != ? LIMIT 4");
$relatedStmt->bind_param("si", $product['category'], $id);
$relatedStmt->execute();
$relatedProducts = $relatedStmt->get_result();
$relatedStmt->close();

$inStock = (int)$product['quantity_in_stock'] > 0;
$onSale = !empty($product['on_sale']) && !empty($product['sale_price']);
$displayPrice = $onSale ? $product['sale_price'] : $product['price'];
$originalPrice = $onSale ? $product['price'] : null;
?>

<link rel="stylesheet" href="http://localhost/Ria-Pet-Store/assets/css/shop/product_details.css?v=<?php echo ASSET_VERSION; ?>">

<div class="product-details-page">
    <!-- Back navigation -->
    <div class="back-nav">
        <div class="container">
            <a href="<?php echo url('products'); ?>" class="back-link">
                <?php echo icon('arrow-left', 16); ?> Back to Products
            </a>
        </div>
    </div>

    <!-- Product Details Section -->
    <section class="product-details-section">
        <div class="container">
            <div class="product-details-container">
                <!-- Product Image -->
                <div class="product-image-gallery">
                    <div class="main-image">
                        <?php if (!empty($product['image'])): ?>
                            <img src="<?php echo asset('images/products/' . $product['image']); ?>" 
                                 alt="<?php echo e($product['product_name']); ?>"
                                 onerror="this.src='<?php echo asset('images/product-placeholder.jpg'); ?>'">
                        <?php else: ?>
                            <div class="no-image">
                                <?php echo icon('package', 64); ?>
                                <p>No image available</p>
                            </div>
                        <?php endif; ?>
                        
                        <?php if ($onSale): ?>
                            <span class="sale-badge">SALE</span>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Product Info -->
                <div class="product-info">
                    <div class="product-header">
                        <h1><?php echo e($product['product_name']); ?></h1>
                        <span class="product-category-badge"><?php echo ucfirst(e($product['category'])); ?></span>
                    </div>

                    <div class="product-price-section">
                        <?php if ($onSale): ?>
                            <div class="price-block">
                                <span class="original-price"><?php echo CURRENCY_SYMBOL . number_format($product['price'], 2); ?></span>
                                <span class="sale-price"><?php echo CURRENCY_SYMBOL . number_format($product['sale_price'], 2); ?></span>
                            </div>
                            <span class="discount-badge">Save <?php echo CURRENCY_SYMBOL . number_format($product['price'] - $product['sale_price'], 2); ?></span>
                        <?php else: ?>
                            <div class="price-block">
                                <span class="regular-price"><?php echo CURRENCY_SYMBOL . number_format($product['price'], 2); ?></span>
                            </div>
                        <?php endif; ?>
                    </div>

                    <div class="stock-status <?php echo $inStock ? 'in-stock' : 'out-of-stock'; ?>">
                        <?php echo icon($inStock ? 'check' : 'x', 16); ?>
                        <span>
                            <?php echo $inStock 
                                ? 'In Stock (' . (int)$product['quantity_in_stock'] . ' available)' 
                                : 'Out of Stock'; ?>
                        </span>
                    </div>

                    <?php if (!empty($product['description'])): ?>
                    <div class="product-description">
                        <h2>Description</h2>
                        <p><?php echo nl2br(e($product['description'])); ?></p>
                    </div>
                    <?php endif; ?>

                    <?php if ($inStock): ?>
                    <div class="product-actions">
                        <div class="quantity-selector">
                            <label for="qty_<?php echo $id; ?>">Quantity:</label>
                            <select id="qty_<?php echo $id; ?>" class="qty-select">
                                <?php for ($i = 1; $i <= min(10, (int)$product['quantity_in_stock']); $i++): ?>
                                    <option value="<?php echo $i; ?>"><?php echo $i; ?></option>
                                <?php endfor; ?>
                            </select>
                        </div>
                        <button data-add-to-cart="<?php echo $id; ?>" class="btn btn-primary btn-large">
                            <?php echo icon('cart', 18); ?> Add to Cart
                        </button>
                    </div>
                    <?php endif; ?>

                    <div class="product-meta">
                        <p><strong>Category:</strong> <?php echo ucfirst(e($product['category'])); ?></p>
                        <?php if (!empty($product['brand'])): ?>
                            <p><strong>Brand:</strong> <?php echo e($product['brand']); ?></p>
                        <?php endif; ?>
                    </div>

                    <div class="product-share">
                        <span>Share:</span>
                        <a href="#" class="share-link" onclick="window.open('https://www.facebook.com/sharer/sharer.php?u='+encodeURIComponent(window.location.href), 'facebook-share', 'width=580,height=296');return false;">
                            <?php echo icon('facebook', 18); ?>
                        </a>
                        <a href="#" class="share-link" onclick="window.open('https://twitter.com/intent/tweet?text=<?php echo urlencode('Check out ' . $product['product_name'] . '!'); ?>&url='+encodeURIComponent(window.location.href), 'twitter-share', 'width=550,height=235');return false;">
                            <?php echo icon('twitter', 18); ?>
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Related Products -->
    <?php if ($relatedProducts->num_rows > 0): ?>
    <section class="related-products-section">
        <div class="container">
            <h2>Related Products</h2>
            <div class="related-products-grid">
                <?php while ($rel = $relatedProducts->fetch_assoc()): ?>
                    <div class="related-product-card">
                        <a href="<?php echo url('product_details?id=' . $rel['id']); ?>">
                            <div class="related-product-image">
                                <?php if (!empty($rel['image'])): ?>
                                    <img src="<?php echo asset('images/products/' . $rel['image']); ?>" 
                                         alt="<?php echo e($rel['product_name']); ?>"
                                         onerror="this.src='<?php echo asset('images/product-placeholder.jpg'); ?>'">
                                <?php else: ?>
                                    <div class="no-image">
                                        <?php echo icon('package', 24); ?>
                                    </div>
                                <?php endif; ?>
                            </div>
                            <h3><?php echo e($rel['product_name']); ?></h3>
                            <p class="related-product-price"><?php echo CURRENCY_SYMBOL . number_format($rel['price'], 2); ?></p>
                        </a>
                    </div>
                <?php endwhile; ?>
            </div>
        </div>
    </section>
    <?php endif; ?>

    <!-- Recently Viewed -->
    <?php if (!empty($_SESSION['recently_viewed']) && count($_SESSION['recently_viewed']) > 1): ?>
    <section class="recently-viewed-section">
        <div class="container">
            <h2>Recently Viewed</h2>
            <div class="recently-viewed-grid">
                <?php foreach ($_SESSION['recently_viewed'] as $viewed): ?>
                    <?php if ($viewed['id'] != $id): ?>
                        <div class="recently-viewed-card">
                            <a href="<?php echo url('product_details?id=' . $viewed['id']); ?>">
                                <div class="recently-viewed-image">
                                    <?php if (!empty($viewed['image'])): ?>
                                        <img src="<?php echo asset('images/products/' . $viewed['image']); ?>" 
                                             alt="<?php echo e($viewed['name']); ?>"
                                             onerror="this.src='<?php echo asset('images/product-placeholder.jpg'); ?>'">
                                    <?php else: ?>
                                        <div class="no-image">
                                            <?php echo icon('package', 20); ?>
                                        </div>
                                    <?php endif; ?>
                                </div>
                                <h4><?php echo e($viewed['name']); ?></h4>
                                <p><?php echo CURRENCY_SYMBOL . number_format($viewed['price'], 2); ?></p>
                            </a>
                        </div>
                    <?php endif; ?>
                <?php endforeach; ?>
            </div>
        </div>
    </section>
    <?php endif; ?>
</div>

<?php 
$relatedProducts->close();
require_once __DIR__ . '/../../backend/includes/footer.php'; 
?>