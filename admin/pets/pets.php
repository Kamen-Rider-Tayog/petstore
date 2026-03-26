<?php
session_name('petstore_session');
session_start();

if (!isset($_SESSION['is_admin']) || $_SESSION['is_admin'] !== true) {
    header('Location: ' . url('login?error=Access denied'));
    exit;
}

require_once __DIR__ . '/../../backend/config/database.php';
require_once __DIR__ . '/../../backend/functions/helpers.php';

$page_title = 'Pets Management';
require_once __DIR__ . '/../includes/header.php';

$active_tab = isset($_GET['tab']) ? $_GET['tab'] : 'store';

// Admin CSS
echo '<link rel="stylesheet" href="/Ria-Pet-Store/admin/css/pets.css?v=' . time() . '">';
?>

<div class="admin-dashboard">
    <!-- Tabs -->
    <div class="pets-tabs">
        <a href="?tab=store" class="tab <?php echo $active_tab === 'store' ? 'active' : ''; ?>">
            <?php echo icon('building', 16); ?> Pets For Adoption
        </a>
        <a href="?tab=customer" class="tab <?php echo $active_tab === 'customer' ? 'active' : ''; ?>">
            <?php echo icon('users', 16); ?> Customer Pets
        </a>
    </div>

    <?php if ($active_tab === 'store'): ?>
        <?php include 'store_pets.php'; ?>
    <?php else: ?>
        <?php include 'customer_pets.php'; ?>
    <?php endif; ?>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>