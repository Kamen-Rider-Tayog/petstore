<?php
session_name('petstore_session');
session_start();

if (!isset($_SESSION['is_admin']) || $_SESSION['is_admin'] !== true) {
    header('Location: ' . url('login?error=Access denied'));
    exit;
}

require_once __DIR__ . '/../../backend/config/database.php';
require_once __DIR__ . '/../../backend/functions/helpers.php';

$petId = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if (!$petId) {
    header('Location: pets.php?tab=store');
    exit();
}

// Get pet data
$stmt = $conn->prepare("SELECT * FROM store_pets WHERE id = ?");
$stmt->bind_param('i', $petId);
$stmt->execute();
$pet = $stmt->get_result()->fetch_assoc();

if (!$pet) {
    header('Location: pets.php?tab=store');
    exit();
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['confirm_delete'])) {
    // Delete image file if exists
    if (!empty($pet['pet_image'])) {
        $imagePath = __DIR__ . '/../../assets/images/pets/' . $pet['pet_image'];
        if (file_exists($imagePath)) {
            unlink($imagePath);
        }
    }

    $stmt = $conn->prepare("DELETE FROM store_pets WHERE id = ?");
    $stmt->bind_param('i', $petId);
    
    if ($stmt->execute()) {
        header('Location: pets.php?tab=store&deleted=1');
        exit();
    } else {
        $error = 'Error deleting pet: ' . $conn->error;
    }
}

$page_title = 'Delete Store Pet - ' . $pet['name'];
require_once __DIR__ . '/../includes/header.php';

echo '<link rel="stylesheet" href="/Ria-Pet-Store/admin/css/pets.css?v=' . time() . '">';
?>

<div class="admin-dashboard">
    <div class="page-header">
        <h1>Delete Store Pet</h1>
        <a href="pets.php?tab=store" class="btn btn-outline"><?php echo icon('arrow-left', 16); ?> Back to Pets</a>
    </div>

    <?php if ($error): ?>
        <div class="message error"><?php echo htmlspecialchars($error); ?></div>
    <?php endif; ?>

    <div class="delete-confirmation">
        <div class="warning-box">
            <?php echo icon('alert', 32); ?>
            <h3>Warning: This action cannot be undone!</h3>
            <p>You are about to permanently delete <strong><?php echo htmlspecialchars($pet['name']); ?></strong> from the store.</p>
        </div>

        <div class="pet-summary">
            <div class="summary-header">
                <h3>Pet Details</h3>
            </div>
            <div class="summary-grid">
                <div class="summary-item">
                    <span class="label">Name:</span>
                    <span class="value"><?php echo htmlspecialchars($pet['name']); ?></span>
                </div>
                <div class="summary-item">
                    <span class="label">Species:</span>
                    <span class="value"><?php echo ucfirst(htmlspecialchars($pet['species'])); ?></span>
                </div>
                <div class="summary-item">
                    <span class="label">Age:</span>
                    <span class="value"><?php echo $pet['age']; ?> <?php echo $pet['age'] == 1 ? 'year' : 'years'; ?></span>
                </div>
                <div class="summary-item">
                    <span class="label">Price:</span>
                    <span class="value">₱<?php echo number_format($pet['price'], 2); ?></span>
                </div>
                <div class="summary-item">
                    <span class="label">Status:</span>
                    <span class="value"><?php echo ucfirst($pet['pet_status'] ?? 'Available'); ?></span>
                </div>
                <?php if (!empty($pet['pet_image'])): ?>
                <div class="summary-item full-width">
                    <span class="label">Photo:</span>
                    <span class="value">
                        <img src="/Ria-Pet-Store/assets/images/pets/<?php echo htmlspecialchars($pet['pet_image']); ?>" class="pet-photo-medium">
                    </span>
                </div>
                <?php endif; ?>
            </div>
        </div>

        <form method="post" class="delete-form">
            <div class="action-buttons">
                <button type="submit" name="confirm_delete" value="1" class="btn btn-danger"><?php echo icon('x', 16); ?> Yes, Delete Pet</button>
                <a href="pets.php?tab=store" class="btn btn-outline">Cancel</a>
            </div>
        </form>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>