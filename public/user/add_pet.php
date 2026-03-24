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

require_once __DIR__ . '/../../backend/includes/header.php';

$customerId = $_SESSION['customer_id'];
$error = '';
$success = false;

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $species = trim($_POST['species'] ?? '');
    $breed = trim($_POST['breed'] ?? '');
    $age = (int)($_POST['age'] ?? 0);
    $gender = $_POST['gender'] ?? '';
    $color = trim($_POST['color'] ?? '');
    $weight = (float)($_POST['weight'] ?? 0);
    $weight_unit = $_POST['weight_unit'] ?? 'kg';
    $microchip_id = trim($_POST['microchip_id'] ?? '');
    $medical_notes = trim($_POST['medical_notes'] ?? '');

    // Validate
    if (empty($name) || empty($species)) {
        $error = 'Pet name and species are required.';
    } else {
        $stmt = $conn->prepare("
            INSERT INTO customer_pets (customer_id, name, species, breed, age, gender, color, weight, weight_unit, microchip_id, medical_notes, is_active)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 1)
        ");
        $stmt->bind_param("isssissssss", $customerId, $name, $species, $breed, $age, $gender, $color, $weight, $weight_unit, $microchip_id, $medical_notes);
        
        if ($stmt->execute()) {
            $success = true;
        } else {
            $error = 'Failed to add pet. Please try again.';
        }
    }
}

$page_title = 'Add Pet';
?>
<link rel="stylesheet" href="/Ria-Pet-Store/assets/css/user/add_pet.css?v=<?php echo time(); ?>">

<div class="add-pet-page">
    <section class="page-hero">
        <div class="container">
            <h1>Add New Pet</h1>
            <p>Register your pet to book appointments</p>
        </div>
    </section>

    <section class="add-pet-content">
        <div class="container">
            <div class="add-pet-card">
                <?php if ($error): ?>
                    <div class="message error"><?php echo htmlspecialchars($error); ?></div>
                <?php endif; ?>

                <?php if ($success): ?>
                    <div class="message success">
                        <p>Pet added successfully!</p>
                        <a href="<?php echo url('my_pets'); ?>" class="btn btn-primary">View My Pets</a>
                    </div>
                <?php else: ?>
                    <form method="post">
                        <div class="form-group">
                            <label for="name">Pet Name <span class="required">*</span></label>
                            <input type="text" id="name" name="name" required>
                        </div>

                        <div class="form-row">
                            <div class="form-group">
                                <label for="species">Species <span class="required">*</span></label>
                                <select id="species" name="species" required>
                                    <option value="">Select Species</option>
                                    <option value="dog">Dog</option>
                                    <option value="cat">Cat</option>
                                    <option value="bird">Bird</option>
                                    <option value="rabbit">Rabbit</option>
                                    <option value="hamster">Hamster</option>
                                    <option value="guinea pig">Guinea Pig</option>
                                    <option value="other">Other</option>
                                </select>
                            </div>
                            <div class="form-group">
                                <label for="breed">Breed</label>
                                <input type="text" id="breed" name="breed">
                            </div>
                        </div>

                        <div class="form-row">
                            <div class="form-group">
                                <label for="age">Age (years)</label>
                                <input type="number" id="age" name="age" min="0" step="1" value="0">
                            </div>
                            <div class="form-group">
                                <label for="gender">Gender</label>
                                <select id="gender" name="gender">
                                    <option value="">Select Gender</option>
                                    <option value="male">Male</option>
                                    <option value="female">Female</option>
                                </select>
                            </div>
                        </div>

                        <div class="form-row">
                            <div class="form-group">
                                <label for="color">Color</label>
                                <input type="text" id="color" name="color">
                            </div>
                            <div class="form-group">
                                <label for="weight">Weight</label>
                                <div style="display: flex; gap: 0.5rem;">
                                    <input type="number" id="weight" name="weight" step="0.01" style="flex: 1;">
                                    <select id="weight_unit" name="weight_unit" style="width: 80px;">
                                        <option value="kg">kg</option>
                                        <option value="lb">lb</option>
                                    </select>
                                </div>
                            </div>
                        </div>

                        <div class="form-group">
                            <label for="microchip_id">Microchip ID</label>
                            <input type="text" id="microchip_id" name="microchip_id">
                        </div>

                        <div class="form-group">
                            <label for="medical_notes">Medical Notes</label>
                            <textarea id="medical_notes" name="medical_notes" rows="3"></textarea>
                        </div>

                        <div class="btn-group">
                            <button type="submit" class="btn btn-primary"><?php echo icon('check', 16); ?> Add Pet</button>
                            <a href="<?php echo url('my_pets'); ?>" class="btn btn-secondary"><?php echo icon('x', 16); ?> Cancel</a>
                        </div>
                    </form>
                <?php endif; ?>
            </div>
        </div>
    </section>
</div>

<?php require_once __DIR__ . '/../../backend/includes/footer.php'; ?>