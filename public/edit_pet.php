<?php
require_once '../backend/config/database.php';
require_once '../backend/includes/auth.php';

$id = isset($_GET['id']) ? $_GET['id'] : 0;

if (!is_numeric($id) || $id <= 0) {
    die("Invalid pet ID");
}

$sql = "SELECT * FROM pets WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();
$pet = $result->fetch_assoc();
$stmt->close();

if (!$pet) {
    die("Pet not found");
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Pet</title>
</head>
<body>
    <h1>Edit Pet: <?php echo htmlspecialchars($pet['name']); ?></h1>

    <?php if(!empty($pet['image'])): ?>
    <div style="margin-bottom: 20px;">
        <h3>Current Photo:</h3>
        <img src="../assets/uploads/pets/<?php echo htmlspecialchars($pet['image']); ?>" 
             width="200" style="border-radius: 5px;">
    </div>
    <?php endif; ?>

    <form method="POST" action="edit_pet_process" enctype="multipart/form-data">
        <input type="hidden" name="id" value="<?php echo $pet['id']; ?>">
        
        <div>
            <label for="name">Name:</label><br>
            <input type="text" id="name" name="name" value="<?php echo htmlspecialchars($pet['name']); ?>" required>
        </div>
        
        <div>
            <label for="species">Species:</label><br>
            <select id="species" name="species" required>
                <option value="dog" <?php echo $pet['species'] == 'dog' ? 'selected' : ''; ?>>Dog</option>
                <option value="cat" <?php echo $pet['species'] == 'cat' ? 'selected' : ''; ?>>Cat</option>
                <option value="rabbit" <?php echo $pet['species'] == 'rabbit' ? 'selected' : ''; ?>>Rabbit</option>
                <option value="bird" <?php echo $pet['species'] == 'bird' ? 'selected' : ''; ?>>Bird</option>
                <option value="hamster" <?php echo $pet['species'] == 'hamster' ? 'selected' : ''; ?>>Hamster</option>
            </select>
        </div>
        
        <div>
            <label for="breed">Breed:</label><br>
            <input type="text" id="breed" name="breed" value="<?php echo htmlspecialchars($pet['breed'] ?? ''); ?>">
        </div>
        
        <div>
            <label for="age">Age (years):</label><br>
            <input type="number" id="age" name="age" min="0" max="20" value="<?php echo $pet['age']; ?>" required>
        </div>
        
        <div>
            <label for="price">Price (₱):</label><br>
            <input type="number" id="price" name="price" min="0" step="0.01" value="<?php echo $pet['price']; ?>" required>
        </div>
        
        <div>
            <label>Image Options:</label><br>
            <input type="radio" name="image_option" id="keep_image" value="keep" checked>
            <label for="keep_image">Keep current image</label><br>
            
            <input type="radio" name="image_option" id="new_image" value="new">
            <label for="new_image">Upload new image</label><br>
            
            <input type="radio" name="image_option" id="remove_image" value="remove">
            <label for="remove_image">Remove image</label><br>
        </div>
        
        <div id="upload_field" style="display: none;">
            <label for="pet_image">New Photo:</label><br>
            <input type="file" id="pet_image" name="pet_image" accept="image/*">
        </div>
        
        <br>
        <button type="submit">Update Pet</button>
    </form>

    <br>
    <a href="pet_details?id=<?php echo $pet['id']; ?>">Cancel</a>

    <script>
    document.querySelectorAll('input[name="image_option"]').forEach(radio => {
        radio.addEventListener('change', function() {
            const uploadField = document.getElementById('upload_field');
            uploadField.style.display = this.value === 'new' ? 'block' : 'none';
        });
    });
    </script>
</body>
</html>