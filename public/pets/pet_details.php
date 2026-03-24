<?php
// Get pet ID from URL
$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($id <= 0) {
    header('Location: ' . url('pets'));
    exit;
}

require_once __DIR__ . '/../../backend/config/database.php';

// Fetch pet details - use pet_image column
$stmt = $conn->prepare("SELECT * FROM pets WHERE id = ? AND pet_status = 'available'");
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();
$pet = $result->fetch_assoc();
$stmt->close();

if (!$pet) {
    header('Location: ' . url('pets'));
    exit;
}

// Set page title and meta
$page_title = $pet['name'] . ' - Pet Details';
$page_description = "Meet {$pet['name']}, a {$pet['age']}-year-old {$pet['species']}. Learn more about this adorable pet and bring them home today!";

require_once __DIR__ . '/../../backend/includes/header.php';
?>

<link rel="stylesheet" href="http://localhost/Ria-Pet-Store/assets/css/pets/pet_details.css?v=<?php echo ASSET_VERSION; ?>">

<div class="pet-details-page">
    <!-- Back navigation -->
    <div class="back-nav">
        <div class="container">
            <a href="<?php echo url('pets'); ?>" class="back-link">
                <?php echo icon('arrow-left', 16); ?> Back to All Pets
            </a>
        </div>
    </div>

    <!-- Pet Details Section -->
    <section class="pet-details-section">
        <div class="container">
            <div class="pet-details-container">
                <!-- Image Gallery -->
                <div class="pet-image-gallery">
                    <div class="main-image">
                        <?php if (!empty($pet['pet_image'])): ?>
                            <img src="<?php echo asset('images/pets/' . $pet['pet_image']); ?>" 
                                 alt="<?php echo e($pet['name']); ?>"
                                 onerror="this.src='<?php echo asset('images/pet-placeholder.jpg'); ?>'">
                        <?php else: ?>
                            <div class="no-image">
                                <?php echo icon('paw', 64); ?>
                                <p>No photo available</p>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Pet Info -->
                <div class="pet-info">
                    <div class="pet-header">
                        <h1><?php echo e($pet['name']); ?></h1>
                        <span class="pet-species-badge"><?php echo ucfirst(e($pet['species'])); ?></span>
                    </div>

                    <div class="pet-details-grid">
                        <div class="detail-item">
                            <span class="detail-icon"><?php echo icon('paw', 20); ?></span>
                            <div class="detail-content">
                                <span class="detail-label">Breed</span>
                                <span class="detail-value"><?php echo e($pet['breed'] ?? 'Mixed'); ?></span>
                            </div>
                        </div>

                        <div class="detail-item">
                            <span class="detail-icon"><?php echo icon('calendar', 20); ?></span>
                            <div class="detail-content">
                                <span class="detail-label">Age</span>
                                <span class="detail-value"><?php echo $pet['age']; ?> <?php echo $pet['age'] == 1 ? 'year' : 'years'; ?> old</span>
                            </div>
                        </div>

                        <div class="detail-item">
                            <span class="detail-icon"><?php echo icon('user', 20); ?></span>
                            <div class="detail-content">
                                <span class="detail-label">Gender</span>
                                <span class="detail-value"><?php echo ucfirst(e($pet['gender'] ?? 'Unknown')); ?></span>
                            </div>
                        </div>

                        <div class="detail-item">
                            <span class="detail-icon"><?php echo icon('heart', 20); ?></span>
                            <div class="detail-content">
                                <span class="detail-label">Status</span>
                                <span class="detail-value">Available for adoption</span>
                            </div>
                        </div>
                        
                        <?php if (!empty($pet['color'])): ?>
                        <div class="detail-item">
                            <span class="detail-icon"><?php echo icon('sun', 20); ?></span>
                            <div class="detail-content">
                                <span class="detail-label">Color</span>
                                <span class="detail-value"><?php echo e($pet['color']); ?></span>
                            </div>
                        </div>
                        <?php endif; ?>
                    </div>

                    <?php if (!empty($pet['description'])): ?>
                    <div class="pet-description">
                        <h2>About <?php echo e($pet['name']); ?></h2>
                        <p><?php echo nl2br(e($pet['description'])); ?></p>
                    </div>
                    <?php endif; ?>

                    <div class="pet-actions">
                        <a href="<?php echo url('book_appointment'); ?>?pet_id=<?php echo $pet['id']; ?>" class="btn btn-primary">
                            <?php echo icon('calendar', 18); ?> Schedule a Visit
                        </a>
                        <a href="<?php echo url('contact'); ?>?subject=Question about <?php echo urlencode($pet['name']); ?>" class="btn btn-outline">
                            <?php echo icon('message', 18); ?> Ask About Me
                        </a>
                    </div>

                    <div class="pet-share">
                        <span>Share:</span>
                        <a href="#" class="share-link" onclick="window.open('https://www.facebook.com/sharer/sharer.php?u='+encodeURIComponent(window.location.href), 'facebook-share', 'width=580,height=296');return false;">
                            <?php echo icon('facebook', 18); ?>
                        </a>
                        <a href="#" class="share-link" onclick="window.open('https://twitter.com/intent/tweet?text=<?php echo urlencode('Meet ' . $pet['name'] . '!'); ?>&url='+encodeURIComponent(window.location.href), 'twitter-share', 'width=550,height=235');return false;">
                            <?php echo icon('twitter', 18); ?>
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </section>
</div>

<?php require_once __DIR__ . '/../../backend/includes/footer.php'; ?>