<?php
require_once '../includes/header.php';
require_once '../includes/sidebar.php';
?>

<main class="admin-main">
    <h2>Search Analytics</h2>

    <div class="analytics-grid">
        <div class="analytics-card">
            <h3>Top Searches (Last 30 Days)</h3>
            <table>
                <thead>
                    <tr>
                        <th>Search Term</th>
                        <th>Frequency</th>
                        <th>Avg Results</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $sql = "SELECT search_term, COUNT(*) as frequency, AVG(results_count) as avg_results
                            FROM search_log
                            WHERE search_date >= DATE_SUB(NOW(), INTERVAL 30 DAY)
                            GROUP BY search_term
                            ORDER BY frequency DESC
                            LIMIT 20";
                    $result = $conn->query($sql);
                    while ($row = $result->fetch_assoc()):
                    ?>
                        <tr>
                            <td><?php echo htmlspecialchars($row['search_term']); ?></td>
                            <td><?php echo $row['frequency']; ?></td>
                            <td><?php echo round($row['avg_results'], 1); ?></td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>

        <div class="analytics-card">
            <h3>Searches with No Results</h3>
            <table>
                <thead>
                    <tr>
                        <th>Search Term</th>
                        <th>Frequency</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $sql = "SELECT search_term, COUNT(*) as frequency
                            FROM search_log
                            WHERE results_count = 0
                            GROUP BY search_term
                            ORDER BY frequency DESC
                            LIMIT 20";
                    $result = $conn->query($sql);
                    while ($row = $result->fetch_assoc()):
                    ?>
                        <tr>
                            <td><?php echo htmlspecialchars($row['search_term']); ?></td>
                            <td><?php echo $row['frequency']; ?></td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>

        <div class="analytics-card">
            <h3>Today's Searches</h3>
            <table>
                <thead>
                    <tr>
                        <th>Time</th>
                        <th>Search Term</th>
                        <th>Results</th>
                        <th>IP</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $sql = "SELECT search_term, results_count, ip_address, search_date
                            FROM search_log
                            WHERE DATE(search_date) = CURDATE()
                            ORDER BY search_date DESC
                            LIMIT 50";
                    $result = $conn->query($sql);
                    while ($row = $result->fetch_assoc()):
                    ?>
                        <tr>
                            <td><?php echo date('H:i', strtotime($row['search_date'])); ?></td>
                            <td><?php echo htmlspecialchars($row['search_term']); ?></td>
                            <td><?php echo $row['results_count']; ?></td>
                            <td><?php echo htmlspecialchars($row['ip_address']); ?></td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    </div>
</main>

<link rel="stylesheet" href="../assets/css/admin_search_analytics.css">

<?php require_once '../includes/footer.php'; ?>