<?php
require_once '../../backend/config/database.php';
require_once '../../backend/includes/header.php';
<link rel="stylesheet" href="../../assets/css/appointments.css">


$query = "
    SELECT 
        a.appointment_date,
        c.first_name as customer_first,
        c.last_name as customer_last,
        p.name as pet_name,
        e.first_name as employee_first,
        e.last_name as employee_last,
        a.service_type,
        a.duration_minutes
    FROM appointments a
    JOIN customers c ON a.customer_id = c.id
    JOIN pets p ON a.pet_id = p.id
    JOIN employees e ON a.employee_id = e.id
    ORDER BY a.appointment_date DESC
";

$result = $conn->query($query);
?>

<h1>Appointments</h1>

<table border="1" cellpadding="5">
    <tr>
        <th>Date & Time</th>
        <th>Customer</th>
        <th>Pet</th>
        <th>Employee</th>
        <th>Service</th>
        <th>Duration</th>
    </tr>
    
    <?php if ($result->num_rows > 0): ?>
        <?php while($row = $result->fetch_assoc()): ?>
        <tr>
            <td><?php echo date('M j, Y g:i A', strtotime($row['appointment_date'])); ?></td>
            <td><?php echo htmlspecialchars($row['customer_first'] . ' ' . $row['customer_last']); ?></td>
            <td><?php echo htmlspecialchars($row['pet_name']); ?></td>
            <td><?php echo htmlspecialchars($row['employee_first'] . ' ' . $row['employee_last']); ?></td>
            <td><?php echo htmlspecialchars($row['service_type']); ?></td>
            <td><?php echo $row['duration_minutes']; ?> min</td>
        </tr>
        <?php endwhile; ?>
    <?php else: ?>
        <tr><td colspan="6">No appointments found</td></tr>
    <?php endif; ?>
</table>

<br>
<a href="index">Back to Home</a>

<?php require_once '../../backend/includes/footer.php'; ?>