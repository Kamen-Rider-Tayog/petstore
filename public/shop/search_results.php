<?php
require_once '../../backend/includes/header.php';
require_once '../../backend/includes/search_functions.php';

$type = $_GET['type'] ?? 'products';
$query = trim($_GET['q'] ?? '');
$category = $_GET['category'] ?? '';
$min_price = $_GET['min_price'] ?? '';
$max_price = $_GET['max_price'] ?? '';
$in_stock = isset($_GET['in_stock']);
$sort = $_GET['sort'] ?? 'relevance';

// For pets
$species = $_GET['species'] ?? '';
$breed = $_GET['breed'] ?? '';
$min_age = $_GET['min_age'] ?? '';
$max_age = $_GET['max_age'] ?? '';
$gender = $_GET['gender'] ?? '';
$color = $_GET['color'] ?? '';

$results = [];
$results_count = 0;

if ($type === 'products') {
    // Build product search query
    $where = [];
    $params = [];
    $types = '';

    if (!empty($query)) {
        $where[] = "(product_name LIKE ? OR description LIKE ?)";
        $search_term = "%$query%";
        $params[] = $search_term;
        $params[] = $search_term;
        $types .= 'ss';
    }

    if (!empty($category)) {
        $where[] = "category = ?";
        $params[] = $category;
        $types .= 's';
    }

    if (!empty($_GET['brand'])) {
        $where[] = "brand LIKE ?";
        $params[] = "%{$_GET['brand']}%";
        $types .= 's';
    }

    if (!empty($min_price)) {
        $where[] = "price >= ?";
        $params[] = $min_price;
        $types .= 'd';
    }

    if (!empty($max_price)) {
        $where[] = "price <= ?";
        $params[] = $max_price;
        $types .= 'd';
    }

    if ($in_stock) {
        $where[] = "quantity_in_stock > 0";
    }

    $where_clause = !empty($where) ? 'WHERE ' . implode(' AND ', $where) : '';

    // Sort order
    $sort_options = [
        'relevance' => 'product_name ASC',
        'price_low' => 'price ASC',
        'price_high' => 'price DESC',
        'name' => 'product_name ASC'
    ];
    $order_by = $sort_options[$sort] ?? 'product_name ASC';

    $sql = "SELECT * FROM products $where_clause ORDER BY $order_by";

    $stmt = $conn->prepare($sql);
    if (!empty($params)) {
        $stmt->bind_param($types, ...$params);
    }
    $stmt->execute();
    $results = $stmt->get_result();
    $results_count = $results->num_rows;

    // Log search
    logSearch($query, $results_count);

} elseif ($type === 'pets') {
    // Build pet search query
    $where = [];
    $params = [];
    $types = '';

    if (!empty($species)) {
        $where[] = "species = ?";
        $params[] = $species;
        $types .= 's';
    }

    if (!empty($breed)) {
        $where[] = "breed LIKE ?";
        $params[] = "%$breed%";
        $types .= 's';
    }

    if (!empty($min_age)) {
        $where[] = "age >= ?";
        $params[] = $min_age;
        $types .= 'i';
    }

    if (!empty($max_age)) {
        $where[] = "age <= ?";
        $params[] = $max_age;
        $types .= 'i';
    }

    if (!empty($gender)) {
        $where[] = "gender = ?";
        $params[] = $gender;
        $types .= 's';
    }

    if (!empty($color)) {
        $where[] = "color LIKE ?";
        $params[] = "%$color%";
        $types .= 's';
    }

    if (!empty($min_price)) {
        $where[] = "price >= ?";
        $params[] = $min_price;
        $types .= 'd';
    }

    if (!empty($max_price)) {
        $where[] = "price <= ?";
        $params[] = $max_price;
        $types .= 'd';
    }

    $where_clause = !empty($where) ? 'WHERE ' . implode(' AND ', $where) : '';

    $sql = "SELECT * FROM pets $where_clause ORDER BY name";

    $stmt = $conn->prepare($sql);
    if (!empty($params)) {
        $stmt->bind_param($types, ...$params);
    }
    $stmt->execute();
    $results = $stmt->get_result();
    $results_count = $results->num_rows;
}
?>

<main>
    <h1>Search Results</h1>

    <div class="search-summary">
        <p><?php echo $results_count; ?> results found for your search</p>
        <a href="advanced_search.php" class="btn">Modify Search</a>
    </div>

    <div class="search-layout">
        <!-- Filters Sidebar -->
        <aside class="filters-sidebar">
            <h3>Filters</h3>

            <?php if ($type === 'products'): ?>
                <div class="filter-group">
                    <h4>Categories</h4>
                    <?php
                    $cat_sql = "SELECT category, COUNT(*) as count FROM products GROUP BY category ORDER BY category";
                    $cat_results = $conn->query($cat_sql);
                    while ($cat = $cat_results->fetch_assoc()):
                    ?>
                        <label>
                            <input type="checkbox" name="category_filter" value="<?php echo htmlspecialchars($cat['category']); ?>">
                            <?php echo htmlspecialchars($cat['category']); ?> (<?php echo $cat['count']; ?>)
                        </label>
                    <?php endwhile; ?>
                </div>

                <div class="filter-group">
                    <h4>Price Range</h4>
                    <input type="number" id="min-price" placeholder="Min price" step="0.01">
                    <input type="number" id="max-price" placeholder="Max price" step="0.01">
                    <button id="apply-price">Apply</button>
                </div>

                <div class="filter-group">
                    <h4>Brands</h4>
                    <?php
                    $brand_sql = "SELECT brand, COUNT(*) as count FROM products WHERE brand IS NOT NULL AND brand != '' GROUP BY brand ORDER BY count DESC LIMIT 10";
                    $brand_results = $conn->query($brand_sql);
                    while ($brand = $brand_results->fetch_assoc()):
                    ?>
                        <label>
                            <input type="checkbox" name="brand_filter" value="<?php echo htmlspecialchars($brand['brand']); ?>">
                            <?php echo htmlspecialchars($brand['brand']); ?> (<?php echo $brand['count']; ?>)
                        </label>
                    <?php endwhile; ?>
                </div>

                <div class="filter-group">
                    <label><input type="checkbox" id="in-stock" <?php echo $in_stock ? 'checked' : ''; ?>> In Stock Only</label>
                </div>
            <?php endif; ?>
        </aside>

        <!-- Results Area -->
        <div class="results-area">
            <div class="sort-options">
                <label>Sort by:</label>
                <select id="sort-select">
                    <option value="relevance" <?php echo $sort === 'relevance' ? 'selected' : ''; ?>>Relevance</option>
                    <option value="price_low" <?php echo $sort === 'price_low' ? 'selected' : ''; ?>>Price: Low to High</option>
                    <option value="price_high" <?php echo $sort === 'price_high' ? 'selected' : ''; ?>>Price: High to Low</option>
                    <option value="name" <?php echo $sort === 'name' ? 'selected' : ''; ?>>Name</option>
                </select>
            </div>

            <div class="products-grid">
                <?php if ($results_count === 0): ?>
                    <div class="no-results">
                        <h3>No products found</h3>
                        <p>Try adjusting your search criteria or browse our categories:</p>
                        <a href="categories.php" class="btn">Browse Categories</a>
                    </div>
                <?php else: ?>
                    <?php while ($item = $results->fetch_assoc()): ?>
                        <div class="product-card">
                            <?php if ($type === 'products' && $item['featured']): ?>
                                <span class="badge featured">Featured</span>
                            <?php endif; ?>

                            <img src="../../assets/images/placeholder.jpg" alt="<?php echo htmlspecialchars($item[$type === 'products' ? 'product_name' : 'name']); ?>">

                            <h3><?php echo htmlspecialchars($item[$type === 'products' ? 'product_name' : 'name']); ?></h3>
                            <p class="price">$<?php echo number_format($item['price'], 2); ?></p>

                            <a href="<?php echo $type === 'products' ? 'product_details.php' : 'pet_details.php'; ?>?id=<?php echo $item['id']; ?>" class="btn">View Details</a>
                        </div>
                    <?php endwhile; ?>
                <?php endif; ?>
            </div>
        </div>
    </div>
</main>

<link rel="stylesheet" href="../../assets/css/search_results.css">

<script src="../../assets/js/filters.js"></script>

<?php require_once '../../backend/includes/footer.php'; ?>