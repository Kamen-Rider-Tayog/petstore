<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../functions/helpers.php';
require_once __DIR__ . '/../functions/cache.php';
require_once __DIR__ . '/constants.php';

if (session_status() === PHP_SESSION_NONE) {
    session_name(Config::get('SESSION_NAME', 'petstore_session'));
    session_start();
}

date_default_timezone_set(DEFAULT_TIMEZONE);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="<?php echo isset($page_description) ? e($page_description) : 'Ria Pet Store - Your one-stop shop for pets, supplies, and services.'; ?>">
    <title><?php echo isset($page_title) ? e($page_title) . ' | ' . APP_NAME : APP_NAME; ?></title>
    <link rel="icon" type="image/x-icon" href="<?php echo asset('images/favicon.ico'); ?>">

    <link rel="stylesheet" href="<?php echo asset('css/base.css'); ?>">
    <link rel="stylesheet" href="<?php echo asset('css/layout.css'); ?>">
    <link rel="stylesheet" href="<?php echo asset('css/components.css'); ?>">
    <link rel="stylesheet" href="<?php echo asset('css/utilities.css'); ?>">
    <link rel="stylesheet" href="<?php echo asset('css/navigation.css'); ?>">
    <link rel="stylesheet" href="<?php echo asset('css/footer.css'); ?>">
    <link rel="stylesheet" href="<?php echo asset('css/animations.css'); ?>">
    <link rel="stylesheet" href="<?php echo asset('css/print.css'); ?>" media="print">

    <?php if (isset($page_styles)): ?>
        <?php foreach ($page_styles as $style): ?>
            <link rel="stylesheet" href="<?php echo $style; ?>">
        <?php endforeach; ?>
    <?php endif; ?>

    <script>const BASE_URL = '<?php echo BASE_URL; ?>';</script>
</head>
<body>
<?php include __DIR__ . '/navigation.php'; ?>
<main class="container">