/**
 * Cart Module
 * Handles all cart-related functionality
 */

const CartModule = (function() {
  
  /**
   * Show a toast notification
   */
  function showToast(message, type) {
    type = type || 'info';
    if (typeof window.showNotification === 'function') {
      window.showNotification(message, type);
      return;
    }
    // Fallback
    const el = document.createElement('div');
    el.className = 'notification notification-' + type;
    el.innerHTML = '<div class="notification-content"><span class="notification-message">' +
        message + '</span></div>';
    document.body.appendChild(el);
    setTimeout(() => el.classList.add('active'), 10);
    setTimeout(() => {
      el.classList.remove('active');
      setTimeout(() => el.parentNode && el.parentNode.removeChild(el), 300);
    }, 3500);
  }

  /**
   * Add a product to the cart
   */
  function addToCart(productId, quantity) {
    quantity = quantity || 1;
    const qtySelect = document.getElementById('qty_' + productId);
    if (qtySelect) quantity = parseInt(qtySelect.value, 10) || 1;

    const button = document.querySelector('[data-add-to-cart="' + productId + '"]');
    if (button) { button.disabled = true; button.textContent = 'Adding...'; }

    fetch(BASE_URL + 'backend/api/add_to_cart.php', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ product_id: productId, quantity: quantity })
    })
    .then(r => r.json())
    .then(data => {
      if (data.success) {
        showToast(data.message || 'Added to cart!', 'success');
        updateCartCount();
        renderMiniCart();
      } else {
        showToast(data.message || 'Could not add to cart', 'error');
      }
    })
    .catch(() => showToast('Error adding to cart', 'error'))
    .finally(() => {
      if (button) { button.disabled = false; button.textContent = 'Add to Cart'; }
    });
  }

  /**
   * Update the cart badge count
   */
  function updateCartCount() {
    fetch(BASE_URL + 'backend/api/cart_count.php')
    .then(r => r.json())
    .then(data => {
      document.querySelectorAll('.cart-count, #cart-count, .cart-count-badge').forEach(el => {
        if (data.count > 0) {
          el.textContent = data.count;
          el.style.display = 'inline-flex';
        } else {
          el.style.display = 'none';
        }
      });
    })
    .catch(() => {});
  }

  /**
   * Update item quantity in cart
   */
  function updateQuantity(cartId, action, quantity) {
    const payload = { cart_id: cartId, action: action };
    if (quantity != null) payload.quantity = quantity;

    return fetch(BASE_URL + 'backend/api/update_cart.php', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify(payload)
    })
    .then(r => r.json())
    .then(data => {
      if (data.success) { updateCartCount(); renderMiniCart(); }
      return data;
    });
  }

  /**
   * Remove item from cart
   */
  function removeItem(cartId) {
    if (!confirm('Remove this item from cart?')) return;

    fetch(BASE_URL + 'backend/api/remove_from_cart.php', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ cart_id: cartId })
    })
    .then(r => r.json())
    .then(data => {
      if (data.success) {
        updateCartCount();
        renderMiniCart();
        if (window.location.pathname.indexOf('/cart') !== -1) {
          window.location.reload();
        }
      } else {
        showToast(data.message || 'Could not remove item', 'error');
      }
    });
  }

  /**
   * Fetch cart data from server
   */
  function getCart() {
    return fetch(BASE_URL + 'backend/api/get_cart.php').then(r => r.json());
  }

  /**
   * Render the mini-cart dropdown
   */
  function renderMiniCart() {
    const itemsEl = document.getElementById('miniCartItems');
    const countEl = document.getElementById('miniCartCount');
    const totalEl = document.getElementById('miniCartTotal');
    if (!itemsEl) return;

    getCart().then(data => {
      if (!data.success) {
        itemsEl.innerHTML = '<p class="mini-cart-empty">Unable to load cart.</p>';
        return;
      }

      const items = data.items || [];
      const total = parseFloat(data.total) || 0;

      if (countEl) countEl.textContent = items.length;
      if (totalEl) totalEl.innerHTML = '₱' + total.toFixed(2);

      if (items.length === 0) {
        itemsEl.innerHTML = '<p class="mini-cart-empty">Your cart is empty.</p>';
        return;
      }

      let html = items.slice(0, 3).map(item => {
        return '<div class="mini-cart-item">' +
          '<span class="mini-cart-item-name">' + (item.product_name || 'Item') + '</span>' +
          '<span class="mini-cart-item-qty">x' + (item.quantity || 1) + '</span>' +
          '</div>';
      }).join('');

      if (items.length > 3) {
        html += '<div class="mini-cart-more">+' + (items.length - 3) + ' more</div>';
      }

      itemsEl.innerHTML = html;
    })
    .catch(() => {
      itemsEl.innerHTML = '<p class="mini-cart-empty">Unable to load cart.</p>';
    });
  }

  /**
   * Attach data-add-to-cart click handlers
   */
  function attachAddToCartHandlers() {
    document.querySelectorAll('[data-add-to-cart]').forEach(button => {
      if (button._cartBound) return;
      button._cartBound = true;
      button.addEventListener('click', function(e) {
        e.preventDefault();
        addToCart(this.getAttribute('data-add-to-cart'));
      });
    });
  }

  // Public API
  return {
    addToCart,
    updateCartCount,
    updateQuantity,
    removeItem,
    getCart,
    renderMiniCart,
    attachAddToCartHandlers
  };
})();

// Make it globally available
window.CartModule = CartModule;

// Auto-initialize on page load
document.addEventListener('DOMContentLoaded', () => {
  CartModule.updateCartCount();
  CartModule.attachAddToCartHandlers();
  CartModule.renderMiniCart();
});