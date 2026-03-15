<?php
require_once '../backend/config/database.php';
require_once '../backend/includes/header.php';

// Get all products
$sql = "SELECT * FROM products ORDER BY category, product_name";
$products = $conn->query($sql);

// Get unique categories for filtering
$categories = $conn->query("SELECT DISTINCT category FROM products ORDER BY category");

// For cart functionality
$customer_id = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : 1; // Default to 1 for testing
?>

<h1>Product Catalog</h1>

<?php if(isset($_SESSION['user_id'])): ?>
<div style="margin-bottom: 20px; text-align: right;">
    <a href="cart">🛒 View Cart (<span id="cart-count">0</span>)</a>
</div>
<?php endif; ?>

<!-- Simple category filter -->
<div style="margin-bottom: 20px;">
    <form method="get">
        <label for="category">Filter by Category:</label>
        <select name="category" id="category" onchange="this.form.submit()">
            <option value="">All Categories</option>
            <?php while($cat = $categories->fetch_assoc()): ?>
                <option value="<?php echo $cat['category']; ?>" 
                    <?php echo (isset($_GET['category']) && $_GET['category'] == $cat['category']) ? 'selected' : ''; ?>>
                    <?php echo ucfirst($cat['category']); ?>
                </option>
            <?php endwhile; ?>
        </select>
    </form>
</div>

<!-- Product grid -->
<div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(250px, 1fr)); gap: 20px;">
    <?php 
    // Reset pointer
    $products = $conn->query($sql);
    
    while($product = $products->fetch_assoc()): 
        // Skip if category filter is active and doesn't match
        if(isset($_GET['category']) && !empty($_GET['category']) && $product['category'] != $_GET['category']) {
            continue;
        }
    ?>
    <div style="border: 1px solid #ddd; padding: 15px; border-radius: 5px; background: white;">
        <h3 style="margin-top: 0; margin-bottom: 10px;"><?php echo htmlspecialchars($product['product_name']); ?></h3>
        
        <p style="margin: 5px 0;"><strong>Category:</strong> <?php echo ucfirst($product['category']); ?></p>
        <p style="margin: 5px 0;"><strong>Price:</strong> ₱<?php echo number_format($product['price'], 2); ?></p>
        <p style="margin: 5px 0;"><strong>Availability:</strong> 
            <?php if($product['quantity_in_stock'] > 0): ?>
                <span style="color: green;">In Stock (<?php echo $product['quantity_in_stock']; ?>)</span>
            <?php else: ?>
                <span style="color: red;">Out of Stock</span>
            <?php endif; ?>
        </p>
        
        <?php if(isset($_SESSION['user_id']) && $product['quantity_in_stock'] > 0): ?>
        <div style="margin-top: 15px;">
            <select id="qty_<?php echo $product['id']; ?>" style="width: 60px; padding: 5px;">
                <?php for($i = 1; $i <= min(5, $product['quantity_in_stock']); $i++): ?>
                <option value="<?php echo $i; ?>"><?php echo $i; ?></option>
                <?php endfor; ?>
            </select>
            <button onclick="addToCart(<?php echo $product['id']; ?>, <?php echo $customer_id; ?>)" style="margin-left: 5px;">
                Add to Cart
            </button>
        </div>
        <?php endif; ?>
    </div>
    <?php endwhile; ?>
</div>

<br>
<a href="index">← Back to Home</a>

<?php if(isset($_SESSION['user_id'])): ?>
<script src="../assets/js/cart.js"></script>
<script>
// Load cart count on page load
updateCartCount(<?php echo $customer_id; ?>);
</script>
<?php endif; ?>

<?php require_once '../backend/includes/footer.php'; ?>