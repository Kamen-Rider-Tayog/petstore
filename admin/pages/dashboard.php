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

$page_title = 'Dashboard';
require_once __DIR__ . '/../includes/header.php';

// Get stats
$stats = [];

// Total sales
$result = $conn->query("SELECT SUM(total_amount) as total FROM orders WHERE status != 'cancelled'");
$stats['sales'] = $result->fetch_assoc()['total'] ?? 0;

// Total purchases (from products inventory value)
$result = $conn->query("SELECT SUM(price * quantity_in_stock) as total FROM products");
$stats['purchases'] = $result->fetch_assoc()['total'] ?? 0;

// Total orders
$result = $conn->query("SELECT COUNT(*) as total FROM orders");
$stats['orders'] = $result->fetch_assoc()['total'];

// Total customers
$result = $conn->query("SELECT COUNT(*) as total FROM customers");
$stats['customers'] = $result->fetch_assoc()['total'];

// Get recent activities (simplified)
$activities = [];

// Recent orders
$recentOrders = $conn->query("SELECT id, created_at FROM orders ORDER BY created_at DESC LIMIT 3");
while ($order = $recentOrders->fetch_assoc()) {
    $activities[] = [
        'text' => "New order #" . str_pad($order['id'], 6, '0', STR_PAD_LEFT) . " was placed",
        'time' => $order['created_at']
    ];
}

// Recent customer registrations
$recentCustomers = $conn->query("SELECT first_name, last_name, created_at FROM customers ORDER BY created_at DESC LIMIT 2");
while ($customer = $recentCustomers->fetch_assoc()) {
    $activities[] = [
        'text' => "New customer registered: " . $customer['first_name'] . ' ' . $customer['last_name'],
        'time' => $customer['created_at']
    ];
}

// Sort activities by time
usort($activities, function($a, $b) {
    return strtotime($b['time']) - strtotime($a['time']);
});
$activities = array_slice($activities, 0, 5);
?>

<div class="admin-dashboard">
    <!-- Stats Grid -->
    <div class="stats-grid">
        <div class="stat-card">
            <div class="stat-header">
                <?php echo icon('credit-card', 32); ?>
                <h3>Sales</h3>
            </div>
            <div class="stat-value">₱<?php echo number_format($stats['sales'], 2); ?></div>
            <div class="stat-change up">
                <?php echo icon('arrow-up', 12); ?> 5.67% Since Last Month
            </div>
        </div>
        
        <div class="stat-card">
            <div class="stat-header">
                <?php echo icon('package', 32); ?>
                <h3>Purchases</h3>
            </div>
            <div class="stat-value">₱<?php echo number_format($stats['purchases'], 2); ?></div>
            <div class="stat-change down">
                <?php echo icon('arrow-down', 12); ?> 5.64% Since Last Month
            </div>
        </div>
        
        <div class="stat-card">
            <div class="stat-header">
                <?php echo icon('cart', 32); ?>
                <h3>Orders</h3>
            </div>
            <div class="stat-value"><?php echo number_format($stats['orders']); ?></div>
            <div class="stat-change up">
                <?php echo icon('arrow-up', 12); ?> 5.67% Since Last Month
            </div>
        </div>
        
        <div class="stat-card">
            <div class="stat-header">
                <?php echo icon('users', 32); ?>
                <h3>Customers</h3>
            </div>
            <div class="stat-value"><?php echo number_format($stats['customers']); ?></div>
            <div class="stat-change up">
                <?php echo icon('arrow-up', 12); ?> 12.3% Since Last Month
            </div>
        </div>
    </div>
    
    <!-- Dashboard Grid -->
    <div class="dashboard-grid">
        <!-- Overview Section -->
        <div class="chart-card">
            <h3>Overview</h3>
            <div class="overview-grid">
                <div class="overview-item">
                    <h4>Member Profit</h4>
                    <div class="overview-value">+₱23,430</div>
                    <div class="overview-change up">
                        <?php echo icon('arrow-up', 12); ?> +2,343 Last Month
                    </div>
                </div>
                <div class="overview-item">
                    <h4>Member Profit</h4>
                    <div class="overview-value">+₱18,920</div>
                    <div class="overview-change up">
                        <?php echo icon('arrow-up', 12); ?> +1,234 Last Month
                    </div>
                </div>
                <div class="overview-item">
                    <h4>Member Profit</h4>
                    <div class="overview-value">+₱32,150</div>
                    <div class="overview-change up">
                        <?php echo icon('arrow-up', 12); ?> +2,890 Last Month
                    </div>
                </div>
                <div class="overview-item">
                    <h4>Member Profit</h4>
                    <div class="overview-value">+₱25,670</div>
                    <div class="overview-change up">
                        <?php echo icon('arrow-up', 12); ?> +4,321 Last Month
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Activity Section -->
        <div class="activity-list">
            <h3>Activity</h3>
            <?php foreach ($activities as $activity): ?>
            <div class="activity-item">
                <div class="activity-icon">
                    <?php echo icon('activity', 20); ?>
                </div>
                <div class="activity-content">
                    <p><?php echo htmlspecialchars($activity['text']); ?></p>
                    <span class="activity-time"><?php echo time_ago(strtotime($activity['time'])); ?></span>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
    
    <!-- Total Sale Section -->
    <div class="chart-card">
        <h3>Total Sale</h3>
        <div class="chart-placeholder">
            <div style="text-align: center;">
                <?php echo icon('chart', 48); ?>
                <p>Sales chart coming soon</p>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>