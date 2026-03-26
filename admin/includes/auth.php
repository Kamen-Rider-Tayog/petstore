<?php
// admin/includes/auth.php
require_once __DIR__ . '/../../backend/functions/helpers.php';

session_name('petstore_session');
session_start();

if (!isset($_SESSION['is_admin']) || $_SESSION['is_admin'] !== true) {
    header('Location: ' . url('login?error=Access denied'));
    exit;
}