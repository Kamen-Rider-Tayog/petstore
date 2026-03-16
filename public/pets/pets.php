<?php
require_once '../../backend/config/database.php';

$selectedSpecies = isset($_GET['species']) ? trim($_GET['species']) : 'all';

// Load available species for dropdown
$speciesResult = $conn->query("SELECT DISTINCT species FROM pets ORDER BY species");

// Load pets list (will also be updated via AJAX)
$petsql = "SELECT * FROM pets";
$params = [];
$types = '';

if (!empty($selectedSpecies) && $selectedSpecies !== 'all') {
    $petsql .= " WHERE species = ?";
    $params[] = $selectedSpecies;
    $types .= 's';
}

$petsql .= " ORDER BY id ASC";
$stmt = $conn->prepare($petsql);
if (!empty($params)) {
    $stmt->bind_param($types, ...$params);
}
$stmt->execute();
$pets = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pets</title>
    <link rel="stylesheet" href="../../assets/css/pets.css">
</head>
<body>
    <h1>Our Pets</h1>

    <div class="filter-row">
        <label for="species">Filter by species:</label>
        <select id="species" onchange="loadPets()">
            <option value="all" <?php echo $selectedSpecies === 'all' ? 'selected' : ''; ?>>All species</option>
            <?php while ($row = $speciesResult->fetch_assoc()): ?>
                <option value="<?php echo htmlspecialchars($row['species']); ?>" <?php echo $selectedSpecies === $row['species'] ? 'selected' : ''; ?>>
                    <?php echo htmlspecialchars(ucfirst($row['species'])); ?>
                </option>
            <?php endwhile; ?>
        </select>

        <div id="loading" style="display: none;">Loading...</div>
    </div>

    <table>
        <thead>
            <tr>
                <th>Photo</th>
                <th>Name</th>
                <th>Species</th>
                <th>Age</th>
                <th>Price</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody id="petsTableBody">
            <?php if($pets->num_rows > 0) : ?>
                <?php while($row = $pets->fetch_assoc()) : ?>
                    <tr>
                        <td>
                            <?php if(!empty($row['image'])): ?>
                            <img src="../../assets/uploads/pets/<?php echo htmlspecialchars($row['image']); ?>" 
                                 width="50" height="50">
                            <?php else: ?>
                            No photo
                            <?php endif; ?>
                        </td>
                        <td><?php echo htmlspecialchars($row['name']); ?></td>
                        <td><?php echo htmlspecialchars($row['species']); ?></td>
                        <td><?php echo $row['age']; ?> year old</td>
                        <td>₱<?php echo $row['price']; ?></td>
                        <td>
                            <a href="pet_details?id=<?php echo $row['id']; ?>">View</a>
                            <?php if(isset($_SESSION['user_id'])): ?>
                            | <a href="edit_pet?id=<?php echo $row['id']; ?>">Edit</a>
                            | <a href="delete_pet?id=<?php echo $row['id']; ?>" 
                                 onclick="return confirm('Are you sure?')">Delete</a>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endwhile; ?>
            <?php else : ?>
                <tr><td colspan='6'>No pets available.</td></tr>
            <?php endif; ?>
        </tbody>
    </table>

    <br>
    <a href="index">Back to Home</a>

    <script>
    function buildRow(pet) {
        const hasImage = pet.image && pet.image.trim() !== '';
        const imageHtml = hasImage
            ? `<img src="../../assets/uploads/pets/${encodeURIComponent(pet.image)}" width="50" height="50">`
            : 'No photo';

        const actions = [
            `<a href="pet_details?id=${encodeURIComponent(pet.id)}">View</a>`
        ];
        <?php if(isset($_SESSION['user_id'])): ?>
        actions.push(`<a href="edit_pet?id=${encodeURIComponent(pet.id)}">Edit</a>`);
        actions.push(`<a href="delete_pet?id=${encodeURIComponent(pet.id)}" onclick="return confirm('Are you sure?')">Delete</a>`);
        <?php endif; ?>

        return `
            <tr>
                <td>${imageHtml}</td>
                <td>${pet.name}</td>
                <td>${pet.species}</td>
                <td>${pet.age} year old</td>
                <td>₱${parseFloat(pet.price).toFixed(2)}</td>
                <td>${actions.join(' | ')}</td>
            </tr>
        `;
    }

    async function loadPets() {
        const species = document.getElementById('species').value;
        const loadingEl = document.getElementById('loading');
        const tbody = document.getElementById('petsTableBody');

        loadingEl.style.display = 'inline';
        tbody.innerHTML = '';

        try {
            const res = await fetch('../../backend/api/search_pets.php?species=' + encodeURIComponent(species));
            const data = await res.json();

            if (!data.success) {
                tbody.innerHTML = '<tr><td colspan="6">Failed to load pets.</td></tr>';
                return;
            }

            if (data.count === 0) {
                tbody.innerHTML = '<tr><td colspan="6">No pets found.</td></tr>';
                return;
            }

            tbody.innerHTML = data.data.map(buildRow).join('');
        } catch (error) {
            tbody.innerHTML = `<tr><td colspan="6" style="color: red;">Error loading pets: ${error.message}</td></tr>`;
        } finally {
            loadingEl.style.display = 'none';
        }
    }

    document.addEventListener('DOMContentLoaded', () => {
        document.getElementById('species').addEventListener('change', loadPets);
    });
    </script>
</body>
</html>
