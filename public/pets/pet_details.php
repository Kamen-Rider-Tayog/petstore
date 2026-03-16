<?php
require_once '../../backend/config/database.php';

$id = isset($_GET['id']) ? $_GET['id'] : '';

if (empty($id)) {
    $error = "No pet selected";
} elseif (!is_numeric($id)) {
    $error = "Invalid pet ID";
} else {
    $sql = "SELECT * FROM pets WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();
    $pet = $result->fetch_assoc();
    $stmt->close();
    
    if (!$pet) {
        $error = "Pet not found";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pet Details</title>
    <link rel="stylesheet" href="../../assets/css/pet_details.css">
</head>
<body>
    <h1>Pet Details</h1>

    <?php if (isset($error)): ?>
        <p><?php echo $error; ?></p>
    <?php else: ?>
        <div style="display: flex; gap: 30px;">
            <div>
                <?php if(!empty($pet['image'])): ?>
                <img src="../../assets/uploads/pets/<?php echo htmlspecialchars($pet['image']); ?>" 
                     class="pet-image">
                <?php else: ?>
                <div style="width: 300px; height: 300px; background: #f0f0f0; border-radius: 10px; 
                            display: flex; align-items: center; justify-content: center;">
                    No photo available
                </div>
                <?php endif; ?>
            </div>
            
            <div>
                <p class="pet-detail"><strong>Name:</strong> <?php echo htmlspecialchars($pet['name']); ?></p>
                <p class="pet-detail"><strong>Species:</strong> <?php echo htmlspecialchars($pet['species']); ?></p>
                <p class="pet-detail"><strong>Breed:</strong> <?php echo htmlspecialchars($pet['breed'] ?? 'N/A'); ?></p>
                <p class="pet-detail"><strong>Age:</strong> <?php echo $pet['age']; ?> year old</p>
                <p class="pet-detail"><strong>Price:</strong> ₱<?php echo $pet['price']; ?></p>
            </div>
        </div>
        
        <br>
        <?php if(isset($_SESSION['user_id'])): ?>
        <a href="edit_pet?id=<?php echo $pet['id']; ?>">Edit Pet</a> |
        <?php endif; ?>
        <a href="pets">Back to All Pets</a>
    <?php endif; ?>
</body>
</html>