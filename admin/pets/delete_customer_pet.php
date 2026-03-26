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
    header('Location: pets.php?tab=customer');
    exit();
}

// Get pet data
$stmt = $conn->prepare("SELECT cp.*, c.first_name, c.last_name 
                        FROM customer_pets cp 
                        LEFT JOIN customers c ON cp.customer_id = c.id 
                        WHERE cp.id = ?");
$stmt->bind_param('i', $petId);
$stmt->execute();
$pet = $stmt->get_result()->fetch_assoc();

if (!$pet) {
    header('Location: pets.php?tab=customer');
    exit();
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['confirm_delete'])) {
    $stmt = $conn->prepare("DELETE FROM customer_pets WHERE id = ?");
    $stmt->bind_param('i', $petId);
    
    if ($stmt->execute()) {
        header('Location: pets.php?tab=customer&deleted=1');
        exit();
    } else {
        $error = 'Error deleting pet: ' . $conn->error;
    }
}

$page_title = 'Delete Customer Pet - ' . $pet['name'];
require_once __DIR__ . '/../includes/header.php';

echo '<link rel="stylesheet" href="/Ria-Pet-Store/admin/css/pets.css?v=' . time() . '">';
?>

<div class="admin-dashboard">
    <div class="page-header">
        <h1>Delete Customer Pet</h1>
        <a href="pets.php?tab=customer" class="btn btn-outline"><?php echo icon('arrow-left', 16); ?> Back to Pets</a>
    </div>

    <?php if ($error): ?>
        <div class="message error"><?php echo htmlspecialchars($error); ?></div>
    <?php endif; ?>

    <div class="delete-confirmation">
        <div class="warning-box">
            <?php echo icon('alert', 32); ?>
            <h3>Warning: This action cannot be undone!</h3>
            <p>You are about to permanently delete <strong><?php echo htmlspecialchars($pet['name']); ?></strong> from the customer's pets.</p>
        </div>

        <div class="pet-summary">
            <div class="summary-header">
                <h3>Pet Details</h3>
            </div>
            <div class="summary-grid">
                <div class="summary-item">
                    <span class="label">Owner:</span>
                    <span class="value"><?php echo htmlspecialchars($pet['first_name'] . ' ' . $pet['last_name']); ?></span>
                </div>
                <div class="summary-item">
                    <span class="label">Pet Name:</span>
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
                    <span class="label">Gender:</span>
                    <span class="value"><?php echo ucfirst($pet['gender'] ?? 'Unknown'); ?></span>
                </div>
                <div class="summary-item">
                    <span class="label">Status:</span>
                    <span class="value"><?php echo $pet['is_active'] ? 'Active' : 'Inactive'; ?></span>
                </div>
            </div>
        </div>

        <form method="post" class="delete-form">
            <div class="action-buttons">
                <button type="submit" name="confirm_delete" value="1" class="btn btn-danger"><?php echo icon('x', 16); ?> Yes, Delete Pet</button>
                <a href="pets.php?tab=customer" class="btn btn-outline">Cancel</a>
            </div>
        </form>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>