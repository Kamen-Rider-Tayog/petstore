<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../functions/helpers.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/constants.php';

if (session_status() === PHP_SESSION_NONE) {
    session_name(Config::get('SESSION_NAME', 'petstore_session'));
    session_start();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Ria Pet Store - Your one-stop shop for pets, supplies, and veterinary services. Find your perfect pet companion today!">
    <meta name="keywords" content="ria pet store, pets, dogs, cats, pet supplies, veterinary services, pet adoption">
    <title><?php echo APP_NAME; ?></title>
    <link rel="icon" type="image/x-icon" href="<?php echo asset('images/favicon.ico'); ?>">
    <link rel="stylesheet" href="<?php echo asset('css/base.css'); ?>">
    <link rel="stylesheet" href="<?php echo asset('css/layout.css'); ?>">
    <link rel="stylesheet" href="<?php echo asset('css/components.css'); ?>">
    <link rel="stylesheet" href="<?php echo asset('css/utilities.css'); ?>">
    <link rel="stylesheet" href="<?php echo asset('css/animations.css'); ?>">
    <link rel="stylesheet" href="<?php echo asset('css/print.css'); ?>" media="print">
    <script>
        const BASE_URL = '<?php echo BASE_URL; ?>';
    </script>
</head>
<body>
    <header>
        <?php include __DIR__ . '/navigation.php'; ?>
    </header>
    <script src="<?php echo asset('js/cart.js'); ?>"></script>
    <script>
    document.addEventListener('DOMContentLoaded', () => {
        if (typeof updateCartCount === 'function') {
            updateCartCount();
        }
        if (typeof renderMiniCart === 'function') {
            renderMiniCart();
        }
    });
    </script>
    <main class="container">