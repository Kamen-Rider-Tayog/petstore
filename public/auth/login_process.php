<?php
session_start();
require_once '../../backend/config/database.php';
require_once '../../backend/includes/cart_functions.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: login');
    exit;
}

$username = $_POST['username'] ?? '';
$password = $_POST['password'] ?? '';

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

        // Merge any guest cart items into the user's cart
        mergeSessionCartIntoUser($user['id']);

        $stmt->close();
        header('Location: dashboard');
        exit;
    }
}

$stmt->close();
header('Location: login?error=Invalid username or password');
exit;
?>
<link rel="stylesheet" href="../../assets/css/login_process.css">
