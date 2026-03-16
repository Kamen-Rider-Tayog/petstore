<?php
require_once '../../backend/includes/header.php';

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header('Location: categories.php');
    exit;
}

$category_id = (int)$_GET['id'];
$sort = $_GET['sort'] ?? 'name';
$page = (int)($_GET['page'] ?? 1);
$per_page = 12;
$offset = ($page - 1) * $per_page;

// Get category info
$category = $conn->query("SELECT * FROM categories WHERE id = $category_id")->fetch_assoc();
if (!$category) {
    header('Location: categories.php');
    exit;
}

// Build sort order
$sort_options = [
    'name' => 'product_name ASC',
    'price_low' => 'price ASC',
    'price_high' => 'price DESC',
    'newest' => 'id DESC'
];
$order_by = $sort_options[$sort] ?? 'product_name ASC';

// Get products
$sql = "SELECT * FROM products WHERE category = ? ORDER BY $order_by LIMIT ? OFFSET ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param('sii', $category['category_name'], $per_page, $offset);
$stmt->execute();
$products = $stmt->get_result();

// Get total count
$total_sql = "SELECT COUNT(*) as total FROM products WHERE category = ?";
$total_stmt = $conn->prepare($total_sql);
$total_stmt->bind_param('s', $category['category_name']);
$total_stmt->execute();
$total = $total_stmt->get_result()->fetch_assoc()['total'];
$total_pages = ceil($total / $per_page);
?>

<main>
    <div class="breadcrumb">
        <a href="index.php">Home</a> > <a href="categories.php">Categories</a> > <?= htmlspecialchars($category['category_name']) ?>
    </div>

    <h1>Products in <?= htmlspecialchars($category['category_name']) ?></h1>

    <div class="filters">
        <form method="get">
            <input type="hidden" name="id" value="<?= $category_id ?>">
            <label>Sort by:</label>
            <select name="sort" onchange="this.form.submit()">
                <option value="name" <?= $sort === 'name' ? 'selected' : '' ?>>Name</option>
                <option value="price_low" <?= $sort === 'price_low' ? 'selected' : '' ?>>Price: Low to High</option>
                <option value="price_high" <?= $sort === 'price_high' ? 'selected' : '' ?>>Price: High to Low</option>
                <option value="newest" <?= $sort === 'newest' ? 'selected' : '' ?>>Newest</option>
            </select>
        </form>
    </div>

    <div class="products-grid">
        <?php if ($products->num_rows === 0): ?>
            <p>No products found in this category.</p>
        <?php else: ?>
            <?php while ($product = $products->fetch_assoc()): ?>
                <div class="product-card">
                    <?php if ($product['featured']): ?>
                        <span class="badge featured">Featured</span>
                    <?php endif; ?>

                    <img src="../../assets/images/placeholder.jpg" alt="<?= htmlspecialchars($product['product_name']) ?>">

                    <h3><?= htmlspecialchars($product['product_name']) ?></h3>
                    <p class="price">$<?= number_format($product['price'], 2) ?></p>

                    <a href="product_details.php?id=<?= $product['id'] ?>" class="btn">View Details</a>
                </div>
            <?php endwhile; ?>
        <?php endif; ?>
    </div>

    <?php if ($total_pages > 1): ?>
        <div class="pagination">
            <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                <a href="?id=<?= $category_id ?>&sort=<?= $sort ?>&page=<?= $i ?>" class="<?= $i === $page ? 'active' : '' ?>"><?= $i ?></a>
            <?php endfor; ?>
        </div>
    <?php endif; ?>
</main>

<link rel="stylesheet" href="../../assets/css/category_products.css">

<?php require_once '../../backend/includes/footer.php'; ?>