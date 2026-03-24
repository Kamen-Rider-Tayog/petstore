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

if ($appointmentId <= 0) {
    header('Location: ' . url('my_appointments'));
    exit;
}

// Get appointment details
$stmt = $conn->prepare("
    SELECT a.*, p.name as pet_name, s.service_name 
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

// Handle cancellation
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $stmt = $conn->prepare("UPDATE appointments SET status = 'cancelled' WHERE id = ? AND customer_id = ?");
    $stmt->bind_param("ii", $appointmentId, $customerId);
    
    if ($stmt->execute()) {
        header('Location: ' . url('my_appointments?cancelled=1'));
        exit;
    } else {
        $error = 'Failed to cancel appointment. Please try again.';
    }
}

$page_title = 'Cancel Appointment';
?>

<link rel="stylesheet" href="/Ria-Pet-Store/assets/css/appointments/cancel_appointment.css?v=<?php echo time(); ?>">

<div class="cancel-appointment-page">
    <section class="page-hero">
        <div class="container">
            <h1>Cancel Appointment</h1>
        </div>
    </section>

    <section class="cancel-content">
        <div class="container">
            <div class="cancel-card">
                <?php if ($error): ?>
                    <div class="message error"><?php echo htmlspecialchars($error); ?></div>
                <?php endif; ?>

                <div class="appointment-info">
                    <h3><?php echo htmlspecialchars($appointment['service_name'] ?? 'Service'); ?></h3>
                    <p><strong>Pet:</strong> <?php echo htmlspecialchars($appointment['pet_name'] ?? 'N/A'); ?></p>
                    <p><strong>Date:</strong> <?php echo date('F j, Y', strtotime($appointment['appointment_date'])); ?></p>
                    <p><strong>Time:</strong> <?php echo date('g:i A', strtotime($appointment['appointment_date'])); ?></p>
                </div>

                <p class="warning-text">Are you sure you want to cancel this appointment? This action cannot be undone.</p>

                <div class="action-buttons">
                    <form method="post" style="display: inline;">
                        <button type="submit" class="btn btn-danger"><?php echo icon('x', 16); ?> Yes, Cancel Appointment</button>
                    </form>
                    <a href="<?php echo url('my_appointments'); ?>" class="btn btn-secondary"><?php echo icon('arrow-left', 16); ?> No, Go Back</a>
                </div>
            </div>
        </div>
    </section>
</div>

<?php require_once __DIR__ . '/../../backend/includes/footer.php'; ?>