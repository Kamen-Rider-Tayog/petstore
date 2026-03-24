<?php
require_once '../includes/header.php';
require_once '../includes/sidebar.php';

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header('Location: appointments.php');
    exit;
}

$id = (int)$_GET['id'];

$sql = "SELECT a.*, c.first_name AS customer_first, c.last_name AS customer_last, c.email AS customer_email, c.phone AS customer_phone,
               p.name AS pet_name, p.species, p.breed, p.age,
               s.service_name, s.price, s.duration_minutes,
               e.first_name AS emp_first, e.last_name AS emp_last
        FROM appointments a
        LEFT JOIN customers c ON a.customer_id = c.id
        LEFT JOIN store_pets p ON a.pet_id = p.id
        LEFT JOIN services s ON a.service_type = s.service_name
        LEFT JOIN employees e ON a.employee_id = e.id
        WHERE a.id = ?";

$stmt = $conn->prepare($sql);
$stmt->bind_param('i', $id);
$stmt->execute();
$appointment = $stmt->get_result()->fetch_assoc();

if (!$appointment) {
    header('Location: appointments.php');
    exit;
}

function statusBadge($status) {
    $map = [
        'pending' => 'badge badge-info',
        'confirmed' => 'badge badge-success',
        'completed' => 'badge badge-primary',
        'cancelled' => 'badge badge-warning',
        'no_show' => 'badge badge-danger',
    ];
    $class = $map[$status] ?? 'badge';
    return "<span class=\"$class\">" . ucfirst(str_replace('_', ' ', $status)) . "</span>";
}
?>

<main class="admin-main">
    <h2>Appointment Details</h2>

    <div style="display: flex; gap: 10px; margin-bottom: 20px;">
        <a href="appointments.php" class="btn">Back to Appointments</a>
        <a href="appointment_edit.php?id=<?php echo $id; ?>" class="btn btn-primary">Edit Appointment</a>
        <a href="appointment_delete.php?id=<?php echo $id; ?>" class="btn btn-danger" onclick="return confirm('Are you sure you want to delete this appointment?')">Delete</a>
    </div>

    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">
        <div>
            <h3>Appointment Information</h3>
            <p><strong>ID:</strong> <?php echo $appointment['id']; ?></p>
            <p><strong>Date & Time:</strong> <?php echo htmlspecialchars($appointment['appointment_date']); ?></p>
            <p><strong>Status:</strong> <?php echo statusBadge($appointment['status']); ?></p>
            <p><strong>Service:</strong> <?php echo htmlspecialchars($appointment['service_name']); ?> (<?php echo $appointment['price']; ?> - <?php echo $appointment['duration_minutes']; ?> min)</p>
            <p><strong>Employee:</strong> <?php echo htmlspecialchars($appointment['emp_first'] . ' ' . $appointment['emp_last']); ?></p>
        </div>

        <div>
            <h3>Customer Information</h3>
            <p><strong>Name:</strong> <?php echo htmlspecialchars($appointment['customer_first'] . ' ' . $appointment['customer_last']); ?></p>
            <p><strong>Email:</strong> <?php echo htmlspecialchars($appointment['customer_email']); ?></p>
            <p><strong>Phone:</strong> <?php echo htmlspecialchars($appointment['customer_phone']); ?></p>
        </div>

        <div>
            <h3>Pet Information</h3>
            <p><strong>Name:</strong> <?php echo htmlspecialchars($appointment['pet_name']); ?></p>
            <p><strong>Species:</strong> <?php echo htmlspecialchars($appointment['species']); ?></p>
            <p><strong>Breed:</strong> <?php echo htmlspecialchars($appointment['breed']); ?></p>
            <p><strong>Age:</strong> <?php echo $appointment['age']; ?> years</p>
        </div>
    </div>
</main>

<?php require_once '../includes/footer.php'; ?>