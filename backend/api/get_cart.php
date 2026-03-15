<?php
header('Content-Type: application/json');
require_once '../config/database.php';

$customer_id = $_GET['customer_id'] ?? 0;

if (!$customer_id) {
    echo json_encode(['success' => false, 'message' => 'Customer ID required']);
    exit;
}

$query = "
    SELECT 
        c.id as cart_id,
        c.quantity,
        p.id as product_id,
        p.product_name,
        p.price,
        (c.quantity * p.price) as subtotal
    FROM cart c
    JOIN products p ON c.product_id = p.id
    WHERE c.customer_id = ?
";

$stmt = $conn->prepare($query);
$stmt->bind_param("i", $customer_id);
$stmt->execute();
$result = $stmt->get_result();

$cart_items = [];
$total = 0;

while ($row = $result->fetch_assoc()) {
    $cart_items[] = $row;
    $total += $row['subtotal'];
}

echo json_encode([
    'success' => true,
    'items' => $cart_items,
    'total' => $total
]);

$stmt->close();
$conn->close();
?>