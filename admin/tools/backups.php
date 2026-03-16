<?php
require_once '../../backend/config/database.php';
require_once '../../backend/includes/auth.php';
require_once '../../backend/includes/header.php';

// Check if user is admin
if (!isset($_SESSION['user_id']) || !isset($_SESSION['is_admin']) || !$_SESSION['is_admin']) {
    header('Location: login.php');
    exit;
}

$message = '';
$message_type = '';

// Handle backup creation
if (isset($_POST['create_backup'])) {
    $backup_name = 'backup_' . date('Y-m-d_H-i-s') . '.sql';
    $backup_path = '../database/backups/' . $backup_name;

    // Ensure backups directory exists
    if (!is_dir('../database/backups/')) {
        mkdir('../database/backups/', 0755, true);
    }

    // Create backup using mysqldump
    $command = "\"C:\\xampp\\mysql\\bin\\mysqldump.exe\" --user=" . DB_USER . " --password=" . DB_PASS . " --host=" . DB_HOST . " " . DB_NAME . " > \"$backup_path\" 2>&1";

    $output = [];
    $return_var = 0;
    exec($command, $output, $return_var);

    if ($return_var === 0) {
        // Save backup info to database
        $stmt = $conn->prepare("INSERT INTO backups (filename, file_path, file_size, created_by, created_at) VALUES (?, ?, ?, ?, NOW())");
        $file_size = filesize($backup_path);
        $stmt->bind_param("sssi", $backup_name, $backup_path, $file_size, $_SESSION['user_id']);
        $stmt->execute();
        $stmt->close();

        $message = 'Backup created successfully!';
        $message_type = 'success';
    } else {
        $message = 'Failed to create backup: ' . implode(' ', $output);
        $message_type = 'error';
    }
}

// Handle backup download
if (isset($_GET['download'])) {
    $backup_id = (int)$_GET['download'];

    $stmt = $conn->prepare("SELECT filename, file_path FROM backups WHERE id = ?");
    $stmt->bind_param("i", $backup_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $backup = $result->fetch_assoc();
    $stmt->close();

    if ($backup && file_exists($backup['file_path'])) {
        header('Content-Type: application/octet-stream');
        header('Content-Disposition: attachment; filename="' . $backup['filename'] . '"');
        header('Content-Length: ' . filesize($backup['file_path']));
        readfile($backup['file_path']);
        exit;
    } else {
        $message = 'Backup file not found.';
        $message_type = 'error';
    }
}

// Handle backup deletion
if (isset($_POST['delete_backup'])) {
    $backup_id = (int)$_POST['delete_backup'];

    $stmt = $conn->prepare("SELECT file_path FROM backups WHERE id = ?");
    $stmt->bind_param("i", $backup_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $backup = $result->fetch_assoc();
    $stmt->close();

    if ($backup) {
        // Delete file
        if (file_exists($backup['file_path'])) {
            unlink($backup['file_path']);
        }

        // Delete from database
        $stmt = $conn->prepare("DELETE FROM backups WHERE id = ?");
        $stmt->bind_param("i", $backup_id);
        $stmt->execute();
        $stmt->close();

        $message = 'Backup deleted successfully!';
        $message_type = 'success';
    } else {
        $message = 'Backup not found.';
        $message_type = 'error';
    }
}

// Get list of backups
$backups = $conn->query("SELECT b.*, u.username FROM backups b LEFT JOIN users u ON b.created_by = u.id ORDER BY b.created_at DESC");
?>

<div class="admin-container">
    <div class="admin-sidebar">
        <?php include '../../backend/includes/sidebar.php'; ?>
    </div>

    <div class="admin-main">
        <div class="admin-header">
            <h1>Database Backups</h1>
            <p>Manage database backups for data safety and recovery</p>
        </div>

        <?php if ($message): ?>
            <div class="alert alert-<?php echo $message_type; ?>">
                <?php echo htmlspecialchars($message); ?>
            </div>
        <?php endif; ?>

        <div class="admin-content">
            <div class="content-section">
                <div class="section-header">
                    <h2>Create New Backup</h2>
                    <p>Create a full database backup that can be downloaded and restored if needed.</p>
                </div>

                <form method="post" class="backup-form">
                    <button type="submit" name="create_backup" class="btn btn-primary">
                        <i class="icon-plus"></i> Create New Backup
                    </button>
                </form>
            </div>

            <div class="content-section">
                <div class="section-header">
                    <h2>Existing Backups</h2>
                    <p>View and download previously created backups.</p>
                </div>

                <?php if ($backups->num_rows > 0): ?>
                    <div class="backups-table">
                        <table class="admin-table">
                            <thead>
                                <tr>
                                    <th>Filename</th>
                                    <th>Size</th>
                                    <th>Created By</th>
                                    <th>Created At</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php while ($backup = $backups->fetch_assoc()): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($backup['filename']); ?></td>
                                        <td><?php echo $backup['file_size'] ? number_format($backup['file_size'] / 1024, 1) . ' KB' : 'Unknown'; ?></td>
                                        <td><?php echo htmlspecialchars($backup['username'] ?? 'System'); ?></td>
                                        <td><?php echo date('M d, Y H:i', strtotime($backup['created_at'])); ?></td>
                                        <td>
                                            <a href="?download=<?php echo $backup['id']; ?>" class="btn btn-small btn-primary">
                                                <i class="icon-download"></i> Download
                                            </a>
                                            <form method="post" style="display: inline;" onsubmit="return confirm('Are you sure you want to delete this backup?')">
                                                <input type="hidden" name="delete_backup" value="<?php echo $backup['id']; ?>">
                                                <button type="submit" class="btn btn-small btn-danger">
                                                    <i class="icon-trash"></i> Delete
                                                </button>
                                            </form>
                                        </td>
                                    </tr>
                                <?php endwhile; ?>
                            </tbody>
                        </table>
                    </div>
                <?php else: ?>
                    <div class="no-backups">
                        <p>No backups found. Create your first backup above.</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<link rel="stylesheet" href="../assets/css/admin_backups.css">

<?php include '../../backend/includes/footer.php'; ?>