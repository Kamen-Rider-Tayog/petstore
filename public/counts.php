<?php
require_once '../backend/config/database.php';

$petCount = $conn->query("SELECT COUNT(*) FROM pets")->fetch_row()[0];

$customerCount = $conn->query("SELECT COUNT(*) FROM customers")->fetch_row()[0];

$productCount = $conn->query("SELECT COUNT(*) FROM products")->fetch_row()[0];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Count table</title>
</head>
<body>
    <h1>Count Table</h1>
    <p>Pets: <?php echo $petCount; ?></p>
    <p>Customers: <?php echo $customerCount; ?></p>
    <p>Products: <?php echo $productCount; ?></p>

    <a href="index">Back to Home</a>
</body>
</html>

