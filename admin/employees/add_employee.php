<?php
session_name('petstore_session');
session_start();

if (!isset($_SESSION['is_admin']) || $_SESSION['is_admin'] !== true) {
    header('Location: ' . url('login?error=Access denied'));
    exit;
}

require_once __DIR__ . '/../../backend/config/database.php';
require_once __DIR__ . '/../../backend/functions/helpers.php';

$page_title = 'Add Employee';
require_once __DIR__ . '/../includes/header.php';

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
    $notes = trim($_POST['notes'] ?? '');

    if (empty($firstName) || empty($lastName) || empty($email) || empty($password)) {
        $error = 'Please fill in all required fields.';
    } else {
        // Check if email already exists
        $checkStmt = $conn->prepare("SELECT id FROM employees WHERE email = ?");
        $checkStmt->bind_param('s', $email);
        $checkStmt->execute();
        if ($checkStmt->get_result()->num_rows > 0) {
            $error = 'Email address already exists.';
        } else {
            $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
            
            $stmt = $conn->prepare("
                INSERT INTO employees (first_name, last_name, email, password, position, phone, address, hire_date, hourly_wage, is_admin, notes, created_at) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())
            ");
            $stmt->bind_param('ssssssssdis', $firstName, $lastName, $email, $hashedPassword, $position, $phone, $address, $hireDate, $hourlyWage, $isAdmin, $notes);
            
            if ($stmt->execute()) {
                $employeeId = $stmt->insert_id;
                $success = 'Employee added successfully!';
                header('Location: employee_details.php?id=' . $employeeId . '&message=Employee added successfully');
                exit();
            } else {
                $error = 'Error adding employee: ' . $conn->error;
            }
        }
    }
}

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
            <button type="submit" form="add-employee-form" class="btn btn-primary">
                <?php echo icon('save', 16); ?> Save Employee
            </button>
        </div>
    </div>

    <?php if ($error): ?>
        <div class="message error"><?php echo htmlspecialchars($error); ?></div>
    <?php endif; ?>

    <form method="post" id="add-employee-form" class="employee-form">
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
                    <label for="email">Email *</label>
                    <input type="email" id="email" name="email" class="form-control" required>
                </div>
                <div class="form-group">
                    <label for="password">Password *</label>
                    <input type="password" id="password" name="password" class="form-control" required>
                </div>
                <div class="form-group">
                    <label for="phone">Phone</label>
                    <input type="text" id="phone" name="phone" class="form-control">
                </div>
                <div class="form-group">
                    <label for="address">Address</label>
                    <textarea id="address" name="address" rows="3" class="form-control"></textarea>
                </div>
            </div>

            <!-- Employment Information -->
            <div class="info-card">
                <h3><?php echo icon('briefcase', 20); ?> Employment Information</h3>
                <div class="form-group">
                    <label for="position">Position</label>
                    <select id="position" name="position" class="form-control">
                        <option value="">-- Select Position --</option>
                        <option value="Veterinarian">Veterinarian</option>
                        <option value="Groomer">Groomer</option>
                        <option value="Receptionist">Receptionist</option>
                        <option value="Assistant">Assistant</option>
                        <option value="Manager">Manager</option>
                        <option value="Trainer">Trainer</option>
                    </select>
                </div>
                <div class="form-group">
                    <label for="hire_date">Hire Date</label>
                    <input type="date" id="hire_date" name="hire_date" class="form-control">
                </div>
                <div class="form-group">
                    <label for="hourly_wage">Hourly Wage (₱)</label>
                    <input type="number" id="hourly_wage" name="hourly_wage" class="form-control" step="0.01" min="0">
                </div>
                <div class="form-group">
                    <label class="checkbox-label">
                        <input type="checkbox" name="is_admin" value="1"> Admin Access
                    </label>
                    <small class="help-text">Admins have full access to all admin features</small>
                </div>
                <div class="form-group">
                    <label for="notes">Notes</label>
                    <textarea id="notes" name="notes" rows="3" class="form-control" placeholder="Additional notes about this employee..."></textarea>
                </div>
            </div>
        </div>
    </form>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>