<?php
$page_title = 'Services';
$page_description = 'Professional pet care services - grooming, veterinary checkups, dental cleaning and more.';

require_once __DIR__ . '/../../backend/includes/header.php';
?>

<link rel="stylesheet" href="http://localhost/Ria-Pet-Store/assets/css/shop/services.css?v=<?php echo ASSET_VERSION; ?>">

<div class="services-page">
    <!-- Hero Section -->
    <section class="services-hero">
        <div class="container">
            <h1>Our Services</h1>
            <p>Professional care for your beloved pets</p>
        </div>
    </section>

    <!-- Services Grid -->
    <section class="services-section">
        <div class="container">
            <div class="services-grid">
                <?php
                // Fetch all services from database
                $services = $conn->query("SELECT * FROM services ORDER BY category, service_name");
                
                if ($services->num_rows > 0):
                    while ($service = $services->fetch_assoc()):
                ?>
                <div class="service-card">
                    <div class="service-icon">
                        <?php
                        // Icon based on service category
                        $icon = 'heart';
                        switch (strtolower($service['category'])) {
                            case 'grooming':
                                $icon = 'scissors';
                                break;
                            case 'veterinary':
                            case 'checkup':
                                $icon = 'stethoscope';
                                break;
                            case 'dental':
                                $icon = 'tooth';
                                break;
                            default:
                                $icon = 'heart';
                        }
                        echo icon($icon, 40);
                        ?>
                    </div>
                    <div class="service-card-body">
                        <h3><?php echo e($service['service_name']); ?></h3>
                        <p><?php echo e($service['description']); ?></p>
                        <div class="service-meta">
                            <span class="service-price"><?php echo CURRENCY_SYMBOL . number_format($service['price'], 2); ?></span>
                            <span class="service-duration">
                                <?php echo icon('clock', 14); ?> <?php echo (int)$service['duration_minutes']; ?> min
                            </span>
                        </div>
                    </div>
                    <div class="service-card-footer">
                        <a href="<?php echo url('service_details?id=' . (int)$service['id']); ?>" class="btn btn-primary btn-small">
                            Learn More <?php echo icon('arrow-right', 14); ?>
                        </a>
                    </div>
                </div>
                <?php 
                    endwhile;
                else: 
                ?>
                <div class="no-services">
                    <p>No services available at the moment.</p>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </section>

    <!-- Call to Action -->
    <section class="services-cta">
        <div class="container">
            <div class="cta-content">
                <h2>Need a Custom Service?</h2>
                <p>Contact us for special requests or custom pet care packages</p>
                <a href="<?php echo url('contact'); ?>" class="btn btn-primary">
                    <?php echo icon('message', 18); ?> Contact Us
                </a>
            </div>
        </div>
    </section>
</div>

<?php require_once __DIR__ . '/../../backend/includes/footer.php'; ?>