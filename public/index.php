<?php require_once '../backend/includes/header.php'; ?>
<?php require_once '../backend/config/database.php'; ?>

<?php if (isset($_GET['message'])): ?>
    <p><?php echo $_GET['message']; ?></p>
<?php endif; ?>

<p>Welcome to our pet store!</p>

<h2>Navigation</h2>

<div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(250px, 1fr)); gap: 20px; margin: 20px 0;">
    
    <!-- Pets Section -->
    <div style="border: 1px solid #ddd; padding: 15px; border-radius: 5px;">
        <h3 style="margin-top: 0;">🐾 Pets</h3>
        <ul style="list-style-type: none; padding-left: 0;">
            <li><a href="pets">View All Pets</a></li>
            <li><a href="dogs">View Only Dogs</a></li>
            <li><a href="search_pets">Search Pets by Species</a></li>
            <li><a href="ajax_demo">AJAX Live Search</a></li>
            <?php if (isset($_SESSION['user_id'])): ?>
                <li><a href="add_pet">Add New Pet</a></li>
            <?php endif; ?>
        </ul>
    </div>
    
    <!-- Products Section -->
    <div style="border: 1px solid #ddd; padding: 15px; border-radius: 5px;">
        <h3 style="margin-top: 0;">📦 Products</h3>
        <ul style="list-style-type: none; padding-left: 0;">
            <li><a href="products">Product Catalog</a></li>
            <li><a href="products_by_supplier">Products by Supplier</a></li>
            <li><a href="low_stock">Low Stock Report</a></li>
            <?php if (isset($_SESSION['user_id'])): ?>
                <li><a href="supplier_products">Supplier Inventory (Admin)</a></li>
            <?php endif; ?>
        </ul>
    </div>
    
    <!-- Customers Section -->
    <div style="border: 1px solid #ddd; padding: 15px; border-radius: 5px;">
        <h3 style="margin-top: 0;">👥 Customers</h3>
        <ul style="list-style-type: none; padding-left: 0;">
            <li><a href="customers">View All Customers</a></li>
            <li><a href="customer_orders">Customer Orders History</a></li>
            <li><a href="counts">Record Counts</a></li>
        </ul>
    </div>
    
    <!-- Employees Section -->
    <div style="border: 1px solid #ddd; padding: 15px; border-radius: 5px;">
        <h3 style="margin-top: 0;">👔 Employees</h3>
        <ul style="list-style-type: none; padding-left: 0;">
            <li><a href="employee_sales">Sales Performance</a></li>
            <li><a href="appointments">Appointments</a></li>
        </ul>
    </div>
    
    <!-- Shopping Section (for logged in users) -->
    <?php if (isset($_SESSION['user_id'])): ?>
    <div style="border: 1px solid #ddd; padding: 15px; border-radius: 5px; background: #f9f9f9;">
        <h3 style="margin-top: 0;">🛒 Shopping</h3>
        <ul style="list-style-type: none; padding-left: 0;">
            <li><a href="cart">View Cart (<span id="cart-count">0</span>)</a></li>
            <li><a href="checkout">Checkout</a></li>
        </ul>
    </div>
    <?php endif; ?>
    
    <!-- Account Section -->
    <div style="border: 1px solid #ddd; padding: 15px; border-radius: 5px;">
        <h3 style="margin-top: 0;">🔑 Account</h3>
        <ul style="list-style-type: none; padding-left: 0;">
            <?php if (isset($_SESSION['user_id'])): ?>
                <li><a href="dashboard">Dashboard</a></li>
                <li><a href="logout">Logout</a></li>
            <?php else: ?>
                <li><a href="login">Login</a></li>
                <li><a href="register">Register</a></li>
            <?php endif; ?>
        </ul>
    </div>
    
</div>

<?php
$petCount = $conn->query("SELECT COUNT(*) FROM pets")->fetch_row()[0];
$customerCount = $conn->query("SELECT COUNT(*) FROM customers")->fetch_row()[0];
$productCount = $conn->query("SELECT COUNT(*) FROM products")->fetch_row()[0];
$employeeCount = $conn->query("SELECT COUNT(*) FROM employees")->fetch_row()[0];
?>

<h3>Quick Stats</h3>
<div style="display: flex; gap: 20px; flex-wrap: wrap;">
    <div style="background: #e3f2fd; padding: 10px 20px; border-radius: 5px;">
        <strong>🐾 Pets:</strong> <?php echo $petCount; ?>
    </div>
    <div style="background: #e8f5e8; padding: 10px 20px; border-radius: 5px;">
        <strong>👥 Customers:</strong> <?php echo $customerCount; ?>
    </div>
    <div style="background: #fff3e0; padding: 10px 20px; border-radius: 5px;">
        <strong>📦 Products:</strong> <?php echo $productCount; ?>
    </div>
    <div style="background: #f3e5f5; padding: 10px 20px; border-radius: 5px;">
        <strong>👔 Employees:</strong> <?php echo $employeeCount; ?>
    </div>
</div>

<script>
// Update cart count if logged in
<?php if (isset($_SESSION['user_id'])): ?>
fetch('../backend/api/cart_count?customer_id=<?php echo $_SESSION['user_id']; ?>')
    .then(response => response.json())
    .then(data => {
        const cartCount = document.getElementById('cart-count');
        if (cartCount) cartCount.textContent = data.count || 0;
    });
<?php endif; ?>
</script>

<?php require_once '../backend/includes/footer.php'; ?>