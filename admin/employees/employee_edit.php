<?php
require_once '../includes/header.php';
require_once '../includes/sidebar.php';

$employeeId = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$message = '';

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

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $firstName = trim($_POST['first_name'] ?? '');
    $lastName = trim($_POST['last_name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $position = trim($_POST['position'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $address = trim($_POST['address'] ?? '');
    $hireDate = trim($_POST['hire_date'] ?? '');
    $isAdmin = isset($_POST['is_admin']) ? 1 : 0;
    $notes = trim($_POST['notes'] ?? '');

    // Basic validation
    if (empty($firstName) || empty($lastName) || empty($email)) {
        $message = 'Please fill in all required fields.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $message = 'Please enter a valid email address.';
    } else {
        // Check if email is already used by another employee
        $stmt = $conn->prepare("SELECT id FROM employees WHERE email = ? AND id != ?");
        $stmt->bind_param('si', $email, $employeeId);
        $stmt->execute();
        if ($stmt->get_result()->num_rows > 0) {
            $message = 'This email address is already registered to another employee.';
        } else {
            // Update employee
            $stmt = $conn->prepare("UPDATE employees SET first_name = ?, last_name = ?, email = ?, position = ?, phone = ?, address = ?, hire_date = ?, is_admin = ?, notes = ? WHERE id = ?");
            $stmt->bind_param('sssssssssi', $firstName, $lastName, $email, $position, $phone, $address, $hireDate, $isAdmin, $notes, $employeeId);

            if ($stmt->execute()) {
                header('Location: employee_details.php?id=' . $employeeId . '&message=Employee updated successfully');
                exit();
            } else {
                $message = 'Error updating employee: ' . $conn->error;
            }
        }
    }
}
?>

<main class="admin-main">
    <h2>Edit Employee</h2>

    <?php if ($message): ?>
        <div style="background: #f8d7da; color: #721c24; padding: 1rem; border-radius: 4px; margin-bottom: 1rem;">
            <?php echo htmlspecialchars($message); ?>
        </div>
    <?php endif; ?>

    <div style="background: #fff; padding: 2rem; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); max-width: 800px;">
        <form method="post">
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
                <div class="form-group">
                    <label for="first_name">First Name *</label>
                    <input type="text" id="first_name" name="first_name" value="<?php echo htmlspecialchars($employee['first_name']); ?>" required>
                </div>

                <div class="form-group">
                    <label for="last_name">Last Name *</label>
                    <input type="text" id="last_name" name="last_name" value="<?php echo htmlspecialchars($employee['last_name']); ?>" required>
                </div>
            </div>

            <div class="form-group">
                <label for="email">Email Address *</label>
                <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($employee['email']); ?>" required>
            </div>

            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
                <div class="form-group">
                    <label for="position">Position</label>
                    <input type="text" id="position" name="position" value="<?php echo htmlspecialchars($employee['position'] ?? ''); ?>" placeholder="e.g., Manager, Sales Associate">
                </div>

                <div class="form-group">
                    <label for="hire_date">Hire Date</label>
                    <input type="date" id="hire_date" name="hire_date" value="<?php echo $employee['hire_date'] ?? ''; ?>">
                </div>
            </div>

            <div class="form-group">
                <label for="phone">Phone Number</label>
                <input type="tel" id="phone" name="phone" value="<?php echo htmlspecialchars($employee['phone'] ?? ''); ?>">
            </div>

            <div class="form-group">
                <label for="address">Address</label>
                <textarea id="address" name="address" rows="3"><?php echo htmlspecialchars($employee['address'] ?? ''); ?></textarea>
            </div>

            <div class="form-group">
                <label style="display: flex; align-items: center; gap: 0.5rem;">
                    <input type="checkbox" name="is_admin" value="1" <?php echo $employee['is_admin'] ? 'checked' : ''; ?>>
                    Administrator Access
                </label>
                <small style="color: #666; display: block; margin-top: 0.25rem;">
                    Administrators have full access to the admin panel and can manage all aspects of the store.
                </small>
            </div>

            <div class="form-group">
                <label for="notes">Additional Notes</label>
                <textarea id="notes" name="notes" rows="3" placeholder="Any additional information about this employee..."><?php echo htmlspecialchars($employee['notes'] ?? ''); ?></textarea>
            </div>

            <div style="margin-top: 2rem;">
                <button type="submit" class="btn btn-success">Update Employee</button>
                <a href="employee_details.php?id=<?php echo $employee['id']; ?>" class="btn" style="margin-left: 1rem;">Cancel</a>
            </div>
        </form>
    </div>
</main>

<?php require_once '../includes/footer.php'; ?>