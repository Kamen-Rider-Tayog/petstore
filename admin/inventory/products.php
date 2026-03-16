<?php
require_once '../includes/header.php';
require_once '../includes/sidebar.php';

// Handle search and pagination
$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$category = isset($_GET['category']) ? trim($_GET['category']) : 'all';
$supplier = isset($_GET['supplier']) ? trim($_GET['supplier']) : 'all';
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
?>

<main class="admin-main">
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 2rem;">
        <h2>Manage Products</h2>
        <a href="product_add.php" class="btn btn-success">Add New Product</a>
    </div>

    <!-- Filters -->
    <div style="background: #fff; padding: 1.5rem; border-radius: 8px; margin-bottom: 2rem; box-shadow: 0 2px 4px rgba(0,0,0,0.1);">
        <form method="get" style="display: flex; gap: 1rem; flex-wrap: wrap; align-items: center;">
            <div class="form-group" style="margin: 0;">
                <label for="search" style="display: block; margin-bottom: 0.5rem;">Search:</label>
                <input type="text" id="search" name="search" value="<?php echo htmlspecialchars($search); ?>" placeholder="Product name">
            </div>
            <div class="form-group" style="margin: 0;">
                <label for="category" style="display: block; margin-bottom: 0.5rem;">Category:</label>
                <select name="category" id="category">
                    <option value="all" <?php echo $category === 'all' ? 'selected' : ''; ?>>All Categories</option>
                    <?php while ($cat = $categories->fetch_assoc()): ?>
                        <option value="<?php echo htmlspecialchars($cat['category']); ?>" <?php echo $category === $cat['category'] ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars(ucfirst($cat['category'])); ?>
                        </option>
                    <?php endwhile; ?>
                </select>
            </div>
            <div class="form-group" style="margin: 0;">
                <label for="supplier" style="display: block; margin-bottom: 0.5rem;">Supplier:</label>
                <select name="supplier" id="supplier">
                    <option value="all" <?php echo $supplier === 'all' ? 'selected' : ''; ?>>All Suppliers</option>
                    <?php while ($sup = $suppliers->fetch_assoc()): ?>
                        <option value="<?php echo $sup['id']; ?>" <?php echo $supplier == $sup['id'] ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($sup['supplier_name']); ?>
                        </option>
                    <?php endwhile; ?>
                </select>
            </div>
            <div style="align-self: flex-end;">
                <button type="submit" class="btn">Filter</button>
                <?php if ($search || $category !== 'all' || $supplier !== 'all'): ?>
                    <a href="products.php" class="btn btn-warning" style="margin-left: 0.5rem;">Clear</a>
                <?php endif; ?>
            </div>
        </form>
    </div>

    <!-- Products Table -->
    <div style="background: #fff; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); overflow: hidden;">
        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Image</th>
                    <th>Product Name</th>
                    <th>Category</th>
                    <th>Price</th>
                    <th>Stock</th>
                    <th>Supplier</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($products->num_rows > 0): ?>
                    <?php while ($product = $products->fetch_assoc()): ?>
                        <tr class="<?php echo $product['quantity_in_stock'] < 10 ? 'low-stock-row' : ''; ?>">
                            <td><?php echo $product['id']; ?></td>
                            <td>
                                <?php if (!empty($product['image'])): ?>
                                    <img src="../assets/images/<?php echo htmlspecialchars($product['image']); ?>" width="50" height="50" style="object-fit: cover; border-radius: 4px;">
                                <?php else: ?>
                                    No image
                                <?php endif; ?>
                            </td>
                            <td><?php echo htmlspecialchars($product['product_name']); ?></td>
                            <td><?php echo htmlspecialchars(ucfirst($product['category'])); ?></td>
                            <td>₱<?php echo number_format($product['price'], 2); ?></td>
                            <td>
                                <span class="<?php echo $product['quantity_in_stock'] < 5 ? 'low-stock' : ($product['quantity_in_stock'] < 10 ? 'medium-stock' : 'good-stock'); ?>">
                                    <?php echo $product['quantity_in_stock']; ?>
                                </span>
                            </td>
                            <td><?php echo htmlspecialchars($product['supplier_name'] ?? 'N/A'); ?></td>
                            <td>
                                <a href="product_edit.php?id=<?php echo $product['id']; ?>" class="btn btn-small btn-warning">Edit</a>
                                <a href="product_delete.php?id=<?php echo $product['id']; ?>" class="btn btn-small btn-danger" onclick="return confirm('Are you sure you want to delete this product?')">Delete</a>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="8" style="text-align: center; padding: 2rem;">No products found.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

    <!-- Pagination -->
    <?php if ($totalPages > 1): ?>
        <div style="margin-top: 2rem; text-align: center;">
            <?php
            $queryString = http_build_query(array_filter([
                'search' => $search,
                'category' => $category !== 'all' ? $category : null,
                'supplier' => $supplier !== 'all' ? $supplier : null,
                'page' => null
            ]));

            for ($i = 1; $i <= $totalPages; $i++):
                $active = $i === $page ? ' style="font-weight: bold; color: #007bff;"' : '';
                $url = "products.php?" . $queryString . "&page=$i";
            ?>
                <a href="<?php echo $url; ?>"<?php echo $active; ?> style="margin: 0 0.25rem;"><?php echo $i; ?></a>
            <?php endfor; ?>
        </div>
    <?php endif; ?>
</main>

<link rel="stylesheet" href="../assets/css/admin_products.css">

<?php require_once '../includes/footer.php'; ?>