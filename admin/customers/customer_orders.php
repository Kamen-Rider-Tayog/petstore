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

$customerId = isset($_GET['id']) ? (int)$_GET['id'] : 0;

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

// Handle filters
$status = isset($_GET['status']) ? $_GET['status'] : 'all';
$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$limit = 20;
$offset = ($page - 1) * $limit;

// Build query for customer's orders
$query = "SELECT o.* FROM orders o WHERE o.customer_id = ?";
$params = [$customerId];
$types = 'i';

if ($status !== 'all') {
    $query .= " AND o.status = ?";
    $params[] = $status;
    $types .= 's';
}

if (!empty($search)) {
    $query .= " AND (o.id LIKE ?)";
    $searchTerm = "%$search%";
    $params[] = $searchTerm;
    $types .= 's';
}

$query .= " ORDER BY o.created_at DESC LIMIT ? OFFSET ?";
$params[] = $limit;
$params[] = $offset;
$types .= 'ii';

$stmt = $conn->prepare($query);
$stmt->bind_param($types, ...$params);
$stmt->execute();
$orders = $stmt->get_result();

// Get total count
$countQuery = "SELECT COUNT(*) as total FROM orders WHERE customer_id = ?";
$countParams = [$customerId];
$countTypes = 'i';

if ($status !== 'all') {
    $countQuery .= " AND status = ?";
    $countParams[] = $status;
    $countTypes .= 's';
}

if (!empty($search)) {
    $countQuery .= " AND (id LIKE ?)";
    $countParams[] = $searchTerm;
    $countTypes .= 's';
}

$countStmt = $conn->prepare($countQuery);
$countStmt->bind_param($countTypes, ...$countParams);
$countStmt->execute();
$totalOrders = $countStmt->get_result()->fetch_assoc()['total'];
$totalPages = ceil($totalOrders / $limit);

// Get counts for status filters
$allCount = $conn->query("SELECT COUNT(*) as count FROM orders WHERE customer_id = $customerId")->fetch_assoc()['count'];
$pendingCount = $conn->query("SELECT COUNT(*) as count FROM orders WHERE customer_id = $customerId AND status = 'pending'")->fetch_assoc()['count'];
$processingCount = $conn->query("SELECT COUNT(*) as count FROM orders WHERE customer_id = $customerId AND status = 'processing'")->fetch_assoc()['count'];
$shippedCount = $conn->query("SELECT COUNT(*) as count FROM orders WHERE customer_id = $customerId AND status = 'shipped'")->fetch_assoc()['count'];
$deliveredCount = $conn->query("SELECT COUNT(*) as count FROM orders WHERE customer_id = $customerId AND status = 'delivered'")->fetch_assoc()['count'];
$cancelledCount = $conn->query("SELECT COUNT(*) as count FROM orders WHERE customer_id = $customerId AND status = 'cancelled'")->fetch_assoc()['count'];

$page_title = 'Customer Orders - ' . $customer['first_name'] . ' ' . $customer['last_name'];
require_once __DIR__ . '/../includes/header.php';

echo '<link rel="stylesheet" href="/Ria-Pet-Store/admin/css/customers.css?v=' . time() . '">';
?>

<div class="admin-dashboard">
    <div class="header-actions">
        <a href="customer_details.php?id=<?php echo $customerId; ?>" class="btn btn-outline">
            <?php echo icon('arrow-left', 16); ?> Back to Customer
        </a>
    </div>
    
    <!-- Orders Table -->
    <div class="table-container">
        <?php if ($orders->num_rows > 0): ?>
            <table class="admin-table clickable-rows">
                <thead>
                    <tr>
                        <th>Order #</th>
                        <th>Date</th>
                        <th>Amount</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($order = $orders->fetch_assoc()): ?>
                    <tr class="clickable-row" data-href="../orders/order_details.php?id=<?php echo $order['id']; ?>">
                        <td class="order-id">#<?php echo str_pad($order['id'], 6, '0', STR_PAD_LEFT); ?></td>
                        <td><?php echo date('M d, Y', strtotime($order['created_at'])); ?></td>
                        <td class="order-amount">₱<?php echo number_format($order['total_amount'], 2); ?></td>
                        <td>
                            <div class="status-indicators">
                                <span class="status-dot <?php echo strtolower($order['status']); ?>" title="<?php echo ucfirst($order['status']); ?>"></span>
                                <span class="status-text"><?php echo ucfirst($order['status']); ?></span>
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
                        'id' => $customerId,
                        'status' => $status !== 'all' ? $status : null,
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
                <p>No orders found for this customer. <?php echo icon('package', 20); ?></p>
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