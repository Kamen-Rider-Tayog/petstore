<?php
session_name('petstore_session');
session_start();

if (!isset($_SESSION['is_admin']) || $_SESSION['is_admin'] !== true) {
    header('Location: ' . url('login?error=Access denied'));
    exit;
}

require_once __DIR__ . '/../../backend/config/database.php';
require_once __DIR__ . '/../../backend/functions/helpers.php';

$page_title = 'Add Service';
require_once __DIR__ . '/../includes/header.php';

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
            INSERT INTO services (service_name, description, category, duration_minutes, price, featured, created_at) 
            VALUES (?, ?, ?, ?, ?, ?, NOW())
        ");
        $stmt->bind_param('sssidi', $service_name, $description, $category, $duration_minutes, $price, $featured);
        
        if ($stmt->execute()) {
            $serviceId = $stmt->insert_id;
            header('Location: service_details.php?id=' . $serviceId . '&message=Service added successfully');
            exit();
        } else {
            $error = 'Error adding service: ' . $conn->error;
        }
    }
}

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
            <button type="submit" form="add-service-form" class="btn btn-primary">
                <?php echo icon('save', 16); ?> Save Service
            </button>
        </div>
    </div>

    <?php if ($error): ?>
        <div class="message error"><?php echo htmlspecialchars($error); ?></div>
    <?php endif; ?>

    <form method="post" id="add-service-form" class="service-form">
        <div class="form-grid">
            <!-- Basic Information -->
            <div class="info-card">
                <h3><?php echo icon('heart', 20); ?> Service Information</h3>
                <div class="form-group">
                    <label for="service_name">Service Name *</label>
                    <input type="text" id="service_name" name="service_name" class="form-control" required>
                </div>
                <div class="form-group">
                    <label for="category">Category</label>
                    <select id="category" name="category" class="form-control">
                        <option value="">-- Select Category --</option>
                        <option value="grooming">Grooming</option>
                        <option value="veterinary">Veterinary</option>
                        <option value="training">Training</option>
                        <option value="boarding">Boarding</option>
                        <option value="daycare">Daycare</option>
                        <option value="spa">Spa</option>
                        <option value="dental">Dental</option>
                        <option value="wellness">Wellness</option>
                    </select>
                </div>
                <div class="form-group">
                    <label for="duration_minutes">Duration (minutes)</label>
                    <select id="duration_minutes" name="duration_minutes" class="form-control">
                        <option value="15">15 minutes</option>
                        <option value="30" selected>30 minutes</option>
                        <option value="45">45 minutes</option>
                        <option value="60">1 hour</option>
                        <option value="90">1.5 hours</option>
                        <option value="120">2 hours</option>
                    </select>
                </div>
                <div class="form-group">
                    <label for="price">Price (₱) *</label>
                    <input type="number" id="price" name="price" class="form-control" step="0.01" min="0" required>
                </div>
                <div class="form-group">
                    <label class="checkbox-label">
                        <input type="checkbox" name="featured" value="1"> Featured Service
                    </label>
                    <small class="help-text">Featured services are highlighted on the frontend</small>
                </div>
            </div>

            <!-- Description -->
            <div class="info-card">
                <h3><?php echo icon('file', 20); ?> Description</h3>
                <div class="form-group">
                    <label for="description">Service Description</label>
                    <textarea id="description" name="description" rows="10" class="form-control" placeholder="Describe what this service includes..."></textarea>
                    <small class="help-text">Provide details about the service, what to expect, etc.</small>
                </div>
            </div>
        </div>
    </form>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>