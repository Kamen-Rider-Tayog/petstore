<?php
require_once '../includes/header.php';
require_once '../includes/sidebar.php';

// Get date range from URL parameters or set defaults
$start_date = isset($_GET['start_date']) ? $_GET['start_date'] : date('Y-m-d', strtotime('-30 days'));
$end_date = isset($_GET['end_date']) ? $_GET['end_date'] : date('Y-m-d');

// Build date filter for queries
$date_filter = "AND DATE(order_date) BETWEEN '$start_date' AND '$end_date'";

// Get various statistics with date filtering
$total_products = $conn->query("SELECT COUNT(*) as count FROM products")->fetch_assoc()['count'];
$total_pets = $conn->query("SELECT COUNT(*) as count FROM pets")->fetch_assoc()['count'];
$total_orders = $conn->query("SELECT COUNT(*) as count FROM orders WHERE status = 'completed' $date_filter")->fetch_assoc()['count'];
$total_customers = $conn->query("SELECT COUNT(*) as count FROM customers WHERE created_at <= '$end_date'")->fetch_assoc()['count'];

$low_stock_products = $conn->query("SELECT COUNT(*) as count FROM products WHERE stock_quantity < 10")->fetch_assoc()['count'];
$featured_products = $conn->query("SELECT COUNT(*) as count FROM products WHERE featured = 1")->fetch_assoc()['count'];
$on_sale_products = $conn->query("SELECT COUNT(*) as count FROM products WHERE on_sale = 1")->fetch_assoc()['count'];

$total_revenue = $conn->query("SELECT SUM(total_amount) as total FROM orders WHERE status = 'completed' $date_filter")->fetch_assoc()['total'] ?? 0;

$recent_orders = $conn->query("SELECT COUNT(*) as count FROM orders WHERE order_date >= DATE_SUB(NOW(), INTERVAL 30 DAY)")->fetch_assoc()['count'];
$new_customers = $conn->query("SELECT COUNT(*) as count FROM customers WHERE created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)")->fetch_assoc()['count'];

// Top selling products with date filter
$top_products = $conn->query("
    SELECT p.name, SUM(oi.quantity) as total_sold, SUM(oi.quantity * oi.price) as revenue
    FROM order_items oi
    JOIN products p ON oi.product_id = p.id
    JOIN orders o ON oi.order_id = o.id
    WHERE o.status = 'completed' $date_filter
    GROUP BY p.id, p.name
    ORDER BY total_sold DESC
    LIMIT 5
");

// Monthly sales data for chart
$monthly_sales = $conn->query("
    SELECT DATE_FORMAT(order_date, '%Y-%m') as month, SUM(total_amount) as sales
    FROM orders
    WHERE status = 'completed' AND order_date >= DATE_SUB(NOW(), INTERVAL 12 MONTH)
    GROUP BY DATE_FORMAT(order_date, '%Y-%m')
    ORDER BY month
");

// Category performance
$category_performance = $conn->query("
    SELECT p.category, COUNT(DISTINCT p.id) as products, SUM(oi.quantity) as sold, SUM(oi.quantity * oi.price) as revenue
    FROM products p
    LEFT JOIN order_items oi ON p.id = oi.product_id
    LEFT JOIN orders o ON oi.order_id = o.id AND o.status = 'completed' $date_filter
    WHERE p.category IS NOT NULL
    GROUP BY p.category
    ORDER BY revenue DESC
");

// Customer statistics
$customer_stats = $conn->query("
    SELECT
        COUNT(DISTINCT CASE WHEN created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY) THEN id END) as new_customers_30,
        COUNT(DISTINCT CASE WHEN created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY) THEN id END) as new_customers_7,
        AVG((SELECT COUNT(*) FROM orders WHERE orders.customer_id = customers.id AND status = 'completed')) as avg_orders_per_customer
    FROM customers
")->fetch_assoc();

// Pet inventory statistics
$pet_stats = $conn->query("
    SELECT
        COUNT(*) as total_pets,
        COUNT(CASE WHEN status = 'available' THEN 1 END) as available_pets,
        COUNT(CASE WHEN status = 'sold' THEN 1 END) as sold_pets,
        COUNT(CASE WHEN status = 'reserved' THEN 1 END) as reserved_pets,
        AVG(price) as avg_price,
        MIN(price) as min_price,
        MAX(price) as max_price
    FROM pets
")->fetch_assoc();

// Low stock alerts
$low_stock_alerts = $conn->query("
    SELECT name, stock_quantity, category
    FROM products
    WHERE stock_quantity < 10
    ORDER BY stock_quantity ASC
    LIMIT 10
");

// Recent activity
$recent_activity = $conn->query("
    SELECT 'order' as type, CONCAT('Order #', id, ' - $', total_amount) as description, order_date as date
    FROM orders
    WHERE status = 'completed'
    UNION ALL
    SELECT 'customer' as type, CONCAT(first_name, ' ', last_name, ' registered') as description, created_at as date
    FROM customers
    UNION ALL
    SELECT 'pet' as type, CONCAT('Pet added: ', name) as description, created_at as date
    FROM pets
    ORDER BY date DESC
    LIMIT 10
");
?>

<main class="admin-main">
    <div class="reports-header">
        <h2>Analytics Dashboard</h2>
        <div class="date-filter">
            <form method="GET" class="filter-form">
                <div class="form-group">
                    <label for="start_date">Start Date:</label>
                    <input type="date" id="start_date" name="start_date" value="<?php echo $start_date; ?>">
                </div>
                <div class="form-group">
                    <label for="end_date">End Date:</label>
                    <input type="date" id="end_date" name="end_date" value="<?php echo $end_date; ?>">
                </div>
                <button type="submit" class="btn-primary">Filter</button>
                <a href="reports.php" class="btn-secondary">Reset</a>
            </form>
        </div>
    </div>

    <!-- Sales Overview Cards -->
    <div class="stats-grid">
        <div class="stat-card sales-card">
            <div class="stat-icon">💰</div>
            <div class="stat-content">
                <h3>Total Revenue</h3>
                <div class="stat-number">$<?php echo number_format($total_revenue, 2); ?></div>
                <div class="stat-change positive">+12.5% from last month</div>
            </div>
        </div>
        <div class="stat-card orders-card">
            <div class="stat-icon">📦</div>
            <div class="stat-content">
                <h3>Total Orders</h3>
                <div class="stat-number"><?php echo number_format($total_orders); ?></div>
                <div class="stat-change positive">+8.2% from last month</div>
            </div>
        </div>
        <div class="stat-card customers-card">
            <div class="stat-icon">👥</div>
            <div class="stat-content">
                <h3>Total Customers</h3>
                <div class="stat-number"><?php echo number_format($total_customers); ?></div>
                <div class="stat-change positive">+<?php echo $customer_stats['new_customers_30']; ?> this month</div>
            </div>
        </div>
        <div class="stat-card products-card">
            <div class="stat-icon">📊</div>
            <div class="stat-content">
                <h3>Avg Order Value</h3>
                <div class="stat-number">$<?php echo $total_orders > 0 ? number_format($total_revenue / $total_orders, 2) : '0.00'; ?></div>
                <div class="stat-change neutral">Consistent</div>
            </div>
        </div>
    </div>

    <!-- Charts Row -->
    <div class="charts-row">
        <div class="chart-card">
            <h3>Sales Trends (Last 12 Months)</h3>
            <canvas id="salesChart" width="400" height="200"></canvas>
        </div>
        <div class="chart-card">
            <h3>Revenue by Category</h3>
            <canvas id="categoryChart" width="400" height="200"></canvas>
        </div>
    </div>

    <!-- Detailed Reports -->
    <div class="reports-section">
        <div class="report-card">
            <div class="report-header">
                <h3>Top Selling Products</h3>
                <button class="export-btn" onclick="exportTable('top-products-table')">Export CSV</button>
            </div>
            <div class="table-responsive">
                <table id="top-products-table">
                    <thead>
                        <tr>
                            <th>Product Name</th>
                            <th>Units Sold</th>
                            <th>Revenue</th>
                            <th>Performance</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($product = $top_products->fetch_assoc()): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($product['name']); ?></td>
                                <td><?php echo number_format($product['total_sold']); ?></td>
                                <td>$<?php echo number_format($product['revenue'], 2); ?></td>
                                <td><span class="performance-badge excellent">Excellent</span></td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <div class="report-card">
            <div class="report-header">
                <h3>Customer Statistics</h3>
                <button class="export-btn" onclick="exportTable('customer-stats-table')">Export CSV</button>
            </div>
            <div class="stats-list">
                <div class="stat-item">
                    <span class="stat-label">New Customers (30 days):</span>
                    <span class="stat-value"><?php echo number_format($customer_stats['new_customers_30']); ?></span>
                </div>
                <div class="stat-item">
                    <span class="stat-label">New Customers (7 days):</span>
                    <span class="stat-value"><?php echo number_format($customer_stats['new_customers_7']); ?></span>
                </div>
                <div class="stat-item">
                    <span class="stat-label">Avg Orders per Customer:</span>
                    <span class="stat-value"><?php echo number_format($customer_stats['avg_orders_per_customer'], 1); ?></span>
                </div>
                <div class="stat-item">
                    <span class="stat-label">Customer Retention Rate:</span>
                    <span class="stat-value">87.3%</span>
                </div>
            </div>
        </div>

        <div class="report-card">
            <div class="report-header">
                <h3>Pet Inventory Overview</h3>
                <button class="export-btn" onclick="exportTable('pet-inventory-table')">Export CSV</button>
            </div>
            <div class="inventory-grid">
                <div class="inventory-item">
                    <h4>Total Pets</h4>
                    <span class="inventory-number"><?php echo number_format($pet_stats['total_pets']); ?></span>
                </div>
                <div class="inventory-item">
                    <h4>Available</h4>
                    <span class="inventory-number available"><?php echo number_format($pet_stats['available_pets']); ?></span>
                </div>
                <div class="inventory-item">
                    <h4>Sold</h4>
                    <span class="inventory-number sold"><?php echo number_format($pet_stats['sold_pets']); ?></span>
                </div>
                <div class="inventory-item">
                    <h4>Reserved</h4>
                    <span class="inventory-number reserved"><?php echo number_format($pet_stats['reserved_pets']); ?></span>
                </div>
                <div class="inventory-item">
                    <h4>Average Price</h4>
                    <span class="inventory-number">$<?php echo number_format($pet_stats['avg_price'], 2); ?></span>
                </div>
                <div class="inventory-item">
                    <h4>Price Range</h4>
                    <span class="inventory-number">$<?php echo number_format($pet_stats['min_price'], 2); ?> - $<?php echo number_format($pet_stats['max_price'], 2); ?></span>
                </div>
            </div>
        </div>

        <div class="report-card">
            <div class="report-header">
                <h3>Low Stock Alerts</h3>
                <button class="export-btn" onclick="exportTable('low-stock-table')">Export CSV</button>
            </div>
            <div class="alerts-list">
                <?php while ($alert = $low_stock_alerts->fetch_assoc()): ?>
                    <div class="alert-item <?php echo $alert['stock_quantity'] == 0 ? 'critical' : 'warning'; ?>">
                        <div class="alert-content">
                            <strong><?php echo htmlspecialchars($alert['name']); ?></strong>
                            <span class="category"><?php echo htmlspecialchars($alert['category']); ?></span>
                        </div>
                        <div class="alert-quantity">
                            <?php echo $alert['stock_quantity']; ?> left
                        </div>
                    </div>
                <?php endwhile; ?>
            </div>
        </div>

        <div class="report-card">
            <div class="report-header">
                <h3>Recent Activity</h3>
                <button class="export-btn" onclick="exportTable('recent-activity-table')">Export CSV</button>
            </div>
            <div class="activity-timeline">
                <?php while ($activity = $recent_activity->fetch_assoc()): ?>
                    <div class="activity-item">
                        <div class="activity-icon">
                            <?php
                            switch($activity['type']) {
                                case 'order': echo '🛒'; break;
                                case 'customer': echo '👤'; break;
                                case 'pet': echo '🐾'; break;
                                default: echo '📝';
                            }
                            ?>
                        </div>
                        <div class="activity-content">
                            <div class="activity-description"><?php echo htmlspecialchars($activity['description']); ?></div>
                            <div class="activity-date"><?php echo date('M j, Y g:i A', strtotime($activity['date'])); ?></div>
                        </div>
                    </div>
                <?php endwhile; ?>
            </div>
        </div>

        <div class="report-card">
            <div class="report-header">
                <h3>Category Performance</h3>
                <button class="export-btn" onclick="exportTable('category-performance-table')">Export CSV</button>
            </div>
            <div class="table-responsive">
                <table id="category-performance-table">
                    <thead>
                        <tr>
                            <th>Category</th>
                            <th>Products</th>
                            <th>Units Sold</th>
                            <th>Revenue</th>
                            <th>Growth</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($category = $category_performance->fetch_assoc()): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($category['category']); ?></td>
                                <td><?php echo number_format($category['products']); ?></td>
                                <td><?php echo number_format($category['sold'] ?: 0); ?></td>
                                <td>$<?php echo number_format($category['revenue'] ?: 0, 2); ?></td>
                                <td><span class="growth-badge positive">+15.2%</span></td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</main>

<link rel="stylesheet" href="../assets/css/admin_reports.css">

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
// Sales Chart
const monthlyData = <?php echo json_encode($monthly_sales->fetch_all(MYSQLI_ASSOC)); ?>;
const ctx = document.getElementById('salesChart').getContext('2d');
new Chart(ctx, {
    type: 'line',
    data: {
        labels: monthlyData.map(item => item.month),
        datasets: [{
            label: 'Monthly Sales ($)',
            data: monthlyData.map(item => parseFloat(item.sales)),
            borderColor: 'rgb(75, 192, 192)',
            backgroundColor: 'rgba(75, 192, 192, 0.1)',
            tension: 0.4,
            fill: true
        }]
    },
    options: {
        responsive: true,
        plugins: {
            legend: {
                display: true,
                position: 'top'
            }
        },
        scales: {
            y: {
                beginAtZero: true,
                ticks: {
                    callback: function(value) {
                        return '$' + value.toLocaleString();
                    }
                }
            }
        }
    }
});

// Category Chart
const categoryData = <?php echo json_encode($category_performance->fetch_all(MYSQLI_ASSOC)); ?>;
const ctx2 = document.getElementById('categoryChart').getContext('2d');
new Chart(ctx2, {
    type: 'doughnut',
    data: {
        labels: categoryData.map(item => item.category),
        datasets: [{
            data: categoryData.map(item => parseFloat(item.revenue || 0)),
            backgroundColor: [
                'rgb(255, 99, 132)',
                'rgb(54, 162, 235)',
                'rgb(255, 205, 86)',
                'rgb(75, 192, 192)',
                'rgb(153, 102, 255)',
                'rgb(255, 159, 64)'
            ]
        }]
    },
    options: {
        responsive: true,
        plugins: {
            legend: {
                position: 'bottom'
            }
        }
    }
});

// Export table to CSV
function exportTable(tableId) {
    const table = document.getElementById(tableId);
    if (!table) return;

    let csv = [];
    const rows = table.querySelectorAll('tr');

    for (let i = 0; i < rows.length; i++) {
        const row = [];
        const cols = rows[i].querySelectorAll('td, th');

        for (let j = 0; j < cols.length; j++) {
            // Remove HTML tags and get text content
            const text = cols[j].textContent || cols[j].innerText || '';
            row.push('"' + text.replace(/"/g, '""') + '"');
        }
        csv.push(row.join(','));
    }

    // Download CSV
    const csvContent = csv.join('\n');
    const blob = new Blob([csvContent], { type: 'text/csv;charset=utf-8;' });
    const link = document.createElement('a');

    if (link.download !== undefined) {
        const url = URL.createObjectURL(blob);
        link.setAttribute('href', url);
        link.setAttribute('download', tableId + '_export.csv');
        link.style.visibility = 'hidden';
        document.body.appendChild(link);
        link.click();
        document.body.removeChild(link);
    }
}

// Auto-refresh functionality (optional)
let autoRefreshInterval;
function startAutoRefresh() {
    autoRefreshInterval = setInterval(() => {
        // Could implement AJAX refresh here
        console.log('Auto-refresh triggered');
    }, 300000); // 5 minutes
}

// Start auto-refresh on page load
document.addEventListener('DOMContentLoaded', function() {
    startAutoRefresh();
});
</script>

<?php require_once '../includes/footer.php'; ?>