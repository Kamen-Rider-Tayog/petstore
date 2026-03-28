<?php
// Customer pets listing - included in pets.php
$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$species = isset($_GET['species']) ? trim($_GET['species']) : 'all';
$status_filter = isset($_GET['status_filter']) ? trim($_GET['status_filter']) : 'all';
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$limit = 20;
$offset = ($page - 1) * $limit;

// Build query
$query = "SELECT cp.*, c.first_name, c.last_name, c.email 
          FROM customer_pets cp
          LEFT JOIN customers c ON cp.customer_id = c.id
          WHERE 1=1";
$params = [];
$types = '';

if (!empty($search)) {
    $query .= " AND (cp.name LIKE ? OR cp.species LIKE ? OR c.first_name LIKE ? OR c.last_name LIKE ?)";
    $params[] = "%$search%";
    $params[] = "%$search%";
    $params[] = "%$search%";
    $params[] = "%$search%";
    $types .= 'ssss';
}

if (!empty($species) && $species !== 'all') {
    $query .= " AND cp.species = ?";
    $params[] = $species;
    $types .= 's';
}

// Status filter
if (!empty($status_filter) && $status_filter !== 'all') {
    $query .= " AND cp.is_active = ?";
    $params[] = $status_filter === 'active' ? 1 : 0;
    $types .= 'i';
}

$query .= " ORDER BY cp.id DESC LIMIT ? OFFSET ?";
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
$countQuery = "SELECT COUNT(*) as total FROM customer_pets cp LEFT JOIN customers c ON cp.customer_id = c.id WHERE 1=1";
$countParams = [];
$countTypes = '';

if (!empty($search)) {
    $countQuery .= " AND (cp.name LIKE ? OR cp.species LIKE ? OR c.first_name LIKE ? OR c.last_name LIKE ?)";
    $countParams[] = "%$search%";
    $countParams[] = "%$search%";
    $countParams[] = "%$search%";
    $countParams[] = "%$search%";
    $countTypes .= 'ssss';
}

if (!empty($species) && $species !== 'all') {
    $countQuery .= " AND cp.species = ?";
    $countParams[] = $species;
    $countTypes .= 's';
}

if (!empty($status_filter) && $status_filter !== 'all') {
    $countQuery .= " AND cp.is_active = ?";
    $countParams[] = $status_filter === 'active' ? 1 : 0;
    $countTypes .= 'i';
}

$countStmt = $conn->prepare($countQuery);
if (!empty($countParams)) {
    $countStmt->bind_param($countTypes, ...$countParams);
}
$countStmt->execute();
$totalPets = $countStmt->get_result()->fetch_assoc()['total'];
$totalPages = ceil($totalPets / $limit);

// Get unique species for filter
$speciesList = $conn->query("SELECT DISTINCT species FROM customer_pets ORDER BY species");

// Get counts for status
$activeCount = $conn->query("SELECT COUNT(*) as count FROM customer_pets WHERE is_active = 1")->fetch_assoc()['count'];
$inactiveCount = $conn->query("SELECT COUNT(*) as count FROM customer_pets WHERE is_active = 0")->fetch_assoc()['count'];
$totalCount = $conn->query("SELECT COUNT(*) as count FROM customer_pets")->fetch_assoc()['count'];
?>

<div class="customer-pets-section">
    <!-- Search Bar with Add Button -->
    <div class="search-bar">
        <form method="get" action="">
            <input type="hidden" name="tab" value="customer">
            <input type="text" name="search" placeholder="Search by pet name, species, or owner..." value="<?php echo htmlspecialchars($search); ?>">
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
                <a href="?tab=customer" class="btn btn-outline"><?php echo icon('x', 16); ?> Clear</a>
            <?php endif; ?>
        </form>
        <a href="add_customer_pet.php" class="btn btn-success"><?php echo icon('plus', 16); ?> Add Pet</a>
    </div>

    <!-- Status Legend -->
    <div class="status-legend">
        <span class="legend-item"><span class="status-dot active"></span> Active</span>
        <span class="legend-item"><span class="status-dot inactive"></span> Inactive</span>
    </div>

    <!-- Pets Table -->
    <div class="table-container">
        <?php if ($pets->num_rows > 0): ?>
            <table class="admin-table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Photo</th>
                        <th>Pet Name</th>
                        <th>Species</th>
                        <th>Age</th>
                        <th>Owner</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($pet = $pets->fetch_assoc()): ?>
                        <tr>
                            <td>#<?php echo $pet['id']; ?></td>
                            <td>
                                <?php if (!empty($pet['pet_image'])): ?>
                                    <img src="/Ria-Pet-Store/assets/images/customer_pets/<?php echo htmlspecialchars($pet['pet_image']); ?>" class="pet-photo">
                                <?php else: ?>
                                    <span class="no-photo">No photo</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <a href="customer_pet_details.php?id=<?php echo $pet['id']; ?>" class="pet-link">
                                    <?php echo htmlspecialchars($pet['name']); ?>
                                </a>
                            </td>
                            <td><?php echo ucfirst(htmlspecialchars($pet['species'])); ?></td>
                            <td><?php echo $pet['age']; ?> <?php echo $pet['age'] == 1 ? 'year' : 'years'; ?></td>
                            <td>
                                <a href="../customers/customer_details.php?id=<?php echo $pet['customer_id']; ?>" class="customer-link">
                                    <?php echo htmlspecialchars($pet['first_name'] . ' ' . $pet['last_name']); ?>
                                </a>
                            </td>
                            <td>
                                <div class="status-indicators">
                                    <span class="status-dot <?php echo $pet['is_active'] ? 'active' : 'inactive'; ?>" title="<?php echo $pet['is_active'] ? 'Active' : 'Inactive'; ?>"></span>
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
                        'tab' => 'customer',
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
            <div class="no-data">No customer pets found. <?php echo icon('paw', 20); ?></div>
        <?php endif; ?>
    </div>
</div>