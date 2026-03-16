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

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $productName = trim($_POST['product_name'] ?? '');
    $category = trim($_POST['category'] ?? '');
    $price = (float)($_POST['price'] ?? 0);
    $quantity = (int)($_POST['quantity_in_stock'] ?? 0);
    $supplierId = (int)($_POST['supplier_id'] ?? 0);

    // Handle file upload
    $image = $product['image']; // Keep existing image by default
    if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
        $uploadDir = '../assets/uploads/products/';
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }

        // Delete old image if exists
        if (!empty($product['image']) && file_exists($uploadDir . $product['image'])) {
            unlink($uploadDir . $product['image']);
        }

        $fileName = uniqid() . '_' . basename($_FILES['image']['name']);
        $uploadFile = $uploadDir . $fileName;

        if (move_uploaded_file($_FILES['image']['tmp_name'], $uploadFile)) {
            $image = $fileName;
        }
    } elseif (isset($_POST['remove_image'])) {
        // Remove image
        $uploadDir = '../assets/uploads/products/';
        if (!empty($product['image']) && file_exists($uploadDir . $product['image'])) {
            unlink($uploadDir . $product['image']);
        }
        $image = '';
    }

    if (empty($productName) || empty($category) || $price <= 0 || $quantity < 0) {
        $message = 'Please fill in all required fields correctly.';
    } else {
        $stmt = $conn->prepare("UPDATE products SET product_name = ?, category = ?, price = ?, quantity_in_stock = ?, supplier_id = ?, image = ? WHERE id = ?");
        $stmt->bind_param('ssdissi', $productName, $category, $price, $quantity, $supplierId, $image, $productId);

        if ($stmt->execute()) {
            header('Location: products.php?message=Product updated successfully');
            exit();
        } else {
            $message = 'Error updating product: ' . $conn->error;
        }
    }
}

// Get suppliers for dropdown
$suppliers = $conn->query("SELECT id, supplier_name FROM suppliers ORDER BY supplier_name");
?>

<main class="admin-main">
    <h2>Edit Product</h2>

    <?php if ($message): ?>
        <div style="background: #f8d7da; color: #721c24; padding: 1rem; border-radius: 4px; margin-bottom: 1rem;">
            <?php echo htmlspecialchars($message); ?>
        </div>
    <?php endif; ?>

    <div style="background: #fff; padding: 2rem; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1);">
        <form method="post" enctype="multipart/form-data">
            <div class="form-group">
                <label for="product_name">Product Name *</label>
                <input type="text" id="product_name" name="product_name" value="<?php echo htmlspecialchars($product['product_name']); ?>" required>
            </div>

            <div class="form-group">
                <label for="category">Category *</label>
                <select id="category" name="category" required>
                    <option value="">Select Category</option>
                    <option value="food" <?php echo $product['category'] === 'food' ? 'selected' : ''; ?>>Food</option>
                    <option value="toys" <?php echo $product['category'] === 'toys' ? 'selected' : ''; ?>>Toys</option>
                    <option value="beds" <?php echo $product['category'] === 'beds' ? 'selected' : ''; ?>>Beds</option>
                    <option value="grooming" <?php echo $product['category'] === 'grooming' ? 'selected' : ''; ?>>Grooming</option>
                    <option value="health" <?php echo $product['category'] === 'health' ? 'selected' : ''; ?>>Health</option>
                    <option value="accessories" <?php echo $product['category'] === 'accessories' ? 'selected' : ''; ?>>Accessories</option>
                    <option value="other" <?php echo $product['category'] === 'other' ? 'selected' : ''; ?>>Other</option>
                </select>
            </div>

            <div class="form-group">
                <label for="price">Price (₱) *</label>
                <input type="number" id="price" name="price" value="<?php echo $product['price']; ?>" step="0.01" min="0" required>
            </div>

            <div class="form-group">
                <label for="quantity_in_stock">Quantity in Stock *</label>
                <input type="number" id="quantity_in_stock" name="quantity_in_stock" value="<?php echo $product['quantity_in_stock']; ?>" min="0" required>
            </div>

            <div class="form-group">
                <label for="supplier_id">Supplier</label>
                <select id="supplier_id" name="supplier_id">
                    <option value="0">No Supplier</option>
                    <?php while ($supplier = $suppliers->fetch_assoc()): ?>
                        <option value="<?php echo $supplier['id']; ?>" <?php echo $product['supplier_id'] == $supplier['id'] ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($supplier['supplier_name']); ?>
                        </option>
                    <?php endwhile; ?>
                </select>
            </div>

            <div class="form-group">
                <label>Current Image</label>
                <div>
                    <?php if (!empty($product['image'])): ?>
                        <img src="../assets/images/<?php echo htmlspecialchars($product['image']); ?>" width="100" height="100" style="object-fit: cover; border-radius: 4px; border: 1px solid #ddd;">
                        <br>
                        <label><input type="checkbox" name="remove_image" value="1"> Remove current image</label>
                    <?php else: ?>
                        No image uploaded
                    <?php endif; ?>
                </div>
            </div>

            <div class="form-group">
                <label for="image">Upload New Image</label>
                <input type="file" id="image" name="image" accept="image/*">
                <small>Leave empty to keep current image (or check "Remove" above)</small>
            </div>

            <div style="margin-top: 2rem;">
                <button type="submit" class="btn btn-success">Update Product</button>
                <a href="products.php" class="btn" style="margin-left: 1rem;">Cancel</a>
            </div>
        </form>
    </div>
</main>

<?php require_once '../includes/footer.php'; ?>