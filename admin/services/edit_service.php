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

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $service_name = trim($_POST['service_name'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $category = trim($_POST['category'] ?? '');
    $duration_minutes = (int)($_POST['duration_minutes'] ?? 30);
    $price = (float)($_POST['price'] ?? 0);
    $featured = isset($_POST['featured']) ? 1 : 0;

    if (empty($service_name) || $price <= 0) {
        $error = 'Please fill in all required fields.';
    } else {
        $stmt = $conn->prepare("
            UPDATE services 
            SET service_name = ?, description = ?, category = ?, duration_minutes = ?, price = ?, featured = ?
            WHERE id = ?
        ");
        $stmt->bind_param('sssidis', $service_name, $description, $category, $duration_minutes, $price, $featured, $serviceId);
        
        if ($stmt->execute()) {
            $success = 'Service updated successfully!';
            // Refresh service data
            $stmt = $conn->prepare("SELECT * FROM services WHERE id = ?");
            $stmt->bind_param('i', $serviceId);
            $stmt->execute();
            $service = $stmt->get_result()->fetch_assoc();
        } else {
            $error = 'Error updating service: ' . $conn->error;
        }
    }
}

$page_title = 'Edit Service - ' . $service['service_name'];
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
            <button type="submit" form="edit-service-form" class="btn btn-primary">
                <?php echo icon('save', 16); ?> Save Changes
            </button>
            <a href="service_details.php?id=<?php echo $service['id']; ?>" class="btn btn-outline">
                <?php echo icon('eye', 16); ?> View Details
            </a>
        </div>
    </div>

    <?php if ($error): ?>
        <div class="message error"><?php echo htmlspecialchars($error); ?></div>
    <?php endif; ?>
    
    <?php if ($success): ?>
        <div class="message success"><?php echo htmlspecialchars($success); ?></div>
    <?php endif; ?>

    <form method="post" id="edit-service-form" class="service-form">
        <div class="form-grid">
            <!-- Basic Information -->
            <div class="info-card">
                <h3><?php echo icon('heart', 20); ?> Service Information</h3>
                <div class="form-group">
                    <label for="service_name">Service Name *</label>
                    <input type="text" id="service_name" name="service_name" class="form-control" 
                           value="<?php echo htmlspecialchars($service['service_name']); ?>" required>
                </div>
                <div class="form-group">
                    <label for="category">Category</label>
                    <select id="category" name="category" class="form-control">
                        <option value="">-- Select Category --</option>
                        <option value="grooming" <?php echo ($service['category'] ?? '') == 'grooming' ? 'selected' : ''; ?>>Grooming</option>
                        <option value="veterinary" <?php echo ($service['category'] ?? '') == 'veterinary' ? 'selected' : ''; ?>>Veterinary</option>
                        <option value="training" <?php echo ($service['category'] ?? '') == 'training' ? 'selected' : ''; ?>>Training</option>
                        <option value="boarding" <?php echo ($service['category'] ?? '') == 'boarding' ? 'selected' : ''; ?>>Boarding</option>
                        <option value="daycare" <?php echo ($service['category'] ?? '') == 'daycare' ? 'selected' : ''; ?>>Daycare</option>
                        <option value="spa" <?php echo ($service['category'] ?? '') == 'spa' ? 'selected' : ''; ?>>Spa</option>
                        <option value="dental" <?php echo ($service['category'] ?? '') == 'dental' ? 'selected' : ''; ?>>Dental</option>
                        <option value="wellness" <?php echo ($service['category'] ?? '') == 'wellness' ? 'selected' : ''; ?>>Wellness</option>
                    </select>
                </div>
                <div class="form-group">
                    <label for="duration_minutes">Duration (minutes)</label>
                    <select id="duration_minutes" name="duration_minutes" class="form-control">
                        <option value="15" <?php echo $service['duration_minutes'] == 15 ? 'selected' : ''; ?>>15 minutes</option>
                        <option value="30" <?php echo $service['duration_minutes'] == 30 ? 'selected' : ''; ?>>30 minutes</option>
                        <option value="45" <?php echo $service['duration_minutes'] == 45 ? 'selected' : ''; ?>>45 minutes</option>
                        <option value="60" <?php echo $service['duration_minutes'] == 60 ? 'selected' : ''; ?>>1 hour</option>
                        <option value="90" <?php echo $service['duration_minutes'] == 90 ? 'selected' : ''; ?>>1.5 hours</option>
                        <option value="120" <?php echo $service['duration_minutes'] == 120 ? 'selected' : ''; ?>>2 hours</option>
                    </select>
                </div>
                <div class="form-group">
                    <label for="price">Price (₱) *</label>
                    <input type="number" id="price" name="price" class="form-control" step="0.01" min="0" 
                           value="<?php echo $service['price']; ?>" required>
                </div>
                <div class="form-group">
                    <label class="checkbox-label">
                        <input type="checkbox" name="featured" value="1" <?php echo $service['featured'] ? 'checked' : ''; ?>> Featured Service
                    </label>
                    <small class="help-text">Featured services are highlighted on the frontend</small>
                </div>
            </div>

            <!-- Description -->
            <div class="info-card">
                <h3><?php echo icon('file', 20); ?> Description</h3>
                <div class="form-group">
                    <label for="description">Service Description</label>
                    <textarea id="description" name="description" rows="10" class="form-control" 
                              placeholder="Describe what this service includes..."><?php echo htmlspecialchars($service['description'] ?? ''); ?></textarea>
                    <small class="help-text">Provide details about the service, what to expect, etc.</small>
                </div>
            </div>
        </div>
    </form>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>