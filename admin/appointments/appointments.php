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

$page_title = 'Appointments';
require_once __DIR__ . '/../includes/header.php';

// Handle filters
$status = isset($_GET['status']) ? $_GET['status'] : 'all';
$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$date_filter = isset($_GET['date_filter']) ? $_GET['date_filter'] : 'all';
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$limit = 20;
$offset = ($page - 1) * $limit;

// Build query - join with customer_pets and employees using correct column names
$query = "SELECT a.*, 
          c.first_name, c.last_name, c.email, c.phone,
          cp.name as pet_name, cp.species as pet_species,
          e.first_name as employee_first_name, e.last_name as employee_last_name, e.position as employee_position
          FROM appointments a
          LEFT JOIN customers c ON a.customer_id = c.id
          LEFT JOIN customer_pets cp ON a.pet_id = cp.id
          LEFT JOIN employees e ON a.employee_id = e.id
          WHERE 1=1";

$params = [];
$types = '';

if ($status !== 'all') {
    $query .= " AND a.status = ?";
    $params[] = $status;
    $types .= 's';
}

if (!empty($search)) {
    $query .= " AND (a.id LIKE ? OR c.first_name LIKE ? OR c.last_name LIKE ? OR c.email LIKE ? OR a.service_type LIKE ? OR cp.name LIKE ? OR e.first_name LIKE ? OR e.last_name LIKE ?)";
    $searchTerm = "%$search%";
    $params = array_merge($params, [$searchTerm, $searchTerm, $searchTerm, $searchTerm, $searchTerm, $searchTerm, $searchTerm, $searchTerm]);
    $types .= 'ssssssss';
}

if ($date_filter !== 'all') {
    $today = date('Y-m-d');
    if ($date_filter === 'today') {
        $query .= " AND DATE(a.appointment_date) = ?";
        $params[] = $today;
        $types .= 's';
    } elseif ($date_filter === 'tomorrow') {
        $tomorrow = date('Y-m-d', strtotime('+1 day'));
        $query .= " AND DATE(a.appointment_date) = ?";
        $params[] = $tomorrow;
        $types .= 's';
    } elseif ($date_filter === 'this_week') {
        $query .= " AND WEEK(a.appointment_date) = WEEK(CURDATE()) AND YEAR(a.appointment_date) = YEAR(CURDATE())";
    } elseif ($date_filter === 'upcoming') {
        $query .= " AND a.appointment_date >= NOW() AND a.status IN ('pending', 'confirmed')";
    }
}

$query .= " ORDER BY a.appointment_date ASC LIMIT ? OFFSET ?";
$params[] = $limit;
$params[] = $offset;
$types .= 'ii';

// Get appointments
$stmt = $conn->prepare($query);
if (!empty($params)) {
    $stmt->bind_param($types, ...$params);
}
$stmt->execute();
$appointments = $stmt->get_result();

// Get total count for pagination
$countQuery = "SELECT COUNT(*) as total FROM appointments a LEFT JOIN customers c ON a.customer_id = c.id WHERE 1=1";
$countParams = [];
$countTypes = '';

if ($status !== 'all') {
    $countQuery .= " AND a.status = ?";
    $countParams[] = $status;
    $countTypes .= 's';
}

if (!empty($search)) {
    $countQuery .= " AND (a.id LIKE ? OR c.first_name LIKE ? OR c.last_name LIKE ? OR c.email LIKE ? OR a.service_type LIKE ?)";
    $searchTerm = "%$search%";
    $countParams = array_merge($countParams, [$searchTerm, $searchTerm, $searchTerm, $searchTerm, $searchTerm]);
    $countTypes .= 'sssss';
}

if ($date_filter !== 'all') {
    $today = date('Y-m-d');
    if ($date_filter === 'today') {
        $countQuery .= " AND DATE(a.appointment_date) = ?";
        $countParams[] = $today;
        $countTypes .= 's';
    } elseif ($date_filter === 'tomorrow') {
        $tomorrow = date('Y-m-d', strtotime('+1 day'));
        $countQuery .= " AND DATE(a.appointment_date) = ?";
        $countParams[] = $tomorrow;
        $countTypes .= 's';
    } elseif ($date_filter === 'this_week') {
        $countQuery .= " AND WEEK(a.appointment_date) = WEEK(CURDATE()) AND YEAR(a.appointment_date) = YEAR(CURDATE())";
    } elseif ($date_filter === 'upcoming') {
        $countQuery .= " AND a.appointment_date >= NOW() AND a.status IN ('pending', 'confirmed')";
    }
}

$countStmt = $conn->prepare($countQuery);
if (!empty($countParams)) {
    $countStmt->bind_param($countTypes, ...$countParams);
}
$countStmt->execute();
$totalAppointments = $countStmt->get_result()->fetch_assoc()['total'];
$totalPages = ceil($totalAppointments / $limit);

// Get counts for status filters
$allCount = $conn->query("SELECT COUNT(*) as count FROM appointments")->fetch_assoc()['count'];
$pendingCount = $conn->query("SELECT COUNT(*) as count FROM appointments WHERE status = 'pending'")->fetch_assoc()['count'];
$confirmedCount = $conn->query("SELECT COUNT(*) as count FROM appointments WHERE status = 'confirmed'")->fetch_assoc()['count'];
$completedCount = $conn->query("SELECT COUNT(*) as count FROM appointments WHERE status = 'completed'")->fetch_assoc()['count'];
$cancelledCount = $conn->query("SELECT COUNT(*) as count FROM appointments WHERE status = 'cancelled'")->fetch_assoc()['count'];

// Get counts for date filters
$todayCount = $conn->query("SELECT COUNT(*) as count FROM appointments WHERE DATE(appointment_date) = CURDATE()")->fetch_assoc()['count'];
$upcomingCount = $conn->query("SELECT COUNT(*) as count FROM appointments WHERE appointment_date >= NOW() AND status IN ('pending', 'confirmed')")->fetch_assoc()['count'];

// Admin CSS
echo '<link rel="stylesheet" href="/Ria-Pet-Store/admin/css/appointments.css?v=' . time() . '">';
?>

<div class="admin-dashboard">
    <!-- Search Bar -->
    <div class="search-bar">
        <form method="get" action="">
            <input type="hidden" name="status" value="<?php echo $status; ?>">
            <input type="hidden" name="date_filter" value="<?php echo $date_filter; ?>">
            <input type="text" name="search" placeholder="Search by ID, customer name, email, service, pet, or employee..." value="<?php echo htmlspecialchars($search); ?>">
            <button type="submit" class="btn btn-primary"><?php echo icon('search', 16); ?> Search</button>
            <?php if ($search): ?>
                <a href="?status=<?php echo $status; ?>&date_filter=<?php echo $date_filter; ?>" class="btn btn-outline"><?php echo icon('x', 16); ?> Clear</a>
            <?php endif; ?>
        </form>
        <a href="add_appointment.php" class="btn btn-success"><?php echo icon('plus', 16); ?> Add Appointment</a>
    </div>

    <!-- Status Filter Tabs -->
    <div class="filter-tabs">
        <a href="?status=all" class="filter-tab <?php echo $status === 'all' ? 'active' : ''; ?>">
            All Appointments
            <span class="filter-count"><?php echo $allCount; ?></span>
        </a>
        <a href="?status=pending" class="filter-tab <?php echo $status === 'pending' ? 'active' : ''; ?>">
            <span class="status-dot pending"></span>
            Pending
            <span class="filter-count"><?php echo $pendingCount; ?></span>
        </a>
        <a href="?status=confirmed" class="filter-tab <?php echo $status === 'confirmed' ? 'active' : ''; ?>">
            <span class="status-dot confirmed"></span>
            Confirmed
            <span class="filter-count"><?php echo $confirmedCount; ?></span>
        </a>
        <a href="?status=completed" class="filter-tab <?php echo $status === 'completed' ? 'active' : ''; ?>">
            <span class="status-dot completed"></span>
            Completed
            <span class="filter-count"><?php echo $completedCount; ?></span>
        </a>
        <a href="?status=cancelled" class="filter-tab <?php echo $status === 'cancelled' ? 'active' : ''; ?>">
            <span class="status-dot cancelled"></span>
            Cancelled
            <span class="filter-count"><?php echo $cancelledCount; ?></span>
        </a>
    </div>

    <!-- Date Filter Tabs -->
    <div class="date-tabs">
        <a href="?date_filter=all&status=<?php echo $status; ?>" class="date-tab <?php echo $date_filter === 'all' ? 'active' : ''; ?>">
            All Dates
        </a>
        <a href="?date_filter=today&status=<?php echo $status; ?>" class="date-tab <?php echo $date_filter === 'today' ? 'active' : ''; ?>">
            Today (<?php echo $todayCount; ?>)
        </a>
        <a href="?date_filter=tomorrow&status=<?php echo $status; ?>" class="date-tab <?php echo $date_filter === 'tomorrow' ? 'active' : ''; ?>">
            Tomorrow
        </a>
        <a href="?date_filter=this_week&status=<?php echo $status; ?>" class="date-tab <?php echo $date_filter === 'this_week' ? 'active' : ''; ?>">
            This Week
        </a>
        <a href="?date_filter=upcoming&status=<?php echo $status; ?>" class="date-tab <?php echo $date_filter === 'upcoming' ? 'active' : ''; ?>">
            Upcoming (<?php echo $upcomingCount; ?>)
        </a>
    </div>

    <!-- Status Legend -->
    <div class="status-legend">
        <span class="legend-item"><span class="status-dot pending"></span> Pending</span>
        <span class="legend-item"><span class="status-dot confirmed"></span> Confirmed</span>
        <span class="legend-item"><span class="status-dot completed"></span> Completed</span>
        <span class="legend-item"><span class="status-dot cancelled"></span> Cancelled</span>
    </div>

    <!-- Appointments Table -->
    <div class="table-container">
        <?php if ($appointments->num_rows > 0): ?>
            <table class="admin-table clickable-rows">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Date & Time</th>
                        <th>Customer</th>
                        <th>Service</th>
                        <th>Pet</th>
                        <th>Employee</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($appointment = $appointments->fetch_assoc()): 
                        $employeeName = '';
                        if (!empty($appointment['employee_first_name'])) {
                            $employeeName = $appointment['employee_first_name'] . ' ' . $appointment['employee_last_name'];
                        }
                    ?>
                    <tr class="clickable-row" data-href="appointment_details.php?id=<?php echo $appointment['id']; ?>">
                        <td class="appointment-id">#<?php echo str_pad($appointment['id'], 6, '0', STR_PAD_LEFT); ?></td>
                        <td>
                            <div class="datetime-info">
                                <span class="date"><?php echo date('M d, Y', strtotime($appointment['appointment_date'])); ?></span>
                                <span class="time"><?php echo date('g:i A', strtotime($appointment['appointment_date'])); ?></span>
                            </div>
                        </td>
                        <td>
                            <div class="customer-info">
                                <span class="customer-name"><?php echo htmlspecialchars($appointment['first_name'] . ' ' . $appointment['last_name']); ?></span>
                                <span class="customer-email"><?php echo htmlspecialchars($appointment['email']); ?></span>
                            </div>
                        </td>
                        <td class="service-type"><?php echo htmlspecialchars($appointment['service_type']); ?></td>
                        <td><?php echo htmlspecialchars($appointment['pet_name'] ?? 'N/A'); ?></td>
                        <td><?php echo htmlspecialchars($employeeName ?: 'N/A'); ?></td>
                        <td>
                            <div class="status-indicators">
                                <span class="status-dot <?php echo strtolower($appointment['status']); ?>" title="<?php echo ucfirst($appointment['status']); ?>"></span>
                                <span class="status-text"><?php echo ucfirst($appointment['status']); ?></span>
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
                        'date_filter' => $date_filter !== 'all' ? $date_filter : null,
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
                <p>No appointments found. <?php echo icon('calendar', 20); ?></p>
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