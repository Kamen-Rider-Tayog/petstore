<?php
// Get service ID from URL
$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($id <= 0) {
    header('Location: ' . url('services'));
    exit;
}

require_once __DIR__ . '/../../backend/config/database.php';

// Fetch service details
$stmt = $conn->prepare("SELECT * FROM services WHERE id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();
$service = $result->fetch_assoc();
$stmt->close();

if (!$service) {
    header('Location: ' . url('services'));
    exit;
}

// Set page title and meta
$page_title = $service['service_name'] . ' - Service Details';
$page_description = "Learn more about {$service['service_name']}. {$service['description']}";

require_once __DIR__ . '/../../backend/includes/header.php';
?>

<link rel="stylesheet" href="http://localhost/Ria-Pet-Store/assets/css/shop/service_details.css?v=<?php echo ASSET_VERSION; ?>">

<div class="service-details-page">
    <!-- Back navigation -->
    <div class="back-nav">
        <div class="container">
            <a href="<?php echo url('services'); ?>" class="back-link">
                <?php echo icon('arrow-left', 16); ?> Back to Services
            </a>
        </div>
    </div>

    <!-- Service Details Section -->
    <section class="service-details-section">
        <div class="container">
            <div class="service-details-container">
                <!-- Service Info -->
                <div class="service-info">
                    <div class="service-header">
                        <h1><?php echo e($service['service_name']); ?></h1>
                        <span class="service-category-badge"><?php echo ucfirst(e($service['category'])); ?></span>
                    </div>

                    <div class="service-price-section">
                        <div class="price-block">
                            <span class="price-label">Price:</span>
                            <span class="price-value"><?php echo CURRENCY_SYMBOL . number_format($service['price'], 2); ?></span>
                        </div>
                        <div class="duration-block">
                            <span class="duration-label">Duration:</span>
                            <span class="duration-value">
                                <?php echo icon('clock', 16); ?> <?php echo (int)$service['duration_minutes']; ?> minutes
                            </span>
                        </div>
                    </div>

                    <div class="service-description">
                        <h2>About This Service</h2>
                        <p><?php echo nl2br(e($service['description'])); ?></p>
                    </div>

                    <?php if (!empty($service['benefits'])): ?>
                    <div class="service-benefits">
                        <h2>Benefits</h2>
                        <ul>
                            <?php 
                            $benefits = explode("\n", $service['benefits']);
                            foreach ($benefits as $benefit):
                                if (trim($benefit)):
                            ?>
                                <li><?php echo icon('check', 14); ?> <?php echo e($benefit); ?></li>
                            <?php 
                                endif;
                            endforeach; 
                            ?>
                        </ul>
                    </div>
                    <?php endif; ?>

                    <div class="service-actions">
                        <a href="<?php echo url('book_appointment?service_id=' . $service['id']); ?>" class="btn btn-primary btn-large">
                            <?php echo icon('calendar', 18); ?> Book Appointment
                        </a>
                        <a href="<?php echo url('contact?subject=Question about ' . urlencode($service['service_name'])); ?>" class="btn btn-outline btn-large">
                            <?php echo icon('message', 18); ?> Ask a Question
                        </a>
                    </div>

                    <div class="service-share">
                        <span>Share:</span>
                        <a href="#" class="share-link" onclick="window.open('https://www.facebook.com/sharer/sharer.php?u='+encodeURIComponent(window.location.href), 'facebook-share', 'width=580,height=296');return false;">
                            <?php echo icon('facebook', 18); ?>
                        </a>
                        <a href="#" class="share-link" onclick="window.open('https://twitter.com/intent/tweet?text=<?php echo urlencode('Check out ' . $service['service_name'] . '!'); ?>&url='+encodeURIComponent(window.location.href), 'twitter-share', 'width=550,height=235');return false;">
                            <?php echo icon('twitter', 18); ?>
                        </a>
                    </div>
                </div>

                <!-- Sidebar -->
                <aside class="service-sidebar">
                    <div class="sidebar-card">
                        <h3>Quick Info</h3>
                        <ul>
                            <li>
                                <span class="label">Service Name:</span>
                                <span class="value"><?php echo e($service['service_name']); ?></span>
                            </li>
                            <li>
                                <span class="label">Category:</span>
                                <span class="value"><?php echo ucfirst(e($service['category'])); ?></span>
                            </li>
                            <li>
                                <span class="label">Duration:</span>
                                <span class="value"><?php echo (int)$service['duration_minutes']; ?> minutes</span>
                            </li>
                            <li>
                                <span class="label">Price:</span>
                                <span class="value price"><?php echo CURRENCY_SYMBOL . number_format($service['price'], 2); ?></span>
                            </li>
                        </ul>
                        <a href="<?php echo url('book_appointment?service_id=' . $service['id']); ?>" class="btn btn-primary btn-block">
                            <?php echo icon('calendar', 16); ?> Book Now
                        </a>
                    </div>
                </aside>
            </div>
        </div>
    </section>
</div>

<?php require_once __DIR__ . '/../../backend/includes/footer.php'; ?>