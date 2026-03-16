<?php
require_once '../includes/header.php';
require_once '../includes/sidebar.php';

$employeeId = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if (!$employeeId) {
    header('Location: employees.php');
    exit();
}

// Get employee data
$stmt = $conn->prepare("SELECT * FROM employees WHERE id = ?");
$stmt->bind_param('i', $employeeId);
$stmt->execute();
$employee = $stmt->get_result()->fetch_assoc();

if (!$employee) {
    header('Location: employees.php');
    exit();
}

$message = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['confirm_delete'])) {
    // Check if this is the last admin
    if ($employee['is_admin']) {
        $stmt = $conn->prepare("SELECT COUNT(*) as admin_count FROM employees WHERE is_admin = 1");
        $stmt->execute();
        $adminCount = $stmt->get_result()->fetch_assoc()['admin_count'];

        if ($adminCount <= 1) {
            $message = 'Cannot delete the last administrator. At least one admin must remain.';
        }
    }

    if (empty($message)) {
        // Delete employee
        $stmt = $conn->prepare("DELETE FROM employees WHERE id = ?");
        $stmt->bind_param('i', $employeeId);

        if ($stmt->execute()) {
            header('Location: employees.php?message=Employee deleted successfully');
            exit();
        } else {
            $message = 'Error deleting employee: ' . $conn->error;
        }
    }
}
?>

<main class="admin-main">
    <h2>Delete Employee</h2>

    <?php if ($message): ?>
        <div style="background: #f8d7da; color: #721c24; padding: 1rem; border-radius: 4px; margin-bottom: 1rem;">
            <?php echo htmlspecialchars($message); ?>
        </div>
    <?php endif; ?>

    <div style="background: #fff; padding: 2rem; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1);">
        <div style="background: #fff3cd; border: 1px solid #ffeaa7; padding: 1rem; border-radius: 4px; margin-bottom: 2rem;">
            <h3 style="color: #856404; margin-top: 0;">⚠️ Warning: This action cannot be undone!</h3>
            <p style="margin-bottom: 0;">Are you sure you want to delete this employee? This will permanently remove the employee from the database and revoke their access to the system.</p>
            <?php if ($employee['is_admin']): ?>
                <p style="color: #dc3545; font-weight: bold; margin-top: 1rem; margin-bottom: 0;">
                    ⚠️ This employee has administrator privileges. Deleting them will remove their admin access.
                </p>
            <?php endif; ?>
        </div>

        <div style="display: grid; grid-template-columns: 1fr 2fr; gap: 2rem; margin-bottom: 2rem;">
            <div>
                <div style="width: 150px; height: 150px; background: #007bff; border-radius: 50%; display: flex; align-items: center; justify-content: center; color: white; font-size: 3rem; font-weight: bold;">
                    <?php echo strtoupper(substr($employee['first_name'], 0, 1) . substr($employee['last_name'], 0, 1)); ?>
                </div>
            </div>

            <div>
                <h3><?php echo htmlspecialchars($employee['first_name'] . ' ' . $employee['last_name']); ?></h3>
                <table style="width: 100%; border-collapse: collapse;">
                    <tr>
                        <td style="padding: 0.5rem 0; font-weight: bold; width: 120px;">Email:</td>
                        <td style="padding: 0.5rem 0;"><?php echo htmlspecialchars($employee['email']); ?></td>
                    </tr>
                    <tr>
                        <td style="padding: 0.5rem 0; font-weight: bold;">Position:</td>
                        <td style="padding: 0.5rem 0;"><?php echo htmlspecialchars($employee['position'] ?? 'N/A'); ?></td>
                    </tr>
                    <tr>
                        <td style="padding: 0.5rem 0; font-weight: bold;">Role:</td>
                        <td style="padding: 0.5rem 0;">
                            <?php if ($employee['is_admin']): ?>
                                <span class="status-badge status-admin">Administrator</span>
                            <?php else: ?>
                                <span class="status-badge status-employee">Employee</span>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <tr>
                        <td style="padding: 0.5rem 0; font-weight: bold;">Phone:</td>
                        <td style="padding: 0.5rem 0;"><?php echo htmlspecialchars($employee['phone'] ?? 'N/A'); ?></td>
                    </tr>
                    <tr>
                        <td style="padding: 0.5rem 0; font-weight: bold;">Hire Date:</td>
                        <td style="padding: 0.5rem 0;"><?php echo $employee['hire_date'] ? date('M d, Y', strtotime($employee['hire_date'])) : 'N/A'; ?></td>
                    </tr>
                    <tr>
                        <td style="padding: 0.5rem 0; font-weight: bold;">Joined:</td>
                        <td style="padding: 0.5rem 0;"><?php echo date('M d, Y', strtotime($employee['created_at'])); ?></td>
                    </tr>
                </table>
            </div>
        </div>

        <form method="post">
            <div style="display: flex; gap: 1rem;">
                <button type="submit" name="confirm_delete" value="1" class="btn" style="background: #dc3545; color: white; border: none;">
                    Delete Employee
                </button>
                <a href="employees.php" class="btn">Cancel</a>
            </div>
        </form>
    </div>
</main>

<?php require_once '../includes/footer.php'; ?>