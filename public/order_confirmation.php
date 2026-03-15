<?php
require_once '../backend/config/database.php';
require_once '../backend/includes/header.php';

// Redirect if not logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: login');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: products');
    exit;
}

$customer_id = $_POST['customer_id'];
$total = $_POST['total'];
$payment_method = $_POST['payment_method'];

// Start transaction
$conn->begin_transaction();

try {
    // Create order in sales table (since you have sales table)
    $order_stmt = $conn->prepare("INSERT INTO sales (customer_id, employee_id, quantity_sold, sale_date) VALUES (?, 1, 1, NOW())");
    $order_stmt->bind_param("i", $customer_id);
    $order_stmt->execute();
    $order_id = $conn->insert_id;
    
    // Get cart items
    $cart_stmt = $conn->prepare("
        SELECT c.product_id, c.quantity, p.price 
        FROM cart c
        JOIN products p ON c.product_id = p.id
        WHERE c.customer_id = ?
    ");
    $cart_stmt->bind_param("i", $customer_id);
    $cart_stmt->execute();
    $cart_result = $cart_stmt->get_result();
    
    // Update product stock
    while ($item = $cart_result->fetch_assoc()) {
        $update_stmt = $conn->prepare("UPDATE products SET quantity_in_stock = quantity_in_stock - ? WHERE id = ?");
        $update_stmt->bind_param("ii", $item['quantity'], $item['product_id']);
        $update_stmt->execute();
    }
    
    // Clear cart
    $clear_stmt = $conn->prepare("DELETE FROM cart WHERE customer_id = ?");
    $clear_stmt->bind_param("i", $customer_id);
    $clear_stmt->execute();
    
    // Commit transaction
    $conn->commit();
    
    ?>
    <h1>Order Confirmed!</h1>
    
    <div style="text-align: center; padding: 30px; background: #d4edda; border: 1px solid #c3e6cb; border-radius: 5px; margin: 20px 0;">
        <h2 style="color: #155724;">Thank You for Your Purchase!</h2>
        <p>Your order has been placed successfully.</p>
        <p><strong>Total Amount:</strong> ₱<?php echo number_format($total, 2); ?></p>
        <p><strong>Payment Method:</strong> <?php echo ucwords(str_replace('_', ' ', $payment_method)); ?></p>
    </div>
    
    <p>You will receive an order confirmation email shortly.</p>
    
    <div style="margin-top: 30px;">
        <a href="products" style="padding: 10px 20px; background: #007bff; color: white; text-decoration: none; border-radius: 5px;">Continue Shopping</a>
        <a href="index" style="margin-left: 10px; padding: 10px 20px; background: #6c757d; color: white; text-decoration: none; border-radius: 5px;">Return to Home</a>
    </div>
    <?php
    
} catch (Exception $e) {
    $conn->rollback();
    echo "<p style='color: red;'>Error processing order: " . $e->getMessage() . "</p>";
}

$conn->close();
require_once '../backend/includes/footer.php';
?>