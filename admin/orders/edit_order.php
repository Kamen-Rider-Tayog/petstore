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

$orderId = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$error = '';
$success = '';

if (!$orderId) {
    header('Location: orders.php');
    exit();
}

// Get order details
$stmt = $conn->prepare("
    SELECT o.*, c.first_name, c.last_name, c.email
    FROM orders o
    LEFT JOIN customers c ON o.customer_id = c.id
    WHERE o.id = ?
");
$stmt->bind_param('i', $orderId);
$stmt->execute();
$order = $stmt->get_result()->fetch_assoc();

if (!$order) {
    header('Location: orders.php');
    exit();
}

// Get order items
$stmt = $conn->prepare("
    SELECT oi.*, p.product_name, p.price as current_price
    FROM order_items oi
    LEFT JOIN products p ON oi.product_id = p.id
    WHERE oi.order_id = ?
");
$stmt->bind_param('i', $orderId);
$stmt->execute();
$orderItems = $stmt->get_result();

$items = [];
while ($row = $orderItems->fetch_assoc()) {
    $items[] = $row;
}

// Get products for dropdown
$products = [];
$productResult = $conn->query("SELECT id, product_name, price, quantity_in_stock FROM products WHERE quantity_in_stock > 0 ORDER BY product_name");
while ($row = $productResult->fetch_assoc()) {
    $products[] = $row;
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $status = trim($_POST['status'] ?? '');
    $notes = trim($_POST['notes'] ?? '');
    
    if (empty($status)) {
        $error = 'Please select a status.';
    } else {
        $stmt = $conn->prepare("UPDATE orders SET status = ?, notes = ? WHERE id = ?");
        $stmt->bind_param('ssi', $status, $notes, $orderId);
        
        if ($stmt->execute()) {
            $success = 'Order updated successfully!';
        } else {
            $error = 'Error updating order: ' . $conn->error;
        }
    }
}

$page_title = 'Edit Order - #' . str_pad($order['id'], 6, '0', STR_PAD_LEFT);
require_once __DIR__ . '/../includes/header.php';

// Admin CSS
echo '<link rel="stylesheet" href="/Ria-Pet-Store/admin/css/orders.css?v=' . time() . '">';
?>

<div class="admin-dashboard">
    <div class="page-header">
        <h1>Edit Order</h1>
        <div class="action-buttons">
            <a href="order_details.php?id=<?php echo $order['id']; ?>" class="btn btn-outline">
                <?php echo icon('eye', 16); ?> View Details
            </a>
            <a href="orders.php" class="btn btn-outline">
                <?php echo icon('arrow-left', 16); ?> Back to Orders
            </a>
        </div>
    </div>

    <?php if ($error): ?>
        <div class="message error"><?php echo htmlspecialchars($error); ?></div>
    <?php endif; ?>
    
    <?php if ($success): ?>
        <div class="message success"><?php echo htmlspecialchars($success); ?></div>
    <?php endif; ?>

    <div class="order-grid">
        <!-- Order Summary -->
        <div class="info-card">
            <h3><?php echo icon('package', 20); ?> Order Summary</h3>
            <table class="info-table">
                <tr>
                    <td>Order ID</td>
                    <td>#<?php echo str_pad($order['id'], 6, '0', STR_PAD_LEFT); ?></td>
                </tr>
                <tr>
                    <td>Customer</td>
                    <td><?php echo htmlspecialchars($order['first_name'] . ' ' . $order['last_name']); ?></td>
                </tr>
                <tr>
                    <td>Email</td>
                    <td><?php echo htmlspecialchars($order['email']); ?></td>
                </tr>
                <tr>
                    <td>Total Amount</td>
                    <td class="total-amount">₱<?php echo number_format($order['total_amount'], 2); ?></td>
                </tr>
                <tr>
                    <td>Order Date</td>
                    <td><?php echo date('M d, Y H:i', strtotime($order['created_at'])); ?></td>
                </tr>
            </table>
        </div>

        <!-- Edit Form -->
        <div class="info-card">
            <h3><?php echo icon('settings', 20); ?> Update Status</h3>
            <form method="post">
                <div class="form-group">
                    <label for="status">Order Status</label>
                    <select id="status" name="status" class="form-control">
                        <option value="pending" <?php echo $order['status'] === 'pending' ? 'selected' : ''; ?>>Pending</option>
                        <option value="processing" <?php echo $order['status'] === 'processing' ? 'selected' : ''; ?>>Processing</option>
                        <option value="shipped" <?php echo $order['status'] === 'shipped' ? 'selected' : ''; ?>>Shipped</option>
                        <option value="delivered" <?php echo $order['status'] === 'delivered' ? 'selected' : ''; ?>>Delivered</option>
                        <option value="cancelled" <?php echo $order['status'] === 'cancelled' ? 'selected' : ''; ?>>Cancelled</option>
                    </select>
                </div>

                <div class="form-group">
                    <label for="notes">Order Notes</label>
                    <textarea id="notes" name="notes" rows="4" class="form-control" placeholder="Add any notes about this order..."><?php echo htmlspecialchars($order['notes'] ?? ''); ?></textarea>
                </div>

                <div class="action-buttons" style="margin-top: 1rem;">
                    <button type="submit" class="btn btn-primary"><?php echo icon('check', 16); ?> Update Order</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Order Items -->
    <div class="items-card">
        <h3><?php echo icon('shopping-bag', 20); ?> Order Items</h3>
        <table class="items-table">
            <thead>
                <tr>
                    <th>Product</th>
                    <th>Quantity</th>
                    <th>Unit Price</th>
                    <th>Total</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($items as $item): ?>
                <tr>
                    <td><?php echo htmlspecialchars($item['product_name']); ?></td>
                    <td><?php echo $item['quantity']; ?></td>
                    <td>₱<?php echo number_format($item['unit_price'], 2); ?></td>
                    <td>₱<?php echo number_format($item['quantity'] * $item['unit_price'], 2); ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
            <tfoot>
                <tr class="total-row">
                    <td colspan="3" style="text-align: right;"><strong>Total</strong></td>
                    <td><strong>₱<?php echo number_format($order['total_amount'], 2); ?></strong></td>
                </tr>
            </tfoot>
        </table>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>