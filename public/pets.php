<?php
require_once '../backend/config/database.php';

$petsql = "SELECT * FROM pets ORDER BY id ASC";
$pets = $conn->query($petsql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pets</title>
    <style>
        table { border-collapse: collapse; width: 100%; }
        th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
        th { background-color: #f2f2f2; }
        img { border-radius: 5px; object-fit: cover; }
    </style>
</head>
<body>
    <h1>Our Pets</h1>
    
    <table>
        <tr>
            <th>Photo</th>
            <th>Name</th>
            <th>Species</th>
            <th>Age</th>
            <th>Price</th>
            <th>Actions</th>
        </tr>
        
        <?php if($pets->num_rows > 0) : ?>
            <?php while($row = $pets->fetch_assoc()) : ?>
                <tr>
                    <td>
                        <?php if(!empty($row['image'])): ?>
                        <img src="../assets/uploads/pets/<?php echo htmlspecialchars($row['image']); ?>" 
                             width="50" height="50">
                        <?php else: ?>
                        No photo
                        <?php endif; ?>
                    </td>
                    <td><?php echo htmlspecialchars($row['name']); ?></td>
                    <td><?php echo htmlspecialchars($row['species']); ?></td>
                    <td><?php echo $row['age']; ?> year old</td>
                    <td>₱<?php echo $row['price']; ?></td>
                    <td>
                        <a href="pet_details?id=<?php echo $row['id']; ?>">View</a>
                        <?php if(isset($_SESSION['user_id'])): ?>
                        | <a href="edit_pet?id=<?php echo $row['id']; ?>">Edit</a>
                        | <a href="delete_pet?id=<?php echo $row['id']; ?>" 
                             onclick="return confirm('Are you sure?')">Delete</a>
                        <?php endif; ?>
                    </td>
                </tr>
            <?php endwhile; ?>
        <?php else : ?>
            <tr><td colspan='6'>No pets available.</td></tr>
        <?php endif; ?>
    </table>

    <br>
    <a href="index">Back to Home</a>
</body>
</html>