<?php
require_once '../backend/config/database.php';
require_once '../backend/includes/auth.php';

// Fetch species for dropdown
$speciesResult = $conn->query("SELECT DISTINCT species FROM pets ORDER BY species");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add New Pet</title>
</head>
<body>
    <h1>Add New Pet</h1>

    <form method="POST" action="add_pet_process" enctype="multipart/form-data">
        <div>
            <label for="name">Name:</label><br>
            <input type="text" id="name" name="name" required>
        </div>
        
        <div>
            <label for="species">Species:</label><br>
            <select id="species" name="species" required>
                <option value="">Select Species</option>
                <?php while($species = $speciesResult->fetch_assoc()): ?>
                <option value="<?php echo htmlspecialchars($species['species']); ?>">
                    <?php echo htmlspecialchars($species['species']); ?>
                </option>
                <?php endwhile; ?>
                <option value="other">Other (specify below)</option>
            </select>
            <input type="text" id="other_species" name="other_species" placeholder="Enter species" style="display:none; margin-top:10px;">
        </div>
        
        <div>
            <label for="breed">Breed:</label><br>
            <input type="text" id="breed" name="breed">
        </div>
        
        <div>
            <label for="age">Age (years):</label><br>
            <input type="number" id="age" name="age" min="0" max="20" required>
        </div>
        
        <div>
            <label for="price">Price (₱):</label><br>
            <input type="number" id="price" name="price" min="0" step="0.01" required>
        </div>
        
        <div>
            <label for="pet_image">Pet Photo:</label><br>
            <input type="file" id="pet_image" name="pet_image" accept="image/*">
            <small>Max size: 2MB. Allowed: JPG, PNG, GIF</small>
        </div>
        
        <br>
        <button type="submit">Add Pet</button>
    </form>

    <br>
    <a href="pets">Cancel</a>

    <script>
    document.getElementById('species').addEventListener('change', function() {
        const otherInput = document.getElementById('other_species');
        if (this.value === 'other') {
            otherInput.style.display = 'block';
            otherInput.required = true;
        } else {
            otherInput.style.display = 'none';
            otherInput.required = false;
        }
    });
    </script>
</body>
</html>