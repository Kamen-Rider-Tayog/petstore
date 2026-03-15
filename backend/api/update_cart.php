<?php
header('Content-Type: application/json');
require_once '../config/database.php';

$input = json_decode(file_get_contents('php://input'), true);

if (!$input) {
    echo json_encode(['success' => false, 'message' => 'Invalid input']);
    exit;
}

$cart_id = $input['cart_id'] ?? 0;
$action = $input['action'] ?? '';
$customer_id = $input['customer_id'] ?? 0;

if (!$cart_id || !$action) {
    echo json_encode(['success' => false, 'message' => 'Missing required fields']);
    exit;
}

if ($action === 'remove') {
    // Remove item from cart
    $stmt = $conn->prepare("DELETE FROM cart WHERE id = ?");
    $stmt->bind_param("i", $cart_id);
    $stmt->execute();
} else {
    // Update quantity
    $stmt = $conn->prepare("SELECT quantity FROM cart WHERE id = ?");
    $stmt->bind_param("i", $cart_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $item = $result->fetch_assoc();
    
    if ($item) {
        $new_quantity = $item['quantity'];
        
        if ($action === 'increase') {
            $new_quantity++;
        } elseif ($action === 'decrease' && $item['quantity'] > 1) {
            $new_quantity--;
        }
        
        if ($new_quantity != $item['quantity']) {
            $update = $conn->prepare("UPDATE cart SET quantity = ? WHERE id = ?");
            $update->bind_param("ii", $new_quantity, $cart_id);
            $update->execute();
            $update->close();
        }
    }
}

// Get updated cart info
$count_stmt = $conn->prepare("SELECT SUM(quantity) as total FROM cart WHERE customer_id = ?");
$count_stmt->bind_param("i", $customer_id);
$count_stmt->execute();
$count_result = $count_stmt->get_result();
$count_row = $count_result->fetch_assoc();

echo json_encode([
    'success' => true,
    'cart_count' => $count_row['total'] ?? 0
]);

$stmt->close();
$count_stmt->close();
$conn->close();
?>