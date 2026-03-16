<?php
require_once '../includes/header.php';
require_once '../includes/sidebar.php';

$productId = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$message = '';

if (!$productId) {
    header('Location: products.php');
    exit();
}

// Get product data
$stmt = $conn->prepare("SELECT p.*, s.supplier_name FROM products p LEFT JOIN suppliers s ON p.supplier_id = s.id WHERE p.id = ?");
$stmt->bind_param('i', $productId);
$stmt->execute();
$product = $stmt->get_result()->fetch_assoc();

if (!$product) {
    header('Location: products.php');
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['confirm_delete'])) {
    // Delete image file if exists
    if (!empty($product['image'])) {
        $uploadDir = '../assets/uploads/products/';
        if (file_exists($uploadDir . $product['image'])) {
            unlink($uploadDir . $product['image']);
        }
    }

    // Delete product from database
    $stmt = $conn->prepare("DELETE FROM products WHERE id = ?");
    $stmt->bind_param('i', $productId);

    if ($stmt->execute()) {
        header('Location: products.php?message=Product deleted successfully');
        exit();
    } else {
        $message = 'Error deleting product: ' . $conn->error;
    }
}
?>

<main class="admin-main">
    <h2>Delete Product</h2>

    <?php if ($message): ?>
        <div style="background: #f8d7da; color: #721c24; padding: 1rem; border-radius: 4px; margin-bottom: 1rem;">
            <?php echo htmlspecialchars($message); ?>
        </div>
    <?php endif; ?>

    <div style="background: #fff; padding: 2rem; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1);">
        <div style="background: #fff3cd; border: 1px solid #ffeaa7; padding: 1rem; border-radius: 4px; margin-bottom: 2rem;">
            <h3 style="color: #856404; margin-top: 0;">⚠️ Warning: This action cannot be undone!</h3>
            <p style="margin-bottom: 0;">Are you sure you want to delete this product? This will permanently remove the product from the database and delete any associated image files.</p>
        </div>

        <div style="display: grid; grid-template-columns: 1fr 2fr; gap: 2rem; margin-bottom: 2rem;">
            <div>
                <?php if (!empty($product['image'])): ?>
                    <img src="../assets/images/<?php echo htmlspecialchars($product['image']); ?>" style="width: 100%; max-width: 200px; height: auto; border-radius: 4px; border: 1px solid #ddd;">
                <?php else: ?>
                    <div style="width: 200px; height: 200px; background: #f8f9fa; border: 1px solid #ddd; border-radius: 4px; display: flex; align-items: center; justify-content: center; color: #6c757d;">
                        No Image
                    </div>
                <?php endif; ?>
            </div>

            <div>
                <h3><?php echo htmlspecialchars($product['product_name']); ?></h3>
                <table style="width: 100%; border-collapse: collapse;">
                    <tr>
                        <td style="padding: 0.5rem 0; font-weight: bold; width: 120px;">Category:</td>
                        <td style="padding: 0.5rem 0;"><?php echo htmlspecialchars(ucfirst($product['category'])); ?></td>
                    </tr>
                    <tr>
                        <td style="padding: 0.5rem 0; font-weight: bold;">Price:</td>
                        <td style="padding: 0.5rem 0;">₱<?php echo number_format($product['price'], 2); ?></td>
                    </tr>
                    <tr>
                        <td style="padding: 0.5rem 0; font-weight: bold;">Stock:</td>
                        <td style="padding: 0.5rem 0;"><?php echo $product['quantity_in_stock']; ?> units</td>
                    </tr>
                    <tr>
                        <td style="padding: 0.5rem 0; font-weight: bold;">Supplier:</td>
                        <td style="padding: 0.5rem 0;"><?php echo htmlspecialchars($product['supplier_name'] ?: 'None'); ?></td>
                    </tr>
                </table>
            </div>
        </div>

        <form method="post">
            <div style="display: flex; gap: 1rem;">
                <button type="submit" name="confirm_delete" value="1" class="btn" style="background: #dc3545; color: white; border: none;" onclick="return confirm('Are you absolutely sure you want to delete this product?')">Delete Product</button>
                <a href="products.php" class="btn">Cancel</a>
            </div>
        </form>
    </div>
</main>

<?php require_once '../includes/footer.php'; ?>