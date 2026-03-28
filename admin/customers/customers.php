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

$page_title = 'Customers';
require_once __DIR__ . '/../includes/header.php';

// Handle filters
$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$limit = 20;
$offset = ($page - 1) * $limit;

// Build query
$query = "SELECT c.*, 
          COUNT(o.id) as total_orders,
          COALESCE(SUM(o.total_amount), 0) as total_spent,
          MAX(o.created_at) as last_order_date
          FROM customers c
          LEFT JOIN orders o ON c.id = o.customer_id
          WHERE 1=1";

$params = [];
$types = '';

if (!empty($search)) {
    $query .= " AND (c.first_name LIKE ? OR c.last_name LIKE ? OR c.email LIKE ? OR c.phone LIKE ?)";
    $searchTerm = "%$search%";
    $params = array_merge($params, [$searchTerm, $searchTerm, $searchTerm, $searchTerm]);
    $types .= 'ssss';
}

$query .= " GROUP BY c.id ORDER BY c.created_at DESC LIMIT ? OFFSET ?";
$params[] = $limit;
$params[] = $offset;
$types .= 'ii';

// Get customers
$stmt = $conn->prepare($query);
if (!empty($params)) {
    $stmt->bind_param($types, ...$params);
}
$stmt->execute();
$customers = $stmt->get_result();

// Get total count for pagination
$countQuery = "SELECT COUNT(*) as total FROM customers c WHERE 1=1";
$countParams = array_slice($params, 0, -2);
$countTypes = substr($types, 0, -2);

if (!empty($search)) {
    $countQuery .= " AND (c.first_name LIKE ? OR c.last_name LIKE ? OR c.email LIKE ? OR c.phone LIKE ?)";
}

$stmt = $conn->prepare($countQuery);
if (!empty($countParams)) {
    $stmt->bind_param($countTypes, ...$countParams);
}
$stmt->execute();
$totalCustomers = $stmt->get_result()->fetch_assoc()['total'];
$totalPages = ceil($totalCustomers / $limit);

// Admin CSS
echo '<link rel="stylesheet" href="/Ria-Pet-Store/admin/css/customers.css?v=' . time() . '">';
?>

<div class="admin-dashboard">
    <!-- Search Bar with Add Button -->
    <div class="search-bar">
        <form method="get">
            <input type="text" name="search" placeholder="Search by name, email, or phone..." value="<?php echo htmlspecialchars($search); ?>">
            <button type="submit" class="btn btn-primary"><?php echo icon('search', 16); ?> Search</button>
            <?php if ($search): ?>
                <a href="customers.php" class="btn btn-outline">Clear</a>
            <?php endif; ?>
        </form>
        <a href="customer_add.php" class="btn btn-primary"><?php echo icon('plus', 16); ?> Add Customer</a>
    </div>

    <!-- Customers Table -->
    <div class="table-container">
        <?php if ($customers->num_rows > 0): ?>
            <table class="admin-table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Name</th>
                        <th>Email</th>
                        <th>Phone</th>
                        <th>Orders</th>
                        <th>Total Spent</th>
                        <th>Joined</th>
                     </tr>
                </thead>
                <tbody>
                    <?php while ($customer = $customers->fetch_assoc()): ?>
                     <tr>
                        <td>#<?php echo $customer['id']; ?></td>
                        <td>
                            <a href="customer_details.php?id=<?php echo $customer['id']; ?>" class="customer-link">
                                <?php echo htmlspecialchars($customer['first_name'] . ' ' . $customer['last_name']); ?>
                            </a>
                        </td>
                        <td><?php echo htmlspecialchars($customer['email']); ?></td>
                        <td><?php echo htmlspecialchars($customer['phone'] ?? '—'); ?></td>
                        <td><?php echo $customer['total_orders']; ?></td>
                        <td>₱<?php echo number_format($customer['total_spent'], 2); ?></td>
                        <td><?php echo date('M d, Y', strtotime($customer['created_at'])); ?></td>
                     </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>

            <!-- Pagination -->
            <?php if ($totalPages > 1): ?>
                <div class="pagination">
                    <?php
                    $queryString = http_build_query(array_filter(['search' => $search]));
                    for ($i = 1; $i <= $totalPages; $i++):
                        $active = $i === $page ? 'active' : '';
                        $url = "customers.php?" . ($queryString ? $queryString . '&' : '') . "page=$i";
                    ?>
                        <a href="<?php echo $url; ?>" class="pagination-link <?php echo $active; ?>"><?php echo $i; ?></a>
                    <?php endfor; ?>
                </div>
            <?php endif; ?>
        <?php else: ?>
            <div class="no-data">
                <p>No customers found.</p>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>