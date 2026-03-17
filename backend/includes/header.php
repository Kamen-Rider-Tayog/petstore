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

// Cache-bust version — increment this whenever CSS/JS changes
define('ASSET_VERSION', '2.1');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="<?php echo isset($page_description) ? e($page_description) : 'Ria Pet Store - Your one-stop shop for pets, supplies, and services.'; ?>">
    <title><?php echo isset($page_title) ? e($page_title) . ' | ' . APP_NAME : APP_NAME; ?></title>
    <link rel="icon" type="image/x-icon" href="<?php echo asset('images/favicon.ico'); ?>">

    <link rel="stylesheet" href="<?php echo asset('css/base.css');       ?>?v=<?php echo ASSET_VERSION; ?>">
    <link rel="stylesheet" href="<?php echo asset('css/layout.css');     ?>?v=<?php echo ASSET_VERSION; ?>">
    <link rel="stylesheet" href="<?php echo asset('css/components.css'); ?>?v=<?php echo ASSET_VERSION; ?>">
    <link rel="stylesheet" href="<?php echo asset('css/utilities.css');  ?>?v=<?php echo ASSET_VERSION; ?>">
    <link rel="stylesheet" href="<?php echo asset('css/navigation.css'); ?>?v=<?php echo ASSET_VERSION; ?>">
    <link rel="stylesheet" href="<?php echo asset('css/footer.css');     ?>?v=<?php echo ASSET_VERSION; ?>">
    <link rel="stylesheet" href="<?php echo asset('css/animations.css'); ?>?v=<?php echo ASSET_VERSION; ?>">
    <link rel="stylesheet" href="<?php echo asset('css/icons.css'); ?>?v=<?php echo ASSET_VERSION; ?>">
    <link rel="stylesheet" href="<?php echo asset('css/btn.css'); ?>?v=<?php echo ASSET_VERSION; ?>">
    <link rel="stylesheet" href="<?php echo asset('css/print.css');      ?>" media="print">

    <?php if (!empty($page_styles)): ?>
        <?php foreach ($page_styles as $style): ?>
            <link rel="stylesheet" href="<?php echo $style; ?>?v=<?php echo ASSET_VERSION; ?>">
        <?php endforeach; ?>
    <?php endif; ?>

    <script>const BASE_URL = '<?php echo BASE_URL; ?>';</script>
</head>
<body>
<?php include __DIR__ . '/navigation.php'; ?>
<main class="container">