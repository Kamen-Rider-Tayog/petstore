<?php
require_once __DIR__ . '/../../backend/functions/helpers.php';
session_start();
require_once __DIR__ . '/../../backend/config/database.php';
require_once __DIR__ . '/../../backend/includes/cart_functions.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ' . url('login'));
    exit;
}

$username = $_POST['username'] ?? '';
$password = $_POST['password'] ?? '';

if (empty($username) || empty($password)) {
    header('Location: ' . url('login?error=Username and password are required'));
    exit;
}

// Use prepared statement
$sql = "SELECT * FROM users WHERE username = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $username);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    $user = $result->fetch_assoc();
    
    if (password_verify($password, $user['password'])) {
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['username'] = $user['username'];
        $_SESSION['full_name'] = $user['full_name'];
        $_SESSION['customer_id'] = $user['id'];
        $_SESSION['customer_name'] = $user['full_name'] ?: $user['username'];

        // Merge any guest cart items into the user's cart
        if (function_exists('mergeSessionCartIntoUser')) {
            mergeSessionCartIntoUser($user['id']);
        }

        $stmt->close();
        header('Location: ' . url(''));
        exit;
    }
}

$stmt->close();
header('Location: ' . url('login?error=Invalid username or password'));
exit;
?>