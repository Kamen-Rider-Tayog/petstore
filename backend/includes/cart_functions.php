<?php
// backend/includes/cart_functions.php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/database.php';

if (session_status() === PHP_SESSION_NONE) {
    session_name(Config::get('SESSION_NAME', 'petstore_session'));
    session_start();
}

/**
 * Determine if the user is logged in.
 */
function isUserLoggedIn() {
    return isset($_SESSION['user_id']);
}

/**
 * Get the active customer identifier.
 * Returns integer user ID for logged in users. Returns null for guests.
 */
function getCustomerId() {
    return $_SESSION['user_id'] ?? null;
}

/**
 * Get the session cart array for guest users.
 */
function &getSessionCart() {
    if (!isset($_SESSION['cart']) || !is_array($_SESSION['cart'])) {
        $_SESSION['cart'] = [];
    }

    return $_SESSION['cart'];
}

/**
 * Add item to cart (supports logged-in users and guests).
 */
function addToCart($product_id, $quantity = 1) {
    $quantity = max(1, (int)$quantity);

    if (isUserLoggedIn()) {
        return addToCartForUser(getCustomerId(), $product_id, $quantity);
    }

    return addToCartForGuest($product_id, $quantity);
}

/**
 * Add item to DB cart for a logged-in user.
 */
function addToCartForUser($customer_id, $product_id, $quantity = 1) {
    global $conn;

    // Validate product
    $product_query = "SELECT id, quantity_in_stock FROM products WHERE id = ?";
    $stmt = $conn->prepare($product_query);
    $stmt->bind_param("i", $product_id);
    $stmt->execute();
    $product = $stmt->get_result()->fetch_assoc();

    if (!$product) {
        return ['success' => false, 'message' => 'Product not found'];
    }

    if ($product['quantity_in_stock'] < $quantity) {
        return ['success' => false, 'message' => 'Not enough stock available'];
    }

    $check_query = "SELECT * FROM cart WHERE customer_id = ? AND product_id = ?";
    $stmt = $conn->prepare($check_query);
    $stmt->bind_param("ii", $customer_id, $product_id);
    $stmt->execute();
    $existing = $stmt->get_result()->fetch_assoc();

    if ($existing) {
        $new_quantity = $existing['quantity'] + $quantity;
        $update_query = "UPDATE cart SET quantity = ? WHERE id = ?";
        $stmt = $conn->prepare($update_query);
        $stmt->bind_param("ii", $new_quantity, $existing['id']);
        $stmt->execute();
    } else {
        $insert_query = "INSERT INTO cart (customer_id, product_id, quantity) VALUES (?, ?, ?)";
        $stmt = $conn->prepare($insert_query);
        $stmt->bind_param("iii", $customer_id, $product_id, $quantity);
        $stmt->execute();
    }

    return [
        'success' => true,
        'message' => 'Product added to cart',
        'cart_count' => getCartCount()
    ];
}

/**
 * Add item to session cart for guests.
 */
function addToCartForGuest($product_id, $quantity = 1) {
    $cart = &getSessionCart();

    if (!isset($cart[$product_id])) {
        $cart[$product_id] = 0;
    }

    $cart[$product_id] += $quantity;

    return [
        'success' => true,
        'message' => 'Product added to cart',
        'cart_count' => getCartCount()
    ];
}

/**
 * Get all cart items with product details.
 */
function getCartItems() {
    if (isUserLoggedIn()) {
        return getCartItemsForUser(getCustomerId());
    }

    return getCartItemsForGuest();
}

/**
 * Get cart items from DB for logged-in user.
 */
function getCartItemsForUser($customer_id) {
    global $conn;

    $query = "SELECT c.id as cart_id, c.quantity, p.id as product_id, p.product_name, p.price, p.quantity_in_stock
              FROM cart c
              JOIN products p ON c.product_id = p.id
              WHERE c.customer_id = ?
              ORDER BY c.added_date DESC";

    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $customer_id);
    $stmt->execute();
    $result = $stmt->get_result();

    $items = [];
    while ($row = $result->fetch_assoc()) {
        $row['subtotal'] = $row['price'] * $row['quantity'];
        $row['image'] = $row['product_id'] . '.jpg'; // Default image filename
        $items[] = $row;
    }

    return $items;
}

/**
 * Get cart items from session for guests.
 */
function getCartItemsForGuest() {
    $cart = getSessionCart();

    if (empty($cart)) {
        return [];
    }

    global $conn;

    $product_ids = array_keys($cart);
    $placeholders = implode(',', array_fill(0, count($product_ids), '?'));

    $query = "SELECT id as product_id, product_name, price, quantity_in_stock FROM products WHERE id IN ($placeholders)";
    $stmt = $conn->prepare($query);

    $types = str_repeat('i', count($product_ids));
    $stmt->bind_param($types, ...$product_ids);
    $stmt->execute();
    $result = $stmt->get_result();

    $items = [];
    while ($row = $result->fetch_assoc()) {
        $quantity = $cart[$row['product_id']] ?? 0;
        $row['quantity'] = $quantity;
        $row['cart_id'] = $row['product_id'];
        $row['subtotal'] = $row['price'] * $quantity;
        $row['image'] = $row['product_id'] . '.jpg'; // Default image filename
        $items[] = $row;
    }

    return $items;
}

/**
 * Update cart item quantity.
 */
function updateCartQuantity($cart_id, $quantity) {
    $quantity = max(1, (int)$quantity);

    if (isUserLoggedIn()) {
        return updateCartQuantityForUser(getCustomerId(), $cart_id, $quantity);
    }

    return updateCartQuantityForGuest($cart_id, $quantity);
}

function updateCartQuantityForUser($customer_id, $cart_id, $quantity) {
    global $conn;

    $query = "UPDATE cart SET quantity = ? WHERE id = ? AND customer_id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("iii", $quantity, $cart_id, $customer_id);
    $stmt->execute();

    return ['success' => true];
}

function updateCartQuantityForGuest($product_id, $quantity) {
    $cart = &getSessionCart();
    if (!isset($cart[$product_id])) {
        return ['success' => false, 'message' => 'Item not found in cart'];
    }

    $cart[$product_id] = $quantity;

    return ['success' => true];
}

/**
 * Remove item from cart.
 */
function removeFromCart($cart_id) {
    if (isUserLoggedIn()) {
        return removeFromCartForUser(getCustomerId(), $cart_id);
    }

    return removeFromCartForGuest($cart_id);
}

function removeFromCartForUser($customer_id, $cart_id) {
    global $conn;

    $query = "DELETE FROM cart WHERE id = ? AND customer_id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("ii", $cart_id, $customer_id);
    $stmt->execute();

    return ['success' => true];
}

function removeFromCartForGuest($product_id) {
    $cart = &getSessionCart();
    if (isset($cart[$product_id])) {
        unset($cart[$product_id]);
    }

    return ['success' => true];
}

/**
 * Get total number of items in cart.
 */
function getCartCount() {
    if (isUserLoggedIn()) {
        return getCartCountForUser(getCustomerId());
    }

    return getCartCountForGuest();
}

function getCartCountForUser($customer_id) {
    global $conn;

    $query = "SELECT SUM(quantity) as total FROM cart WHERE customer_id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $customer_id);
    $stmt->execute();
    $result = $stmt->get_result()->fetch_assoc();

    return $result['total'] ?? 0;
}

function getCartCountForGuest() {
    $cart = getSessionCart();
    return array_sum($cart);
}

/**
 * Calculate cart total.
 */
function calculateCartTotal() {
    $items = getCartItems();
    $total = 0;

    foreach ($items as $item) {
        $total += ($item['price'] * $item['quantity']);
    }

    return $total;
}

/**
 * Clear entire cart.
 */
function clearCart() {
    if (isUserLoggedIn()) {
        return clearCartForUser(getCustomerId());
    }

    return clearCartForGuest();
}

function clearCartForUser($customer_id) {
    global $conn;

    $query = "DELETE FROM cart WHERE customer_id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $customer_id);
    $stmt->execute();

    return true;
}

function clearCartForGuest() {
    $_SESSION['cart'] = [];
    return true;
}

/**
 * Merge session cart into database cart after login.
 */
function mergeSessionCartIntoUser($user_id) {
    if (!isset($_SESSION['cart']) || !is_array($_SESSION['cart'])) {
        return;
    }

    $cart = $_SESSION['cart'];
    foreach ($cart as $product_id => $quantity) {
        addToCartForUser($user_id, $product_id, $quantity);
    }

    // Clear session cart
    $_SESSION['cart'] = [];
}
