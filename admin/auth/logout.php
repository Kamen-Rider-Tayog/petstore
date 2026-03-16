<?php
session_start();
session_destroy();

// Clear remember cookie if exists
if (isset($_COOKIE['admin_remember'])) {
    setcookie('admin_remember', '', time() - 3600, '/');
}

header('Location: login.php?message=Logged out successfully');
exit();
?>