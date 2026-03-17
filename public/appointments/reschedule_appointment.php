<?php
require_once '../../backend/includes/auth.php';
require_once '../../backend/includes/appointment_functions.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header('Location: my_appointments.php');
    exit;
}

$id = (int)$_GET['id'];
$user_id = $_SESSION['user_id'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $appointment_date = $_POST['appointment_date'] ?? '';

    if ($appointment_date) {
        // Check if the new time is available for the service and employee
        $sql = "SELECT a.service_id, a.employee_id FROM appointments a WHERE a.id = ? AND a.customer_id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param('ii', $id, $user_id);
        $stmt->execute();
        $appt = $stmt->get_result()->fetch_assoc();

        if ($appt) {
            $service_id = $appt['service_id'];
            $employee_id = $appt['employee_id'];

            if (isTimeSlotAvailable($conn, $employee_id, $appointment_date, $service_id)) {
                $sql = "UPDATE appointments SET appointment_date = ? WHERE id = ? AND customer_id = ?";
                $stmt = $conn->prepare($sql);
                $stmt->bind_param('sii', $appointment_date, $id, $user_id);
                $stmt->execute();
                header('Location: my_appointments.php');
                exit;
            } else {
                $error = "The selected time slot is not available.";
            }
        }
    }
}

$sql = "SELECT a.*, s.service_name, s.duration_minutes FROM appointments a
        LEFT JOIN services s ON a.service_id = s.id
        WHERE a.id = ? AND a.customer_id = ? AND a.status != 'cancelled'";

$stmt = $conn->prepare($sql);
$stmt->bind_param('ii', $id, $user_id);
$stmt->execute();
$appointment = $stmt->get_result()->fetch_assoc();

if (!$appointment) {
    header('Location: my_appointments.php');
    exit;
}

require_once '../../backend/includes/header.php';
?>

<link rel="stylesheet" href="../../assets/css/reschedule_appointment.css">
<main>
    <h2>Reschedule Appointment</h2>

    <?php if (isset($error)): ?>
        <p style="color: red;"><?php echo $error; ?></p>
    <?php endif; ?>

    <form method="post">
        <div style="margin-bottom: 15px;">
            <label for="appointment_date">New Date & Time:</label>
            <input type="datetime-local" id="appointment_date" name="appointment_date" value="<?php echo date('Y-m-d\TH:i', strtotime($appointment['appointment_date'])); ?>" required>
        </div>

        <button type="submit" class="btn btn-primary">Reschedule</button>
        <a href="my_appointments.php" class="btn">Cancel</a>
    </form>
</main>

<?php require_once '../../backend/includes/footer.php'; ?>