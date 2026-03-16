<?php
require_once '../includes/header.php';
require_once '../includes/sidebar.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $service_name = trim($_POST['service_name'] ?? '');
    $price = (float)($_POST['price'] ?? 0);
    $duration_minutes = (int)($_POST['duration_minutes'] ?? 0);

    if ($service_name && $price > 0 && $duration_minutes > 0) {
        $sql = "INSERT INTO services (service_name, price, duration_minutes) VALUES (?, ?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param('sdi', $service_name, $price, $duration_minutes);
        $stmt->execute();
        header('Location: services.php');
        exit;
    }
}
?>

<main class="admin-main">
    <h2>Add New Service</h2>

    <form method="post" style="max-width: 400px;">
        <div style="margin-bottom: 15px;">
            <label for="service_name">Service Name:</label>
            <input type="text" id="service_name" name="service_name" required style="width: 100%; padding: 8px;">
        </div>

        <div style="margin-bottom: 15px;">
            <label for="price">Price:</label>
            <input type="number" id="price" name="price" step="0.01" min="0" required style="width: 100%; padding: 8px;">
        </div>

        <div style="margin-bottom: 15px;">
            <label for="duration_minutes">Duration (minutes):</label>
            <input type="number" id="duration_minutes" name="duration_minutes" min="1" required style="width: 100%; padding: 8px;">
        </div>

        <button type="submit" class="btn btn-primary">Add Service</button>
        <a href="services.php" class="btn">Cancel</a>
    </form>
</main>

<?php require_once '../includes/footer.php'; ?>