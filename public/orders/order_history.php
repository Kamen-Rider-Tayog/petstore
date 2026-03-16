<?php
require_once '../../backend/config/database.php';
require_once '../../backend/includes/header.php';
<link rel="stylesheet" href="../../assets/css/order_history.css">

require_once '../../backend/includes/order_functions.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login?error=Please log in to view your orders');
    exit;
}

$orders = getOrdersForCustomer($_SESSION['user_id']);

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

<h1>Order History</h1>

<?php if (empty($orders)): ?>
    <p>You have not placed any orders yet.</p>
    <p><a href="products">Start shopping</a></p>
<?php else: ?>
    <table border="1" cellpadding="8" style="width: 100%; border-collapse: collapse;">
        <thead>
            <tr>
                <th>Order ID</th>
                <th>Date</th>
                <th>Total</th>
                <th>Status</th>
                <th>Items</th>
                <th>Action</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($orders as $order): ?>
                <?php $countItems = 0; ?>
                <?php
                    $stmt = $conn->prepare('SELECT SUM(quantity) as total_items FROM order_items WHERE order_id = ?');
                    $stmt->bind_param('i', $order['id']);
                    $stmt->execute();
                    $result = $stmt->get_result()->fetch_assoc();
                    $countItems = $result['total_items'] ?? 0;
                    $stmt->close();
                ?>
                <tr>
                    <td><?php echo $order['id']; ?></td>
                    <td><?php echo date('Y-m-d H:i', strtotime($order['order_date'])); ?></td>
                    <td>₱<?php echo number_format($order['total_amount'], 2); ?></td>
                    <td><span style="padding: 4px 8px; border-radius: 4px; <?php echo statusLabel($order['status']); ?>"><?php echo ucfirst($order['status']); ?></span></td>
                    <td><?php echo $countItems; ?></td>
                    <td><a href="order_details?id=<?php echo $order['id']; ?>">View Details</a></td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
<?php endif; ?>

<?php require_once '../../backend/includes/footer.php'; ?>
