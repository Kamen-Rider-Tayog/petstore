<?php
header('Content-Type: application/json');
require_once '../config/database.php';

// Get POST data
$input = json_decode(file_get_contents('php://input'), true);

if (!$input) {
    echo json_encode(['success' => false, 'message' => 'Invalid input']);
    exit;
}

$product_id = $input['product_id'] ?? 0;
$customer_id = $input['customer_id'] ?? 0;
$quantity = $input['quantity'] ?? 1;

if (!$product_id || !$customer_id) {
    echo json_encode(['success' => false, 'message' => 'Missing required fields']);
    exit;
}

// Check if product already in cart
$stmt = $conn->prepare("SELECT id, quantity FROM cart WHERE customer_id = ? AND product_id = ?");
$stmt->bind_param("ii", $customer_id, $product_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    // Update existing cart item
    $cart_item = $result->fetch_assoc();
    $new_quantity = $cart_item['quantity'] + $quantity;
    
    $update = $conn->prepare("UPDATE cart SET quantity = ? WHERE id = ?");
    $update->bind_param("ii", $new_quantity, $cart_item['id']);
    $update->execute();
    $update->close();
} else {
    // Insert new cart item
    $insert = $conn->prepare("INSERT INTO cart (customer_id, product_id, quantity) VALUES (?, ?, ?)");
    $insert->bind_param("iii", $customer_id, $product_id, $quantity);
    $insert->execute();
    $insert->close();
}

// Get updated cart count
$count_stmt = $conn->prepare("SELECT SUM(quantity) as total FROM cart WHERE customer_id = ?");
$count_stmt->bind_param("i", $customer_id);
$count_stmt->execute();
$count_result = $count_stmt->get_result();
$count_row = $count_result->fetch_assoc();
$cart_count = $count_row['total'] ?? 0;

echo json_encode([
    'success' => true,
    'message' => 'Product added to cart',
    'cart_count' => $cart_count
]);

$stmt->close();
$count_stmt->close();
$conn->close();
?>