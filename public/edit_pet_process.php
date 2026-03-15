<?php
require_once '../backend/config/database.php';
require_once '../backend/uploads/handle_upload.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: pets');
    exit;
}

$id = $_POST['id'] ?? 0;
$name = $_POST['name'] ?? '';
$species = $_POST['species'] ?? '';
$breed = $_POST['breed'] ?? '';
$age = $_POST['age'] ?? '';
$price = $_POST['price'] ?? '';
$image_option = $_POST['image_option'] ?? 'keep';

$errors = [];

if (empty($id) || !is_numeric($id)) {
    $errors[] = "Invalid pet ID";
}
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

// Get current image
if (empty($errors)) {
    $stmt = $conn->prepare("SELECT image FROM pets WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();
    $current = $result->fetch_assoc();
    $image_filename = $current['image'];
    $stmt->close();
    
    // Handle image based on option
    if ($image_option === 'new' && isset($_FILES['pet_image']) && $_FILES['pet_image']['error'] === UPLOAD_ERR_OK) {
        $new_image = uploadImage($_FILES['pet_image']);
        if ($new_image !== false) {
            // Delete old image if exists
            if (!empty($image_filename) && file_exists('../assets/uploads/pets/' . $image_filename)) {
                unlink('../assets/uploads/pets/' . $image_filename);
            }
            $image_filename = $new_image;
        } else {
            $errors[] = "Failed to upload new image";
        }
    } elseif ($image_option === 'remove') {
        // Delete old image if exists
        if (!empty($image_filename) && file_exists('../assets/uploads/pets/' . $image_filename)) {
            unlink('../assets/uploads/pets/' . $image_filename);
        }
        $image_filename = null;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Pet Result</title>
</head>
<body>
    <h1>Edit Pet Result</h1>

    <?php if (!empty($errors)): ?>
        <h3>Errors:</h3>
        <ul>
        <?php foreach($errors as $error): ?>
            <li><?php echo $error; ?></li>
        <?php endforeach; ?>
        </ul>
        <a href="edit_pet?id=<?php echo $id; ?>">Go back</a>
    <?php else: ?>
        <?php
        $sql = "UPDATE pets SET name = ?, species = ?, breed = ?, age = ?, price = ?, image = ? WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("sssidsi", $name, $species, $breed, $age, $price, $image_filename, $id);
        
        if ($stmt->execute()) {
            echo "<p>" . htmlspecialchars($name) . " updated successfully!</p>";
            echo '<br><a href="pet_details?id=' . $id . '">View Pet Details</a>';
        } else {
            echo "<p>Error: " . $conn->error . "</p>";
        }
        $stmt->close();
        ?>
    <?php endif; ?>
    
    <br>
    <a href="pets">View All Pets</a>
</body>
</html>