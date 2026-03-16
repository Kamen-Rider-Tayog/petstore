<?php
require_once '../../backend/config/database.php';
require_once '../../backend/includes/header.php';
<link rel="stylesheet" href="../../assets/css/order_details.css">

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

function statusLabel($status) {
    $map = [
        'pending' => 'background: #fcebbe; color: #8a6d3b;',
        'processing' => 'background: #d9edf7; color: #31708f;',
        'completed' => 'background: #dff0d8; color: #3c763d;',
        'cancelled' => 'background: #f2dede; color: #a94442;',
    ];
    return $map[$status] ?? 'background: #eee; color: #333;';
}
?>

<h1>Order Details</h1>

<p><strong>Order #</strong> <?php echo $order['id']; ?></p>
<p><strong>Date:</strong> <?php echo date('F j, Y g:i a', strtotime($order['order_date'])); ?></p>
<p><strong>Status:</strong> <span style="padding: 4px 8px; border-radius: 4px; <?php echo statusLabel($order['status']); ?>"><?php echo ucfirst($order['status']); ?></span></p>
<p><strong>Total:</strong> ₱<?php echo number_format($order['total_amount'], 2); ?></p>
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
</table>

<div style="margin-top: 20px; display: flex; gap: 10px;">
    <a href="order_history" class="btn">Back to Orders</a>
    <?php if ($order['status'] === 'pending'): ?>
        <a href="cancel_order?id=<?php echo $order['id']; ?>" class="btn btn-warning" onclick="return confirm('Are you sure you want to cancel this order?');">Cancel Order</a>
    <?php endif; ?>
    <a href="products" class="btn">Continue Shopping</a>
</div>

<?php require_once '../../backend/includes/footer.php'; ?>
