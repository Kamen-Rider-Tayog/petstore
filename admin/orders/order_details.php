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

if (!$orderId) {
    header('Location: orders.php');
    exit();
}

// Get order details
$stmt = $conn->prepare("
    SELECT o.*, c.first_name, c.last_name, c.email, c.phone, c.address
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
    SELECT oi.*, p.product_name, p.product_image
    FROM order_items oi
    LEFT JOIN products p ON oi.product_id = p.id
    WHERE oi.order_id = ?
");
$stmt->bind_param('i', $orderId);
$stmt->execute();
$orderItems = $stmt->get_result();

$page_title = 'Order Details - #' . str_pad($order['id'], 6, '0', STR_PAD_LEFT);
require_once __DIR__ . '/../includes/header.php';

// Admin CSS
echo '<link rel="stylesheet" href="/Ria-Pet-Store/admin/css/orders.css?v=' . time() . '">';
?>

<div class="admin-dashboard">
    <div class="page-header">
        <a href="orders.php" class="btn btn-outline">
            <?php echo icon('arrow-left', 16); ?> Back to Orders
        </a>
        <div class="action-buttons">
            <a href="edit_order.php?id=<?php echo $order['id']; ?>" class="btn btn-primary">
                <?php echo icon('edit', 16); ?> Edit Order
            </a>
            <?php if ($order['status'] !== 'cancelled' && $order['status'] !== 'delivered'): ?>
            <a href="cancel_order.php?id=<?php echo $order['id']; ?>" class="btn btn-danger">
                <?php echo icon('x', 16); ?> Cancel Order
            </a>
            <?php endif; ?>

        </div>
    </div>

    <div class="order-grid">
        <!-- Order Information -->
        <div class="info-card">
            <h3><?php echo icon('package', 20); ?> Order Information</h3>
            <table class="info-table">
                <tr>
                    <td>Order ID</td>
                    <td>#<?php echo str_pad($order['id'], 6, '0', STR_PAD_LEFT); ?></td>
                </tr>
                <tr>
                    <td>Status</td>
                    <td>
                        <span class="status-badge status-<?php echo strtolower($order['status']); ?>">
                            <?php echo ucfirst($order['status']); ?>
                        </span>
                    </td>
                </tr>
                <tr>
                    <td>Order Date</td>
                    <td><?php echo date('F j, Y \a\t g:i A', strtotime($order['created_at'])); ?></td>
                </tr>
                <tr>
                    <td>Total Amount</td>
                    <td class="total-amount">₱<?php echo number_format($order['total_amount'], 2); ?></td>
                </tr>
                <?php if (!empty($order['notes'])): ?>
                <tr>
                    <td>Notes</td>
                    <td><?php echo nl2br(htmlspecialchars($order['notes'])); ?></td>
                </tr>
                <?php endif; ?>
            </table>
        </div>

        <!-- Customer Information -->
        <div class="info-card">
            <h3><?php echo icon('user', 20); ?> Customer Information</h3>
            <table class="info-table">
                <tr>
                    <td>Name</td>
                    <td><?php echo htmlspecialchars($order['first_name'] . ' ' . $order['last_name']); ?></td>
                </tr>
                <tr>
                    <td>Email</td>
                    <td><?php echo htmlspecialchars($order['email']); ?></td>
                </tr>
                <?php if (!empty($order['phone'])): ?>
                <tr>
                    <td>Phone</td>
                    <td><?php echo htmlspecialchars($order['phone']); ?></td>
                </tr>
                <?php endif; ?>
                <?php if (!empty($order['address'])): ?>
                <tr>
                    <td>Address</td>
                    <td><?php echo nl2br(htmlspecialchars($order['address'])); ?></td>
                </tr>
                <?php endif; ?>
            </table>
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
                <?php
                $subtotal = 0;
                while ($item = $orderItems->fetch_assoc()):
                    $itemTotal = $item['quantity'] * $item['unit_price'];
                    $subtotal += $itemTotal;
                ?>
                <tr>
                    <td><?php echo htmlspecialchars($item['product_name']); ?></td>
                    <td><?php echo $item['quantity']; ?></td>
                    <td>₱<?php echo number_format($item['unit_price'], 2); ?></td>
                    <td>₱<?php echo number_format($itemTotal, 2); ?></td>
                </tr>
                <?php endwhile; ?>
            </tbody>
            <tfoot>
                <tr>
                    <td colspan="3" style="text-align: right;"><strong>Subtotal</strong></td>
                    <td>₱<?php echo number_format($subtotal, 2); ?></td>
                </tr>
                <?php if (isset($order['shipping_amount']) && $order['shipping_amount'] > 0): ?>
                <tr>
                    <td colspan="3" style="text-align: right;">Shipping</td>
                    <td>₱<?php echo number_format($order['shipping_amount'], 2); ?></td>
                </tr>
                <?php endif; ?>
                <tr class="total-row">
                    <td colspan="3" style="text-align: right;"><strong>Total</strong></td>
                    <td><strong>₱<?php echo number_format($order['total_amount'], 2); ?></strong></td>
                </tr>
            </tfoot>
        </table>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>