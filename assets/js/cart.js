// Add to cart function
function addToCart(productId, customerId) {
    const qtySelect = document.getElementById(`qty_${productId}`);
    const quantity = qtySelect ? parseInt(qtySelect.value) : 1;
    
    fetch('../backend/api/add_to_cart', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify({
            product_id: productId,
            customer_id: customerId,
            quantity: quantity
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert('Product added to cart!');
            updateCartCount(customerId);
        } else {
            alert('Error: ' + data.message);
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Error adding to cart');
    });
}

// Update cart count
function updateCartCount(customerId) {
    fetch('../backend/api/cart_count?customer_id=' + customerId)
        .then(response => response.json())
        .then(data => {
            const cartCountElement = document.getElementById('cart-count');
            if (cartCountElement) {
                cartCountElement.textContent = data.count || 0;
            }
        })
        .catch(error => console.error('Error updating cart count:', error));
}

// Update cart quantity
function updateCartQuantity(cartId, action, customerId) {
    fetch('../backend/api/update_cart', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify({
            cart_id: cartId,
            action: action,
            customer_id: customerId
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Reload cart page if on cart page
            if (window.location.pathname.includes('cart')) {
                location.reload();
            } else {
                updateCartCount(customerId);
            }
        }
    })
    .catch(error => console.error('Error:', error));
}

// Remove from cart
function removeFromCart(cartId, customerId) {
    if (confirm('Remove this item from cart?')) {
        updateCartQuantity(cartId, 'remove', customerId);
    }
}