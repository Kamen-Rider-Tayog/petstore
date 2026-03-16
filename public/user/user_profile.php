<?php
require_once '../../backend/config/database.php';
require_once '../../backend/includes/auth.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

require_once '../../backend/includes/header.php';

$userId = $_SESSION['user_id'];
$message = '';
$messageType = '';

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $firstName = trim($_POST['first_name'] ?? '');
        $lastName = trim($_POST['last_name'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $phone = trim($_POST['phone'] ?? '');
        $address = trim($_POST['address'] ?? '');
        $city = trim($_POST['city'] ?? '');
        $state = trim($_POST['state'] ?? '');
        $zipCode = trim($_POST['zip_code'] ?? '');

        // Validate input
        if (empty($firstName) || empty($lastName) || empty($email)) {
            throw new Exception('First name, last name, and email are required.');
        }

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            throw new Exception('Please enter a valid email address.');
        }

        // Check if email is already taken by another user
        $stmt = $conn->prepare("SELECT id FROM customers WHERE email = ? AND id != ?");
        $stmt->bind_param("si", $email, $userId);
        $stmt->execute();
        $result = $stmt->get_result();
        if ($result->fetch_assoc()) {
            throw new Exception('This email address is already in use.');
        }

        // Update user profile
        $stmt = $conn->prepare("
            UPDATE customers SET
                first_name = ?,
                last_name = ?,
                email = ?,
                phone = ?,
                address = ?,
                city = ?,
                state = ?,
                zip_code = ?,
                updated_at = NOW()
            WHERE id = ?
        ");
        $stmt->bind_param("ssssssssi", $firstName, $lastName, $email, $phone, $address, $city, $state, $zipCode, $userId);
        $stmt->execute();

        // Update session data
        $_SESSION['user_name'] = $firstName . ' ' . $lastName;
        $_SESSION['user_email'] = $email;

        $message = 'Profile updated successfully!';
        $messageType = 'success';

    } catch (Exception $e) {
        $message = $e->getMessage();
        $messageType = 'error';
    }
}

// Get user data
try {
    $stmt = $conn->prepare("SELECT * FROM customers WHERE id = ?");
    $stmt->bind_param("i", $userId);
    $stmt->execute();
    $result = $stmt->get_result();
    $user = $result->fetch_assoc();

    if (!$user) {
        header('Location: login.php');
        exit;
    }
} catch (Exception $e) {
    $message = 'Error loading profile data.';
    $messageType = 'error';
    $user = [];
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
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Profile - Pet Store</title>
    <link rel="stylesheet" href="../../assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="../../assets/css/user_profile.css">
</head>
<body>
    <?php include '../../backend/includes/header.php'; ?>

    <!-- Profile Hero -->
    <section class="profile-hero">
        <div class="container">
            <h1>My Profile</h1>
        </div>
    </section>

    <!-- Profile Content -->
    <section class="profile-content">
        <div class="container">
            <div class="profile-container">
                <!-- Sidebar -->
                <div class="profile-sidebar">
                    <div class="profile-avatar">
                        <div class="avatar-circle">
                            <i class="fas fa-user"></i>
                        </div>
                        <h3><?php echo htmlspecialchars($_SESSION['user_name'] ?? 'User'); ?></h3>
                        <p><?php echo htmlspecialchars($_SESSION['user_email'] ?? ''); ?></p>
                    </div>

                    <nav class="profile-nav">
                        <li class="profile-nav-item">
                            <a href="#profile" class="profile-nav-link active">
                                <i class="fas fa-user"></i> Profile Information
                            </a>
                        </li>
                        <li class="profile-nav-item">
                            <a href="#orders" class="profile-nav-link">
                                <i class="fas fa-shopping-bag"></i> Recent Orders
                            </a>
                        </li>
                        <li class="profile-nav-item">
                            <a href="#security" class="profile-nav-link">
                                <i class="fas fa-shield-alt"></i> Security
                            </a>
                        </li>
                    </nav>
                </div>

                <!-- Main Content -->
                <div class="profile-main">
                    <?php if ($message): ?>
                        <div class="message <?php echo $messageType; ?>">
                            <?php echo htmlspecialchars($message); ?>
                        </div>
                    <?php endif; ?>

                    <!-- Profile Information -->
                    <div id="profile" class="profile-section">
                        <div class="section-header">
                            <h2>Profile Information</h2>
                            <button class="edit-btn" onclick="toggleEditMode()">Edit Profile</button>
                        </div>

                        <form id="profileForm" method="POST" style="display: none;">
                            <div class="form-row">
                                <div class="form-group">
                                    <label for="first_name">First Name *</label>
                                    <input type="text" id="first_name" name="first_name" value="<?php echo htmlspecialchars($user['first_name'] ?? ''); ?>" required>
                                </div>
                                <div class="form-group">
                                    <label for="last_name">Last Name *</label>
                                    <input type="text" id="last_name" name="last_name" value="<?php echo htmlspecialchars($user['last_name'] ?? ''); ?>" required>
                                </div>
                            </div>

                            <div class="form-row">
                                <div class="form-group">
                                    <label for="email">Email Address *</label>
                                    <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($user['email'] ?? ''); ?>" required>
                                </div>
                                <div class="form-group">
                                    <label for="phone">Phone Number</label>
                                    <input type="tel" id="phone" name="phone" value="<?php echo htmlspecialchars($user['phone'] ?? ''); ?>">
                                </div>
                            </div>

                            <div class="form-group">
                                <label for="address">Street Address</label>
                                <input type="text" id="address" name="address" value="<?php echo htmlspecialchars($user['address'] ?? ''); ?>">
                            </div>

                            <div class="form-row">
                                <div class="form-group">
                                    <label for="city">City</label>
                                    <input type="text" id="city" name="city" value="<?php echo htmlspecialchars($user['city'] ?? ''); ?>">
                                </div>
                                <div class="form-group">
                                    <label for="state">State</label>
                                    <input type="text" id="state" name="state" value="<?php echo htmlspecialchars($user['state'] ?? ''); ?>">
                                </div>
                                <div class="form-group">
                                    <label for="zip_code">ZIP Code</label>
                                    <input type="text" id="zip_code" name="zip_code" value="<?php echo htmlspecialchars($user['zip_code'] ?? ''); ?>">
                                </div>
                            </div>

                            <div class="btn-group">
                                <button type="submit" class="btn-primary">Save Changes</button>
                                <button type="button" class="btn-secondary" onclick="toggleEditMode()">Cancel</button>
                            </div>
                        </form>

                        <!-- View Mode -->
                        <div id="profileView">
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

                            <?php if (!empty($user['address'])): ?>
                            <div class="info-item" style="margin-top: 1rem;">
                                <div class="info-label">Address</div>
                                <div class="info-value">
                                    <?php
                                    $addressParts = array_filter([
                                        $user['address'],
                                        $user['city'],
                                        $user['state'],
                                        $user['zip_code']
                                    ]);
                                    echo htmlspecialchars(implode(', ', $addressParts));
                                    ?>
                                </div>
                            </div>
                            <?php endif; ?>
                        </div>
                    </div>

                    <!-- Recent Orders -->
                    <div id="orders" class="profile-section">
                        <div class="section-header">
                            <h2>Recent Orders</h2>
                            <a href="customer_order.php" class="edit-btn" style="text-decoration: none;">View All Orders</a>
                        </div>

                        <?php if (empty($recentOrders)): ?>
                            <p>You haven't placed any orders yet. <a href="pets.php">Start shopping</a> to see your orders here!</p>
                        <?php else: ?>
                            <table class="orders-table">
                                <thead>
                                    <tr>
                                        <th>Order #</th>
                                        <th>Date</th>
                                        <th>Items</th>
                                        <th>Total</th>
                                        <th>Status</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($recentOrders as $order): ?>
                                    <tr>
                                        <td>#<?php echo $order['id']; ?></td>
                                        <td><?php echo date('M j, Y', strtotime($order['created_at'])); ?></td>
                                        <td><?php echo $order['item_count']; ?> item(s)</td>
                                        <td>$<?php echo number_format($order['total_amount'], 2); ?></td>
                                        <td>
                                            <span class="status-badge status-<?php echo strtolower($order['status']); ?>">
                                                <?php echo ucfirst($order['status']); ?>
                                            </span>
                                        </td>
                                        <td>
                                            <a href="customer_order.php?order_id=<?php echo $order['id']; ?>" style="color: #667eea;">View</a>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        <?php endif; ?>
                    </div>

                    <!-- Security Section -->
                    <div id="security" class="profile-section">
                        <div class="section-header">
                            <h2>Account Security</h2>
                        </div>

                        <div class="info-grid">
                            <div class="info-item">
                                <div class="info-label">Password</div>
                                <div class="info-value">••••••••</div>
                                <a href="#" style="color: #667eea; font-size: 0.9rem; margin-top: 0.5rem; display: inline-block;">Change Password</a>
                            </div>
                            <div class="info-item">
                                <div class="info-label">Two-Factor Auth</div>
                                <div class="info-value">Not enabled</div>
                                <a href="#" style="color: #667eea; font-size: 0.9rem; margin-top: 0.5rem; display: inline-block;">Enable 2FA</a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <?php include '../../backend/includes/footer.php'; ?>

    <script>
        function toggleEditMode() {
            const form = document.getElementById('profileForm');
            const view = document.getElementById('profileView');

            if (form.style.display === 'none') {
                form.style.display = 'block';
                view.style.display = 'none';
            } else {
                form.style.display = 'none';
                view.style.display = 'block';
            }
        }

        // Smooth scrolling for navigation
        document.querySelectorAll('.profile-nav-link').forEach(link => {
            link.addEventListener('click', function(e) {
                e.preventDefault();

                // Remove active class from all links
                document.querySelectorAll('.profile-nav-link').forEach(l => l.classList.remove('active'));
                this.classList.add('active');

                // Scroll to section
                const targetId = this.getAttribute('href').substring(1);
                const targetElement = document.getElementById(targetId);
                if (targetElement) {
                    targetElement.scrollIntoView({ behavior: 'smooth' });
                }
            });
        });
    </script>
</body>
</html>