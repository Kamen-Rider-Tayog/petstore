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

// Define APP_NAME if not defined
if (!defined('APP_NAME')) {
    define('APP_NAME', 'Ria Pet Store');
}

$customerId = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if (!$customerId) {
    header('Location: customers.php');
    exit();
}

// Get customer data
$stmt = $conn->prepare("
    SELECT c.*,
           COUNT(o.id) as total_orders,
           COALESCE(SUM(o.total_amount), 0) as total_spent
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

$message = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['confirm_delete'])) {
    // Check if customer has orders
    if ($customer['total_orders'] > 0) {
        $message = 'Cannot delete customer with existing orders. Please archive or deactivate instead.';
    } else {
        // Delete customer
        $stmt = $conn->prepare("DELETE FROM customers WHERE id = ?");
        $stmt->bind_param('i', $customerId);

        if ($stmt->execute()) {
            header('Location: customers.php?message=Customer deleted successfully');
            exit();
        } else {
            $message = 'Error deleting customer: ' . $conn->error;
        }
    }
}

$page_title = 'Delete Customer';
require_once __DIR__ . '/../includes/header.php';

echo '<link rel="stylesheet" href="/Ria-Pet-Store/admin/css/customers.css?v=' . time() . '">';
?>

<div class="admin-dashboard">
    <div class="delete-confirmation">
        <div class="warning-box">
            <?php echo icon('alert-triangle', 48); ?>
            <h3>Warning: This action cannot be undone!</h3>
            <p>Are you sure you want to delete this customer? This will permanently remove the customer from the database.</p>
            <?php if ($customer['total_orders'] > 0): ?>
                <p class="warning-text">
                    This customer has <?php echo $customer['total_orders']; ?> order(s) with a total value of ₱<?php echo number_format($customer['total_spent'], 2); ?>.
                    Customers with orders cannot be deleted.
                </p>
            <?php endif; ?>
        </div>

        <div class="customer-summary">
            <h3>Customer Details</h3>
            <div class="summary-grid">
                <div class="summary-item">
                    <span class="label">Customer ID:</span>
                    <span class="value">#<?php echo $customer['id']; ?></span>
                </div>
                <div class="summary-item">
                    <span class="label">Name:</span>
                    <span class="value"><?php echo htmlspecialchars($customer['first_name'] . ' ' . $customer['last_name']); ?></span>
                </div>
                <div class="summary-item">
                    <span class="label">Email:</span>
                    <span class="value"><?php echo htmlspecialchars($customer['email']); ?></span>
                </div>
                <div class="summary-item">
                    <span class="label">Phone:</span>
                    <span class="value"><?php echo htmlspecialchars($customer['phone'] ?? 'N/A'); ?></span>
                </div>
                <div class="summary-item">
                    <span class="label">Total Orders:</span>
                    <span class="value"><?php echo $customer['total_orders']; ?></span>
                </div>
                <div class="summary-item">
                    <span class="label">Total Spent:</span>
                    <span class="value">₱<?php echo number_format($customer['total_spent'], 2); ?></span>
                </div>
                <div class="summary-item">
                    <span class="label">Joined:</span>
                    <span class="value"><?php echo date('M d, Y', strtotime($customer['created_at'])); ?></span>
                </div>
            </div>
        </div>

        <form method="post" class="delete-form">
            <div class="action-buttons">
                <button type="submit" name="confirm_delete" value="1" class="btn btn-danger" <?php echo $customer['total_orders'] > 0 ? 'disabled' : ''; ?>>
                    <?php echo icon('trash', 16); ?> Yes, Delete Customer
                </button>
                <a href="customer_details.php?id=<?php echo $customer['id']; ?>" class="btn btn-outline">Cancel</a>
            </div>
        </form>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>