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

if ($appointmentId <= 0) {
    header('Location: ' . url('my_appointments'));
    exit;
}

// Get appointment details
$stmt = $conn->prepare("
    SELECT a.*, 
           p.name as pet_name, 
           p.species,
           p.breed,
           p.age,
           p.gender,
           s.service_name,
           s.description as service_description,
           s.price,
           s.duration_minutes,
           s.category,
           e.first_name as employee_first,
           e.last_name as employee_last,
           e.position as employee_position,
           e.phone as employee_phone
    FROM appointments a
    LEFT JOIN store_pets p ON a.pet_id = p.id
    LEFT JOIN services s ON a.service_id = s.id
    LEFT JOIN employees e ON a.employee_id = e.id
    WHERE a.id = ? AND a.customer_id = ?
");

$stmt->bind_param("ii", $appointmentId, $customerId);
$stmt->execute();
$appointment = $stmt->get_result()->fetch_assoc();

if (!$appointment) {
    header('Location: ' . url('my_appointments'));
    exit;
}

// Check if appointment can be cancelled/rescheduled (only upcoming appointments)
$appointmentTime = strtotime($appointment['appointment_date']);
$canModify = ($appointmentTime > time() && $appointment['status'] !== 'cancelled');

$page_title = 'Appointment Details';
?>

<link rel="stylesheet" href="/Ria-Pet-Store/assets/css/appointments/appointment_details.css?v=<?php echo time(); ?>">

<div class="appointment-details-page">
    <section class="page-hero">
        <div class="container">
            <h1>Appointment Details</h1>
            <p>View your appointment information</p>
        </div>
    </section>

    <section class="details-content">
        <div class="container">
            <div class="details-grid">
                <!-- Left Column - Appointment Info -->
                <div class="details-card">
                    <div class="card-header">
                        <h2>Appointment Information</h2>
                        <span class="status-badge status-<?php echo strtolower($appointment['status']); ?>">
                            <?php echo ucfirst($appointment['status']); ?>
                        </span>
                    </div>

                    <div class="info-list">
                        <div class="info-row">
                            <div class="info-label">Appointment ID</div>
                            <div class="info-value">#<?php echo str_pad($appointment['id'], 6, '0', STR_PAD_LEFT); ?></div>
                        </div>
                        <div class="info-row">
                            <div class="info-label">Date</div>
                            <div class="info-value"><?php echo date('F j, Y', $appointmentTime); ?></div>
                        </div>
                        <div class="info-row">
                            <div class="info-label">Time</div>
                            <div class="info-value"><?php echo date('g:i A', $appointmentTime); ?></div>
                        </div>
                        <div class="info-row">
                            <div class="info-label">Duration</div>
                            <div class="info-value"><?php echo (int)$appointment['duration_minutes']; ?> minutes</div>
                        </div>
                        <div class="info-row">
                            <div class="info-label">Service</div>
                            <div class="info-value"><?php echo htmlspecialchars($appointment['service_name']); ?></div>
                        </div>
                        <div class="info-row">
                            <div class="info-label">Category</div>
                            <div class="info-value"><?php echo htmlspecialchars($appointment['category'] ?? 'General'); ?></div>
                        </div>
                        <div class="info-row">
                            <div class="info-label">Price</div>
                            <div class="info-value price">₱<?php echo number_format($appointment['price'], 2); ?></div>
                        </div>
                    </div>
                </div>

                <!-- Right Column - Pet Info -->
                <div class="details-card">
                    <div class="card-header">
                        <h2>Pet Information</h2>
                    </div>

                    <div class="info-list">
                        <div class="info-row">
                            <div class="info-label">Name</div>
                            <div class="info-value"><?php echo htmlspecialchars($appointment['pet_name'] ?? 'N/A'); ?></div>
                        </div>
                        <div class="info-row">
                            <div class="info-label">Species</div>
                            <div class="info-value"><?php echo htmlspecialchars($appointment['species'] ?? 'N/A'); ?></div>
                        </div>
                        <?php if (!empty($appointment['breed'])): ?>
                        <div class="info-row">
                            <div class="info-label">Breed</div>
                            <div class="info-value"><?php echo htmlspecialchars($appointment['breed']); ?></div>
                        </div>
                        <?php endif; ?>
                        <div class="info-row">
                            <div class="info-label">Age</div>
                            <div class="info-value"><?php echo (int)$appointment['age']; ?> <?php echo (int)$appointment['age'] == 1 ? 'year' : 'years'; ?> old</div>
                        </div>
                        <div class="info-row">
                            <div class="info-label">Gender</div>
                            <div class="info-value"><?php echo ucfirst($appointment['gender'] ?? 'Unknown'); ?></div>
                        </div>
                    </div>
                </div>

                <!-- Service Description -->
                <?php if (!empty($appointment['service_description'])): ?>
                <div class="details-card full-width">
                    <div class="card-header">
                        <h2>Service Details</h2>
                    </div>
                    <div class="service-description">
                        <p><?php echo nl2br(htmlspecialchars($appointment['service_description'])); ?></p>
                    </div>
                </div>
                <?php endif; ?>

                <!-- Staff Information -->
                <div class="details-card">
                    <div class="card-header">
                        <h2>Staff Information</h2>
                    </div>

                    <div class="info-list">
                        <div class="info-row">
                            <div class="info-label">Assigned Staff</div>
                            <div class="info-value">
                                <?php echo htmlspecialchars(($appointment['employee_first'] ?? '') . ' ' . ($appointment['employee_last'] ?? '')); ?>
                            </div>
                        </div>
                        <?php if (!empty($appointment['employee_position'])): ?>
                        <div class="info-row">
                            <div class="info-label">Position</div>
                            <div class="info-value"><?php echo htmlspecialchars($appointment['employee_position']); ?></div>
                        </div>
                        <?php endif; ?>
                        <?php if (!empty($appointment['employee_phone'])): ?>
                        <div class="info-row">
                            <div class="info-label">Contact</div>
                            <div class="info-value"><?php echo htmlspecialchars($appointment['employee_phone']); ?></div>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Reminders -->
                <div class="details-card">
                    <div class="card-header">
                        <h2>Reminders</h2>
                    </div>
                    <div class="reminders-list">
                        <div class="reminder-item">
                            <?php echo icon('clock', 16); ?>
                            <span>Please arrive 10 minutes before your appointment</span>
                        </div>
                        <div class="reminder-item">
                            <?php echo icon('file', 16); ?>
                            <span>Bring any relevant medical records</span>
                        </div>
                        <div class="reminder-item">
                            <?php echo icon('phone', 16); ?>
                            <span>Call us if you need to reschedule at least 24 hours in advance</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Action Buttons -->
            <div class="action-buttons">
                <?php if ($canModify): ?>
                    <a href="<?php echo url('reschedule_appointment?id=' . $appointment['id']); ?>" class="btn btn-primary">
                        <?php echo icon('calendar', 16); ?> Reschedule
                    </a>
                    <a href="<?php echo url('cancel_appointment?id=' . $appointment['id']); ?>" class="btn btn-danger" onclick="return confirm('Are you sure you want to cancel this appointment?')">
                        <?php echo icon('x', 16); ?> Cancel Appointment
                    </a>
                <?php endif; ?>
                <a href="<?php echo url('my_appointments'); ?>" class="btn btn-secondary">
                    <?php echo icon('arrow-left', 16); ?> Back to Appointments
                </a>
            </div>
        </div>
    </section>
</div>

<?php require_once __DIR__ . '/../../backend/includes/footer.php'; ?>