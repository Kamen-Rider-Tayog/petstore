<?php
function logSearch($search_term, $results_count = 0) {
    global $conn;

    $customer_id = $_SESSION['customer_id'] ?? null;
    $ip = $_SERVER['REMOTE_ADDR'];

    $sql = "INSERT INTO search_log (search_term, results_count, customer_id, ip_address)
            VALUES (?, ?, ?, ?)";

    $stmt = $conn->prepare($sql);
    $stmt->bind_param("siss", $search_term, $results_count, $customer_id, $ip);
    $stmt->execute();
}
?>