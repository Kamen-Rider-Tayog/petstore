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

$page_title = 'Employees';
require_once __DIR__ . '/../includes/header.php';

// Handle filters
$status = isset($_GET['status']) ? $_GET['status'] : 'all';
$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$limit = 20;
$offset = ($page - 1) * $limit;

// Build query
$query = "SELECT * FROM employees WHERE 1=1";
$params = [];
$types = '';

if ($status !== 'all') {
    if ($status === 'active') {
        $query .= " AND is_active = 1";
    } elseif ($status === 'inactive') {
        $query .= " AND is_active = 0";
    } elseif ($status === 'admin') {
        $query .= " AND is_admin = 1";
    } elseif ($status === 'staff') {
        $query .= " AND is_admin = 0";
    }
}

if (!empty($search)) {
    $query .= " AND (first_name LIKE ? OR last_name LIKE ? OR email LIKE ? OR position LIKE ? OR phone LIKE ?)";
    $searchTerm = "%$search%";
    $params = array_merge($params, [$searchTerm, $searchTerm, $searchTerm, $searchTerm, $searchTerm]);
    $types .= 'sssss';
}

$query .= " ORDER BY first_name ASC LIMIT ? OFFSET ?";
$params[] = $limit;
$params[] = $offset;
$types .= 'ii';

$stmt = $conn->prepare($query);
if (!empty($params)) {
    $stmt->bind_param($types, ...$params);
}
$stmt->execute();
$employees = $stmt->get_result();

// Get total count for pagination
$countQuery = "SELECT COUNT(*) as total FROM employees WHERE 1=1";
$countParams = [];
$countTypes = '';

if ($status !== 'all') {
    if ($status === 'active') {
        $countQuery .= " AND is_active = 1";
    } elseif ($status === 'inactive') {
        $countQuery .= " AND is_active = 0";
    } elseif ($status === 'admin') {
        $countQuery .= " AND is_admin = 1";
    } elseif ($status === 'staff') {
        $countQuery .= " AND is_admin = 0";
    }
}

if (!empty($search)) {
    $countQuery .= " AND (first_name LIKE ? OR last_name LIKE ? OR email LIKE ? OR position LIKE ? OR phone LIKE ?)";
    $searchTerm = "%$search%";
    $countParams = array_merge($countParams, [$searchTerm, $searchTerm, $searchTerm, $searchTerm, $searchTerm]);
    $countTypes .= 'sssss';
}

$countStmt = $conn->prepare($countQuery);
if (!empty($countParams)) {
    $countStmt->bind_param($countTypes, ...$countParams);
}
$countStmt->execute();
$totalEmployees = $countStmt->get_result()->fetch_assoc()['total'];
$totalPages = ceil($totalEmployees / $limit);

// Get counts for filters
$allCount = $conn->query("SELECT COUNT(*) as count FROM employees")->fetch_assoc()['count'];
$activeCount = $conn->query("SELECT COUNT(*) as count FROM employees WHERE is_active = 1")->fetch_assoc()['count'];
$inactiveCount = $conn->query("SELECT COUNT(*) as count FROM employees WHERE is_active = 0")->fetch_assoc()['count'];
$adminCount = $conn->query("SELECT COUNT(*) as count FROM employees WHERE is_admin = 1")->fetch_assoc()['count'];
$staffCount = $conn->query("SELECT COUNT(*) as count FROM employees WHERE is_admin = 0")->fetch_assoc()['count'];

// Admin CSS
echo '<link rel="stylesheet" href="/Ria-Pet-Store/admin/css/employees.css?v=' . time() . '">';
?>

<div class="admin-dashboard">
    <!-- Search Bar -->
    <div class="search-bar">
        <form method="get" action="">
            <input type="hidden" name="status" value="<?php echo $status; ?>">
            <input type="text" name="search" placeholder="Search by name, email, position, or phone..." value="<?php echo htmlspecialchars($search); ?>">
            <button type="submit" class="btn btn-primary"><?php echo icon('search', 16); ?> Search</button>
            <?php if ($search): ?>
                <a href="?status=<?php echo $status; ?>" class="btn btn-outline"><?php echo icon('x', 16); ?> Clear</a>
            <?php endif; ?>
        </form>
        <a href="add_employee.php" class="btn btn-success"><?php echo icon('plus', 16); ?> Add Employee</a>
    </div>

    <!-- Filter Tabs -->
    <div class="filter-tabs">
        <a href="?status=all" class="filter-tab <?php echo $status === 'all' ? 'active' : ''; ?>">
            All Employees
            <span class="filter-count"><?php echo $allCount; ?></span>
        </a>
        <a href="?status=active" class="filter-tab <?php echo $status === 'active' ? 'active' : ''; ?>">
            <span class="status-dot active"></span>
            Active
            <span class="filter-count"><?php echo $activeCount; ?></span>
        </a>
        <a href="?status=inactive" class="filter-tab <?php echo $status === 'inactive' ? 'active' : ''; ?>">
            <span class="status-dot inactive"></span>
            Inactive
            <span class="filter-count"><?php echo $inactiveCount; ?></span>
        </a>
        <a href="?status=admin" class="filter-tab <?php echo $status === 'admin' ? 'active' : ''; ?>">
            <span class="status-dot admin"></span>
            Admins
            <span class="filter-count"><?php echo $adminCount; ?></span>
        </a>
        <a href="?status=staff" class="filter-tab <?php echo $status === 'staff' ? 'active' : ''; ?>">
            <span class="status-dot staff"></span>
            Staff
            <span class="filter-count"><?php echo $staffCount; ?></span>
        </a>
    </div>

    <!-- Status Legend -->
    <div class="status-legend">
        <span class="legend-item"><span class="status-dot active"></span> Active</span>
        <span class="legend-item"><span class="status-dot inactive"></span> Inactive</span>
        <span class="legend-item"><span class="status-dot admin"></span> Admin</span>
        <span class="legend-item"><span class="status-dot staff"></span> Staff</span>
    </div>

    <!-- Employees Table -->
    <div class="table-container">
        <?php if ($employees->num_rows > 0): ?>
            <table class="admin-table clickable-rows">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Name</th>
                        <th>Email</th>
                        <th>Position</th>
                        <th>Phone</th>
                        <th>Status</th>
                     </tr>
                </thead>
                <tbody>
                    <?php while ($employee = $employees->fetch_assoc()): ?>
                    <tr class="clickable-row" data-href="employee_details.php?id=<?php echo $employee['id']; ?>">
                        <td class="employee-id">#<?php echo str_pad($employee['id'], 4, '0', STR_PAD_LEFT); ?></td>
                        <td>
                            <div class="employee-info">
                                <span class="employee-name"><?php echo htmlspecialchars($employee['first_name'] . ' ' . $employee['last_name']); ?></span>
                            </div>
                        </td>
                        <td><?php echo htmlspecialchars($employee['email']); ?></td>
                        <td><?php echo htmlspecialchars($employee['position'] ?? 'N/A'); ?></td>
                        <td><?php echo htmlspecialchars($employee['phone'] ?? 'N/A'); ?></td>
                        <td>
                            <div class="status-indicators">
                                <span class="status-dot <?php echo $employee['is_active'] ? 'active' : 'inactive'; ?>" title="<?php echo $employee['is_active'] ? 'Active' : 'Inactive'; ?>"></span>
                                <span class="status-dot <?php echo $employee['is_admin'] ? 'admin' : 'staff'; ?>" title="<?php echo $employee['is_admin'] ? 'Admin' : 'Staff'; ?>"></span>
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
                        'search' => $search,
                        'status' => $status !== 'all' ? $status : null,
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
                <p>No employees found. <?php echo icon('users', 20); ?></p>
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