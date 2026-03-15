<?php
header('Content-Type: application/json');
require_once '../config/database.php';

$customer_id = $_GET['customer_id'] ?? 0;

if (!$customer_id) {
    echo json_encode(['count' => 0]);
    exit;
}

$stmt = $conn->prepare("SELECT SUM(quantity) as total FROM cart WHERE customer_id = ?");
$stmt->bind_param("i", $customer_id);
$stmt->execute();
$result = $stmt->get_result();
$row = $result->fetch_assoc();

echo json_encode(['count' => $row['total'] ?? 0]);

$stmt->close();
$conn->close();
?>