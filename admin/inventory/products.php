<?php
session_name('petstore_session');
session_start();

// Check if user is logged in as admin
if (!isset($_SESSION['is_admin']) || $_SESSION['is_admin'] !== true) {
    header('Location: ' . url('login?error=Access denied'));
    exit;
}

require_once __DIR__ . '/../../backend/config/database.php';
require_once __DIR__ . '/../../backend/functions/helpers.php';

$page_title = 'Products';
require_once __DIR__ . '/../includes/header.php';

echo '<link rel="stylesheet" href="/Ria-Pet-Store/admin/css/products.css?v=' . time() . '">';

// Handle search and pagination
$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$category = isset($_GET['category']) ? trim($_GET['category']) : 'all';
$supplier = isset($_GET['supplier']) ? trim($_GET['supplier']) : 'all';
$stock_filter = isset($_GET['stock_filter']) ? trim($_GET['stock_filter']) : 'all';
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$limit = 20;
$offset = ($page - 1) * $limit;

// Build query
$query = "SELECT p.*, s.supplier_name FROM products p LEFT JOIN suppliers s ON p.supplier_id = s.id WHERE 1=1";
$params = [];
$types = '';

if (!empty($search)) {
    $query .= " AND p.product_name LIKE ?";
    $params[] = "%$search%";
    $types .= 's';
}

if (!empty($category) && $category !== 'all') {
    $query .= " AND p.category = ?";
    $params[] = $category;
    $types .= 's';
}

if (!empty($supplier) && $supplier !== 'all') {
    $query .= " AND p.supplier_id = ?";
    $params[] = $supplier;
    $types .= 'i';
}

// Stock filter
if (!empty($stock_filter) && $stock_filter !== 'all') {
    if ($stock_filter === 'in_stock') {
        $query .= " AND p.quantity_in_stock > 10";
    } elseif ($stock_filter === 'low_stock') {
        $query .= " AND p.quantity_in_stock BETWEEN 1 AND 10";
    } elseif ($stock_filter === 'critical') {
        $query .= " AND p.quantity_in_stock = 0";
    } elseif ($stock_filter === 'on_sale') {
        $query .= " AND p.sale_price IS NOT NULL AND p.sale_price > 0";
    } elseif ($stock_filter === 'featured') {
        $query .= " AND p.featured = 1";
    }
}

$query .= " ORDER BY p.product_name LIMIT ? OFFSET ?";
$params[] = $limit;
$params[] = $offset;
$types .= 'ii';

// Get products
$stmt = $conn->prepare($query);
if (!empty($params)) {
    $stmt->bind_param($types, ...$params);
}
$stmt->execute();
$products = $stmt->get_result();

// Get total count for pagination
$countQuery = "SELECT COUNT(*) as total FROM products p WHERE 1=1";
$countParams = [];
$countTypes = '';

if (!empty($search)) {
    $countQuery .= " AND p.product_name LIKE ?";
    $countParams[] = "%$search%";
    $countTypes .= 's';
}

if (!empty($category) && $category !== 'all') {
    $countQuery .= " AND p.category = ?";
    $countParams[] = $category;
    $countTypes .= 's';
}

if (!empty($supplier) && $supplier !== 'all') {
    $countQuery .= " AND p.supplier_id = ?";
    $countParams[] = $supplier;
    $countTypes .= 'i';
}

if (!empty($stock_filter) && $stock_filter !== 'all') {
    if ($stock_filter === 'in_stock') {
        $countQuery .= " AND p.quantity_in_stock > 10";
    } elseif ($stock_filter === 'low_stock') {
        $countQuery .= " AND p.quantity_in_stock BETWEEN 1 AND 10";
    } elseif ($stock_filter === 'critical') {
        $countQuery .= " AND p.quantity_in_stock = 0";
    } elseif ($stock_filter === 'on_sale') {
        $countQuery .= " AND p.sale_price IS NOT NULL AND p.sale_price > 0";
    } elseif ($stock_filter === 'featured') {
        $countQuery .= " AND p.featured = 1";
    }
}

$countStmt = $conn->prepare($countQuery);
if (!empty($countParams)) {
    $countStmt->bind_param($countTypes, ...$countParams);
}
$countStmt->execute();
$totalProducts = $countStmt->get_result()->fetch_assoc()['total'];
$totalPages = ceil($totalProducts / $limit);

// Get unique categories and suppliers for filters
$categories = $conn->query("SELECT DISTINCT category FROM products ORDER BY category");
$suppliers = $conn->query("SELECT id, supplier_name FROM suppliers ORDER BY supplier_name");

// Get counts for each filter
$totalCount = $conn->query("SELECT COUNT(*) as count FROM products")->fetch_assoc()['count'];
$inStockCount = $conn->query("SELECT COUNT(*) as count FROM products WHERE quantity_in_stock > 10")->fetch_assoc()['count'];
$lowStockCount = $conn->query("SELECT COUNT(*) as count FROM products WHERE quantity_in_stock BETWEEN 1 AND 10")->fetch_assoc()['count'];
$criticalCount = $conn->query("SELECT COUNT(*) as count FROM products WHERE quantity_in_stock = 0")->fetch_assoc()['count'];
$onSaleCount = $conn->query("SELECT COUNT(*) as count FROM products WHERE sale_price IS NOT NULL AND sale_price > 0")->fetch_assoc()['count'];
$featuredCount = $conn->query("SELECT COUNT(*) as count FROM products WHERE featured = 1")->fetch_assoc()['count'];
?>

<div class="admin-dashboard">
    <!-- Search Bar with Add Button -->
    <div class="search-bar">
        <form method="get" action="">
            <select name="category">
                <option value="all" <?php echo $category === 'all' ? 'selected' : ''; ?>>All Categories</option>
                <?php while ($cat = $categories->fetch_assoc()): ?>
                    <option value="<?php echo htmlspecialchars($cat['category']); ?>" <?php echo $category === $cat['category'] ? 'selected' : ''; ?>>
                        <?php echo htmlspecialchars(ucfirst($cat['category'])); ?>
                    </option>
                <?php endwhile; ?>
            </select>
            <select name="supplier">
                <option value="all" <?php echo $supplier === 'all' ? 'selected' : ''; ?>>All Suppliers</option>
                <?php while ($sup = $suppliers->fetch_assoc()): ?>
                    <option value="<?php echo $sup['id']; ?>" <?php echo $supplier == $sup['id'] ? 'selected' : ''; ?>>
                        <?php echo htmlspecialchars($sup['supplier_name']); ?>
                    </option>
                <?php endwhile; ?>
            </select>
            <button type="submit" class="btn btn-primary"><?php echo icon('search', 16); ?> Filter</button>
            <?php if ($search || $category !== 'all' || $supplier !== 'all'): ?>
                <a href="?stock_filter=<?php echo $stock_filter; ?>" class="btn btn-outline"><?php echo icon('x', 16); ?> Clear</a>
            <?php endif; ?>
        </form>
        <a href="product_add.php" class="btn btn-success"><?php echo icon('plus', 16); ?> Add New Product</a>
    </div>

    <!-- Filter Tabs with Counts -->
    <div class="filter-tabs">
        <a href="?stock_filter=all" class="filter-tab <?php echo $stock_filter === 'all' ? 'active' : ''; ?>">
            <span class="filter-dot all"></span>
            All Products
            <span class="filter-count"><?php echo $totalCount; ?></span>
        </a>
        <a href="?stock_filter=in_stock" class="filter-tab <?php echo $stock_filter === 'in_stock' ? 'active' : ''; ?>">
            <span class="filter-dot in-stock"></span>
            In Stock
            <span class="filter-count"><?php echo $inStockCount; ?></span>
        </a>
        <a href="?stock_filter=low_stock" class="filter-tab <?php echo $stock_filter === 'low_stock' ? 'active' : ''; ?>">
            <span class="filter-dot low-stock"></span>
            Low Stock
            <span class="filter-count"><?php echo $lowStockCount; ?></span>
        </a>
        <a href="?stock_filter=critical" class="filter-tab <?php echo $stock_filter === 'critical' ? 'active' : ''; ?>">
            <span class="filter-dot critical"></span>
            Critical
            <span class="filter-count"><?php echo $criticalCount; ?></span>
        </a>
        <a href="?stock_filter=on_sale" class="filter-tab <?php echo $stock_filter === 'on_sale' ? 'active' : ''; ?>">
            <span class="filter-dot on-sale"></span>
            On Sale
            <span class="filter-count"><?php echo $onSaleCount; ?></span>
        </a>
        <a href="?stock_filter=featured" class="filter-tab <?php echo $stock_filter === 'featured' ? 'active' : ''; ?>">
            <span class="filter-dot featured"></span>
            Featured
            <span class="filter-count"><?php echo $featuredCount; ?></span>
        </a>
    </div>

    <!-- Legend -->
    <div class="status-legend">
        <span class="legend-item"><span class="status-dot in-stock"></span> In Stock (>10)</span>
        <span class="legend-item"><span class="status-dot low-stock"></span> Low Stock (1-10)</span>
        <span class="legend-item"><span class="status-dot critical"></span> Critical (0)</span>
        <span class="legend-item"><span class="status-dot on-sale"></span> On Sale</span>
        <span class="legend-item"><span class="status-dot featured"></span> Featured</span>
    </div>

    <!-- Products Table -->
    <div class="table-container">
        <?php if ($products->num_rows > 0): ?>
        <table class="admin-table">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Image</th>
                    <th>Product Name</th>
                    <th>Category</th>
                    <th>Price</th>
                    <th>Stock</th>
                    <th>Status</th>
                    <th>Supplier</th>
                </tr>
            </thead>
            <tbody>
                
                    <?php while ($product = $products->fetch_assoc()): 
                        // Determine stock status
                        if ($product['quantity_in_stock'] == 0) {
                            $stockStatus = 'critical';
                            $stockTooltip = 'Out of Stock';
                        } elseif ($product['quantity_in_stock'] <= 10) {
                            $stockStatus = 'low-stock';
                            $stockTooltip = 'Low Stock: ' . $product['quantity_in_stock'] . ' units left';
                        } else {
                            $stockStatus = 'in-stock';
                            $stockTooltip = 'In Stock: ' . $product['quantity_in_stock'] . ' units';
                        }
                        
                        // Check if on sale
                        $isOnSale = !empty($product['sale_price']) && $product['sale_price'] > 0;
                        // Check if featured
                        $isFeatured = !empty($product['featured']) && $product['featured'] == 1;
                    ?>
                        <tr>
                            <td><?php echo $product['id']; ?></td>
                            <td>
                                <?php if (!empty($product['image'])): ?>
                                    <img src="../assets/images/<?php echo htmlspecialchars($product['image']); ?>" class="product-photo">
                                <?php else: ?>
                                    <div class="no-photo">No image</div>
                                <?php endif; ?>
                            </td>
                            <td>
                                <a href="product_details.php?id=<?php echo $product['id']; ?>" class="product-link">
                                    <?php echo htmlspecialchars($product['product_name']); ?>
                                </a>
                            </td>
                            <td><?php echo htmlspecialchars(ucfirst($product['category'])); ?></td>
                            <td class="product-price">
                                <?php if ($isOnSale): ?>
                                    <span class="original-price">₱<?php echo number_format($product['price'], 2); ?></span>
                                    <span class="sale-price">₱<?php echo number_format($product['sale_price'], 2); ?></span>
                                <?php else: ?>
                                    ₱<?php echo number_format($product['price'], 2); ?>
                                <?php endif; ?>
                            </td>
                            <td><?php echo $product['quantity_in_stock']; ?></td>
                            <td>
                                <div class="status-indicators">
                                    <span class="status-dot <?php echo $stockStatus; ?>" title="<?php echo $stockTooltip; ?>"></span>
                                    <?php if ($isOnSale): ?>
                                        <span class="status-dot on-sale" title="On Sale"></span>
                                    <?php endif; ?>
                                    <?php if ($isFeatured): ?>
                                        <span class="status-dot featured" title="Featured Product"></span>
                                    <?php endif; ?>
                                </div>
                            </td>
                            <td><?php echo htmlspecialchars($product['supplier_name'] ?? 'N/A'); ?></td>
                    <?php endwhile; ?>
                <?php else: ?>
                    <div class="no-data">
                        <p>No products found. <?php echo icon('package', 20); ?></p>
                    </div>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

    <!-- Pagination -->
    <?php if ($totalPages > 1): ?>
        <div class="pagination">
            <?php
            $queryParams = array_filter([
                'search' => $search,
                'category' => $category !== 'all' ? $category : null,
                'supplier' => $supplier !== 'all' ? $supplier : null,
                'stock_filter' => $stock_filter !== 'all' ? $stock_filter : null,
                'page' => null
            ]);
            $queryString = http_build_query($queryParams);
            
            if ($page > 1) {
                echo '<a href="?' . $queryString . '&page=' . ($page - 1) . '" class="pagination-link">&laquo; Prev</a>';
            }
            
            $startPage = max(1, $page - 2);
            $endPage = min($totalPages, $page + 2);
            
            if ($startPage > 1) {
                echo '<a href="?' . $queryString . '&page=1" class="pagination-link">1</a>';
                if ($startPage > 2) {
                    echo '<span class="pagination-dots">...</span>';
                }
            }
            
            for ($i = $startPage; $i <= $endPage; $i++):
                $activeClass = $i === $page ? 'active' : '';
            ?>
                <a href="?<?php echo $queryString; ?>&page=<?php echo $i; ?>" class="pagination-link <?php echo $activeClass; ?>"><?php echo $i; ?></a>
            <?php endfor;
            
            if ($endPage < $totalPages) {
                if ($endPage < $totalPages - 1) {
                    echo '<span class="pagination-dots">...</span>';
                }
                echo '<a href="?' . $queryString . '&page=' . $totalPages . '" class="pagination-link">' . $totalPages . '</a>';
            }
            
            if ($page < $totalPages) {
                echo '<a href="?' . $queryString . '&page=' . ($page + 1) . '" class="pagination-link">Next &raquo;</a>';
            }
            ?>
        </div>
    <?php endif; ?>
</div>

<?php require_once '../includes/footer.php'; ?>