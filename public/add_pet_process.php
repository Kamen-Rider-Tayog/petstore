<?php
require_once '../backend/config/database.php';
require_once '../backend/uploads/handle_upload.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: add_pet');
    exit;
}

$name = $_POST['name'] ?? '';
$species = $_POST['species'] === 'other' ? trim($_POST['other_species'] ?? '') : $_POST['species'] ?? '';
$breed = $_POST['breed'] ?? '';
$age = $_POST['age'] ?? '';
$price = $_POST['price'] ?? '';

$errors = [];

if (empty($name)) {
    $errors[] = "Name is required";
}
if (empty($species)) {
    $errors[] = "Species is required";
}
if (empty($age) || $age < 0 || $age > 20) {
    $errors[] = "Valid age (0-20) is required";
}
if (empty($price) || $price < 0) {
    $errors[] = "Valid price is required";
}

// Handle image upload
$image_filename = null;
if (empty($errors) && isset($_FILES['pet_image']) && $_FILES['pet_image']['error'] === UPLOAD_ERR_OK) {
    $image_filename = uploadImage($_FILES['pet_image']);
    if ($image_filename === false) {
        $errors[] = "Failed to upload image. Please check file type and size.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Pet Result</title>
</head>
<body>
    <h1>Add Pet Result</h1>

    <?php if (!empty($errors)): ?>
        <h3>Errors:</h3>
        <ul>
        <?php foreach($errors as $error): ?>
            <li><?php echo $error; ?></li>
        <?php endforeach; ?>
        </ul>
        <a href="add_pet">Go back to form</a>
    <?php else: ?>
        <?php
        // Use prepared statement
        $sql = "INSERT INTO pets (name, species, breed, age, price, image) VALUES (?, ?, ?, ?, ?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("sssids", $name, $species, $breed, $age, $price, $image_filename);
        
        if ($stmt->execute()) {
            $new_id = $conn->insert_id;
            echo "<p>" . htmlspecialchars($name) . " added successfully!</p>";
            if ($image_filename) {
                echo '<img src="../assets/uploads/pets/' . htmlspecialchars($image_filename) . '" width="200"><br>';
            }
            echo '<br><a href="pet_details?id=' . $new_id . '">View Pet Details</a>';
            echo '<br><a href="pets">View All Pets</a>';
        } else {
            echo "<p>Error: " . $conn->error . "</p>";
        }
        $stmt->close();
        ?>
    <?php endif; ?>
</body>
</html>