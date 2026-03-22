<?php
require_once __DIR__ . '/../../backend/config/database.php';
require_once __DIR__ . '/../../backend/functions/helpers.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ' . url('register'));
    exit;
}

$first_name = trim($_POST['first_name'] ?? '');
$last_name = trim($_POST['last_name'] ?? '');
$email = trim($_POST['email'] ?? '');
$password = $_POST['password'] ?? '';
$confirm_password = $_POST['confirm_password'] ?? '';

$errors = [];

if (empty($first_name)) {
    $errors[] = "First name is required";
}

if (empty($last_name)) {
    $errors[] = "Last name is required";
}

if (empty($email)) {
    $errors[] = "Email is required";
} elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $errors[] = "Invalid email address";
}

if (empty($password)) {
    $errors[] = "Password is required";
} elseif (strlen($password) < 6) {
    $errors[] = "Password must be at least 6 characters";
}

if ($password !== $confirm_password) {
    $errors[] = "Passwords do not match";
}

// Check if email exists
if (empty($errors)) {
    $check_sql = "SELECT id FROM customers WHERE email = ?";
    $stmt = $conn->prepare($check_sql);
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $check_result = $stmt->get_result();
    
    if ($check_result->num_rows > 0) {
        $errors[] = "Email already registered";
    }
    $stmt->close();
}

if (!empty($errors)) {
    $error_string = implode(', ', $errors);
    header('Location: ' . url('register?error=' . urlencode($error_string)));
    exit;
}

// Create customer
$hashed_password = password_hash($password, PASSWORD_DEFAULT);

$insert_sql = "INSERT INTO customers (first_name, last_name, email, password) VALUES (?, ?, ?, ?)";
$stmt = $conn->prepare($insert_sql);
$stmt->bind_param("ssss", $first_name, $last_name, $email, $hashed_password);

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