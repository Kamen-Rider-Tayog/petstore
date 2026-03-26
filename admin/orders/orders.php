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
$countParams = array_slice($params, 0, -2);
$countTypes = substr($types, 0, -2);

if ($status !== 'all') {
    $countQuery .= " AND o.status = ?";
}
if (!empty($search)) {
    $countQuery .= " AND (o.id LIKE ? OR c.first_name LIKE ? OR c.last_name LIKE ? OR c.email LIKE ?)";
}

$stmt = $conn->prepare($countQuery);
if (!empty($countParams)) {
    $stmt->bind_param($countTypes, ...$countParams);
}
$stmt->execute();
$totalOrders = $stmt->get_result()->fetch_assoc()['total'];
$totalPages = ceil($totalOrders / $limit);

// Admin CSS
echo '<link rel="stylesheet" href="/Ria-Pet-Store/admin/css/orders.css?v=' . time() . '">';
?>

<div class="admin-dashboard">
    <!-- Search and Filter -->
    <div class="search-bar">
        <form method="get">
            <div class="form-group">
                <input type="text" name="search" placeholder="Search by Order ID, Customer name, Email..." value="<?php echo htmlspecialchars($search); ?>">
            </div>
            <div class="form-group">
                <select name="status">
                    <option value="all" <?php echo $status === 'all' ? 'selected' : ''; ?>>All Statuses</option>
                    <option value="pending" <?php echo $status === 'pending' ? 'selected' : ''; ?>>Pending</option>
                    <option value="processing" <?php echo $status === 'processing' ? 'selected' : ''; ?>>Processing</option>
                    <option value="shipped" <?php echo $status === 'shipped' ? 'selected' : ''; ?>>Shipped</option>
                    <option value="delivered" <?php echo $status === 'delivered' ? 'selected' : ''; ?>>Delivered</option>
                    <option value="cancelled" <?php echo $status === 'cancelled' ? 'selected' : ''; ?>>Cancelled</option>
                </select>
            </div>
            <button type="submit" class="btn btn-primary"><?php echo icon('search', 16); ?> Filter</button>
            <?php if ($search || $status !== 'all'): ?>
                <a href="orders.php" class="btn btn-outline">Clear</a>
            <?php endif; ?>
        </form>
    </div>

    <!-- Orders Table -->
    <div class="table-container">
        <?php if ($orders->num_rows > 0): ?>
            <table class="admin-table">
                <thead>
                    <tr>
                        <th>Order ID</th>
                        <th>Customer</th>
                        <th>Total</th>
                        <th>Status</th>
                        <th>Date</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($order = $orders->fetch_assoc()): ?>
                    <tr>
                        <td>#<?php echo str_pad($order['id'], 6, '0', STR_PAD_LEFT); ?></td>
                        <td>
                            <div class="customer-info">
                                <span class="customer-name"><?php echo htmlspecialchars($order['first_name'] . ' ' . $order['last_name']); ?></span>
                                <span class="customer-email"><?php echo htmlspecialchars($order['email']); ?></span>
                            </div>
                        </td>
                        <td>₱<?php echo number_format($order['total_amount'], 2); ?></td>
                        <td>
                            <span class="status-badge status-<?php echo strtolower($order['status']); ?>">
                                <?php echo ucfirst($order['status']); ?>
                            </span>
                        </td>
                        <td><?php echo date('M d, Y', strtotime($order['created_at'])); ?></td>
                        <td class="actions">
                            <a href="order_details.php?id=<?php echo $order['id']; ?>" class="btn-icon" title="View Details">
                                <?php echo icon('eye', 14); ?>
                            </a>
                            <a href="order_edit.php?id=<?php echo $order['id']; ?>" class="btn-icon" title="Edit">
                                <?php echo icon('edit', 14); ?>
                            </a>
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
                        'status' => $status !== 'all' ? $status : null
                    ]));
                    for ($i = 1; $i <= $totalPages; $i++):
                        $active = $i === $page ? 'active' : '';
                        $url = "orders.php?" . ($queryString ? $queryString . '&' : '') . "page=$i";
                    ?>
                        <a href="<?php echo $url; ?>" class="pagination-link <?php echo $active; ?>"><?php echo $i; ?></a>
                    <?php endfor; ?>
                </div>
            <?php endif; ?>
        <?php else: ?>
            <div class="no-data">
                <p>No orders found.</p>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>