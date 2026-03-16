<?php
require_once '../../backend/includes/header.php';
?>

<main>
    <h1>Featured Products</h1>

    <div class="products-grid">
        <?php
        $sql = "SELECT * FROM products WHERE featured = TRUE ORDER BY id DESC";
        $products = $conn->query($sql);

        if ($products->num_rows === 0):
        ?>
            <p>No featured products at the moment.</p>
        <?php else: ?>
            <?php while ($product = $products->fetch_assoc()): ?>
                <div class="product-card">
                    <span class="badge featured">Featured</span>

                    <img src="../../assets/images/placeholder.jpg" alt="<?= htmlspecialchars($product['product_name']) ?>">

                    <h3><?= htmlspecialchars($product['product_name']) ?></h3>
                    <p class="price">$<?= number_format($product['price'], 2) ?></p>

                    <a href="product_details.php?id=<?= $product['id'] ?>" class="btn">View Details</a>
                </div>
            <?php endwhile; ?>
        <?php endif; ?>
    </div>
</main>

<link rel="stylesheet" href="../../assets/css/featured.css">

<?php require_once '../../backend/includes/footer.php'; ?>