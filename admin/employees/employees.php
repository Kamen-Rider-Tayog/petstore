<?php
require_once '../includes/header.php';
require_once '../includes/sidebar.php';

// Handle filters
$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$role = isset($_GET['role']) ? $_GET['role'] : 'all';
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$limit = 20;
$offset = ($page - 1) * $limit;

// Build query
$query = "SELECT e.* FROM employees e WHERE 1=1";

$params = [];
$types = '';

if ($role !== 'all') {
    if ($role === 'admin') {
        $query .= " AND e.is_admin = 1";
    } elseif ($role === 'employee') {
        $query .= " AND (e.is_admin = 0 OR e.is_admin IS NULL)";
    }
}

if (!empty($search)) {
    $query .= " AND (e.first_name LIKE ? OR e.last_name LIKE ? OR e.email LIKE ? OR e.position LIKE ?)";
    $searchTerm = "%$search%";
    $params = array_merge($params, [$searchTerm, $searchTerm, $searchTerm, $searchTerm]);
    $types .= 'ssss';
}

$query .= " ORDER BY e.is_admin DESC, e.created_at DESC LIMIT ? OFFSET ?";
$params[] = $limit;
$params[] = $offset;
$types .= 'ii';

// Get employees
$stmt = $conn->prepare($query);
if (!empty($params)) {
    $stmt->bind_param($types, ...$params);
}
$stmt->execute();
$employees = $stmt->get_result();

// Get total count for pagination
$countQuery = "SELECT COUNT(*) as total FROM employees e WHERE 1=1";
$countParams = array_slice($params, 0, -2); // Remove limit and offset
$countTypes = substr($types, 0, -2);

if ($role !== 'all') {
    if ($role === 'admin') {
        $countQuery .= " AND e.is_admin = 1";
    } elseif ($role === 'employee') {
        $countQuery .= " AND (e.is_admin = 0 OR e.is_admin IS NULL)";
    }
}

if (!empty($search)) {
    $countQuery .= " AND (e.first_name LIKE ? OR e.last_name LIKE ? OR e.email LIKE ? OR e.position LIKE ?)";
}

$stmt = $conn->prepare($countQuery);
if (!empty($countParams)) {
    $stmt->bind_param($countTypes, ...$countParams);
}
$stmt->execute();
$totalEmployees = $stmt->get_result()->fetch_assoc()['total'];
$totalPages = ceil($totalEmployees / $limit);
?>

<main class="admin-main">
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 2rem;">
        <h2>Employees Management</h2>
        <a href="employee_add.php" class="btn btn-success">Add New Employee</a>
    </div>

    <!-- Filters -->
    <div style="background: #fff; padding: 1.5rem; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); margin-bottom: 2rem;">
        <form method="get" style="display: flex; gap: 1rem; align-items: flex-end; flex-wrap: wrap;">
            <div class="form-group" style="margin: 0;">
                <label for="search" style="display: block; margin-bottom: 0.5rem;">Search:</label>
                <input type="text" name="search" id="search" value="<?php echo htmlspecialchars($search); ?>" placeholder="Name, Email, Position">
            </div>
            <div class="form-group" style="margin: 0;">
                <label for="role" style="display: block; margin-bottom: 0.5rem;">Role:</label>
                <select name="role" id="role">
                    <option value="all" <?php echo $role === 'all' ? 'selected' : ''; ?>>All Roles</option>
                    <option value="admin" <?php echo $role === 'admin' ? 'selected' : ''; ?>>Admins</option>
                    <option value="employee" <?php echo $role === 'employee' ? 'selected' : ''; ?>>Employees</option>
                </select>
            </div>
            <div style="align-self: flex-end;">
                <button type="submit" class="btn">Filter</button>
                <?php if ($search || $role !== 'all'): ?>
                    <a href="employees.php" class="btn btn-warning" style="margin-left: 0.5rem;">Clear</a>
                <?php endif; ?>
            </div>
        </form>
    </div>

    <!-- Employees Table -->
    <div style="background: #fff; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); overflow: hidden;">
        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Name</th>
                    <th>Email</th>
                    <th>Position</th>
                    <th>Role</th>
                    <th>Phone</th>
                    <th>Joined</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($employees->num_rows > 0): ?>
                    <?php while ($employee = $employees->fetch_assoc()): ?>
                        <tr>
                            <td><?php echo $employee['id']; ?></td>
                            <td><?php echo htmlspecialchars($employee['first_name'] . ' ' . $employee['last_name']); ?></td>
                            <td><?php echo htmlspecialchars($employee['email']); ?></td>
                            <td><?php echo htmlspecialchars($employee['position'] ?? 'N/A'); ?></td>
                            <td>
                                <?php if ($employee['is_admin']): ?>
                                    <span class="status-badge status-admin">Admin</span>
                                <?php else: ?>
                                    <span class="status-badge status-employee">Employee</span>
                                <?php endif; ?>
                            </td>
                            <td><?php echo htmlspecialchars($employee['phone'] ?? 'N/A'); ?></td>
                            <td><?php echo date('M d, Y', strtotime($employee['created_at'])); ?></td>
                            <td>
                                <a href="employee_details.php?id=<?php echo $employee['id']; ?>" class="btn btn-small">View</a>
                                <a href="employee_edit.php?id=<?php echo $employee['id']; ?>" class="btn btn-small btn-warning">Edit</a>
                                <a href="employee_delete.php?id=<?php echo $employee['id']; ?>" class="btn btn-small btn-danger" onclick="return confirm('Are you sure you want to delete this employee?')">Delete</a>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="8" style="text-align: center; padding: 2rem;">No employees found.</td>
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
                'role' => $role !== 'all' ? $role : null,
                'page' => null
            ]));

            for ($i = 1; $i <= $totalPages; $i++):
                $active = $i === $page ? ' style="font-weight: bold; color: #007bff;"' : '';
                $url = "employees.php?" . $queryString . "&page=$i";
            ?>
                <a href="<?php echo $url; ?>"<?php echo $active; ?> style="margin: 0 0.25rem;"><?php echo $i; ?></a>
            <?php endfor; ?>
        </div>
    <?php endif; ?>
</main>

<?php require_once '../includes/footer.php'; ?>