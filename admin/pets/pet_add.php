<?php
require_once '../includes/header.php';
require_once '../includes/sidebar.php';

$message = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $species = trim($_POST['species'] ?? '');
    $age = (int)($_POST['age'] ?? 0);
    $price = (float)($_POST['price'] ?? 0);
    $description = trim($_POST['description'] ?? '');
    $status = $_POST['status'] ?? 'available';

    // Handle file upload
    $image = '';
    if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
        $uploadDir = '../assets/uploads/pets/';
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }

        $fileName = uniqid() . '_' . basename($_FILES['image']['name']);
        $uploadFile = $uploadDir . $fileName;

        if (move_uploaded_file($_FILES['image']['tmp_name'], $uploadFile)) {
            $image = $fileName;
        }
    }

    if (empty($name) || empty($species) || $age <= 0 || $price <= 0) {
        $message = 'Please fill in all required fields correctly.';
    } else {
        // Check if description column exists, add if not
        $result = $conn->query("SHOW COLUMNS FROM pets LIKE 'description'");
        if ($result->num_rows == 0) {
            $conn->query("ALTER TABLE pets ADD COLUMN description TEXT AFTER price");
        }

        $result = $conn->query("SHOW COLUMNS FROM pets LIKE 'status'");
        if ($result->num_rows == 0) {
            $conn->query("ALTER TABLE pets ADD COLUMN status ENUM('available', 'sold') DEFAULT 'available'");
        }

        $stmt = $conn->prepare("INSERT INTO store_pets (name, species, age, price, description, status, image) VALUES (?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param('ssidsss', $name, $species, $age, $price, $description, $status, $image);

        if ($stmt->execute()) {
            header('Location: pets.php?message=Pet added successfully');
            exit();
        } else {
            $message = 'Error adding pet: ' . $conn->error;
        }
    }
}
?>

<main class="admin-main">
    <h2>Add New Pet</h2>

    <?php if ($message): ?>
        <div style="background: #f8d7da; color: #721c24; padding: 1rem; border-radius: 4px; margin-bottom: 1rem;">
            <?php echo htmlspecialchars($message); ?>
        </div>
    <?php endif; ?>

    <div style="background: #fff; padding: 2rem; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1);">
        <form method="post" enctype="multipart/form-data">
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
                    <option value="fish">Fish</option>
                    <option value="other">Other</option>
                </select>
            </div>

            <div class="form-group">
                <label for="age">Age (years) *</label>
                <input type="number" id="age" name="age" min="0" max="30" required>
            </div>

            <div class="form-group">
                <label for="price">Price (₱) *</label>
                <input type="number" id="price" name="price" step="0.01" min="0" required>
            </div>

            <div class="form-group">
                <label for="description">Description/Bio</label>
                <textarea id="description" name="description" rows="4"></textarea>
            </div>

            <div class="form-group">
                <label for="status">Status</label>
                <select id="status" name="status">
                    <option value="available">Available</option>
                    <option value="sold">Sold</option>
                </select>
            </div>

            <div class="form-group">
                <label for="image">Photo</label>
                <input type="file" id="image" name="image" accept="image/*">
                <small>Leave empty to add photo later</small>
            </div>

            <div style="margin-top: 2rem;">
                <button type="submit" class="btn btn-success">Add Pet</button>
                <a href="pets.php" class="btn" style="margin-left: 1rem;">Cancel</a>
            </div>
        </form>
    </div>
</main>

<?php require_once '../includes/footer.php'; ?>