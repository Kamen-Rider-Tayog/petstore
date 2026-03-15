<?php
require_once '../backend/config/database.php';
require_once '../backend/includes/header.php';

// Redirect if not logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: login?error=Please log in to view cart');
    exit;
}

$customer_id = $_SESSION['user_id'];
?>

<h1>Shopping Cart</h1>

<div id="cart-container">
    <div id="loading" style="text-align: center; padding: 20px;">Loading cart...</div>
</div>

<div style="margin-top: 20px;">
    <a href="products" class="btn">Continue Shopping</a>
    <a href="checkout" class="btn" id="checkout-btn" style="display: none;">Proceed to Checkout</a>
</div>

<br>
<a href="index">Back to Home</a>

<script src="../assets/js/cart.js"></script>
<script>
function loadCart() {
    fetch('../backend/api/get_cart?customer_id=<?php echo $customer_id; ?>')
        .then(response => response.json())
        .then(data => {
            const container = document.getElementById('cart-container');
            const checkoutBtn = document.getElementById('checkout-btn');
            
            if (!data.success || data.items.length === 0) {
                container.innerHTML = '<p>Your cart is empty.</p>';
                checkoutBtn.style.display = 'none';
                return;
            }
            
            let html = `
                <table border="1" cellpadding="8" style="width:100%;">
                    <tr>
                        <th>Product</th>
                        <th>Price</th>
                        <th>Quantity</th>
                        <th>Subtotal</th>
                        <th>Action</th>
                    </tr>
            `;
            
            data.items.forEach(item => {
                html += `
                    <tr>
                        <td>${item.product_name}</td>
                        <td>₱${parseFloat(item.price).toFixed(2)}</td>
                        <td>
                            <button onclick="updateCartQuantity(${item.cart_id}, 'decrease', ${<?php echo $customer_id; ?>})">-</button>
                            ${item.quantity}
                            <button onclick="updateCartQuantity(${item.cart_id}, 'increase', ${<?php echo $customer_id; ?>})">+</button>
                        </td>
                        <td>₱${parseFloat(item.subtotal).toFixed(2)}</td>
                        <td>
                            <button onclick="removeFromCart(${item.cart_id}, ${<?php echo $customer_id; ?>})" style="background: #dc3545; color: white;">Remove</button>
                        </td>
                    </tr>
                `;
            });
            
            html += `
                    <tr>
                        <td colspan="3" style="text-align: right;"><strong>Total:</strong></td>
                        <td><strong>₱${parseFloat(data.total).toFixed(2)}</strong></td>
                        <td></td>
                    </tr>
                </table>
            `;
            
            container.innerHTML = html;
            checkoutBtn.style.display = 'inline-block';
        })
        .catch(error => {
            console.error('Error:', error);
            document.getElementById('cart-container').innerHTML = '<p style="color: red;">Error loading cart</p>';
        });
}

// Load cart on page load
loadCart();
</script>

<?php require_once '../backend/includes/footer.php'; ?>