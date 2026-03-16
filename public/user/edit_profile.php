<?php
require_once '../../backend/config/database.php';
require_once '../../backend/includes/auth.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

require_once '../../backend/includes/header.php';

$userId = $_SESSION['user_id'];
$message = '';
$messageType = '';

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $firstName = trim($_POST['first_name'] ?? '');
        $lastName = trim($_POST['last_name'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $phone = trim($_POST['phone'] ?? '');
        $address = trim($_POST['address'] ?? '');
        $city = trim($_POST['city'] ?? '');
        $state = trim($_POST['state'] ?? '');
        $zipCode = trim($_POST['zip_code'] ?? '');
        $currentPassword = $_POST['current_password'] ?? '';
        $newPassword = $_POST['new_password'] ?? '';
        $confirmPassword = $_POST['confirm_password'] ?? '';

        // Validate input
        if (empty($firstName) || empty($lastName) || empty($email)) {
            throw new Exception('First name, last name, and email are required.');
        }

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            throw new Exception('Please enter a valid email address.');
        }

        // Check if email is already taken by another user
        $stmt = $conn->prepare("SELECT id FROM customers WHERE email = ? AND id != ?");
        $stmt->bind_param("si", $email, $userId);
        $stmt->execute();
        $result = $stmt->get_result();
        if ($result->fetch_assoc()) {
            throw new Exception('This email address is already in use.');
        }

        // Handle password change
        if (!empty($newPassword)) {
            if (empty($currentPassword)) {
                throw new Exception('Current password is required to change password.');
            }

            // Verify current password
            $stmt = $conn->prepare("SELECT password FROM customers WHERE id = ?");
            $stmt->bind_param("i", $userId);
            $stmt->execute();
            $result = $stmt->get_result();
            $userData = $result->fetch_assoc();

            if (!password_verify($currentPassword, $userData['password'])) {
                throw new Exception('Current password is incorrect.');
            }

            if (strlen($newPassword) < 8) {
                throw new Exception('New password must be at least 8 characters long.');
            }

            if ($newPassword !== $confirmPassword) {
                throw new Exception('New passwords do not match.');
            }

            $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);
        }

        // Update user profile
        if (!empty($newPassword)) {
            $stmt = $conn->prepare("
                UPDATE customers SET
                    first_name = ?,
                    last_name = ?,
                    email = ?,
                    phone = ?,
                    address = ?,
                    city = ?,
                    state = ?,
                    zip_code = ?,
                    password = ?,
                    updated_at = NOW()
                WHERE id = ?
            ");
            $stmt->bind_param("sssssssssi", $firstName, $lastName, $email, $phone, $address, $city, $state, $zipCode, $hashedPassword, $userId);
            $stmt->execute();
        } else {
            $stmt = $conn->prepare("
                UPDATE customers SET
                    first_name = ?,
                    last_name = ?,
                    email = ?,
                    phone = ?,
                    address = ?,
                    city = ?,
                    state = ?,
                    zip_code = ?,
                    updated_at = NOW()
                WHERE id = ?
            ");
            $stmt->bind_param("ssssssssi", $firstName, $lastName, $email, $phone, $address, $city, $state, $zipCode, $userId);
            $stmt->execute();
        }

        // Update session data
        $_SESSION['user_name'] = $firstName . ' ' . $lastName;
        $_SESSION['user_email'] = $email;

        $message = 'Profile updated successfully!';
        $messageType = 'success';

    } catch (Exception $e) {
        $message = $e->getMessage();
        $messageType = 'error';
    }
}

// Get user data
try {
    $stmt = $conn->prepare("SELECT * FROM customers WHERE id = ?");
    $stmt->bind_param("i", $userId);
    $stmt->execute();
    $result = $stmt->get_result();
    $user = $result->fetch_assoc();

    if (!$user) {
        header('Location: login.php');
        exit;
    }
} catch (Exception $e) {
    $message = 'Error loading profile data.';
    $messageType = 'error';
    $user = [];
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Profile - Pet Store</title>
    <link rel="stylesheet" href="../../assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="../../assets/css/edit_profile.css">
</head>
<body>
    <?php include '../../backend/includes/header.php'; ?>

    <!-- Edit Profile Hero -->
    <section class="edit-profile-hero">
        <div class="container">
            <h1>Edit Profile</h1>
        </div>
    </section>

    <!-- Edit Profile Content -->
    <section class="edit-profile-content">
        <div class="container">
            <div class="edit-profile-container">
                <div class="form-header">
                    <h1>Update Your Information</h1>
                </div>

                <div class="form-body">
                    <?php if ($message): ?>
                        <div class="message <?php echo $messageType; ?>">
                            <?php echo htmlspecialchars($message); ?>
                        </div>
                    <?php endif; ?>

                    <form method="POST">
                        <!-- Personal Information -->
                        <div class="form-section">
                            <h2 class="section-title">
                                <i class="fas fa-user"></i> Personal Information
                            </h2>

                            <div class="form-row">
                                <div class="form-group">
                                    <label for="first_name">First Name <span class="required">*</span></label>
                                    <input type="text" id="first_name" name="first_name" value="<?php echo htmlspecialchars($user['first_name'] ?? ''); ?>" required>
                                </div>
                                <div class="form-group">
                                    <label for="last_name">Last Name <span class="required">*</span></label>
                                    <input type="text" id="last_name" name="last_name" value="<?php echo htmlspecialchars($user['last_name'] ?? ''); ?>" required>
                                </div>
                            </div>

                            <div class="form-row">
                                <div class="form-group">
                                    <label for="email">Email Address <span class="required">*</span></label>
                                    <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($user['email'] ?? ''); ?>" required>
                                    <div class="help-text">We'll use this to send you order updates and account notifications</div>
                                </div>
                                <div class="form-group">
                                    <label for="phone">Phone Number</label>
                                    <input type="tel" id="phone" name="phone" value="<?php echo htmlspecialchars($user['phone'] ?? ''); ?>">
                                    <div class="help-text">For delivery and service coordination</div>
                                </div>
                            </div>
                        </div>

                        <!-- Address Information -->
                        <div class="form-section">
                            <h2 class="section-title">
                                <i class="fas fa-map-marker-alt"></i> Address Information
                            </h2>

                            <div class="form-group">
                                <label for="address">Street Address</label>
                                <input type="text" id="address" name="address" value="<?php echo htmlspecialchars($user['address'] ?? ''); ?>">
                            </div>

                            <div class="form-row">
                                <div class="form-group">
                                    <label for="city">City</label>
                                    <input type="text" id="city" name="city" value="<?php echo htmlspecialchars($user['city'] ?? ''); ?>">
                                </div>
                                <div class="form-group">
                                    <label for="state">State</label>
                                    <input type="text" id="state" name="state" value="<?php echo htmlspecialchars($user['state'] ?? ''); ?>">
                                </div>
                                <div class="form-group">
                                    <label for="zip_code">ZIP Code</label>
                                    <input type="text" id="zip_code" name="zip_code" value="<?php echo htmlspecialchars($user['zip_code'] ?? ''); ?>">
                                </div>
                            </div>
                        </div>

                        <!-- Password Change -->
                        <div class="form-section">
                            <h2 class="section-title">
                                <i class="fas fa-lock"></i> Change Password
                            </h2>

                            <div class="password-toggle">
                                <input type="checkbox" id="changePassword" onchange="togglePasswordFields()">
                                <label for="changePassword">I want to change my password</label>
                            </div>

                            <div class="password-section" id="passwordFields" style="display: none;">
                                <div class="form-group">
                                    <label for="current_password">Current Password <span class="required">*</span></label>
                                    <input type="password" id="current_password" name="current_password">
                                    <div class="help-text">Enter your current password to verify changes</div>
                                </div>

                                <div class="form-row">
                                    <div class="form-group">
                                        <label for="new_password">New Password <span class="required">*</span></label>
                                        <input type="password" id="new_password" name="new_password">
                                        <div class="help-text">At least 8 characters long</div>
                                    </div>
                                    <div class="form-group">
                                        <label for="confirm_password">Confirm New Password <span class="required">*</span></label>
                                        <input type="password" id="confirm_password" name="confirm_password">
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Form Actions -->
                        <div class="btn-group">
                            <button type="submit" class="btn-primary">
                                <i class="fas fa-save"></i> Save Changes
                            </button>
                            <a href="user_profile.php" class="btn-secondary">
                                <i class="fas fa-times"></i> Cancel
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </section>

    <?php include '../../backend/includes/footer.php'; ?>

    <script>
        function togglePasswordFields() {
            const checkbox = document.getElementById('changePassword');
            const passwordFields = document.getElementById('passwordFields');
            const passwordInputs = passwordFields.querySelectorAll('input[type="password"]');

            if (checkbox.checked) {
                passwordFields.style.display = 'block';
                passwordInputs.forEach(input => input.required = true);
            } else {
                passwordFields.style.display = 'none';
                passwordInputs.forEach(input => {
                    input.required = false;
                    input.value = '';
                });
            }
        }

        // Form validation
        document.querySelector('form').addEventListener('submit', function(e) {
            const changePassword = document.getElementById('changePassword').checked;
            const newPassword = document.getElementById('new_password').value;
            const confirmPassword = document.getElementById('confirm_password').value;

            if (changePassword) {
                if (newPassword.length < 8) {
                    alert('New password must be at least 8 characters long.');
                    e.preventDefault();
                    return;
                }

                if (newPassword !== confirmPassword) {
                    alert('New passwords do not match.');
                    e.preventDefault();
                    return;
                }
            }
        });
    </script>
</body>
</html>