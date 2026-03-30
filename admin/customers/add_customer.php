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

$page_title = 'Add Customer';
require_once __DIR__ . '/../includes/header.php';

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $firstName = trim($_POST['first_name'] ?? '');
    $lastName = trim($_POST['last_name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $address = trim($_POST['address'] ?? '');
    $password = trim($_POST['password'] ?? '');
    $is_active = isset($_POST['is_active']) ? 1 : 1; // Default active

    // Basic validation
    if (empty($firstName) || empty($lastName) || empty($email)) {
        $error = 'Please fill in all required fields.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Please enter a valid email address.';
    } elseif (empty($password)) {
        $error = 'Please enter a password.';
    } else {
        // Check if email already exists
        $checkStmt = $conn->prepare("SELECT id FROM customers WHERE email = ?");
        $checkStmt->bind_param('s', $email);
        $checkStmt->execute();
        if ($checkStmt->get_result()->num_rows > 0) {
            $error = 'This email address is already registered.';
        } else {
            $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
            
            $stmt = $conn->prepare("
                INSERT INTO customers (first_name, last_name, email, phone, address, password, is_active, created_at) 
                VALUES (?, ?, ?, ?, ?, ?, ?, NOW())
            ");
            $stmt->bind_param('ssssssi', $firstName, $lastName, $email, $phone, $address, $hashedPassword, $is_active);
            
            if ($stmt->execute()) {
                $customerId = $stmt->insert_id;
                header('Location: customer_details.php?id=' . $customerId . '&message=Customer added successfully');
                exit();
            } else {
                $error = 'Error adding customer: ' . $conn->error;
            }
        }
    }
}

// Admin CSS
echo '<link rel="stylesheet" href="/Ria-Pet-Store/admin/css/customers.css?v=' . time() . '">';
?>

<div class="admin-dashboard">
    <div class="page-header">
        <div class="header-left">
            <a href="customers.php" class="btn btn-outline">
                <?php echo icon('arrow-left', 16); ?> Back to Customers
            </a>
        </div>
        <div class="header-right">
            <button type="submit" form="add-customer-form" class="btn btn-primary">
                <?php echo icon('save', 16); ?> Save Customer
            </button>
        </div>
    </div>

    <?php if ($error): ?>
        <div class="message error"><?php echo htmlspecialchars($error); ?></div>
    <?php endif; ?>

    <form method="post" id="add-customer-form" class="customer-form">
        <div class="form-grid">
            <!-- Personal Information -->
            <div class="info-card">
                <h3><?php echo icon('user', 20); ?> Personal Information</h3>
                <div class="form-group">
                    <label for="first_name">First Name *</label>
                    <input type="text" id="first_name" name="first_name" class="form-control" required>
                </div>
                <div class="form-group">
                    <label for="last_name">Last Name *</label>
                    <input type="text" id="last_name" name="last_name" class="form-control" required>
                </div>
                <div class="form-group">
                    <label for="email">Email Address *</label>
                    <input type="email" id="email" name="email" class="form-control" required>
                </div>
                <div class="form-group">
                    <label for="password">Password *</label>
                    <input type="password" id="password" name="password" class="form-control" required>
                    <small class="help-text">Minimum 6 characters recommended</small>
                </div>
            </div>

            <!-- Contact Information -->
            <div class="info-card">
                <h3><?php echo icon('phone', 20); ?> Contact Information</h3>
                <div class="form-group">
                    <label for="phone">Phone Number</label>
                    <input type="tel" id="phone" name="phone" class="form-control" placeholder="e.g., 09123456789">
                </div>
                <div class="form-group">
                    <label for="address">Address</label>
                    <textarea id="address" name="address" rows="4" class="form-control" placeholder="Street, City, Province"></textarea>
                </div>
                <div class="form-group">
                    <label class="checkbox-label">
                        <input type="checkbox" name="is_active" value="1" checked> Active Account
                    </label>
                    <small class="help-text">Inactive customers cannot log in</small>
                </div>
            </div>
        </div>
    </form>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>