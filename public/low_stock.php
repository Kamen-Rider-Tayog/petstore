<?php
require_once '../backend/config/database.php';

$productsql = "SELECT * FROM products WHERE quantity_in_stock < 10 ORDER BY quantity_in_stock ASC";
$products = $conn->query($productsql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Low Stock Report</title>
</head>
<body>
    <h1>Low Stock Report</h1>
    <p>Products with quantity less than 10</p>

    <table border="1" cellpadding="5">
        <tr>
            <th>Name</th>
            <th>Category</th>
            <th>Quantity</th>
            <th>Price</th>
        </tr>
        <?php
        if($products->num_rows > 0){
            while($row = $products->fetch_assoc()){
                echo "<tr>";
                echo "<td>" . $row['product_name'] . "</td>";
                echo "<td>" . $row['category'] . "</td>";
                echo "<td>" . $row['quantity_in_stock'] . "</td>";
                echo "<td>₱" . $row['price'] . "</td>";
                echo "</tr>";
            }
        } else {
            echo "<tr><td colspan='4'>No low stock items.</td></tr>";
        }
        ?>
    </table>

    <br>
    <a href="index.php">Back to Home</a>
</body>
</html>