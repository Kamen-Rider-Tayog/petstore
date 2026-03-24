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
$message = '';
$messageType = '';

// Handle pet deletion
if (isset($_GET['delete']) && isset($_GET['id'])) {
    $petId = (int)$_GET['id'];
    $stmt = $conn->prepare("DELETE FROM customer_pets WHERE id = ? AND customer_id = ?");
    $stmt->bind_param("ii", $petId, $customerId);
    if ($stmt->execute()) {
        $message = 'Pet removed successfully!';
        $messageType = 'success';
    } else {
        $message = 'Failed to remove pet.';
        $messageType = 'error';
    }
}

// Get customer's pets
$stmt = $conn->prepare("SELECT * FROM customer_pets WHERE customer_id = ? AND is_active = 1 ORDER BY name");
$stmt->bind_param("i", $customerId);
$stmt->execute();
$result = $stmt->get_result();
$pets = $result->fetch_all(MYSQLI_ASSOC);

$page_title = 'My Pets';
?>
<link rel="stylesheet" href="/Ria-Pet-Store/assets/css/user/my_pets.css?v=<?php echo time(); ?>">

<div class="my-pets-page">
    <section class="page-hero">
        <div class="container">
            <h1>My Pets</h1>
            <p>Manage your registered pets</p>
        </div>
    </section>

    <section class="pets-content">
        <div class="container">
            <div class="pets-header">
                <a href="<?php echo url('add_pet'); ?>" class="btn btn-primary">
                    <?php echo icon('plus', 16); ?> Add New Pet
                </a>
            </div>

            <?php if ($message): ?>
                <div class="message <?php echo $messageType; ?>">
                    <?php echo htmlspecialchars($message); ?>
                </div>
            <?php endif; ?>

            <?php if (empty($pets)): ?>
                <div class="no-pets">
                    <?php echo icon('paw', 48); ?>
                    <h3>No Pets Registered</h3>
                    <p>You haven't added any pets yet. Add your first pet to book appointments!</p>
                    <a href="<?php echo url('add_pet'); ?>" class="btn btn-primary">Add Your First Pet</a>
                </div>
            <?php else: ?>
                <div class="pets-grid">
                    <?php foreach ($pets as $pet): ?>
                        <div class="pet-card">
                            <div class="pet-avatar">
                                <?php echo icon('paw', 48); ?>
                            </div>
                            <div class="pet-info">
                                <h3><?php echo htmlspecialchars($pet['name']); ?></h3>
                                <p class="pet-details">
                                    <?php echo htmlspecialchars($pet['species']); ?>
                                    <?php if (!empty($pet['breed'])): ?> • <?php echo htmlspecialchars($pet['breed']); ?><?php endif; ?>
                                </p>
                                <p class="pet-age"><?php echo $pet['age']; ?> <?php echo $pet['age'] == 1 ? 'year' : 'years'; ?> old</p>
                                <?php if (!empty($pet['gender'])): ?>
                                    <p class="pet-gender"><?php echo ucfirst($pet['gender']); ?></p>
                                <?php endif; ?>
                            </div>
                            <div class="pet-actions">
                                <a href="<?php echo url('edit_pet?id=' . $pet['id']); ?>" class="btn btn-small btn-outline">
                                    <?php echo icon('edit', 14); ?> Edit
                                </a>
                                <a href="<?php echo url('my_pets?delete=1&id=' . $pet['id']); ?>" class="btn btn-small btn-danger" onclick="return confirm('Are you sure you want to remove this pet?')">
                                    <?php echo icon('x', 14); ?> Remove
                                </a>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </section>
</div>

<?php require_once __DIR__ . '/../../backend/includes/footer.php'; ?>