<?php
require_once __DIR__ . '/../../config/database.php';

header('Content-Type: application/json');

$serviceId = isset($_GET['service_id']) ? (int)$_GET['service_id'] : 0;
date_default_timezone_set('Asia/Manila');
$date = isset($_GET['date']) ? $_GET['date'] : date('Y-m-d');

$response = [
    'success' => false,
    'date' => $date,
    'slots' => [],
    'business_hours' => [
        'open' => '09:00',
        'close' => '17:00',
    ],
];

if ($serviceId <= 0) {
    echo json_encode($response);
    exit;
}

// Fetch employees for this service
$stmt = $conn->prepare('SELECT employee_id FROM employee_services WHERE service_id = ?');
$stmt->bind_param('i', $serviceId);
$stmt->execute();
$result = $stmt->get_result();
$employeeIds = [];
while ($row = $result->fetch_assoc()) {
    $employeeIds[] = (int)$row['employee_id'];
}

if (empty($employeeIds)) {
    echo json_encode($response);
    exit;
}

// For simplicity, generate static slots every 30 minutes between 09:00 and 16:30
$open = new DateTime($date . ' 09:00');
$close = new DateTime($date . ' 17:00');

$interval = new DateInterval('PT30M');
$slotTime = clone $open;

while ($slotTime < $close) {
    $timeStr = $slotTime->format('H:i');

    // Determine which employees are available (simple round-robin)
    $available = [];
    foreach ($employeeIds as $idx => $eid) {
        if ((int)($slotTime->format('H')) % 2 === ($idx % 2)) {
            $available[] = $eid;
        }
    }

    $response['slots'][] = [
        'time' => $timeStr,
        'employees' => $available,
    ];

    $slotTime->add($interval);
}

$response['success'] = true;

echo json_encode($response);
