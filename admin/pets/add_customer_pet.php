<?php
session_name('petstore_session');
session_start();

if (!isset($_SESSION['is_admin']) || $_SESSION['is_admin'] !== true) {
    header('Location: ' . url('login?error=Access denied'));
    exit;
}

require_once __DIR__ . '/../../backend/config/database.php';
require_once __DIR__ . '/../../backend/functions/helpers.php';

$page_title = 'Add Customer Pet';
require_once __DIR__ . '/../includes/header.php';

// Get customers for dropdown
$customers = [];
$customerResult = $conn->query("SELECT id, first_name, last_name, email FROM customers ORDER BY first_name");
while ($row = $customerResult->fetch_assoc()) {
    $customers[] = $row;
}

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $customer_id = (int)($_POST['customer_id'] ?? 0);
    $name = trim($_POST['name'] ?? '');
    $species = trim($_POST['species'] ?? '');
    $custom_species = trim($_POST['custom_species'] ?? '');
    $breed = trim($_POST['breed'] ?? '');
    $age = (int)($_POST['age'] ?? 0);
    $gender = $_POST['gender'] ?? '';
    $color = trim($_POST['color'] ?? '');
    $weight = (float)($_POST['weight'] ?? 0);
    $weight_unit = $_POST['weight_unit'] ?? 'kg';
    $is_active = isset($_POST['is_active']) ? 1 : 0;

    // Handle custom species
    if ($species === 'other' && !empty($custom_species)) {
        $species = $custom_species;
    }

    if (!$customer_id || empty($name) || empty($species)) {
        $error = 'Please select a customer and fill in all required fields.';
    } else {
        $stmt = $conn->prepare("INSERT INTO customer_pets (customer_id, name, species, breed, age, gender, color, weight, weight_unit, is_active) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("isssissssi", $customer_id, $name, $species, $breed, $age, $gender, $color, $weight, $weight_unit, $is_active);
        
        if ($stmt->execute()) {
            $success = 'Pet added successfully!';
        } else {
            $error = 'Error adding pet: ' . $conn->error;
        }
    }
}

echo '<link rel="stylesheet" href="/Ria-Pet-Store/admin/css/pets.css?v=' . time() . '">';
?>

<div class="admin-dashboard">
    <div class="page-header">
        <h1>Add Customer Pet</h1>
        <a href="pets.php?tab=customer" class="btn btn-outline"><?php echo icon('arrow-left', 16); ?> Back to Pets</a>
    </div>

    <?php if ($error): ?>
        <div class="message error"><?php echo htmlspecialchars($error); ?></div>
    <?php endif; ?>
    
    <?php if ($success): ?>
        <div class="message success">
            <?php echo htmlspecialchars($success); ?>
            <div style="margin-top: 0.5rem;">
                <a href="add_customer_pet.php" class="btn btn-primary btn-small">Add Another</a>
                <a href="pets.php?tab=customer" class="btn btn-outline btn-small">View All Pets</a>
            </div>
        </div>
    <?php endif; ?>

    <?php if (!$success): ?>
    <form method="post" class="pet-form">
        <div class="form-grid">
            <div class="form-group">
                <label for="customer_id">Owner *</label>
                <select id="customer_id" name="customer_id" required>
                    <option value="">Select Customer</option>
                    <?php foreach ($customers as $customer): ?>
                        <option value="<?php echo $customer['id']; ?>">
                            <?php echo htmlspecialchars($customer['first_name'] . ' ' . $customer['last_name'] . ' (' . $customer['email'] . ')'); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="form-group">
                <label for="name">Pet Name *</label>
                <input type="text" id="name" name="name" required>
            </div>
            <div class="form-group">
                <label for="species">Species *</label>
                <select id="species" name="species" required>
                    <option value="">Select Species</option>
                    <option value="dog">Dog</option>
                    <option value="cat">Cat</option>
                    <option value="rabbit">Rabbit</option>
                    <option value="bird">Bird</option>
                    <option value="hamster">Hamster</option>
                    <option value="guinea pig">Guinea Pig</option>
                    <option value="ferret">Ferret</option>
                    <option value="other">Other</option>
                </select>
            </div>
            <div class="form-group" id="customSpeciesGroup" style="display: none;">
                <label for="custom_species">Other Species *</label>
                <input type="text" id="custom_species" name="custom_species" placeholder="Enter species name">
            </div>
            <div class="form-group">
                <label for="breed">Breed</label>
                <input type="text" id="breed" name="breed">
            </div>
            <div class="form-group">
                <label for="age">Age (years)</label>
                <input type="number" id="age" name="age" min="0" max="30" value="0">
            </div>
            <div class="form-group">
                <label for="gender">Gender</label>
                <select id="gender" name="gender">
                    <option value="">Select Gender</option>
                    <option value="male">Male</option>
                    <option value="female">Female</option>
                </select>
            </div>
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
            <div class="form-group">
                <label>
                    <input type="checkbox" name="is_active" value="1" checked> Active (pet can book appointments)
                </label>
            </div>
        </div>

        <div class="action-buttons">
            <button type="submit" class="btn btn-primary"><?php echo icon('check', 16); ?> Add Pet</button>
            <a href="pets.php?tab=customer" class="btn btn-outline">Cancel</a>
        </div>
    </form>
    <?php endif; ?>
</div>

<script>
// Show/hide custom species input when "Other" is selected
document.getElementById('species').addEventListener('change', function() {
    const customGroup = document.getElementById('customSpeciesGroup');
    const customInput = document.getElementById('custom_species');
    if (this.value === 'other') {
        customGroup.style.display = 'block';
        customInput.required = true;
    } else {
        customGroup.style.display = 'none';
        customInput.required = false;
        customInput.value = '';
    }
});
</script>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>