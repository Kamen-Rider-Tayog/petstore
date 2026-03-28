<?php
session_name('petstore_session');
session_start();

if (!isset($_SESSION['is_admin']) || $_SESSION['is_admin'] !== true) {
    header('Location: ' . url('login?error=Access denied'));
    exit;
}

require_once __DIR__ . '/../../backend/config/database.php';
require_once __DIR__ . '/../../backend/functions/helpers.php';

$employeeId = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$action = isset($_GET['action']) ? $_GET['action'] : 'deactivate';

if (!$employeeId) {
    header('Location: employees.php');
    exit();
}

// Get employee details
$stmt = $conn->prepare("SELECT * FROM employees WHERE id = ?");
$stmt->bind_param('i', $employeeId);
$stmt->execute();
$employee = $stmt->get_result()->fetch_assoc();

if (!$employee) {
    header('Location: employees.php');
    exit();
}

// Prevent deleting yourself
if ($employeeId == $_SESSION['employee_id'] ?? 0) {
    $error = "You cannot deactivate your own account.";
} elseif ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if ($action === 'activate') {
        $stmt = $conn->prepare("UPDATE employees SET is_active = 1 WHERE id = ?");
        $stmt->bind_param('i', $employeeId);
        if ($stmt->execute()) {
            header('Location: employees.php?message=Employee activated successfully');
            exit();
        } else {
            $error = 'Error activating employee: ' . $conn->error;
        }
    } else {
        $stmt = $conn->prepare("UPDATE employees SET is_active = 0 WHERE id = ?");
        $stmt->bind_param('i', $employeeId);
        if ($stmt->execute()) {
            header('Location: employees.php?message=Employee deactivated successfully');
            exit();
        } else {
            $error = 'Error deactivating employee: ' . $conn->error;
        }
    }
}

$page_title = ($action === 'activate' ? 'Activate' : 'Deactivate') . ' Employee - ' . $employee['first_name'] . ' ' . $employee['last_name'];
require_once __DIR__ . '/../includes/header.php';

echo '<link rel="stylesheet" href="/Ria-Pet-Store/admin/css/employees.css?v=' . time() . '">';
?>

<div class="admin-dashboard">
    <div class="page-header">
        <div class="header-left">
            <a href="employees.php" class="btn btn-outline">
                <?php echo icon('arrow-left', 16); ?> Back to Employees
            </a>
        </div>
    </div>

    <?php if (isset($error)): ?>
        <div class="message error"><?php echo htmlspecialchars($error); ?></div>
    <?php endif; ?>

    <div class="delete-confirmation">
        <div class="warning-box <?php echo $action === 'activate' ? 'warning-success' : ''; ?>">
            <?php echo $action === 'activate' ? icon('check-circle', 48) : icon('alert-triangle', 48); ?>
            <h3><?php echo $action === 'activate' ? 'Activate Employee' : 'Deactivate Employee'; ?></h3>
            <p>Are you sure you want to <?php echo $action === 'activate' ? 'activate' : 'deactivate'; ?> this employee?</p>
        </div>

        <div class="employee-summary">
            <h3>Employee Details</h3>
            <div class="summary-grid">
                <div class="summary-item">
                    <span class="label">Employee ID:</span>
                    <span class="value">#<?php echo str_pad($employee['id'], 4, '0', STR_PAD_LEFT); ?></span>
                </div>
                <div class="summary-item">
                    <span class="label">Name:</span>
                    <span class="value"><?php echo htmlspecialchars($employee['first_name'] . ' ' . $employee['last_name']); ?></span>
                </div>
                <div class="summary-item">
                    <span class="label">Email:</span>
                    <span class="value"><?php echo htmlspecialchars($employee['email']); ?></span>
                </div>
                <div class="summary-item">
                    <span class="label">Position:</span>
                    <span class="value"><?php echo htmlspecialchars($employee['position'] ?? 'N/A'); ?></span>
                </div>
                <div class="summary-item">
                    <span class="label">Current Status:</span>
                    <span class="value">
                        <span class="status-badge <?php echo $employee['is_active'] ? 'status-active' : 'status-inactive'; ?>">
                            <?php echo $employee['is_active'] ? 'Active' : 'Inactive'; ?>
                        </span>
                    </span>
                </div>
            </div>
        </div>

        <form method="post" class="delete-form">
            <div class="action-buttons">
                <button type="submit" class="btn <?php echo $action === 'activate' ? 'btn-success' : 'btn-danger'; ?>">
                    <?php echo $action === 'activate' ? icon('check', 16) . ' Yes, Activate Employee' : icon('x', 16) . ' Yes, Deactivate Employee'; ?>
                </button>
                <a href="employee_details.php?id=<?php echo $employee['id']; ?>" class="btn btn-outline">Cancel</a>
            </div>
        </form>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>