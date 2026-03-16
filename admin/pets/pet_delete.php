<?php
require_once '../includes/header.php';
require_once '../includes/sidebar.php';

$petId = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if (!$petId) {
    header('Location: pets.php');
    exit();
}

// Get pet data
$stmt = $conn->prepare("SELECT * FROM pets WHERE id = ?");
$stmt->bind_param('i', $petId);
$stmt->execute();
$pet = $stmt->get_result()->fetch_assoc();

if (!$pet) {
    header('Location: pets.php');
    exit();
}

$message = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['confirm_delete'])) {
    // Delete image file if exists
    if (!empty($pet['image'])) {
        $imagePath = '../assets/uploads/pets/' . $pet['image'];
        if (file_exists($imagePath)) {
            unlink($imagePath);
        }
    }

    // Delete from database
    $stmt = $conn->prepare("DELETE FROM pets WHERE id = ?");
    $stmt->bind_param('i', $petId);

    if ($stmt->execute()) {
        header('Location: pets.php?message=Pet deleted successfully');
        exit();
    } else {
        $message = 'Error deleting pet: ' . $conn->error;
    }
}
?>

<main class="admin-main">
    <h2>Delete Pet</h2>

    <?php if ($message): ?>
        <div style="background: #f8d7da; color: #721c24; padding: 1rem; border-radius: 4px; margin-bottom: 1rem;">
            <?php echo htmlspecialchars($message); ?>
        </div>
    <?php endif; ?>

    <div style="background: #fff; padding: 2rem; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1);">
        <div style="background: #fff3cd; border: 1px solid #ffeaa7; padding: 1rem; border-radius: 4px; margin-bottom: 2rem;">
            <strong>Warning:</strong> This action cannot be undone. The pet will be permanently deleted from the database.
        </div>

        <h3>Pet Details</h3>
        <table style="width: 100%; margin-bottom: 2rem;">
            <tr>
                <td style="padding: 0.5rem; border-bottom: 1px solid #eee;"><strong>ID:</strong></td>
                <td style="padding: 0.5rem; border-bottom: 1px solid #eee;"><?php echo $pet['id']; ?></td>
            </tr>
            <tr>
                <td style="padding: 0.5rem; border-bottom: 1px solid #eee;"><strong>Name:</strong></td>
                <td style="padding: 0.5rem; border-bottom: 1px solid #eee;"><?php echo htmlspecialchars($pet['name']); ?></td>
            </tr>
            <tr>
                <td style="padding: 0.5rem; border-bottom: 1px solid #eee;"><strong>Species:</strong></td>
                <td style="padding: 0.5rem; border-bottom: 1px solid #eee;"><?php echo htmlspecialchars($pet['species']); ?></td>
            </tr>
            <tr>
                <td style="padding: 0.5rem; border-bottom: 1px solid #eee;"><strong>Age:</strong></td>
                <td style="padding: 0.5rem; border-bottom: 1px solid #eee;"><?php echo $pet['age']; ?> years</td>
            </tr>
            <tr>
                <td style="padding: 0.5rem; border-bottom: 1px solid #eee;"><strong>Price:</strong></td>
                <td style="padding: 0.5rem; border-bottom: 1px solid #eee;">₱<?php echo number_format($pet['price'], 2); ?></td>
            </tr>
            <tr>
                <td style="padding: 0.5rem; border-bottom: 1px solid #eee;"><strong>Photo:</strong></td>
                <td style="padding: 0.5rem; border-bottom: 1px solid #eee;">
                    <?php if (!empty($pet['image'])): ?>
                        <img src="../assets/uploads/pets/<?php echo htmlspecialchars($pet['image']); ?>" width="100" height="100" style="object-fit: cover; border-radius: 4px; border: 1px solid #ddd;">
                    <?php else: ?>
                        No photo
                    <?php endif; ?>
                </td>
            </tr>
        </table>

        <form method="post">
            <div style="display: flex; gap: 1rem;">
                <button type="submit" name="confirm_delete" value="1" class="btn btn-danger">Yes, Delete Pet</button>
                <a href="pets.php" class="btn">Cancel</a>
            </div>
        </form>
    </div>
</main>

<?php require_once '../includes/footer.php'; ?>