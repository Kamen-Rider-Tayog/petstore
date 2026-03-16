<?php
require_once '../includes/header.php';
require_once '../includes/sidebar.php';

// Get today's stats
$today = date('Y-m-d');
$todaySales = $conn->query("SELECT SUM(total_amount) as total FROM orders WHERE DATE(order_date) = '$today'")->fetch_assoc()['total'] ?? 0;
$todayOrders = $conn->query("SELECT COUNT(*) as count FROM orders WHERE DATE(order_date) = '$today'")->fetch_assoc()['count'] ?? 0;

// Get overall stats
$totalProducts = $conn->query("SELECT COUNT(*) as count FROM products")->fetch_assoc()['count'] ?? 0;
$totalCustomers = $conn->query("SELECT COUNT(*) as count FROM customers")->fetch_assoc()['count'] ?? 0;
$lowStock = $conn->query("SELECT COUNT(*) as count FROM products WHERE quantity_in_stock < 10")->fetch_assoc()['count'] ?? 0;
$pendingOrders = $conn->query("SELECT COUNT(*) as count FROM orders WHERE status = 'pending'")->fetch_assoc()['count'] ?? 0;

// Get sales data for last 7 days
$salesData = [];
for ($i = 6; $i >= 0; $i--) {
    $date = date('Y-m-d', strtotime("-$i days"));
    $sales = $conn->query("SELECT SUM(total_amount) as total FROM orders WHERE DATE(order_date) = '$date'")->fetch_assoc()['total'] ?? 0;
    $salesData[] = $sales;
}

// Get order status counts
$statusCounts = $conn->query("SELECT status, COUNT(*) as count FROM orders GROUP BY status");
$statusData = [];
while ($row = $statusCounts->fetch_assoc()) {
    $statusData[$row['status']] = $row['count'];
}

// Get top 5 selling products
$topProducts = $conn->query("
    SELECT p.product_name, SUM(oi.quantity) as total_sold, SUM(oi.price_at_time * oi.quantity) as revenue
    FROM order_items oi
    JOIN products p ON oi.product_id = p.id
    GROUP BY p.id, p.product_name
    ORDER BY total_sold DESC
    LIMIT 5
");

// Get recent orders
$recentOrders = $conn->query("
    SELECT o.id, o.order_date, o.total_amount, o.status,
           c.first_name, c.last_name
    FROM orders o
    JOIN customers c ON o.customer_id = c.id
    ORDER BY o.order_date DESC
    LIMIT 5
");

// Get recent customers
$recentCustomers = $conn->query("
    SELECT id, first_name, last_name, email, created_at
    FROM customers
    ORDER BY created_at DESC
    LIMIT 5
");

// Get low stock products
$lowStockProducts = $conn->query("
    SELECT product_name, quantity_in_stock
    FROM products
    WHERE quantity_in_stock < 10
    ORDER BY quantity_in_stock ASC
    LIMIT 5
");
?>

<main class="admin-main">
    <h2>Dashboard</h2>

    <!-- Stats Cards -->
    <div class="stats-grid">
        <div class="stat-card">
            <h3>Today's Sales</h3>
            <div class="stat-value">₱<?php echo number_format($todaySales, 2); ?></div>
        </div>
        <div class="stat-card">
            <h3>Today's Orders</h3>
            <div class="stat-value"><?php echo $todayOrders; ?></div>
        </div>
        <div class="stat-card">
            <h3>Total Products</h3>
            <div class="stat-value"><?php echo $totalProducts; ?></div>
        </div>
        <div class="stat-card">
            <h3>Total Customers</h3>
            <div class="stat-value"><?php echo $totalCustomers; ?></div>
        </div>
        <div class="stat-card">
            <h3>Low Stock Items</h3>
            <div class="stat-value"><?php echo $lowStock; ?></div>
        </div>
        <div class="stat-card">
            <h3>Pending Orders</h3>
            <div class="stat-value"><?php echo $pendingOrders; ?></div>
        </div>
    </div>

    <!-- Charts -->
    <div class="charts-grid">
        <div class="chart-container">
            <h3>Sales Last 7 Days</h3>
            <canvas id="salesChart"></canvas>
        </div>
        <div class="chart-container">
            <h3>Orders by Status</h3>
            <canvas id="statusChart"></canvas>
        </div>
    </div>

    <!-- Top Products -->
    <div class="chart-container">
        <h3>Top 5 Selling Products</h3>
        <canvas id="productsChart"></canvas>
    </div>

    <!-- Recent Activity -->
    <div class="activity-grid">
        <div class="activity-section">
            <h3>Recent Orders</h3>
            <table class="activity-table">
                <thead>
                    <tr>
                        <th>Order ID</th>
                        <th>Customer</th>
                        <th>Date</th>
                        <th>Total</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($order = $recentOrders->fetch_assoc()): ?>
                        <tr>
                            <td>#<?php echo $order['id']; ?></td>
                            <td><?php echo htmlspecialchars($order['first_name'] . ' ' . $order['last_name']); ?></td>
                            <td><?php echo date('M j, Y', strtotime($order['order_date'])); ?></td>
                            <td>₱<?php echo number_format($order['total_amount'], 2); ?></td>
                            <td><span class="status-badge status-<?php echo $order['status']; ?>"><?php echo ucfirst($order['status']); ?></span></td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>

        <div class="activity-section">
            <h3>Recent Customers</h3>
            <table class="activity-table">
                <thead>
                    <tr>
                        <th>Name</th>
                        <th>Email</th>
                        <th>Joined</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($customer = $recentCustomers->fetch_assoc()): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($customer['first_name'] . ' ' . $customer['last_name']); ?></td>
                            <td><?php echo htmlspecialchars($customer['email']); ?></td>
                            <td><?php echo date('M j, Y', strtotime($customer['created_at'] ?? 'now')); ?></td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>

        <div class="activity-section">
            <h3>Low Stock Alert</h3>
            <table class="activity-table">
                <thead>
                    <tr>
                        <th>Product</th>
                        <th>Stock</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($product = $lowStockProducts->fetch_assoc()): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($product['product_name']); ?></td>
                            <td><span class="low-stock"><?php echo $product['quantity_in_stock']; ?></span></td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    </div>
</main>

<script>
// Sales Chart
const salesCtx = document.getElementById('salesChart').getContext('2d');
new Chart(salesCtx, {
    type: 'line',
    data: {
        labels: <?php echo json_encode(array_map(function($i) { return date('M j', strtotime("-$i days")); }, range(6, 0, -1))); ?>,
        datasets: [{
            label: 'Sales (₱)',
            data: <?php echo json_encode($salesData); ?>,
            borderColor: 'rgb(75, 192, 192)',
            tension: 0.1
        }]
    }
});

// Status Chart
const statusCtx = document.getElementById('statusChart').getContext('2d');
new Chart(statusCtx, {
    type: 'pie',
    data: {
        labels: <?php echo json_encode(array_keys($statusData)); ?>,
        datasets: [{
            data: <?php echo json_encode(array_values($statusData)); ?>,
            backgroundColor: ['#ff6384', '#36a2eb', '#cc65fe', '#ffce56', '#4bc0c0']
        }]
    }
});

// Top Products Chart
const productsCtx = document.getElementById('productsChart').getContext('2d');
new Chart(productsCtx, {
    type: 'bar',
    data: {
        labels: <?php
            $labels = [];
            $data = [];
            $topProducts->data_seek(0);
            while ($product = $topProducts->fetch_assoc()) {
                $labels[] = $product['product_name'];
                $data[] = $product['total_sold'];
            }
            echo json_encode($labels);
        ?>,
        datasets: [{
            label: 'Units Sold',
            data: <?php echo json_encode($data); ?>,
            backgroundColor: 'rgba(54, 162, 235, 0.5)',
            borderColor: 'rgba(54, 162, 235, 1)',
            borderWidth: 1
        }]
    },
    options: {
        scales: {
            y: {
                beginAtZero: true
            }
        }
    }
});
</script>

<?php require_once '../includes/footer.php'; ?>
