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
$message = '';
$messageType = '';

// Check for messages
if (isset($_GET['cancelled'])) {
    $message = 'Appointment cancelled successfully!';
    $messageType = 'success';
}
if (isset($_GET['rescheduled'])) {
    $message = 'Appointment rescheduled successfully!';
    $messageType = 'success';
}

// Get appointments - adjust query based on your actual table structure
// If you don't have service_id, use service_type instead
$stmt = $conn->prepare("
    SELECT a.*, 
           p.name as pet_name, 
           p.species,
           s.service_name,
           s.price,
           s.duration_minutes,
           e.first_name as employee_first,
           e.last_name as employee_last
    FROM appointments a
    LEFT JOIN store_pets p ON a.pet_id = p.id
    LEFT JOIN services s ON a.service_type = s.service_name
    LEFT JOIN employees e ON a.employee_id = e.id
    WHERE a.customer_id = ? AND a.status != 'cancelled'
    ORDER BY a.appointment_date DESC
");

$stmt->bind_param("i", $customerId);
$stmt->execute();
$result = $stmt->get_result();

$page_title = 'My Appointments';
?>
<!-- Direct CSS link -->
<link rel="stylesheet" href="/Ria-Pet-Store/assets/css/appointments/my_appointments.css?v=<?php echo time(); ?>">

<div class="my-appointments-page">
    <section class="page-hero">
        <div class="container">
            <h1>My Appointments</h1>
            <p>View and manage your scheduled appointments</p>
        </div>
    </section>

    <section class="appointments-content">
        <div class="container">
            <?php if ($message): ?>
                <div class="message <?php echo $messageType; ?>">
                    <?php echo htmlspecialchars($message); ?>
                </div>
            <?php endif; ?>

            <?php if ($result->num_rows === 0): ?>
                <div class="no-appointments">
                    <?php echo icon('calendar', 48); ?>
                    <h3>No Appointments Found</h3>
                    <p>You haven't booked any appointments yet.</p>
                    <a href="<?php echo url('book_appointment'); ?>" class="btn btn-primary">
                        <?php echo icon('calendar', 16); ?> Book an Appointment
                    </a>
                </div>
            <?php else: ?>
                <div class="appointments-grid">
                    <?php while ($appointment = $result->fetch_assoc()): 
                        $isUpcoming = strtotime($appointment['appointment_date']) > time();
                        $canCancel = $isUpcoming && $appointment['status'] !== 'cancelled';
                        $canReschedule = $isUpcoming && $appointment['status'] !== 'cancelled';
                    ?>
                        <div class="appointment-card">
                            <div class="appointment-header">
                                <h3><?php echo htmlspecialchars($appointment['service_name'] ?? 'Service'); ?></h3>
                                <span class="status-badge status-<?php echo strtolower($appointment['status'] ?? 'pending'); ?>">
                                    <?php echo ucfirst($appointment['status'] ?? 'Pending'); ?>
                                </span>
                            </div>
                            
                            <div class="appointment-details">
                                <div class="detail-row">
                                    <?php echo icon('paw', 16); ?>
                                    <span><strong>Pet:</strong> <?php echo htmlspecialchars($appointment['pet_name'] ?? 'N/A'); ?></span>
                                </div>
                                <div class="detail-row">
                                    <?php echo icon('calendar', 16); ?>
                                    <span><strong>Date:</strong> <?php echo date('F j, Y', strtotime($appointment['appointment_date'])); ?></span>
                                </div>
                                <div class="detail-row">
                                    <?php echo icon('clock', 16); ?>
                                    <span><strong>Time:</strong> <?php echo date('g:i A', strtotime($appointment['appointment_date'])); ?></span>
                                </div>
                                <div class="detail-row">
                                    <?php echo icon('clock', 16); ?>
                                    <span><strong>Duration:</strong> <?php echo (int)($appointment['duration_minutes'] ?? 0); ?> minutes</span>
                                </div>
                                <div class="detail-row">
                                    <?php echo icon('user', 16); ?>
                                    <span><strong>Staff:</strong> <?php echo htmlspecialchars(($appointment['employee_first'] ?? '') . ' ' . ($appointment['employee_last'] ?? '')); ?></span>
                                </div>
                                <div class="detail-row">
                                    <?php echo icon('tag', 16); ?>
                                    <span><strong>Price:</strong> ₱<?php echo number_format($appointment['price'] ?? 0, 2); ?></span>
                                </div>
                            </div>
                            
                            <div class="appointment-actions">
                                <a href="<?php echo url('appointment_details?id=' . $appointment['id']); ?>" class="btn btn-small btn-outline">
                                    <?php echo icon('eye', 14); ?> Details
                                </a>
                                <?php if ($canReschedule): ?>
                                    <a href="<?php echo url('reschedule_appointment?id=' . $appointment['id']); ?>" class="btn btn-small btn-outline">
                                        <?php echo icon('calendar', 14); ?> Reschedule
                                    </a>
                                <?php endif; ?>
                                <?php if ($canCancel): ?>
                                    <a href="<?php echo url('cancel_appointment?id=' . $appointment['id']); ?>" class="btn btn-small btn-danger" onclick="return confirm('Are you sure you want to cancel this appointment?')">
                                        <?php echo icon('x', 14); ?> Cancel
                                    </a>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php endwhile; ?>
                </div>
            <?php endif; ?>
        </div>
    </section>
</div>

<?php require_once __DIR__ . '/../../backend/includes/footer.php'; ?>