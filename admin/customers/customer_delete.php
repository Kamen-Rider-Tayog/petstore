<?php
require_once '../includes/header.php';
require_once '../includes/sidebar.php';

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
?>

<main class="admin-main">
    <h2>Delete Customer</h2>

    <?php if ($message): ?>
        <div style="background: #f8d7da; color: #721c24; padding: 1rem; border-radius: 4px; margin-bottom: 1rem;">
            <?php echo htmlspecialchars($message); ?>
        </div>
    <?php endif; ?>

    <div style="background: #fff; padding: 2rem; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1);">
        <div style="background: #fff3cd; border: 1px solid #ffeaa7; padding: 1rem; border-radius: 4px; margin-bottom: 2rem;">
            <h3 style="color: #856404; margin-top: 0;">⚠️ Warning: This action cannot be undone!</h3>
            <p style="margin-bottom: 0;">Are you sure you want to delete this customer? This will permanently remove the customer from the database.</p>
            <?php if ($customer['total_orders'] > 0): ?>
                <p style="color: #dc3545; font-weight: bold; margin-top: 1rem; margin-bottom: 0;">
                    ⚠️ This customer has <?php echo $customer['total_orders']; ?> order(s) with a total value of ₱<?php echo number_format($customer['total_spent'], 2); ?>.
                    Customers with orders cannot be deleted.
                </p>
            <?php endif; ?>
        </div>

        <div style="display: grid; grid-template-columns: 1fr 2fr; gap: 2rem; margin-bottom: 2rem;">
            <div>
                <div style="width: 150px; height: 150px; background: #007bff; border-radius: 50%; display: flex; align-items: center; justify-content: center; color: white; font-size: 3rem; font-weight: bold;">
                    <?php echo strtoupper(substr($customer['first_name'], 0, 1) . substr($customer['last_name'], 0, 1)); ?>
                </div>
            </div>

            <div>
                <h3><?php echo htmlspecialchars($customer['first_name'] . ' ' . $customer['last_name']); ?></h3>
                <table style="width: 100%; border-collapse: collapse;">
                    <tr>
                        <td style="padding: 0.5rem 0; font-weight: bold; width: 120px;">Email:</td>
                        <td style="padding: 0.5rem 0;"><?php echo htmlspecialchars($customer['email']); ?></td>
                    </tr>
                    <tr>
                        <td style="padding: 0.5rem 0; font-weight: bold;">Phone:</td>
                        <td style="padding: 0.5rem 0;"><?php echo htmlspecialchars($customer['phone'] ?? 'N/A'); ?></td>
                    </tr>
                    <tr>
                        <td style="padding: 0.5rem 0; font-weight: bold;">Address:</td>
                        <td style="padding: 0.5rem 0;"><?php echo htmlspecialchars($customer['address'] ?? 'N/A'); ?></td>
                    </tr>
                    <tr>
                        <td style="padding: 0.5rem 0; font-weight: bold;">Total Orders:</td>
                        <td style="padding: 0.5rem 0;"><?php echo $customer['total_orders']; ?></td>
                    </tr>
                    <tr>
                        <td style="padding: 0.5rem 0; font-weight: bold;">Total Spent:</td>
                        <td style="padding: 0.5rem 0;">₱<?php echo number_format($customer['total_spent'], 2); ?></td>
                    </tr>
                    <tr>
                        <td style="padding: 0.5rem 0; font-weight: bold;">Joined:</td>
                        <td style="padding: 0.5rem 0;"><?php echo date('M d, Y', strtotime($customer['created_at'])); ?></td>
                    </tr>
                </table>
            </div>
        </div>

        <form method="post">
            <div style="display: flex; gap: 1rem;">
                <button type="submit" name="confirm_delete" value="1" class="btn" style="background: #dc3545; color: white; border: none;" <?php echo $customer['total_orders'] > 0 ? 'disabled' : ''; ?>>
                    Delete Customer
                </button>
                <a href="customers.php" class="btn">Cancel</a>
            </div>
        </form>
    </div>
</main>

<?php require_once '../includes/footer.php'; ?>