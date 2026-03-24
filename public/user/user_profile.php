<?php
session_name('petstore_session');
session_start();

require_once __DIR__ . '/../../backend/config/database.php';
require_once __DIR__ . '/../../backend/functions/helpers.php';

// Check if user is logged in
if (!isset($_SESSION['customer_id'])) {
    header('Location: ' . url('login'));
    exit;
}

require_once __DIR__ . '/../../backend/includes/header.php';

$userId = $_SESSION['customer_id'];
$message = '';
$messageType = '';

// Get user data
try {
    $stmt = $conn->prepare("SELECT * FROM customers WHERE id = ?");
    $stmt->bind_param("i", $userId);
    $stmt->execute();
    $result = $stmt->get_result();
    $user = $result->fetch_assoc();

    // Get default address
    $address = [];
    $addrStmt = $conn->prepare("SELECT * FROM addresses WHERE customer_id = ? AND is_default = 1");
    $addrStmt->bind_param("i", $userId);
    $addrStmt->execute();
    $addrResult = $addrStmt->get_result();
    if ($addrResult->num_rows > 0) {
        $address = $addrResult->fetch_assoc();
    }
    $addrStmt->close();

    if (!$user) {
        header('Location: ' . url('login'));
        exit;
    }
} catch (Exception $e) {
    $message = 'Error loading profile data.';
    $messageType = 'error';
    $user = [];
    $address = [];
}

// Get user's recent orders
try {
    $stmt = $conn->prepare("
        SELECT o.id, o.total_amount, o.status, o.created_at,
               COUNT(oi.id) as item_count
        FROM orders o
        LEFT JOIN order_items oi ON o.id = oi.order_id
        WHERE o.customer_id = ?
        GROUP BY o.id
        ORDER BY o.created_at DESC
        LIMIT 5
    ");
    $stmt->bind_param("i", $userId);
    $stmt->execute();
    $result = $stmt->get_result();
    $recentOrders = $result->fetch_all(MYSQLI_ASSOC);
} catch (Exception $e) {
    $recentOrders = [];
}

$page_title = 'My Profile';
?>

<link rel="stylesheet" href="/Ria-Pet-Store/assets/css/user/user_profile.css?v=<?php echo time(); ?>">

<div class="profile-page">
    <section class="profile-hero">
        <div class="container">
            <h1>My Profile</h1>
        </div>
    </section>

    <section class="profile-content">
        <div class="container">
            <div class="profile-container">
                <div class="profile-sidebar">
                    <div class="profile-avatar">
                        <div class="avatar-circle">
                            <?php echo icon('user', 40); ?>
                        </div>
                        <h3><?php echo htmlspecialchars(($user['first_name'] ?? '') . ' ' . ($user['last_name'] ?? '')); ?></h3>
                        <p><?php echo htmlspecialchars($user['email'] ?? ''); ?></p>
                    </div>

                    <nav class="profile-nav">
                        <a href="#profile" class="profile-nav-link active">
                            <?php echo icon('user', 16); ?> Profile Information
                        </a>
                        <a href="<?php echo url('my_pets'); ?>" class="profile-nav-link">
                            <?php echo icon('paw', 16); ?> My Pets
                        </a>
                        <a href="<?php echo url('order_history'); ?>" class="profile-nav-link">
                            <?php echo icon('package', 16); ?> Order History
                        </a>
                        <a href="<?php echo url('my_appointments'); ?>" class="profile-nav-link">
                            <?php echo icon('calendar', 16); ?> My Appointments
                        </a>
                        <a href="<?php echo url('edit_profile'); ?>" class="profile-nav-link">
                            <?php echo icon('edit', 16); ?> Edit Profile
                        </a>
                        <a href="<?php echo url('logout'); ?>" class="profile-nav-link logout-link">
                            <?php echo icon('x', 16); ?> Logout
                        </a>
                    </nav>
                </div>

                <div class="profile-main">
                    <?php if ($message): ?>
                        <div class="message <?php echo $messageType; ?>">
                            <?php echo htmlspecialchars($message); ?>
                        </div>
                    <?php endif; ?>

                    <div id="profile" class="profile-section">
                        <div class="section-header">
                            <h2>Profile Information</h2>
                        </div>

                        <div class="info-grid">
                            <div class="info-item">
                                <div class="info-label">Full Name</div>
                                <div class="info-value"><?php echo htmlspecialchars(($user['first_name'] ?? '') . ' ' . ($user['last_name'] ?? '')); ?></div>
                            </div>
                            <div class="info-item">
                                <div class="info-label">Email</div>
                                <div class="info-value"><?php echo htmlspecialchars($user['email'] ?? ''); ?></div>
                            </div>
                            <div class="info-item">
                                <div class="info-label">Phone</div>
                                <div class="info-value"><?php echo htmlspecialchars($user['phone'] ?? 'Not provided'); ?></div>
                            </div>
                            <div class="info-item">
                                <div class="info-label">Member Since</div>
                                <div class="info-value"><?php echo date('M j, Y', strtotime($user['created_at'] ?? 'now')); ?></div>
                            </div>
                        </div>

                        <?php if (!empty($address)): ?>
                        <div class="info-item full-width" style="margin-top: 1rem;">
                            <div class="info-label">Address</div>
                            <div class="info-value">
                                <?php 
                                $addressParts = array_filter([
                                    $address['address_line1'],
                                    $address['address_line2'],
                                    $address['city'],
                                    $address['state'],
                                    $address['zip_code'],
                                    $address['country']
                                ]);
                                echo htmlspecialchars(implode(', ', $addressParts));
                                ?>
                            </div>
                        </div>
                        <?php endif; ?>
                    </div>

                    <div id="orders" class="profile-section">
                        <div class="section-header">
                            <h2>Recent Orders</h2>
                        </div>

                        <?php if (empty($recentOrders)): ?>
                            <div class="no-orders">
                                <p>You haven't placed any orders yet.</p>
                                <a href="<?php echo url('products'); ?>" class="btn btn-primary">Start Shopping</a>
                            </div>
                        <?php else: ?>
                            <div class="orders-list">
                                <?php foreach ($recentOrders as $order): ?>
                                <div class="order-card">
                                    <div class="order-header">
                                        <span class="order-number">Order #<?php echo $order['id']; ?></span>
                                        <span class="status-badge status-<?php echo strtolower($order['status']); ?>">
                                            <?php echo ucfirst($order['status']); ?>
                                        </span>
                                    </div>
                                    <div class="order-details">
                                        <span><?php echo date('M j, Y', strtotime($order['created_at'])); ?></span>
                                        <span><?php echo $order['item_count']; ?> item(s)</span>
                                        <span class="order-total">₱<?php echo number_format($order['total_amount'], 2); ?></span>
                                    </div>
                                    <div class="order-actions">
                                        <a href="<?php echo url('order_details?id=' . $order['id']); ?>" class="btn btn-small">View Details</a>
                                    </div>
                                </div>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </section>
</div>

<?php require_once __DIR__ . '/../../backend/includes/footer.php'; ?>