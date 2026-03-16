<?php
require_once '../../backend/includes/header.php';
?>

<main>
    <div class="search-container">
        <h1>Search our store</h1>
        <form action="search_results.php" method="get">
            <div class="search-wrapper">
                <input type="text" name="q" id="search-input" placeholder="Search for products...">
                <button type="submit">🔍</button>
                <div id="search-suggestions" class="suggestions-dropdown"></div>
            </div>
        </form>

        <div class="popular-categories">
            <h3>Popular Categories</h3>
            <?php
            // Fetch popular categories from database
            $categories = $conn->query("SELECT category, COUNT(*) as count FROM products GROUP BY category ORDER BY count DESC LIMIT 6");
            while($cat = $categories->fetch_assoc()):
            ?>
                <a href="category_products.php?name=<?= urlencode($cat['category']) ?>">
                    <?= htmlspecialchars($cat['category']) ?>
                </a>
            <?php endwhile; ?>
        </div>
    </div>
</main>

<link rel="stylesheet" href="../../assets/css/search.css">

<script src="../../assets/js/live_search.js"></script>

<?php require_once '../../backend/includes/footer.php'; ?>