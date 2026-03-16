<?php
require_once '../../backend/config/database.php';

$dogsSql = "SELECT * FROM pets WHERE species = 'Dog' ORDER BY id ASC";
$dogs = $conn->query($dogsSql);
?>


<link rel="stylesheet" href="../../assets/css/dogs.css">
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dogs</title>
</head>
<body>
    <table>
        <tr>
            <th>Name</th>
            <th>Age</th>
            <th>Price</th>
        </tr>
        <?php
        if($dogs -> num_rows > 0){
            while($row = $dogs -> fetch_assoc()){
                echo "<tr>";
                echo "<td>" . $row['name'] . "</td>";
                echo "<td>" . $row['age'] . " year old" . "</td>";
                echo "<td>" . "₱" . $row['price'] . "</td>";
                echo "</tr>";
            }
        }else {
            echo "<tr><td colspan='3'>No dogs available.</td></tr>";
        }
        ?>
    </table>

    <a href="index">Back to Home</a>
</body>
</html>