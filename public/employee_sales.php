<?php
require_once '../backend/config/database.php';
require_once '../backend/includes/header.php';

// Get employee sales performance
$query = "
    SELECT 
        e.id,
        e.first_name,
        e.last_name,
        e.position,
        COUNT(DISTINCT s.id) as total_transactions,
        SUM(s.quantity_sold) as total_items,
        SUM(s.quantity_sold * p.price) as total_revenue
    FROM employees e
    LEFT JOIN sales s ON e.id = s.employee_id
    LEFT JOIN products p ON s.product_id = p.id
    GROUP BY e.id
    ORDER BY total_revenue DESC
";

$result = $conn->query($query);
?>

<h1>Employee Sales Performance</h1>

<table border="1" cellpadding="5">
    <tr>
        <th>Employee</th>
        <th>Position</th>
        <th>Total Transactions</th>
        <th>Total Items Sold</th>
        <th>Total Revenue</th>
    </tr>
    
    <?php if ($result->num_rows > 0): ?>
        <?php while($row = $result->fetch_assoc()): ?>
        <tr>
            <td><?php echo htmlspecialchars($row['first_name'] . ' ' . $row['last_name']); ?></td>
            <td><?php echo htmlspecialchars($row['position']); ?></td>
            <td><?php echo $row['total_transactions'] ?? 0; ?></td>
            <td><?php echo $row['total_items'] ?? 0; ?></td>
            <td>₱<?php echo number_format($row['total_revenue'] ?? 0, 2); ?></td>
        </tr>
        <?php endwhile; ?>
    <?php else: ?>
        <tr><td colspan="5">No sales data available</td></tr>
    <?php endif; ?>
</table>

<br>
<a href="index">Back to Home</a>

<?php require_once '../backend/includes/footer.php'; ?>