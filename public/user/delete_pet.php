<?php
session_name('petstore_session');
session_start();

require_once __DIR__ . '/../../backend/config/database.php';
require_once __DIR__ . '/../../backend/functions/helpers.php';

// Check if user is logged in
if (!isset($_SESSION['customer_id'])) {
    header('Location: ' . url('login'));
    exit;
}

require_once __DIR__ . '/../../backend/includes/header.php';

$customerId = $_SESSION['customer_id'];
$petId = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$confirmed = isset($_GET['confirm']) && $_GET['confirm'] == '1';

if ($petId <= 0) {
    header('Location: ' . url('my_pets'));
    exit;
}

// Get pet details
$stmt = $conn->prepare("SELECT * FROM customer_pets WHERE id = ? AND customer_id = ?");
$stmt->bind_param("ii", $petId, $customerId);
$stmt->execute();
$pet = $stmt->get_result()->fetch_assoc();

if (!$pet) {
    header('Location: ' . url('my_pets'));
    exit;
}

// Handle deletion
if ($confirmed) {
    $stmt = $conn->prepare("DELETE FROM customer_pets WHERE id = ? AND customer_id = ?");
    $stmt->bind_param("ii", $petId, $customerId);
    if ($stmt->execute()) {
        header('Location: ' . url('my_pets?deleted=1'));
        exit;
    } else {
        $error = 'Failed to delete pet. Please try again.';
    }
}

$page_title = 'Delete Pet';
?>
<link rel="stylesheet" href="/Ria-Pet-Store/assets/css/user/delete_pet.css?v=<?php echo time(); ?>">

<div class="delete-pet-page">
    <section class="page-hero">
        <div class="container">
            <h1>Delete Pet</h1>
        </div>
    </section>

    <section class="delete-content">
        <div class="container">
            <div class="delete-card">
                <?php if (isset($error)): ?>
                    <div class="message error"><?php echo htmlspecialchars($error); ?></div>
                <?php endif; ?>

                <div class="pet-info">
                    <?php echo icon('paw', 48); ?>
                    <h3><?php echo htmlspecialchars($pet['name']); ?></h3>
                    <p><?php echo htmlspecialchars($pet['species']); ?>
                    <?php if (!empty($pet['breed'])): ?> • <?php echo htmlspecialchars($pet['breed']); ?><?php endif; ?></p>
                    <p class="pet-age"><?php echo $pet['age']; ?> <?php echo $pet['age'] == 1 ? 'year' : 'years'; ?> old</p>
                </div>

                <p class="warning-text">Are you sure you want to remove <strong><?php echo htmlspecialchars($pet['name']); ?></strong> from your pets? This action cannot be undone.</p>

                <div class="action-buttons">
                    <a href="<?php echo url('my_pets?delete=1&id=' . $petId); ?>" class="btn btn-danger" onclick="return confirm('Are you absolutely sure? This cannot be undone.')">
                        <?php echo icon('x', 16); ?> Yes, Delete Pet
                    </a>
                    <a href="<?php echo url('my_pets'); ?>" class="btn btn-secondary">
                        <?php echo icon('arrow-left', 16); ?> No, Go Back
                    </a>
                </div>
            </div>
        </div>
    </section>
</div>

<?php require_once __DIR__ . '/../../backend/includes/footer.php'; ?>