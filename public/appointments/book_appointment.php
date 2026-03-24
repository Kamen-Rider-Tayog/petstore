<?php
session_name('petstore_session');
session_start();

require_once __DIR__ . '/../../backend/config/database.php';
require_once __DIR__ . '/../../backend/functions/helpers.php';

// Check if user is logged in
if (!isset($_SESSION['customer_id'])) {
    header('Location: ' . url('login'));
    exit;
}

$customerId = $_SESSION['customer_id'];
$step = isset($_GET['step']) ? (int)$_GET['step'] : 1;
$error = '';
$success = false;
$booking = $_SESSION['booking'] ?? [];

// Get services and pets data first
$services = [];
$stmt = $conn->prepare("SELECT * FROM services ORDER BY category, service_name");
$stmt->execute();
$result = $stmt->get_result();
while ($row = $result->fetch_assoc()) {
    $services[] = $row;
}

// Get customer's pets
$pets = [];
$stmt = $conn->prepare("SELECT * FROM customer_pets WHERE customer_id = ? AND is_active = 1");
$stmt->bind_param("i", $customerId);
$stmt->execute();
$result = $stmt->get_result();
while ($row = $result->fetch_assoc()) {
    $pets[] = $row;
}

// Handle form submissions - BEFORE any output
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $postStep = (int)($_POST['step'] ?? 0);
    
    if ($postStep === 1 && isset($_POST['service_id'])) {
        $_SESSION['booking']['service_id'] = (int)$_POST['service_id'];
        header('Location: ' . url('book_appointment?step=2'));
        exit;
    }
    
    if ($postStep === 2 && isset($_POST['pet_id'])) {
        $_SESSION['booking']['pet_id'] = (int)$_POST['pet_id'];
        header('Location: ' . url('book_appointment?step=3'));
        exit;
    }
    
    if ($postStep === 3 && isset($_POST['appointment_date'])) {
        $_SESSION['booking']['appointment_date'] = $_POST['appointment_date'];
        header('Location: ' . url('book_appointment?step=4'));
        exit;
    }
    
    if ($postStep === 4) {
        // Confirm booking
        $serviceId = $_SESSION['booking']['service_id'] ?? 0;
        $petId = $_SESSION['booking']['pet_id'] ?? 0;
        $appointmentDate = $_SESSION['booking']['appointment_date'] ?? '';
        
        if ($serviceId && $petId && $appointmentDate) {
            // Get service details
            $service = null;
            foreach ($services as $s) {
                if ($s['id'] == $serviceId) {
                    $service = $s;
                    break;
                }
            }
            
            if ($service) {
                // Find available employee
                $empStmt = $conn->prepare("SELECT id FROM employees LIMIT 1");
                $empStmt->execute();
                $employee = $empStmt->get_result()->fetch_assoc();
                $employeeId = $employee['id'] ?? null;
                
                // Insert appointment
                $insertStmt = $conn->prepare("
                    INSERT INTO appointments (customer_id, pet_id, service_type, employee_id, appointment_date, status) 
                    VALUES (?, ?, ?, ?, ?, 'confirmed')
                ");
                $insertStmt->bind_param("iisss", $customerId, $petId, $service['service_name'], $employeeId, $appointmentDate);
                
                if ($insertStmt->execute()) {
                    unset($_SESSION['booking']);
                    $success = true;
                    $step = 5;
                } else {
                    $error = 'Failed to book appointment. Please try again.';
                }
            } else {
                $error = 'Invalid service selected.';
            }
        } else {
            $error = 'Please complete all steps.';
            header('Location: ' . url('book_appointment?step=1'));
            exit;
        }
    }
}

// Include header AFTER processing POST
require_once __DIR__ . '/../../backend/includes/header.php';

$page_title = 'Book Appointment';
?>
<!-- Direct CSS link -->
<link rel="stylesheet" href="/Ria-Pet-Store/assets/css/appointments/book_appointment.css?v=<?php echo time(); ?>">

<div class="book-appointment-page">
    <section class="page-hero">
        <div class="container">
            <h1>Book an Appointment</h1>
            <p>Schedule a service for your pet</p>
        </div>
    </section>

    <section class="booking-steps">
        <div class="container">
            <div class="steps-indicator">
                <div class="step <?php echo $step >= 1 ? 'active' : ''; ?>">1. Service</div>
                <div class="step <?php echo $step >= 2 ? 'active' : ''; ?>">2. Pet</div>
                <div class="step <?php echo $step >= 3 ? 'active' : ''; ?>">3. Date & Time</div>
                <div class="step <?php echo $step >= 4 ? 'active' : ''; ?>">4. Confirm</div>
            </div>

            <?php if ($error): ?>
                <div class="message error"><?php echo htmlspecialchars($error); ?></div>
            <?php endif; ?>

            <?php if ($success): ?>
                <div class="message success">
                    <h3>Appointment Booked!</h3>
                    <p>Your appointment has been confirmed. Check your email for details.</p>
                    <div class="success-actions">
                        <a href="<?php echo url('my_appointments'); ?>" class="btn btn-primary">View My Appointments</a>
                        <a href="<?php echo url(''); ?>" class="btn btn-outline">Back to Home</a>
                    </div>
                </div>
            <?php endif; ?>

            <?php if (!$success): ?>
                <?php if ($step === 1): ?>
                    <!-- Step 1: Select Service -->
                    <form method="post" class="booking-form">
                        <input type="hidden" name="step" value="1">
                        <div class="services-grid">
                            <?php foreach ($services as $service): ?>
                                <label class="service-card">
                                    <input type="radio" name="service_id" value="<?php echo $service['id']; ?>" required>
                                    <div class="service-content">
                                        <h3><?php echo htmlspecialchars($service['service_name']); ?></h3>
                                        <p class="service-category"><?php echo htmlspecialchars($service['category']); ?></p>
                                        <p class="service-description"><?php echo htmlspecialchars(substr($service['description'] ?? '', 0, 100)); ?></p>
                                        <div class="service-meta">
                                            <span class="service-duration"><?php echo $service['duration_minutes']; ?> min</span>
                                            <span class="service-price">₱<?php echo number_format($service['price'], 2); ?></span>
                                        </div>
                                    </div>
                                </label>
                            <?php endforeach; ?>
                        </div>
                        <div class="form-actions">
                            <button type="submit" class="btn btn-primary">Continue to Pet Selection</button>
                        </div>
                    </form>

                <?php elseif ($step === 2): ?>
                    <!-- Step 2: Select Pet -->
                    <form method="post" class="booking-form">
                        <input type="hidden" name="step" value="2">
                        <?php if (empty($pets)): ?>
                            <div class="no-pets">
                                <p>You don't have any pets registered yet.</p>
                                <a href="<?php echo url('add_pet'); ?>" class="btn btn-primary">Add a Pet</a>
                            </div>
                        <?php else: ?>
                            <div class="pets-grid">
                                <?php foreach ($pets as $pet): ?>
                                    <label class="pet-card">
                                        <input type="radio" name="pet_id" value="<?php echo $pet['id']; ?>" required>
                                        <div class="pet-content">
                                            <div class="pet-avatar">
                                                <?php echo icon('paw', 32); ?>
                                            </div>
                                            <h3><?php echo htmlspecialchars($pet['name']); ?></h3>
                                            <p class="pet-details">
                                                <?php echo htmlspecialchars($pet['species']); ?>
                                                <?php if (!empty($pet['breed'])): ?> • <?php echo htmlspecialchars($pet['breed']); ?><?php endif; ?>
                                                <br><?php echo $pet['age']; ?> <?php echo $pet['age'] == 1 ? 'year' : 'years'; ?> old
                                            </p>
                                        </div>
                                    </label>
                                <?php endforeach; ?>
                            </div>
                            <div class="form-actions">
                                <a href="<?php echo url('book_appointment?step=1'); ?>" class="btn btn-secondary">Back</a>
                                <button type="submit" class="btn btn-primary">Continue to Date & Time</button>
                            </div>
                        <?php endif; ?>
                    </form>

                <?php elseif ($step === 3): ?>
                    <!-- Step 3: Select Date & Time -->
                    <form method="post" class="booking-form">
                        <input type="hidden" name="step" value="3">
                        <div class="datetime-picker">
                            <div class="form-group">
                                <label for="appointment_date">Select Date and Time</label>
                                <input type="datetime-local" id="appointment_date" name="appointment_date" 
                                       min="<?php echo date('Y-m-d\TH:i', strtotime('+1 hour')); ?>" 
                                       max="<?php echo date('Y-m-d\TH:i', strtotime('+90 days')); ?>" 
                                       value="<?php echo htmlspecialchars($booking['appointment_date'] ?? ''); ?>" required>
                                <small class="help-text">Please choose a date at least 1 hour from now</small>
                            </div>
                        </div>
                        <div class="form-actions">
                            <a href="<?php echo url('book_appointment?step=2'); ?>" class="btn btn-secondary">Back</a>
                            <button type="submit" class="btn btn-primary">Continue to Confirmation</button>
                        </div>
                    </form>

                <?php elseif ($step === 4): ?>
                    <!-- Step 4: Confirmation -->
                    <?php
                    $serviceId = $booking['service_id'] ?? 0;
                    $petId = $booking['pet_id'] ?? 0;
                    $appointmentDate = $booking['appointment_date'] ?? '';
                    
                    $selectedService = null;
                    foreach ($services as $s) {
                        if ($s['id'] == $serviceId) {
                            $selectedService = $s;
                            break;
                        }
                    }
                    
                    $selectedPet = null;
                    foreach ($pets as $p) {
                        if ($p['id'] == $petId) {
                            $selectedPet = $p;
                            break;
                        }
                    }
                    ?>
                    
                    <div class="confirmation-section">
                        <div class="confirmation-card">
                            <h3>Service Details</h3>
                            <div class="confirmation-details">
                                <div class="detail-row">
                                    <span class="detail-label">Service:</span>
                                    <span class="detail-value"><?php echo htmlspecialchars($selectedService['service_name'] ?? 'N/A'); ?></span>
                                </div>
                                <div class="detail-row">
                                    <span class="detail-label">Category:</span>
                                    <span class="detail-value"><?php echo htmlspecialchars($selectedService['category'] ?? 'N/A'); ?></span>
                                </div>
                                <div class="detail-row">
                                    <span class="detail-label">Duration:</span>
                                    <span class="detail-value"><?php echo $selectedService['duration_minutes'] ?? 0; ?> minutes</span>
                                </div>
                                <div class="detail-row">
                                    <span class="detail-label">Price:</span>
                                    <span class="detail-value price">₱<?php echo number_format($selectedService['price'] ?? 0, 2); ?></span>
                                </div>
                            </div>
                        </div>
                        
                        <div class="confirmation-card">
                            <h3>Pet Details</h3>
                            <div class="confirmation-details">
                                <div class="detail-row">
                                    <span class="detail-label">Name:</span>
                                    <span class="detail-value"><?php echo htmlspecialchars($selectedPet['name'] ?? 'N/A'); ?></span>
                                </div>
                                <div class="detail-row">
                                    <span class="detail-label">Species:</span>
                                    <span class="detail-value"><?php echo htmlspecialchars($selectedPet['species'] ?? 'N/A'); ?></span>
                                </div>
                                <div class="detail-row">
                                    <span class="detail-label">Age:</span>
                                    <span class="detail-value"><?php echo $selectedPet['age'] ?? 0; ?> <?php echo ($selectedPet['age'] ?? 0) == 1 ? 'year' : 'years'; ?> old</span>
                                </div>
                            </div>
                        </div>
                        
                        <div class="confirmation-card">
                            <h3>Appointment Details</h3>
                            <div class="confirmation-details">
                                <div class="detail-row">
                                    <span class="detail-label">Date & Time:</span>
                                    <span class="detail-value"><?php echo date('F j, Y \a\t g:i A', strtotime($appointmentDate)); ?></span>
                                </div>
                            </div>
                        </div>
                        
                        <form method="post" class="confirm-form">
                            <input type="hidden" name="step" value="4">
                            <div class="form-actions">
                                <a href="<?php echo url('book_appointment?step=3'); ?>" class="btn btn-secondary">Back</a>
                                <button type="submit" class="btn btn-primary">Confirm Booking</button>
                            </div>
                        </form>
                    </div>
                <?php endif; ?>
            <?php endif; ?>
        </div>
    </section>
</div>

<script>
// Service card selection highlighting
document.querySelectorAll('.service-card').forEach(card => {
    const radio = card.querySelector('input[type="radio"]');
    
    // Check if radio is checked on page load
    if (radio && radio.checked) {
        card.classList.add('selected');
    }
    
    card.addEventListener('click', function() {
        // Remove selected class from all service cards
        document.querySelectorAll('.service-card').forEach(c => c.classList.remove('selected'));
        // Add selected class to clicked card
        this.classList.add('selected');
        // Check the radio button
        if (radio) radio.checked = true;
    });
});

// Pet card selection highlighting
document.querySelectorAll('.pet-card').forEach(card => {
    const radio = card.querySelector('input[type="radio"]');
    
    // Check if radio is checked on page load
    if (radio && radio.checked) {
        card.classList.add('selected');
    }
    
    card.addEventListener('click', function() {
        // Remove selected class from all pet cards
        document.querySelectorAll('.pet-card').forEach(c => c.classList.remove('selected'));
        // Add selected class to clicked card
        this.classList.add('selected');
        // Check the radio button
        if (radio) radio.checked = true;
    });
});
</script>

<?php require_once __DIR__ . '/../../backend/includes/footer.php'; ?>