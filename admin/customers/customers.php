<?php
require_once '../includes/header.php';
require_once '../includes/sidebar.php';

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
$countParams = array_slice($params, 0, -2); // Remove limit and offset
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
?>

<main class="admin-main">
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 2rem;">
        <h2>Customers Management</h2>
        <a href="customer_add.php" class="btn btn-success">Add New Customer</a>
    </div>

    <!-- Filters -->
    <div style="background: #fff; padding: 1.5rem; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); margin-bottom: 2rem;">
        <form method="get" style="display: flex; gap: 1rem; align-items: flex-end;">
            <div class="form-group" style="margin: 0;">
                <label for="search" style="display: block; margin-bottom: 0.5rem;">Search:</label>
                <input type="text" name="search" id="search" value="<?php echo htmlspecialchars($search); ?>" placeholder="Name, Email, Phone">
            </div>
            <div style="align-self: flex-end;">
                <button type="submit" class="btn">Search</button>
                <?php if ($search): ?>
                    <a href="customers.php" class="btn btn-warning" style="margin-left: 0.5rem;">Clear</a>
                <?php endif; ?>
            </div>
        </form>
    </div>

    <!-- Customers Table -->
    <div style="background: #fff; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); overflow: hidden;">
        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Name</th>
                    <th>Email</th>
                    <th>Phone</th>
                    <th>Orders</th>
                    <th>Total Spent</th>
                    <th>Last Order</th>
                    <th>Joined</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($customers->num_rows > 0): ?>
                    <?php while ($customer = $customers->fetch_assoc()): ?>
                        <tr>
                            <td><?php echo $customer['id']; ?></td>
                            <td><?php echo htmlspecialchars($customer['first_name'] . ' ' . $customer['last_name']); ?></td>
                            <td><?php echo htmlspecialchars($customer['email']); ?></td>
                            <td><?php echo htmlspecialchars($customer['phone'] ?? 'N/A'); ?></td>
                            <td><?php echo $customer['total_orders']; ?></td>
                            <td>₱<?php echo number_format($customer['total_spent'], 2); ?></td>
                            <td>
                                <?php echo $customer['last_order_date'] ? date('M d, Y', strtotime($customer['last_order_date'])) : 'Never'; ?>
                            </td>
                            <td><?php echo date('M d, Y', strtotime($customer['created_at'])); ?></td>
                            <td>
                                <a href="customer_details.php?id=<?php echo $customer['id']; ?>" class="btn btn-small">View</a>
                                <a href="customer_edit.php?id=<?php echo $customer['id']; ?>" class="btn btn-small btn-warning">Edit</a>
                                <a href="customer_delete.php?id=<?php echo $customer['id']; ?>" class="btn btn-small btn-danger" onclick="return confirm('Are you sure you want to delete this customer?')">Delete</a>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="9" style="text-align: center; padding: 2rem;">No customers found.</td>
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
                'page' => null
            ]));

            for ($i = 1; $i <= $totalPages; $i++):
                $active = $i === $page ? ' style="font-weight: bold; color: #007bff;"' : '';
                $url = "customers.php?" . $queryString . "&page=$i";
            ?>
                <a href="<?php echo $url; ?>"<?php echo $active; ?> style="margin: 0 0.25rem;"><?php echo $i; ?></a>
            <?php endfor; ?>
        </div>
    <?php endif; ?>
</main>

<?php require_once '../includes/footer.php'; ?>
