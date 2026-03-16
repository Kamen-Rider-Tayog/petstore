<?php
require_once '../../backend/includes/header.php';
require_once '../../backend/config/database.php';
require_once '../../backend/includes/appointment_functions.php';
?>
<link rel="stylesheet" href="<?php echo asset('css/appointment.css'); ?>">
<?php

if (!isset($_SESSION['booking'])) {
    $_SESSION['booking'] = [];
}

// For testing, default to customer_id = 1 if not logged in.
$customerId = $_SESSION['user_id'] ?? 1;

$step = isset($_GET['step']) ? (int)$_GET['step'] : 1;
$step = max(1, min(5, $step));

function getService($conn, $serviceId) {
    $stmt = $conn->prepare('SELECT * FROM services WHERE id = ? LIMIT 1');
    $stmt->bind_param('i', $serviceId);
    $stmt->execute();
    return $stmt->get_result()->fetch_assoc();
}

function getCustomerPets($conn, $customerId) {
    $stmt = $conn->prepare('SELECT * FROM pets WHERE owner_id = ?');
    $stmt->bind_param('i', $customerId);
    $stmt->execute();
    return $stmt->get_result();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['step']) && (int)$_POST['step'] === 1) {
        $serviceId = (int)($_POST['service_id'] ?? 0);
        if ($serviceId > 0) {
            $_SESSION['booking']['service_id'] = $serviceId;
            header('Location: book_appointment.php?step=2');
            exit;
        }
    }

    if (isset($_POST['step']) && (int)$_POST['step'] === 2) {
        $petId = (int)($_POST['pet_id'] ?? 0);
        if ($petId > 0) {
            $_SESSION['booking']['pet_id'] = $petId;
            header('Location: book_appointment.php?step=3');
            exit;
        }
    }

    if (isset($_POST['step']) && (int)$_POST['step'] === 3) {
        $date = trim($_POST['appointment_date'] ?? '');
        if ($date) {
            $_SESSION['booking']['date'] = $date;
            header('Location: book_appointment.php?step=4');
            exit;
        }
    }

    if (isset($_POST['step']) && (int)$_POST['step'] === 4) {
        $time = trim($_POST['appointment_time'] ?? '');
        if ($time) {
            $_SESSION['booking']['time'] = $time;
            // In a full system we'd choose an employee here; leave empty for now
            header('Location: book_appointment.php?step=5');
            exit;
        }
    }

    if (isset($_POST['step']) && (int)$_POST['step'] === 5) {
        // Confirm and save appointment
        if (!empty($booking['service_id']) && !empty($booking['pet_id']) && !empty($booking['date']) && !empty($booking['time'])) {
            $service = getService($conn, $booking['service_id']);
            if ($service) {
                $appointmentDateTime = $booking['date'] . ' ' . $booking['time'] . ':00';
                $employeeId = autoAssignEmployee($conn, $booking['service_id'], $appointmentDateTime);
                if ($employeeId) {
                    $stmt = $conn->prepare('INSERT INTO appointments (customer_id, pet_id, employee_id, appointment_date, service_type, duration_minutes, status) VALUES (?, ?, ?, ?, ?, ?, ?)');
                    $status = 'confirmed';
                    $stmt->bind_param('iiisssis', $customerId, $booking['pet_id'], $employeeId, $appointmentDateTime, $service['service_name'], $service['duration_minutes'], $status);
                    if ($stmt->execute()) {
                        $_SESSION['booking']['confirmed'] = true;
                        unset($_SESSION['booking']); // Clear after success
                        header('Location: book_appointment.php?step=5&done=1');
                        exit;
                    } else {
                        $error = 'Failed to save appointment.';
                    }
                } else {
                    $error = 'No available employee for the selected time.';
                }
            } else {
                $error = 'Invalid service.';
            }
        } else {
            $error = 'Incomplete booking information.';
        }
        if (isset($error)) {
            // Stay on step 5 and show error
        }
    }
}

$booking = &$_SESSION['booking'];

?>

<h1>Book Appointment</h1>

<nav style="margin-bottom: 20px; display:flex; gap:10px; flex-wrap:wrap;">
    <?php for ($i = 1; $i <= 5; $i++): ?>
        <a href="book_appointment.php?step=<?php echo $i; ?>" class="btn" style="<?php echo $step === $i ? 'background:#2ecc71;' : ''; ?>">
            Step <?php echo $i; ?>
        </a>
    <?php endfor; ?>
</nav>

<?php if ($step === 1): ?>
    <h2>Select Service</h2>
    <?php
    $services = [];
    $stmt = $conn->prepare('SELECT * FROM services ORDER BY category, service_name');
    $stmt->execute();
    $result = $stmt->get_result();
    while ($row = $result->fetch_assoc()) {
        $services[] = $row;
    }
    ?>

    <form method="post">
        <input type="hidden" name="step" value="1">
        <div style="display:grid; grid-template-columns: repeat(auto-fit,minmax(260px,1fr)); gap:16px;">
            <?php foreach ($services as $service): ?>
                <label style="border:1px solid rgba(0,0,0,0.13); padding:14px; border-radius:10px; display:block; cursor:pointer;">
                    <input type="radio" name="service_id" value="<?php echo (int)$service['id']; ?>" style="margin-right:10px;" <?php echo (isset($booking['service_id']) && $booking['service_id'] == $service['id']) ? 'checked' : ''; ?>>
                    <strong><?php echo htmlspecialchars($service['service_name']); ?></strong><br>
                    <small><?php echo htmlspecialchars($service['category']); ?> • <?php echo (int)$service['duration_minutes']; ?> min • ₱<?php echo number_format($service['price'],2); ?></small>
                </label>
            <?php endforeach; ?>
        </div>

        <div style="margin-top:20px;">
            <button class="btn btn-primary" type="submit">Continue to Pet Selection</button>
        </div>
    </form>

<?php elseif ($step === 2): ?>
    <h2>Select Your Pet</h2>
    <?php if (empty($booking['service_id'])): ?>
        <p>Please choose a service first. <a href="book_appointment.php?step=1">Go back to Step 1</a></p>
    <?php else: ?>
        <?php
        $pets = getCustomerPets($conn, $customerId);
        ?>
        <?php if ($pets->num_rows === 0): ?>
            <p>You don&#39;t have any pets yet.</p>
            <p><a href="add_pet.php" class="btn">Add a Pet</a></p>
        <?php else: ?>
            <form method="post">
                <input type="hidden" name="step" value="2">
                <div style="display:grid; grid-template-columns: repeat(auto-fit,minmax(240px,1fr)); gap:16px;">
                    <?php while ($pet = $pets->fetch_assoc()): ?>
                        <label style="border:1px solid rgba(0,0,0,0.13); padding:14px; border-radius:10px; display:block; cursor:pointer;">
                            <input type="radio" name="pet_id" value="<?php echo (int)$pet['id']; ?>" style="margin-right:10px;" <?php echo (isset($booking['pet_id']) && $booking['pet_id'] == $pet['id']) ? 'checked' : ''; ?>>
                            <strong><?php echo htmlspecialchars($pet['name']); ?></strong><br>
                            <small><?php echo htmlspecialchars($pet['species']); ?> • <?php echo (int)$pet['age']; ?> years</small>
                        </label>
                    <?php endwhile; ?>
                </div>

                <div style="margin-top:20px;">
                    <button class="btn btn-primary" type="submit">Continue to Date</button>
                </div>
            </form>
        <?php endif; ?>
    <?php endif; ?>

<?php elseif ($step === 3): ?>
    <h2>Select Appointment Date</h2>
    <?php if (empty($booking['pet_id']) || empty($booking['service_id'])): ?>
        <p>Please complete previous steps first. <a href="book_appointment.php?step=1">Start booking</a></p>
    <?php else: ?>
        <form method="post">
            <input type="hidden" name="step" value="3">
            <div style="max-width:320px;">
                <label for="appointment_date">Choose a date:</label><br>
                <input type="date" id="appointment_date" name="appointment_date" required value="<?php echo htmlspecialchars($booking['date'] ?? ''); ?>" style="padding:8px; width:100%; margin-top:6px;" min="<?php echo date('Y-m-d'); ?>" max="<?php echo date('Y-m-d', strtotime('+90 days')); ?>">
            </div>
            <div style="margin-top:20px;">
                <button class="btn btn-primary" type="submit">Continue to Time</button>
            </div>
        </form>
    <?php endif; ?>

<?php elseif ($step === 4): ?>
    <h2>Select Time (Placeholder)</h2>
    <p>This step will show available time slots based on the selected date and service.</p>
    <form method="post">
        <input type="hidden" name="step" value="4">
        <div style="max-width:320px;">
            <label for="appointment_time">Preferred time (HH:MM):</label><br>
            <input type="time" id="appointment_time" name="appointment_time" required value="<?php echo htmlspecialchars($booking['time'] ?? ''); ?>" style="padding:8px; width:100%; margin-top:6px;">
        </div>
        <div style="margin-top:20px;">
            <button class="btn btn-primary" type="submit">Continue to Confirmation</button>
        </div>
    </form>

<?php else: ?>
    <h2>Review & Confirm</h2>
    <?php if (isset($_GET['done'])): ?>
        <div style="padding:16px; border:1px solid #2ecc71; background:#e8f8f5; border-radius:8px; margin-bottom:20px;">
            <strong>Booking confirmed!</strong> Your appointment has been saved.
        </div>
    <?php endif; ?>

    <div style="display:grid; grid-template-columns:1fr 1fr; gap:20px;">
        <div style="border:1px solid rgba(0,0,0,0.1); padding:16px; border-radius:10px;">
            <h3>Service</h3>
            <?php if (!empty($booking['service_id'])): $service = getService($conn, $booking['service_id']); ?>
                <p><strong><?php echo htmlspecialchars($service['service_name']); ?></strong></p>
                <p><?php echo nl2br(htmlspecialchars($service['description'])); ?></p>
                <p><strong>Duration:</strong> <?php echo (int)$service['duration_minutes']; ?> min</p>
                <p><strong>Price:</strong> ₱<?php echo number_format($service['price'],2); ?></p>
            <?php else: ?>
                <p>No service selected.</p>
            <?php endif; ?>
            <a href="book_appointment.php?step=1" class="btn btn-warning">Change Service</a>
        </div>
        <div style="border:1px solid rgba(0,0,0,0.1); padding:16px; border-radius:10px;">
            <h3>Details</h3>
            <p><strong>Pet:</strong> <?php
                if (!empty($booking['pet_id'])) {
                    $pstmt = $conn->prepare('SELECT name, species FROM pets WHERE id = ?');
                    $pstmt->bind_param('i', $booking['pet_id']);
                    $pstmt->execute();
                    $pet = $pstmt->get_result()->fetch_assoc();
                    echo htmlspecialchars($pet['name'] ?? '-');
                } else {
                    echo '-';
                }
            ?></p>
            <p><strong>Date:</strong> <?php echo htmlspecialchars($booking['date'] ?? '-'); ?></p>
            <p><strong>Time:</strong> <?php echo htmlspecialchars($booking['time'] ?? '-'); ?></p>
            <a href="book_appointment.php?step=2" class="btn btn-warning">Change Pet</a>
            <a href="book_appointment.php?step=3" class="btn btn-warning" style="margin-top:10px;">Change Date</a>
        </div>
    </div>

    <form method="post" style="margin-top:24px;">
        <input type="hidden" name="step" value="5">
        <button class="btn btn-primary" type="submit">Confirm Booking</button>
    </form>

<?php endif; ?>

<script src="<?php echo asset('js/appointment.js'); ?>"></script>
<?php require_once '../../backend/includes/footer.php'; ?>
