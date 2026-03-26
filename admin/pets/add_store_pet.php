<?php
session_name('petstore_session');
session_start();

if (!isset($_SESSION['is_admin']) || $_SESSION['is_admin'] !== true) {
    header('Location: ' . url('login?error=Access denied'));
    exit;
}

require_once __DIR__ . '/../../backend/config/database.php';
require_once __DIR__ . '/../../backend/functions/helpers.php';

$page_title = 'Add Pet for Adoption';
require_once __DIR__ . '/../includes/header.php';

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $species = trim($_POST['species'] ?? '');
    $custom_species = trim($_POST['custom_species'] ?? '');
    $breed = trim($_POST['breed'] ?? '');
    $age = (int)($_POST['age'] ?? 0);
    $gender = $_POST['gender'] ?? '';
    $color = trim($_POST['color'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $pet_status = $_POST['pet_status'] ?? 'available';
    $featured = isset($_POST['featured']) ? 1 : 0;

    // Handle custom species
    if ($species === 'other' && !empty($custom_species)) {
        $species = $custom_species;
    }

    $image = '';
    if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
        $uploadDir = __DIR__ . '/../../assets/images/pets/';
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }
        $fileName = uniqid() . '_' . basename($_FILES['image']['name']);
        if (move_uploaded_file($_FILES['image']['tmp_name'], $uploadDir . $fileName)) {
            $image = $fileName;
        }
    }

    if (empty($name) || empty($species)) {
        $error = 'Please fill in all required fields.';
    } else {
        $stmt = $conn->prepare("INSERT INTO store_pets (name, species, breed, age, gender, color, description, pet_status, featured, pet_image) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("sssissssii", $name, $species, $breed, $age, $gender, $color, $description, $pet_status, $featured, $image);
        
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
        <h1>Add Pet for Adoption</h1>
        <a href="pets.php?tab=store" class="btn btn-outline"><?php echo icon('arrow-left', 16); ?> Back to Pets</a>
    </div>

    <?php if ($error): ?>
        <div class="message error"><?php echo htmlspecialchars($error); ?></div>
    <?php endif; ?>
    
    <?php if ($success): ?>
        <div class="message success">
            <?php echo htmlspecialchars($success); ?>
            <div style="margin-top: 0.5rem;">
                <a href="add_store_pet.php" class="btn btn-primary btn-small">Add Another</a>
                <a href="pets.php?tab=store" class="btn btn-outline btn-small">View All Pets</a>
            </div>
        </div>
    <?php endif; ?>

    <?php if (!$success): ?>
    <form method="post" enctype="multipart/form-data" class="pet-form">
        <div class="form-grid">
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
                <label for="pet_status">Status</label>
                <select id="pet_status" name="pet_status">
                    <option value="available">Available</option>
                    <option value="reserved">Reserved</option>
                    <option value="adopted">Adopted</option>
                </select>
            </div>
            <div class="form-group">
                <label>
                    <input type="checkbox" name="featured" value="1"> Featured Pet
                </label>
            </div>
        </div>

        <div class="form-group">
            <label for="description">Description/Bio</label>
            <textarea id="description" name="description" rows="4"></textarea>
        </div>

        <div class="form-group">
            <label for="image">Photo</label>
            <input type="file" id="image" name="image" accept="image/*">
        </div>

        <div class="action-buttons">
            <button type="submit" class="btn btn-primary"><?php echo icon('check', 16); ?> Add Pet</button>
            <a href="pets.php?tab=store" class="btn btn-outline">Cancel</a>
        </div>
    </form>
    <?php endif; ?>
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
        customInput.value = '';
    }
});
</script>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>