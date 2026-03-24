<?php
require_once '../includes/header.php';
require_once '../includes/sidebar.php';

$petId = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$message = '';

if (!$petId) {
    header('Location: pets.php');
    exit();
}

// Get pet data
$stmt = $conn->prepare("SELECT * FROM store_pets WHERE id = ?");
$stmt->bind_param('i', $petId);
$stmt->execute();
$pet = $stmt->get_result()->fetch_assoc();

if (!$pet) {
    header('Location: pets.php');
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $species = trim($_POST['species'] ?? '');
    $age = (int)($_POST['age'] ?? 0);
    $price = (float)($_POST['price'] ?? 0);
    $description = trim($_POST['description'] ?? '');
    $status = $_POST['status'] ?? 'available';

    // Handle file upload
    $image = $pet['image']; // Keep existing image by default
    if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
        $uploadDir = '../assets/uploads/pets/';
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }

        // Delete old image if exists
        if (!empty($pet['image']) && file_exists($uploadDir . $pet['image'])) {
            unlink($uploadDir . $pet['image']);
        }

        $fileName = uniqid() . '_' . basename($_FILES['image']['name']);
        $uploadFile = $uploadDir . $fileName;

        if (move_uploaded_file($_FILES['image']['tmp_name'], $uploadFile)) {
            $image = $fileName;
        }
    } elseif (isset($_POST['remove_image'])) {
        // Remove image
        $uploadDir = '../assets/uploads/pets/';
        if (!empty($pet['image']) && file_exists($uploadDir . $pet['image'])) {
            unlink($uploadDir . $pet['image']);
        }
        $image = '';
    }

    if (empty($name) || empty($species) || $age <= 0 || $price <= 0) {
        $message = 'Please fill in all required fields correctly.';
    } else {
        // Ensure columns exist
        $result = $conn->query("SHOW COLUMNS FROM pets LIKE 'description'");
        if ($result->num_rows == 0) {
            $conn->query("ALTER TABLE pets ADD COLUMN description TEXT AFTER price");
        }

        $result = $conn->query("SHOW COLUMNS FROM pets LIKE 'status'");
        if ($result->num_rows == 0) {
            $conn->query("ALTER TABLE pets ADD COLUMN status ENUM('available', 'sold') DEFAULT 'available'");
        }

        $stmt = $conn->prepare("UPDATE store_pets SET name = ?, species = ?, age = ?, price = ?, description = ?, status = ?, image = ? WHERE id = ?");
        $stmt->bind_param('ssidsssi', $name, $species, $age, $price, $description, $status, $image, $petId);

        if ($stmt->execute()) {
            header('Location: pets.php?message=Pet updated successfully');
            exit();
        } else {
            $message = 'Error updating pet: ' . $conn->error;
        }
    }
}
?>

<main class="admin-main">
    <h2>Edit Pet</h2>

    <?php if ($message): ?>
        <div style="background: #f8d7da; color: #721c24; padding: 1rem; border-radius: 4px; margin-bottom: 1rem;">
            <?php echo htmlspecialchars($message); ?>
        </div>
    <?php endif; ?>

    <div style="background: #fff; padding: 2rem; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1);">
        <form method="post" enctype="multipart/form-data">
            <div class="form-group">
                <label for="name">Pet Name *</label>
                <input type="text" id="name" name="name" value="<?php echo htmlspecialchars($pet['name']); ?>" required>
            </div>

            <div class="form-group">
                <label for="species">Species *</label>
                <select id="species" name="species" required>
                    <option value="">Select Species</option>
                    <option value="dog" <?php echo $pet['species'] === 'dog' ? 'selected' : ''; ?>>Dog</option>
                    <option value="cat" <?php echo $pet['species'] === 'cat' ? 'selected' : ''; ?>>Cat</option>
                    <option value="rabbit" <?php echo $pet['species'] === 'rabbit' ? 'selected' : ''; ?>>Rabbit</option>
                    <option value="bird" <?php echo $pet['species'] === 'bird' ? 'selected' : ''; ?>>Bird</option>
                    <option value="hamster" <?php echo $pet['species'] === 'hamster' ? 'selected' : ''; ?>>Hamster</option>
                    <option value="fish" <?php echo $pet['species'] === 'fish' ? 'selected' : ''; ?>>Fish</option>
                    <option value="other" <?php echo $pet['species'] === 'other' ? 'selected' : ''; ?>>Other</option>
                </select>
            </div>

            <div class="form-group">
                <label for="age">Age (years) *</label>
                <input type="number" id="age" name="age" value="<?php echo $pet['age']; ?>" min="0" max="30" required>
            </div>

            <div class="form-group">
                <label for="price">Price (₱) *</label>
                <input type="number" id="price" name="price" value="<?php echo $pet['price']; ?>" step="0.01" min="0" required>
            </div>

            <div class="form-group">
                <label for="description">Description/Bio</label>
                <textarea id="description" name="description" rows="4"><?php echo htmlspecialchars($pet['description'] ?? ''); ?></textarea>
            </div>

            <div class="form-group">
                <label for="status">Status</label>
                <select id="status" name="status">
                    <option value="available" <?php echo ($pet['status'] ?? 'available') === 'available' ? 'selected' : ''; ?>>Available</option>
                    <option value="sold" <?php echo ($pet['status'] ?? 'available') === 'sold' ? 'selected' : ''; ?>>Sold</option>
                </select>
            </div>

            <div class="form-group">
                <label>Current Photo</label>
                <div>
                    <?php if (!empty($pet['image'])): ?>
                        <img src="../assets/uploads/pets/<?php echo htmlspecialchars($pet['image']); ?>" width="100" height="100" style="object-fit: cover; border-radius: 4px; border: 1px solid #ddd;">
                        <br>
                        <label><input type="checkbox" name="remove_image" value="1"> Remove current photo</label>
                    <?php else: ?>
                        No photo uploaded
                    <?php endif; ?>
                </div>
            </div>

            <div class="form-group">
                <label for="image">Upload New Photo</label>
                <input type="file" id="image" name="image" accept="image/*">
                <small>Leave empty to keep current photo (or check "Remove" above)</small>
            </div>

            <div style="margin-top: 2rem;">
                <button type="submit" class="btn btn-success">Update Pet</button>
                <a href="pets.php" class="btn" style="margin-left: 1rem;">Cancel</a>
            </div>
        </form>
    </div>
</main>

<?php require_once '../includes/footer.php'; ?>