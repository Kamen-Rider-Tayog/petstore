<?php
header('Content-Type: application/json');
require_once '../config/database.php';

$result = $conn->query("SELECT id, name, species, age, price FROM pets");
$pets = [];

while($row = $result->fetch_assoc()) {
    $pets[] = $row;
}

echo json_encode($pets);
$conn->close();
?>