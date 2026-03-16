<?php
header('Content-Type: application/json');

require_once __DIR__ . '/../includes/cart_functions.php';

$input = json_decode(file_get_contents('php://input'), true);

if (!$input) {
    echo json_encode(['success' => false, 'message' => 'Invalid input']);
    exit;
}

$cart_id = isset($input['cart_id']) ? (int)$input['cart_id'] : 0;

if (!$cart_id) {
    echo json_encode(['success' => false, 'message' => 'Cart ID is required']);
    exit;
}

$result = removeFromCart($cart_id);

$items = getCartItems();
$total = 0;
foreach ($items as $item) {
    $total += $item['subtotal'];
}

$result['cart_total'] = $total;
$result['cart_count'] = getCartCount();

// If cart is empty, provide a flag
$result['empty'] = ($result['cart_count'] === 0);

echo json_encode($result);
