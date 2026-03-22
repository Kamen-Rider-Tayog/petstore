<?php
session_name('petstore_session');
session_start();

require_once __DIR__ . '/../../backend/config/database.php';
require_once __DIR__ . '/../../backend/functions/helpers.php';

// Check if user is logged in
if (!isset($_SESSION['customer_id'])) {
    header('Location: ' . url('login'));
    exit;
}

$userId = $_SESSION['customer_id'];
$message = '';
$messageType = '';

// Handle form submission - THIS MUST BE BEFORE HEADER
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $firstName = trim($_POST['first_name'] ?? '');
        $lastName = trim($_POST['last_name'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $phone = trim($_POST['phone'] ?? '');
        $addressLine1 = trim($_POST['address_line1'] ?? '');
        $addressLine2 = trim($_POST['address_line2'] ?? '');
        $city = trim($_POST['city'] ?? '');
        $state = trim($_POST['state'] ?? '');
        $zipCode = trim($_POST['zip_code'] ?? '');
        $country = trim($_POST['country'] ?? 'Philippines');
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
        $hashedPassword = null;
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

            if (strlen($newPassword) < 6) {
                throw new Exception('New password must be at least 6 characters long.');
            }

            if ($newPassword !== $confirmPassword) {
                throw new Exception('New passwords do not match.');
            }

            $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);
        }

        // Update user profile
        if ($hashedPassword) {
            $stmt = $conn->prepare("
                UPDATE customers SET
                    first_name = ?,
                    last_name = ?,
                    email = ?,
                    phone = ?,
                    password = ?,
                    updated_at = NOW()
                WHERE id = ?
            ");
            $stmt->bind_param("sssssi", $firstName, $lastName, $email, $phone, $hashedPassword, $userId);
        } else {
            $stmt = $conn->prepare("
                UPDATE customers SET
                    first_name = ?,
                    last_name = ?,
                    email = ?,
                    phone = ?,
                    updated_at = NOW()
                WHERE id = ?
            ");
            $stmt->bind_param("ssssi", $firstName, $lastName, $email, $phone, $userId);
        }
        $stmt->execute();

        // Update or insert address
        $addrCheck = $conn->prepare("SELECT id FROM addresses WHERE customer_id = ? AND is_default = 1");
        $addrCheck->bind_param("i", $userId);
        $addrCheck->execute();
        $addrResult = $addrCheck->get_result();
        $hasAddress = $addrResult->num_rows > 0;
        $addrCheck->close();

        if ($hasAddress) {
            // Update existing address
            $addrStmt = $conn->prepare("
                UPDATE addresses SET 
                    address_line1 = ?,
                    address_line2 = ?,
                    city = ?,
                    state = ?,
                    zip_code = ?,
                    country = ?
                WHERE customer_id = ? AND is_default = 1
            ");
            $addrStmt->bind_param("ssssssi", $addressLine1, $addressLine2, $city, $state, $zipCode, $country, $userId);
        } else {
            // Insert new address
            $addrStmt = $conn->prepare("
                INSERT INTO addresses (customer_id, address_line1, address_line2, city, state, zip_code, country, is_default)
                VALUES (?, ?, ?, ?, ?, ?, ?, 1)
            ");
            $addrStmt->bind_param("issssss", $userId, $addressLine1, $addressLine2, $city, $state, $zipCode, $country);
        }
        $addrStmt->execute();
        $addrStmt->close();

        // Update session data
        $_SESSION['customer_name'] = $firstName . ' ' . $lastName;
        $_SESSION['customer_email'] = $email;

        // Redirect BEFORE any output
        header('Location: ' . url('user_profile?updated=1'));
        exit;

    } catch (Exception $e) {
        $message = $e->getMessage();
        $messageType = 'error';
        // Store error in session to show after redirect
        $_SESSION['profile_error'] = $message;
        $_SESSION['profile_error_type'] = 'error';
        header('Location: ' . url('edit_profile'));
        exit;
    }
}

// If there's an error stored in session, show it
if (isset($_SESSION['profile_error'])) {
    $message = $_SESSION['profile_error'];
    $messageType = $_SESSION['profile_error_type'];
    unset($_SESSION['profile_error']);
    unset($_SESSION['profile_error_type']);
}

// Include header AFTER processing POST
require_once __DIR__ . '/../../backend/includes/header.php';

// Get user data
try {
    $stmt = $conn->prepare("SELECT * FROM customers WHERE id = ?");
    $stmt->bind_param("i", $userId);
    $stmt->execute();
    $result = $stmt->get_result();
    $user = $result->fetch_assoc();

    // Get default address
    $address = [];
    $addrStmt = $conn->prepare("SELECT * FROM addresses WHERE customer_id = ? AND is_default = 1");
    $addrStmt->bind_param("i", $userId);
    $addrStmt->execute();
    $addrResult = $addrStmt->get_result();
    if ($addrResult->num_rows > 0) {
        $address = $addrResult->fetch_assoc();
    }
    $addrStmt->close();

    if (!$user) {
        header('Location: ' . url('login'));
        exit;
    }
} catch (Exception $e) {
    $message = 'Error loading profile data.';
    $messageType = 'error';
    $user = [];
    $address = [];
}

$page_title = 'Edit Profile';
?>
<link rel="stylesheet" href="/Ria-Pet-Store/assets/css/user/edit_profile.css?v=<?php echo time(); ?>">

<div class="edit-profile-page">
    <section class="edit-profile-hero">
        <div class="container">
            <h1>Edit Profile</h1>
            <p>Update your personal information</p>
        </div>
    </section>

    <section class="edit-profile-content">
        <div class="container">
            <div class="edit-profile-container">
                <?php if ($message): ?>
                    <div class="message <?php echo $messageType; ?>">
                        <?php echo htmlspecialchars($message); ?>
                    </div>
                <?php endif; ?>

                <form method="POST">
                    <!-- Personal Information -->
                    <div class="form-section">
                        <h2 class="section-title">
                            <?php echo icon('user', 20); ?> Personal Information
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
                            </div>
                            <div class="form-group">
                                <label for="phone">Phone Number</label>
                                <input type="tel" id="phone" name="phone" value="<?php echo htmlspecialchars($user['phone'] ?? ''); ?>">
                            </div>
                        </div>
                    </div>

                    <!-- Address Information -->
                    <div class="form-section">
                        <h2 class="section-title">
                            <?php echo icon('marker', 20); ?> Address Information
                        </h2>

                        <div class="form-group">
                            <label for="address_line1">Street Address <span class="required">*</span></label>
                            <input type="text" id="address_line1" name="address_line1" value="<?php echo htmlspecialchars($address['address_line1'] ?? ''); ?>" required>
                        </div>

                        <div class="form-group">
                            <label for="address_line2">Address Line 2 (Optional)</label>
                            <input type="text" id="address_line2" name="address_line2" value="<?php echo htmlspecialchars($address['address_line2'] ?? ''); ?>">
                        </div>

                        <div class="form-row">
                            <div class="form-group">
                                <label for="city">City <span class="required">*</span></label>
                                <input type="text" id="city" name="city" value="<?php echo htmlspecialchars($address['city'] ?? ''); ?>" required>
                            </div>
                            <div class="form-group">
                                <label for="state">State/Province</label>
                                <input type="text" id="state" name="state" value="<?php echo htmlspecialchars($address['state'] ?? ''); ?>">
                            </div>
                            <div class="form-group">
                                <label for="zip_code">ZIP Code</label>
                                <input type="text" id="zip_code" name="zip_code" value="<?php echo htmlspecialchars($address['zip_code'] ?? ''); ?>">
                            </div>
                        </div>
                        
                        <div class="form-group">
                            <label for="country">Country</label>
                            <input type="text" id="country" name="country" value="<?php echo htmlspecialchars($address['country'] ?? 'Philippines'); ?>">
                        </div>
                    </div>

                    <!-- Password Change -->
                    <div class="form-section">
                        <h2 class="section-title">
                            <?php echo icon('lock', 20); ?> Change Password
                        </h2>

                        <div class="password-toggle">
                            <input type="checkbox" id="changePassword" onchange="togglePasswordFields()">
                            <label for="changePassword">I want to change my password</label>
                        </div>

                        <div class="password-section" id="passwordFields" style="display: none;">
                            <div class="form-group">
                                <label for="current_password">Current Password <span class="required">*</span></label>
                                <input type="password" id="current_password" name="current_password">
                            </div>

                            <div class="form-row">
                                <div class="form-group">
                                    <label for="new_password">New Password <span class="required">*</span></label>
                                    <input type="password" id="new_password" name="new_password">
                                    <div class="help-text">At least 6 characters long</div>
                                </div>
                                <div class="form-group">
                                    <label for="confirm_password">Confirm New Password <span class="required">*</span></label>
                                    <input type="password" id="confirm_password" name="confirm_password">
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="btn-group">
                        <button type="submit" class="btn btn-primary">
                            <?php echo icon('check', 16); ?> Save Changes
                        </button>
                        <a href="<?php echo url('user_profile'); ?>" class="btn btn-secondary">
                            <?php echo icon('x', 16); ?> Cancel
                        </a>
                        <a href="<?php echo url('logout'); ?>" class="btn btn-danger">
                            <?php echo icon('x', 16); ?> Logout
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </section>
</div>

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

document.querySelector('form')?.addEventListener('submit', function(e) {
    const changePassword = document.getElementById('changePassword')?.checked;
    const newPassword = document.getElementById('new_password')?.value;
    const confirmPassword = document.getElementById('confirm_password')?.value;

    if (changePassword) {
        if (newPassword.length < 6) {
            alert('New password must be at least 6 characters long.');
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

<?php require_once __DIR__ . '/../../backend/includes/footer.php'; ?>