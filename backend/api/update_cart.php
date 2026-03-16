<?php
header('Content-Type: application/json');

require_once __DIR__ . '/../includes/cart_functions.php';

$input = json_decode(file_get_contents('php://input'), true);

if (!$input) {
    echo json_encode(['success' => false, 'message' => 'Invalid input']);
    exit;
}

$cart_id = isset($input['cart_id']) ? (int)$input['cart_id'] : 0;
$action = $input['action'] ?? '';
$newQuantity = isset($input['quantity']) ? (int)$input['quantity'] : null;

if (!$cart_id || !$action) {
    echo json_encode(['success' => false, 'message' => 'Missing required fields']);
    exit;
}

$response = ['success' => true];

if ($action === 'remove') {
    $response = removeFromCart($cart_id);
} else {
    // Determine desired quantity
    $items = getCartItems();
    $item = null;
    foreach ($items as $row) {
        if ($row['cart_id'] == $cart_id) {
            $item = $row;
            break;
        }
    }

    if (!$item) {
        echo json_encode(['success' => false, 'message' => 'Cart item not found']);
        exit;
    }

    $quantity = $item['quantity'];
    if ($action === 'increase') {
        $quantity++;
    } elseif ($action === 'decrease') {
        $quantity = max(1, $quantity - 1);
    } elseif ($newQuantity !== null) {
        $quantity = max(1, $newQuantity);
    }

    $response = updateCartQuantity($cart_id, $quantity);
}

// Fetch updated cart totals
$items = getCartItems();
$cartTotal = 0;
foreach ($items as $row) {
    $cartTotal += $row['subtotal'];
}

$response['cart_total'] = $cartTotal;
$response['cart_count'] = getCartCount();

echo json_encode($response);
