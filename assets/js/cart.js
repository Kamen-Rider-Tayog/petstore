// cart.js
// Requires BASE_URL defined by header.php

function showToast(message, type = "info") {
  // Simple toast via alert for now; can be enhanced.
  alert(message);
}

function addToCart(productId, quantity = 1) {
  const qtySelect = document.getElementById(`qty_${productId}`);
  if (qtySelect) {
    quantity = parseInt(qtySelect.value, 10) || 1;
  }

  const button = document.querySelector(`[data-add-to-cart="${productId}"]`);
  if (button) {
    button.disabled = true;
    button.textContent = "Adding...";
  }

  fetch(BASE_URL + "/backend/api/add_to_cart.php", {
    method: "POST",
    headers: {
      "Content-Type": "application/json",
    },
    body: JSON.stringify({
      product_id: productId,
      quantity: quantity,
    }),
  })
    .then((response) => response.json())
    .then((data) => {
      if (data.success) {
        showToast(data.message, "success");
        updateCartCount();
      } else {
        showToast(data.message || "Could not add to cart", "error");
      }
    })
    .catch((error) => {
      console.error("Error:", error);
      showToast("Error adding to cart", "error");
    })
    .finally(() => {
      if (button) {
        button.disabled = false;
        button.textContent = "Add to Cart";
      }
    });
}

function updateCartCount() {
  fetch(BASE_URL + "/backend/api/cart_count.php")
    .then((response) => response.json())
    .then((data) => {
      const cartCountElement = document.getElementById("cart-count");
      if (cartCountElement) {
        cartCountElement.textContent = data.count || 0;
      }
    })
    .catch((error) => console.error("Error updating cart count:", error));
}

function updateQuantity(cartId, action, quantity = null) {
  const payload = { cart_id: cartId, action };
  if (quantity !== null) {
    payload.quantity = quantity;
  }

  return fetch(BASE_URL + "/backend/api/update_cart.php", {
    method: "POST",
    headers: {
      "Content-Type": "application/json",
    },
    body: JSON.stringify(payload),
  })
    .then((res) => res.json())
    .then((data) => {
      if (data.success) {
        updateCartCount();
        if (typeof renderMiniCart === "function") {
          renderMiniCart();
        }
      }
      return data;
    });
}

function removeItem(cartId) {
  if (!confirm("Remove this item from cart?")) {
    return;
  }

  fetch(BASE_URL + "/backend/api/remove_from_cart.php", {
    method: "POST",
    headers: {
      "Content-Type": "application/json",
    },
    body: JSON.stringify({ cart_id: cartId }),
  })
    .then((res) => res.json())
    .then((data) => {
      if (data.success) {
        updateCartCount();
        if (typeof renderMiniCart === "function") {
          renderMiniCart();
        }

        // If on cart page, refresh it
        if (window.location.pathname.includes("cart.php")) {
          window.location.reload();
        }
      } else {
        showToast(data.message || "Could not remove item", "error");
      }
    });
}

function getCart() {
  return fetch(BASE_URL + "/backend/api/get_cart.php").then((res) =>
    res.json(),
  );
}

function renderMiniCart() {
  const itemsContainer = document.getElementById("miniCartItems");
  const countEl = document.getElementById("miniCartCount");
  const totalEl = document.getElementById("miniCartTotal");

  if (!itemsContainer) return;

  getCart()
    .then((data) => {
      if (!data.success) {
        itemsContainer.innerHTML =
          '<p class="mini-cart-empty">Unable to load cart.</p>';
        return;
      }

      const items = data.items || [];
      const total = data.total || 0;

      if (countEl) {
        countEl.textContent = items.length;
      }
      if (totalEl) {
        totalEl.textContent = `₱${parseFloat(total).toFixed(2)}`;
      }

      if (items.length === 0) {
        itemsContainer.innerHTML =
          '<p class="mini-cart-empty">Your cart is empty.</p>';
        return;
      }

      const preview = items
        .slice(0, 3)
        .map((item) => {
          const name = item.product_name || "Item";
          const qty = item.quantity || 1;
          return `<div class="mini-cart-item"><span class="mini-cart-item-name">${name}</span> <span class="mini-cart-item-qty">x${qty}</span></div>`;
        })
        .join("");

      let more = "";
      if (items.length > 3) {
        more = `<div class="mini-cart-more">+${items.length - 3} more</div>`;
      }

      itemsContainer.innerHTML = preview + more;
    })
    .catch(() => {
      itemsContainer.innerHTML =
        '<p class="mini-cart-empty">Unable to load cart.</p>';
    });
}

function attachAddToCartHandlers() {
  document.querySelectorAll("[data-add-to-cart]").forEach((button) => {
    button.removeEventListener("click", button._addToCartHandler);

    const handler = (e) => {
      e.preventDefault();
      const productId = button.getAttribute("data-add-to-cart");
      addToCart(productId);
    };

    button._addToCartHandler = handler;
    button.addEventListener("click", handler);
  });
}

// Attach add-to-cart behavior to buttons (if present)
document.addEventListener("DOMContentLoaded", () => {
  attachAddToCartHandlers();
});
