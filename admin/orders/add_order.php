<?php
session_name('petstore_session');
session_start();

// Check if user is logged in as admin
if (!isset($_SESSION['is_admin']) || $_SESSION['is_admin'] !== true) {
    header('Location: ' . url('login?error=Access denied'));
    exit;
}

require_once __DIR__ . '/../../backend/config/database.php';
require_once __DIR__ . '/../../backend/functions/helpers.php';

$page_title = 'Add Order';
require_once __DIR__ . '/../includes/header.php';

// Get customers
$customers = [];
$customerResult = $conn->query("SELECT id, first_name, last_name, email FROM customers ORDER BY first_name");
while ($row = $customerResult->fetch_assoc()) {
    $customers[] = $row;
}

// Get products
$products = [];
$productResult = $conn->query("SELECT id, product_name, price, quantity_in_stock FROM products WHERE quantity_in_stock > 0 ORDER BY product_name");
while ($row = $productResult->fetch_assoc()) {
    $products[] = $row;
}

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $customerId = (int)($_POST['customer_id'] ?? 0);
    $notes = trim($_POST['notes'] ?? '');
    $productIds = $_POST['product_id'] ?? [];
    $quantities = $_POST['quantity'] ?? [];
    
    // Collect valid items
    $items = [];
    for ($i = 0; $i < count($productIds); $i++) {
        if (!empty($productIds[$i]) && !empty($quantities[$i]) && $quantities[$i] > 0) {
            $product = array_filter($products, function($p) use ($productIds, $i) {
                return $p['id'] == $productIds[$i];
            });
            $product = reset($product);
            
            if ($product) {
                $items[] = [
                    'product_id' => (int)$productIds[$i],
                    'quantity' => (int)$quantities[$i],
                    'unit_price' => $product['price'],
                    'product_name' => $product['product_name'],
                    'stock' => $product['quantity_in_stock']
                ];
            }
        }
    }
    
    if (!$customerId) {
        $error = 'Please select a customer.';
    } elseif (empty($items)) {
        $error = 'Please add at least one item to the order.';
    } else {
        // Check stock and calculate total
        $total = 0;
        $stockError = false;
        
        foreach ($items as $item) {
            if ($item['stock'] < $item['quantity']) {
                $error = "Insufficient stock for {$item['product_name']}. Available: {$item['stock']}";
                $stockError = true;
                break;
            }
            $total += $item['unit_price'] * $item['quantity'];
        }
        
        if (!$stockError && $total > 0) {
            // Begin transaction
            $conn->begin_transaction();
            
            try {
                // Insert order
                $stmt = $conn->prepare("INSERT INTO orders (customer_id, total_amount, status, notes, created_at) VALUES (?, ?, 'pending', ?, NOW())");
                $stmt->bind_param("ids", $customerId, $total, $notes);
                $stmt->execute();
                $orderId = $stmt->insert_id;
                
                // Insert order items and update stock
                $itemStmt = $conn->prepare("INSERT INTO order_items (order_id, product_id, quantity, unit_price) VALUES (?, ?, ?, ?)");
                $updateStockStmt = $conn->prepare("UPDATE products SET quantity_in_stock = quantity_in_stock - ? WHERE id = ?");
                
                foreach ($items as $item) {
                    // Insert order item
                    $itemStmt->bind_param("iiid", $orderId, $item['product_id'], $item['quantity'], $item['unit_price']);
                    $itemStmt->execute();
                    
                    // Update stock
                    $updateStockStmt->bind_param("ii", $item['quantity'], $item['product_id']);
                    $updateStockStmt->execute();
                }
                
                $conn->commit();
                $success = "Order #" . str_pad($orderId, 6, '0', STR_PAD_LEFT) . " created successfully!";
                
                // Clear form data
                $_POST = [];
                
            } catch (Exception $e) {
                $conn->rollback();
                $error = 'Failed to create order: ' . $e->getMessage();
            }
        }
    }
}

// Admin CSS
echo '<link rel="stylesheet" href="/Ria-Pet-Store/admin/css/orders.css?v=' . time() . '">';
?>

<div class="admin-dashboard">
    <div class="page-header">
        <h1>Add New Order</h1>
        <div class="action-buttons">
            <a href="orders.php" class="btn btn-outline">
                <?php echo icon('arrow-left', 16); ?> Back to Orders
            </a>
        </div>
    </div>

    <?php if ($error): ?>
        <div class="message error"><?php echo htmlspecialchars($error); ?></div>
    <?php endif; ?>
    
    <?php if ($success): ?>
        <div class="message success">
            <?php echo htmlspecialchars($success); ?>
            <div style="margin-top: 0.5rem;">
                <a href="order_details.php?id=<?php echo $orderId ?? 0; ?>" class="btn btn-primary btn-small">View Order</a>
                <a href="add_order.php" class="btn btn-outline btn-small">Add Another Order</a>
            </div>
        </div>
    <?php endif; ?>

    <?php if (!$success): ?>
    <form method="post" id="orderForm">
        <!-- Customer Selection -->
        <div class="info-card">
            <h3><?php echo icon('user', 20); ?> Customer Information</h3>
            <div class="form-group">
                <label for="customer_id">Select Customer *</label>
                <select id="customer_id" name="customer_id" class="form-control" required>
                    <option value="">-- Select Customer --</option>
                    <?php foreach ($customers as $customer): ?>
                        <option value="<?php echo $customer['id']; ?>" <?php echo (isset($_POST['customer_id']) && $_POST['customer_id'] == $customer['id']) ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($customer['first_name'] . ' ' . $customer['last_name'] . ' (' . $customer['email'] . ')'); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
        </div>

        <!-- Order Items -->
        <div class="info-card">
            <h3><?php echo icon('shopping-bag', 20); ?> Order Items</h3>
            <div id="items-container">
                <div class="order-item">
                    <div class="item-row">
                        <div class="item-product">
                            <label>Product</label>
                            <select name="product_id[]" class="form-control product-select" required>
                                <option value="">-- Select Product --</option>
                                <?php foreach ($products as $product): ?>
                                    <option value="<?php echo $product['id']; ?>" data-price="<?php echo $product['price']; ?>" data-stock="<?php echo $product['quantity_in_stock']; ?>">
                                        <?php echo htmlspecialchars($product['product_name'] . ' - ₱' . number_format($product['price'], 2) . ' (Stock: ' . $product['quantity_in_stock'] . ')'); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="item-qty">
                            <label>Quantity</label>
                            <input type="number" name="quantity[]" class="form-control qty-input" min="1" value="1">
                        </div>
                        <div class="item-price">
                            <label>Price</label>
                            <span class="item-price-display">₱0.00</span>
                        </div>
                        <div class="item-total">
                            <label>Total</label>
                            <span class="item-total-display">₱0.00</span>
                        </div>
                        <div class="item-actions">
                            <button type="button" class="btn-icon remove-item" title="Remove Item">
                                <?php echo icon('x', 14); ?>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
            <button type="button" id="add-item" class="btn btn-outline btn-small" style="margin-top: 1rem;">
                <?php echo icon('plus', 14); ?> Add Another Item
            </button>
        </div>

        <!-- Order Notes -->
        <div class="info-card">
            <h3><?php echo icon('file', 20); ?> Order Notes</h3>
            <div class="form-group">
                <textarea name="notes" class="form-control" rows="3" placeholder="Add any notes about this order..."><?php echo htmlspecialchars($_POST['notes'] ?? ''); ?></textarea>
            </div>
        </div>

        <!-- Order Summary -->
        <div class="info-card">
            <h3><?php echo icon('credit-card', 20); ?> Order Summary</h3>
            <div class="order-summary">
                <div class="summary-row">
                    <span>Subtotal:</span>
                    <span id="subtotal">₱0.00</span>
                </div>
                <div class="summary-row total">
                    <span>Total:</span>
                    <span id="total">₱0.00</span>
                </div>
            </div>
        </div>

        <div class="action-buttons">
            <button type="submit" class="btn btn-primary"><?php echo icon('check', 16); ?> Create Order</button>
            <a href="orders.php" class="btn btn-outline">Cancel</a>
        </div>
    </form>
    <?php endif; ?>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const container = document.getElementById('items-container');
    const addButton = document.getElementById('add-item');
    const subtotalSpan = document.getElementById('subtotal');
    const totalSpan = document.getElementById('total');
    
    function updateTotals() {
        let subtotal = 0;
        document.querySelectorAll('.order-item').forEach(item => {
            const select = item.querySelector('.product-select');
            const qty = item.querySelector('.qty-input');
            const priceSpan = item.querySelector('.item-price-display');
            const totalSpan = item.querySelector('.item-total-display');
            
            if (select.value && qty.value > 0) {
                const option = select.options[select.selectedIndex];
                const price = parseFloat(option.dataset.price) || 0;
                const quantity = parseInt(qty.value) || 0;
                const itemTotal = price * quantity;
                subtotal += itemTotal;
                
                priceSpan.textContent = '₱' + price.toFixed(2);
                totalSpan.textContent = '₱' + itemTotal.toFixed(2);
            } else {
                priceSpan.textContent = '₱0.00';
                totalSpan.textContent = '₱0.00';
            }
        });
        
        subtotalSpan.textContent = '₱' + subtotal.toFixed(2);
        totalSpan.textContent = '₱' + subtotal.toFixed(2);
    }
    
    function addItem() {
        const template = document.querySelector('.order-item').cloneNode(true);
        template.querySelectorAll('input, select').forEach(input => {
            if (input.type === 'number') input.value = '1';
            else if (input.tagName === 'SELECT') input.selectedIndex = 0;
            else input.value = '';
        });
        template.querySelectorAll('.item-price-display, .item-total-display').forEach(span => span.textContent = '₱0.00');
        
        container.appendChild(template);
        attachItemEvents(template);
        updateTotals();
    }
    
    function attachItemEvents(item) {
        const select = item.querySelector('.product-select');
        const qty = item.querySelector('.qty-input');
        const removeBtn = item.querySelector('.remove-item');
        
        if (select) select.addEventListener('change', updateTotals);
        if (qty) qty.addEventListener('input', updateTotals);
        if (removeBtn) {
            removeBtn.addEventListener('click', function() {
                if (container.children.length > 1) {
                    item.remove();
                    updateTotals();
                } else {
                    alert('At least one item is required.');
                }
            });
        }
    }
    
    if (addButton) addButton.addEventListener('click', addItem);
    
    document.querySelectorAll('.order-item').forEach(attachItemEvents);
    updateTotals();
});
</script>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>