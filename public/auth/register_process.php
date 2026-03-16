<?php
require_once '../../backend/config/database.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: register');
    exit;
}

$username = $_POST['username'] ?? '';
$password = $_POST['password'] ?? '';
$confirm_password = $_POST['confirm_password'] ?? '';
$full_name = $_POST['full_name'] ?? '';

$errors = [];

if (empty($username)) {
    $errors[] = "Username is required";
}
if (empty($password)) {
    $errors[] = "Password is required";
} elseif (strlen($password) < 6) {
    $errors[] = "Password must be at least 6 characters";
}
if ($password !== $confirm_password) {
    $errors[] = "Passwords do not match";
}

// Check if username exists using prepared statement
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
?>


<link rel="stylesheet" href="../../assets/css/register_process.css">
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registration Result</title>
</head>
<body>
    <h1>Registration Result</h1>

    <?php if (!empty($errors)): ?>
        <h3>Errors:</h3>
        <ul>
        <?php foreach($errors as $error): ?>
            <li><?php echo $error; ?></li>
        <?php endforeach; ?>
        </ul>
        <a href="register">Go back to registration</a>
    <?php else: ?>
        <?php
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);
        
        // Insert using prepared statement
        $insert_sql = "INSERT INTO users (username, password, full_name) VALUES (?, ?, ?)";
        $stmt = $conn->prepare($insert_sql);
        $stmt->bind_param("sss", $username, $hashed_password, $full_name);
        
        if ($stmt->execute()) {
            echo "<p>Registration successful! You can now login.</p>";
            echo '<a href="login">Go to Login</a>';
        } else {
            echo "<p>Error: " . $conn->error . "</p>";
        }
        $stmt->close();
        ?>
    <?php endif; ?>
</body>
</html>