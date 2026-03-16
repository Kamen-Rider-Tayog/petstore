<?php
require_once '../../backend/includes/header.php';
<link rel="stylesheet" href="../../assets/css/my_appointments.css">

require_once '../../backend/config/database.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

$customerId = $_SESSION['user_id'];

$stmt = $conn->prepare('
    SELECT a.*, p.name as pet_name, p.species, s.service_name, s.price, e.first_name, e.last_name
    FROM appointments a
    JOIN pets p ON a.pet_id = p.id
    JOIN services s ON a.service_type = s.service_name
    JOIN employees e ON a.employee_id = e.id
    WHERE a.customer_id = ? AND a.status != ?
    ORDER BY a.appointment_date DESC
');
$cancelled = 'cancelled';
$stmt->bind_param('is', $customerId, $cancelled);
$stmt->execute();
$result = $stmt->get_result();

?>

<h1>My Appointments</h1>

<?php if ($result->num_rows === 0): ?>
    <p>You have no appointments yet.</p>
    <p><a href="services.php" class="btn btn-primary">Book an Appointment</a></p>
<?php else: ?>
    <div style="display: grid; gap: 16px;">
        <?php while ($appointment = $result->fetch_assoc()): ?>
            <div style="border: 1px solid rgba(0,0,0,0.1); padding: 16px; border-radius: 10px;">
                <h3><?php echo htmlspecialchars($appointment['service_name']); ?> for <?php echo htmlspecialchars($appointment['pet_name']); ?></h3>
                <p><strong>Pet:</strong> <?php echo htmlspecialchars($appointment['pet_name']); ?> (<?php echo htmlspecialchars($appointment['species']); ?>)</p>
                <p><strong>Date & Time:</strong> <?php echo date('F j, Y \a\t g:i A', strtotime($appointment['appointment_date'])); ?></p>
                <p><strong>Duration:</strong> <?php echo (int)$appointment['duration_minutes']; ?> minutes</p>
                <p><strong>Employee:</strong> <?php echo htmlspecialchars($appointment['first_name'] . ' ' . $appointment['last_name']); ?></p>
                <p><strong>Price:</strong> ₱<?php echo number_format($appointment['price'], 2); ?></p>
                <div style="margin-top: 10px;">
                    <a href="appointment_details.php?id=<?php echo $appointment['id']; ?>" class="btn">View Details</a>
                    <?php if (strtotime($appointment['appointment_date']) > time() + 3600): // More than 1 hour away ?>
                        <a href="reschedule_appointment.php?id=<?php echo $appointment['id']; ?>" class="btn btn-warning">Reschedule</a>
                        <a href="cancel_appointment.php?id=<?php echo $appointment['id']; ?>" class="btn btn-danger" onclick="return confirm('Are you sure you want to cancel this appointment?')">Cancel</a>
                    <?php endif; ?>
                </div>
            </div>
        <?php endwhile; ?>
    </div>
<?php endif; ?>

<?php require_once '../../backend/includes/footer.php'; ?>