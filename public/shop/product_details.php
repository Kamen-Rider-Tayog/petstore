<?php
require_once '../../backend/config/database.php';
require_once '../../backend/includes/header.php';
<link rel="stylesheet" href="../../assets/css/product_details.css">


$productId = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if (!$productId) {
    header('Location: products');
    exit;
}

$stmt = $conn->prepare('SELECT * FROM products WHERE id = ? LIMIT 1');
$stmt->bind_param('i', $productId);
$stmt->execute();
$product = $stmt->get_result()->fetch_assoc();

if (!$product) {
    header('Location: products');
    exit;
}

// Track recently viewed products
if (!isset($_SESSION['recently_viewed']) || !is_array($_SESSION['recently_viewed'])) {
    $_SESSION['recently_viewed'] = [];
}

// Remove existing entry if already viewed
foreach ($_SESSION['recently_viewed'] as $key => $item) {
    if ($item['id'] === $productId) {
        unset($_SESSION['recently_viewed'][$key]);
        break;
    }
}

$_SESSION['recently_viewed'] = array_values(array_merge([
    [
        'id' => $productId,
        'name' => $product['product_name'],
        'image' => $product['image'] ?? '',
    ]
], $_SESSION['recently_viewed']));

// Keep only last 5
$_SESSION['recently_viewed'] = array_slice($_SESSION['recently_viewed'], 0, 5);

// Related products (same category)
$relatedStmt = $conn->prepare('SELECT * FROM products WHERE category = ? AND id <> ? ORDER BY product_name LIMIT 4');
$relatedStmt->bind_param('si', $product['category'], $productId);
$relatedStmt->execute();
$relatedProducts = $relatedStmt->get_result();
?>

<h1><?php echo htmlspecialchars($product['product_name']); ?></h1>

<div style="display: grid; grid-template-columns: 1fr 1fr; gap: 30px; align-items: start;">
    <div>
        <?php $imageUrl = (!empty($product['image']) ? asset('images/' . $product['image']) : Config::get('PLACEHOLDER_IMAGE_LARGE')); ?>
        <img src="<?php echo $imageUrl; ?>" alt="<?php echo htmlspecialchars($product['product_name']); ?>" style="max-width: 100%; border: 1px solid #ddd;" />
    </div>

    <div>
        <p><strong>Category:</strong> <?php echo ucfirst(htmlspecialchars($product['category'])); ?></p>
        <p><strong>Price:</strong> ₱<?php echo number_format($product['price'], 2); ?></p>
        <p><strong>Stock:</strong> <?php echo $product['quantity_in_stock'] > 0 ? 'In stock (' . $product['quantity_in_stock'] . ')' : '<span style="color:red;">Out of stock</span>'; ?></p>
        <p><strong>Description:</strong><br /><?php echo nl2br(htmlspecialchars($product['description'] ?: 'No description available.')); ?></p>

        <?php if ($product['quantity_in_stock'] > 0): ?>
        <div style="margin-top: 15px; display: flex; gap: 8px; align-items: center;">
            <label for="qty_<?php echo $productId; ?>">Qty</label>
            <select id="qty_<?php echo $productId; ?>" style="width: 80px; padding: 5px;">
                <?php for ($i = 1; $i <= min(10, $product['quantity_in_stock']); $i++): ?>
                    <option value="<?php echo $i; ?>"><?php echo $i; ?></option>
                <?php endfor; ?>
            </select>
            <button data-add-to-cart="<?php echo $productId; ?>" style="padding: 8px 14px;">Add to Cart</button>
        </div>
        <?php else: ?>
            <p style="color: red;"><strong>Out of stock</strong></p>
        <?php endif; ?>

        <p style="margin-top: 20px;"><a href="products">← Back to Products</a></p>
    </div>
</div>

<?php if ($relatedProducts->num_rows > 0): ?>
    <h2>Related Products</h2>
    <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(220px, 1fr)); gap: 15px;">
        <?php while ($rel = $relatedProducts->fetch_assoc()): ?>
            <div style="border: 1px solid #ddd; padding: 10px; border-radius: 5px; background: #fff;">
                <h4 style="margin: 0 0 8px 0;"><a href="product_details?id=<?php echo $rel['id']; ?>" style="text-decoration: none; color: inherit;"><?php echo htmlspecialchars($rel['product_name']); ?></a></h4>
                <p style="margin: 0;">₱<?php echo number_format($rel['price'], 2); ?></p>
            </div>
        <?php endwhile; ?>
    </div>
<?php endif; ?>

<?php require_once '../../backend/includes/footer.php'; ?>
