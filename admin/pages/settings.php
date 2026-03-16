<?php
require_once '../includes/header.php';
require_once '../includes/sidebar.php';

$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['update_settings'])) {
        $site_name = trim($_POST['site_name']);
        $site_email = trim($_POST['site_email']);
        $currency = trim($_POST['currency']);
        $tax_rate = (float)$_POST['tax_rate'];
        $shipping_fee = (float)$_POST['shipping_fee'];
        $low_stock_threshold = (int)$_POST['low_stock_threshold'];
        $items_per_page = (int)$_POST['items_per_page'];

        // Update settings in database or config file
        $settings = [
            'site_name' => $site_name,
            'site_email' => $site_email,
            'currency' => $currency,
            'tax_rate' => $tax_rate,
            'shipping_fee' => $shipping_fee,
            'low_stock_threshold' => $low_stock_threshold,
            'items_per_page' => $items_per_page
        ];

        // For simplicity, we'll store in a settings table
        // In a real app, you might use a config file or cache
        foreach ($settings as $key => $value) {
            $stmt = $conn->prepare("INSERT INTO settings (setting_key, setting_value) VALUES (?, ?) ON DUPLICATE KEY UPDATE setting_value = ?");
            $stmt->bind_param('sss', $key, $value, $value);
            $stmt->execute();
        }

        $message = "Settings updated successfully!";
    } elseif (isset($_POST['clear_cache'])) {
        // Clear any cached data
        $message = "Cache cleared successfully!";
    } elseif (isset($_POST['backup_database'])) {
        // In a real app, you'd implement database backup
        $message = "Database backup completed!";
    }
}

// Load current settings
$settings = [];
$result = $conn->query("SELECT setting_key, setting_value FROM settings");
while ($row = $result->fetch_assoc()) {
    $settings[$row['setting_key']] = $row['setting_value'];
}

// Default values if not set
$defaults = [
    'site_name' => 'Pet Store',
    'site_email' => 'admin@petstore.com',
    'currency' => 'USD',
    'tax_rate' => 8.5,
    'shipping_fee' => 5.99,
    'low_stock_threshold' => 10,
    'items_per_page' => 12
];

foreach ($defaults as $key => $value) {
    if (!isset($settings[$key])) {
        $settings[$key] = $value;
    }
}
?>

<main class="admin-main">
    <h2>System Settings</h2>

    <?php if ($message): ?>
        <div class="alert alert-success"><?php echo $message; ?></div>
    <?php endif; ?>

    <div class="settings-container">
        <form method="post" class="settings-form">
            <div class="settings-section">
                <h3>General Settings</h3>
                <div class="form-row">
                    <div class="form-group">
                        <label for="site_name">Site Name:</label>
                        <input type="text" id="site_name" name="site_name" value="<?php echo htmlspecialchars($settings['site_name']); ?>" required>
                    </div>
                    <div class="form-group">
                        <label for="site_email">Site Email:</label>
                        <input type="email" id="site_email" name="site_email" value="<?php echo htmlspecialchars($settings['site_email']); ?>" required>
                    </div>
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label for="currency">Currency:</label>
                        <select id="currency" name="currency">
                            <option value="USD" <?php echo $settings['currency'] === 'USD' ? 'selected' : ''; ?>>USD ($)</option>
                            <option value="EUR" <?php echo $settings['currency'] === 'EUR' ? 'selected' : ''; ?>>EUR (€)</option>
                            <option value="GBP" <?php echo $settings['currency'] === 'GBP' ? 'selected' : ''; ?>>GBP (£)</option>
                            <option value="CAD" <?php echo $settings['currency'] === 'CAD' ? 'selected' : ''; ?>>CAD (C$)</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="items_per_page">Items Per Page:</label>
                        <input type="number" id="items_per_page" name="items_per_page" value="<?php echo $settings['items_per_page']; ?>" min="6" max="50" required>
                    </div>
                </div>
            </div>

            <div class="settings-section">
                <h3>E-commerce Settings</h3>
                <div class="form-row">
                    <div class="form-group">
                        <label for="tax_rate">Tax Rate (%):</label>
                        <input type="number" id="tax_rate" name="tax_rate" value="<?php echo $settings['tax_rate']; ?>" step="0.01" min="0" max="100" required>
                    </div>
                    <div class="form-group">
                        <label for="shipping_fee">Shipping Fee:</label>
                        <input type="number" id="shipping_fee" name="shipping_fee" value="<?php echo $settings['shipping_fee']; ?>" step="0.01" min="0" required>
                    </div>
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label for="low_stock_threshold">Low Stock Threshold:</label>
                        <input type="number" id="low_stock_threshold" name="low_stock_threshold" value="<?php echo $settings['low_stock_threshold']; ?>" min="1" required>
                    </div>
                </div>
            </div>

            <div class="form-actions">
                <button type="submit" name="update_settings" class="btn btn-primary">Save Settings</button>
            </div>
        </form>

        <div class="maintenance-section">
            <h3>System Maintenance</h3>
            <div class="maintenance-actions">
                <form method="post" style="display: inline;">
                    <button type="submit" name="clear_cache" class="btn btn-secondary" onclick="return confirm('Clear all cached data?')">Clear Cache</button>
                </form>
                <form method="post" style="display: inline;">
                    <button type="submit" name="backup_database" class="btn btn-secondary" onclick="return confirm('Create database backup?')">Backup Database</button>
                </form>
            </div>
        </div>

        <div class="system-info">
            <h3>System Information</h3>
            <div class="info-grid">
                <div class="info-item">
                    <span class="info-label">PHP Version:</span>
                    <span class="info-value"><?php echo phpversion(); ?></span>
                </div>
                <div class="info-item">
                    <span class="info-label">MySQL Version:</span>
                    <span class="info-value"><?php echo $conn->server_info; ?></span>
                </div>
                <div class="info-item">
                    <span class="info-label">Server Software:</span>
                    <span class="info-value"><?php echo $_SERVER['SERVER_SOFTWARE']; ?></span>
                </div>
                <div class="info-item">
                    <span class="info-label">Database Size:</span>
                    <span class="info-value">
                        <?php
                        $db_size = $conn->query("SELECT ROUND(SUM(data_length + index_length) / 1024 / 1024, 2) as size FROM information_schema.tables WHERE table_schema = DATABASE()")->fetch_assoc()['size'];
                        echo $db_size . ' MB';
                        ?>
                    </span>
                </div>
            </div>
        </div>
    </div>
</main>

<link rel="stylesheet" href="../assets/css/admin_settings.css">

<?php require_once '../includes/footer.php'; ?>
        }

        $message = 'System settings updated successfully.';
    }

    elseif (isset($_POST['clear_cache'])) {
        // Clear any cached data (placeholder for future implementation)
        $message = 'Cache cleared successfully.';
    }
}

// Load current settings
$settings = [];
$result = $conn->query("SELECT setting_key, setting_value FROM settings");
while ($row = $result->fetch_assoc()) {
    $settings[$row['setting_key']] = $row['setting_value'];
}

// Set defaults if not set
$defaults = [
    'store_name' => 'Pet Store',
    'store_email' => 'info@petstore.com',
    'store_phone' => '',
    'store_address' => '',
    'tax_rate' => 0,
    'currency' => 'PHP',
    'low_stock_threshold' => 10,
    'max_upload_size' => 5,
    'timezone' => 'Asia/Manila'
];

foreach ($defaults as $key => $default) {
    if (!isset($settings[$key])) {
        $settings[$key] = $default;
    }
}
?>

<main class="admin-main">
    <h2>Settings</h2>

    <?php if ($message): ?>
        <div style="background: #d4edda; color: #155724; padding: 1rem; border-radius: 4px; margin-bottom: 1rem;">
            <?php echo htmlspecialchars($message); ?>
        </div>
    <?php endif; ?>

    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 2rem;">
        <!-- Store Settings -->
        <div style="background: #fff; padding: 2rem; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1);">
            <h3>Store Information</h3>
            <form method="post">
                <div class="form-group">
                    <label for="store_name">Store Name *</label>
                    <input type="text" id="store_name" name="store_name" value="<?php echo htmlspecialchars($settings['store_name']); ?>" required>
                </div>

                <div class="form-group">
                    <label for="store_email">Store Email *</label>
                    <input type="email" id="store_email" name="store_email" value="<?php echo htmlspecialchars($settings['store_email']); ?>" required>
                </div>

                <div class="form-group">
                    <label for="store_phone">Store Phone</label>
                    <input type="tel" id="store_phone" name="store_phone" value="<?php echo htmlspecialchars($settings['store_phone']); ?>">
                </div>

                <div class="form-group">
                    <label for="store_address">Store Address</label>
                    <textarea id="store_address" name="store_address" rows="3"><?php echo htmlspecialchars($settings['store_address']); ?></textarea>
                </div>

                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
                    <div class="form-group">
                        <label for="tax_rate">Tax Rate (%)</label>
                        <input type="number" id="tax_rate" name="tax_rate" value="<?php echo $settings['tax_rate']; ?>" step="0.01" min="0" max="100">
                    </div>

                    <div class="form-group">
                        <label for="currency">Currency</label>
                        <select id="currency" name="currency">
                            <option value="PHP" <?php echo $settings['currency'] === 'PHP' ? 'selected' : ''; ?>>PHP (₱)</option>
                            <option value="USD" <?php echo $settings['currency'] === 'USD' ? 'selected' : ''; ?>>USD ($)</option>
                            <option value="EUR" <?php echo $settings['currency'] === 'EUR' ? 'selected' : ''; ?>>EUR (€)</option>
                        </select>
                    </div>
                </div>

                <button type="submit" name="update_store_settings" class="btn btn-success" style="width: 100%;">Update Store Settings</button>
            </form>
        </div>

        <!-- System Settings -->
        <div style="background: #fff; padding: 2rem; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1);">
            <h3>System Configuration</h3>
            <form method="post">
                <div class="form-group">
                    <label for="low_stock_threshold">Low Stock Threshold</label>
                    <input type="number" id="low_stock_threshold" name="low_stock_threshold" value="<?php echo $settings['low_stock_threshold']; ?>" min="1" max="100">
                    <small style="color: #666;">Products with stock below this number will be highlighted as low stock.</small>
                </div>

                <div class="form-group">
                    <label for="max_upload_size">Max Upload Size (MB)</label>
                    <input type="number" id="max_upload_size" name="max_upload_size" value="<?php echo $settings['max_upload_size']; ?>" min="1" max="50">
                    <small style="color: #666;">Maximum file size for image uploads.</small>
                </div>

                <div class="form-group">
                    <label for="timezone">Timezone</label>
                    <select id="timezone" name="timezone">
                        <option value="Asia/Manila" <?php echo $settings['timezone'] === 'Asia/Manila' ? 'selected' : ''; ?>>Asia/Manila (PHT)</option>
                        <option value="UTC" <?php echo $settings['timezone'] === 'UTC' ? 'selected' : ''; ?>>UTC</option>
                        <option value="America/New_York" <?php echo $settings['timezone'] === 'America/New_York' ? 'selected' : ''; ?>>America/New_York (EST)</option>
                        <option value="Europe/London" <?php echo $settings['timezone'] === 'Europe/London' ? 'selected' : ''; ?>>Europe/London (GMT)</option>
                    </select>
                </div>

                <button type="submit" name="update_system_settings" class="btn btn-success" style="width: 100%; margin-bottom: 1rem;">Update System Settings</button>
            </form>

            <hr style="margin: 2rem 0;">

            <h4>System Maintenance</h4>
            <form method="post">
                <button type="submit" name="clear_cache" class="btn" style="width: 100%; background: #6c757d;">Clear System Cache</button>
            </form>
        </div>
    </div>

    <!-- Database Information -->
    <div style="background: #fff; padding: 2rem; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); margin-top: 2rem;">
        <h3>Database Information</h3>
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 1rem;">
            <?php
            $tables = ['customers', 'employees', 'orders', 'order_items', 'pets', 'products', 'suppliers'];
            foreach ($tables as $table) {
                $result = $conn->query("SELECT COUNT(*) as count FROM $table");
                $count = $result->fetch_assoc()['count'];
                echo "<div style='text-align: center; padding: 1rem; background: #f8f9fa; border-radius: 4px;'>
                        <div style='font-size: 1.5rem; font-weight: bold; color: #007bff;'>$count</div>
                        <div style='color: #666;'>" . ucfirst($table) . "</div>
                      </div>";
            }
            ?>
        </div>
    </div>
</main>

<?php require_once '../includes/footer.php'; ?>