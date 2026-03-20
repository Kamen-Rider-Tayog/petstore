<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/filter_functions.php';

header('Content-Type: application/json');

$data = json_decode(file_get_contents('php://input'), true);

$filterQuery = buildProductFilterQuery($data);
$sort = getSortOrder($data['sort'] ?? 'relevance');
$sql = "SELECT * FROM products " . $filterQuery['where'] . " ORDER BY " . $sort;

$stmt = $conn->prepare($sql);
if (!empty($filterQuery['params'])) {
    $stmt->bind_param($filterQuery['types'], ...$filterQuery['params']);
}
$stmt->execute();
$result = $stmt->get_result();

// Generate HTML
ob_start();
if ($result->num_rows > 0) {
    while ($product = $result->fetch_assoc()) {
        include __DIR__ . '/../../public/includes/product_card.php';
    }
} else {
    echo '<div class="no-results">No products found.</div>';
}
$html = ob_get_clean();

echo json_encode([
    'success' => true,
    'html' => $html,
    'count' => $result->num_rows
]);