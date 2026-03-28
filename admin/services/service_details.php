<?php
session_name('petstore_session');
session_start();

if (!isset($_SESSION['is_admin']) || $_SESSION['is_admin'] !== true) {
    header('Location: ' . url('login?error=Access denied'));
    exit;
}

require_once __DIR__ . '/../../backend/config/database.php';
require_once __DIR__ . '/../../backend/functions/helpers.php';

$serviceId = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if (!$serviceId) {
    header('Location: services.php');
    exit();
}

// Get service details
$stmt = $conn->prepare("SELECT * FROM services WHERE id = ?");
$stmt->bind_param('i', $serviceId);
$stmt->execute();
$service = $stmt->get_result()->fetch_assoc();

if (!$service) {
    header('Location: services.php');
    exit();
}

$page_title = 'Service Details - ' . $service['service_name'];
require_once __DIR__ . '/../includes/header.php';

echo '<link rel="stylesheet" href="/Ria-Pet-Store/admin/css/services.css?v=' . time() . '">';
?>

<div class="admin-dashboard">
    <div class="page-header">
        <div class="header-left">
            <a href="services.php" class="btn btn-outline">
                <?php echo icon('arrow-left', 16); ?> Back to Services
            </a>
        </div>
        <div class="header-right">
            <a href="edit_service.php?id=<?php echo $service['id']; ?>" class="btn btn-outline">
                <?php echo icon('edit', 16); ?> Edit
            </a>
            <a href="delete_service.php?id=<?php echo $service['id']; ?>" class="btn btn-danger" onclick="return confirm('Are you sure you want to delete this service?')">
                <?php echo icon('trash', 16); ?> Delete
            </a>
        </div>
    </div>

    <div class="service-details-container">
        <div class="service-header">
            <div class="service-icon">
                <?php echo icon('heart', 64); ?>
            </div>
            <div class="service-summary">
                <h1><?php echo htmlspecialchars($service['service_name']); ?></h1>
                <div class="service-badges">
                    <?php if ($service['featured']): ?>
                        <span class="featured-badge"><?php echo icon('star', 14); ?> Featured</span>
                    <?php endif; ?>
                    <?php if (!empty($service['category'])): ?>
                        <span class="category-badge"><?php echo htmlspecialchars(ucfirst($service['category'])); ?></span>
                    <?php endif; ?>
                </div>
                <p class="service-price">₱<?php echo number_format($service['price'], 2); ?> per session</p>
                <p class="service-duration"><?php echo $service['duration_minutes']; ?> minutes</p>
            </div>
        </div>

        <div class="details-grid">
            <!-- Service Details -->
            <div class="info-card full-width">
                <h3><?php echo icon('info', 20); ?> Service Details</h3>
                <table class="info-table">
                    <tr>
                        <td class="label">Service ID:</td>
                        <td>#<?php echo str_pad($service['id'], 4, '0', STR_PAD_LEFT); ?></td>
                    </tr>
                    <tr>
                        <td class="label">Service Name:</td>
                        <td><?php echo htmlspecialchars($service['service_name']); ?></td>
                    </tr>
                    <?php if (!empty($service['category'])): ?>
                    <tr>
                        <td class="label">Category:</td>
                        <td><?php echo htmlspecialchars(ucfirst($service['category'])); ?></td>
                    </tr>
                    <?php endif; ?>
                    <tr>
                        <td class="label">Duration:</td>
                        <td><?php echo $service['duration_minutes']; ?> minutes</td>
                    </tr>
                    <tr>
                        <td class="label">Price:</td>
                        <td class="price-value">₱<?php echo number_format($service['price'], 2); ?></td>
                    </tr>
                    <tr>
                        <td class="label">Featured:</td>
                        <td><?php echo $service['featured'] ? 'Yes' : 'No'; ?></td>
                    </tr>
                    <tr>
                        <td class="label">Created:</td>
                        <td><?php echo date('F j, Y g:i A', strtotime($service['created_at'])); ?></td>
                    </tr>
                    <?php if (!empty($service['updated_at']) && $service['updated_at'] != $service['created_at']): ?>
                    <tr>
                        <td class="label">Last Updated:</td>
                        <td><?php echo date('F j, Y g:i A', strtotime($service['updated_at'])); ?></td>
                    </tr>
                    <?php endif; ?>
                </table>
            </div>

            <!-- Description -->
            <?php if (!empty($service['description'])): ?>
            <div class="info-card full-width">
                <h3><?php echo icon('file', 20); ?> Description</h3>
                <div class="description-content">
                    <?php echo nl2br(htmlspecialchars($service['description'])); ?>
                </div>
            </div>
            <?php endif; ?>
        </div>

        <div class="action-buttons">
            <a href="edit_service.php?id=<?php echo $service['id']; ?>" class="btn btn-primary">
                <?php echo icon('edit', 16); ?> Edit Service
            </a>
            <a href="delete_service.php?id=<?php echo $service['id']; ?>" class="btn btn-danger" onclick="return confirm('Are you sure you want to delete this service?')">
                <?php echo icon('trash', 16); ?> Delete Service
            </a>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>