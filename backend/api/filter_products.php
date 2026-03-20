<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/filter_functions.php';

header('Content-Type: application/json');

// Clear any output buffers
while (ob_get_level()) ob_end_clean();

try {
    $data = json_decode(file_get_contents('php://input'), true);
    if (!$data) {
        $data = [];
    }

    // Build filter query
    $filterQuery = buildProductFilterQuery($data);
    $sort = getSortOrder($data['sort'] ?? 'relevance');
    
    // Build full SQL query
    $sql = "SELECT * FROM products " . $filterQuery['where'] . " ORDER BY " . $sort;
    
    // Prepare and execute
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
            include __DIR__ . '/../../backend/includes/product_card.php';
        }
    } else {
        echo '<div class="no-results">No products found.</div>';
    }
    $html = ob_get_clean();
    
    // Return JSON response
    echo json_encode([
        'success' => true,
        'html' => $html,
        'count' => $result->num_rows,
        'total' => $result->num_rows
    ]);

} catch (Exception $e) {
    error_log("filter_products.php error: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => 'An error occurred while fetching products.'
    ]);
}

$stmt->close();
$conn->close();
?>