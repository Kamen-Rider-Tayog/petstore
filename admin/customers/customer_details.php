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

$customerId = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if (!$customerId) {
    header('Location: customers.php');
    exit();
}

// Get customer details
$stmt = $conn->prepare("
    SELECT c.*,
           COUNT(o.id) as total_orders,
           COALESCE(SUM(o.total_amount), 0) as total_spent,
           MAX(o.created_at) as last_order_date
    FROM customers c
    LEFT JOIN orders o ON c.id = o.customer_id
    WHERE c.id = ?
    GROUP BY c.id
");
$stmt->bind_param('i', $customerId);
$stmt->execute();
$customer = $stmt->get_result()->fetch_assoc();

if (!$customer) {
    header('Location: customers.php');
    exit();
}

// Get recent orders
$stmt = $conn->prepare("
    SELECT o.id, o.total_amount, o.status, o.created_at
    FROM orders o
    WHERE o.customer_id = ?
    ORDER BY o.created_at DESC
    LIMIT 5
");
$stmt->bind_param('i', $customerId);
$stmt->execute();
$recentOrders = $stmt->get_result();

$page_title = 'Customer Details';
require_once __DIR__ . '/../includes/header.php';

// Admin CSS
echo '<link rel="stylesheet" href="/Ria-Pet-Store/admin/css/customers.css?v=' . time() . '">';
?>

<div class="admin-dashboard">
    <!-- Header -->
    <div class="customer-header">
        <div class="customer-avatar">
            <div class="avatar-circle">
                <?php echo icon('user', 40); ?>
            </div>
        </div>
        <div class="customer-info">
            <h1><?php echo htmlspecialchars($customer['first_name'] . ' ' . $customer['last_name']); ?></h1>
            <p class="customer-email"><?php echo htmlspecialchars($customer['email']); ?></p>
            <?php if (!empty($customer['phone'])): ?>
                <p class="customer-phone"><?php echo htmlspecialchars($customer['phone']); ?></p>
            <?php endif; ?>
        </div>
        <div class="customer-actions">
            <a href="customer_edit.php?id=<?php echo $customer['id']; ?>" class="btn btn-outline">
                <?php echo icon('edit', 16); ?> Edit
            </a>
            <a href="customers.php" class="btn btn-outline">
                <?php echo icon('arrow-left', 16); ?> Back
            </a>
        </div>
    </div>

    <!-- Stats -->
    <div class="stats-grid">
        <div class="stat-card">
            <div class="stat-header">
                <?php echo icon('package', 32); ?>
                <h3>Orders</h3>
            </div>
            <div class="stat-value"><?php echo $customer['total_orders']; ?></div>
        </div>
        <div class="stat-card">
            <div class="stat-header">
                <?php echo icon('credit-card', 32); ?>
                <h3>Total Spent</h3>
            </div>
            <div class="stat-value">₱<?php echo number_format($customer['total_spent'], 2); ?></div>
        </div>
        <div class="stat-card">
            <div class="stat-header">
                <?php echo icon('calendar', 32); ?>
                <h3>Joined</h3>
            </div>
            <div class="stat-value"><?php echo date('M d, Y', strtotime($customer['created_at'])); ?></div>
        </div>
        <div class="stat-card">
            <div class="stat-header">
                <?php echo icon('clock', 32); ?>
                <h3>Last Order</h3>
            </div>
            <div class="stat-value">
                <?php echo $customer['last_order_date'] ? date('M d, Y', strtotime($customer['last_order_date'])) : 'No orders'; ?>
            </div>
        </div>
    </div>

    <!-- Address -->
    <?php if (!empty($customer['address'])): ?>
    <div class="info-card">
        <h3><?php echo icon('marker', 20); ?> Address</h3>
        <p><?php echo nl2br(htmlspecialchars($customer['address'])); ?></p>
    </div>
    <?php endif; ?>

    <!-- Recent Orders -->
    <div class="info-card">
        <h3><?php echo icon('package', 20); ?> Recent Orders</h3>
        
        <?php if ($recentOrders->num_rows > 0): ?>
            <table class="admin-table">
                <thead>
                    <tr>
                        <th>Order #</th>
                        <th>Date</th>
                        <th>Amount</th>
                        <th>Status</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($order = $recentOrders->fetch_assoc()): ?>
                    <tr>
                        <td>#<?php echo str_pad($order['id'], 6, '0', STR_PAD_LEFT); ?></td>
                        <td><?php echo date('M d, Y', strtotime($order['created_at'])); ?></td>
                        <td>₱<?php echo number_format($order['total_amount'], 2); ?></td>
                        <td>
                            <span class="status-badge status-<?php echo strtolower($order['status']); ?>">
                                <?php echo ucfirst($order['status']); ?>
                            </span>
                        </td>
                        <td>
                            <a href="../orders/order_details.php?id=<?php echo $order['id']; ?>" class="btn-small">View</a>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
            <?php if ($customer['total_orders'] > 5): ?>
                <div class="view-all">
                    <a href="customer_orders.php?id=<?php echo $customer['id']; ?>" class="btn btn-outline btn-small">View All</a>
                </div>
            <?php endif; ?>
        <?php else: ?>
            <p class="no-data">No orders found.</p>
        <?php endif; ?>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>