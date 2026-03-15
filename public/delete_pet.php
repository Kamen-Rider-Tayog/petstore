<?php
require_once '../backend/config/database.php';
require_once '../backend/includes/auth.php';

$id = isset($_GET['id']) ? $_GET['id'] : 0;
$confirm = isset($_GET['confirm']) ? $_GET['confirm'] : '';

if (!is_numeric($id) || $id <= 0) {
    die("Invalid pet ID");
}

if ($confirm === 'yes') {
    $sql = "DELETE FROM pets WHERE id = $id";
    
    if ($conn->query($sql)) {
        $message = "Pet deleted successfully!";
        $deleted = true;
    } else {
        $message = "Error deleting pet: " . $conn->error;
        $deleted = false;
    }
} else {
    $sql = "SELECT name FROM pets WHERE id = $id";
    $result = $conn->query($sql);
    $pet = $result->fetch_assoc();
    
    if (!$pet) {
        die("Pet not found");
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Delete Pet</title>
</head>
<body>
    <h1>Delete Pet</h1>

    <?php if (isset($message)): ?>
        <p><?php echo $message; ?></p>
        <?php if (isset($deleted) && $deleted): ?>
            <a href="pets">View All Pets</a>
        <?php else: ?>
            <a href="pet_details?id=<?php echo $id; ?>">Go Back</a>
        <?php endif; ?>
    <?php else: ?>
        <p>Are you sure you want to delete "<?php echo $pet['name']; ?>"?</p>
        
        <a href="delete_pet?id=<?php echo $id; ?>&confirm=yes">Yes, Delete</a>
        <a href="pet_details?id=<?php echo $id; ?>">Cancel</a>
    <?php endif; ?>
</body>
</html>