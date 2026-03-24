<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/filter_functions.php';

// Turn off error reporting to prevent HTML output
error_reporting(0);
ini_set('display_errors', 0);

// Clear any previous output
if (ob_get_level()) ob_clean();
header('Content-Type: application/json');

$data = json_decode(file_get_contents('php://input'), true);

try {
    // Simple query without filters first to test
    $sql = "SELECT id, name, species, breed, age, pet_image FROM pets WHERE pet_status = 'available' ORDER BY id DESC";
    $result = $conn->query($sql);
    
    // Generate HTML
    ob_start();
    if ($result->num_rows > 0) {
        while ($pet = $result->fetch_assoc()) {
            // Check if pet_card.php exists
            $cardPath = __DIR__ . '/../includes/pet_card.php';
            if (file_exists($cardPath)) {
                include $cardPath;
            } else {
                // Fallback HTML if card doesn't exist
                echo '<div class="pet-card">';
                echo '<h3>' . htmlspecialchars($pet['name']) . '</h3>';
                echo '<p>' . htmlspecialchars($pet['species']) . '</p>';
                echo '</div>';
            }
        }
    } else {
        echo '<div class="no-results">No pets found.</div>';
    }
    $html = ob_get_clean();
    
    echo json_encode([
        'success' => true,
        'html' => $html,
        'count' => $result->num_rows
    ]);

} catch (Exception $e) {
    ob_clean();
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
?>