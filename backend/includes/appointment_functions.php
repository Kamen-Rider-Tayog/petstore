<?php

/**
 * Helper functions for appointment booking.
 */

/**
 * Get the available services in the system.
 */
function getAllServices($conn) {
    $services = [];
    $stmt = $conn->prepare('SELECT * FROM services ORDER BY category, service_name');
    $stmt->execute();
    $result = $stmt->get_result();
    while ($row = $result->fetch_assoc()) {
        $services[] = $row;
    }
    return $services;
}

/**
 * Auto-assign an employee for a given service, date, and time.
 *
 * This is a simple version: it selects the first available employee who has the service assigned.
 */
function autoAssignEmployee($conn, $serviceId, $appointmentDateTime) {
    // Find employees who can perform the service
    $stmt = $conn->prepare('SELECT employee_id FROM employee_services WHERE service_id = ?');
    $stmt->bind_param('i', $serviceId);
    $stmt->execute();

    $result = $stmt->get_result();
    $employees = [];
    while ($row = $result->fetch_assoc()) {
        $employees[] = (int)$row['employee_id'];
    }

    if (empty($employees)) {
        return null;
    }

    // Check if any of the employees already have an appointment at the same time
    $stmt = $conn->prepare('SELECT employee_id FROM appointments WHERE appointment_date = ? AND employee_id IN (' . implode(',', array_fill(0, count($employees), '?')) . ')');
    $types = str_repeat('i', count($employees));
    $params = array_merge([$appointmentDateTime], $employees);

    $stmt->bind_param('s' . $types, ...$params);
    $stmt->execute();
    $result = $stmt->get_result();

    $busy = [];
    while ($row = $result->fetch_assoc()) {
        $busy[] = (int)$row['employee_id'];
    }

    foreach ($employees as $emp) {
        if (!in_array($emp, $busy, true)) {
            return $emp;
        }
    }

    return null;
}

/**
 * Check if a time slot is available for an employee and service.
 */
function isTimeSlotAvailable($conn, $employeeId, $appointmentDateTime, $serviceId) {
    // Get service duration
    $stmt = $conn->prepare('SELECT duration_minutes FROM services WHERE id = ?');
    $stmt->bind_param('i', $serviceId);
    $stmt->execute();
    $service = $stmt->get_result()->fetch_assoc();
    if (!$service) return false;

    $duration = $service['duration_minutes'];

    // Calculate end time
    $start = new DateTime($appointmentDateTime);
    $end = clone $start;
    $end->modify("+{$duration} minutes");

    // Check for overlapping appointments
    $stmt = $conn->prepare('SELECT COUNT(*) as count FROM appointments WHERE employee_id = ? AND status != "cancelled" AND appointment_date < ? AND DATE_ADD(appointment_date, INTERVAL duration_minutes MINUTE) > ?');
    $stmt->bind_param('iss', $employeeId, $end->format('Y-m-d H:i:s'), $start->format('Y-m-d H:i:s'));
    $stmt->execute();
    $result = $stmt->get_result()->fetch_assoc();

    return $result['count'] == 0;
}
