<?php
require_once __DIR__ . '/../../backend/config/database.php';
require_once __DIR__ . '/../../backend/functions/helpers.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ' . url('register'));
    exit;
}

$username = trim($_POST['username'] ?? '');
$password = $_POST['password'] ?? '';
$confirm_password = $_POST['confirm_password'] ?? '';
$full_name = trim($_POST['full_name'] ?? '');
$email = trim($_POST['email'] ?? '');

$errors = [];

if (empty($username)) {
    $errors[] = "Username is required";
} elseif (strlen($username) < 3) {
    $errors[] = "Username must be at least 3 characters";
}

if (empty($password)) {
    $errors[] = "Password is required";
} elseif (strlen($password) < 6) {
    $errors[] = "Password must be at least 6 characters";
}

if ($password !== $confirm_password) {
    $errors[] = "Passwords do not match";
}

if (!empty($email) && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $errors[] = "Invalid email address";
}

// Check if username exists
if (empty($errors)) {
    $check_sql = "SELECT id FROM users WHERE username = ?";
    $stmt = $conn->prepare($check_sql);
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $check_result = $stmt->get_result();
    
    if ($check_result->num_rows > 0) {
        $errors[] = "Username already exists";
    }
    $stmt->close();
}

if (!empty($errors)) {
    $error_string = implode(', ', $errors);
    header('Location: ' . url('register?error=' . urlencode($error_string)));
    exit;
}

// Create user
$hashed_password = password_hash($password, PASSWORD_DEFAULT);

$insert_sql = "INSERT INTO users (username, password, full_name, email) VALUES (?, ?, ?, ?)";
$stmt = $conn->prepare($insert_sql);
$stmt->bind_param("ssss", $username, $hashed_password, $full_name, $email);

if ($stmt->execute()) {
    $stmt->close();
    header('Location: ' . url('login?registered=1'));
    exit;
} else {
    $stmt->close();
    header('Location: ' . url('register?error=Registration failed. Please try again.'));
    exit;
}
?>