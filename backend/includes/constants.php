    <?php
/**
 * Global Constants Configuration
 * Defines all application-wide constants
 */

// Site Information
define('SITE_NAME', 'Ria Pet Store');
define('SITE_URL', 'http://localhost/petstore');
define('SITE_EMAIL', 'info@riapetstore.com');
define('SITE_PHONE', '555-RIA-PETS');

// Database Constants
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'pet_store');

// Pagination Settings
define('ITEMS_PER_PAGE', 12);
define('ADMIN_ITEMS_PER_PAGE', 25);
define('SEARCH_RESULTS_PER_PAGE', 20);

// Upload Settings
define('UPLOAD_MAX_SIZE', 5 * 1024 * 1024); // 5MB in bytes
define('ALLOWED_IMAGE_TYPES', ['jpg', 'jpeg', 'png', 'gif', 'webp']);

// Ensure UPLOAD_PATH is set when constants are loaded standalone
if (!defined('UPLOAD_PATH')) {
    $defaultUploadPath = realpath(__DIR__ . '/../../assets/uploads');
    define('UPLOAD_PATH', $defaultUploadPath ? $defaultUploadPath . '/' : __DIR__ . '/../../assets/uploads/');
}

// Upload Paths (can be overridden in config.php)
define('PET_IMAGES_PATH', UPLOAD_PATH . 'pets/');
define('PRODUCT_IMAGES_PATH', UPLOAD_PATH . 'products/');
define('USER_AVATARS_PATH', UPLOAD_PATH . 'avatars/');

// Currency and Pricing
define('CURRENCY_SYMBOL', '₱');
define('CURRENCY_CODE', 'PHP');
define('TAX_RATE', 0.12); // 12%
define('SHIPPING_FEE', 150.00);

// File Upload Limits
define('MAX_UPLOAD_SIZE', 5 * 1024 * 1024); // 5MB
define('ALLOWED_DOCUMENT_TYPES', ['pdf', 'doc', 'docx']);

// Session and Security
define('SESSION_NAME', 'petstore_session');
define('SESSION_TIMEOUT', 3600);
define('CSRF_TOKEN_LENGTH', 32);
define('PASSWORD_MIN_LENGTH', 8);
define('PASSWORD_RESET_EXPIRY', 3600); // 1 hour

// Date and Time Formats
define('DATE_FORMAT', 'M d, Y');
define('DATETIME_FORMAT', 'M d, Y H:i');
define('MYSQL_DATE_FORMAT', 'Y-m-d');
define('MYSQL_DATETIME_FORMAT', 'Y-m-d H:i:s');

// Email Settings
define('SMTP_HOST', 'localhost');
define('SMTP_PORT', 587);
define('SMTP_USERNAME', '');
define('SMTP_PASSWORD', '');
define('EMAIL_FROM_NAME', SITE_NAME);

// Social Media Links
define('FACEBOOK_URL', '#');
define('TWITTER_URL', '#');
define('INSTAGRAM_URL', '#');

// API Keys (placeholders - replace with actual keys)
define('GOOGLE_MAPS_API_KEY', '');
define('RECAPTCHA_SITE_KEY', '');
define('RECAPTCHA_SECRET_KEY', '');

// Feature Flags
define('ENABLE_NEWSLETTER', true);
define('ENABLE_REVIEWS', true);
define('ENABLE_WISHLIST', false);
define('ENABLE_COMPARE', false);

// Cache Settings
define('CACHE_ENABLED', false);
define('CACHE_DIR', '../cache/');
define('CACHE_EXPIRY', 3600); // 1 hour

// Error Reporting (set to false in production)
define('DEBUG_MODE', true);
define('LOG_ERRORS', true);
define('ERROR_LOG_FILE', '../logs/errors.log');

// Backup Settings
define('BACKUP_DIR', '../database/backups/');
define('BACKUP_RETENTION_DAYS', 30);
define('AUTO_BACKUP_ENABLED', false);
define('AUTO_BACKUP_TIME', '02:00'); // 2 AM

// Performance Settings
define('ENABLE_COMPRESSION', true);
define('ENABLE_MINIFICATION', false);
define('CDN_URL', '');

// Admin Settings
define('ADMIN_EMAIL', 'admin@petstore.com');
define('ADMIN_NOTIFICATIONS', true);

// User Roles
define('ROLE_CUSTOMER', 1);
define('ROLE_EMPLOYEE', 2);
define('ROLE_ADMIN', 3);
define('ROLE_SUPER_ADMIN', 4);

// Order Status
define('ORDER_PENDING', 'pending');
define('ORDER_PROCESSING', 'processing');
define('ORDER_SHIPPED', 'shipped');
define('ORDER_DELIVERED', 'delivered');
define('ORDER_CANCELLED', 'cancelled');

// Appointment Status
define('APPOINTMENT_PENDING', 'pending');
define('APPOINTMENT_CONFIRMED', 'confirmed');
define('APPOINTMENT_COMPLETED', 'completed');
define('APPOINTMENT_CANCELLED', 'cancelled');

// Product Status
define('PRODUCT_ACTIVE', 'active');
define('PRODUCT_INACTIVE', 'inactive');
define('PRODUCT_OUT_OF_STOCK', 'out_of_stock');

// Review Status
define('REVIEW_PENDING', 'pending');
define('REVIEW_APPROVED', 'approved');
define('REVIEW_REJECTED', 'rejected');

// Low Stock Threshold
define('LOW_STOCK_THRESHOLD', 10);

// Timezone
define('DEFAULT_TIMEZONE', 'Asia/Manila');
?>