<?php
session_name('petstore_session');
session_start();

// Check if user is logged in as admin
if (!isset($_SESSION['is_admin']) || $_SESSION['is_admin'] !== true) {
    header('Location: ' . url('login?error=Access denied'));
    exit;
}

require_once __DIR__ . '/../../backend/config/database.php';
require_once __DIR__ . '/../../backend/functions/helpers.php';

$customerId = isset($_GET['customer_id']) ? (int)$_GET['customer_id'] : 0;

if (!$customerId) {
    header('Location: customers.php');
    exit();
}

// Get customer details
$stmt = $conn->prepare("SELECT first_name, last_name, email FROM customers WHERE id = ?");
$stmt->bind_param('i', $customerId);
$stmt->execute();
$customer = $stmt->get_result()->fetch_assoc();

if (!$customer) {
    header('Location: customers.php');
    exit();
}

// Handle search
$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$limit = 20;
$offset = ($page - 1) * $limit;

// Build query for customer's pets
$query = "SELECT cp.* FROM customer_pets cp WHERE cp.customer_id = ?";
$params = [$customerId];
$types = 'i';

if (!empty($search)) {
    $query .= " AND (cp.name LIKE ? OR cp.species LIKE ? OR cp.breed LIKE ?)";
    $searchTerm = "%$search%";
    $params = array_merge($params, [$searchTerm, $searchTerm, $searchTerm]);
    $types .= 'sss';
}

$query .= " ORDER BY cp.name ASC LIMIT ? OFFSET ?";
$params[] = $limit;
$params[] = $offset;
$types .= 'ii';

$stmt = $conn->prepare($query);
$stmt->bind_param($types, ...$params);
$stmt->execute();
$pets = $stmt->get_result();

// Get total count
$countQuery = "SELECT COUNT(*) as total FROM customer_pets WHERE customer_id = ?";
$countParams = [$customerId];
$countTypes = 'i';

if (!empty($search)) {
    $countQuery .= " AND (name LIKE ? OR species LIKE ? OR breed LIKE ?)";
    $searchTerm = "%$search%";
    $countParams = array_merge($countParams, [$searchTerm, $searchTerm, $searchTerm]);
    $countTypes .= 'sss';
}

$countStmt = $conn->prepare($countQuery);
$countStmt->bind_param($countTypes, ...$countParams);
$countStmt->execute();
$totalPets = $countStmt->get_result()->fetch_assoc()['total'];
$totalPages = ceil($totalPets / $limit);

$page_title = 'Customer Pets - ' . $customer['first_name'] . ' ' . $customer['last_name'];
require_once __DIR__ . '/../includes/header.php';

echo '<link rel="stylesheet" href="/Ria-Pet-Store/admin/css/customers.css?v=' . time() . '">';
?>

<div class="admin-dashboard">
    <div class="header-actions">
        <a href="customer_details.php?id=<?php echo $customerId; ?>" class="btn btn-outline">
            <?php echo icon('arrow-left', 16); ?> Back to Customer
        </a>
        <div class="action-buttons-group">
            <a href="../customer_pets/add_customer_pet.php?customer_id=<?php echo $customerId; ?>" class="btn btn-success">
                <?php echo icon('plus', 16); ?> Add Pet
            </a>
        </div>
    </div>

    <!-- Pets Table -->
    <div class="table-container">
        <?php if ($pets->num_rows > 0): ?>
            <table class="admin-table clickable-rows">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Pet Name</th>
                        <th>Species</th>
                        <th>Breed</th>
                        <th>Age</th>
                        <th>Gender</th>
                        <th>Color</th>
                        <th>Weight</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($pet = $pets->fetch_assoc()): ?>
                    <tr class="clickable-row" data-href="../customer_pets/customer_pet_details.php?id=<?php echo $pet['id']; ?>">
                        <td>#<?php echo $pet['id']; ?></td>
                        <td class="pet-name"><?php echo htmlspecialchars($pet['name']); ?></td>
                        <td><?php echo htmlspecialchars(ucfirst($pet['species'])); ?></td>
                        <td><?php echo htmlspecialchars($pet['breed'] ?? '—'); ?></td>
                        <td><?php echo $pet['age'] ? $pet['age'] . ' yrs' : '—'; ?></td>
                        <td><?php echo $pet['gender'] ? ucfirst($pet['gender']) : '—'; ?></td>
                        <td><?php echo htmlspecialchars($pet['color'] ?? '—'); ?></td>
                        <td><?php echo $pet['weight'] ? $pet['weight'] . ' ' . ($pet['weight_unit'] ?? 'kg') : '—'; ?></td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>

            <!-- Pagination -->
            <?php if ($totalPages > 1): ?>
                <div class="pagination">
                    <?php
                    $queryParams = array_filter([
                        'customer_id' => $customerId,
                        'search' => $search,
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
            <div class="no-data">
                <p>No pets found for this customer. <?php echo icon('paw', 20); ?></p>
                <a href="../customer_pets/add_customer_pet.php?customer_id=<?php echo $customerId; ?>" class="btn btn-outline btn-small">
                    <?php echo icon('plus', 14); ?> Add First Pet
                </a>
            </div>
        <?php endif; ?>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const rows = document.querySelectorAll('.clickable-row');
    rows.forEach(row => {
        row.addEventListener('click', function() {
            window.location.href = this.dataset.href;
        });
        row.style.cursor = 'pointer';
    });
});
</script>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>