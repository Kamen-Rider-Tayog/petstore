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

// Get appointment details - using customer_pets instead of pets, removed address
$stmt = $conn->prepare("
    SELECT a.*, 
           c.first_name, c.last_name, c.email, c.phone,
           cp.name as pet_name, cp.species as pet_species, cp.breed as pet_breed, cp.age as pet_age, cp.gender as pet_gender, cp.color as pet_color,
           e.first_name as employee_first_name, e.last_name as employee_last_name, e.position as employee_position
    FROM appointments a
    LEFT JOIN customers c ON a.customer_id = c.id
    LEFT JOIN customer_pets cp ON a.pet_id = cp.id
    LEFT JOIN employees e ON a.employee_id = e.id
    WHERE a.id = ?
");
$stmt->bind_param('i', $appointmentId);
$stmt->execute();
$appointment = $stmt->get_result()->fetch_assoc();

if (!$appointment) {
    header('Location: appointments.php');
    exit();
}

$page_title = 'Appointment Details - #' . str_pad($appointment['id'], 6, '0', STR_PAD_LEFT);
require_once __DIR__ . '/../includes/header.php';

// Admin CSS
echo '<link rel="stylesheet" href="/Ria-Pet-Store/admin/css/appointments.css?v=' . time() . '">';
?>

<div class="admin-dashboard">
    <div class="page-header">
        <div class="action-buttons">
            <a href="appointments.php" class="btn btn-outline">
                <?php echo icon('arrow-left', 16); ?> Back
            </a>
        </div>
        <div class="action-buttons">
            <a href="edit_appointment.php?id=<?php echo $appointment['id']; ?>" class="btn btn-primary">
                <?php echo icon('edit', 16); ?> Edit Appointment
            </a>
            <?php if ($appointment['status'] !== 'cancelled' && $appointment['status'] !== 'completed'): ?>
            <a href="delete_appointment.php?id=<?php echo $appointment['id']; ?>" class="btn btn-danger" onclick="return confirm('Are you sure you want to cancel this appointment?')">
                <?php echo icon('x', 16); ?> Cancel Appointment
            </a>
            <?php endif; ?>
        </div>
    </div>

    <div class="appointment-grid">
        <!-- Appointment Information -->
        <div class="info-card">
            <h3><?php echo icon('calendar', 20); ?> Appointment Information</h3>
            <table class="info-table">
                <tr>
                    <td>Appointment ID:</td>
                    <td class="appointment-id">#<?php echo str_pad($appointment['id'], 6, '0', STR_PAD_LEFT); ?></td>
                </tr>
                <tr>
                    <td>Status:</td>
                    <td>
                        <span class="status-badge status-<?php echo strtolower($appointment['status']); ?>">
                            <?php echo ucfirst($appointment['status']); ?>
                        </span>
                    </td>
                </tr>
                <tr>
                    <td>Date & Time:</td>
                    <td><?php echo date('F j, Y \a\t g:i A', strtotime($appointment['appointment_date'])); ?></td>
                </tr>
                <tr>
                    <td>Duration:</td>
                    <td><?php echo $appointment['duration_minutes']; ?> minutes</td>
                </tr>
                <tr>
                    <td>Service Type:</td>
                    <td><?php echo htmlspecialchars($appointment['service_type']); ?></td>
                </tr>
                <tr>
                    <td>Created:</td>
                    <td><?php echo date('M d, Y g:i A', strtotime($appointment['created_at'] ?? $appointment['appointment_date'])); ?></td>
                </tr>
            </table>
        </div>

        <!-- Customer Information -->
        <div class="info-card">
            <h3><?php echo icon('user', 20); ?> Customer Information</h3>
            <table class="info-table">
                <tr>
                    <td>Name:</td>
                    <td><?php echo htmlspecialchars($appointment['first_name'] . ' ' . $appointment['last_name']); ?></td>
                </tr>
                <tr>
                    <td>Email:</td>
                    <td><?php echo htmlspecialchars($appointment['email']); ?></td>
                </tr>
                <?php if (!empty($appointment['phone'])): ?>
                <tr>
                    <td>Phone:</td>
                    <td><?php echo htmlspecialchars($appointment['phone']); ?></td>
                </tr>
                <?php endif; ?>
            </table>
        </div>
    </div>

    <!-- Pet Information -->
    <?php if (!empty($appointment['pet_name'])): ?>
    <div class="info-card">
        <h3><?php echo icon('paw', 20); ?> Pet Information</h3>
        <table class="info-table">
            <tr>
                <td>Pet Name:</td>
                <td><?php echo htmlspecialchars($appointment['pet_name']); ?></td>
            </tr>
            <?php if (!empty($appointment['pet_species'])): ?>
            <tr>
                <td>Species:</td>
                <td><?php echo htmlspecialchars($appointment['pet_species']); ?></td>
            </tr>
            <?php endif; ?>
            <?php if (!empty($appointment['pet_breed'])): ?>
            <tr>
                <td>Breed:</td>
                <td><?php echo htmlspecialchars($appointment['pet_breed']); ?></td>
            </tr>
            <?php endif; ?>
            <?php if (!empty($appointment['pet_age'])): ?>
            <tr>
                <td>Age:</td>
                <td><?php echo $appointment['pet_age']; ?> years</td>
            </tr>
            <?php endif; ?>
            <?php if (!empty($appointment['pet_gender'])): ?>
            <tr>
                <td>Gender:</td>
                <td><?php echo ucfirst($appointment['pet_gender']); ?></td>
            </tr>
            <?php endif; ?>
            <?php if (!empty($appointment['pet_color'])): ?>
            <tr>
                <td>Color:</td>
                <td><?php echo htmlspecialchars($appointment['pet_color']); ?></td>
            </tr>
            <?php endif; ?>
        </table>
    </div>
    <?php endif; ?>

    <!-- Employee Information -->
    <?php if (!empty($appointment['employee_first_name'])): ?>
    <div class="info-card">
        <h3><?php echo icon('briefcase', 20); ?> Employee Information</h3>
        <table class="info-table">
            <tr>
                <td>Employee:</td>
                <td><?php echo htmlspecialchars($appointment['employee_first_name'] . ' ' . $appointment['employee_last_name']); ?></td>
            </tr>
            <?php if (!empty($appointment['employee_position'])): ?>
            <tr>
                <td>Position:</td>
                <td><?php echo htmlspecialchars($appointment['employee_position']); ?></td>
            </tr>
            <?php endif; ?>
        </table>
    </div>
    <?php endif; ?>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>