<?php
require_once '../../backend/config/database.php';
require_once '../../backend/includes/header.php';
<link rel="stylesheet" href="../../assets/css/order_confirmation.css">

require_once '../../backend/includes/order_functions.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login?error=Please log in to view your order');
    exit;
}

$orderId = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if (!$orderId) {
    header('Location: order_history');
    exit;
}

$order = getOrder($orderId);
if (!$order || $order['customer_id'] != $_SESSION['user_id']) {
    echo '<p style="color:red;">Order not found.</p>';
    require_once '../../backend/includes/footer.php';
    exit;
}

$emailMessage = "An order confirmation has been queued to be sent to your registered email.";
?>

<h1>Order Confirmation</h1>
<p>Thank you for your purchase! Your order has been placed successfully.</p>

<h2>Order #<?php echo $order['id']; ?></h2>
<p><strong>Date:</strong> <?php echo date('F j, Y, g:i a', strtotime($order['order_date'])); ?></p>
<p><strong>Status:</strong> <?php echo htmlspecialchars(ucfirst($order['status'] ?? 'pending')); ?></p>
<p><strong>Payment Method:</strong> <?php echo htmlspecialchars($order['payment_method']); ?></p>

<h3>Shipping Address</h3>
<pre style="background: #f4f4f4; padding: 10px; border: 1px solid #ddd;"><?php echo htmlspecialchars($order['shipping_address']); ?></pre>

<h3>Items</h3>
<table border="1" cellpadding="8" style="width: 100%; border-collapse: collapse;">
    <tr>
        <th>Product</th>
        <th>Qty</th>
        <th>Price</th>
        <th>Subtotal</th>
    </tr>
    <?php foreach ($order['items'] as $item): ?>
        <tr>
            <td style="display:flex; gap: 10px; align-items: center;">
                <?php $img = $item['image'] ? asset('images/' . $item['image']) : 'https://via.placeholder.com/80x80?text=No+Image'; ?>
                <img src="<?php echo $img; ?>" alt="<?php echo htmlspecialchars($item['product_name']); ?>" style="width: 60px; height: 60px; object-fit: cover;" />
                <span><?php echo htmlspecialchars($item['product_name']); ?></span>
            </td>
            <td><?php echo (int)$item['quantity']; ?></td>
            <td>₱<?php echo number_format($item['price_at_time'] ?? $item['price'], 2); ?></td>
            <td>₱<?php echo number_format($item['subtotal'], 2); ?></td>
        </tr>
    <?php endforeach; ?>
    <tr>
        <td colspan="3" style="text-align: right;"><strong>Total:</strong></td>
        <td><strong>₱<?php echo number_format($order['total_amount'], 2); ?></strong></td>
    </tr>
</table>

<p><?php echo htmlspecialchars($emailMessage); ?></p>

<div style="margin-top: 20px; display: flex; gap: 10px;">
    <a href="products" class="btn">Continue Shopping</a>
    <a href="order_history" class="btn">View Order History</a>
</div>

<?php require_once '../../backend/includes/footer.php'; ?>
