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

$page_title = 'Services';
require_once __DIR__ . '/../includes/header.php';

// Handle filters
$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$category = isset($_GET['category']) ? trim($_GET['category']) : 'all';
$featured = isset($_GET['featured']) ? trim($_GET['featured']) : 'all';
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$limit = 20;
$offset = ($page - 1) * $limit;

// Build query
$query = "SELECT * FROM services WHERE 1=1";
$params = [];
$types = '';

if (!empty($search)) {
    $query .= " AND (service_name LIKE ? OR description LIKE ? OR category LIKE ?)";
    $searchTerm = "%$search%";
    $params = array_merge($params, [$searchTerm, $searchTerm, $searchTerm]);
    $types .= 'sss';
}

if ($category !== 'all') {
    $query .= " AND category = ?";
    $params[] = $category;
    $types .= 's';
}

if ($featured !== 'all') {
    $query .= " AND featured = ?";
    $params[] = $featured == 'featured' ? 1 : 0;
    $types .= 'i';
}

$query .= " ORDER BY service_name ASC LIMIT ? OFFSET ?";
$params[] = $limit;
$params[] = $offset;
$types .= 'ii';

$stmt = $conn->prepare($query);
if (!empty($params)) {
    $stmt->bind_param($types, ...$params);
}
$stmt->execute();
$services = $stmt->get_result();

// Get total count for pagination
$countQuery = "SELECT COUNT(*) as total FROM services WHERE 1=1";
$countParams = [];
$countTypes = '';

if (!empty($search)) {
    $countQuery .= " AND (service_name LIKE ? OR description LIKE ? OR category LIKE ?)";
    $searchTerm = "%$search%";
    $countParams = array_merge($countParams, [$searchTerm, $searchTerm, $searchTerm]);
    $countTypes .= 'sss';
}

if ($category !== 'all') {
    $countQuery .= " AND category = ?";
    $countParams[] = $category;
    $countTypes .= 's';
}

if ($featured !== 'all') {
    $countQuery .= " AND featured = ?";
    $countParams[] = $featured == 'featured' ? 1 : 0;
    $countTypes .= 'i';
}

$countStmt = $conn->prepare($countQuery);
if (!empty($countParams)) {
    $countStmt->bind_param($countTypes, ...$countParams);
}
$countStmt->execute();
$totalServices = $countStmt->get_result()->fetch_assoc()['total'];
$totalPages = ceil($totalServices / $limit);

// Get counts for filters
$allCount = $conn->query("SELECT COUNT(*) as count FROM services")->fetch_assoc()['count'];
$featuredCount = $conn->query("SELECT COUNT(*) as count FROM services WHERE featured = 1")->fetch_assoc()['count'];

// Get unique categories
$categories = $conn->query("SELECT DISTINCT category FROM services WHERE category IS NOT NULL ORDER BY category");

// Admin CSS
echo '<link rel="stylesheet" href="/Ria-Pet-Store/admin/css/services.css?v=' . time() . '">';
?>

<div class="admin-dashboard">
    <div class="page-header">
        <h1>Services</h1>
        <a href="add_service.php" class="btn btn-success"><?php echo icon('plus', 16); ?> Add Service</a>
    </div>

    <!-- Filter Tabs -->
    <div class="filter-tabs">
        <a href="?featured=all&category=<?php echo $category; ?>" class="filter-tab <?php echo $featured === 'all' ? 'active' : ''; ?>">
            All Services
            <span class="filter-count"><?php echo $allCount; ?></span>
        </a>
        <a href="?featured=featured&category=<?php echo $category; ?>" class="filter-tab <?php echo $featured === 'featured' ? 'active' : ''; ?>">
            <span class="status-dot featured"></span>
            Featured
            <span class="filter-count"><?php echo $featuredCount; ?></span>
        </a>
    </div>

    <!-- Category Filter -->
    <div class="category-tabs">
        <a href="?category=all&featured=<?php echo $featured; ?>" class="category-tab <?php echo $category === 'all' ? 'active' : ''; ?>">
            All Categories
        </a>
        <?php while ($cat = $categories->fetch_assoc()): ?>
            <a href="?category=<?php echo urlencode($cat['category']); ?>&featured=<?php echo $featured; ?>" class="category-tab <?php echo $category === $cat['category'] ? 'active' : ''; ?>">
                <?php echo ucfirst(htmlspecialchars($cat['category'])); ?>
            </a>
        <?php endwhile; ?>
    </div>

    <!-- Status Legend -->
    <div class="status-legend">
        <span class="legend-item"><span class="status-dot featured"></span> Featured</span>
        <span class="legend-item"><span class="status-dot regular"></span> Regular</span>
    </div>

    <!-- Search Bar -->
    <div class="search-bar">
        <form method="get" action="">
            <input type="hidden" name="category" value="<?php echo $category; ?>">
            <input type="hidden" name="featured" value="<?php echo $featured; ?>">
            <input type="text" name="search" placeholder="Search by service name, description, or category..." value="<?php echo htmlspecialchars($search); ?>">
            <button type="submit" class="btn btn-primary"><?php echo icon('search', 16); ?> Search</button>
            <?php if ($search): ?>
                <a href="?category=<?php echo $category; ?>&featured=<?php echo $featured; ?>" class="btn btn-outline"><?php echo icon('x', 16); ?> Clear</a>
            <?php endif; ?>
        </form>
    </div>

    <!-- Services Table -->
    <div class="table-container">
        <?php if ($services->num_rows > 0): ?>
            <table class="admin-table clickable-rows">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Service Name</th>
                        <th>Category</th>
                        <th>Duration</th>
                        <th>Price</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($service = $services->fetch_assoc()): ?>
                    <tr class="clickable-row" data-href="service_details.php?id=<?php echo $service['id']; ?>">
                        <td class="service-id">#<?php echo str_pad($service['id'], 4, '0', STR_PAD_LEFT); ?></td>
                        <td>
                            <div class="service-info">
                                <span class="service-name"><?php echo htmlspecialchars($service['service_name']); ?></span>
                                <?php if (!empty($service['description'])): ?>
                                    <span class="service-description"><?php echo htmlspecialchars(substr($service['description'], 0, 60)) . (strlen($service['description']) > 60 ? '...' : ''); ?></span>
                                <?php endif; ?>
                            </div>
                        </td>
                        <td><?php echo htmlspecialchars(ucfirst($service['category'] ?? 'General')); ?></td>
                        <td><?php echo $service['duration_minutes']; ?> min</td>
                        <td class="service-price">₱<?php echo number_format($service['price'], 2); ?></td>
                        <td>
                            <div class="status-indicators">
                                <span class="status-dot <?php echo $service['featured'] ? 'featured' : 'regular'; ?>" title="<?php echo $service['featured'] ? 'Featured' : 'Regular'; ?>"></span>
                                <span class="status-text"><?php echo $service['featured'] ? 'Featured' : 'Regular'; ?></span>
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
                        'category' => $category !== 'all' ? $category : null,
                        'featured' => $featured !== 'all' ? $featured : null,
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
                <p>No services found. <?php echo icon('heart', 20); ?></p>
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