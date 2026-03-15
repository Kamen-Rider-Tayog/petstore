<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pet Store</title>
</head>
<body>
    <header>
        <h1>Pet Store</h1>
        <nav>
            <a href="index">Home</a> |
            <a href="pets">Pets</a> |
            <a href="customers">Customers</a> |
            <a href="products_by_supplier">Products</a> |
            
            <?php if (isset($_SESSION['user_id'])): ?>
                <span>Welcome, <?php echo $_SESSION['full_name'] ?: $_SESSION['username']; ?>!</span> |
                <a href="dashboard">Dashboard</a> |
                <a href="logout">Logout</a>
            <?php else: ?>
                <a href="login">Login</a> |
                <a href="register">Register</a>
            <?php endif; ?>
        </nav>
        <hr>
    </header>
    <main>