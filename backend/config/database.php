<?php
$host = 'localhost';
$username = 'root';
$password = '';
$database = 'pet_store';

$conn = new mysqli($host, $username, $password, $database);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Set charset to prevent some injection attempts
$conn->set_charset("utf8mb4");
?>