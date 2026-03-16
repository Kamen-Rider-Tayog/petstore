<?php
require_once '../../backend/includes/header.php';
?>

<main>
    <h1>Shop by Category</h1>

    <div class="categories-grid">
        <?php
        $sql = "SELECT c.*,
                       (SELECT COUNT(*) FROM products WHERE category = c.category_name) as product_count
                FROM categories c
                WHERE parent_id IS NULL
                ORDER BY category_name";

        $categories = $conn->query($sql);

        while ($category = $categories->fetch_assoc()):
            $subcategories = $conn->query("SELECT * FROM categories WHERE parent_id = " . $category['id'] . " ORDER BY category_name");
        ?>
            <div class="category-card">
                <h2><a href="category_products.php?id=<?= $category['id'] ?>"><?= htmlspecialchars($category['category_name']) ?></a></h2>
                <p class="product-count">(<?= $category['product_count'] ?> products)</p>

                <?php if ($subcategories->num_rows > 0): ?>
                    <ul class="subcategories">
                        <?php while ($sub = $subcategories->fetch_assoc()): ?>
                            <li><a href="category_products.php?id=<?= $sub['id'] ?>"><?= htmlspecialchars($sub['category_name']) ?></a></li>
                        <?php endwhile; ?>
                    </ul>
                <?php endif; ?>
            </div>
        <?php endwhile; ?>
    </div>
</main>

<link rel="stylesheet" href="../../assets/css/categories.css">

<?php require_once '../../backend/includes/footer.php'; ?>