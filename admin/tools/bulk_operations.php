<?php
require_once '../includes/header.php';
require_once '../includes/sidebar.php';

$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['bulk_update'])) {
        $product_ids = $_POST['product_ids'] ?? [];
        $action = $_POST['bulk_action'];
        $value = $_POST['bulk_value'] ?? null;

        if (!empty($product_ids) && !empty($action)) {
            $ids_string = implode(',', array_map('intval', $product_ids));

            switch ($action) {
                case 'update_price':
                    if (is_numeric($value)) {
                        $conn->query("UPDATE products SET price = $value WHERE id IN ($ids_string)");
                        $message = "Updated prices for " . count($product_ids) . " products.";
                    }
                    break;
                case 'update_stock':
                    if (is_numeric($value)) {
                        $conn->query("UPDATE products SET stock_quantity = $value WHERE id IN ($ids_string)");
                        $message = "Updated stock for " . count($product_ids) . " products.";
                    }
                    break;
                case 'update_category':
                    if (!empty($value)) {
                        $conn->query("UPDATE products SET category = '$value' WHERE id IN ($ids_string)");
                        $message = "Updated category for " . count($product_ids) . " products.";
                    }
                    break;
                case 'set_featured':
                    $featured_value = ($value === 'true') ? 1 : 0;
                    $conn->query("UPDATE products SET featured = $featured_value WHERE id IN ($ids_string)");
                    $message = "Updated featured status for " . count($product_ids) . " products.";
                    break;
                case 'set_on_sale':
                    $sale_value = ($value === 'true') ? 1 : 0;
                    $conn->query("UPDATE products SET on_sale = $sale_value WHERE id IN ($ids_string)");
                    $message = "Updated sale status for " . count($product_ids) . " products.";
                    break;
                case 'delete':
                    $conn->query("DELETE FROM products WHERE id IN ($ids_string)");
                    $message = "Deleted " . count($product_ids) . " products.";
                    break;
            }
        }
    }
}

// Get products for display
$products = $conn->query("SELECT * FROM products ORDER BY id DESC LIMIT 50");
$categories = $conn->query("SELECT DISTINCT category FROM products WHERE category IS NOT NULL ORDER BY category");
?>

<main class="admin-main">
    <h2>Bulk Product Operations</h2>

    <?php if ($message): ?>
        <div class="alert alert-success"><?php echo $message; ?></div>
    <?php endif; ?>

    <div class="bulk-form">
        <h3>Select Products and Action</h3>
        <form method="post" id="bulk-form">
            <div class="bulk-controls">
                <div class="control-group">
                    <label for="bulk_action">Action:</label>
                    <select id="bulk_action" name="bulk_action" onchange="showValueField()">
                        <option value="">Select Action</option>
                        <option value="update_price">Update Price</option>
                        <option value="update_stock">Update Stock</option>
                        <option value="update_category">Update Category</option>
                        <option value="set_featured">Set Featured</option>
                        <option value="set_on_sale">Set On Sale</option>
                        <option value="delete">Delete Products</option>
                    </select>
                </div>
                <div class="control-group" id="value-field" style="display: none;">
                    <label for="bulk_value">Value:</label>
                    <input type="text" id="bulk_value" name="bulk_value">
                </div>
                <div class="control-group">
                    <button type="submit" name="bulk_update" class="btn btn-primary" onclick="return confirmBulkAction()">Apply to Selected</button>
                </div>
            </div>

            <div class="products-table">
                <table>
                    <thead>
                        <tr>
                            <th><input type="checkbox" id="select-all"></th>
                            <th>ID</th>
                            <th>Name</th>
                            <th>Category</th>
                            <th>Price</th>
                            <th>Stock</th>
                            <th>Featured</th>
                            <th>On Sale</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($product = $products->fetch_assoc()): ?>
                            <tr>
                                <td><input type="checkbox" name="product_ids[]" value="<?php echo $product['id']; ?>" class="product-checkbox"></td>
                                <td><?php echo $product['id']; ?></td>
                                <td><?php echo htmlspecialchars($product['name']); ?></td>
                                <td><?php echo htmlspecialchars($product['category'] ?: '—'); ?></td>
                                <td>$<?php echo number_format($product['price'], 2); ?></td>
                                <td><?php echo $product['stock_quantity']; ?></td>
                                <td><?php echo $product['featured'] ? 'Yes' : 'No'; ?></td>
                                <td><?php echo $product['on_sale'] ? 'Yes' : 'No'; ?></td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        </form>
    </div>
</main>

<link rel="stylesheet" href="../assets/css/admin_bulk_operations.css">

<script>
document.getElementById('select-all').addEventListener('change', function() {
    const checkboxes = document.querySelectorAll('.product-checkbox');
    checkboxes.forEach(cb => cb.checked = this.checked);
});

function showValueField() {
    const action = document.getElementById('bulk_action').value;
    const valueField = document.getElementById('value-field');
    const valueInput = document.getElementById('bulk_value');

    if (['update_price', 'update_stock', 'update_category'].includes(action)) {
        valueField.style.display = 'block';
        valueInput.type = action === 'update_price' || action === 'update_stock' ? 'number' : 'text';
        valueInput.placeholder = action === 'update_price' ? 'New price' :
                               action === 'update_stock' ? 'New stock quantity' : 'New category';
        valueInput.required = true;
    } else if (['set_featured', 'set_on_sale'].includes(action)) {
        valueField.style.display = 'block';
        valueInput.type = 'hidden';
        valueInput.value = 'true';
        valueInput.required = false;
    } else {
        valueField.style.display = 'none';
        valueInput.required = false;
    }
}

function confirmBulkAction() {
    const selected = document.querySelectorAll('.product-checkbox:checked');
    if (selected.length === 0) {
        alert('Please select at least one product.');
        return false;
    }

    const action = document.getElementById('bulk_action').value;
    if (!action) {
        alert('Please select an action.');
        return false;
    }

    let message = `Are you sure you want to ${action.replace('_', ' ')} for ${selected.length} product(s)?`;
    return confirm(message);
}
</script>

<?php require_once '../includes/footer.php'; ?>