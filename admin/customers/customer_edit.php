<?php
require_once '../includes/header.php';
require_once '../includes/sidebar.php';

$customerId = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$message = '';

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
        $stmt = $conn->prepare("SELECT id FROM customers WHERE email = ? AND id != ?");
        $stmt->bind_param('si', $email, $customerId);
        $stmt->execute();
        if ($stmt->get_result()->num_rows > 0) {
            $message = 'This email address is already registered to another customer.';
        } else {
            // Update customer
            $stmt = $conn->prepare("UPDATE customers SET first_name = ?, last_name = ?, email = ?, phone = ?, address = ? WHERE id = ?");
            $stmt->bind_param('sssssi', $firstName, $lastName, $email, $phone, $address, $customerId);

            if ($stmt->execute()) {
                header('Location: customer_details.php?id=' . $customerId . '&message=Customer updated successfully');
                exit();
            } else {
                $message = 'Error updating customer: ' . $conn->error;
            }
        }
    }
}
?>

<main class="admin-main">
    <h2>Edit Customer</h2>

    <?php if ($message): ?>
        <div style="background: #f8d7da; color: #721c24; padding: 1rem; border-radius: 4px; margin-bottom: 1rem;">
            <?php echo htmlspecialchars($message); ?>
        </div>
    <?php endif; ?>

    <div style="background: #fff; padding: 2rem; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); max-width: 600px;">
        <form method="post">
            <div class="form-group">
                <label for="first_name">First Name *</label>
                <input type="text" id="first_name" name="first_name" value="<?php echo htmlspecialchars($customer['first_name']); ?>" required>
            </div>

            <div class="form-group">
                <label for="last_name">Last Name *</label>
                <input type="text" id="last_name" name="last_name" value="<?php echo htmlspecialchars($customer['last_name']); ?>" required>
            </div>

            <div class="form-group">
                <label for="email">Email Address *</label>
                <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($customer['email']); ?>" required>
            </div>

            <div class="form-group">
                <label for="phone">Phone Number</label>
                <input type="tel" id="phone" name="phone" value="<?php echo htmlspecialchars($customer['phone'] ?? ''); ?>">
            </div>

            <div class="form-group">
                <label for="address">Address</label>
                <textarea id="address" name="address" rows="3"><?php echo htmlspecialchars($customer['address'] ?? ''); ?></textarea>
            </div>

            <div style="margin-top: 2rem;">
                <button type="submit" class="btn btn-success">Update Customer</button>
                <a href="customer_details.php?id=<?php echo $customer['id']; ?>" class="btn" style="margin-left: 1rem;">Cancel</a>
            </div>
        </form>
    </div>
</main>

<?php require_once '../includes/footer.php'; ?>