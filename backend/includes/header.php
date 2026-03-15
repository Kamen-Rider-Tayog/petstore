<?php
if (session_status() === PHP_SESSION_NONE) {
    session_name(Config::get('SESSION_NAME', 'petstore_session'));
    session_start();
}

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../functions/helpers.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo APP_NAME; ?></title>
    <link rel="stylesheet" href="<?php echo asset('css/style.css'); ?>">
    <script>
        const BASE_URL = '<?php echo BASE_URL; ?>';
    </script>
</head>
<body>
    <header>
        <div class="container">
            <h1><?php echo APP_NAME; ?></h1>
            <nav>
                <ul>
                    <li><a href="<?php echo url('public/index'); ?>">Home</a></li>
                    <li><a href="<?php echo url('public/pets'); ?>">Pets</a></li>
                    <li><a href="<?php echo url('public/products'); ?>">Products</a></li>
                    <?php if (isset($_SESSION['user_id'])): ?>
                        <li><a href="<?php echo url('public/dashboard'); ?>">Dashboard</a></li>
                        <li><a href="<?php echo url('public/cart'); ?>">Cart (<span id="cart-count">0</span>)</a></li>
                        <li><a href="<?php echo url('public/logout'); ?>">Logout</a></li>
                    <?php else: ?>
                        <li><a href="<?php echo url('public/login'); ?>">Login</a></li>
                        <li><a href="<?php echo url('public/register'); ?>">Register</a></li>
                    <?php endif; ?>
                </ul>
            </nav>
        </div>
    </header>
    <main class="container">