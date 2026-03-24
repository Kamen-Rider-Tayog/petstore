<?php
require_once '../includes/header.php';
require_once '../includes/sidebar.php';

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header('Location: appointments.php');
    exit;
}

$id = (int)$_GET['id'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $appointment_date = $_POST['appointment_date'] ?? '';
    $status = $_POST['status'] ?? '';
    $employee_id = (int)($_POST['employee_id'] ?? 0);

    if ($appointment_date && $status && $employee_id) {
        $sql = "UPDATE appointments SET appointment_date = ?, status = ?, employee_id = ? WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param('ssii', $appointment_date, $status, $employee_id, $id);
        $stmt->execute();
        header('Location: appointment_details.php?id=' . $id);
        exit;
    }
}

$sql = "SELECT a.*, c.first_name AS customer_first, c.last_name AS customer_last,
               p.name AS pet_name,
               s.service_name,
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

// Get all employees for dropdown
$employees = $conn->query("SELECT id, first_name, last_name FROM employees ORDER BY first_name");

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
    <h2>Edit Appointment</h2>

    <div style="margin-bottom: 20px;">
        <a href="appointment_details.php?id=<?php echo $id; ?>" class="btn">Back to Details</a>
    </div>

    <form method="post" style="max-width: 600px;">
        <div style="margin-bottom: 15px;">
            <label>Customer:</label>
            <input type="text" value="<?php echo htmlspecialchars($appointment['customer_first'] . ' ' . $appointment['customer_last']); ?>" readonly style="width: 100%; padding: 8px;">
        </div>

        <div style="margin-bottom: 15px;">
            <label>Pet:</label>
            <input type="text" value="<?php echo htmlspecialchars($appointment['pet_name']); ?>" readonly style="width: 100%; padding: 8px;">
        </div>

        <div style="margin-bottom: 15px;">
            <label>Service:</label>
            <input type="text" value="<?php echo htmlspecialchars($appointment['service_name']); ?>" readonly style="width: 100%; padding: 8px;">
        </div>

        <div style="margin-bottom: 15px;">
            <label for="appointment_date">Date & Time:</label>
            <input type="datetime-local" id="appointment_date" name="appointment_date" value="<?php echo date('Y-m-d\TH:i', strtotime($appointment['appointment_date'])); ?>" required style="width: 100%; padding: 8px;">
        </div>

        <div style="margin-bottom: 15px;">
            <label for="employee_id">Employee:</label>
            <select id="employee_id" name="employee_id" required style="width: 100%; padding: 8px;">
                <option value="">Select Employee</option>
                <?php while ($emp = $employees->fetch_assoc()): ?>
                    <option value="<?php echo $emp['id']; ?>" <?php echo $emp['id'] == $appointment['employee_id'] ? 'selected' : ''; ?>>
                        <?php echo htmlspecialchars($emp['first_name'] . ' ' . $emp['last_name']); ?>
                    </option>
                <?php endwhile; ?>
            </select>
        </div>

        <div style="margin-bottom: 15px;">
            <label for="status">Status:</label>
            <select id="status" name="status" required style="width: 100%; padding: 8px;">
                <option value="pending" <?php echo $appointment['status'] === 'pending' ? 'selected' : ''; ?>>Pending</option>
                <option value="confirmed" <?php echo $appointment['status'] === 'confirmed' ? 'selected' : ''; ?>>Confirmed</option>
                <option value="completed" <?php echo $appointment['status'] === 'completed' ? 'selected' : ''; ?>>Completed</option>
                <option value="cancelled" <?php echo $appointment['status'] === 'cancelled' ? 'selected' : ''; ?>>Cancelled</option>
                <option value="no_show" <?php echo $appointment['status'] === 'no_show' ? 'selected' : ''; ?>>No Show</option>
            </select>
        </div>

        <button type="submit" class="btn btn-primary">Update Appointment</button>
    </form>
</main>

<?php require_once '../includes/footer.php'; ?>