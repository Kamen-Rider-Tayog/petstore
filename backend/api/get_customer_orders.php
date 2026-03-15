<?php
header('Content-Type: application/json');
require_once '../config/database.php';

if (!isset($_GET['customer_id']) || !is_numeric($_GET['customer_id'])) {
    echo json_encode(['success' => false, 'message' => 'Invalid customer ID']);
    exit;
}

$customer_id = $_GET['customer_id'];

// Get customer details
$stmt = $conn->prepare("SELECT first_name, last_name, email, phone FROM customers WHERE id = ?");
$stmt->bind_param("i", $customer_id);
$stmt->execute();
$customer_result = $stmt->get_result();
$customer = $customer_result->fetch_assoc();

if (!$customer) {
    echo json_encode(['success' => false, 'message' => 'Customer not found']);
    exit;
}

// Get orders with products - using sales table
$query = "
    SELECT 
        s.id as sale_id,
        s.sale_date as order_date,
        s.quantity_sold,
        p.product_name,
        p.price,
        (s.quantity_sold * p.price) as subtotal
    FROM sales s
    JOIN products p ON s.product_id = p.id
    WHERE s.customer_id = ?
    ORDER BY s.sale_date DESC
";

$stmt = $conn->prepare($query);
$stmt->bind_param("i", $customer_id);
$stmt->execute();
$result = $stmt->get_result();

$orders = [];
while ($row = $result->fetch_assoc()) {
    $orders[] = [
        'id' => $row['sale_id'],
        'order_date' => $row['order_date'],
        'total' => $row['subtotal'],
        'products' => [[
            'product_name' => $row['product_name'],
            'quantity' => $row['quantity_sold'],
            'price' => $row['price'],
            'subtotal' => $row['subtotal']
        ]]
    ];
}

echo json_encode([
    'success' => true,
    'customer' => $customer,
    'orders' => $orders
]);

$conn->close();
?>