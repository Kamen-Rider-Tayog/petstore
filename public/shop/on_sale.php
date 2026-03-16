<?php
require_once '../../backend/includes/header.php';
?>

<main>
    <h1>On Sale</h1>

    <div class="products-grid">
        <?php
        $sql = "SELECT * FROM products WHERE on_sale = TRUE ORDER BY sale_price ASC";
        $products = $conn->query($sql);

        if ($products->num_rows === 0):
        ?>
            <p>No products on sale at the moment.</p>
        <?php else: ?>
            <?php while ($product = $products->fetch_assoc()): ?>
                <div class="product-card">
                    <span class="badge sale">Sale</span>

                    <img src="../../assets/images/placeholder.jpg" alt="<?= htmlspecialchars($product['product_name']) ?>">

                    <h3><?= htmlspecialchars($product['product_name']) ?></h3>
                    <p class="price">
                        <span class="original-price">$<?= number_format($product['price'], 2) ?></span>
                        <span class="sale-price">$<?= number_format($product['sale_price'], 2) ?></span>
                        <?php
                        $discount = round((($product['price'] - $product['sale_price']) / $product['price']) * 100);
                        ?>
                        <span class="discount">(<?= $discount ?>% off)</span>
                    </p>

                    <a href="product_details.php?id=<?= $product['id'] ?>" class="btn">View Details</a>
                </div>
            <?php endwhile; ?>
        <?php endif; ?>
    </div>
</main>

<link rel="stylesheet" href="../../assets/css/on_sale.css">

<?php require_once '../../backend/includes/footer.php'; ?>