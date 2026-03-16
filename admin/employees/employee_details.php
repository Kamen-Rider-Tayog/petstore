<?php
require_once '../includes/header.php';
require_once '../includes/sidebar.php';

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
?>

<main class="admin-main">
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 2rem;">
        <h2>Employee Details - <?php echo htmlspecialchars($employee['first_name'] . ' ' . $employee['last_name']); ?></h2>
        <div>
            <a href="employee_edit.php?id=<?php echo $employee['id']; ?>" class="btn btn-warning">Edit Employee</a>
            <a href="employees.php" class="btn">Back to Employees</a>
        </div>
    </div>

    <div style="display: grid; grid-template-columns: 1fr 2fr; gap: 2rem;">
        <!-- Employee Information -->
        <div style="background: #fff; padding: 2rem; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1);">
            <h3>Employee Information</h3>
            <table style="width: 100%; border-collapse: collapse;">
                <tr>
                    <td style="padding: 0.5rem 0; font-weight: bold; width: 120px;">Employee ID:</td>
                    <td style="padding: 0.5rem 0;"><?php echo $employee['id']; ?></td>
                </tr>
                <tr>
                    <td style="padding: 0.5rem 0; font-weight: bold;">Name:</td>
                    <td style="padding: 0.5rem 0;"><?php echo htmlspecialchars($employee['first_name'] . ' ' . $employee['last_name']); ?></td>
                </tr>
                <tr>
                    <td style="padding: 0.5rem 0; font-weight: bold;">Email:</td>
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
                <?php if (!empty($employee['phone'])): ?>
                <tr>
                    <td style="padding: 0.5rem 0; font-weight: bold;">Phone:</td>
                    <td style="padding: 0.5rem 0;"><?php echo htmlspecialchars($employee['phone']); ?></td>
                </tr>
                <?php endif; ?>
                <?php if (!empty($employee['address'])): ?>
                <tr>
                    <td style="padding: 0.5rem 0; font-weight: bold;">Address:</td>
                    <td style="padding: 0.5rem 0;"><?php echo nl2br(htmlspecialchars($employee['address'])); ?></td>
                </tr>
                <?php endif; ?>
                <?php if (!empty($employee['hire_date'])): ?>
                <tr>
                    <td style="padding: 0.5rem 0; font-weight: bold;">Hire Date:</td>
                    <td style="padding: 0.5rem 0;"><?php echo date('M d, Y', strtotime($employee['hire_date'])); ?></td>
                </tr>
                <?php endif; ?>
                <tr>
                    <td style="padding: 0.5rem 0; font-weight: bold;">Joined:</td>
                    <td style="padding: 0.5rem 0;"><?php echo date('M d, Y', strtotime($employee['created_at'])); ?></td>
                </tr>
            </table>
        </div>

        <!-- Employee Avatar/Photo -->
        <div style="background: #fff; padding: 2rem; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1);">
            <h3>Employee Photo</h3>
            <div style="text-align: center;">
                <div style="width: 200px; height: 200px; background: #007bff; border-radius: 50%; display: flex; align-items: center; justify-content: center; color: white; font-size: 4rem; font-weight: bold; margin: 0 auto 1rem;">
                    <?php echo strtoupper(substr($employee['first_name'], 0, 1) . substr($employee['last_name'], 0, 1)); ?>
                </div>
                <p style="color: #666; margin: 0;">Employee Avatar</p>
            </div>
        </div>
    </div>

    <!-- Additional Information -->
    <?php if (!empty($employee['notes'])): ?>
    <div style="background: #fff; padding: 2rem; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); margin-top: 2rem;">
        <h3>Additional Notes</h3>
        <p><?php echo nl2br(htmlspecialchars($employee['notes'])); ?></p>
    </div>
    <?php endif; ?>
</main>

<?php require_once '../includes/footer.php'; ?>