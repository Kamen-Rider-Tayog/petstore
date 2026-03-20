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
    <title><?php 
        if (isset($page_title) && $page_title !== 'Home' && $page_title !== '') {
            echo APP_NAME . ' | ' . e($page_title);
        } else {
            echo APP_NAME;
        }?>
    </title>
    <link rel="icon" type="image/x-icon" href="<?php echo asset('images/favicon.ico'); ?>">

    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Nunito:wght@400;500;600;700&family=Lato:wght@400;700&display=swap" rel="stylesheet">

    <!-- Core CSS -->
    <link rel="stylesheet" href="<?php echo asset('css/core/base.css'); ?>?v=<?php echo ASSET_VERSION; ?>">
    <link rel="stylesheet" href="<?php echo asset('css/core/layout.css'); ?>?v=<?php echo ASSET_VERSION; ?>">
    <link rel="stylesheet" href="<?php echo asset('css/core/components.css'); ?>?v=<?php echo ASSET_VERSION; ?>">
    <link rel="stylesheet" href="<?php echo asset('css/core/utilities.css'); ?>?v=<?php echo ASSET_VERSION; ?>">
    <link rel="stylesheet" href="<?php echo asset('css/core/animations.css'); ?>?v=<?php echo ASSET_VERSION; ?>">
    <link rel="stylesheet" href="<?php echo asset('css/core/icons.css'); ?>?v=<?php echo ASSET_VERSION; ?>">
    <link rel="stylesheet" href="<?php echo asset('css/core/btn.css'); ?>?v=<?php echo ASSET_VERSION; ?>">
    <link rel="stylesheet" href="<?php echo asset('css/core/print.css'); ?>" media="print">

    <!-- Component CSS -->
    <link rel="stylesheet" href="<?php echo asset('css/components/navigation.css'); ?>?v=<?php echo ASSET_VERSION; ?>">
    <link rel="stylesheet" href="<?php echo asset('css/components/footer.css'); ?>?v=<?php echo ASSET_VERSION; ?>">
    <link rel="stylesheet" href="<?php echo asset('css/components/dropdown.css'); ?>?v=<?php echo ASSET_VERSION; ?>">

    <?php if (!empty($page_styles)): ?>
        <?php foreach ($page_styles as $style): ?>
            <link rel="stylesheet" href="<?php echo $style; ?>?v=<?php echo ASSET_VERSION; ?>">
        <?php endforeach; ?>
    <?php endif; ?>

    <!-- JavaScript -->
    <script>const BASE_URL = '<?php echo BASE_URL; ?>';</script>
    
    <!-- Core JS -->
    <script src="<?php echo asset('js/core/utils.js'); ?>?v=<?php echo ASSET_VERSION; ?>"></script>
    <script src="<?php echo asset('js/core/app.js'); ?>?v=<?php echo ASSET_VERSION; ?>"></script>
    
    <!-- UI Modules -->
    <script src="<?php echo asset('js/ui/back-to-top.js'); ?>?v=<?php echo ASSET_VERSION; ?>"></script>
    <script src="<?php echo asset('js/ui/dropdown.js'); ?>?v=<?php echo ASSET_VERSION; ?>"></script>
    <script src="<?php echo asset('js/ui/images.js'); ?>?v=<?php echo ASSET_VERSION; ?>"></script>
    <script src="<?php echo asset('js/ui/scroll.js'); ?>?v=<?php echo ASSET_VERSION; ?>"></script>
    <script src="<?php echo asset('js/ui/tooltips.js'); ?>?v=<?php echo ASSET_VERSION; ?>"></script>
    <script src="<?php echo asset('js/ui/modals.js'); ?>?v=<?php echo ASSET_VERSION; ?>"></script>
    <script src="<?php echo asset('js/ui/notifications.js'); ?>?v=<?php echo ASSET_VERSION; ?>"></script>
    
    <!-- Form Modules -->
    <script src="<?php echo asset('js/forms/validation.js'); ?>?v=<?php echo ASSET_VERSION; ?>"></script>
    
    <!-- Feature Modules -->
    <script src="<?php echo asset('js/features/cart.js'); ?>?v=<?php echo ASSET_VERSION; ?>"></script>
    <script src="<?php echo asset('js/features/filter.js'); ?>?v=<?php echo ASSET_VERSION; ?>"></script>
    
    <!-- Page specific scripts -->
    <?php if (!empty($page_scripts)): ?>
        <?php foreach ($page_scripts as $script): ?>
            <script src="<?php echo $script; ?>?v=<?php echo ASSET_VERSION; ?>"></script>
        <?php endforeach; ?>
    <?php endif; ?>
</head>
<body>
<?php include __DIR__ . '/navigation.php'; ?>
<main class="container">