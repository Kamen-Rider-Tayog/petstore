<?php
require_once '../../backend/config/database.php';
require_once '../../backend/includes/header.php';
<link rel="stylesheet" href="../../assets/css/checkout.css">

require_once '../../backend/includes/cart_functions.php';

// Require login to checkout
if (!isset($_SESSION['user_id'])) {
    header('Location: login?error=Please log in to checkout');
    exit;
}

$step = isset($_GET['step']) ? (int)$_GET['step'] : 1;

// Prefill shipping info from session if present
$shipping = $_SESSION['shipping'] ?? [
    'full_name' => '',
    'address1' => '',
    'address2' => '',
    'city' => '',
    'state' => '',
    'postal_code' => '',
    'phone' => '',
    'email' => ''
];

// Persist shipping info on POST (step 1)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['checkout_step']) && $_POST['checkout_step'] === 'shipping') {
    $shipping = [
        'full_name' => trim($_POST['full_name'] ?? ''),
        'address1' => trim($_POST['address1'] ?? ''),
        'address2' => trim($_POST['address2'] ?? ''),
        'city' => trim($_POST['city'] ?? ''),
        'state' => trim($_POST['state'] ?? ''),
        'postal_code' => trim($_POST['postal_code'] ?? ''),
        'phone' => trim($_POST['phone'] ?? ''),
        'email' => trim($_POST['email'] ?? ''),
    ];

    $_SESSION['shipping'] = $shipping;
    header('Location: checkout?step=2');
    exit;
}

$cartItems = getCartItems();
$cartTotal = calculateCartTotal();
?>

<h1>Checkout</h1>

<?php if (empty($cartItems)): ?>
    <p>Your cart is empty. <a href="products">Shop products</a>.</p>
    <?php require_once '../../backend/includes/footer.php';
    return;
endif; ?>

<?php if ($step === 1): ?>
    <h2>Shipping Information</h2>
    <form method="post">
        <input type="hidden" name="checkout_step" value="shipping" />
        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px;">
            <div>
                <label>Full Name<br /><input type="text" name="full_name" value="<?php echo htmlspecialchars($shipping['full_name']); ?>" required /></label>
            </div>
            <div>
                <label>Email<br /><input type="email" name="email" value="<?php echo htmlspecialchars($shipping['email']); ?>" required /></label>
            </div>
            <div>
                <label>Phone<br /><input type="text" name="phone" value="<?php echo htmlspecialchars($shipping['phone']); ?>" required /></label>
            </div>
            <div>
                <label>City<br /><input type="text" name="city" value="<?php echo htmlspecialchars($shipping['city']); ?>" required /></label>
            </div>
            <div>
                <label>State/Province<br /><input type="text" name="state" value="<?php echo htmlspecialchars($shipping['state']); ?>" required /></label>
            </div>
            <div>
                <label>ZIP/Postal Code<br /><input type="text" name="postal_code" value="<?php echo htmlspecialchars($shipping['postal_code']); ?>" required /></label>
            </div>
            <div style="grid-column: 1 / -1;">
                <label>Address Line 1<br /><input type="text" name="address1" value="<?php echo htmlspecialchars($shipping['address1']); ?>" required style="width: 100%;" /></label>
            </div>
            <div style="grid-column: 1 / -1;">
                <label>Address Line 2 (optional)<br /><input type="text" name="address2" value="<?php echo htmlspecialchars($shipping['address2']); ?>" style="width: 100%;" /></label>
            </div>
        </div>

        <div style="margin-top: 20px; display: flex; gap: 10px;">
            <button type="submit" class="btn btn-primary">Continue to Payment</button>
            <a href="cart" class="btn">Back to Cart</a>
        </div>
    </form>

    <hr />

    <h3>Order Summary</h3>
    <ul>
        <?php foreach ($cartItems as $item): ?>
            <li><?php echo htmlspecialchars($item['product_name']); ?> × <?php echo (int)$item['quantity']; ?> — ₱<?php echo number_format($item['subtotal'], 2); ?></li>
        <?php endforeach; ?>
    </ul>
    <p><strong>Subtotal:</strong> ₱<?php echo number_format($cartTotal, 2); ?></p>

<?php else: ?>
    <h2>Payment & Review</h2>

    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px; align-items: start;">
        <div>
            <h3>Shipping Information</h3>
            <p><?php echo nl2br(htmlspecialchars($shipping['full_name'])); ?><br />
            <?php echo nl2br(htmlspecialchars($shipping['address1'] . ($shipping['address2'] ? ", " . $shipping['address2'] : ''))); ?><br />
            <?php echo htmlspecialchars($shipping['city'] . ', ' . $shipping['state'] . ' ' . $shipping['postal_code']); ?><br />
            Phone: <?php echo htmlspecialchars($shipping['phone']); ?><br />
            Email: <?php echo htmlspecialchars($shipping['email']); ?></p>

            <h3>Payment Method</h3>
            <form method="post" action="place_order.php">
                <input type="hidden" name="payment_step" value="1" />
                <div>
                    <label><input type="radio" name="payment_method" value="Credit Card" checked /> Credit Card</label>
                </div>
                <div>
                    <label><input type="radio" name="payment_method" value="Cash on Delivery" /> Cash on Delivery</label>
                </div>
                <div>
                    <label><input type="radio" name="payment_method" value="PayPal" /> PayPal</label>
                </div>

                <div id="card-details" style="margin-top: 15px; padding: 10px; border: 1px solid #ddd; border-radius: 5px;">
                    <p><strong>Credit Card Details (simulated)</strong></p>
                    <label>Card Number<br /><input type="text" name="card_number" placeholder="1234 5678 9012 3456" style="width: 100%;" /></label>
                    <div style="display:flex; gap: 10px; margin-top: 10px;">
                        <label>Expiry<br /><input type="text" name="card_expiry" placeholder="MM/YY" style="width: 100%;" /></label>
                        <label>CVC<br /><input type="text" name="card_cvc" placeholder="123" style="width: 100%;" /></label>
                    </div>
                </div>

                <div style="margin-top: 20px;">
                    <button type="submit" class="btn btn-primary">Place Order</button>
                    <a href="checkout?step=1" class="btn">Back to Shipping</a>
                </div>
            </form>
        </div>

        <div style="background: #f9f9f9; padding: 15px; border: 1px solid #ddd; border-radius: 6px;">
            <h3>Order Summary</h3>
            <ul style="padding-left: 1rem;">
                <?php foreach ($cartItems as $item): ?>
                    <li><?php echo htmlspecialchars($item['product_name']); ?> × <?php echo (int)$item['quantity']; ?> — ₱<?php echo number_format($item['subtotal'], 2); ?></li>
                <?php endforeach; ?>
            </ul>
            <p><strong>Subtotal:</strong> ₱<?php echo number_format($cartTotal, 2); ?></p>
            <p><strong>Shipping:</strong> ₱0.00 (free)</p>
            <p><strong>Total:</strong> ₱<?php echo number_format($cartTotal, 2); ?></p>
        </div>
    </div>

<?php endif; ?>

<?php require_once '../../backend/includes/footer.php'; ?>
