<?php
require_once '../backend/config/database.php';

$species = isset($_GET['species']) ? $_GET['species'] : '';
$max_price = isset($_GET['max_price']) ? $_GET['max_price'] : '';
$min_age = isset($_GET['min_age']) ? $_GET['min_age'] : '';

$conditions = [];
$params = [];

if (!empty($species)) {
    $conditions[] = "species = '$species'";
}
if (!empty($max_price) && is_numeric($max_price)) {
    $conditions[] = "price <= $max_price";
}
if (!empty($min_age) && is_numeric($min_age)) {
    $conditions[] = "age >= $min_age";
}

$sql = "SELECT * FROM pets";
if (!empty($conditions)) {
    $sql .= " WHERE " . implode(" AND ", $conditions);
}
$sql .= " ORDER BY name";

$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Advanced Pet Search</title>
</head>
<body>
    <h1>Advanced Pet Search</h1>

    <form method="get">
        <div>
            <label for="species">Species:</label><br>
            <select id="species" name="species">
                <option value="">Any Species</option>
                <option value="dog" <?php echo $species == 'dog' ? 'selected' : ''; ?>>Dog</option>
                <option value="cat" <?php echo $species == 'cat' ? 'selected' : ''; ?>>Cat</option>
                <option value="rabbit" <?php echo $species == 'rabbit' ? 'selected' : ''; ?>>Rabbit</option>
                <option value="bird" <?php echo $species == 'bird' ? 'selected' : ''; ?>>Bird</option>
            </select>
        </div>
        
        <div>
            <label for="max_price">Maximum Price (₱):</label><br>
            <input type="number" id="max_price" name="max_price" min="0" step="0.01" value="<?php echo $max_price; ?>">
        </div>
        
        <div>
            <label for="min_age">Minimum Age (years):</label><br>
            <input type="number" id="min_age" name="min_age" min="0" value="<?php echo $min_age; ?>">
        </div>
        
        <br>
        <button type="submit">Search</button>
    </form>

    <br>

    <?php if (isset($_GET['species']) || isset($_GET['max_price']) || isset($_GET['min_age'])): ?>
        <h2>Search Results</h2>
        
        <?php if ($result->num_rows > 0): ?>
            <table border="1" cellpadding="5">
                <tr>
                    <th>Name</th>
                    <th>Species</th>
                    <th>Age</th>
                    <th>Price</th>
                </tr>
                <?php while($row = $result->fetch_assoc()): ?>
                    <tr>
                        <td><?php echo $row['name']; ?></td>
                        <td><?php echo $row['species']; ?></td>
                        <td><?php echo $row['age']; ?> year old</td>
                        <td>₱<?php echo $row['price']; ?></td>
                    </tr>
                <?php endwhile; ?>
            </table>
        <?php else: ?>
            <p>No pets match your search criteria.</p>
        <?php endif; ?>
    <?php endif; ?>

    <br>
    <a href="index">Back to Home</a>
</body>
</html>