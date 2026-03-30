<?php
session_name('petstore_session');
session_start();

// Check if user is logged in as admin
if (!isset($_SESSION['is_admin']) || $_SESSION['is_admin'] !== true) {
    header('Location: ' . url('login?error=Access denied'));
    exit;
}

require_once __DIR__ . '/../../backend/config/database.php';
require_once __DIR__ . '/../../backend/functions/helpers.php';

$orderId = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if (!$orderId) {
    header('Location: orders.php');
    exit();
}

// Get order details
$stmt = $conn->prepare("
    SELECT o.*, c.first_name, c.last_name, c.email, c.phone
    FROM orders o
    LEFT JOIN customers c ON o.customer_id = c.id
    WHERE o.id = ?
");
$stmt->bind_param('i', $orderId);
$stmt->execute();
$order = $stmt->get_result()->fetch_assoc();

if (!$order) {
    header('Location: orders.php');
    exit();
}

// Check if order can be cancelled
$cancellableStatuses = ['pending', 'processing'];
$canCancel = in_array($order['status'], $cancellableStatuses);
$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['confirm_cancel'])) {
        $reason = trim($_POST['cancellation_reason'] ?? '');
        
        // Begin transaction
        $conn->begin_transaction();
        
        try {
            // Update order status to cancelled
            $stmt = $conn->prepare("UPDATE orders SET status = 'cancelled', notes = CONCAT(IFNULL(notes, ''), '\n[Cancelled on ' , NOW() , '] Reason: ', ?) WHERE id = ?");
            $stmt->bind_param('si', $reason, $orderId);
            $stmt->execute();
            
            // Restore stock quantities
            $stmt = $conn->prepare("
                SELECT oi.product_id, oi.quantity 
                FROM order_items oi 
                WHERE oi.order_id = ?
            ");
            $stmt->bind_param('i', $orderId);
            $stmt->execute();
            $items = $stmt->get_result();
            
            $updateStockStmt = $conn->prepare("UPDATE products SET quantity_in_stock = quantity_in_stock + ? WHERE id = ?");
            
            while ($item = $items->fetch_assoc()) {
                $updateStockStmt->bind_param('ii', $item['quantity'], $item['product_id']);
                $updateStockStmt->execute();
            }
            
            $conn->commit();
            
            header('Location: order_details.php?id=' . $orderId . '&message=Order cancelled successfully');
            exit();
            
        } catch (Exception $e) {
            $conn->rollback();
            $error = 'Error cancelling order: ' . $e->getMessage();
        }
    }
}

$page_title = 'Cancel Order - #' . str_pad($order['id'], 6, '0', STR_PAD_LEFT);
require_once __DIR__ . '/../includes/header.php';

echo '<link rel="stylesheet" href="/Ria-Pet-Store/admin/css/orders.css?v=' . time() . '">';
?>

<div class="admin-dashboard">
    <div class="page-header">
        <div class="header-left">
            <a href="order_details.php?id=<?php echo $order['id']; ?>" class="btn btn-outline">
                <?php echo icon('arrow-left', 16); ?> Back to Order
            </a>
        </div>
    </div>

    <?php if ($error): ?>
        <div class="message error"><?php echo htmlspecialchars($error); ?></div>
    <?php endif; ?>

    <div class="cancel-confirmation">
        <div class="warning-box">
            <?php echo icon('alert-triangle', 48); ?>
            <h3>Cancel Order</h3>
            <p>Are you sure you want to cancel this order?</p>
            <?php if (!$canCancel): ?>
                <p class="warning-text">
                    This order has status "<?php echo ucfirst($order['status']); ?>" and cannot be cancelled.
                    Only pending or processing orders can be cancelled.
                </p>
            <?php endif; ?>
        </div>

        <div class="order-summary">
            <div><h2>Order Details</h2></div>
            <div class="summary-grid">
                <div class="summary-item">
                    <span class="label">Order ID:</span>
                    <span class="value">#<?php echo str_pad($order['id'], 6, '0', STR_PAD_LEFT); ?></span>
                </div>
                <div class="summary-item">
                    <span class="label">Customer:</span>
                    <span class="value"><?php echo htmlspecialchars($order['first_name'] . ' ' . $order['last_name']); ?></span>
                </div>
                <div class="summary-item">
                    <span class="label">Email:</span>
                    <span class="value"><?php echo htmlspecialchars($order['email']); ?></span>
                </div>
                <div class="summary-item">
                    <span class="label">Phone:</span>
                    <span class="value"><?php echo htmlspecialchars($order['phone'] ?? 'N/A'); ?></span>
                </div>
                <div class="summary-item">
                    <span class="label">Order Date:</span>
                    <span class="value"><?php echo date('M d, Y g:i A', strtotime($order['created_at'])); ?></span>
                </div>
                <div class="summary-item">
                    <span class="label">Total Amount:</span>
                    <span class="value">₱<?php echo number_format($order['total_amount'], 2); ?></span>
                </div>
                <div class="summary-item">
                    <span class="label">Current Status:</span>
                    <span class="value">
                        <span class="status-badge status-<?php echo strtolower($order['status']); ?>">
                            <?php echo ucfirst($order['status']); ?>
                        </span>
                    </span>
                </div>
            </div>
        </div>

        <?php if ($canCancel): ?>
            <form method="post" class="cancel-form">
                <div class="form-group">
                    <label for="cancellation_reason">Cancellation Reason (Optional)</label>
                    <textarea id="cancellation_reason" name="cancellation_reason" rows="3" class="form-control" 
                              placeholder="Please provide a reason for cancelling this order..."></textarea>
                </div>

                <div class="action-buttons">
                    <button type="submit" name="confirm_cancel" class="btn btn-danger" onclick="return confirm('Are you sure you want to cancel this order? This action will restore product stock.')">
                        <?php echo icon('x', 16); ?> Yes, Cancel Order
                    </button>
                    <a href="order_details.php?id=<?php echo $order['id']; ?>" class="btn btn-outline">No, Go Back</a>
                </div>
            </form>
        <?php else: ?>
            <div class="action-buttons">
                <a href="order_details.php?id=<?php echo $order['id']; ?>" class="btn btn-primary">Back to Order Details</a>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>