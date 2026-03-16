<?php
header('Content-Type: application/json');

require_once __DIR__ . '/../includes/cart_functions.php';

echo json_encode(['count' => getCartCount()]);
