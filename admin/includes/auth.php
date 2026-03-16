<?php
session_start();
if(!isset($_SESSION['admin_id'])) {
    header('Location: /petstore/admin/login.php');
    exit();
}
?>