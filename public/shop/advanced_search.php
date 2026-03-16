<?php
require_once '../../backend/includes/header.php';
?>

<main>
    <h1>Advanced Search</h1>

    <div class="search-tabs">
        <button class="tab-button active" onclick="showTab('products')">Products</button>
        <button class="tab-button" onclick="showTab('pets')">Pets</button>
    </div>

    <div id="products-tab" class="tab-content active">
        <form action="search_results.php" method="get">
            <input type="hidden" name="type" value="products">

            <div class="form-row">
                <div class="form-group">
                    <label for="product_search">Search Keywords:</label>
                    <input type="text" id="product_search" name="q" placeholder="Enter product name or description">
                </div>

                <div class="form-group">
                    <label for="product_category">Category:</label>
                    <select id="product_category" name="category">
                        <option value="">All Categories</option>
                        <?php
                        $categories = $conn->query("SELECT DISTINCT category FROM products ORDER BY category");
                        while ($cat = $categories->fetch_assoc()):
                        ?>
                            <option value="<?= htmlspecialchars($cat['category']) ?>"><?= htmlspecialchars($cat['category']) ?></option>
                        <?php endwhile; ?>
                    </select>
                </div>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label for="brand">Brand:</label>
                    <input type="text" id="brand" name="brand" placeholder="Enter brand name">
                </div>

                <div class="form-group">
                    <label>Price Range:</label>
                    <div class="price-range">
                        <input type="number" name="min_price" placeholder="Min" step="0.01" min="0">
                        <span>to</span>
                        <input type="number" name="max_price" placeholder="Max" step="0.01" min="0">
                    </div>
                </div>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label for="in_stock">
                        <input type="checkbox" id="in_stock" name="in_stock" value="1"> In Stock Only
                    </label>
                </div>

                <div class="form-group">
                    <label for="sort">Sort by:</label>
                    <select id="sort" name="sort">
                        <option value="relevance">Relevance</option>
                        <option value="price_low">Price: Low to High</option>
                        <option value="price_high">Price: High to Low</option>
                        <option value="name">Name</option>
                    </select>
                </div>
            </div>

            <button type="submit" class="btn btn-primary">Search Products</button>
        </form>
    </div>

    <div id="pets-tab" class="tab-content">
        <form action="search_results.php" method="get">
            <input type="hidden" name="type" value="pets">

            <div class="form-row">
                <div class="form-group">
                    <label for="pet_species">Species:</label>
                    <select id="pet_species" name="species">
                        <option value="">All Species</option>
                        <option value="Dog">Dog</option>
                        <option value="Cat">Cat</option>
                        <option value="Rabbit">Rabbit</option>
                        <option value="Bird">Bird</option>
                        <option value="Hamster">Hamster</option>
                    </select>
                </div>

                <div class="form-group">
                    <label for="pet_breed">Breed:</label>
                    <input type="text" id="pet_breed" name="breed" placeholder="Enter breed">
                </div>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label>Age Range:</label>
                    <div class="age-range">
                        <input type="number" name="min_age" placeholder="Min age" min="0">
                        <span>to</span>
                        <input type="number" name="max_age" placeholder="Max age" min="0">
                    </div>
                </div>

                <div class="form-group">
                    <label>Price Range:</label>
                    <div class="price-range">
                        <input type="number" name="min_price" placeholder="Min" step="0.01" min="0">
                        <span>to</span>
                        <input type="number" name="max_price" placeholder="Max" step="0.01" min="0">
                    </div>
                </div>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label>Gender:</label>
                    <div class="gender-options">
                        <label><input type="radio" name="gender" value=""> Any</label>
                        <label><input type="radio" name="gender" value="male"> Male</label>
                        <label><input type="radio" name="gender" value="female"> Female</label>
                    </div>
                </div>

                <div class="form-group">
                    <label for="pet_color">Color:</label>
                    <input type="text" id="pet_color" name="color" placeholder="Enter color">
                </div>
            </div>

            <button type="submit" class="btn btn-primary">Search Pets</button>
        </form>
    </div>
</main>

<link rel="stylesheet" href="../../assets/css/advanced_search.css">

<script>
function showTab(tabName) {
    // Hide all tabs
    document.querySelectorAll('.tab-content').forEach(tab => {
        tab.classList.remove('active');
    });
    document.querySelectorAll('.tab-button').forEach(btn => {
        btn.classList.remove('active');
    });

    // Show selected tab
    document.getElementById(tabName + '-tab').classList.add('active');
    event.target.classList.add('active');
}
</script>

<?php require_once '../../backend/includes/footer.php'; ?>

    <br>

    <?php if (isset($_GET['species']) || isset($_GET['max_price']) || isset($_GET['min_age'])): ?>
        <h2>Search Results</h2>
        
        <?php if ($result->num_rows > 0): ?>
            <table border="1" cellpadding="5">
                <tr>
                    <th>Name</th>
                    <th>Species</th>
                    <th>Age</th>
                    <th>Price</th>
                </tr>
                <?php while($row = $result->fetch_assoc()): ?>
                    <tr>
                        <td><?php echo $row['name']; ?></td>
                        <td><?php echo $row['species']; ?></td>
                        <td><?php echo $row['age']; ?> year old</td>
                        <td>₱<?php echo $row['price']; ?></td>
                    </tr>
                <?php endwhile; ?>
            </table>
        <?php else: ?>
            <p>No pets match your search criteria.</p>
        <?php endif; ?>
    <?php endif; ?>

    <br>
    <a href="index">Back to Home</a>
</body>
</html>