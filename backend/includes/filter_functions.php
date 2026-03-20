<?php
/**
 * Build filter query for products
 */
function buildProductFilterQuery($filters) {
    $where = [];
    $params = [];
    $types = '';

    // Search filter
    if (!empty($filters['search'])) {
        $where[] = "(product_name LIKE ? OR description LIKE ?)";
        $search_term = "%" . $filters['search'] . "%";
        $params[] = $search_term;
        $params[] = $search_term;
        $types .= 'ss';
    }

    // Category filter (single from dropdown)
    if (!empty($filters['category']) && $filters['category'] !== 'all') {
        $where[] = "category = ?";
        $params[] = $filters['category'];
        $types .= 's';
    }

    // Category filter (multiple from checkboxes - for future use)
    if (!empty($filters['categories']) && is_array($filters['categories'])) {
        $placeholders = implode(',', array_fill(0, count($filters['categories']), '?'));
        $where[] = "category IN ($placeholders)";
        foreach ($filters['categories'] as $cat) {
            $params[] = $cat;
            $types .= 's';
        }
    }

    // Price range
    if (!empty($filters['min_price'])) {
        $where[] = "price >= ?";
        $params[] = (float)$filters['min_price'];
        $types .= 'd';
    }

    if (!empty($filters['max_price'])) {
        $where[] = "price <= ?";
        $params[] = (float)$filters['max_price'];
        $types .= 'd';
    }

    // In stock only
    if (!empty($filters['in_stock'])) {
        $where[] = "quantity_in_stock > 0";
    }

    // Brand filter
    if (!empty($filters['brand']) && is_array($filters['brand'])) {
        $placeholders = implode(',', array_fill(0, count($filters['brand']), '?'));
        $where[] = "brand IN ($placeholders)";
        foreach ($filters['brand'] as $brand) {
            $params[] = $brand;
            $types .= 's';
        }
    }

    // Featured products
    if (!empty($filters['featured'])) {
        $where[] = "featured = 1";
    }

    // On sale products
    if (!empty($filters['on_sale'])) {
        $where[] = "on_sale = 1";
    }

    // New arrivals (products from last X days)
    if (!empty($filters['new_arrivals'])) {
        $where[] = "created_at >= ?";
        $params[] = $filters['new_arrivals'];
        $types .= 's';
    }

    $where_clause = !empty($where) ? "WHERE " . implode(" AND ", $where) : "";

    return [
        'where' => $where_clause,
        'params' => $params,
        'types' => $types
    ];
}

/**
 * Build filter query for pets
 */
function buildPetFilterQuery($filters) {
    $where = ["pet_status = 'available'"];
    $params = [];
    $types = '';

    // Species filter
    if (!empty($filters['species']) && $filters['species'] !== 'all') {
        $where[] = "species = ?";
        $params[] = $filters['species'];
        $types .= 's';
    }

    // Search by name
    if (!empty($filters['search'])) {
        $where[] = "name LIKE ?";
        $params[] = '%' . $filters['search'] . '%';
        $types .= 's';
    }

    // Price range
    if (!empty($filters['min_price'])) {
        $where[] = "price >= ?";
        $params[] = (float)$filters['min_price'];
        $types .= 'd';
    }

    if (!empty($filters['max_price'])) {
        $where[] = "price <= ?";
        $params[] = (float)$filters['max_price'];
        $types .= 'd';
    }

    // Age range
    if (!empty($filters['min_age'])) {
        $where[] = "age >= ?";
        $params[] = (int)$filters['min_age'];
        $types .= 'i';
    }

    if (!empty($filters['max_age'])) {
        $where[] = "age <= ?";
        $params[] = (int)$filters['max_age'];
        $types .= 'i';
    }

    // Gender filter
    if (!empty($filters['gender']) && $filters['gender'] !== 'all') {
        $where[] = "gender = ?";
        $params[] = $filters['gender'];
        $types .= 's';
    }

    $where_clause = !empty($where) ? "WHERE " . implode(" AND ", $where) : "";

    return [
        'where' => $where_clause,
        'params' => $params,
        'types' => $types
    ];
}

/**
 * Get sort order for products
 */
function getSortOrder($sort) {
    $sort_options = [
        'relevance' => 'id DESC',
        'price_low' => 'price ASC',
        'price_high' => 'price DESC',
        'name_asc' => 'product_name ASC',
        'name_desc' => 'product_name DESC',
        'newest' => 'id DESC'
    ];
    return $sort_options[$sort] ?? 'id DESC';
}