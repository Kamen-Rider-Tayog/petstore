<?php
require_once '../backend/config/database.php';
require_once '../backend/includes/header.php';

// Redirect if not logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: login?error=Please log in to checkout');
    exit;
}

$customer_id = $_SESSION['user_id'];

// Get customer info
$stmt = $conn->prepare("SELECT * FROM customers WHERE id = ?");
$stmt->bind_param("i", $customer_id);
$stmt->execute();
$customer = $stmt->get_result()->fetch_assoc();

// Get cart items
$cart_query = "
    SELECT 
        c.id as cart_id,
        c.quantity,
        p.id as product_id,
        p.product_name,
        p.price,
        (c.quantity * p.price) as subtotal
    FROM cart c
    JOIN products p ON c.product_id = p.id
    WHERE c.customer_id = ?
";

$stmt = $conn->prepare($cart_query);
$stmt->bind_param("i", $customer_id);
$stmt->execute();
$cart_result = $stmt->get_result();

$cart_items = [];
$total = 0;
while ($row = $cart_result->fetch_assoc()) {
    $cart_items[] = $row;
    $total += $row['subtotal'];
}

// If cart is empty, redirect to products
if (empty($cart_items)) {
    header('Location: products?message=Your cart is empty');
    exit;
}
?>

<h1>Checkout</h1>

<div style="display: flex; gap: 30px;">
    <div style="flex: 2;">
        <h2>Order Summary</h2>
        <table border="1" cellpadding="8" style="width:100%;">
            <tr>
                <th>Product</th>
                <th>Quantity</th>
                <th>Price</th>
                <th>Subtotal</th>
            </tr>
            <?php foreach($cart_items as $item): ?>
            <tr>
                <td><?php echo htmlspecialchars($item['product_name']); ?></td>
                <td><?php echo $item['quantity']; ?></td>
                <td>₱<?php echo number_format($item['price'], 2); ?></td>
                <td>₱<?php echo number_format($item['subtotal'], 2); ?></td>
            </tr>
            <?php endforeach; ?>
            <tr>
                <td colspan="3" style="text-align: right;"><strong>Total:</strong></td>
                <td><strong>₱<?php echo number_format($total, 2); ?></strong></td>
            </tr>
        </table>
    </div>
    
    <div style="flex: 1;">
        <h2>Customer Information</h2>
        <form action="order_confirmation" method="POST">
            <div style="margin-bottom: 10px;">
                <label>Name:</label><br>
                <input type="text" value="<?php echo htmlspecialchars($customer['first_name'] . ' ' . $customer['last_name']); ?>" readonly style="width:100%; padding:5px;">
            </div>
            
            <div style="margin-bottom: 10px;">
                <label>Email:</label><br>
                <input type="email" value="<?php echo htmlspecialchars($customer['email']); ?>" readonly style="width:100%; padding:5px;">
            </div>
            
            <div style="margin-bottom: 10px;">
                <label>Phone:</label><br>
                <input type="text" value="<?php echo htmlspecialchars($customer['phone']); ?>" readonly style="width:100%; padding:5px;">
            </div>
            
            <div style="margin-bottom: 10px;">
                <label for="payment_method">Payment Method:</label><br>
                <select id="payment_method" name="payment_method" required style="width:100%; padding:5px;">
                    <option value="credit_card">Credit Card</option>
                    <option value="cash">Cash on Delivery</option>
                </select>
            </div>
            
            <input type="hidden" name="customer_id" value="<?php echo $customer_id; ?>">
            <input type="hidden" name="total" value="<?php echo $total; ?>">
            
            <button type="submit" style="width:100%; padding:10px; background: #28a745; color: white; border: none; cursor: pointer;">
                Place Order
            </button>
        </form>
    </div>
</div>

<br>
<a href="cart">← Back to Cart</a>

<?php 
$stmt->close();
$conn->close();
require_once '../backend/includes/footer.php'; 
?>