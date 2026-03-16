<?php
require_once '../../backend/includes/header.php';
?>

<main>
    <h1>New Arrivals</h1>

    <div class="products-grid">
        <?php
        $sql = "SELECT * FROM products WHERE created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY) ORDER BY created_at DESC";
        $products = $conn->query($sql);

        if ($products->num_rows === 0):
        ?>
            <p>No new arrivals in the last 30 days.</p>
        <?php else: ?>
            <?php while ($product = $products->fetch_assoc()): ?>
                <div class="product-card">
                    <?php if (strtotime($product['created_at']) >= strtotime('-7 days')): ?>
                        <span class="badge new">New</span>
                    <?php endif; ?>

                    <img src="../../assets/images/placeholder.jpg" alt="<?= htmlspecialchars($product['product_name']) ?>">

                    <h3><?= htmlspecialchars($product['product_name']) ?></h3>
                    <p class="price">$<?= number_format($product['price'], 2) ?></p>
                    <p class="date">Added <?= date('M j, Y', strtotime($product['created_at'])) ?></p>

                    <a href="product_details.php?id=<?= $product['id'] ?>" class="btn">View Details</a>
                </div>
            <?php endwhile; ?>
        <?php endif; ?>
    </div>
</main>

<link rel="stylesheet" href="../../assets/css/new_arrivals.css">

<?php require_once '../../backend/includes/footer.php'; ?>