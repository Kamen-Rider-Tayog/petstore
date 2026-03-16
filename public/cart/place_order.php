<?php
require_once '../../backend/config/database.php';
require_once '../../backend/includes/header.php';
<link rel="stylesheet" href="../../assets/css/place_order.css">

require_once '../../backend/includes/order_functions.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login?error=Please log in to place an order');
    exit;
}

$shipping = $_SESSION['shipping'] ?? null;
if (!$shipping) {
    header('Location: checkout?step=1');
    exit;
}

$paymentMethod = $_POST['payment_method'] ?? 'Cash on Delivery';

$result = createOrder($_SESSION['user_id'], json_encode($shipping), $paymentMethod);

if (!$result['success']) {
    echo '<p style="color: red;">' . htmlspecialchars($result['message'] ?? 'Unable to place order') . '</p>';
    echo '<p><a href="checkout?step=2">Back to Checkout</a></p>';
    require_once '../../backend/includes/footer.php';
    exit;
}

$orderId = $result['order_id'];

// Clear shipping info from session
unset($_SESSION['shipping']);

header('Location: order_confirmation?id=' . $orderId);
exit;
