<?php
header('Content-Type: application/json');

require_once __DIR__ . '/../includes/cart_functions.php';

$input = json_decode(file_get_contents('php://input'), true);

if (!$input) {
    echo json_encode(['success' => false, 'message' => 'Invalid input']);
    exit;
}

$product_id = isset($input['product_id']) ? (int)$input['product_id'] : 0;
$quantity = isset($input['quantity']) ? (int)$input['quantity'] : 1;

if (!$product_id) {
    echo json_encode(['success' => false, 'message' => 'Product ID is required']);
    exit;
}

$result = addToCart($product_id, $quantity);

// Always include cart count for UI updates
$result['cart_count'] = getCartCount();

echo json_encode($result);
