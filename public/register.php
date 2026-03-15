<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register</title>
</head>
<body>
    <h1>Create an Account</h1>

    <form method="POST" action="register_process">
        <div>
            <label for="username">Username:</label><br>
            <input type="text" id="username" name="username" required>
        </div>
        
        <div>
            <label for="password">Password:</label><br>
            <input type="password" id="password" name="password" required>
        </div>
        
        <div>
            <label for="confirm_password">Confirm Password:</label><br>
            <input type="password" id="confirm_password" name="confirm_password" required>
        </div>
        
        <div>
            <label for="full_name">Full Name:</label><br>
            <input type="text" id="full_name" name="full_name">
        </div>
        
        <br>
        <button type="submit">Register</button>
    </form>

    <br>
    <p>Already have an account? <a href="login">Login here</a></p>
    <a href="index">Back to Home</a>
</body>
</html>