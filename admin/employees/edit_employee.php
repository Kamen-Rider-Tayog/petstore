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

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $firstName = trim($_POST['first_name'] ?? '');
    $lastName = trim($_POST['last_name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = trim($_POST['password'] ?? '');
    $position = trim($_POST['position'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $address = trim($_POST['address'] ?? '');
    $hireDate = trim($_POST['hire_date'] ?? '');
    $hourlyWage = (float)($_POST['hourly_wage'] ?? 0);
    $isAdmin = isset($_POST['is_admin']) ? 1 : 0;
    $isActive = isset($_POST['is_active']) ? 1 : 0;
    $notes = trim($_POST['notes'] ?? '');

    if (empty($firstName) || empty($lastName) || empty($email)) {
        $error = 'Please fill in all required fields.';
    } else {
        // Check if email already exists for another employee
        $checkStmt = $conn->prepare("SELECT id FROM employees WHERE email = ? AND id != ?");
        $checkStmt->bind_param('si', $email, $employeeId);
        $checkStmt->execute();
        if ($checkStmt->get_result()->num_rows > 0) {
            $error = 'Email address already exists for another employee.';
        } else {
            if (!empty($password)) {
                $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
                $stmt = $conn->prepare("
                    UPDATE employees 
                    SET first_name = ?, last_name = ?, email = ?, password = ?, position = ?, 
                        phone = ?, address = ?, hire_date = ?, hourly_wage = ?, is_admin = ?, is_active = ?, notes = ?
                    WHERE id = ?
                ");
                $stmt->bind_param('ssssssssdiisi', $firstName, $lastName, $email, $hashedPassword, $position, 
                    $phone, $address, $hireDate, $hourlyWage, $isAdmin, $isActive, $notes, $employeeId);
            } else {
                $stmt = $conn->prepare("
                    UPDATE employees 
                    SET first_name = ?, last_name = ?, email = ?, position = ?, 
                        phone = ?, address = ?, hire_date = ?, hourly_wage = ?, is_admin = ?, is_active = ?, notes = ?
                    WHERE id = ?
                ");
                $stmt->bind_param('sssssssdiisi', $firstName, $lastName, $email, $position, 
                    $phone, $address, $hireDate, $hourlyWage, $isAdmin, $isActive, $notes, $employeeId);
            }
            
            if ($stmt->execute()) {
                $success = 'Employee updated successfully!';
                // Refresh employee data
                $stmt = $conn->prepare("SELECT * FROM employees WHERE id = ?");
                $stmt->bind_param('i', $employeeId);
                $stmt->execute();
                $employee = $stmt->get_result()->fetch_assoc();
            } else {
                $error = 'Error updating employee: ' . $conn->error;
            }
        }
    }
}

$page_title = 'Edit Employee - ' . $employee['first_name'] . ' ' . $employee['last_name'];
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
            <button type="submit" form="edit-employee-form" class="btn btn-primary">
                <?php echo icon('save', 16); ?> Save Changes
            </button>
            <a href="employee_details.php?id=<?php echo $employee['id']; ?>" class="btn btn-outline">
                <?php echo icon('eye', 16); ?> View Details
            </a>
        </div>
    </div>

    <?php if ($error): ?>
        <div class="message error"><?php echo htmlspecialchars($error); ?></div>
    <?php endif; ?>
    
    <?php if ($success): ?>
        <div class="message success"><?php echo htmlspecialchars($success); ?></div>
    <?php endif; ?>

    <form method="post" id="edit-employee-form" class="employee-form">
        <div class="form-grid">
            <!-- Personal Information -->
            <div class="info-card">
                <h3><?php echo icon('user', 20); ?> Personal Information</h3>
                <div class="form-group">
                    <label for="first_name">First Name *</label>
                    <input type="text" id="first_name" name="first_name" class="form-control" 
                           value="<?php echo htmlspecialchars($employee['first_name']); ?>" required>
                </div>
                <div class="form-group">
                    <label for="last_name">Last Name *</label>
                    <input type="text" id="last_name" name="last_name" class="form-control" 
                           value="<?php echo htmlspecialchars($employee['last_name']); ?>" required>
                </div>
                <div class="form-group">
                    <label for="email">Email *</label>
                    <input type="email" id="email" name="email" class="form-control" 
                           value="<?php echo htmlspecialchars($employee['email']); ?>" required>
                </div>
                <div class="form-group">
                    <label for="password">Password (leave blank to keep current)</label>
                    <input type="password" id="password" name="password" class="form-control">
                </div>
                <div class="form-group">
                    <label for="phone">Phone</label>
                    <input type="text" id="phone" name="phone" class="form-control" 
                           value="<?php echo htmlspecialchars($employee['phone'] ?? ''); ?>">
                </div>
                <div class="form-group">
                    <label for="address">Address</label>
                    <textarea id="address" name="address" rows="3" class="form-control"><?php echo htmlspecialchars($employee['address'] ?? ''); ?></textarea>
                </div>
            </div>

            <!-- Employment Information -->
            <div class="info-card">
                <h3><?php echo icon('briefcase', 20); ?> Employment Information</h3>
                <div class="form-group">
                    <label for="position">Position</label>
                    <select id="position" name="position" class="form-control">
                        <option value="">-- Select Position --</option>
                        <option value="Veterinarian" <?php echo ($employee['position'] ?? '') == 'Veterinarian' ? 'selected' : ''; ?>>Veterinarian</option>
                        <option value="Groomer" <?php echo ($employee['position'] ?? '') == 'Groomer' ? 'selected' : ''; ?>>Groomer</option>
                        <option value="Receptionist" <?php echo ($employee['position'] ?? '') == 'Receptionist' ? 'selected' : ''; ?>>Receptionist</option>
                        <option value="Assistant" <?php echo ($employee['position'] ?? '') == 'Assistant' ? 'selected' : ''; ?>>Assistant</option>
                        <option value="Manager" <?php echo ($employee['position'] ?? '') == 'Manager' ? 'selected' : ''; ?>>Manager</option>
                        <option value="Trainer" <?php echo ($employee['position'] ?? '') == 'Trainer' ? 'selected' : ''; ?>>Trainer</option>
                    </select>
                </div>
                <div class="form-group">
                    <label for="hire_date">Hire Date</label>
                    <input type="date" id="hire_date" name="hire_date" class="form-control" 
                           value="<?php echo $employee['hire_date'] ?? ''; ?>">
                </div>
                <div class="form-group">
                    <label for="hourly_wage">Hourly Wage (₱)</label>
                    <input type="number" id="hourly_wage" name="hourly_wage" class="form-control" step="0.01" min="0" 
                           value="<?php echo $employee['hourly_wage'] ?? 0; ?>">
                </div>
                <div class="form-group">
                    <label class="checkbox-label">
                        <input type="checkbox" name="is_admin" value="1" <?php echo $employee['is_admin'] ? 'checked' : ''; ?>> Admin Access
                    </label>
                    <small class="help-text">Admins have full access to all admin features</small>
                </div>
                <div class="form-group">
                    <label class="checkbox-label">
                        <input type="checkbox" name="is_active" value="1" <?php echo $employee['is_active'] ? 'checked' : ''; ?>> Active
                    </label>
                    <small class="help-text">Inactive employees cannot log in</small>
                </div>
                <div class="form-group">
                    <label for="notes">Notes</label>
                    <textarea id="notes" name="notes" rows="3" class="form-control" placeholder="Additional notes about this employee..."><?php echo htmlspecialchars($employee['notes'] ?? ''); ?></textarea>
                </div>
            </div>
        </div>
    </form>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>