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
$stmt = $conn->prepare("SELECT * FROM products WHERE id = ?");
$stmt->bind_param('i', $productId);
$stmt->execute();
$product = $stmt->get_result()->fetch_assoc();

if (!$product) {
    header('Location: products.php');
    exit();
}

// Get suppliers for dropdown
$suppliers = $conn->query("SELECT id, supplier_name FROM suppliers ORDER BY supplier_name");

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $product_name = trim($_POST['product_name'] ?? '');
    $category = trim($_POST['category'] ?? '');
    $price = (float)($_POST['price'] ?? 0);
    $sale_price = !empty($_POST['sale_price']) ? (float)$_POST['sale_price'] : null;
    $quantity_in_stock = (int)($_POST['quantity_in_stock'] ?? 0);
    $supplier_id = (int)($_POST['supplier_id'] ?? 0);
    $featured = isset($_POST['featured']) ? 1 : 0;
    $description = trim($_POST['description'] ?? '');
    $remove_image = isset($_POST['remove_image']) ? 1 : 0;

    // Handle file upload
    $image = $product['image'];
    if ($remove_image) {
        if (!empty($product['image']) && file_exists('../assets/images/' . $product['image'])) {
            unlink('../assets/images/' . $product['image']);
        }
        $image = '';
    }
    
    if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
        $uploadDir = '../assets/uploads/products/';
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }
        
        $fileInfo = pathinfo($_FILES['image']['name']);
        $extension = strtolower($fileInfo['extension']);
        $allowedExtensions = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
        
        if (in_array($extension, $allowedExtensions)) {
            // Delete old image
            if (!empty($product['image']) && file_exists($uploadDir . $product['image'])) {
                unlink($uploadDir . $product['image']);
            }
            
            $fileName = uniqid() . '_' . preg_replace('/[^a-zA-Z0-9]/', '_', $fileInfo['filename']) . '.' . $extension;
            $uploadFile = $uploadDir . $fileName;
            
            if (move_uploaded_file($_FILES['image']['tmp_name'], $uploadFile)) {
                $image = $fileName;
            } else {
                $error = 'Failed to upload image.';
            }
        } else {
            $error = 'Invalid file type. Allowed: jpg, jpeg, png, gif, webp';
        }
    }

    if (empty($product_name) || empty($category) || $price <= 0) {
        $error = 'Please fill in all required fields.';
    } elseif ($supplier_id == 0) {
        $error = 'Please select a supplier.';
    } else {
        $stmt = $conn->prepare("
            UPDATE products 
            SET product_name = ?, category = ?, price = ?, sale_price = ?, quantity_in_stock = ?, 
                supplier_id = ?, featured = ?, description = ?, image = ?
            WHERE id = ?
        ");
        $stmt->bind_param('ssddiisssi', $product_name, $category, $price, $sale_price, $quantity_in_stock, $supplier_id, $featured, $description, $image, $productId);
        
        if ($stmt->execute()) {
            $success = 'Product updated successfully!';
            // Refresh product data
            $stmt = $conn->prepare("SELECT * FROM products WHERE id = ?");
            $stmt->bind_param('i', $productId);
            $stmt->execute();
            $product = $stmt->get_result()->fetch_assoc();
        } else {
            $error = 'Error updating product: ' . $conn->error;
        }
    }
}

$page_title = 'Edit Product - ' . $product['product_name'];
require_once __DIR__ . '/../includes/header.php';

echo '<link rel="stylesheet" href="/Ria-Pet-Store/admin/css/products.css?v=' . time() . '">';
?>

<div class="admin-dashboard">
    <div class="page-header">
        <div class="header-left">
            <a href="product_details.php?id=<?php echo $product['id']; ?>" class="btn btn-outline">
                <?php echo icon('arrow-left', 16); ?> Back to Details
            </a>
        </div>
        <div class="header-right">
            <button type="submit" form="edit-product-form" class="btn btn-primary">
                <?php echo icon('save', 16); ?> Save Changes
            </button>
        </div>
    </div>

    <?php if ($error): ?>
        <div class="message error"><?php echo htmlspecialchars($error); ?></div>
    <?php endif; ?>
    
    <?php if ($success): ?>
        <div class="message success"><?php echo htmlspecialchars($success); ?></div>
    <?php endif; ?>

    <form method="post" id="edit-product-form" class="product-form" enctype="multipart/form-data">
        <div class="form-grid">
            <!-- Basic Information -->
            <div class="info-card">
                <h3><?php echo icon('package', 20); ?> Basic Information</h3>
                <div class="form-group">
                    <label for="product_name">Product Name *</label>
                    <input type="text" id="product_name" name="product_name" class="form-control" 
                           value="<?php echo htmlspecialchars($product['product_name']); ?>" required>
                </div>
                <div class="form-group">
                    <label for="category">Category *</label>
                    <select id="category" name="category" class="form-control" required>
                        <option value="">-- Select Category --</option>
                        <option value="food" <?php echo $product['category'] == 'food' ? 'selected' : ''; ?>>Food</option>
                        <option value="toys" <?php echo $product['category'] == 'toys' ? 'selected' : ''; ?>>Toys</option>
                        <option value="beds" <?php echo $product['category'] == 'beds' ? 'selected' : ''; ?>>Beds</option>
                        <option value="grooming" <?php echo $product['category'] == 'grooming' ? 'selected' : ''; ?>>Grooming</option>
                        <option value="health" <?php echo $product['category'] == 'health' ? 'selected' : ''; ?>>Health</option>
                        <option value="accessories" <?php echo $product['category'] == 'accessories' ? 'selected' : ''; ?>>Accessories</option>
                        <option value="other" <?php echo $product['category'] == 'other' ? 'selected' : ''; ?>>Other</option>
                    </select>
                </div>
                <div class="form-group">
                    <label for="supplier_id">Supplier *</label>
                    <select id="supplier_id" name="supplier_id" class="form-control" required>
                        <option value="0">-- Select Supplier --</option>
                        <?php while ($supplier = $suppliers->fetch_assoc()): ?>
                            <option value="<?php echo $supplier['id']; ?>" <?php echo $product['supplier_id'] == $supplier['id'] ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($supplier['supplier_name']); ?>
                            </option>
                        <?php endwhile; ?>
                    </select>
                </div>
                <div class="form-group">
                    <label class="checkbox-label">
                        <input type="checkbox" name="featured" value="1" <?php echo $product['featured'] ? 'checked' : ''; ?>> Featured Product
                    </label>
                </div>
            </div>

            <!-- Pricing & Stock -->
            <div class="info-card">
                <h3><?php echo icon('credit-card', 20); ?> Pricing & Stock</h3>
                <div class="form-row">
                    <div class="form-group half">
                        <label for="price">Regular Price (₱) *</label>
                        <input type="number" id="price" name="price" class="form-control" step="0.01" min="0" 
                               value="<?php echo $product['price']; ?>" required>
                    </div>
                    <div class="form-group half">
                        <label for="sale_price">Sale Price (₱)</label>
                        <input type="number" id="sale_price" name="sale_price" class="form-control" step="0.01" min="0" 
                               value="<?php echo $product['sale_price']; ?>">
                    </div>
                </div>
                <div class="form-group">
                    <label for="quantity_in_stock">Quantity in Stock</label>
                    <input type="number" id="quantity_in_stock" name="quantity_in_stock" class="form-control" min="0" 
                           value="<?php echo $product['quantity_in_stock']; ?>">
                </div>
            </div>

            <!-- Description & Image -->
            <div class="info-card full-width">
                <h3><?php echo icon('file', 20); ?> Description & Image</h3>
                <div class="form-group">
                    <label for="description">Product Description</label>
                    <textarea id="description" name="description" rows="5" class="form-control" 
                              placeholder="Describe the product..."><?php echo htmlspecialchars($product['description'] ?? ''); ?></textarea>
                </div>
                <div class="form-group">
                    <label>Current Image</label>
                    <?php if (!empty($product['image'])): ?>
                        <div class="current-image">
                            <img src="../assets/images/<?php echo htmlspecialchars($product['image']); ?>" class="image-preview">
                            <label class="checkbox-label">
                                <input type="checkbox" name="remove_image" value="1"> Remove current image
                            </label>
                        </div>
                    <?php else: ?>
                        <p class="no-image-text">No image uploaded</p>
                    <?php endif; ?>
                </div>
                <div class="form-group">
                    <label for="image">Upload New Image</label>
                    <input type="file" id="image" name="image" class="form-control" accept="image/*">
                    <small class="help-text">Accepted formats: JPG, JPEG, PNG, GIF, WEBP</small>
                </div>
            </div>
        </div>
    </form>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>