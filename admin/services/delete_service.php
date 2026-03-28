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

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $stmt = $conn->prepare("DELETE FROM services WHERE id = ?");
    $stmt->bind_param('i', $serviceId);
    if ($stmt->execute()) {
        header('Location: services.php?message=Service deleted successfully');
        exit();
    } else {
        $error = 'Error deleting service: ' . $conn->error;
    }
}

$page_title = 'Delete Service - ' . $service['service_name'];
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
    </div>

    <?php if (isset($error)): ?>
        <div class="message error"><?php echo htmlspecialchars($error); ?></div>
    <?php endif; ?>

    <div class="delete-confirmation">
        <div class="warning-box">
            <?php echo icon('alert-triangle', 48); ?>
            <h3>Warning: This action cannot be undone!</h3>
            <p>Are you sure you want to permanently delete this service?</p>
        </div>

        <div class="service-summary">
            <h3>Service Details</h3>
            <div class="summary-grid">
                <div class="summary-item">
                    <span class="label">Service ID:</span>
                    <span class="value">#<?php echo str_pad($service['id'], 4, '0', STR_PAD_LEFT); ?></span>
                </div>
                <div class="summary-item">
                    <span class="label">Service Name:</span>
                    <span class="value"><?php echo htmlspecialchars($service['service_name']); ?></span>
                </div>
                <div class="summary-item">
                    <span class="label">Category:</span>
                    <span class="value"><?php echo htmlspecialchars(ucfirst($service['category'] ?? 'General')); ?></span>
                </div>
                <div class="summary-item">
                    <span class="label">Duration:</span>
                    <span class="value"><?php echo $service['duration_minutes']; ?> minutes</span>
                </div>
                <div class="summary-item">
                    <span class="label">Price:</span>
                    <span class="value">₱<?php echo number_format($service['price'], 2); ?></span>
                </div>
                <div class="summary-item">
                    <span class="label">Featured:</span>
                    <span class="value"><?php echo $service['featured'] ? 'Yes' : 'No'; ?></span>
                </div>
            </div>
        </div>

        <form method="post" class="delete-form">
            <div class="action-buttons">
                <button type="submit" class="btn btn-danger">
                    <?php echo icon('trash', 16); ?> Yes, Delete Service
                </button>
                <a href="service_details.php?id=<?php echo $service['id']; ?>" class="btn btn-outline">Cancel</a>
            </div>
        </form>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>