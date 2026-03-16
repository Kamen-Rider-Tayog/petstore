<?php
require_once '../includes/header.php';
require_once '../includes/sidebar.php';

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
$countParams = array_slice($params, 0, -2); // Remove limit and offset
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

// Get status options
$statusOptions = $conn->query("SELECT DISTINCT status FROM orders ORDER BY status");
?>

<main class="admin-main">
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 2rem;">
        <h2>Orders Management</h2>
        <a href="order_add.php" class="btn btn-success">Add New Order</a>
    </div>

    <!-- Filters -->
    <div style="background: #fff; padding: 1.5rem; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); margin-bottom: 2rem;">
        <form method="get" style="display: flex; gap: 1rem; align-items: flex-end; flex-wrap: wrap;">
            <div class="form-group" style="margin: 0;">
                <label for="search" style="display: block; margin-bottom: 0.5rem;">Search:</label>
                <input type="text" name="search" id="search" value="<?php echo htmlspecialchars($search); ?>" placeholder="Order ID, Customer name, Email">
            </div>
            <div class="form-group" style="margin: 0;">
                <label for="status" style="display: block; margin-bottom: 0.5rem;">Status:</label>
                <select name="status" id="status">
                    <option value="all" <?php echo $status === 'all' ? 'selected' : ''; ?>>All Statuses</option>
                    <option value="pending" <?php echo $status === 'pending' ? 'selected' : ''; ?>>Pending</option>
                    <option value="processing" <?php echo $status === 'processing' ? 'selected' : ''; ?>>Processing</option>
                    <option value="shipped" <?php echo $status === 'shipped' ? 'selected' : ''; ?>>Shipped</option>
                    <option value="delivered" <?php echo $status === 'delivered' ? 'selected' : ''; ?>>Delivered</option>
                    <option value="cancelled" <?php echo $status === 'cancelled' ? 'selected' : ''; ?>>Cancelled</option>
                </select>
            </div>
            <div style="align-self: flex-end;">
                <button type="submit" class="btn">Filter</button>
                <?php if ($search || $status !== 'all'): ?>
                    <a href="orders.php" class="btn btn-warning" style="margin-left: 0.5rem;">Clear</a>
                <?php endif; ?>
            </div>
        </form>
    </div>

    <!-- Orders Table -->
    <div style="background: #fff; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); overflow: hidden;">
        <table>
            <thead>
                <tr>
                    <th>Order ID</th>
                    <th>Customer</th>
                    <th>Total Amount</th>
                    <th>Status</th>
                    <th>Order Date</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($orders->num_rows > 0): ?>
                    <?php while ($order = $orders->fetch_assoc()): ?>
                        <tr>
                            <td>#<?php echo str_pad($order['id'], 6, '0', STR_PAD_LEFT); ?></td>
                            <td>
                                <?php echo htmlspecialchars($order['first_name'] . ' ' . $order['last_name']); ?><br>
                                <small style="color: #666;"><?php echo htmlspecialchars($order['email']); ?></small>
                            </td>
                            <td>₱<?php echo number_format($order['total_amount'], 2); ?></td>
                            <td>
                                <span class="status-badge status-<?php echo strtolower($order['status']); ?>">
                                    <?php echo htmlspecialchars(ucfirst($order['status'])); ?>
                                </span>
                            </td>
                            <td><?php echo date('M d, Y H:i', strtotime($order['created_at'])); ?></td>
                            <td>
                                <a href="order_details.php?id=<?php echo $order['id']; ?>" class="btn btn-small">View</a>
                                <a href="order_edit.php?id=<?php echo $order['id']; ?>" class="btn btn-small btn-warning">Edit</a>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="6" style="text-align: center; padding: 2rem;">No orders found.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

    <!-- Pagination -->
    <?php if ($totalPages > 1): ?>
        <div style="margin-top: 2rem; text-align: center;">
            <?php
            $queryString = http_build_query(array_filter([
                'search' => $search,
                'status' => $status !== 'all' ? $status : null,
                'page' => null
            ]));

            for ($i = 1; $i <= $totalPages; $i++):
                $active = $i === $page ? ' style="font-weight: bold; color: #007bff;"' : '';
                $url = "orders.php?" . $queryString . "&page=$i";
            ?>
                <a href="<?php echo $url; ?>"<?php echo $active; ?> style="margin: 0 0.25rem;"><?php echo $i; ?></a>
            <?php endfor; ?>
        </div>
    <?php endif; ?>
</main>

<?php require_once '../includes/footer.php'; ?>