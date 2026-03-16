<?php
require_once '../../backend/includes/header.php';
<link rel="stylesheet" href="../../assets/css/cancel_appointment.css">

require_once '../../backend/config/database.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

$appointmentId = (int)($_GET['id'] ?? 0);
$customerId = $_SESSION['user_id'];

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['confirm_cancel'])) {
    $stmt = $conn->prepare('UPDATE appointments SET status = ? WHERE id = ? AND customer_id = ?');
    $status = 'cancelled';
    $stmt->bind_param('sii', $status, $appointmentId, $customerId);
    if ($stmt->execute() && $stmt->affected_rows > 0) {
        header('Location: my_appointments.php?cancelled=1');
        exit;
    } else {
        $error = 'Failed to cancel appointment.';
    }
}

// Fetch appointment
$stmt = $conn->prepare('
    SELECT a.*, p.name as pet_name, s.service_name
    FROM appointments a
    JOIN pets p ON a.pet_id = p.id
    JOIN services s ON a.service_type = s.service_name
    WHERE a.id = ? AND a.customer_id = ?
');
$stmt->bind_param('ii', $appointmentId, $customerId);
$stmt->execute();
$appointment = $stmt->get_result()->fetch_assoc();

if (!$appointment) {
    echo '<p>Appointment not found.</p>';
    require_once '../../backend/includes/footer.php';
    exit;
}

?>

<h1>Cancel Appointment</h1>

<?php if (isset($error)): ?>
    <div style="color: red;"><?php echo $error; ?></div>
<?php endif; ?>

<p>Are you sure you want to cancel this appointment?</p>

<div style="border: 1px solid rgba(0,0,0,0.1); padding: 16px; border-radius: 10px; margin: 20px 0;">
    <h3><?php echo htmlspecialchars($appointment['service_name']); ?> for <?php echo htmlspecialchars($appointment['pet_name']); ?></h3>
    <p><strong>Date & Time:</strong> <?php echo date('F j, Y \a\t g:i A', strtotime($appointment['appointment_date'])); ?></p>
</div>

<form method="post">
    <button type="submit" name="confirm_cancel" value="1" class="btn btn-danger">Yes, Cancel Appointment</button>
    <a href="my_appointments.php" class="btn">No, Go Back</a>
</form>

<?php require_once '../../backend/includes/footer.php'; ?>