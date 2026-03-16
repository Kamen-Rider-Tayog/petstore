<?php
require_once '../../backend/config/database.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: login.php');
    exit();
}

$email = trim($_POST['email'] ?? '');
$password = $_POST['password'] ?? '';
$remember = isset($_POST['remember']);

if (empty($email) || empty($password)) {
    header('Location: login.php?message=Please fill in all fields');
    exit();
}

// Check if employees table has the new columns
$result = $conn->query("SHOW COLUMNS FROM employees LIKE 'is_admin'");
if ($result->num_rows == 0) {
    // Add missing columns
    $conn->query("ALTER TABLE employees ADD COLUMN is_admin BOOLEAN DEFAULT FALSE");
    $conn->query("ALTER TABLE employees ADD COLUMN password VARCHAR(255)");
    $conn->query("ALTER TABLE employees ADD COLUMN email VARCHAR(100)");
}

$stmt = $conn->prepare("SELECT id, first_name, last_name, email, password, is_admin FROM employees WHERE email = ? AND is_admin = TRUE LIMIT 1");
$stmt->bind_param('s', $email);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    header('Location: login.php?message=Invalid email or password');
    exit();
}

$user = $result->fetch_assoc();

// Verify password using bcrypt
if (!password_verify($password, $user['password'])) {
    header('Location: login.php?message=Invalid email or password');
    exit();
}

session_start();
$_SESSION['admin_id'] = $user['id'];
$_SESSION['admin_name'] = $user['first_name'] . ' ' . $user['last_name'];
$_SESSION['admin_email'] = $user['email'];

if ($remember) {
    // Set cookie for 30 days
    setcookie('admin_remember', session_id(), time() + (30 * 24 * 60 * 60), '/');
}

header('Location: dashboard.php');
exit();
?>