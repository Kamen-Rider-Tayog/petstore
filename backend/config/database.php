<?php
require_once __DIR__ . '/config.php';

$host = Config::get('DB_HOST', 'localhost');
$username = Config::get('DB_USER', 'root');
$password = Config::get('DB_PASS', '');
$database = Config::get('DB_NAME', 'pet_store');
$charset = Config::get('DB_CHARSET', 'utf8mb4');

$conn = new mysqli($host, $username, $password, $database);

if ($conn->connect_error) {
    if (Config::isDebug()) {
        die("Connection failed: " . $conn->connect_error);
    } else {
        die("Database connection error. Please try again later.");
    }
}

// Set charset
$conn->set_charset($charset);

// For debugging in development
if (Config::isDevelopment() && Config::isDebug()) {
    error_reporting(E_ALL);
    ini_set('display_errors', 1);
} else {
    error_reporting(0);
    ini_set('display_errors', 0);
}
?>