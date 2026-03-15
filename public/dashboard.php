<?php
require_once '../backend/includes/header.php';
require_once '../backend/config/database.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login?error=Please log in first');
    exit;
}

$petCount = $conn->query("SELECT COUNT(*) FROM pets")->fetch_row()[0];
$customerCount = $conn->query("SELECT COUNT(*) FROM customers")->fetch_row()[0];
$productCount = $conn->query("SELECT COUNT(*) FROM products")->fetch_row()[0];
$userCount = $conn->query("SELECT COUNT(*) FROM users")->fetch_row()[0];
?>

<h1>Dashboard</h1>
<p>Welcome back, <?php echo $_SESSION['full_name'] ?: $_SESSION['username']; ?>!</p>

<h2>Store Statistics</h2>
<ul>
    <li>Total Pets: <?php echo $petCount; ?></li>
    <li>Total Customers: <?php echo $customerCount; ?></li>
    <li>Total Products: <?php echo $productCount; ?></li>
    <li>Total Users: <?php echo $userCount; ?></li>
</ul>

<h2>Quick Actions</h2>
<ul>
    <li><a href="add_pet">Add New Pet</a></li>
    <li><a href="pets">Manage Pets</a></li>
    <li><a href="low_stock">Check Low Stock</a></li>
</ul>

<?php require_once '../backend/includes/footer.php'; ?>