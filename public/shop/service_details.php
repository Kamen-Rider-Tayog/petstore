<?php
require_once '../../backend/includes/header.php';
<link rel="stylesheet" href="../../assets/css/service_details.css">

require_once '../../backend/config/database.php';

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if ($id <= 0) {
    header('Location: services.php');
    exit;
}

// Fetch service details
$stmt = $conn->prepare('SELECT * FROM services WHERE id = ? LIMIT 1');
$stmt->bind_param('i', $id);
$stmt->execute();
$service = $stmt->get_result()->fetch_assoc();
if (!$service) {
    header('Location: services.php');
    exit;
}

// Fetch employees that can perform this service
$empStmt = $conn->prepare(
    'SELECT e.id, e.first_name, e.last_name, e.position
     FROM employees e
     JOIN employee_services es ON es.employee_id = e.id
     WHERE es.service_id = ?'
);
$empStmt->bind_param('i', $id);
$empStmt->execute();
$employees = $empStmt->get_result();

// Related services (same category)
$relatedStmt = $conn->prepare('SELECT id, service_name, price FROM services WHERE category = ? AND id != ? LIMIT 4');
$relatedStmt->bind_param('si', $service['category'], $id);
$relatedStmt->execute();
$related = $relatedStmt->get_result();

function formatPrice($value) {
    return '₱' . number_format($value, 2);
}
?>

<h1><?php echo htmlspecialchars($service['service_name']); ?></h1>

<div style="display: grid; grid-template-columns: 1fr 320px; gap: 24px;">
    <div>
        <p style="font-size:1.1rem; line-height:1.6; color:#444;">
            <?php echo nl2br(htmlspecialchars($service['description'] ?? 'No description available.')); ?>
        </p>

        <div style="display:flex; gap:12px; flex-wrap:wrap; margin:20px 0;">
            <div style="padding:12px; border:1px solid rgba(0,0,0,0.08); border-radius:8px;">
                <strong>Duration</strong><br>
                <?php echo (int)$service['duration_minutes']; ?> minutes
            </div>
            <div style="padding:12px; border:1px solid rgba(0,0,0,0.08); border-radius:8px;">
                <strong>Price</strong><br>
                <?php echo formatPrice($service['price']); ?>
            </div>
            <div style="padding:12px; border:1px solid rgba(0,0,0,0.08); border-radius:8px;">
                <strong>Category</strong><br>
                <?php echo htmlspecialchars(ucfirst($service['category'] ?? '')); ?>
            </div>
        </div>

        <a href="book_appointment.php?service_id=<?php echo (int)$service['id']; ?>" class="btn btn-primary" style="margin-bottom:24px;">Book This Service</a>

        <h2 style="margin-top:0;">Who can do this?</h2>
        <?php if ($employees && $employees->num_rows > 0): ?>
            <ul style="padding-left: 18px;">
                <?php while ($emp = $employees->fetch_assoc()): ?>
                    <li><?php echo htmlspecialchars($emp['first_name'] . ' ' . $emp['last_name']); ?> (<?php echo htmlspecialchars($emp['position'] ?? 'Staff'); ?>)</li>
                <?php endwhile; ?>
            </ul>
        <?php else: ?>
            <p>No staff are currently assigned to this service.</p>
        <?php endif; ?>

        <?php if ($related && $related->num_rows > 0): ?>
            <h2>Related Services</h2>
            <ul style="padding-left: 18px;">
                <?php while ($r = $related->fetch_assoc()): ?>
                    <li>
                        <a href="service_details.php?id=<?php echo (int)$r['id']; ?>"><?php echo htmlspecialchars($r['service_name']); ?></a>
                        (<?php echo formatPrice($r['price']); ?>)
                    </li>
                <?php endwhile; ?>
            </ul>
        <?php endif; ?>
    </div>

    <aside style="border:1px solid rgba(0,0,0,0.1); border-radius:8px; padding:16px; background:#fafafa;">
        <h3 style="margin-top:0;">Quick Info</h3>
        <p><strong>Service:</strong> <?php echo htmlspecialchars($service['service_name']); ?></p>
        <p><strong>Duration:</strong> <?php echo (int)$service['duration_minutes']; ?> minutes</p>
        <p><strong>Price:</strong> <?php echo formatPrice($service['price']); ?></p>
        <a href="book_appointment.php?service_id=<?php echo (int)$service['id']; ?>" class="btn btn-primary">Book Now</a>
    </aside>
</div>

<?php require_once '../../backend/includes/footer.php'; ?>
