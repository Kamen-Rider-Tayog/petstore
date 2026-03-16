<?php
require_once '../../backend/config/database.php';

header('Content-Type: application/json');

$term = trim($_GET['term'] ?? '');

if (strlen($term) < 2) {
    echo json_encode(['products' => [], 'categories' => []]);
    exit;
}

$results = ['products' => [], 'categories' => []];

// Search products
$product_sql = "SELECT id, product_name, category FROM products WHERE product_name LIKE ? LIMIT 5";
$product_stmt = $conn->prepare($product_sql);
$search_term = "%$term%";
$product_stmt->bind_param('s', $search_term);
$product_stmt->execute();
$product_results = $product_stmt->get_result();

while ($product = $product_results->fetch_assoc()) {
    $results['products'][] = [
        'id' => $product['id'],
        'name' => $product['product_name'],
        'url' => "product_details.php?id={$product['id']}"
    ];
}

// Search categories
$category_sql = "SELECT category_name FROM categories WHERE category_name LIKE ? LIMIT 3";
$category_stmt = $conn->prepare($category_sql);
$category_stmt->bind_param('s', $search_term);
$category_stmt->execute();
$category_results = $category_stmt->get_result();

while ($category = $category_results->fetch_assoc()) {
    $results['categories'][] = [
        'name' => $category['category_name'],
        'url' => "category_products.php?name=" . urlencode($category['category_name'])
    ];
}

echo json_encode($results);
?>