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
$countParams = [];
$countTypes = '';

if (!empty($search)) {
    $countQuery .= " AND (c.first_name LIKE ? OR c.last_name LIKE ? OR c.email LIKE ? OR c.phone LIKE ?)";
    $searchTerm = "%$search%";
    $countParams = array_merge($countParams, [$searchTerm, $searchTerm, $searchTerm, $searchTerm]);
    $countTypes .= 'ssss';
}

$countStmt = $conn->prepare($countQuery);
if (!empty($countParams)) {
    $countStmt->bind_param($countTypes, ...$countParams);
}
$countStmt->execute();
$totalCustomers = $countStmt->get_result()->fetch_assoc()['total'];
$totalPages = ceil($totalCustomers / $limit);

// Get counts for stats
$totalCount = $conn->query("SELECT COUNT(*) as count FROM customers")->fetch_assoc()['count'];
$withOrdersCount = $conn->query("SELECT COUNT(DISTINCT customer_id) as count FROM orders")->fetch_assoc()['count'];

// Admin CSS
echo '<link rel="stylesheet" href="/Ria-Pet-Store/admin/css/customers.css?v=' . time() . '">';
?>

<div class="admin-dashboard">
    <!-- Search Bar -->
    <div class="search-bar">
        <form method="get" action="">
            <input type="hidden" name="filter" value="<?php echo isset($_GET['filter']) ? htmlspecialchars($_GET['filter']) : 'all'; ?>">
            <input type="text" name="search" placeholder="Search by name, email, or phone..." value="<?php echo htmlspecialchars($search); ?>">
            <button type="submit" class="btn btn-primary"><?php echo icon('search', 16); ?> Search</button>
            <?php if ($search): ?>
                <a href="?filter=<?php echo isset($_GET['filter']) ? htmlspecialchars($_GET['filter']) : 'all'; ?>" class="btn btn-outline"><?php echo icon('x', 16); ?> Clear</a>
            <?php endif; ?>
        </form>
        <a href="add_customer.php" class="btn btn-success"><?php echo icon('plus', 16); ?> Add Customer</a>
    </div>

    <!-- Customers Table -->
    <div class="table-container">
        <?php if ($customers->num_rows > 0): ?>
            <table class="admin-table clickable-rows">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Customer</th>
                        <th>Contact</th>
                        <th>Orders</th>
                        <th>Total Spent</th>
                        <th>Joined</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($customer = $customers->fetch_assoc()): ?>
                    <tr class="clickable-row" data-href="customer_details.php?id=<?php echo $customer['id']; ?>">
                        <td class="customer-id">#<?php echo $customer['id']; ?></td>
                        <td>
                            <div class="customer-info">
                                <span class="customer-name"><?php echo htmlspecialchars($customer['first_name'] . ' ' . $customer['last_name']); ?></span>
                                <span class="customer-email"><?php echo htmlspecialchars($customer['email']); ?></span>
                            </div>
                        </td>
                        <td>
                            <?php if (!empty($customer['phone'])): ?>
                                <span class="customer-phone"><?php echo htmlspecialchars($customer['phone']); ?></span>
                            <?php else: ?>
                                <span class="no-phone">—</span>
                            <?php endif; ?>
                        </td>
                        <td><?php echo $customer['total_orders']; ?></td>
                        <td class="total-spent">₱<?php echo number_format($customer['total_spent'], 2); ?></td>
                        <td><?php echo date('M d, Y', strtotime($customer['created_at'])); ?></td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>

            <!-- Pagination -->
            <?php if ($totalPages > 1): ?>
                <div class="pagination">
                    <?php
                    $queryParams = array_filter([
                        'search' => $search,
                        'filter' => isset($_GET['filter']) && $_GET['filter'] !== 'all' ? $_GET['filter'] : null,
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
                <p>No customers found. <?php echo icon('users', 20); ?></p>
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