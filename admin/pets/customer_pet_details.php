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

// Get pet details with owner info
$stmt = $conn->prepare("
    SELECT cp.*, c.first_name, c.last_name, c.email, c.phone
    FROM customer_pets cp
    LEFT JOIN customers c ON cp.customer_id = c.id
    WHERE cp.id = ?
");
$stmt->bind_param('i', $petId);
$stmt->execute();
$pet = $stmt->get_result()->fetch_assoc();

if (!$pet) {
    header('Location: pets.php?tab=customer');
    exit();
}

$page_title = 'Customer Pet Details - ' . $pet['name'];
require_once __DIR__ . '/../includes/header.php';

echo '<link rel="stylesheet" href="/Ria-Pet-Store/admin/css/pets.css?v=' . time() . '">';
?>

<div class="admin-dashboard">
    <div class="page-header">
        <h1><?php echo htmlspecialchars($pet['name']); ?></h1>
        <div class="action-buttons">
            <a href="edit_customer_pet.php?id=<?php echo $pet['id']; ?>" class="btn btn-outline"><?php echo icon('edit', 16); ?> Edit</a>
            <a href="delete_customer_pet.php?id=<?php echo $pet['id']; ?>" class="btn btn-danger" onclick="return confirm('Delete this pet?')"><?php echo icon('x', 16); ?> Delete</a>
            <a href="pets.php?tab=customer" class="btn btn-outline"><?php echo icon('arrow-left', 16); ?> Back</a>
        </div>
    </div>

    <div class="pet-details-container">
        <div class="pet-header-details">
            <div class="pet-photo-container">
                <div class="no-photo-large"><?php echo icon('paw', 48); ?><br>No photo available</div>
            </div>
            <div class="pet-info-summary">
                <div class="pet-status-badge">
                    <span class="status-badge <?php echo $pet['is_active'] ? 'status-available' : 'status-sold'; ?>">
                        <?php echo $pet['is_active'] ? 'Active' : 'Inactive'; ?>
                    </span>
                </div>
                <h2><?php echo htmlspecialchars($pet['name']); ?></h2>
                <p class="pet-species"><?php echo ucfirst(htmlspecialchars($pet['species'])); ?></p>
            </div>
        </div>

        <div class="details-grid">
            <div class="info-card">
                <h3><?php echo icon('info', 20); ?> Pet Information</h3>
                <div class="info-row">
                    <span class="info-label">Breed:</span>
                    <span class="info-value"><?php echo htmlspecialchars($pet['breed'] ?? 'Mixed'); ?></span>
                </div>
                <div class="info-row">
                    <span class="info-label">Age:</span>
                    <span class="info-value"><?php echo $pet['age']; ?> <?php echo $pet['age'] == 1 ? 'year' : 'years'; ?></span>
                </div>
                <div class="info-row">
                    <span class="info-label">Gender:</span>
                    <span class="info-value"><?php echo ucfirst($pet['gender'] ?? 'Unknown'); ?></span>
                </div>
                <div class="info-row">
                    <span class="info-label">Color:</span>
                    <span class="info-value"><?php echo htmlspecialchars($pet['color'] ?? 'Not specified'); ?></span>
                </div>
                <div class="info-row">
                    <span class="info-label">Weight:</span>
                    <span class="info-value"><?php echo $pet['weight'] ? $pet['weight'] . ' ' . $pet['weight_unit'] : 'Not recorded'; ?></span>
                </div>
            </div>

            <div class="info-card">
                <h3><?php echo icon('user', 20); ?> Owner Information</h3>
                <div class="info-row">
                    <span class="info-label">Name:</span>
                    <span class="info-value">
                        <a href="../customers/customer_details.php?id=<?php echo $pet['customer_id']; ?>" class="customer-link">
                            <?php echo htmlspecialchars($pet['first_name'] . ' ' . $pet['last_name']); ?>
                        </a>
                    </span>
                </div>
                <div class="info-row">
                    <span class="info-label">Email:</span>
                    <span class="info-value"><?php echo htmlspecialchars($pet['email']); ?></span>
                </div>
                <div class="info-row">
                    <span class="info-label">Phone:</span>
                    <span class="info-value"><?php echo htmlspecialchars($pet['phone'] ?? 'Not provided'); ?></span>
                </div>
            </div>
        </div>

        <?php if (!empty($pet['medical_notes'])): ?>
        <div class="info-card">
            <h3><?php echo icon('file', 20); ?> Medical Notes</h3>
            <div class="description-text">
                <?php echo nl2br(htmlspecialchars($pet['medical_notes'])); ?>
            </div>
        </div>
        <?php endif; ?>

        <?php if (!empty($pet['microchip_id'])): ?>
        <div class="info-card">
            <h3><?php echo icon('microchip', 20); ?> Microchip Information</h3>
            <div class="info-row">
                <span class="info-label">Microchip ID:</span>
                <span class="info-value"><?php echo htmlspecialchars($pet['microchip_id']); ?></span>
            </div>
        </div>
        <?php endif; ?>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>