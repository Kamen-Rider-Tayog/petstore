<?php
// Customer pets listing - included in pets.php
$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$species = isset($_GET['species']) ? trim($_GET['species']) : 'all';
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

$countStmt = $conn->prepare($countQuery);
if (!empty($countParams)) {
    $countStmt->bind_param($countTypes, ...$countParams);
}
$countStmt->execute();
$totalPets = $countStmt->get_result()->fetch_assoc()['total'];
$totalPages = ceil($totalPets / $limit);

// Get unique species for filter
$speciesList = $conn->query("SELECT DISTINCT species FROM customer_pets ORDER BY species");
?>

<div class="customer-pets-section">
    <div class="search-bar">
        <form method="get">
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
            <?php if ($search || $species !== 'all'): ?>
                <a href="?tab=customer" class="btn btn-outline">Clear</a>
            <?php endif; ?>
        </form>
        <a href="add_customer_pet.php" class="btn btn-primary"><?php echo icon('plus', 16); ?> Add Pet</a>
    </div>

    <div class="table-container">
        <?php if ($pets->num_rows > 0): ?>
            <table class="admin-table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Pet Name</th>
                        <th>Species</th>
                        <th>Age</th>
                        <th>Owner</th>
                        <th>Status</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($pet = $pets->fetch_assoc()): ?>
                    <tr>
                        <td>#<?php echo $pet['id']; ?></td>
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
                            <span class="status-badge <?php echo $pet['is_active'] ? 'status-available' : 'status-sold'; ?>">
                                <?php echo $pet['is_active'] ? 'Active' : 'Inactive'; ?>
                            </span>
                        </td>
                        <td class="actions">
                            <a href="customer_pet_details.php?id=<?php echo $pet['id']; ?>" class="btn-icon"><?php echo icon('eye', 14); ?></a>
                            <a href="edit_customer_pet.php?id=<?php echo $pet['id']; ?>" class="btn-icon"><?php echo icon('edit', 14); ?></a>
                            <a href="delete_customer_pet.php?id=<?php echo $pet['id']; ?>" class="btn-icon" onclick="return confirm('Delete this pet?')"><?php echo icon('x', 14); ?></a>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>

            <?php if ($totalPages > 1): ?>
                <div class="pagination">
                    <?php
                    $queryString = http_build_query(array_filter([
                        'search' => $search,
                        'species' => $species !== 'all' ? $species : null,
                        'tab' => 'customer'
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
            <div class="no-data">No customer pets found.</div>
        <?php endif; ?>
    </div>
</div>