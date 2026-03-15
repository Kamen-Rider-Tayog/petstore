<?php
header('Content-Type: application/json');
require_once '../config/database.php';

$species = isset($_GET['species']) ? $_GET['species'] : '';

$query = "SELECT id, name, species, age, price FROM pets";
$params = [];
$types = "";

if (!empty($species) && $species !== 'all') {
    $query .= " WHERE species = ?";
    $params[] = $species;
    $types .= "s";
}

$stmt = $conn->prepare($query);

if (!empty($params)) {
    $stmt->bind_param($types, ...$params);
}

$stmt->execute();
$result = $stmt->get_result();

$pets = [];
while($row = $result->fetch_assoc()) {
    $pets[] = $row;
}

$response = [
    'success' => true,
    'count' => count($pets),
    'data' => $pets
];

if (count($pets) === 0) {
    $response['message'] = 'No pets found for this species';
}

echo json_encode($response);
$stmt->close();
$conn->close();
?>