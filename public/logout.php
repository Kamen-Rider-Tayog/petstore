<?php
session_start();
session_destroy();
header('Location: index?message=You have been logged out');
exit;
?>