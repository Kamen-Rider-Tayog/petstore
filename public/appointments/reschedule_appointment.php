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
$appointmentId = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$error = '';
$success = false;

if ($appointmentId <= 0) {
    header('Location: ' . url('my_appointments'));
    exit;
}

// Get appointment details
$stmt = $conn->prepare("
    SELECT a.*, p.name as pet_name, s.service_name, s.duration_minutes 
    FROM appointments a
    LEFT JOIN store_pets p ON a.pet_id = p.id
    LEFT JOIN services s ON a.service_id = s.id
    WHERE a.id = ? AND a.customer_id = ? AND a.status != 'cancelled'
");
$stmt->bind_param("ii", $appointmentId, $customerId);
$stmt->execute();
$appointment = $stmt->get_result()->fetch_assoc();

if (!$appointment) {
    header('Location: ' . url('my_appointments'));
    exit;
}

// Handle reschedule
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $newDateTime = $_POST['appointment_date'] ?? '';
    
    if (empty($newDateTime)) {
        $error = 'Please select a new date and time.';
    } else {
        // Check if time slot is available (simplified check)
        $checkStmt = $conn->prepare("
            SELECT id FROM appointments 
            WHERE appointment_date = ? AND employee_id = ? AND id != ? AND status != 'cancelled'
        ");
        $checkStmt->bind_param("sii", $newDateTime, $appointment['employee_id'], $appointmentId);
        $checkStmt->execute();
        
        if ($checkStmt->get_result()->num_rows > 0) {
            $error = 'This time slot is not available. Please choose a different time.';
        } else {
            $updateStmt = $conn->prepare("UPDATE appointments SET appointment_date = ? WHERE id = ? AND customer_id = ?");
            $updateStmt->bind_param("sii", $newDateTime, $appointmentId, $customerId);
            
            if ($updateStmt->execute()) {
                header('Location: ' . url('my_appointments?rescheduled=1'));
                exit;
            } else {
                $error = 'Failed to reschedule. Please try again.';
            }
        }
    }
}

$page_title = 'Reschedule Appointment';
?>

<link rel="stylesheet" href="/Ria-Pet-Store/assets/css/appointments/reschedule_appointment.css?v=<?php echo time(); ?>">

<div class="reschedule-appointment-page">
    <section class="page-hero">
        <div class="container">
            <h1>Reschedule Appointment</h1>
        </div>
    </section>

    <section class="reschedule-content">
        <div class="container">
            <div class="reschedule-card">
                <?php if ($error): ?>
                    <div class="message error"><?php echo htmlspecialchars($error); ?></div>
                <?php endif; ?>

                <div class="current-appointment">
                    <h3>Current Appointment</h3>
                    <p><strong>Service:</strong> <?php echo htmlspecialchars($appointment['service_name'] ?? 'Service'); ?></p>
                    <p><strong>Pet:</strong> <?php echo htmlspecialchars($appointment['pet_name'] ?? 'N/A'); ?></p>
                    <p><strong>Date & Time:</strong> <?php echo date('F j, Y \a\t g:i A', strtotime($appointment['appointment_date'])); ?></p>
                </div>

                <form method="post" class="reschedule-form">
                    <div class="form-group">
                        <label for="appointment_date">New Date & Time <span class="required">*</span></label>
                        <input type="datetime-local" id="appointment_date" name="appointment_date" 
                               value="<?php echo date('Y-m-d\TH:i', strtotime($appointment['appointment_date'])); ?>" 
                               min="<?php echo date('Y-m-d\TH:i', strtotime('+1 hour')); ?>" required>
                        <small class="help-text">Select a new date and time for your appointment</small>
                    </div>

                    <div class="action-buttons">
                        <button type="submit" class="btn btn-primary"><?php echo icon('calendar', 16); ?> Confirm Reschedule</button>
                        <a href="<?php echo url('my_appointments'); ?>" class="btn btn-secondary"><?php echo icon('x', 16); ?> Cancel</a>
                    </div>
                </form>
            </div>
        </div>
    </section>
</div>

<?php require_once __DIR__ . '/../../backend/includes/footer.php'; ?>