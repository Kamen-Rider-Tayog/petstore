<?php
require_once '../includes/header.php';
require_once '../includes/sidebar.php';

// Appointment filters
$statusFilter = $_GET['status'] ?? '';
$search = trim($_GET['search'] ?? '');
$dateFrom = $_GET['from'] ?? '';
$dateTo = $_GET['to'] ?? '';

$where = [];
$params = [];
$types = '';

if ($statusFilter) {
    $where[] = 'a.status = ?';
    $params[] = $statusFilter;
    $types .= 's';
}

if ($search) {
    $where[] = '(c.first_name LIKE ? OR c.last_name LIKE ? OR p.name LIKE ?)';
    $searchTerm = "%{$search}%";
    $params[] = $searchTerm;
    $params[] = $searchTerm;
    $params[] = $searchTerm;
    $types .= 'sss';
}

if ($dateFrom) {
    $where[] = 'DATE(a.appointment_date) >= ?';
    $params[] = $dateFrom;
    $types .= 's';
}

if ($dateTo) {
    $where[] = 'DATE(a.appointment_date) <= ?';
    $params[] = $dateTo;
    $types .= 's';
}

$whereSql = '';
if (!empty($where)) {
    $whereSql = 'WHERE ' . implode(' AND ', $where);
}

$sql = "SELECT a.id, a.appointment_date, a.status, c.first_name AS customer_first, c.last_name AS customer_last, p.name AS pet_name, s.service_name, e.first_name AS emp_first, e.last_name AS emp_last
        FROM appointments a
        LEFT JOIN customers c ON a.customer_id = c.id
        LEFT JOIN pets p ON a.pet_id = p.id
        LEFT JOIN services s ON a.service_type = s.service_name
        LEFT JOIN employees e ON a.employee_id = e.id
        $whereSql
        ORDER BY a.appointment_date DESC
        LIMIT 200";

$stmt = $conn->prepare($sql);
if ($types) {
    $stmt->bind_param($types, ...$params);
}
$stmt->execute();
$appointments = $stmt->get_result();

function statusBadge($status) {
    $map = [
        'scheduled' => 'badge badge-info',
        'completed' => 'badge badge-success',
        'cancelled' => 'badge badge-warning',
        'no_show' => 'badge badge-danger',
    ];
    $class = $map[$status] ?? 'badge';
    return "<span class=\"$class\">" . ucfirst(str_replace('_', ' ', $status)) . "</span>";
}
?>

<main class="admin-main">
    <h2>Appointments</h2>

    <form method="get" style="display:flex; flex-wrap:wrap; gap:12px; align-items:center; margin-bottom:16px;">
        <input type="text" name="search" value="<?php echo htmlspecialchars($search); ?>" placeholder="Search customer or pet" style="padding:8px; width:220px;">
        <select name="status" style="padding:8px;">
            <option value="">All Statuses</option>
            <option value="scheduled" <?php echo $statusFilter === 'scheduled' ? 'selected' : ''; ?>>Scheduled</option>
            <option value="completed" <?php echo $statusFilter === 'completed' ? 'selected' : ''; ?>>Completed</option>
            <option value="cancelled" <?php echo $statusFilter === 'cancelled' ? 'selected' : ''; ?>>Cancelled</option>
            <option value="no_show" <?php echo $statusFilter === 'no_show' ? 'selected' : ''; ?>>No Show</option>
        </select>
        <label>From <input type="date" name="from" value="<?php echo htmlspecialchars($dateFrom); ?>" style="padding:8px;"></label>
        <label>To <input type="date" name="to" value="<?php echo htmlspecialchars($dateTo); ?>" style="padding:8px;"></label>
        <button class="btn" type="submit">Filter</button>
        <a href="appointments.php" class="btn btn-primary" style="margin-left:auto;">Clear</a>
    </form>

    <table style="width:100%; border-collapse:collapse;">
        <thead>
            <tr>
                <th style="text-align:left; padding:10px; border-bottom:1px solid #ddd;">ID</th>
                <th style="text-align:left; padding:10px; border-bottom:1px solid #ddd;">Date / Time</th>
                <th style="text-align:left; padding:10px; border-bottom:1px solid #ddd;">Customer</th>
                <th style="text-align:left; padding:10px; border-bottom:1px solid #ddd;">Pet</th>
                <th style="text-align:left; padding:10px; border-bottom:1px solid #ddd;">Service</th>
                <th style="text-align:left; padding:10px; border-bottom:1px solid #ddd;">Employee</th>
                <th style="text-align:left; padding:10px; border-bottom:1px solid #ddd;">Status</th>
                <th style="text-align:left; padding:10px; border-bottom:1px solid #ddd;">Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php while ($row = $appointments->fetch_assoc()): ?>
                <tr>
                    <td style="padding:10px; border-bottom:1px solid #f0f0f0;"><?php echo (int)$row['id']; ?></td>
                    <td style="padding:10px; border-bottom:1px solid #f0f0f0;"><?php echo htmlspecialchars($row['appointment_date']); ?></td>
                    <td style="padding:10px; border-bottom:1px solid #f0f0f0;"><?php echo htmlspecialchars($row['customer_first'] . ' ' . $row['customer_last']); ?></td>
                    <td style="padding:10px; border-bottom:1px solid #f0f0f0;"><?php echo htmlspecialchars($row['pet_name']); ?></td>
                    <td style="padding:10px; border-bottom:1px solid #f0f0f0;"><?php echo htmlspecialchars($row['service_name']); ?></td>
                    <td style="padding:10px; border-bottom:1px solid #f0f0f0;"><?php echo htmlspecialchars($row['emp_first'] . ' ' . $row['emp_last']); ?></td>
                    <td style="padding:10px; border-bottom:1px solid #f0f0f0;"><?php echo statusBadge($row['status']); ?></td>
                    <td style="padding:10px; border-bottom:1px solid #f0f0f0;">
                        <a href="appointment_details.php?id=<?php echo (int)$row['id']; ?>" class="btn btn-primary">View</a>
                        <a href="appointment_edit.php?id=<?php echo (int)$row['id']; ?>" class="btn">Edit</a>
                    </td>
                </tr>
            <?php endwhile; ?>
        </tbody>
    </table>
</main>

<?php require_once '../includes/footer.php'; ?>