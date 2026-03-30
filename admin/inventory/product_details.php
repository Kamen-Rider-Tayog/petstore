<?php
session_name('petstore_session');
session_start();

// Check if user is logged in as admin
if (!isset($_SESSION['is_admin']) || $_SESSION['is_admin'] !== true) {
    header('Location: ' . url('login?error=Access denied'));
    exit;
}

require_once __DIR__ . '/../../backend/config/database.php';
require_once __DIR__ . '/../../backend/functions/helpers.php';

$productId = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if (!$productId) {
    header('Location: products.php');
    exit();
}

// Get product details
$stmt = $conn->prepare("
    SELECT p.*, s.supplier_name 
    FROM products p 
    LEFT JOIN suppliers s ON p.supplier_id = s.id 
    WHERE p.id = ?
");
$stmt->bind_param('i', $productId);
$stmt->execute();
$product = $stmt->get_result()->fetch_assoc();

if (!$product) {
    header('Location: products.php');
    exit();
}

$page_title = 'Product Details - ' . $product['product_name'];
require_once __DIR__ . '/../includes/header.php';

echo '<link rel="stylesheet" href="/Ria-Pet-Store/admin/css/products.css?v=' . time() . '">';
?>

<div class="admin-dashboard">
    <div class="page-header">
        <div class="header-left">
            <a href="products.php" class="btn btn-outline">
                <?php echo icon('arrow-left', 16); ?> Back to Products
            </a>
        </div>
        <div class="header-right">
            <div class="action-buttons">
                <a href="edit_product.php?id=<?php echo $product['id']; ?>" class="btn btn-primary">
                    <?php echo icon('edit', 16); ?> Edit Product
                </a>
                <a href="delete_product.php?id=<?php echo $product['id']; ?>" class="btn btn-danger" >
                    <?php echo icon('trash', 16); ?> Delete Product
                </a>
            </div>
        </div>
    </div>

    <!-- Product Header -->
    <div class="product-header">
        <div class="product-image-container">
            <?php if (!empty($product['image'])): ?>
                <img src="../assets/images/<?php echo htmlspecialchars($product['image']); ?>" class="product-detail-image">
            <?php else: ?>
                <div class="no-image-large">
                    <?php echo icon('image', 48); ?>
                    <p>No image available</p>
                </div>
            <?php endif; ?>
        </div>
        <div class="product-info">
            <h1><?php echo htmlspecialchars($product['product_name']); ?></h1>
            <div class="product-badges">
                <?php
                $stockStatus = '';
                if ($product['quantity_in_stock'] == 0) {
                    $stockStatus = 'critical';
                    $stockLabel = 'Out of Stock';
                } elseif ($product['quantity_in_stock'] <= 10) {
                    $stockStatus = 'low-stock';
                    $stockLabel = 'Low Stock';
                } else {
                    $stockStatus = 'in-stock';
                    $stockLabel = 'In Stock';
                }
                ?>
                <span class="badge stock-badge-<?php echo $stockStatus; ?>">
                    <?php echo $stockLabel; ?>
                </span>
                <?php if (!empty($product['sale_price']) && $product['sale_price'] > 0): ?>
                    <span class="badge sale-badge">On Sale</span>
                <?php endif; ?>
                <?php if (!empty($product['featured']) && $product['featured'] == 1): ?>
                    <span class="badge featured-badge">Featured</span>
                <?php endif; ?>
            </div>
            <div class="product-price-detail">
                <?php if (!empty($product['sale_price']) && $product['sale_price'] > 0): ?>
                    <span class="original-price-large">₱<?php echo number_format($product['price'], 2); ?></span>
                    <span class="sale-price-large">₱<?php echo number_format($product['sale_price'], 2); ?></span>
                <?php else: ?>
                    <span class="regular-price-large">₱<?php echo number_format($product['price'], 2); ?></span>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Product Details Grid -->
    <div class="details-grid">
        <div class="info-card">
            <h3><?php echo icon('info', 20); ?> Product Information</h3>
            <table class="info-table">
                <tr>
                    <td class="label">Product ID:</td>
                    <td>#<?php echo str_pad($product['id'], 6, '0', STR_PAD_LEFT); ?></td>
                </tr>
                <tr>
                    <td class="label">Category:</td>
                    <td><?php echo htmlspecialchars(ucfirst($product['category'])); ?></td>
                </tr>
                <tr>
                    <td class="label">Supplier:</td>
                    <td><?php echo htmlspecialchars($product['supplier_name'] ?? 'N/A'); ?></td>
                </tr>
                <tr>
                    <td class="label">Stock Quantity:</td>
                    <td><?php echo $product['quantity_in_stock']; ?> units</td>
                </tr>
                <tr>
                    <td class="label">Created:</td>
                    <td><?php echo date('F j, Y g:i A', strtotime($product['created_at'] ?? $product['updated_at'])); ?></td>
                </tr>
                <?php if (!empty($product['updated_at']) && $product['updated_at'] != $product['created_at']): ?>
                <tr>
                    <td class="label">Last Updated:</td>
                    <td><?php echo date('F j, Y g:i A', strtotime($product['updated_at'])); ?></td>
                </tr>
                <?php endif; ?>
            </table>
        </div>

        <?php if (!empty($product['description'])): ?>
        <div class="info-card">
            <h3><?php echo icon('file', 20); ?> Description</h3>
            <div class="description-content">
                <?php echo nl2br(htmlspecialchars($product['description'])); ?>
            </div>
        </div>
        <?php endif; ?>
    </div>

    
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>