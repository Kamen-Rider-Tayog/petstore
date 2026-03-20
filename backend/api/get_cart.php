<?php
header('Content-Type: application/json');

require_once __DIR__ . '/../includes/cart_functions.php';

$items = getCartItems();
$total = calculateCartTotal();
echo json_encode([
    'success' => true,
    'items' => $items,
    'total' => $total,
    'count' => getCartCount()
]);