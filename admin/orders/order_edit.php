<?php
require_once '../includes/header.php';
require_once '../includes/sidebar.php';

$orderId = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$message = '';

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

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $status = trim($_POST['status'] ?? '');
    $notes = trim($_POST['notes'] ?? '');

    if (empty($status)) {
        $message = 'Please select a status.';
    } else {
        $stmt = $conn->prepare("UPDATE orders SET status = ?, notes = ? WHERE id = ?");
        $stmt->bind_param('ssi', $status, $notes, $orderId);

        if ($stmt->execute()) {
            header('Location: order_details.php?id=' . $orderId . '&message=Order updated successfully');
            exit();
        } else {
            $message = 'Error updating order: ' . $conn->error;
        }
    }
}
?>

<main class="admin-main">
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 2rem;">
        <h2>Edit Order - #<?php echo str_pad($order['id'], 6, '0', STR_PAD_LEFT); ?></h2>
        <div>
            <a href="order_details.php?id=<?php echo $order['id']; ?>" class="btn">View Details</a>
            <a href="orders.php" class="btn">Back to Orders</a>
        </div>
    </div>

    <?php if ($message): ?>
        <div style="background: #f8d7da; color: #721c24; padding: 1rem; border-radius: 4px; margin-bottom: 1rem;">
            <?php echo htmlspecialchars($message); ?>
        </div>
    <?php endif; ?>

    <div style="display: grid; grid-template-columns: 1fr 2fr; gap: 2rem;">
        <!-- Order Summary -->
        <div style="background: #fff; padding: 2rem; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1);">
            <h3>Order Summary</h3>
            <table style="width: 100%; border-collapse: collapse;">
                <tr>
                    <td style="padding: 0.5rem 0; font-weight: bold; width: 120px;">Order ID:</td>
                    <td style="padding: 0.5rem 0;">#<?php echo str_pad($order['id'], 6, '0', STR_PAD_LEFT); ?></td>
                </tr>
                <tr>
                    <td style="padding: 0.5rem 0; font-weight: bold;">Customer:</td>
                    <td style="padding: 0.5rem 0;"><?php echo htmlspecialchars($order['first_name'] . ' ' . $order['last_name']); ?></td>
                </tr>
                <tr>
                    <td style="padding: 0.5rem 0; font-weight: bold;">Email:</td>
                    <td style="padding: 0.5rem 0;"><?php echo htmlspecialchars($order['email']); ?></td>
                </tr>
                <tr>
                    <td style="padding: 0.5rem 0; font-weight: bold;">Total Amount:</td>
                    <td style="padding: 0.5rem 0; font-weight: bold; color: #28a745;">₱<?php echo number_format($order['total_amount'], 2); ?></td>
                </tr>
                <tr>
                    <td style="padding: 0.5rem 0; font-weight: bold;">Order Date:</td>
                    <td style="padding: 0.5rem 0;"><?php echo date('M d, Y H:i', strtotime($order['created_at'])); ?></td>
                </tr>
            </table>
        </div>

        <!-- Edit Form -->
        <div style="background: #fff; padding: 2rem; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1);">
            <h3>Update Order Status</h3>
            <form method="post">
                <div class="form-group">
                    <label for="status">Order Status *</label>
                    <select id="status" name="status" required>
                        <option value="pending" <?php echo $order['status'] === 'pending' ? 'selected' : ''; ?>>Pending</option>
                        <option value="processing" <?php echo $order['status'] === 'processing' ? 'selected' : ''; ?>>Processing</option>
                        <option value="shipped" <?php echo $order['status'] === 'shipped' ? 'selected' : ''; ?>>Shipped</option>
                        <option value="delivered" <?php echo $order['status'] === 'delivered' ? 'selected' : ''; ?>>Delivered</option>
                        <option value="cancelled" <?php echo $order['status'] === 'cancelled' ? 'selected' : ''; ?>>Cancelled</option>
                    </select>
                </div>

                <div class="form-group">
                    <label for="notes">Order Notes</label>
                    <textarea id="notes" name="notes" rows="4" placeholder="Add any notes about this order..."><?php echo htmlspecialchars($order['notes'] ?? ''); ?></textarea>
                </div>

                <div style="margin-top: 2rem;">
                    <button type="submit" class="btn btn-success">Update Order</button>
                    <a href="order_details.php?id=<?php echo $order['id']; ?>" class="btn" style="margin-left: 1rem;">Cancel</a>
                </div>
            </form>
        </div>
    </div>

    <!-- Order Items Preview -->
    <div style="background: #fff; padding: 2rem; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); margin-top: 2rem;">
        <h3>Order Items</h3>
        <?php
        $stmt = $conn->prepare("
            SELECT oi.*, p.product_name
            FROM order_items oi
            LEFT JOIN products p ON oi.product_id = p.id
            WHERE oi.order_id = ?
        ");
        $stmt->bind_param('i', $orderId);
        $stmt->execute();
        $orderItems = $stmt->get_result();
        ?>

        <div style="overflow-x: auto;">
            <table style="width: 100%; border-collapse: collapse;">
                <thead>
                    <tr style="border-bottom: 2px solid #dee2e6;">
                        <th style="padding: 0.75rem 0.5rem; text-align: left;">Product</th>
                        <th style="padding: 0.75rem 0.5rem; text-align: center;">Quantity</th>
                        <th style="padding: 0.75rem 0.5rem; text-align: right;">Unit Price</th>
                        <th style="padding: 0.75rem 0.5rem; text-align: right;">Total</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($item = $orderItems->fetch_assoc()): ?>
                        <tr style="border-bottom: 1px solid #dee2e6;">
                            <td style="padding: 0.75rem 0.5rem;"><?php echo htmlspecialchars($item['product_name']); ?></td>
                            <td style="padding: 0.75rem 0.5rem; text-align: center;"><?php echo $item['quantity']; ?></td>
                            <td style="padding: 0.75rem 0.5rem; text-align: right;">₱<?php echo number_format($item['unit_price'], 2); ?></td>
                            <td style="padding: 0.75rem 0.5rem; text-align: right;">₱<?php echo number_format($item['quantity'] * $item['unit_price'], 2); ?></td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    </div>
</main>

<?php require_once '../includes/footer.php'; ?>