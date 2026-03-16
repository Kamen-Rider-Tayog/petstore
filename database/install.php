<?php
/**
 * PetStore Installation Script
 * Sets up the database and creates default configuration
 */

session_start();

// Prevent access if already installed
if (file_exists('../backend/config/installed.lock')) {
    header('Location: ../public/index.php');
    exit;
}

$message = '';
$message_type = 'success';

try {
    // Database connection
    require_once '../backend/config/database.php';

    // Check if tables exist
    $tables = [
        'appointments', 'cart', 'categories', 'contact_messages',
        'customers', 'employees', 'orders', 'order_items',
        'pets', 'products', 'product_reviews', 'sales',
        'search_log', 'services', 'settings', 'suppliers',
        'users', 'backups'
    ];

    $missing_tables = [];
    foreach ($tables as $table) {
        $result = $conn->query("SHOW TABLES LIKE '$table'");
        if ($result->num_rows === 0) {
            $missing_tables[] = $table;
        }
    }

    if (!empty($missing_tables)) {
        $message = 'Missing tables detected. Please run the database setup first.';
        $message_type = 'error';
    } else {
        // Check if default admin exists
        $result = $conn->query("SELECT id FROM employees WHERE is_admin = 1 LIMIT 1");
        if ($result->num_rows === 0) {
            // Create default admin
            $password_hash = password_hash('admin123', PASSWORD_DEFAULT);
            $stmt = $conn->prepare("INSERT INTO employees (first_name, last_name, email, password, position, is_admin) VALUES (?, ?, ?, ?, ?, ?)");
            $stmt->bind_param("sssssi", $first_name, $last_name, $email, $password_hash, $position, $is_admin);

            $first_name = 'Super';
            $last_name = 'Admin';
            $email = 'admin@petstore.com';
            $position = 'Administrator';
            $is_admin = 1;

            $stmt->execute();
            $stmt->close();
        }

        // Check if default settings exist
        $result = $conn->query("SELECT COUNT(*) as count FROM settings");
        $settings_count = $result->fetch_assoc()['count'];

        if ($settings_count === 0) {
            // Insert default settings
            $default_settings = [
                ['store_name', 'Ria Pet Store'],
                ['store_email', 'info@petstore.com'],
                ['tax_rate', '0'],
                ['currency', 'PHP'],
                ['low_stock_threshold', '10'],
                ['max_upload_size', '5'],
                ['timezone', 'Asia/Manila']
            ];

            $stmt = $conn->prepare("INSERT INTO settings (setting_key, setting_value) VALUES (?, ?)");
            foreach ($default_settings as $setting) {
                $stmt->bind_param("ss", $setting[0], $setting[1]);
                $stmt->execute();
            }
            $stmt->close();
        }

        // Create installed lock file
        file_put_contents('../backend/config/installed.lock', 'Installed on ' . date('Y-m-d H:i:s'));

        $message = 'Installation completed successfully! Default admin credentials: admin@petstore.com / admin123';
    }

} catch (Exception $e) {
    $message = 'Installation failed: ' . $e->getMessage();
    $message_type = 'error';
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>PetStore Installation</title>
    <link rel="stylesheet" href="../assets/css/install.css">
</head>
<body>
    <div class="install-container">
        <h1>PetStore Installation</h1>

        <div class="alert alert-<?php echo $message_type; ?>">
            <?php echo htmlspecialchars($message); ?>
        </div>

        <?php if ($message_type === 'success'): ?>
            <div class="credentials">
                <h3>Default Admin Credentials</h3>
                <p><strong>Email:</strong> admin@petstore.com</p>
                <p><strong>Password:</strong> admin123</p>
                <p><em>Please change these credentials after first login.</em></p>
            </div>

            <a href="../admin/login.php" class="btn">Go to Admin Login</a>
        <?php else: ?>
            <p>Please ensure your database is properly configured and try again.</p>
            <a href="../public/index.php" class="btn">Go to Homepage</a>
        <?php endif; ?>
    </div>
</body>
</html>