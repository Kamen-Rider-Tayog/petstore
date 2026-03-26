<?php
// Store pets listing - included in pets.php
$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$species = isset($_GET['species']) ? trim($_GET['species']) : 'all';
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$limit = 20;
$offset = ($page - 1) * $limit;

// Build query - removed price
$query = "SELECT * FROM store_pets WHERE 1=1";
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

$stmt = $conn->prepare($query);
if (!empty($params)) {
    $stmt->bind_param($types, ...$params);
}
$stmt->execute();
$pets = $stmt->get_result();

// Get total count
$countQuery = "SELECT COUNT(*) as total FROM store_pets WHERE 1=1";
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
$speciesList = $conn->query("SELECT DISTINCT species FROM store_pets ORDER BY species");
?>

<div class="store-pets-section">
    <!-- Search Bar with Add Button -->
    <div class="search-bar">
        <form method="get">
            <input type="hidden" name="tab" value="store">
            <input type="text" name="search" placeholder="Search by name or species..." value="<?php echo htmlspecialchars($search); ?>">
            <select name="species">
                <option value="all" <?php echo $species === 'all' ? 'selected' : ''; ?>>All Species</option>
                <?php while ($spec = $speciesList->fetch_assoc()): ?>
                    <option value="<?php echo htmlspecialchars($spec['species']); ?>" <?php echo $species === $spec['species'] ? 'selected' : ''; ?>>
                        <?php echo ucfirst(htmlspecialchars($spec['species'])); ?>
                    </option>
                <?php endwhile; ?>
            </select>
            <button type="submit" class="btn btn-primary"><?php echo icon('search', 16); ?> Filter</button>
            <?php if ($search || $species !== 'all'): ?>
                <a href="?tab=store" class="btn btn-outline">Clear</a>
            <?php endif; ?>
        </form>
        <a href="add_store_pet.php" class="btn btn-primary"><?php echo icon('plus', 16); ?> Add Pet</a>
    </div>

    <!-- Pets Table -->
    <div class="table-container">
        <?php if ($pets->num_rows > 0): ?>
            <table class="admin-table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Photo</th>
                        <th>Name</th>
                        <th>Species</th>
                        <th>Age</th>
                        <th>Status</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($pet = $pets->fetch_assoc()): ?>
                    <tr>
                        <td>#<?php echo $pet['id']; ?></td>
                        <td>
                            <?php if (!empty($pet['pet_image'])): ?>
                                <img src="/Ria-Pet-Store/assets/images/pets/<?php echo htmlspecialchars($pet['pet_image']); ?>" class="pet-photo">
                            <?php else: ?>
                                <span class="no-photo">No photo</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <a href="store_pet_details.php?id=<?php echo $pet['id']; ?>" class="pet-link">
                                <?php echo htmlspecialchars($pet['name']); ?>
                            </a>
                        </td>
                        <td><?php echo ucfirst(htmlspecialchars($pet['species'])); ?></td>
                        <td><?php echo $pet['age']; ?> <?php echo $pet['age'] == 1 ? 'year' : 'years'; ?></td>
                        <td>
                            <span class="status-badge status-<?php echo $pet['pet_status'] ?? 'available'; ?>">
                                <?php echo ucfirst($pet['pet_status'] ?? 'Available'); ?>
                            </span>
                            <?php if ($pet['featured']): ?>
                                <span class="featured-badge"><?php echo icon('star', 12); ?> Featured</span>
                            <?php endif; ?>
                        </td>
                        <td class="actions">
                            <a href="store_pet_details.php?id=<?php echo $pet['id']; ?>" class="btn-icon"><?php echo icon('eye', 14); ?></a>
                            <a href="edit_store_pet.php?id=<?php echo $pet['id']; ?>" class="btn-icon"><?php echo icon('edit', 14); ?></a>
                            <a href="delete_store_pet.php?id=<?php echo $pet['id']; ?>" class="btn-icon" onclick="return confirm('Delete this pet?')"><?php echo icon('x', 14); ?></a>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>

            <!-- Pagination -->
            <?php if ($totalPages > 1): ?>
                <div class="pagination">
                    <?php
                    $queryString = http_build_query(array_filter([
                        'search' => $search,
                        'species' => $species !== 'all' ? $species : null,
                        'tab' => 'store'
                    ]));
                    for ($i = 1; $i <= $totalPages; $i++):
                        $active = $i === $page ? 'active' : '';
                        $url = "pets.php?" . $queryString . "&page=$i";
                    ?>
                        <a href="<?php echo $url; ?>" class="pagination-link <?php echo $active; ?>"><?php echo $i; ?></a>
                    <?php endfor; ?>
                </div>
            <?php endif; ?>
        <?php else: ?>
            <div class="no-data">No pets available for adoption.</div>
        <?php endif; ?>
    </div>
</div>