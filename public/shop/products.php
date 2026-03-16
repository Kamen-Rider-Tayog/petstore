<?php
require_once '../../../backend/config/database.php';
require_once '../../../backend/includes/header.php';
<link rel="stylesheet" href="../../../assets/css/products.css">


$categoryFilter = $_GET['category'] ?? '';
$searchTerm = trim($_GET['search'] ?? '');

// Build query with optional filters
$where = [];
$params = [];
$types = '';

if ($categoryFilter) {
    $where[] = 'category = ?';
    $types .= 's';
    $params[] = $categoryFilter;
}

if ($searchTerm) {
    $where[] = 'product_name LIKE ?';
    $types .= 's';
    $params[] = "%{$searchTerm}%";
}

$whereSql = '';
if (!empty($where)) {
    $whereSql = 'WHERE ' . implode(' AND ', $where);
}

$sql = "SELECT * FROM products {$whereSql} ORDER BY category, product_name";
$stmt = $conn->prepare($sql);
if (!empty($params)) {
    $stmt->bind_param($types, ...$params);
}
$stmt->execute();
$products = $stmt->get_result();

// Get unique categories for filtering
$categories = $conn->query("SELECT DISTINCT category FROM products ORDER BY category");
?>

<h1>Product Catalog</h1>

<div style="display: flex; flex-wrap: wrap; gap: 20px; align-items: center; margin-bottom: 20px;">
    <form id="productFilterForm" method="get" style="display: flex; gap: 10px; flex-wrap: wrap; align-items: center;">
        <label for="search">Search:</label>
        <input id="search" name="search" type="text" placeholder="Search products..." value="<?php echo htmlspecialchars($searchTerm); ?>" />
        <label for="category">Category:</label>
        <select name="category" id="category">
            <option value="all" <?php echo ($categoryFilter === '' || $categoryFilter === 'all') ? 'selected' : ''; ?>>All Categories</option>
            <?php while ($cat = $categories->fetch_assoc()): ?>
                <option value="<?php echo htmlspecialchars($cat['category']); ?>" <?php echo ($categoryFilter && $categoryFilter === $cat['category']) ? 'selected' : ''; ?>>
                    <?php echo ucfirst(htmlspecialchars($cat['category'])); ?>
                </option>
            <?php endwhile; ?>
        </select>
        <button type="submit">Filter</button>
        <?php if ($categoryFilter || $searchTerm): ?>
            <a href="products" style="margin-left: 1rem;">Clear</a>
        <?php endif; ?>
    </form>

    <div style="margin-left: auto;">
        <a href="cart" style="text-decoration: none; font-weight: bold;">🛒 Cart (<span id="cart-count">0</span>)</a>
    </div>
</div>

<div id="productLoading" style="display: none; text-align: center; margin: 10px 0;">Loading products...</div>

<div id="productGrid" style="display: grid; grid-template-columns: repeat(auto-fill, minmax(260px, 1fr)); gap: 20px;">
    <?php while ($product = $products->fetch_assoc()): ?>
        <div style="border: 1px solid #ddd; padding: 15px; border-radius: 5px; background: white;">
            <?php $image = (!empty($product['image']) ? asset('images/' . $product['image']) : Config::get('PLACEHOLDER_IMAGE_SMALL')); ?>
            <div style="height: 150px; background: #f9f9f9; display: flex; align-items: center; justify-content: center; margin-bottom: 10px;">
                <img src="<?php echo $image; ?>" alt="<?php echo htmlspecialchars($product['product_name']); ?>" style="max-height: 100%; max-width: 100%; object-fit: contain;" />
            </div>

            <h3 style="margin-top: 0; margin-bottom: 10px;"><a href="product_details?id=<?php echo $product['id']; ?>" style="text-decoration:none; color: inherit;"><?php echo htmlspecialchars($product['product_name']); ?></a></h3>
            <p style="margin: 5px 0;"><strong>Category:</strong> <?php echo ucfirst(htmlspecialchars($product['category'])); ?></p>
            <p style="margin: 5px 0;"><strong>Price:</strong> ₱<?php echo number_format($product['price'], 2); ?></p>
            <p style="margin: 5px 0;"><strong>Availability:</strong>
                <?php if ($product['quantity_in_stock'] > 0): ?>
                    <span style="color: green;">In Stock (<?php echo $product['quantity_in_stock']; ?>)</span>
                <?php else: ?>
                    <span style="color: red;">Out of Stock</span>
                <?php endif; ?>
            </p>

            <?php if ($product['quantity_in_stock'] > 0): ?>
                <div style="margin-top: 15px; display:flex; gap: 8px; align-items: center;">
                    <select id="qty_<?php echo $product['id']; ?>" style="width: 60px; padding: 5px;">
                        <?php for ($i = 1; $i <= min(10, $product['quantity_in_stock']); $i++): ?>
                            <option value="<?php echo $i; ?>"><?php echo $i; ?></option>
                        <?php endfor; ?>
                    </select>
                    <button data-add-to-cart="<?php echo $product['id']; ?>" style="padding: 6px 10px;">Add to Cart</button>
                </div>
            <?php endif; ?>
        </div>
    <?php endwhile; ?>
</div>

<br>
<a href="index">← Back to Home</a>

<script src="../../../assets/js/cart.js"></script>
<script>
const PLACEHOLDER_IMAGE_SMALL = '<?php echo Config::get('PLACEHOLDER_IMAGE_SMALL'); ?>';

function debounce(fn, delay) {
    let timer;
    return (...args) => {
        clearTimeout(timer);
        timer = setTimeout(() => fn(...args), delay);
    };
}

function renderProductCard(product) {
    const image = product.image && product.image.trim()
        ? `<?php echo asset('images/'); ?>${product.image}`
        : PLACEHOLDER_IMAGE_SMALL;

    const availability = product.quantity_in_stock > 0
        ? `<span style="color: green;">In Stock (${product.quantity_in_stock})</span>`
        : `<span style="color: red;">Out of Stock</span>`;

    const actions = [
        `<a href="product_details?id=${encodeURIComponent(product.id)}">View</a>`
    ];

    <?php if(isset($_SESSION['user_id'])): ?>
    actions.push(`<a href="edit_pet?id=${encodeURIComponent(product.id)}">Edit</a>`);
    actions.push(`<a href="delete_pet?id=${encodeURIComponent(product.id)}" onclick="return confirm('Are you sure?')">Delete</a>`);
    <?php endif; ?>

    return `
        <div style="border: 1px solid #ddd; padding: 15px; border-radius: 5px; background: white;">
            <div style="height: 150px; background: #f9f9f9; display: flex; align-items: center; justify-content: center; margin-bottom: 10px;">
                <img src="${image}" alt="${product.product_name}" style="max-height: 100%; max-width: 100%; object-fit: contain;" />
            </div>

            <h3 style="margin-top: 0; margin-bottom: 10px;"><a href="product_details?id=${encodeURIComponent(product.id)}" style="text-decoration:none; color: inherit;">${product.product_name}</a></h3>
            <p style="margin: 5px 0;"><strong>Category:</strong> ${product.category}</p>
            <p style="margin: 5px 0;"><strong>Price:</strong> ₱${parseFloat(product.price).toFixed(2)}</p>
            <p style="margin: 5px 0;"><strong>Availability:</strong> ${availability}</p>

            ${product.quantity_in_stock > 0 ? `
                <div style="margin-top: 15px; display:flex; gap: 8px; align-items: center;">
                    <select id="qty_${product.id}" style="width: 60px; padding: 5px;">
                        ${Array.from({ length: Math.min(10, product.quantity_in_stock) }, (_, i) => `<option value="${i + 1}">${i + 1}</option>`).join('')}
                    </select>
                    <button data-add-to-cart="${product.id}" style="padding: 6px 10px;">Add to Cart</button>
                </div>
            ` : ''}

            <div style="margin-top: 10px; font-size: 0.85em; color:#666;">ID: ${product.id}</div>
        </div>
    `;
}

async function loadProducts() {
    const search = document.getElementById('search').value;
    const category = document.getElementById('category').value;
    const loadingEl = document.getElementById('productLoading');
    const gridEl = document.getElementById('productGrid');

    loadingEl.style.display = 'block';
    gridEl.innerHTML = '';

    try {
        const response = await fetch(`../../../backend/api/search_products.php?search=${encodeURIComponent(search)}&category=${encodeURIComponent(category)}`);
        const data = await response.json();

        if (!data.success) {
            gridEl.innerHTML = '<p style="grid-column: 1 / -1;">Failed to load products.</p>';
            return;
        }

        if (data.count === 0) {
            gridEl.innerHTML = '<p style="grid-column: 1 / -1;">No products found.</p>';
            return;
        }

        gridEl.innerHTML = data.data.map(renderProductCard).join('');

        // Re-attach cart buttons to work with cart.js after re-render
        if (typeof attachAddToCartHandlers === 'function') {
            attachAddToCartHandlers();
        }
    } catch (error) {
        gridEl.innerHTML = `<p style="grid-column: 1 / -1; color: red;">Error loading products: ${error.message}</p>`;
    } finally {
        loadingEl.style.display = 'none';
    }
}

function attachProductFilterEvents() {
    const form = document.getElementById('productFilterForm');
    const searchInput = document.getElementById('search');
    const categoryInput = document.getElementById('category');

    form.addEventListener('submit', event => {
        event.preventDefault();
        loadProducts();
    });

    searchInput.addEventListener('input', debounce(loadProducts, 300));
    categoryInput.addEventListener('change', loadProducts);
}

document.addEventListener('DOMContentLoaded', () => {
    attachProductFilterEvents();
    loadProducts();
    updateCartCount();
});
</script>

<?php require_once '../../../backend/includes/footer.php'; ?>
