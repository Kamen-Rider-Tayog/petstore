<?php
require_once '../../backend/config/database.php';
require_once '../../backend/includes/header.php';
<link rel="stylesheet" href="../../assets/css/cancel_order.css">

require_once '../../backend/includes/order_functions.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login?error=Please log in to cancel orders');
    exit;
}

$orderId = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if (!$orderId) {
    header('Location: order_history');
    exit;
}

$result = cancelOrder($orderId, $_SESSION['user_id']);

if ($result['success']) {
    header('Location: order_details?id=' . $orderId . '&message=Order+cancelled');
    exit;
}

echo '<p style="color:red;">' . htmlspecialchars($result['message'] ?? 'Unable to cancel order') . '</p>';
echo '<p><a href="order_details?id=' . $orderId . '">Back to order</a></p>';

require_once '../../backend/includes/footer.php';
