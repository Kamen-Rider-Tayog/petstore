<?php
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

// First check if user is an admin (employees table)
$adminStmt = $conn->prepare("SELECT * FROM employees WHERE email = ? AND is_admin = 1");
$adminStmt->bind_param("s", $email);
$adminStmt->execute();
$adminResult = $adminStmt->get_result();

if ($adminResult->num_rows > 0) {
    $admin = $adminResult->fetch_assoc();
    
    if (password_verify($password, $admin['password'])) {
        $_SESSION['admin_id'] = $admin['id'];
        $_SESSION['admin_name'] = $admin['first_name'] . ' ' . $admin['last_name'];
        $_SESSION['admin_email'] = $admin['email'];
        $_SESSION['is_admin'] = true;
        
        $adminStmt->close();
        header('Location: ' . url('admin/pages/dashboard.php'));
        exit;
    }
}
$adminStmt->close();

// If not admin, check customers table
$customerStmt = $conn->prepare("SELECT * FROM customers WHERE email = ?");
$customerStmt->bind_param("s", $email);
$customerStmt->execute();
$customerResult = $customerStmt->get_result();

if ($customerResult->num_rows > 0) {
    $customer = $customerResult->fetch_assoc();
    
    if (password_verify($password, $customer['password'])) {
        $_SESSION['customer_id'] = $customer['id'];
        $_SESSION['customer_name'] = $customer['first_name'] . ' ' . $customer['last_name'];
        $_SESSION['customer_email'] = $customer['email'];

        // Merge any guest cart items into the user's cart
        if (function_exists('mergeSessionCartIntoUser')) {
            mergeSessionCartIntoUser($customer['id']);
        }

        $customerStmt->close();
        header('Location: ' . url(''));
        exit;
    }
}
$customerStmt->close();

// If neither admin nor customer found with matching password
header('Location: ' . url('login?error=Invalid email or password'));
exit;
?>