<?php
require_once '../../backend/config/database.php';
require_once '../../backend/includes/header.php';
<link rel="stylesheet" href="../../assets/css/cart.css">

?>

<h1>Shopping Cart</h1>

<div id="cart-container">
    <div id="loading" style="text-align: center; padding: 20px;">Loading cart...</div>
</div>

<div style="margin-top: 20px; display: flex; gap: 10px; flex-wrap: wrap;">
    <a href="products" class="btn">Continue Shopping</a>
    <a href="checkout" class="btn" id="checkout-btn" style="display: none;">Proceed to Checkout</a>
</div>

<br>
<a href="index">Back to Home</a>

<script src="../../assets/js/cart.js"></script>
<script>
function renderCart(items, total) {
    const container = document.getElementById('cart-container');
    const checkoutBtn = document.getElementById('checkout-btn');

    if (!items || items.length === 0) {
        container.innerHTML = '<p>Your cart is empty.</p>';
        checkoutBtn.style.display = 'none';
        return;
    }

    let html = `
        <table border="1" cellpadding="8" style="width:100%; border-collapse: collapse;">
            <thead>
                <tr>
                    <th>Product</th>
                    <th>Price</th>
                    <th>Quantity</th>
                    <th>Subtotal</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
    `;

    items.forEach(item => {
        html += `
            <tr>
                <td style="display:flex; gap: 10px; align-items: center;">
                    <img src="${item.image ? '../../assets/images/' + item.image : 'https://via.placeholder.com/80x80?text=No+Image'}" alt="${item.product_name}" style="width: 80px; height: 80px; object-fit: cover; border: 1px solid #ddd;" />
                    <div>
                        <strong>${item.product_name}</strong><br />
                        <small>In stock: ${item.quantity_in_stock}</small>
                    </div>
                </td>
                <td>₱${parseFloat(item.price).toFixed(2)}</td>
                <td>
                    <button onclick="updateQuantity(${item.cart_id}, 'decrease')" style="padding: 4px 8px;">-</button>
                    <span style="margin: 0 10px;">${item.quantity}</span>
                    <button onclick="updateQuantity(${item.cart_id}, 'increase')" style="padding: 4px 8px;">+</button>
                </td>
                <td>₱${parseFloat(item.subtotal).toFixed(2)}</td>
                <td>
                    <button onclick="removeItem(${item.cart_id})" style="background: #dc3545; color: white; padding: 6px 10px; border: none; cursor: pointer;">Remove</button>
                </td>
            </tr>
        `;
    });

    html += `
            <tr>
                <td colspan="3" style="text-align: right;"><strong>Total:</strong></td>
                <td colspan="2"><strong>₱${parseFloat(total).toFixed(2)}</strong></td>
            </tr>
        </tbody>
    </table>`;

    container.innerHTML = html;
    checkoutBtn.style.display = 'inline-block';
}

function loadCart() {
    document.getElementById('cart-container').innerHTML = '<div id="loading" style="text-align: center; padding: 20px;">Loading cart...</div>';

    getCart().then(data => {
        if (!data.success || !data.items || data.items.length === 0) {
            renderCart([], 0);
            return;
        }

        renderCart(data.items, data.total);
    }).catch(err => {
        console.error(err);
        document.getElementById('cart-container').innerHTML = '<p style="color: red;">Error loading cart.</p>';
    });
}

loadCart();
updateCartCount();
</script>

<?php require_once '../../backend/includes/footer.php'; ?>
