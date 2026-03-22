<?php
// Set session name BEFORE session_start - must match your site
session_name('petstore_session');
session_start();

require_once __DIR__ . '/../../backend/config/database.php';
require_once __DIR__ . '/../../backend/includes/cart_functions.php';
require_once __DIR__ . '/../../backend/functions/helpers.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ' . url('login'));
    exit;
}

$email = $_POST['email'] ?? '';
$password = $_POST['password'] ?? '';

if (empty($email) || empty($password)) {
    header('Location: ' . url('login?error=Email and password are required'));
    exit;
}

// Use customers table
$sql = "SELECT * FROM customers WHERE email = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $email);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    $user = $result->fetch_assoc();
    
    if (password_verify($password, $user['password'])) {
        // Set session variables
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['customer_id'] = $user['id'];
        $_SESSION['customer_name'] = $user['first_name'] . ' ' . $user['last_name'];
        $_SESSION['customer_email'] = $user['email'];

        // Merge any guest cart items into the user's cart
        if (function_exists('mergeSessionCartIntoUser')) {
            mergeSessionCartIntoUser($user['id']);
        }

        $stmt->close();
        
        // Debug - verify session is set
        error_log("Login successful - Session ID: " . session_id());
        error_log("Customer ID: " . $_SESSION['customer_id']);
        error_log("Customer Name: " . $_SESSION['customer_name']);
        
        header('Location: ' . url(''));
        exit;
    }
}

$stmt->close();
header('Location: ' . url('login?error=Invalid email or password'));
exit;
?>