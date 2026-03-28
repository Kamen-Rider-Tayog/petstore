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

$page_title = 'Employee Details - ' . $employee['first_name'] . ' ' . $employee['last_name'];
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
        <div class="header-right">
            <div class="action-buttons">
            <a href="edit_employee.php?id=<?php echo $employee['id']; ?>" class="btn btn-primary">
                <?php echo icon('edit', 16); ?> Edit Employee
            </a>
            <?php if ($employee['is_active']): ?>
                <a href="delete_employee.php?id=<?php echo $employee['id']; ?>" class="btn btn-danger" onclick="return confirm('Are you sure you want to deactivate this employee?')">
                    <?php echo icon('x', 16); ?> Deactivate
                </a>
            <?php else: ?>
                <a href="delete_employee.php?id=<?php echo $employee['id']; ?>&action=activate" class="btn btn-success" onclick="return confirm('Are you sure you want to activate this employee?')">
                    <?php echo icon('check', 16); ?> Activate
                </a>
            <?php endif; ?>
        </div>
        </div>
    </div>

    <div class="employee-details-container">
        <div class="employee-header">
            <div class="employee-avatar">
                <?php echo icon('user', 80); ?>
            </div>
            <div class="employee-summary">
                <h1><?php echo htmlspecialchars($employee['first_name'] . ' ' . $employee['last_name']); ?></h1>
                <div class="employee-badges">
                    <span class="status-badge <?php echo $employee['is_active'] ? 'status-active' : 'status-inactive'; ?>">
                        <?php echo $employee['is_active'] ? 'Active' : 'Inactive'; ?>
                    </span>
                    <span class="status-badge <?php echo $employee['is_admin'] ? 'status-admin' : 'status-staff'; ?>">
                        <?php echo $employee['is_admin'] ? 'Admin' : 'Staff'; ?>
                    </span>
                </div>
                <p class="employee-position"><?php echo htmlspecialchars($employee['position'] ?? 'Position not set'); ?></p>
            </div>
        </div>

        <div class="details-grid">
            <!-- Contact Information -->
            <div class="info-card">
                <h3><?php echo icon('mail', 20); ?> Contact Information</h3>
                <table class="info-table">
                    <tr>
                        <td class="label">Email:</td>
                        <td><?php echo htmlspecialchars($employee['email']); ?></td>
                    </tr>
                    <?php if (!empty($employee['phone'])): ?>
                    <tr>
                        <td class="label">Phone:</td>
                        <td><?php echo htmlspecialchars($employee['phone']); ?></td>
                    </tr>
                    <?php endif; ?>
                    <?php if (!empty($employee['address'])): ?>
                    <tr>
                        <td class="label">Address:</td>
                        <td><?php echo nl2br(htmlspecialchars($employee['address'])); ?></td>
                    </tr>
                    <?php endif; ?>
                </table>
            </div>

            <!-- Employment Information -->
            <div class="info-card">
                <h3><?php echo icon('briefcase', 20); ?> Employment Information</h3>
                <table class="info-table">
                    <tr>
                        <td class="label">Employee ID:</td>
                        <td>#<?php echo str_pad($employee['id'], 4, '0', STR_PAD_LEFT); ?></td>
                    </tr>
                    <?php if (!empty($employee['position'])): ?>
                    <tr>
                        <td class="label">Position:</td>
                        <td><?php echo htmlspecialchars($employee['position']); ?></td>
                    </tr>
                    <?php endif; ?>
                    <?php if (!empty($employee['hire_date'])): ?>
                    <tr>
                        <td class="label">Hire Date:</td>
                        <td><?php echo date('F j, Y', strtotime($employee['hire_date'])); ?></td>
                    </tr>
                    <?php endif; ?>
                    <?php if (!empty($employee['hourly_wage'])): ?>
                    <tr>
                        <td class="label">Hourly Wage:</td>
                        <td>₱<?php echo number_format($employee['hourly_wage'], 2); ?></td>
                    </tr>
                    <?php endif; ?>
                </table>
            </div>
        </div>

        <?php if (!empty($employee['notes'])): ?>
        <div class="info-card">
            <h3><?php echo icon('file', 20); ?> Notes</h3>
            <div class="notes-content">
                <?php echo nl2br(htmlspecialchars($employee['notes'])); ?>
            </div>
        </div>
        <?php endif; ?>

        
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>