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
$message = '';

if (!$appointmentId) {
    header('Location: appointments.php');
    exit();
}

// Get appointment details
$stmt = $conn->prepare("
    SELECT a.*, c.first_name, c.last_name, c.email
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

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['confirm_delete'])) {
        // Delete appointment
        $stmt = $conn->prepare("DELETE FROM appointments WHERE id = ?");
        $stmt->bind_param('i', $appointmentId);
        
        if ($stmt->execute()) {
            header('Location: appointments.php?message=Appointment deleted successfully');
            exit();
        } else {
            $message = 'Error deleting appointment: ' . $conn->error;
        }
    } elseif (isset($_POST['confirm_cancel'])) {
        // Just cancel (update status)
        $stmt = $conn->prepare("UPDATE appointments SET status = 'cancelled' WHERE id = ?");
        $stmt->bind_param('i', $appointmentId);
        
        if ($stmt->execute()) {
            header('Location: appointments.php?message=Appointment cancelled successfully');
            exit();
        } else {
            $message = 'Error cancelling appointment: ' . $conn->error;
        }
    }
}

$page_title = 'Delete/Cancel Appointment - #' . str_pad($appointment['id'], 6, '0', STR_PAD_LEFT);
require_once __DIR__ . '/../includes/header.php';

// Admin CSS
echo '<link rel="stylesheet" href="/Ria-Pet-Store/admin/css/appointments.css?v=' . time() . '">';
?>

<div class="admin-dashboard">
    <div class="page-header">
        <h1>Cancel/Delete Appointment</h1>
        <div class="action-buttons">
            <a href="appointment_details.php?id=<?php echo $appointment['id']; ?>" class="btn btn-outline">
                <?php echo icon('arrow-left', 16); ?> Back to Details
            </a>
        </div>
    </div>

    <?php if ($message): ?>
        <div class="message error"><?php echo htmlspecialchars($message); ?></div>
    <?php endif; ?>

    <div class="delete-confirmation">
        <div class="warning-box">
            <?php echo icon('alert-triangle', 48); ?>
            <h3>Warning: This action cannot be undone!</h3>
            <p>Are you sure you want to cancel/delete this appointment?</p>
        </div>

        <div class="appointment-summary">
            <h3>Appointment Details</h3>
            <div class="summary-grid">
                <div class="summary-item">
                    <span class="label">Appointment ID:</span>
                    <span class="value">#<?php echo str_pad($appointment['id'], 6, '0', STR_PAD_LEFT); ?></span>
                </div>
                <div class="summary-item">
                    <span class="label">Customer:</span>
                    <span class="value"><?php echo htmlspecialchars($appointment['first_name'] . ' ' . $appointment['last_name']); ?></span>
                </div>
                <div class="summary-item">
                    <span class="label">Service:</span>
                    <span class="value"><?php echo htmlspecialchars($appointment['service_type']); ?></span>
                </div>
                <div class="summary-item">
                    <span class="label">Date & Time:</span>
                    <span class="value"><?php echo date('M d, Y', strtotime($appointment['appointment_date'])); ?> at <?php echo date('g:i A', strtotime($appointment['appointment_time'])); ?></span>
                </div>
                <div class="summary-item">
                    <span class="label">Status:</span>
                    <span class="value">
                        <span class="status-badge status-<?php echo strtolower($appointment['status']); ?>">
                            <?php echo ucfirst(str_replace('_', ' ', $appointment['status'])); ?>
                        </span>
                    </span>
                </div>
                <?php if (!empty($appointment['pet_name'])): ?>
                <div class="summary-item">
                    <span class="label">Pet:</span>
                    <span class="value"><?php echo htmlspecialchars($appointment['pet_name']); ?></span>
                </div>
                <?php endif; ?>
            </div>
        </div>

        <div class="action-buttons">
            <?php if ($appointment['status'] !== 'cancelled' && $appointment['status'] !== 'completed'): ?>
                <form method="post" style="display: inline;">
                    <button type="submit" name="confirm_cancel" class="btn btn-warning">
                        <?php echo icon('x', 16); ?> Cancel Appointment Only
                    </button>
                </form>
            <?php endif; ?>
            <form method="post" style="display: inline;" onsubmit="return confirm('Are you absolutely sure you want to permanently delete this appointment?');">
                <button type="submit" name="confirm_delete" class="btn btn-danger">
                    <?php echo icon('trash', 16); ?> Permanently Delete
                </button>
            </form>
            <a href="appointment_details.php?id=<?php echo $appointment['id']; ?>" class="btn btn-outline">Keep Appointment</a>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>