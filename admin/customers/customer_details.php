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

// Get customer's pets
$stmt = $conn->prepare("
    SELECT cp.*
    FROM customer_pets cp
    WHERE cp.customer_id = ?
    ORDER BY cp.name ASC
");
$stmt->bind_param('i', $customerId);
$stmt->execute();
$customerPets = $stmt->get_result();
$totalPets = $customerPets->num_rows;

$page_title = 'Customer Details - ' . $customer['first_name'] . ' ' . $customer['last_name'];
require_once __DIR__ . '/../includes/header.php';

// Admin CSS
echo '<link rel="stylesheet" href="/Ria-Pet-Store/admin/css/customers.css?v=' . time() . '">';
?>

<div class="admin-dashboard">
    <!-- Header Actions -->
    <div class="header-actions">
        <a href="customers.php" class="btn btn-outline">
            <?php echo icon('arrow-left', 16); ?> Back
        </a>
        <div class="action-buttons-group">
            <a href="edit_customer.php?id=<?php echo $customer['id']; ?>" class="btn btn-outline">
                <?php echo icon('edit', 16); ?> Edit
            </a>
            <a href="delete_customer.php?id=<?php echo $customer['id']; ?>" class="btn btn-outline">
                <?php echo icon('trash', 16); ?> Delete
            </a>
        </div>
    </div>

    <!-- Bento Grid Layout - 4 columns -->
    <div class="bento-grid">
        <!-- Customer Header - spans 3 columns -->
        <div class="bento-card customer-header-card">
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
                <?php if (!empty($customer['address'])): ?>
                    <p class="customer-address"><?php echo nl2br(htmlspecialchars($customer['address'])); ?></p>
                <?php endif; ?>
            </div>
        </div>

        <!-- Pets Count Stat - spans 1 column -->
        <div class="bento-card stat-card pets-stat">
            <div class="stat-header">
                <div class="stat-icon"><?php echo icon('paw', 32); ?></div>
                <div class="stat-label">Total Pets</div>
            </div>
            
            <div class="stat-value"><?php echo $totalPets; ?></div>
        </div>

        <!-- Customer's Pets - spans 2 rows and 2 columns -->
        <div class="bento-card pets-list-card">
            <div class="card-header">
                <h3><?php echo icon('paw', 20); ?> Customer's Pets</h3>
                <?php if ($totalPets > 0): ?>
                    <span class="badge"><?php echo $totalPets; ?> pets</span>
                <?php endif; ?>
            </div>
            
            <?php if ($customerPets->num_rows > 0): ?>
                <div class="pets-list">
                    <?php while ($pet = $customerPets->fetch_assoc()): ?>
                        <div class="pet-item">
                            <div class="pet-icon">
                                <?php echo icon('paw', 24); ?>
                            </div>
                            <div class="pet-details">
                                <div class="pet-name"><?php echo htmlspecialchars($pet['name']); ?></div>
                                <div class="pet-meta">
                                    <?php echo htmlspecialchars(ucfirst($pet['species'])); ?>
                                    <?php if (!empty($pet['breed'])): ?>
                                        • <?php echo htmlspecialchars($pet['breed']); ?>
                                    <?php endif; ?>
                                    <?php if (!empty($pet['age'])): ?>
                                        • <?php echo $pet['age']; ?> years
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    <?php endwhile; ?>
                </div>
                <div class="view-all-pets">
                    <a href="../customers/customer_pets.php?customer_id=<?php echo $customer['id']; ?>" class="btn-link">
                        View All Pets <?php echo icon('arrow-right', 12); ?>
                    </a>
                </div>
            <?php else: ?>
                <div class="no-pets">
                    <?php echo icon('paw', 32); ?>
                    <p>No pets registered</p>
                    <a href="../customers/add_customer_pet.php?customer_id=<?php echo $customer['id']; ?>" class="btn btn-outline btn-small">
                        <?php echo icon('plus', 14); ?> Add Pet
                    </a>
                </div>
            <?php endif; ?>
        </div>

        <!-- Orders Stat - 1 row 1 col -->
        <div class="bento-card stat-card">
            <div class="stat-header">
                <div class="stat-icon"><?php echo icon('package', 32); ?></div>
                <div class="stat-label">Total Orders</div>
            </div>
            <div class="stat-value"><?php echo $customer['total_orders']; ?></div>
        </div>

        <!-- Total Spent Stat - 1 row 1 col -->
        <div class="bento-card stat-card">
            <div class="stat-header">
                <div class="stat-icon"><?php echo icon('credit-card', 32); ?></div>
                <div class="stat-label">Total Spent</div>
            </div>
            <div class="stat-value">₱<?php echo number_format($customer['total_spent'], 2); ?></div>
        </div>

        <!-- Joined Date Stat - 1 row 1 col -->
        <div class="bento-card stat-card">
            <div class="stat-header">
                <div class="stat-icon"><?php echo icon('calendar', 32); ?></div>
                <div class="stat-label">Joined Date</div>
            </div>
            <div class="stat-value"><?php echo date('M d, Y', strtotime($customer['created_at'])); ?></div>
        </div>

        <!-- Last Order Stat - 1 row 1 col -->
        <div class="bento-card stat-card">
            <div class="stat-content">
                <div class="stat-header">
                    <div class="stat-icon"><?php echo icon('clock', 32); ?></div>
                    <div class="stat-label">Last Order</div>
                </div>
                <div class="stat-value"><?php echo $customer['last_order_date'] ? date('M d, Y', strtotime($customer['last_order_date'])) : 'No orders'; ?> </div>
            </div>
        </div>

        <!-- Recent Orders - spans full width -->
        <div class="bento-card recent-orders-card full-width">
            <div class="card-header">
                <h3><?php echo icon('package', 20); ?> Recent Orders</h3>
                <?php if ($customer['total_orders'] > 0): ?>
                    <a href="customer_orders.php?id=<?php echo $customer['id']; ?>" class="btn-link">View All <?php echo icon('arrow-right', 12); ?></a>
                <?php endif; ?>
            </div>
            
            <?php if ($recentOrders->num_rows > 0): ?>
                <table class="admin-table clickable-rows">
                    <thead>
                        <tr>
                            <th>Order #</th>
                            <th>Date</th>
                            <th>Amount</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($order = $recentOrders->fetch_assoc()): ?>
                        <tr class="clickable-row" data-href="../orders/order_details.php?id=<?php echo $order['id']; ?>">
                            <td class="order-id">#<?php echo str_pad($order['id'], 6, '0', STR_PAD_LEFT); ?></td>
                            <td><?php echo date('M d, Y', strtotime($order['created_at'])); ?></td>
                            <td class="order-amount">₱<?php echo number_format($order['total_amount'], 2); ?></td>
                            <td>
                                <span class="status-badge status-<?php echo strtolower($order['status']); ?>">
                                    <?php echo ucfirst($order['status']); ?>
                                </span>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            <?php else: ?>
                <div class="no-data">
                    <p>No orders found.</p>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const rows = document.querySelectorAll('.clickable-row');
    rows.forEach(row => {
        row.addEventListener('click', function() {
            window.location.href = this.dataset.href;
        });
        row.style.cursor = 'pointer';
    });
});
</script>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>