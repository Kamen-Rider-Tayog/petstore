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
    header('Location: pets.php?tab=store');
    exit();
}

// Get pet data
$stmt = $conn->prepare("SELECT * FROM store_pets WHERE id = ?");
$stmt->bind_param('i', $petId);
$stmt->execute();
$pet = $stmt->get_result()->fetch_assoc();

if (!$pet) {
    header('Location: pets.php?tab=store');
    exit();
}

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

    // Handle image upload
    $image = $pet['pet_image'];
    if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
        $uploadDir = __DIR__ . '/../../assets/images/pets/';
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }
        
        // Delete old image
        if (!empty($pet['pet_image']) && file_exists($uploadDir . $pet['pet_image'])) {
            unlink($uploadDir . $pet['pet_image']);
        }
        
        $fileName = uniqid() . '_' . basename($_FILES['image']['name']);
        if (move_uploaded_file($_FILES['image']['tmp_name'], $uploadDir . $fileName)) {
            $image = $fileName;
        }
    } elseif (isset($_POST['remove_image'])) {
        // Remove image
        $uploadDir = __DIR__ . '/../../assets/images/pets/';
        if (!empty($pet['pet_image']) && file_exists($uploadDir . $pet['pet_image'])) {
            unlink($uploadDir . $pet['pet_image']);
        }
        $image = '';
    }

    if (empty($name) || empty($species)) {
        $error = 'Please fill in all required fields.';
    } else {
        $stmt = $conn->prepare("UPDATE store_pets SET name = ?, species = ?, breed = ?, age = ?, gender = ?, color = ?, description = ?, pet_status = ?, featured = ?, pet_image = ? WHERE id = ?");
        $stmt->bind_param("sssissssiii", $name, $species, $breed, $age, $gender, $color, $description, $pet_status, $featured, $image, $petId);
        
        if ($stmt->execute()) {
            $success = 'Pet updated successfully!';
            // Refresh pet data
            $stmt = $conn->prepare("SELECT * FROM store_pets WHERE id = ?");
            $stmt->bind_param('i', $petId);
            $stmt->execute();
            $pet = $stmt->get_result()->fetch_assoc();
        } else {
            $error = 'Error updating pet: ' . $conn->error;
        }
    }
}

$page_title = 'Edit Pet for Adoption - ' . $pet['name'];
require_once __DIR__ . '/../includes/header.php';

echo '<link rel="stylesheet" href="/Ria-Pet-Store/admin/css/pets.css?v=' . time() . '">';
?>

<div class="admin-dashboard">
    <div class="page-header">
        <h1>Edit Pet for Adoption: <?php echo htmlspecialchars($pet['name']); ?></h1>
        <a href="pets.php?tab=store" class="btn btn-outline"><?php echo icon('arrow-left', 16); ?> Back to Pets</a>
    </div>

    <?php if ($error): ?>
        <div class="message error"><?php echo htmlspecialchars($error); ?></div>
    <?php endif; ?>
    
    <?php if ($success): ?>
        <div class="message success"><?php echo htmlspecialchars($success); ?></div>
    <?php endif; ?>

    <form method="post" enctype="multipart/form-data" class="pet-form">
        <div class="form-grid">
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
                <label for="pet_status">Status</label>
                <select id="pet_status" name="pet_status">
                    <option value="available" <?php echo $pet['pet_status'] === 'available' ? 'selected' : ''; ?>>Available</option>
                    <option value="reserved" <?php echo $pet['pet_status'] === 'reserved' ? 'selected' : ''; ?>>Reserved</option>
                    <option value="adopted" <?php echo $pet['pet_status'] === 'adopted' ? 'selected' : ''; ?>>Adopted</option>
                </select>
            </div>
            <div class="form-group">
                <label>
                    <input type="checkbox" name="featured" value="1" <?php echo $pet['featured'] ? 'checked' : ''; ?>> Featured Pet
                </label>
            </div>
        </div>

        <div class="form-group">
            <label for="description">Description/Bio</label>
            <textarea id="description" name="description" rows="4"><?php echo htmlspecialchars($pet['description']); ?></textarea>
        </div>

        <div class="form-group">
            <label>Current Photo</label>
            <?php if (!empty($pet['pet_image'])): ?>
                <div class="current-photo">
                    <img src="/Ria-Pet-Store/assets/images/pets/<?php echo htmlspecialchars($pet['pet_image']); ?>" class="pet-photo-large">
                    <label><input type="checkbox" name="remove_image" value="1"> Remove current photo</label>
                </div>
            <?php else: ?>
                <p class="no-photo">No photo uploaded</p>
            <?php endif; ?>
        </div>

        <div class="form-group">
            <label for="image">Upload New Photo</label>
            <input type="file" id="image" name="image" accept="image/*">
            <small>Leave empty to keep current photo</small>
        </div>

        <div class="action-buttons">
            <button type="submit" class="btn btn-primary"><?php echo icon('check', 16); ?> Update Pet</button>
            <a href="pets.php?tab=store" class="btn btn-outline">Cancel</a>
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