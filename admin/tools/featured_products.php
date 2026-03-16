<?php
require_once '../includes/header.php';
require_once '../includes/sidebar.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update'])) {
    foreach ($_POST['featured'] as $product_id => $value) {
        $featured = isset($value) ? 1 : 0;
        $conn->query("UPDATE products SET featured = $featured WHERE id = $product_id");
    }
    $message = "Featured products updated successfully!";
}

$products = $conn->query("SELECT id, product_name, category, featured FROM products ORDER BY product_name");
?>

<main class="admin-main">
    <h2>Manage Featured Products</h2>

    <?php if (isset($message)): ?>
        <div class="alert alert-success"><?php echo $message; ?></div>
    <?php endif; ?>

    <form method="post">
        <div class="table-responsive">
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Product Name</th>
                        <th>Category</th>
                        <th>Featured</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($product = $products->fetch_assoc()): ?>
                        <tr>
                            <td><?php echo $product['id']; ?></td>
                            <td><?php echo htmlspecialchars($product['product_name']); ?></td>
                            <td><?php echo htmlspecialchars($product['category']); ?></td>
                            <td>
                                <input type="checkbox" name="featured[<?php echo $product['id']; ?>]" value="1"
                                       <?php echo $product['featured'] ? 'checked' : ''; ?>>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>

        <div style="margin-top: 20px;">
            <button type="submit" name="update" class="btn btn-primary">Update Featured Products</button>
        </div>
    </form>
</main>

<link rel="stylesheet" href="../assets/css/admin_featured_products.css">

<?php require_once '../includes/footer.php'; ?>