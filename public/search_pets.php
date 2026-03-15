<?php
require_once '../backend/config/database.php';

$species = isset($_GET['species']) ? $_GET['species'] : '';

if($species) {
    $petsql = "SELECT * FROM pets WHERE species = '$species' ORDER BY id ASC";
} else {
    $petsql = "SELECT * FROM pets ORDER BY id ASC";
}
$pets = $conn->query($petsql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Search Pets</title>
</head>
<body>
    <h1>Search Pets by Species</h1>

    <form method="get">
        <select name="species">
            <option value="">All Species</option>
            <option value="dog" <?php echo $species == 'dog' ? 'selected' : ''; ?>>Dog</option>
            <option value="cat" <?php echo $species == 'cat' ? 'selected' : ''; ?>>Cat</option>
            <option value="rabbit" <?php echo $species == 'rabbit' ? 'selected' : ''; ?>>Rabbit</option>
            <option value="bird" <?php echo $species == 'bird' ? 'selected' : ''; ?>>Bird</option>
            <option value="hamster" <?php echo $species == 'hamster' ? 'selected' : ''; ?>>Hamster</option>
        </select>
        <button type="submit">Search</button>
    </form>

    <br>

    <table border="1" cellpadding="5">
        <tr>
            <th>Name</th>
            <th>Species</th>
            <th>Age</th>
            <th>Price</th>
        </tr>
        
        <?php if($pets->num_rows > 0) : ?>
            <?php while($row = $pets->fetch_assoc()) : ?>
                <tr>
                    <td><?php echo $row['name']; ?></td>
                    <td><?php echo $row['species']; ?></td>
                    <td><?php echo $row['age']; ?> year old</td>
                    <td>₱<?php echo $row['price']; ?></td>
                </tr>
            <?php endwhile; ?>
        <?php else : ?>
            <tr><td colspan='4'>No pets found.</td></tr>
        <?php endif; ?>
    </table>

    <br>
    <a href="index.php">Back to Home</a>
</body>
</html>