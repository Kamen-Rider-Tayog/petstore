<?php
session_start();
session_destroy();
header('Location: index?message=You have been logged out');
exit;
?>
<link rel="stylesheet" href="../../assets/css/logout.css">
