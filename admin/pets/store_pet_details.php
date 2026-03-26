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

// Get pet details
$stmt = $conn->prepare("SELECT * FROM store_pets WHERE id = ?");
$stmt->bind_param('i', $petId);
$stmt->execute();
$pet = $stmt->get_result()->fetch_assoc();

if (!$pet) {
    header('Location: pets.php?tab=store');
    exit();
}

$page_title = 'Store Pet Details - ' . $pet['name'];
require_once __DIR__ . '/../includes/header.php';

echo '<link rel="stylesheet" href="/Ria-Pet-Store/admin/css/pets.css?v=' . time() . '">';
?>

<div class="admin-dashboard">
    <div class="page-header">
        <h1><?php echo htmlspecialchars($pet['name']); ?></h1>
        <div class="action-buttons">
            <a href="edit_store_pet.php?id=<?php echo $pet['id']; ?>" class="btn btn-outline"><?php echo icon('edit', 16); ?> Edit</a>
            <a href="delete_store_pet.php?id=<?php echo $pet['id']; ?>" class="btn btn-danger" onclick="return confirm('Delete this pet?')"><?php echo icon('x', 16); ?> Delete</a>
            <a href="pets.php?tab=store" class="btn btn-outline"><?php echo icon('arrow-left', 16); ?> Back</a>
        </div>
    </div>

    <div class="pet-details-container">
        <div class="pet-header-details">
            <div class="pet-photo-container">
                <?php if (!empty($pet['pet_image'])): ?>
                    <img src="/Ria-Pet-Store/assets/images/pets/<?php echo htmlspecialchars($pet['pet_image']); ?>" class="pet-photo-large">
                <?php else: ?>
                    <div class="no-photo-large"><?php echo icon('paw', 48); ?><br>No photo available</div>
                <?php endif; ?>
            </div>
            <div class="pet-info-summary">
                <div class="pet-status-badge">
                    <span class="status-badge status-<?php echo $pet['pet_status'] ?? 'available'; ?>">
                        <?php echo ucfirst($pet['pet_status'] ?? 'Available'); ?>
                    </span>
                    <?php if ($pet['featured']): ?>
                        <span class="featured-badge"><?php echo icon('star', 14); ?> Featured</span>
                    <?php endif; ?>
                </div>
                <h2><?php echo htmlspecialchars($pet['name']); ?></h2>
                <p class="pet-price">₱<?php echo number_format($pet['price'], 2); ?></p>
            </div>
        </div>

        <div class="details-grid">
            <div class="info-card">
                <h3><?php echo icon('info', 20); ?> Basic Information</h3>
                <div class="info-row">
                    <span class="info-label">Species:</span>
                    <span class="info-value"><?php echo ucfirst(htmlspecialchars($pet['species'])); ?></span>
                </div>
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
            </div>

            <div class="info-card">
                <h3><?php echo icon('calendar', 20); ?> Additional Info</h3>
                <div class="info-row">
                    <span class="info-label">ID:</span>
                    <span class="info-value">#<?php echo $pet['id']; ?></span>
                </div>
                <div class="info-row">
                    <span class="info-label">Added:</span>
                    <span class="info-value"><?php echo date('M d, Y', strtotime($pet['created_at'])); ?></span>
                </div>
                <div class="info-row">
                    <span class="info-label">Last Updated:</span>
                    <span class="info-value"><?php echo date('M d, Y', strtotime($pet['updated_at'])); ?></span>
                </div>
                <?php if ($pet['featured']): ?>
                <div class="info-row">
                    <span class="info-label">Featured:</span>
                    <span class="info-value">Yes</span>
                </div>
                <?php endif; ?>
            </div>
        </div>

        <?php if (!empty($pet['description'])): ?>
        <div class="info-card">
            <h3><?php echo icon('file', 20); ?> Description</h3>
            <div class="description-text">
                <?php echo nl2br(htmlspecialchars($pet['description'])); ?>
            </div>
        </div>
        <?php endif; ?>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>