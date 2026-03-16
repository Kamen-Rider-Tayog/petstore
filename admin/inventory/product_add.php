<?php
require_once '../includes/header.php';
require_once '../includes/sidebar.php';

$message = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $productName = trim($_POST['product_name'] ?? '');
    $category = trim($_POST['category'] ?? '');
    $price = (float)($_POST['price'] ?? 0);
    $quantity = (int)($_POST['quantity_in_stock'] ?? 0);
    $supplierId = (int)($_POST['supplier_id'] ?? 0);

    // Handle file upload
    $image = '';
    if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
        $uploadDir = '../assets/uploads/products/';
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }

        $fileName = uniqid() . '_' . basename($_FILES['image']['name']);
        $uploadFile = $uploadDir . $fileName;

        if (move_uploaded_file($_FILES['image']['tmp_name'], $uploadFile)) {
            $image = $fileName;
        }
    }

    if (empty($productName) || empty($category) || $price <= 0 || $quantity < 0) {
        $message = 'Please fill in all required fields correctly.';
    } else {
        $stmt = $conn->prepare("INSERT INTO products (product_name, category, price, quantity_in_stock, supplier_id, image) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->bind_param('ssdiis', $productName, $category, $price, $quantity, $supplierId, $image);

        if ($stmt->execute()) {
            header('Location: products.php?message=Product added successfully');
            exit();
        } else {
            $message = 'Error adding product: ' . $conn->error;
        }
    }
}

// Get suppliers for dropdown
$suppliers = $conn->query("SELECT id, supplier_name FROM suppliers ORDER BY supplier_name");
?>

<main class="admin-main">
    <h2>Add New Product</h2>

    <?php if ($message): ?>
        <div style="background: #f8d7da; color: #721c24; padding: 1rem; border-radius: 4px; margin-bottom: 1rem;">
            <?php echo htmlspecialchars($message); ?>
        </div>
    <?php endif; ?>

    <div style="background: #fff; padding: 2rem; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1);">
        <form method="post" enctype="multipart/form-data">
            <div class="form-group">
                <label for="product_name">Product Name *</label>
                <input type="text" id="product_name" name="product_name" required>
            </div>

            <div class="form-group">
                <label for="category">Category *</label>
                <select id="category" name="category" required>
                    <option value="">Select Category</option>
                    <option value="food">Food</option>
                    <option value="toys">Toys</option>
                    <option value="beds">Beds</option>
                    <option value="grooming">Grooming</option>
                    <option value="health">Health</option>
                    <option value="accessories">Accessories</option>
                    <option value="other">Other</option>
                </select>
            </div>

            <div class="form-group">
                <label for="price">Price (₱) *</label>
                <input type="number" id="price" name="price" step="0.01" min="0" required>
            </div>

            <div class="form-group">
                <label for="quantity_in_stock">Quantity in Stock *</label>
                <input type="number" id="quantity_in_stock" name="quantity_in_stock" min="0" required>
            </div>

            <div class="form-group">
                <label for="supplier_id">Supplier</label>
                <select id="supplier_id" name="supplier_id">
                    <option value="0">No Supplier</option>
                    <?php while ($supplier = $suppliers->fetch_assoc()): ?>
                        <option value="<?php echo $supplier['id']; ?>"><?php echo htmlspecialchars($supplier['supplier_name']); ?></option>
                    <?php endwhile; ?>
                </select>
            </div>

            <div class="form-group">
                <label for="image">Product Image</label>
                <input type="file" id="image" name="image" accept="image/*">
                <small>Leave empty to add image later</small>
            </div>

            <div style="margin-top: 2rem;">
                <button type="submit" class="btn btn-success">Add Product</button>
                <a href="products.php" class="btn" style="margin-left: 1rem;">Cancel</a>
            </div>
        </form>
    </div>
</main>

<?php require_once '../includes/footer.php'; ?>