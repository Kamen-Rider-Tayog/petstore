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

$page_title = 'Orders';
require_once __DIR__ . '/../includes/header.php';

// Handle filters
$status = isset($_GET['status']) ? $_GET['status'] : 'all';
$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$limit = 20;
$offset = ($page - 1) * $limit;

// Build query
$query = "SELECT o.*, c.first_name, c.last_name, c.email
          FROM orders o
          LEFT JOIN customers c ON o.customer_id = c.id
          WHERE 1=1";

$params = [];
$types = '';

if ($status !== 'all') {
    $query .= " AND o.status = ?";
    $params[] = $status;
    $types .= 's';
}

if (!empty($search)) {
    $query .= " AND (o.id LIKE ? OR c.first_name LIKE ? OR c.last_name LIKE ? OR c.email LIKE ?)";
    $searchTerm = "%$search%";
    $params = array_merge($params, [$searchTerm, $searchTerm, $searchTerm, $searchTerm]);
    $types .= 'ssss';
}

$query .= " ORDER BY o.created_at DESC LIMIT ? OFFSET ?";
$params[] = $limit;
$params[] = $offset;
$types .= 'ii';

// Get orders
$stmt = $conn->prepare($query);
if (!empty($params)) {
    $stmt->bind_param($types, ...$params);
}
$stmt->execute();
$orders = $stmt->get_result();

// Get total count for pagination
$countQuery = "SELECT COUNT(*) as total FROM orders o LEFT JOIN customers c ON o.customer_id = c.id WHERE 1=1";
$countParams = [];
$countTypes = '';

if ($status !== 'all') {
    $countQuery .= " AND o.status = ?";
    $countParams[] = $status;
    $countTypes .= 's';
}

if (!empty($search)) {
    $countQuery .= " AND (o.id LIKE ? OR c.first_name LIKE ? OR c.last_name LIKE ? OR c.email LIKE ?)";
    $searchTerm = "%$search%";
    $countParams = array_merge($countParams, [$searchTerm, $searchTerm, $searchTerm, $searchTerm]);
    $countTypes .= 'ssss';
}

$countStmt = $conn->prepare($countQuery);
if (!empty($countParams)) {
    $countStmt->bind_param($countTypes, ...$countParams);
}
$countStmt->execute();
$totalOrders = $countStmt->get_result()->fetch_assoc()['total'];
$totalPages = ceil($totalOrders / $limit);

// Get counts for status filters
$allCount = $conn->query("SELECT COUNT(*) as count FROM orders")->fetch_assoc()['count'];
$pendingCount = $conn->query("SELECT COUNT(*) as count FROM orders WHERE status = 'pending'")->fetch_assoc()['count'];
$processingCount = $conn->query("SELECT COUNT(*) as count FROM orders WHERE status = 'processing'")->fetch_assoc()['count'];
$shippedCount = $conn->query("SELECT COUNT(*) as count FROM orders WHERE status = 'shipped'")->fetch_assoc()['count'];
$deliveredCount = $conn->query("SELECT COUNT(*) as count FROM orders WHERE status = 'delivered'")->fetch_assoc()['count'];
$cancelledCount = $conn->query("SELECT COUNT(*) as count FROM orders WHERE status = 'cancelled'")->fetch_assoc()['count'];

// Admin CSS
echo '<link rel="stylesheet" href="/Ria-Pet-Store/admin/css/orders.css?v=' . time() . '">';
?>

<div class="admin-dashboard">
    <!-- Search Bar -->
    <div class="search-bar">
        <form method="get" action="">
            <input type="hidden" name="status" value="<?php echo $status; ?>">
            <input type="text" name="search" placeholder="Search by Order ID, Customer name, Email..." value="<?php echo htmlspecialchars($search); ?>">
            <button type="submit" class="btn btn-primary"><?php echo icon('search', 16); ?> Search</button>
            <?php if ($search): ?>
                <a href="?status=<?php echo $status; ?>" class="btn btn-outline"><?php echo icon('x', 16); ?> Clear</a>
            <?php endif; ?>
        </form>
        <a href="add_order.php" class="btn btn-success"><?php echo icon('plus', 16); ?> Add New Order</a>
    </div>

    <!-- Status Filter Tabs -->
    <div class="filter-tabs">
        <a href="?status=all" class="filter-tab <?php echo $status === 'all' ? 'active' : ''; ?>">
            All Orders
            <span class="filter-count"><?php echo $allCount; ?></span>
        </a>
        <a href="?status=pending" class="filter-tab <?php echo $status === 'pending' ? 'active' : ''; ?>">
            <span class="status-dot pending"></span>
            Pending
            <span class="filter-count"><?php echo $pendingCount; ?></span>
        </a>
        <a href="?status=processing" class="filter-tab <?php echo $status === 'processing' ? 'active' : ''; ?>">
            <span class="status-dot processing"></span>
            Processing
            <span class="filter-count"><?php echo $processingCount; ?></span>
        </a>
        <a href="?status=shipped" class="filter-tab <?php echo $status === 'shipped' ? 'active' : ''; ?>">
            <span class="status-dot shipped"></span>
            Shipped
            <span class="filter-count"><?php echo $shippedCount; ?></span>
        </a>
        <a href="?status=delivered" class="filter-tab <?php echo $status === 'delivered' ? 'active' : ''; ?>">
            <span class="status-dot delivered"></span>
            Delivered
            <span class="filter-count"><?php echo $deliveredCount; ?></span>
        </a>
        <a href="?status=cancelled" class="filter-tab <?php echo $status === 'cancelled' ? 'active' : ''; ?>">
            <span class="status-dot cancelled"></span>
            Cancelled
            <span class="filter-count"><?php echo $cancelledCount; ?></span>
        </a>
    </div>

    <!-- Status Legend -->
    <div class="status-legend">
        <span class="legend-item"><span class="status-dot pending"></span> Pending</span>
        <span class="legend-item"><span class="status-dot processing"></span> Processing</span>
        <span class="legend-item"><span class="status-dot shipped"></span> Shipped</span>
        <span class="legend-item"><span class="status-dot delivered"></span> Delivered</span>
        <span class="legend-item"><span class="status-dot cancelled"></span> Cancelled</span>
    </div>

    <!-- Orders Table -->
    <div class="table-container">
        <?php if ($orders->num_rows > 0): ?>
            <table class="admin-table clickable-rows">
                <thead>
                    <tr>
                        <th>Order ID</th>
                        <th>Customer</th>
                        <th>Total</th>
                        <th>Status</th>
                        <th>Date</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($order = $orders->fetch_assoc()): ?>
                    <tr class="clickable-row" data-href="order_details.php?id=<?php echo $order['id']; ?>">
                        <td class="order-id">#<?php echo str_pad($order['id'], 6, '0', STR_PAD_LEFT); ?></td>
                        <td>
                            <div class="customer-info">
                                <span class="customer-name"><?php echo htmlspecialchars($order['first_name'] . ' ' . $order['last_name']); ?></span>
                                <span class="customer-email"><?php echo htmlspecialchars($order['email']); ?></span>
                            </div>
                        </td>
                        <td class="order-total">₱<?php echo number_format($order['total_amount'], 2); ?></td>
                        <td>
                            <div class="status-indicators">
                                <span class="status-dot <?php echo strtolower($order['status']); ?>" title="<?php echo ucfirst($order['status']); ?>"></span>
                                <span class="status-text"><?php echo ucfirst($order['status']); ?></span>
                            </div>
                        </td>
                        <td><?php echo date('M d, Y', strtotime($order['created_at'])); ?></td>
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
                        'status' => $status !== 'all' ? $status : null,
                        'page' => null
                    ]);
                    $queryString = http_build_query($queryParams);
                    
                    // Previous button
                    if ($page > 1) {
                        echo '<a href="?' . $queryString . '&page=' . ($page - 1) . '" class="pagination-link">&laquo; Prev</a>';
                    }
                    
                    // Page numbers
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
                    
                    // Next button
                    if ($page < $totalPages) {
                        echo '<a href="?' . $queryString . '&page=' . ($page + 1) . '" class="pagination-link">Next &raquo;</a>';
                    }
                    ?>
                </div>
            <?php endif; ?>
        <?php else: ?>
            <div class="no-data">
                <p>No orders found. <?php echo icon('package', 20); ?></p>
            </div>
        <?php endif; ?>
    </div>
</div>

<script>
// Make table rows clickable
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