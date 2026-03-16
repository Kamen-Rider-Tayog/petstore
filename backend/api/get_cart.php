<?php
header('Content-Type: application/json');

require_once __DIR__ . '/../includes/cart_functions.php';

$items = getCartItems();
$total = 0;
foreach ($items as &$item) {
    $total += $item['subtotal'];
}

echo json_encode([
    'success' => true,
    'items' => $items,
    'total' => $total,
]);
