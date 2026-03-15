<?php
require_once '../backend/config/database.php';

$customerSql = "SELECT id, first_name, last_name, email, phone FROM customers ORDER BY id ASC";
$customers = $conn->query($customerSql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Customers</title>
</head>
<body>
    <table>
        <tr>
            <th>First Name</th>
            <th>Last Name</th>
            <th>Email</th>
            <th>Phone</th>
        </tr>
        <?php
        if($customers -> num_rows > 0){
            while($row = $customers -> fetch_assoc()){
                echo "<tr>";
                echo "<td>" . $row['first_name'] . "</td>";
                echo "<td>" . $row['last_name'] . "</td>";
                echo "<td>" . $row['email'] . "</td>";
                echo "<td>" . $row['phone'] . "</td>";
                echo "</tr>";
            }
        }else {
            echo "<tr><td colspan='4'>No customers available.</td></tr>";
        }
        ?>
    </table>

    <a href="index">Back to Home</a>
</body>
</html>