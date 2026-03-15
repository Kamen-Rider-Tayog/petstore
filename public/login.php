<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login</title>
</head>
<body>
    <h1>Login to Pet Store</h1>

    <?php if (isset($_GET['error'])): ?>
        <p style="color: red;"><?php echo $_GET['error']; ?></p>
    <?php endif; ?>

    <?php if (isset($_GET['registered'])): ?>
        <p style="color: green;">Registration successful! Please login.</p>
    <?php endif; ?>

    <form method="POST" action="login_process">
        <div>
            <label for="username">Username:</label><br>
            <input type="text" id="username" name="username" required>
        </div>
        
        <div>
            <label for="password">Password:</label><br>
            <input type="password" id="password" name="password" required>
        </div>
        
        <br>
        <button type="submit">Login</button>
    </form>

    <br>
    <p>Don't have an account? <a href="register">Register here</a></p>
    <a href="index">Back to Home</a>
</body>
</html>