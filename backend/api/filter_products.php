<?php
require_once '../../backend/config/database.php';
require_once '../../backend/includes/filter_functions.php';

$data = json_decode(file_get_contents('php://input'), true);

$filterQuery = buildProductFilterQuery($data);
$sort = getSortOrder($data['sort'] ?? 'relevance');
$sql = "SELECT * FROM products " . $filterQuery['where'] . " ORDER BY " . $sort;

$stmt = $conn->prepare($sql);
if(!empty($filterQuery['params'])) {
    $stmt->bind_param($filterQuery['types'], ...$filterQuery['params']);
}
$stmt->execute();
$result = $stmt->get_result();

// Generate HTML
ob_start();
while($product = $result->fetch_assoc()) {
    include '../../public/includes/product_card.php';
}
$html = ob_get_clean();

echo json_encode([
    'html' => $html,
    'count' => $result->num_rows
]);
?>