<?php
require_once '../includes/header.php';
require_once '../includes/sidebar.php';

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header('Location: services.php');
    exit;
}

$id = (int)$_GET['id'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $sql = "DELETE FROM services WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('i', $id);
    $stmt->execute();
    header('Location: services.php');
    exit;
}

$sql = "SELECT * FROM services WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param('i', $id);
$stmt->execute();
$service = $stmt->get_result()->fetch_assoc();

if (!$service) {
    header('Location: services.php');
    exit;
}
?>

<main class="admin-main">
    <h2>Delete Service</h2>

    <p>Are you sure you want to delete this service?</p>

    <div style="background: #f9f9f9; padding: 15px; margin: 20px 0; border-radius: 5px;">
        <p><strong>Service Name:</strong> <?php echo htmlspecialchars($service['service_name']); ?></p>
        <p><strong>Price:</strong> <?php echo number_format($service['price'], 2); ?></p>
        <p><strong>Duration:</strong> <?php echo $service['duration_minutes']; ?> minutes</p>
    </div>

    <form method="post">
        <button type="submit" class="btn btn-danger">Yes, Delete Service</button>
        <a href="services.php" class="btn">Cancel</a>
    </form>
</main>

<?php require_once '../includes/footer.php'; ?>