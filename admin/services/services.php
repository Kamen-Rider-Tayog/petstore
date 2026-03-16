<?php
require_once '../includes/header.php';
require_once '../includes/sidebar.php';

$services = $conn->query("SELECT * FROM services ORDER BY service_name");
?>

<main class="admin-main">
    <h2>Services Management</h2>

    <div style="margin-bottom: 20px;">
        <a href="services_add.php" class="btn btn-primary">Add New Service</a>
    </div>

    <table style="width:100%; border-collapse:collapse;">
        <thead>
            <tr>
                <th style="text-align:left; padding:10px; border-bottom:1px solid #ddd;">ID</th>
                <th style="text-align:left; padding:10px; border-bottom:1px solid #ddd;">Service Name</th>
                <th style="text-align:left; padding:10px; border-bottom:1px solid #ddd;">Price</th>
                <th style="text-align:left; padding:10px; border-bottom:1px solid #ddd;">Duration (min)</th>
                <th style="text-align:left; padding:10px; border-bottom:1px solid #ddd;">Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php while ($service = $services->fetch_assoc()): ?>
                <tr>
                    <td style="padding:10px; border-bottom:1px solid #f0f0f0;"><?php echo $service['id']; ?></td>
                    <td style="padding:10px; border-bottom:1px solid #f0f0f0;"><?php echo htmlspecialchars($service['service_name']); ?></td>
                    <td style="padding:10px; border-bottom:1px solid #f0f0f0;"><?php echo number_format($service['price'], 2); ?></td>
                    <td style="padding:10px; border-bottom:1px solid #f0f0f0;"><?php echo $service['duration_minutes']; ?></td>
                    <td style="padding:10px; border-bottom:1px solid #f0f0f0;">
                        <a href="services_edit.php?id=<?php echo $service['id']; ?>" class="btn">Edit</a>
                        <a href="services_delete.php?id=<?php echo $service['id']; ?>" class="btn btn-danger" onclick="return confirm('Delete this service?')">Delete</a>
                    </td>
                </tr>
            <?php endwhile; ?>
        </tbody>
    </table>
</main>

<?php require_once '../includes/footer.php'; ?>