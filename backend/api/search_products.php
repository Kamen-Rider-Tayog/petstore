<?php
header('Content-Type: application/json');
require_once '../config/database.php';

$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$category = isset($_GET['category']) ? trim($_GET['category']) : '';

$query = "SELECT * FROM products";
$conditions = [];
$params = [];
$types = '';

if (!empty($category) && $category !== 'all') {
    $conditions[] = 'category = ?';
    $types .= 's';
    $params[] = $category;
}

if (!empty($search)) {
    $conditions[] = 'product_name LIKE ?';
    $types .= 's';
    $params[] = "%{$search}%";
}

if (!empty($conditions)) {
    $query .= ' WHERE ' . implode(' AND ', $conditions);
}

$query .= ' ORDER BY category, product_name';

$stmt = $conn->prepare($query);
if (!empty($params)) {
    $stmt->bind_param($types, ...$params);
}

$stmt->execute();
$result = $stmt->get_result();

$products = [];
while ($row = $result->fetch_assoc()) {
    $products[] = $row;
}

echo json_encode([
    'success' => true,
    'count' => count($products),
    'data' => $products
]);

$stmt->close();
$conn->close();
