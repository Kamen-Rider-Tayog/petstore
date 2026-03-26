<?php
session_name('petstore_session');
session_start();

if (!isset($_SESSION['is_admin']) || $_SESSION['is_admin'] !== true) {
    header('Location: ' . url('login?error=Access denied'));
    exit;
}

require_once __DIR__ . '/../../backend/config/database.php';
require_once __DIR__ . '/../../backend/functions/helpers.php';

$petId = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if (!$petId) {
    header('Location: pets.php?tab=customer');
    exit();
}

// Get pet data
$stmt = $conn->prepare("SELECT cp.*, c.first_name, c.last_name, c.email 
                        FROM customer_pets cp 
                        LEFT JOIN customers c ON cp.customer_id = c.id 
                        WHERE cp.id = ?");
$stmt->bind_param('i', $petId);
$stmt->execute();
$pet = $stmt->get_result()->fetch_assoc();

if (!$pet) {
    header('Location: pets.php?tab=customer');
    exit();
}

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
        $stmt = $conn->prepare("UPDATE customer_pets SET customer_id = ?, name = ?, species = ?, breed = ?, age = ?, gender = ?, color = ?, weight = ?, weight_unit = ?, is_active = ? WHERE id = ?");
        $stmt->bind_param("isssissssii", $customer_id, $name, $species, $breed, $age, $gender, $color, $weight, $weight_unit, $is_active, $petId);
        
        if ($stmt->execute()) {
            $success = 'Pet updated successfully!';
            // Refresh pet data
            $stmt = $conn->prepare("SELECT cp.*, c.first_name, c.last_name, c.email 
                                    FROM customer_pets cp 
                                    LEFT JOIN customers c ON cp.customer_id = c.id 
                                    WHERE cp.id = ?");
            $stmt->bind_param('i', $petId);
            $stmt->execute();
            $pet = $stmt->get_result()->fetch_assoc();
        } else {
            $error = 'Error updating pet: ' . $conn->error;
        }
    }
}

$page_title = 'Edit Customer Pet - ' . $pet['name'];
require_once __DIR__ . '/../includes/header.php';

echo '<link rel="stylesheet" href="/Ria-Pet-Store/admin/css/pets.css?v=' . time() . '">';
?>

<div class="admin-dashboard">
    <div class="page-header">
        <h1>Edit Customer Pet: <?php echo htmlspecialchars($pet['name']); ?></h1>
        <a href="pets.php?tab=customer" class="btn btn-outline"><?php echo icon('arrow-left', 16); ?> Back to Pets</a>
    </div>

    <?php if ($error): ?>
        <div class="message error"><?php echo htmlspecialchars($error); ?></div>
    <?php endif; ?>
    
    <?php if ($success): ?>
        <div class="message success"><?php echo htmlspecialchars($success); ?></div>
    <?php endif; ?>

    <form method="post" class="pet-form">
        <div class="form-grid">
            <div class="form-group">
                <label for="customer_id">Owner *</label>
                <select id="customer_id" name="customer_id" required>
                    <option value="">Select Customer</option>
                    <?php foreach ($customers as $customer): ?>
                        <option value="<?php echo $customer['id']; ?>" <?php echo $customer['id'] == $pet['customer_id'] ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($customer['first_name'] . ' ' . $customer['last_name'] . ' (' . $customer['email'] . ')'); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="form-group">
                <label for="name">Pet Name *</label>
                <input type="text" id="name" name="name" value="<?php echo htmlspecialchars($pet['name']); ?>" required>
            </div>
            <div class="form-group">
                <label for="species">Species *</label>
                <?php
                $commonSpecies = ['dog', 'cat', 'rabbit', 'bird', 'hamster', 'guinea pig', 'ferret'];
                $isCommonSpecies = in_array(strtolower($pet['species']), $commonSpecies);
                ?>
                <select id="species" name="species" required>
                    <option value="">Select Species</option>
                    <option value="dog" <?php echo $pet['species'] === 'dog' ? 'selected' : ''; ?>>Dog</option>
                    <option value="cat" <?php echo $pet['species'] === 'cat' ? 'selected' : ''; ?>>Cat</option>
                    <option value="rabbit" <?php echo $pet['species'] === 'rabbit' ? 'selected' : ''; ?>>Rabbit</option>
                    <option value="bird" <?php echo $pet['species'] === 'bird' ? 'selected' : ''; ?>>Bird</option>
                    <option value="hamster" <?php echo $pet['species'] === 'hamster' ? 'selected' : ''; ?>>Hamster</option>
                    <option value="guinea pig" <?php echo $pet['species'] === 'guinea pig' ? 'selected' : ''; ?>>Guinea Pig</option>
                    <option value="ferret" <?php echo $pet['species'] === 'ferret' ? 'selected' : ''; ?>>Ferret</option>
                    <option value="other" <?php echo !$isCommonSpecies ? 'selected' : ''; ?>>Other</option>
                </select>
            </div>
            <div class="form-group" id="customSpeciesGroup" style="display: <?php echo !$isCommonSpecies ? 'block' : 'none'; ?>;">
                <label for="custom_species">Other Species *</label>
                <input type="text" id="custom_species" name="custom_species" value="<?php echo !$isCommonSpecies ? htmlspecialchars($pet['species']) : ''; ?>" placeholder="Enter species name">
            </div>
            <div class="form-group">
                <label for="breed">Breed</label>
                <input type="text" id="breed" name="breed" value="<?php echo htmlspecialchars($pet['breed']); ?>">
            </div>
            <div class="form-group">
                <label for="age">Age (years)</label>
                <input type="number" id="age" name="age" min="0" max="30" value="<?php echo $pet['age']; ?>">
            </div>
            <div class="form-group">
                <label for="gender">Gender</label>
                <select id="gender" name="gender">
                    <option value="">Select Gender</option>
                    <option value="male" <?php echo $pet['gender'] === 'male' ? 'selected' : ''; ?>>Male</option>
                    <option value="female" <?php echo $pet['gender'] === 'female' ? 'selected' : ''; ?>>Female</option>
                </select>
            </div>
            <div class="form-group">
                <label for="color">Color</label>
                <input type="text" id="color" name="color" value="<?php echo htmlspecialchars($pet['color']); ?>">
            </div>
            <div class="form-group">
                <label for="weight">Weight</label>
                <div style="display: flex; gap: 0.5rem;">
                    <input type="number" id="weight" name="weight" step="0.01" value="<?php echo $pet['weight']; ?>" style="flex: 1;">
                    <select id="weight_unit" name="weight_unit" style="width: 80px;">
                        <option value="kg" <?php echo $pet['weight_unit'] === 'kg' ? 'selected' : ''; ?>>kg</option>
                        <option value="lb" <?php echo $pet['weight_unit'] === 'lb' ? 'selected' : ''; ?>>lb</option>
                    </select>
                </div>
            </div>
            <div class="form-group">
                <label>
                    <input type="checkbox" name="is_active" value="1" <?php echo $pet['is_active'] ? 'checked' : ''; ?>> Active (pet can book appointments)
                </label>
            </div>
        </div>

        <div class="action-buttons">
            <button type="submit" class="btn btn-primary"><?php echo icon('check', 16); ?> Update Pet</button>
            <a href="pets.php?tab=customer" class="btn btn-outline">Cancel</a>
        </div>
    </form>
</div>

<script>
document.getElementById('species').addEventListener('change', function() {
    const customGroup = document.getElementById('customSpeciesGroup');
    const customInput = document.getElementById('custom_species');
    if (this.value === 'other') {
        customGroup.style.display = 'block';
        customInput.required = true;
    } else {
        customGroup.style.display = 'none';
        customInput.required = false;
    }
});
</script>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>