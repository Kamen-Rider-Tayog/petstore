<?php
// backend/includes/cart_functions.php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/database.php';

/**
 * Get customer ID (logged in or session)
 */
function getCustomerId() {
    session_start();
    
    // If user logged in, return database ID
    if(isset($_SESSION['user_id'])) {
        return $_SESSION['user_id'];
    }
    
    // For guests, create session cart ID
    if(!isset($_SESSION['guest_cart_id'])) {
        $_SESSION['guest_cart_id'] = 'guest_' . session_id();
    }
    
    return $_SESSION['guest_cart_id'];
}

/**
 * Add item to cart
 */
function addToCart($customer_id, $product_id, $quantity = 1) {
    global $conn;
    
    // Check if product exists and has stock
    $product_query = "SELECT * FROM products WHERE id = ?";
    $stmt = $conn->prepare($product_query);
    $stmt->bind_param("i", $product_id);
    $stmt->execute();
    $product = $stmt->get_result()->fetch_assoc();
    
    if(!$product) {
        return ['success' => false, 'message' => 'Product not found'];
    }
    
    if($product['quantity_in_stock'] < $quantity) {
        return ['success' => false, 'message' => 'Not enough stock available'];
    }
    
    // Check if product already in cart
    $check_query = "SELECT * FROM cart WHERE customer_id = ? AND product_id = ?";
    $stmt = $conn->prepare($check_query);
    $stmt->bind_param("si", $customer_id, $product_id);
    $stmt->execute();
    $existing = $stmt->get_result()->fetch_assoc();
    
    if($existing) {
        // Update quantity
        $new_quantity = $existing['quantity'] + $quantity;
        $update_query = "UPDATE cart SET quantity = ? WHERE id = ?";
        $stmt = $conn->prepare($update_query);
        $stmt->bind_param("ii", $new_quantity, $existing['id']);
        $stmt->execute();
    } else {
        // Insert new
        $insert_query = "INSERT INTO cart (customer_id, product_id, quantity) VALUES (?, ?, ?)";
        $stmt = $conn->prepare($insert_query);
        $stmt->bind_param("sii", $customer_id, $product_id, $quantity);
        $stmt->execute();
    }
    
    return [
        'success' => true,
        'message' => 'Product added to cart',
        'cart_count' => getCartCount($customer_id)
    ];
}

/**
 * Get all cart items with product details
 */
function getCartItems($customer_id) {
    global $conn;
    
    $query = "SELECT c.*, p.product_name, p.price, p.quantity_in_stock, p.image 
              FROM cart c 
              JOIN products p ON c.product_id = p.id 
              WHERE c.customer_id = ?
              ORDER BY c.added_date DESC";
    
    $stmt = $conn->prepare($query);
    $stmt->bind_param("s", $customer_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    $items = [];
    while($row = $result->fetch_assoc()) {
        $row['subtotal'] = $row['price'] * $row['quantity'];
        $items[] = $row;
    }
    
    return $items;
}

/**
 * Update cart item quantity
 */
function updateCartQuantity($cart_id, $quantity) {
    global $conn;
    
    if($quantity < 1) {
        return removeFromCart($cart_id);
    }
    
    $query = "UPDATE cart SET quantity = ? WHERE id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("ii", $quantity, $cart_id);
    $stmt->execute();
    
    return ['success' => true];
}

/**
 * Remove item from cart
 */
function removeFromCart($cart_id) {
    global $conn;
    
    $query = "DELETE FROM cart WHERE id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $cart_id);
    $stmt->execute();
    
    return ['success' => true];
}

/**
 * Get total number of items in cart
 */
function getCartCount($customer_id) {
    global $conn;
    
    $query = "SELECT SUM(quantity) as total FROM cart WHERE customer_id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("s", $customer_id);
    $stmt->execute();
    $result = $stmt->get_result()->fetch_assoc();
    
    return $result['total'] ?? 0;
}

/**
 * Calculate cart total
 */
function calculateCartTotal($customer_id) {
    global $conn;
    
    $query = "SELECT SUM(c.quantity * p.price) as total 
              FROM cart c 
              JOIN products p ON c.product_id = p.id 
              WHERE c.customer_id = ?";
    
    $stmt = $conn->prepare($query);
    $stmt->bind_param("s", $customer_id);
    $stmt->execute();
    $result = $stmt->get_result()->fetch_assoc();
    
    return $result['total'] ?? 0;
}

/**
 * Clear entire cart
 */
function clearCart($customer_id) {
    global $conn;
    
    $query = "DELETE FROM cart WHERE customer_id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("s", $customer_id);
    $stmt->execute();
    
    return true;
}

/**
 * Merge session cart with database cart after login
 */
function mergeCarts($session_customer_id, $db_customer_id) {
    global $conn;
    
    // Get session cart items
    $session_items = getCartItems($session_customer_id);
    
    foreach($session_items as $item) {
        // Check if item exists in database cart
        $check_query = "SELECT * FROM cart WHERE customer_id = ? AND product_id = ?";
        $stmt = $conn->prepare($check_query);
        $stmt->bind_param("si", $db_customer_id, $item['product_id']);
        $stmt->execute();
        $existing = $stmt->get_result()->fetch_assoc();
        
        if($existing) {
            // Add quantities
            $new_quantity = $existing['quantity'] + $item['quantity'];
            $update_query = "UPDATE cart SET quantity = ? WHERE id = ?";
            $stmt = $conn->prepare($update_query);
            $stmt->bind_param("ii", $new_quantity, $existing['id']);
            $stmt->execute();
        } else {
            // Insert with new customer_id
            $insert_query = "INSERT INTO cart (customer_id, product_id, quantity) VALUES (?, ?, ?)";
            $stmt = $conn->prepare($insert_query);
            $stmt->bind_param("sii", $db_customer_id, $item['product_id'], $item['quantity']);
            $stmt->execute();
        }
    }
    
    // Clear session cart
    clearCart($session_customer_id);
    
    return true;
}
?>