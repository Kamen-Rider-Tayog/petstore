<?php
require_once '../includes/header.php';
require_once '../includes/sidebar.php';

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header('Location: appointments.php');
    exit;
}

$id = (int)$_GET['id'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $sql = "DELETE FROM appointments WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('i', $id);
    $stmt->execute();
    header('Location: appointments.php');
    exit;
}

$sql = "SELECT a.id, a.appointment_date, c.first_name AS customer_first, c.last_name AS customer_last, p.name AS pet_name
        FROM appointments a
        LEFT JOIN customers c ON a.customer_id = c.id
        LEFT JOIN store_pets p ON a.pet_id = p.id
        WHERE a.id = ?";

$stmt = $conn->prepare($sql);
$stmt->bind_param('i', $id);
$stmt->execute();
$appointment = $stmt->get_result()->fetch_assoc();

if (!$appointment) {
    header('Location: appointments.php');
    exit;
}
?>

<main class="admin-main">
    <h2>Delete Appointment</h2>

    <p>Are you sure you want to delete this appointment?</p>

    <div style="background: #f9f9f9; padding: 15px; margin: 20px 0; border-radius: 5px;">
        <p><strong>Appointment ID:</strong> <?php echo $appointment['id']; ?></p>
        <p><strong>Date:</strong> <?php echo htmlspecialchars($appointment['appointment_date']); ?></p>
        <p><strong>Customer:</strong> <?php echo htmlspecialchars($appointment['customer_first'] . ' ' . $appointment['customer_last']); ?></p>
        <p><strong>Pet:</strong> <?php echo htmlspecialchars($appointment['pet_name']); ?></p>
    </div>

    <form method="post">
        <button type="submit" class="btn btn-danger">Yes, Delete Appointment</button>
        <a href="appointment_details.php?id=<?php echo $id; ?>" class="btn">Cancel</a>
    </form>
</main>

<?php require_once '../includes/footer.php'; ?>