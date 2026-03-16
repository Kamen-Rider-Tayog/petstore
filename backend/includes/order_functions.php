<?php
// backend/includes/order_functions.php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/cart_functions.php';

/**
 * Ensure the orders/order_items tables include the columns we need.
 * This is safe to run on every request (checks before altering).
 */
function ensureOrderSchema() {
    global $conn;

    // Orders table: add status, payment_method, shipping_address
    $neededColumns = [
        'status' => "ENUM('pending','processing','completed','cancelled') NOT NULL DEFAULT 'pending'",
        'payment_method' => "VARCHAR(50) DEFAULT NULL",
        'shipping_address' => "TEXT DEFAULT NULL",
    ];

    $existing = [];
    $res = $conn->query("SHOW COLUMNS FROM orders");
    while ($row = $res->fetch_assoc()) {
        $existing[$row['Field']] = true;
    }

    foreach ($neededColumns as $col => $definition) {
        if (!isset($existing[$col])) {
            $conn->query("ALTER TABLE orders ADD COLUMN {$col} {$definition}");
        }
    }

    // order_items: make sure price_at_time exists (if not, use existing price)
    $existing = [];
    $res = $conn->query("SHOW COLUMNS FROM order_items");
    while ($row = $res->fetch_assoc()) {
        $existing[$row['Field']] = true;
    }

    if (!isset($existing['price_at_time'])) {
        // if price exists, keep it; otherwise add price_at_time
        if (isset($existing['price'])) {
            $conn->query("ALTER TABLE order_items ADD COLUMN price_at_time DECIMAL(10,2) NOT NULL DEFAULT 0 AFTER price");
        } else {
            $conn->query("ALTER TABLE order_items ADD COLUMN price_at_time DECIMAL(10,2) NOT NULL DEFAULT 0");
        }
    }
}

/**
 * Create an order for the given customer.
 */
function createOrder($customer_id, $shipping_address, $payment_method) {
    global $conn;

    ensureOrderSchema();

    $items = getCartItems();
    if (empty($items)) {
        return ['success' => false, 'message' => 'Cart is empty'];
    }

    $total = 0;
    foreach ($items as $item) {
        $total += $item['subtotal'];
    }

    $conn->begin_transaction();

    try {
        $stmt = $conn->prepare("INSERT INTO orders (customer_id, order_date, total_amount, status, payment_method, shipping_address) VALUES (?, NOW(), ?, 'pending', ?, ?)");
        $stmt->bind_param('idss', $customer_id, $total, $payment_method, $shipping_address);
        $stmt->execute();
        $orderId = $stmt->insert_id;
        $stmt->close();

        $insertItem = $conn->prepare("INSERT INTO order_items (order_id, product_id, quantity, price_at_time) VALUES (?, ?, ?, ?)");
        foreach ($items as $item) {
            $insertItem->bind_param('iiid', $orderId, $item['product_id'], $item['quantity'], $item['price']);
            $insertItem->execute();
        }
        $insertItem->close();

        clearCart();

        $conn->commit();

        return ['success' => true, 'order_id' => $orderId];
    } catch (Exception $e) {
        $conn->rollback();
        return ['success' => false, 'message' => 'Unable to place order: ' . $e->getMessage()];
    }
}

/**
 * Fetch a single order with items.
 */
function getOrder($order_id) {
    global $conn;
    ensureOrderSchema();

    $stmt = $conn->prepare("SELECT * FROM orders WHERE id = ? LIMIT 1");
    $stmt->bind_param('i', $order_id);
    $stmt->execute();
    $order = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    if (!$order) {
        return null;
    }

    $items = [];
    $stmt = $conn->prepare("SELECT oi.*, p.product_name, p.image FROM order_items oi JOIN products p ON oi.product_id = p.id WHERE oi.order_id = ?");
    $stmt->bind_param('i', $order_id);
    $stmt->execute();
    $res = $stmt->get_result();

    while ($row = $res->fetch_assoc()) {
        $row['subtotal'] = $row['price_at_time'] * $row['quantity'];
        $items[] = $row;
    }

    $order['items'] = $items;
    return $order;
}

/**
 * Fetch orders for a customer.
 */
function getOrdersForCustomer($customer_id) {
    global $conn;
    ensureOrderSchema();

    $stmt = $conn->prepare("SELECT * FROM orders WHERE customer_id = ? ORDER BY order_date DESC");
    $stmt->bind_param('i', $customer_id);
    $stmt->execute();
    $orders = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    $stmt->close();

    return $orders;
}

/**
 * Cancel an order (if pending).
 */
function cancelOrder($order_id, $customer_id) {
    global $conn;

    $stmt = $conn->prepare("SELECT status FROM orders WHERE id = ? AND customer_id = ?");
    $stmt->bind_param('ii', $order_id, $customer_id);
    $stmt->execute();
    $order = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    if (!$order) {
        return ['success' => false, 'message' => 'Order not found'];
    }

    if ($order['status'] !== 'pending') {
        return ['success' => false, 'message' => 'Only pending orders can be cancelled'];
    }

    $stmt = $conn->prepare("UPDATE orders SET status = 'cancelled' WHERE id = ?");
    $stmt->bind_param('i', $order_id);
    $stmt->execute();

    return ['success' => true];
}
