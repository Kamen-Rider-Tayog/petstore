<?php
session_name('petstore_session');
session_start();

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

// Check if product is in any orders
$orderCheck = $conn->prepare("SELECT COUNT(*) as count FROM order_items WHERE product_id = ?");
$orderCheck->bind_param('i', $productId);
$orderCheck->execute();
$orderCount = $orderCheck->get_result()->fetch_assoc()['count'];

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['confirm_delete'])) {
    if ($orderCount > 0) {
        $error = 'Cannot delete product that has been ordered. Consider marking it as inactive instead.';
    } else {
        // Delete image file if exists
        if (!empty($product['image'])) {
            $imagePath = '../assets/images/' . $product['image'];
            if (file_exists($imagePath)) {
                unlink($imagePath);
            }
        }
        
        // Delete product
        $stmt = $conn->prepare("DELETE FROM products WHERE id = ?");
        $stmt->bind_param('i', $productId);
        
        if ($stmt->execute()) {
            header('Location: products.php?message=Product deleted successfully');
            exit();
        } else {
            $error = 'Error deleting product: ' . $conn->error;
        }
    }
}

$page_title = 'Delete Product - ' . $product['product_name'];
require_once __DIR__ . '/../includes/header.php';

echo '<link rel="stylesheet" href="/Ria-Pet-Store/admin/css/products.css?v=' . time() . '">';
?>

<div class="admin-dashboard">
    <div class="page-header">
        <div class="header-left">
            <a href="product_details.php?id=<?php echo $product['id']; ?>" class="btn btn-outline">
                <?php echo icon('arrow-left', 16); ?> Back to Product
            </a>
        </div>
    </div>

    <?php if ($error): ?>
        <div class="message error"><?php echo htmlspecialchars($error); ?></div>
    <?php endif; ?>

    <div class="delete-confirmation">
        <div class="warning-box">
            <?php echo icon('alert-triangle', 48); ?>
            <h3>Warning: This action cannot be undone!</h3>
            <p>Are you sure you want to delete this product?</p>
            <?php if ($orderCount > 0): ?>
                <p class="warning-text">
                    This product has been ordered <?php echo $orderCount; ?> time(s) and cannot be deleted.
                </p>
            <?php endif; ?>
        </div>

        <div class="product-summary">
            <h3>Product Details</h3>
            <div class="summary-grid">
                <div class="summary-item">
                    <span class="label">Product ID:</span>
                    <span class="value">#<?php echo str_pad($product['id'], 6, '0', STR_PAD_LEFT); ?></span>
                </div>
                <div class="summary-item">
                    <span class="label">Product Name:</span>
                    <span class="value"><?php echo htmlspecialchars($product['product_name']); ?></span>
                </div>
                <div class="summary-item">
                    <span class="label">Category:</span>
                    <span class="value"><?php echo htmlspecialchars(ucfirst($product['category'])); ?></span>
                </div>
                <div class="summary-item">
                    <span class="label">Supplier:</span>
                    <span class="value"><?php echo htmlspecialchars($product['supplier_name'] ?? 'N/A'); ?></span>
                </div>
                <div class="summary-item">
                    <span class="label">Price:</span>
                    <span class="value">₱<?php echo number_format($product['price'], 2); ?></span>
                </div>
                <div class="summary-item">
                    <span class="label">Stock:</span>
                    <span class="value"><?php echo $product['quantity_in_stock']; ?> units</span>
                </div>
            </div>
        </div>

        <?php if ($orderCount == 0): ?>
            <form method="post" class="delete-form">
                <div class="action-buttons">
                    <button type="submit" name="confirm_delete" class="btn btn-danger">
                        <?php echo icon('trash', 16); ?> Yes, Delete Product
                    </button>
                    <a href="product_details.php?id=<?php echo $product['id']; ?>" class="btn btn-outline">Cancel</a>
                </div>
            </form>
        <?php else: ?>
            <div class="action-buttons">
                <a href="product_details.php?id=<?php echo $product['id']; ?>" class="btn btn-primary">Back to Product</a>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>