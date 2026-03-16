<?php
require_once '../includes/header.php';
require_once '../includes/sidebar.php';

// Handle search and pagination
$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$species = isset($_GET['species']) ? trim($_GET['species']) : 'all';
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$limit = 20;
$offset = ($page - 1) * $limit;

// Build query
$query = "SELECT * FROM pets WHERE 1=1";
$params = [];
$types = '';

if (!empty($search)) {
    $query .= " AND (name LIKE ? OR species LIKE ?)";
    $params[] = "%$search%";
    $params[] = "%$search%";
    $types .= 'ss';
}

if (!empty($species) && $species !== 'all') {
    $query .= " AND species = ?";
    $params[] = $species;
    $types .= 's';
}

$query .= " ORDER BY id DESC LIMIT ? OFFSET ?";
$params[] = $limit;
$params[] = $offset;
$types .= 'ii';

// Get pets
$stmt = $conn->prepare($query);
if (!empty($params)) {
    $stmt->bind_param($types, ...$params);
}
$stmt->execute();
$pets = $stmt->get_result();

// Get total count for pagination
$countQuery = "SELECT COUNT(*) as total FROM pets WHERE 1=1";
$countParams = [];
$countTypes = '';

if (!empty($search)) {
    $countQuery .= " AND (name LIKE ? OR species LIKE ?)";
    $countParams[] = "%$search%";
    $countParams[] = "%$search%";
    $countTypes .= 'ss';
}

if (!empty($species) && $species !== 'all') {
    $countQuery .= " AND species = ?";
    $countParams[] = $species;
    $countTypes .= 's';
}

$countStmt = $conn->prepare($countQuery);
if (!empty($countParams)) {
    $countStmt->bind_param($countTypes, ...$countParams);
}
$countStmt->execute();
$totalPets = $countStmt->get_result()->fetch_assoc()['total'];
$totalPages = ceil($totalPets / $limit);

// Get unique species for filter
$speciesList = $conn->query("SELECT DISTINCT species FROM pets ORDER BY species");
?>

<main class="admin-main">
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 2rem;">
        <h2>Manage Pets</h2>
        <a href="pet_add.php" class="btn btn-success">Add New Pet</a>
    </div>

    <!-- Filters -->
    <div style="background: #fff; padding: 1.5rem; border-radius: 8px; margin-bottom: 2rem; box-shadow: 0 2px 4px rgba(0,0,0,0.1);">
        <form method="get" style="display: flex; gap: 1rem; flex-wrap: wrap; align-items: center;">
            <div class="form-group" style="margin: 0;">
                <label for="search" style="display: block; margin-bottom: 0.5rem;">Search:</label>
                <input type="text" id="search" name="search" value="<?php echo htmlspecialchars($search); ?>" placeholder="Pet name or species">
            </div>
            <div class="form-group" style="margin: 0;">
                <label for="species" style="display: block; margin-bottom: 0.5rem;">Species:</label>
                <select name="species" id="species">
                    <option value="all" <?php echo $species === 'all' ? 'selected' : ''; ?>>All Species</option>
                    <?php while ($spec = $speciesList->fetch_assoc()): ?>
                        <option value="<?php echo htmlspecialchars($spec['species']); ?>" <?php echo $species === $spec['species'] ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars(ucfirst($spec['species'])); ?>
                        </option>
                    <?php endwhile; ?>
                </select>
            </div>
            <div style="align-self: flex-end;">
                <button type="submit" class="btn">Filter</button>
                <?php if ($search || $species !== 'all'): ?>
                    <a href="pets.php" class="btn btn-warning" style="margin-left: 0.5rem;">Clear</a>
                <?php endif; ?>
            </div>
        </form>
    </div>

    <!-- Pets Table -->
    <div style="background: #fff; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); overflow: hidden;">
        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Photo</th>
                    <th>Name</th>
                    <th>Species</th>
                    <th>Age</th>
                    <th>Price</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($pets->num_rows > 0): ?>
                    <?php while ($pet = $pets->fetch_assoc()): ?>
                        <tr>
                            <td><?php echo $pet['id']; ?></td>
                            <td>
                                <?php if (!empty($pet['image'])): ?>
                                    <img src="../assets/uploads/pets/<?php echo htmlspecialchars($pet['image']); ?>" width="50" height="50" style="object-fit: cover; border-radius: 4px;">
                                <?php else: ?>
                                    No photo
                                <?php endif; ?>
                            </td>
                            <td><?php echo htmlspecialchars($pet['name']); ?></td>
                            <td><?php echo htmlspecialchars($pet['species']); ?></td>
                            <td><?php echo $pet['age']; ?> years</td>
                            <td>₱<?php echo number_format($pet['price'], 2); ?></td>
                            <td>
                                <span class="status-badge status-<?php echo $pet['status'] ?? 'available'; ?>">
                                    <?php echo ucfirst($pet['status'] ?? 'available'); ?>
                                </span>
                            </td>
                            <td>
                                <a href="pet_details.php?id=<?php echo $pet['id']; ?>" class="btn btn-small">View</a>
                                <a href="pet_edit.php?id=<?php echo $pet['id']; ?>" class="btn btn-small btn-warning">Edit</a>
                                <a href="pet_delete.php?id=<?php echo $pet['id']; ?>" class="btn btn-small btn-danger" onclick="return confirm('Are you sure you want to delete this pet?')">Delete</a>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="8" style="text-align: center; padding: 2rem;">No pets found.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

    <!-- Pagination -->
    <?php if ($totalPages > 1): ?>
        <div style="margin-top: 2rem; text-align: center;">
            <?php
            $queryString = http_build_query(array_filter([
                'search' => $search,
                'species' => $species !== 'all' ? $species : null,
                'page' => null // Will be set in loop
            ]));

            for ($i = 1; $i <= $totalPages; $i++):
                $active = $i === $page ? ' style="font-weight: bold; color: #007bff;"' : '';
                $url = "pets.php?" . $queryString . "&page=$i";
            ?>
                <a href="<?php echo $url; ?>"<?php echo $active; ?> style="margin: 0 0.25rem;"><?php echo $i; ?></a>
            <?php endfor; ?>
        </div>
    <?php endif; ?>
</main>

<?php require_once '../includes/footer.php'; ?>