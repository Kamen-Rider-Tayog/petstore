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

$page_title = 'Edit Customer';
require_once __DIR__ . '/../includes/header.php';

$customerId = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$message = '';
$success = '';

if (!$customerId) {
    header('Location: customers.php');
    exit();
}

// Get customer data
$stmt = $conn->prepare("SELECT * FROM customers WHERE id = ?");
$stmt->bind_param('i', $customerId);
$stmt->execute();
$customer = $stmt->get_result()->fetch_assoc();

if (!$customer) {
    header('Location: customers.php');
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $firstName = trim($_POST['first_name'] ?? '');
    $lastName = trim($_POST['last_name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $address = trim($_POST['address'] ?? '');

    // Basic validation
    if (empty($firstName) || empty($lastName) || empty($email)) {
        $message = 'Please fill in all required fields.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $message = 'Please enter a valid email address.';
    } else {
        // Check if email is already used by another customer
        $checkStmt = $conn->prepare("SELECT id FROM customers WHERE email = ? AND id != ?");
        $checkStmt->bind_param('si', $email, $customerId);
        $checkStmt->execute();
        if ($checkStmt->get_result()->num_rows > 0) {
            $message = 'This email address is already registered to another customer.';
        } else {
            // Update customer
            $updateStmt = $conn->prepare("UPDATE customers SET first_name = ?, last_name = ?, email = ?, phone = ?, address = ? WHERE id = ?");
            $updateStmt->bind_param('sssssi', $firstName, $lastName, $email, $phone, $address, $customerId);

            if ($updateStmt->execute()) {
                header('Location: customer_details.php?id=' . $customerId . '&message=Customer updated successfully');
                exit();
            } else {
                $message = 'Error updating customer: ' . $conn->error;
            }
        }
    }
}

echo '<link rel="stylesheet" href="/Ria-Pet-Store/admin/css/customers.css?v=' . time() . '">';
?>

<div class="admin-dashboard">
    <div class="page-header">
        <div class="header-left">
            <a href="customer_details.php?id=<?php echo $customer['id']; ?>" class="btn btn-outline">
                <?php echo icon('arrow-left', 16); ?> Back to Details
            </a>
        </div>
        <div class="header-right">
            <button type="submit" form="edit-customer-form" class="btn btn-primary">
                <?php echo icon('save', 16); ?> Save Changes
            </button>
        </div>
    </div>

    <?php if ($message): ?>
        <div class="message error"><?php echo htmlspecialchars($message); ?></div>
    <?php endif; ?>

    <div class="customer-form-container">
        <form method="post" id="edit-customer-form" class="customer-form">
            <div class="form-grid">
                <div class="form-group">
                    <label for="first_name">First Name *</label>
                    <input type="text" id="first_name" name="first_name" class="form-control" 
                           value="<?php echo htmlspecialchars($customer['first_name']); ?>" required>
                </div>

                <div class="form-group">
                    <label for="last_name">Last Name *</label>
                    <input type="text" id="last_name" name="last_name" class="form-control" 
                           value="<?php echo htmlspecialchars($customer['last_name']); ?>" required>
                </div>

                <div class="form-group">
                    <label for="email">Email Address *</label>
                    <input type="email" id="email" name="email" class="form-control" 
                           value="<?php echo htmlspecialchars($customer['email']); ?>" required>
                </div>

                <div class="form-group">
                    <label for="phone">Phone Number</label>
                    <input type="tel" id="phone" name="phone" class="form-control" 
                           value="<?php echo htmlspecialchars($customer['phone'] ?? ''); ?>">
                </div>

                <div class="form-group full-width">
                    <label for="address">Address</label>
                    <textarea id="address" name="address" class="form-control" rows="3"><?php echo htmlspecialchars($customer['address'] ?? ''); ?></textarea>
                </div>
            </div>

            <div class="form-actions">
                <button type="submit" class="btn btn-primary"><?php echo icon('save', 16); ?> Update Customer</button>
                <a href="customer_details.php?id=<?php echo $customer['id']; ?>" class="btn btn-outline">Cancel</a>
            </div>
        </form>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>