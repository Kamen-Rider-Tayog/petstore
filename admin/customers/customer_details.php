<?php
require_once '../includes/header.php';
require_once '../includes/sidebar.php';

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
           MAX(o.created_at) as last_order_date,
           MIN(o.created_at) as first_order_date
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
?>

<main class="admin-main">
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 2rem;">
        <h2>Customer Details - <?php echo htmlspecialchars($customer['first_name'] . ' ' . $customer['last_name']); ?></h2>
        <div>
            <a href="customer_edit.php?id=<?php echo $customer['id']; ?>" class="btn btn-warning">Edit Customer</a>
            <a href="customers.php" class="btn">Back to Customers</a>
        </div>
    </div>

    <div style="display: grid; grid-template-columns: 1fr 2fr; gap: 2rem; margin-bottom: 2rem;">
        <!-- Customer Information -->
        <div style="background: #fff; padding: 2rem; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1);">
            <h3>Customer Information</h3>
            <table style="width: 100%; border-collapse: collapse;">
                <tr>
                    <td style="padding: 0.5rem 0; font-weight: bold; width: 120px;">Customer ID:</td>
                    <td style="padding: 0.5rem 0;"><?php echo $customer['id']; ?></td>
                </tr>
                <tr>
                    <td style="padding: 0.5rem 0; font-weight: bold;">Name:</td>
                    <td style="padding: 0.5rem 0;"><?php echo htmlspecialchars($customer['first_name'] . ' ' . $customer['last_name']); ?></td>
                </tr>
                <tr>
                    <td style="padding: 0.5rem 0; font-weight: bold;">Email:</td>
                    <td style="padding: 0.5rem 0;"><?php echo htmlspecialchars($customer['email']); ?></td>
                </tr>
                <?php if (!empty($customer['phone'])): ?>
                <tr>
                    <td style="padding: 0.5rem 0; font-weight: bold;">Phone:</td>
                    <td style="padding: 0.5rem 0;"><?php echo htmlspecialchars($customer['phone']); ?></td>
                </tr>
                <?php endif; ?>
                <?php if (!empty($customer['address'])): ?>
                <tr>
                    <td style="padding: 0.5rem 0; font-weight: bold;">Address:</td>
                    <td style="padding: 0.5rem 0;"><?php echo nl2br(htmlspecialchars($customer['address'])); ?></td>
                </tr>
                <?php endif; ?>
                <tr>
                    <td style="padding: 0.5rem 0; font-weight: bold;">Joined:</td>
                    <td style="padding: 0.5rem 0;"><?php echo date('M d, Y', strtotime($customer['created_at'])); ?></td>
                </tr>
            </table>
        </div>

        <!-- Customer Statistics -->
        <div style="background: #fff; padding: 2rem; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1);">
            <h3>Customer Statistics</h3>
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
                <div style="text-align: center; padding: 1rem; background: #f8f9fa; border-radius: 4px;">
                    <div style="font-size: 2rem; font-weight: bold; color: #007bff;"><?php echo $customer['total_orders']; ?></div>
                    <div style="color: #666;">Total Orders</div>
                </div>
                <div style="text-align: center; padding: 1rem; background: #f8f9fa; border-radius: 4px;">
                    <div style="font-size: 2rem; font-weight: bold; color: #28a745;">₱<?php echo number_format($customer['total_spent'], 0); ?></div>
                    <div style="color: #666;">Total Spent</div>
                </div>
                <div style="text-align: center; padding: 1rem; background: #f8f9fa; border-radius: 4px;">
                    <div style="font-size: 1.2rem; font-weight: bold; color: #6f42c1;">
                        <?php echo $customer['first_order_date'] ? date('M d, Y', strtotime($customer['first_order_date'])) : 'N/A'; ?>
                    </div>
                    <div style="color: #666;">First Order</div>
                </div>
                <div style="text-align: center; padding: 1rem; background: #f8f9fa; border-radius: 4px;">
                    <div style="font-size: 1.2rem; font-weight: bold; color: #fd7e14;">
                        <?php echo $customer['last_order_date'] ? date('M d, Y', strtotime($customer['last_order_date'])) : 'Never'; ?>
                    </div>
                    <div style="color: #666;">Last Order</div>
                </div>
            </div>
        </div>
    </div>

    <!-- Recent Orders -->
    <div style="background: #fff; padding: 2rem; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1);">
        <h3>Recent Orders</h3>
        <?php if ($recentOrders->num_rows > 0): ?>
            <div style="overflow-x: auto;">
                <table style="width: 100%; border-collapse: collapse;">
                    <thead>
                        <tr style="border-bottom: 2px solid #dee2e6;">
                            <th style="padding: 0.75rem 0.5rem; text-align: left;">Order ID</th>
                            <th style="padding: 0.75rem 0.5rem; text-align: center;">Amount</th>
                            <th style="padding: 0.75rem 0.5rem; text-align: center;">Status</th>
                            <th style="padding: 0.75rem 0.5rem; text-align: center;">Date</th>
                            <th style="padding: 0.75rem 0.5rem; text-align: center;">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($order = $recentOrders->fetch_assoc()): ?>
                            <tr style="border-bottom: 1px solid #dee2e6;">
                                <td style="padding: 0.75rem 0.5rem;">#<?php echo str_pad($order['id'], 6, '0', STR_PAD_LEFT); ?></td>
                                <td style="padding: 0.75rem 0.5rem; text-align: center;">₱<?php echo number_format($order['total_amount'], 2); ?></td>
                                <td style="padding: 0.75rem 0.5rem; text-align: center;">
                                    <span class="status-badge status-<?php echo strtolower($order['status']); ?>">
                                        <?php echo htmlspecialchars(ucfirst($order['status'])); ?>
                                    </span>
                                </td>
                                <td style="padding: 0.75rem 0.5rem; text-align: center;"><?php echo date('M d, Y', strtotime($order['created_at'])); ?></td>
                                <td style="padding: 0.75rem 0.5rem; text-align: center;">
                                    <a href="order_details.php?id=<?php echo $order['id']; ?>" class="btn btn-small">View</a>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
            <?php if ($customer['total_orders'] > 5): ?>
                <div style="text-align: center; margin-top: 1rem;">
                    <a href="customer_orders.php?id=<?php echo $customer['id']; ?>" class="btn btn-small">View All Orders</a>
                </div>
            <?php endif; ?>
        <?php else: ?>
            <p style="text-align: center; color: #666; padding: 2rem;">No orders found for this customer.</p>
        <?php endif; ?>
    </div>
</main>

<?php require_once '../includes/footer.php'; ?>