<?php
require_once '../../backend/config/database.php';
require_once '../../backend/includes/filter_functions.php';

$data = json_decode(file_get_contents('php://input'), true);

$page = (int)($data['page'] ?? 1);
$per_page = 12;
$offset = ($page - 1) * $per_page;

$filterQuery = buildProductFilterQuery($data['filters'] ?? []);
$sort = getSortOrder($data['filters']['sort'] ?? 'relevance');
$sql = "SELECT * FROM products " . $filterQuery['where'] . " ORDER BY " . $sort . " LIMIT ? OFFSET ?";

$stmt = $conn->prepare($sql);
$params = array_merge($filterQuery['params'], [$per_page, $offset]);
$types = $filterQuery['types'] . 'ii';
$stmt->bind_param($types, ...$params);
$stmt->execute();
$result = $stmt->get_result();

// Generate HTML
ob_start();
while($product = $result->fetch_assoc()) {
    include '../../public/includes/product_card.php';
}
$html = ob_get_clean();

$hasMore = $result->num_rows === $per_page;

echo json_encode([
    'html' => $html,
    'hasMore' => $hasMore
]);
?>