<?php
session_name('petstore_session');
session_start();

// Check if user is logged in as admin
if (!isset($_SESSION['is_admin']) || $_SESSION['is_admin'] !== true) {
    header('Location: ' . url('login?error=Access denied'));
    exit;
}

require_once __DIR__ . '/../../backend/config/database.php';
require_once __DIR__ . '/../../backend/functions/helpers.php';

$appointmentId = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if (!$appointmentId) {
    header('Location: appointments.php');
    exit();
}

// Get appointment details
$stmt = $conn->prepare("
    SELECT a.*, c.first_name, c.last_name, c.email, c.phone
    FROM appointments a
    LEFT JOIN customers c ON a.customer_id = c.id
    WHERE a.id = ?
");
$stmt->bind_param('i', $appointmentId);
$stmt->execute();
$appointment = $stmt->get_result()->fetch_assoc();

if (!$appointment) {
    header('Location: appointments.php');
    exit();
}

// Get customers for dropdown
$customers = [];
$customerResult = $conn->query("SELECT id, first_name, last_name, email FROM customers ORDER BY first_name");
while ($row = $customerResult->fetch_assoc()) {
    $customers[] = $row;
}

// Get customer pets for dropdown
$pets = [];
$petResult = $conn->query("SELECT id, name, species FROM customer_pets ORDER BY name");
while ($row = $petResult->fetch_assoc()) {
    $pets[] = $row;
}

// Get employees for dropdown
$employees = [];
$employeeResult = $conn->query("SELECT id, first_name, last_name, position FROM employees ORDER BY first_name");
while ($row = $employeeResult->fetch_assoc()) {
    $employees[] = $row;
}

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $customerId = (int)($_POST['customer_id'] ?? 0);
    $petId = (int)($_POST['pet_id'] ?? 0);
    $employeeId = (int)($_POST['employee_id'] ?? 0);
    $appointmentDate = trim($_POST['appointment_date'] ?? '');
    $appointmentTime = trim($_POST['appointment_time'] ?? '');
    $serviceType = trim($_POST['service_type'] ?? '');
    $durationMinutes = (int)($_POST['duration_minutes'] ?? 30);
    $status = trim($_POST['status'] ?? 'pending');

    // Combine date and time
    $appointmentDateTime = $appointmentDate . ' ' . $appointmentTime . ':00';

    if (!$customerId || !$appointmentDate || !$appointmentTime || !$serviceType) {
        $error = 'Please fill in all required fields.';
    } else {
        $stmt = $conn->prepare("
            UPDATE appointments 
            SET customer_id = ?, pet_id = ?, employee_id = ?, 
                appointment_date = ?, service_type = ?, duration_minutes = ?, status = ?
            WHERE id = ?
        ");
        $stmt->bind_param('iiissisi', $customerId, $petId, $employeeId, 
            $appointmentDateTime, $serviceType, $durationMinutes, $status, $appointmentId);
        
        if ($stmt->execute()) {
            $success = 'Appointment updated successfully!';
            // Refresh appointment data
            $stmt = $conn->prepare("
                SELECT a.*, c.first_name, c.last_name, c.email, c.phone
                FROM appointments a
                LEFT JOIN customers c ON a.customer_id = c.id
                WHERE a.id = ?
            ");
            $stmt->bind_param('i', $appointmentId);
            $stmt->execute();
            $appointment = $stmt->get_result()->fetch_assoc();
        } else {
            $error = 'Error updating appointment: ' . $conn->error;
        }
    }
}

$page_title = 'Edit Appointment - #' . str_pad($appointment['id'], 6, '0', STR_PAD_LEFT);
require_once __DIR__ . '/../includes/header.php';

// Admin CSS
echo '<link rel="stylesheet" href="/Ria-Pet-Store/admin/css/appointments.css?v=' . time() . '">';
?>

<div class="admin-dashboard">
    <div class="page-header">
        <div class="header-left">
            <a href="appointments.php" class="btn btn-outline">
                <?php echo icon('arrow-left', 16); ?> Back to Appointments
            </a>
        </div>
        <div class="header-right">
            <button type="submit" form="edit-appointment-form" class="btn btn-primary">
                <?php echo icon('save', 16); ?> Save Changes
            </button>
        </div>
    </div>

    <?php if ($error): ?>
        <div class="message error"><?php echo htmlspecialchars($error); ?></div>
    <?php endif; ?>
    
    <?php if ($success): ?>
        <div class="message success"><?php echo htmlspecialchars($success); ?></div>
    <?php endif; ?>

    <form method="post" id="edit-appointment-form" class="appointment-form">
        <div class="form-grid">
            <!-- Appointment Information -->
            <div class="info-card">
                <h3><?php echo icon('calendar', 20); ?> Appointment Information</h3>
                <div class="form-group">
                    <label for="appointment_date">Appointment Date *</label>
                    <input type="date" id="appointment_date" name="appointment_date" class="form-control" 
                           value="<?php echo date('Y-m-d', strtotime($appointment['appointment_date'])); ?>" required>
                </div>
                <div class="form-group">
                    <label for="appointment_time">Appointment Time *</label>
                    <input type="time" id="appointment_time" name="appointment_time" class="form-control" 
                           value="<?php echo date('H:i', strtotime($appointment['appointment_date'])); ?>" required>
                </div>
                <div class="form-group">
                    <label for="duration_minutes">Duration (minutes)</label>
                    <select id="duration_minutes" name="duration_minutes" class="form-control">
                        <option value="15" <?php echo $appointment['duration_minutes'] == 15 ? 'selected' : ''; ?>>15 minutes</option>
                        <option value="30" <?php echo $appointment['duration_minutes'] == 30 ? 'selected' : ''; ?>>30 minutes</option>
                        <option value="45" <?php echo $appointment['duration_minutes'] == 45 ? 'selected' : ''; ?>>45 minutes</option>
                        <option value="60" <?php echo $appointment['duration_minutes'] == 60 ? 'selected' : ''; ?>>1 hour</option>
                        <option value="90" <?php echo $appointment['duration_minutes'] == 90 ? 'selected' : ''; ?>>1.5 hours</option>
                        <option value="120" <?php echo $appointment['duration_minutes'] == 120 ? 'selected' : ''; ?>>2 hours</option>
                    </select>
                </div>
            </div>

            <!-- Customer Information -->
            <div class="info-card">
                <h3><?php echo icon('user', 20); ?> Customer Information</h3>
                <div class="form-group">
                    <label for="customer_id">Customer *</label>
                    <select id="customer_id" name="customer_id" class="form-control" required>
                        <option value="">-- Select Customer --</option>
                        <?php foreach ($customers as $customer): ?>
                            <option value="<?php echo $customer['id']; ?>" <?php echo $appointment['customer_id'] == $customer['id'] ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($customer['first_name'] . ' ' . $customer['last_name'] . ' (' . $customer['email'] . ')'); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group">
                    <label for="pet_id">Pet</label>
                    <select id="pet_id" name="pet_id" class="form-control">
                        <option value="0">-- No Pet --</option>
                        <?php foreach ($pets as $pet): ?>
                            <option value="<?php echo $pet['id']; ?>" <?php echo $appointment['pet_id'] == $pet['id'] ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($pet['name'] . ' (' . $pet['species'] . ')'); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>

            <!-- Service Details -->
            <div class="info-card">
                <h3><?php echo icon('heart', 20); ?> Service Details</h3>
                <div class="form-group">
                    <label for="service_type">Service Type *</label>
                    <select id="service_type" name="service_type" class="form-control" required>
                        <option value="">-- Select Service --</option>
                        <option value="Grooming" <?php echo $appointment['service_type'] == 'Grooming' ? 'selected' : ''; ?>>Grooming</option>
                        <option value="Veterinary Check-up" <?php echo $appointment['service_type'] == 'Veterinary Check-up' ? 'selected' : ''; ?>>Veterinary Check-up</option>
                        <option value="Vaccination" <?php echo $appointment['service_type'] == 'Vaccination' ? 'selected' : ''; ?>>Vaccination</option>
                        <option value="Dental Care" <?php echo $appointment['service_type'] == 'Dental Care' ? 'selected' : ''; ?>>Dental Care</option>
                        <option value="Surgery" <?php echo $appointment['service_type'] == 'Surgery' ? 'selected' : ''; ?>>Surgery</option>
                        <option value="Training" <?php echo $appointment['service_type'] == 'Training' ? 'selected' : ''; ?>>Training</option>
                        <option value="Daycare" <?php echo $appointment['service_type'] == 'Daycare' ? 'selected' : ''; ?>>Daycare</option>
                        <option value="Boarding" <?php echo $appointment['service_type'] == 'Boarding' ? 'selected' : ''; ?>>Boarding</option>
                    </select>
                </div>
                <div class="form-group">
                    <label for="employee_id">Assigned Employee</label>
                    <select id="employee_id" name="employee_id" class="form-control">
                        <option value="0">-- Unassigned --</option>
                        <?php foreach ($employees as $employee): ?>
                            <option value="<?php echo $employee['id']; ?>" <?php echo $appointment['employee_id'] == $employee['id'] ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($employee['first_name'] . ' ' . $employee['last_name'] . ' (' . $employee['position'] . ')'); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>

            <!-- Status -->
            <div class="info-card">
                <h3><?php echo icon('settings', 20); ?> Status</h3>
                <div class="form-group">
                    <label for="status">Appointment Status</label>
                    <select id="status" name="status" class="form-control">
                        <option value="pending" <?php echo $appointment['status'] == 'pending' ? 'selected' : ''; ?>>Pending</option>
                        <option value="confirmed" <?php echo $appointment['status'] == 'confirmed' ? 'selected' : ''; ?>>Confirmed</option>
                        <option value="completed" <?php echo $appointment['status'] == 'completed' ? 'selected' : ''; ?>>Completed</option>
                        <option value="cancelled" <?php echo $appointment['status'] == 'cancelled' ? 'selected' : ''; ?>>Cancelled</option>
                    </select>
                </div>
            </div>
        </div>
    </form>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>