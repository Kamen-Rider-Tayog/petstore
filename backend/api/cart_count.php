<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../functions/helpers.php';

session_start();
header('Content-Type: application/json');

$count = 0;

if (isset($_SESSION['customer_id'])) {
    $stmt = $conn->prepare("SELECT COALESCE(SUM(quantity), 0) AS total FROM cart WHERE customer_id = ?");
    $stmt->bind_param("i", $_SESSION['customer_id']);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
    $count = (int)$row['total'];
    $stmt->close();
}

echo json_encode([
    'success' => true,
    'count' => $count
]);