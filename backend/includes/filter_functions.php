<?php
function buildProductFilterQuery($filters) {
    $where = [];
    $params = [];
    $types = '';

    if (!empty($filters['category'])) {
        $where[] = "category = ?";
        $params[] = $filters['category'];
        $types .= 's';
    }

    if (!empty($filters['min_price'])) {
        $where[] = "price >= ?";
        $params[] = $filters['min_price'];
        $types .= 'd';
    }

    if (!empty($filters['max_price'])) {
        $where[] = "price <= ?";
        $params[] = $filters['max_price'];
        $types .= 'd';
    }

    if (!empty($filters['in_stock'])) {
        $where[] = "quantity_in_stock > 0";
    }

    if (!empty($filters['brand'])) {
        $where[] = "brand = ?";
        $params[] = $filters['brand'];
        $types .= 's';
    }

    if (!empty($filters['search'])) {
        $where[] = "(product_name LIKE ? OR description LIKE ?)";
        $search_term = "%" . $filters['search'] . "%";
        $params[] = $search_term;
        $params[] = $search_term;
        $types .= 'ss';
    }

    $where_clause = !empty($where) ? "WHERE " . implode(" AND ", $where) : "";

    return [
        'where' => $where_clause,
        'params' => $params,
        'types' => $types
    ];
}

function buildPetFilterQuery($filters) {
    $where = [];
    $params = [];
    $types = '';

    if (!empty($filters['species'])) {
        $where[] = "species = ?";
        $params[] = $filters['species'];
        $types .= 's';
    }

    if (!empty($filters['breed'])) {
        $where[] = "breed LIKE ?";
        $params[] = "%{$filters['breed']}%";
        $types .= 's';
    }

    if (!empty($filters['min_age'])) {
        $where[] = "age >= ?";
        $params[] = $filters['min_age'];
        $types .= 'i';
    }

    if (!empty($filters['max_age'])) {
        $where[] = "age <= ?";
        $params[] = $filters['max_age'];
        $types .= 'i';
    }

    if (!empty($filters['gender'])) {
        $where[] = "gender = ?";
        $params[] = $filters['gender'];
        $types .= 's';
    }

    if (!empty($filters['color'])) {
        $where[] = "color LIKE ?";
        $params[] = "%{$filters['color']}%";
        $types .= 's';
    }

    if (!empty($filters['min_price'])) {
        $where[] = "price >= ?";
        $params[] = $filters['min_price'];
        $types .= 'd';
    }

    if (!empty($filters['max_price'])) {
        $where[] = "price <= ?";
        $params[] = $filters['max_price'];
        $types .= 'd';
    }

    $where_clause = !empty($where) ? "WHERE " . implode(" AND ", $where) : "";

    return [
        'where' => $where_clause,
        'params' => $params,
        'types' => $types
    ];
}

function getSortOrder($sort) {
    $sort_options = [
        'relevance' => 'product_name ASC',
        'price_low' => 'price ASC',
        'price_high' => 'price DESC',
        'name' => 'product_name ASC',
        'newest' => 'id DESC'
    ];
    return $sort_options[$sort] ?? 'product_name ASC';
}
?>