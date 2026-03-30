<?php
// admin/includes/header.php
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Panel - <?php echo APP_NAME; ?></title>
    
    <!-- Core CSS (shared across all pages) -->
    <link rel="stylesheet" href="/Ria-Pet-Store/assets/css/core/base.css">
    <link rel="stylesheet" href="/Ria-Pet-Store/assets/css/core/layout.css">
    <link rel="stylesheet" href="/Ria-Pet-Store/assets/css/core/btn.css">
    <link rel="stylesheet" href="/Ria-Pet-Store/assets/css/core/utilities.css">

    <!-- Admin CSS (page-specific only) -->
    <link rel="stylesheet" href="/Ria-Pet-Store/admin/css/admin-core.css">
    <link rel="stylesheet" href="/Ria-Pet-Store/admin/css/dashboard.css">
    <link rel="stylesheet" href="/Ria-Pet-Store/admin/css/customers.css">
    <link rel="stylesheet" href="/Ria-Pet-Store/admin/css/orders.css">
    <link rel="stylesheet" href="/Ria-Pet-Store/admin/css/products.css">
    <link rel="stylesheet" href="/Ria-Pet-Store/admin/css/pets.css">
    <link rel="stylesheet" href="/Ria-Pet-Store/admin/css/appointments.css">
    <link rel="stylesheet" href="/Ria-Pet-Store/admin/css/services.css">
    <link rel="stylesheet" href="/Ria-Pet-Store/admin/css/employees.css">
    
    <?php if (!empty($page_styles)): ?>
        <?php foreach ($page_styles as $style): ?>
            <link rel="stylesheet" href="<?php echo $style; ?>">
        <?php endforeach; ?>
    <?php endif; ?>
</head>
<body>
    <div class="admin-wrapper">
        <!-- Sidebar -->
        <aside class="admin-sidebar">
            <div class="sidebar-header">
                <a href="<?php echo url('admin/pages/dashboard.php'); ?>" class="logo">
                    <?php echo APP_NAME; ?> 
                    <br>
                    <span>Admin Panel</span>
                </a>
            </div>
            <nav class="sidebar-nav">
                <ul>
                    <li><a href="<?php echo url('admin/pages/dashboard.php'); ?>" class="<?php echo basename($_SERVER['PHP_SELF']) === 'dashboard.php' ? 'active' : ''; ?>">
                        <?php echo icon('dashboard', 20); ?> Dashboard
                    </a></li>
                    <li><a href="<?php echo url('admin/sales/sales'); ?>" class="<?php echo strpos($_SERVER['PHP_SELF'], 'sales') !== false ? 'active' : ''; ?>">
                        <?php echo icon('line-chart-up', 20); ?> Sales
                    </a></li>
                    <li><a href="<?php echo url('admin/customers/customers.php'); ?>" class="<?php echo strpos($_SERVER['PHP_SELF'], 'customers') !== false ? 'active' : ''; ?>">
                        <?php echo icon('users', 20); ?> Customers
                    </a></li>
                    <li><a href="<?php echo url('admin/orders/orders.php'); ?>" class="<?php echo strpos($_SERVER['PHP_SELF'], 'orders') !== false ? 'active' : ''; ?>">
                        <?php echo icon('package', 20); ?> Orders
                    </a></li>
                    <li><a href="<?php echo url('admin/inventory/products.php'); ?>" class="<?php echo strpos($_SERVER['PHP_SELF'], 'products') !== false ? 'active' : ''; ?>">
                        <?php echo icon('package', 20); ?> Products
                    </a></li>
                    <li><a href="<?php echo url('admin/pets/pets.php'); ?>" class="<?php echo strpos($_SERVER['PHP_SELF'], 'pets') !== false ? 'active' : ''; ?>">
                        <?php echo icon('paw', 20); ?> Pets
                    </a></li>
                    <li><a href="<?php echo url('admin/appointments/appointments.php'); ?>" class="<?php echo strpos($_SERVER['PHP_SELF'], 'appointments') !== false ? 'active' : ''; ?>">
                        <?php echo icon('calendar', 20); ?> Appointments
                    </a></li>
                    <li><a href="<?php echo url('admin/employees/employees.php'); ?>" class="<?php echo strpos($_SERVER['PHP_SELF'], 'employees') !== false ? 'active' : ''; ?>">
                        <?php echo icon('users', 20); ?> Employees
                    </a></li>
                    <li><a href="<?php echo url('admin/services/services.php'); ?>" class="<?php echo strpos($_SERVER['PHP_SELF'], 'services') !== false ? 'active' : ''; ?>">
                        <?php echo icon('heart', 20); ?> Services
                    </a></li>
                    <li><a href="<?php echo url('admin/reviews/manage_reviews.php'); ?>" class="<?php echo strpos($_SERVER['PHP_SELF'], 'reviews') !== false ? 'active' : ''; ?>">
                        <?php echo icon('star', 20); ?> Reviews
                    </a></li>
                    <li><a href="<?php echo url('admin/suppliers/manage_suppliers.php'); ?>" class="<?php echo strpos($_SERVER['PHP_SELF'], 'suppliers') !== false ? 'active' : ''; ?>">
                        <?php echo icon('truck', 20); ?> Suppliers
                    </a></li>
                    <li><a href="<?php echo url('admin/pages/settings.php'); ?>" class="<?php echo basename($_SERVER['PHP_SELF']) === 'settings.php' ? 'active' : ''; ?>">
                        <?php echo icon('settings', 20); ?> Settings
                    </a></li>
                </ul>
            </nav>
            <div class="sidebar-footer">
                <a href="<?php echo url('logout'); ?>">
                    <?php echo icon('x', 20); ?> Logout
                </a>
            </div>
        </aside>
        
        <!-- Main Content -->
        <main class="admin-main">
            <!-- Top Bar -->
            <div class="top-bar">
                <div class="greeting-wrapper">
                    <p id="greetingMessage"></p>
                </div>
                <div class="info">
                    <div class="datetime">
                        <div class="current-date" id="currentDate">
                            <?php echo date('F j, Y'); ?>
                        </div>
                        <div class="current-time" id="currentTime">
                            --:--:--
                        </div>
                    </div>
                </div>
            </div>
            
            <script>
                // Greeting based on time of day
                function updateGreeting() {
                    const now = new Date();
                    const hours = now.getHours();
                    let greeting = '';
                    
                    if (hours >= 5 && hours < 12) {
                        greeting = 'Good morning';
                    } else if (hours >= 12 && hours < 17) {
                        greeting = 'Good afternoon';
                    } else if (hours >= 17 && hours < 22) {
                        greeting = 'Good evening';
                    } else {
                        greeting = 'Good night';
                    }
                    
                    const adminName = '<?php echo htmlspecialchars($_SESSION['admin_name']); ?>';
                    const greetingElement = document.getElementById('greetingMessage');
                    if (greetingElement) {
                        greetingElement.textContent = `${greeting}, ${adminName}!`;
                    }
                }
                
                // Update current time
                function updateCurrentTime() {
                    const now = new Date();
                    const hours = String(now.getHours()).padStart(2, '0');
                    const minutes = String(now.getMinutes()).padStart(2, '0');
                    const seconds = String(now.getSeconds()).padStart(2, '0');
                    const timeString = `${hours}:${minutes}:${seconds}`;
                    
                    const timeElement = document.getElementById('currentTime');
                    if (timeElement) {
                        timeElement.textContent = timeString;
                    }
                }
                
                // Update greeting and time immediately
                updateGreeting();
                updateCurrentTime();
                
                // Update time every second
                setInterval(updateCurrentTime, 1000);
            </script>