<?php
// Store pets listing - included in pets.php
$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$species = isset($_GET['species']) ? trim($_GET['species']) : 'all';
$status_filter = isset($_GET['status_filter']) ? trim($_GET['status_filter']) : 'all';
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$limit = 20;
$offset = ($page - 1) * $limit;

// Build query
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

// Status filter
if (!empty($status_filter) && $status_filter !== 'all') {
    $query .= " AND pet_status = ?";
    $params[] = $status_filter;
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

if (!empty($status_filter) && $status_filter !== 'all') {
    $countQuery .= " AND pet_status = ?";
    $countParams[] = $status_filter;
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

// Get counts for status
$availableCount = $conn->query("SELECT COUNT(*) as count FROM store_pets WHERE pet_status = 'available'")->fetch_assoc()['count'];
$reservedCount = $conn->query("SELECT COUNT(*) as count FROM store_pets WHERE pet_status = 'reserved'")->fetch_assoc()['count'];
$adoptedCount = $conn->query("SELECT COUNT(*) as count FROM store_pets WHERE pet_status = 'adopted'")->fetch_assoc()['count'];
$featuredCount = $conn->query("SELECT COUNT(*) as count FROM store_pets WHERE featured = 1")->fetch_assoc()['count'];
$totalCount = $conn->query("SELECT COUNT(*) as count FROM store_pets")->fetch_assoc()['count'];
?>

<div class="store-pets-section">
    <!-- Search Bar with Add Button -->
    <div class="search-bar">
        <form method="get" action="">
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
            <?php if ($search || $species !== 'all' || $status_filter !== 'all'): ?>
                <a href="?tab=store" class="btn btn-outline"><?php echo icon('x', 16); ?> Clear</a>
            <?php endif; ?>
        </form>
        <a href="add_store_pet.php" class="btn btn-success"><?php echo icon('plus', 16); ?> Add Pet</a>
    </div>

    <!-- Status Legend -->
    <div class="status-legend">
        <span class="legend-item"><span class="status-dot available"></span> Available</span>
        <span class="legend-item"><span class="status-dot reserved"></span> Reserved</span>
        <span class="legend-item"><span class="status-dot adopted"></span> Adopted</span>
        <span class="legend-item"><span class="status-dot featured"></span> Featured</span>
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
                    </tr>
                </thead>
                <tbody>
                    <?php while ($pet = $pets->fetch_assoc()): 
                        $isFeatured = !empty($pet['featured']) && $pet['featured'] == 1;
                    ?>
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
                                <div class="status-indicators">
                                    <span class="status-dot <?php echo $pet['pet_status'] ?? 'available'; ?>" title="<?php echo ucfirst($pet['pet_status'] ?? 'Available'); ?>"></span>
                                    <?php if ($isFeatured): ?>
                                        <span class="status-dot featured" title="Featured"></span>
                                    <?php endif; ?>
                                </div>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>

            <!-- Pagination -->
            <?php if ($totalPages > 1): ?>
                <div class="pagination">
                    <?php
                    $queryParams = array_filter([
                        'tab' => 'store',
                        'search' => $search,
                        'species' => $species !== 'all' ? $species : null,
                        'status_filter' => $status_filter !== 'all' ? $status_filter : null,
                        'page' => null
                    ]);
                    $queryString = http_build_query($queryParams);
                    
                    if ($page > 1) {
                        echo '<a href="?' . $queryString . '&page=' . ($page - 1) . '" class="pagination-link">&laquo; Prev</a>';
                    }
                    
                    $startPage = max(1, $page - 2);
                    $endPage = min($totalPages, $page + 2);
                    
                    if ($startPage > 1) {
                        echo '<a href="?' . $queryString . '&page=1" class="pagination-link">1</a>';
                        if ($startPage > 2) {
                            echo '<span class="pagination-dots">...</span>';
                        }
                    }
                    
                    for ($i = $startPage; $i <= $endPage; $i++):
                        $activeClass = $i === $page ? 'active' : '';
                    ?>
                        <a href="?<?php echo $queryString; ?>&page=<?php echo $i; ?>" class="pagination-link <?php echo $activeClass; ?>"><?php echo $i; ?></a>
                    <?php endfor;
                    
                    if ($endPage < $totalPages) {
                        if ($endPage < $totalPages - 1) {
                            echo '<span class="pagination-dots">...</span>';
                        }
                        echo '<a href="?' . $queryString . '&page=' . $totalPages . '" class="pagination-link">' . $totalPages . '</a>';
                    }
                    
                    if ($page < $totalPages) {
                        echo '<a href="?' . $queryString . '&page=' . ($page + 1) . '" class="pagination-link">Next &raquo;</a>';
                    }
                    ?>
                </div>
            <?php endif; ?>
        <?php else: ?>
            <div class="no-data">No pets available for adoption. <?php echo icon('paw', 20); ?></div>
        <?php endif; ?>
    </div>
</div>