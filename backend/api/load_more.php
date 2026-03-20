<?php
// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Log the request
error_log("load_more.php called with data: " . file_get_contents('php://input'));

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/filter_functions.php';

// Clear any output buffering
ob_clean();
header('Content-Type: application/json');

$response = ['success' => false, 'message' => ''];

try {
    $data = json_decode(file_get_contents('php://input'), true);
    
    if (!$data) {
        throw new Exception('Invalid JSON data');
    }

    $page = (int)($data['page'] ?? 1);
    $per_page = (int)($data['per_page'] ?? 12);
    $offset = ($page - 1) * $per_page;
    $type = $data['type'] ?? 'products';

    if ($type === 'pets') {
        // ===== PETS LOAD MORE =====
        $whereConditions = ["pet_status = 'available'"];
        $params = [];
        $types = '';
        
        // Apply filters
        if (!empty($data['species']) && $data['species'] !== 'all') {
            $whereConditions[] = "species = ?";
            $params[] = $data['species'];
            $types .= 's';
        }
        
        if (!empty($data['search'])) {
            $whereConditions[] = "name LIKE ?";
            $params[] = '%' . $data['search'] . '%';
            $types .= 's';
        }
        
        $where = !empty($whereConditions) ? 'WHERE ' . implode(' AND ', $whereConditions) : '';
        
        // Get total count
        $countSql = "SELECT COUNT(*) as total FROM pets $where";
        $countStmt = $conn->prepare($countSql);
        if (!empty($params)) {
            $countStmt->bind_param($types, ...$params);
        }
        $countStmt->execute();
        $total = $countStmt->get_result()->fetch_assoc()['total'];
        $countStmt->close();
        
        // Get paginated results
        $sql = "SELECT id, name, species, breed, age, price, gender, pet_image, description 
                FROM pets $where ORDER BY id DESC LIMIT ? OFFSET ?";
        
        $stmt = $conn->prepare($sql);
        if (!empty($params)) {
            $allParams = array_merge($params, [$per_page, $offset]);
            $allTypes = $types . 'ii';
            $stmt->bind_param($allTypes, ...$allParams);
        } else {
            $stmt->bind_param('ii', $per_page, $offset);
        }
        $stmt->execute();
        $result = $stmt->get_result();
        
        // Generate HTML
        ob_start();
        if ($result->num_rows > 0) {
            while ($pet = $result->fetch_assoc()) {
                include __DIR__ . '/../../backend/includes/pet_card.php';
            }
        }
        $html = ob_get_clean();
        
    } else {
        // ===== PRODUCTS LOAD MORE =====
        $whereConditions = [];
        $params = [];
        $types = '';

        // Search
        if (!empty($data['search'])) {
            $whereConditions[] = "(product_name LIKE ? OR description LIKE ?)";
            $search_term = "%" . $data['search'] . "%";
            $params[] = $search_term;
            $params[] = $search_term;
            $types .= 'ss';
        }

        // Category filter
        if (!empty($data['category']) && $data['category'] !== 'all') {
            $whereConditions[] = "category = ?";
            $params[] = $data['category'];
            $types .= 's';
        }

        // Featured filter
        if (!empty($data['featured'])) {
            $whereConditions[] = "featured = 1";
        }

        // On Sale filter
        if (!empty($data['on_sale'])) {
            $whereConditions[] = "on_sale = 1";
        }

        // New Arrivals filter (products from last 30 days)
        if (!empty($data['new_arrivals'])) {
            $whereConditions[] = "created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)";
        }

        // Price range
        if (!empty($data['min_price'])) {
            $whereConditions[] = "price >= ?";
            $params[] = (float)$data['min_price'];
            $types .= 'd';
        }

        if (!empty($data['max_price'])) {
            $whereConditions[] = "price <= ?";
            $params[] = (float)$data['max_price'];
            $types .= 'd';
        }

        // In stock only
        if (!empty($data['in_stock'])) {
            $whereConditions[] = "quantity_in_stock > 0";
        }

        $where = !empty($whereConditions) ? 'WHERE ' . implode(' AND ', $whereConditions) : '';

        // Sorting
        $sort = 'id DESC';
        if (!empty($data['sort'])) {
            switch ($data['sort']) {
                case 'name_asc': $sort = 'product_name ASC'; break;
                case 'name_desc': $sort = 'product_name DESC'; break;
                case 'price_asc': $sort = 'price ASC'; break;
                case 'price_desc': $sort = 'price DESC'; break;
            }
        }

        // Get total count
        $countSql = "SELECT COUNT(*) as total FROM products $where";
        $countStmt = $conn->prepare($countSql);
        if (!empty($params)) {
            $countStmt->bind_param($types, ...$params);
        }
        $countStmt->execute();
        $total = $countStmt->get_result()->fetch_assoc()['total'];
        $countStmt->close();

        // Get paginated results
        $sql = "SELECT * FROM products $where ORDER BY $sort LIMIT ? OFFSET ?";
        
        $stmt = $conn->prepare($sql);
        if (!empty($params)) {
            $allParams = array_merge($params, [$per_page, $offset]);
            $allTypes = $types . 'ii';
            $stmt->bind_param($allTypes, ...$allParams);
        } else {
            $stmt->bind_param('ii', $per_page, $offset);
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
    }
    
    $hasMore = ($page * $per_page) < $total;
    
    echo json_encode([
        'success' => true,
        'html' => $html ?? '',
        'hasMore' => $hasMore,
        'nextPage' => $hasMore ? $page + 1 : null,
        'total' => $total,
        'loaded' => $result->num_rows,
        'page' => $page
    ]);

} catch (Exception $e) {
    error_log("Error in load_more.php: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}

$conn->close();
?>