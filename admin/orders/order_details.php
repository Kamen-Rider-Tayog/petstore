<?php
require_once '../includes/header.php';
require_once '../includes/sidebar.php';

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
    SELECT oi.*, p.product_name, p.image
    FROM order_items oi
    LEFT JOIN products p ON oi.product_id = p.id
    WHERE oi.order_id = ?
");
$stmt->bind_param('i', $orderId);
$stmt->execute();
$orderItems = $stmt->get_result();
?>

<main class="admin-main">
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 2rem;">
        <h2>Order Details - #<?php echo str_pad($order['id'], 6, '0', STR_PAD_LEFT); ?></h2>
        <div>
            <a href="order_edit.php?id=<?php echo $order['id']; ?>" class="btn btn-warning">Edit Order</a>
            <a href="orders.php" class="btn">Back to Orders</a>
        </div>
    </div>

    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 2rem; margin-bottom: 2rem;">
        <!-- Order Information -->
        <div style="background: #fff; padding: 2rem; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1);">
            <h3>Order Information</h3>
            <table style="width: 100%; border-collapse: collapse;">
                <tr>
                    <td style="padding: 0.5rem 0; font-weight: bold; width: 140px;">Order ID:</td>
                    <td style="padding: 0.5rem 0;">#<?php echo str_pad($order['id'], 6, '0', STR_PAD_LEFT); ?></td>
                </tr>
                <tr>
                    <td style="padding: 0.5rem 0; font-weight: bold;">Status:</td>
                    <td style="padding: 0.5rem 0;">
                        <span class="status-badge status-<?php echo strtolower($order['status']); ?>">
                            <?php echo htmlspecialchars(ucfirst($order['status'])); ?>
                        </span>
                    </td>
                </tr>
                <tr>
                    <td style="padding: 0.5rem 0; font-weight: bold;">Order Date:</td>
                    <td style="padding: 0.5rem 0;"><?php echo date('M d, Y H:i', strtotime($order['created_at'])); ?></td>
                </tr>
                <tr>
                    <td style="padding: 0.5rem 0; font-weight: bold;">Total Amount:</td>
                    <td style="padding: 0.5rem 0; font-weight: bold; color: #28a745;">₱<?php echo number_format($order['total_amount'], 2); ?></td>
                </tr>
                <?php if (!empty($order['notes'])): ?>
                <tr>
                    <td style="padding: 0.5rem 0; font-weight: bold;">Notes:</td>
                    <td style="padding: 0.5rem 0;"><?php echo nl2br(htmlspecialchars($order['notes'])); ?></td>
                </tr>
                <?php endif; ?>
            </table>
        </div>

        <!-- Customer Information -->
        <div style="background: #fff; padding: 2rem; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1);">
            <h3>Customer Information</h3>
            <table style="width: 100%; border-collapse: collapse;">
                <tr>
                    <td style="padding: 0.5rem 0; font-weight: bold; width: 140px;">Name:</td>
                    <td style="padding: 0.5rem 0;"><?php echo htmlspecialchars($order['first_name'] . ' ' . $order['last_name']); ?></td>
                </tr>
                <tr>
                    <td style="padding: 0.5rem 0; font-weight: bold;">Email:</td>
                    <td style="padding: 0.5rem 0;"><?php echo htmlspecialchars($order['email']); ?></td>
                </tr>
                <?php if (!empty($order['phone'])): ?>
                <tr>
                    <td style="padding: 0.5rem 0; font-weight: bold;">Phone:</td>
                    <td style="padding: 0.5rem 0;"><?php echo htmlspecialchars($order['phone']); ?></td>
                </tr>
                <?php endif; ?>
                <?php if (!empty($order['address'])): ?>
                <tr>
                    <td style="padding: 0.5rem 0; font-weight: bold;">Address:</td>
                    <td style="padding: 0.5rem 0;"><?php echo nl2br(htmlspecialchars($order['address'])); ?></td>
                </tr>
                <?php endif; ?>
            </table>
        </div>
    </div>

    <!-- Order Items -->
    <div style="background: #fff; padding: 2rem; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1);">
        <h3>Order Items</h3>
        <div style="overflow-x: auto;">
            <table style="width: 100%; border-collapse: collapse;">
                <thead>
                    <tr style="border-bottom: 2px solid #dee2e6;">
                        <th style="padding: 1rem 0.5rem; text-align: left;">Product</th>
                        <th style="padding: 1rem 0.5rem; text-align: center;">Quantity</th>
                        <th style="padding: 1rem 0.5rem; text-align: right;">Unit Price</th>
                        <th style="padding: 1rem 0.5rem; text-align: right;">Total</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $subtotal = 0;
                    while ($item = $orderItems->fetch_assoc()):
                        $itemTotal = $item['quantity'] * $item['unit_price'];
                        $subtotal += $itemTotal;
                    ?>
                        <tr style="border-bottom: 1px solid #dee2e6;">
                            <td style="padding: 1rem 0.5rem;">
                                <div style="display: flex; align-items: center; gap: 1rem;">
                                    <?php if (!empty($item['image'])): ?>
                                        <img src="../assets/images/<?php echo htmlspecialchars($item['image']); ?>" width="50" height="50" style="object-fit: cover; border-radius: 4px;">
                                    <?php endif; ?>
                                    <div>
                                        <div style="font-weight: bold;"><?php echo htmlspecialchars($item['product_name']); ?></div>
                                        <div style="color: #666; font-size: 0.9rem;">Product ID: <?php echo $item['product_id']; ?></div>
                                    </div>
                                </div>
                            </td>
                            <td style="padding: 1rem 0.5rem; text-align: center;"><?php echo $item['quantity']; ?></td>
                            <td style="padding: 1rem 0.5rem; text-align: right;">₱<?php echo number_format($item['unit_price'], 2); ?></td>
                            <td style="padding: 1rem 0.5rem; text-align: right; font-weight: bold;">₱<?php echo number_format($itemTotal, 2); ?></td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
                <tfoot>
                    <tr style="border-top: 2px solid #dee2e6;">
                        <td colspan="3" style="padding: 1rem 0.5rem; text-align: right; font-weight: bold;">Subtotal:</td>
                        <td style="padding: 1rem 0.5rem; text-align: right; font-weight: bold;">₱<?php echo number_format($subtotal, 2); ?></td>
                    </tr>
                    <?php if (isset($order['tax_amount']) && $order['tax_amount'] > 0): ?>
                    <tr>
                        <td colspan="3" style="padding: 0.5rem; text-align: right;">Tax:</td>
                        <td style="padding: 0.5rem; text-align: right;">₱<?php echo number_format($order['tax_amount'], 2); ?></td>
                    </tr>
                    <?php endif; ?>
                    <?php if (isset($order['shipping_amount']) && $order['shipping_amount'] > 0): ?>
                    <tr>
                        <td colspan="3" style="padding: 0.5rem; text-align: right;">Shipping:</td>
                        <td style="padding: 0.5rem; text-align: right;">₱<?php echo number_format($order['shipping_amount'], 2); ?></td>
                    </tr>
                    <?php endif; ?>
                    <tr style="border-top: 2px solid #28a745; background: #f8fff9;">
                        <td colspan="3" style="padding: 1rem 0.5rem; text-align: right; font-weight: bold; color: #28a745;">Total:</td>
                        <td style="padding: 1rem 0.5rem; text-align: right; font-weight: bold; color: #28a745; font-size: 1.1rem;">₱<?php echo number_format($order['total_amount'], 2); ?></td>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>
</main>

<?php require_once '../includes/footer.php'; ?>