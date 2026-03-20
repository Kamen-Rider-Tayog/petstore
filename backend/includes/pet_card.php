<?php
// Pet card template for filter results
$pet = $pet ?? []; // Ensure $pet is defined

// Safely get values with defaults
$id = isset($pet['id']) ? (int)$pet['id'] : 0;
$name = isset($pet['name']) ? htmlspecialchars($pet['name']) : '';
$species = isset($pet['species']) ? ucfirst(htmlspecialchars($pet['species'])) : 'Pet';
$breed = isset($pet['breed']) ? htmlspecialchars($pet['breed']) : '';
$age = isset($pet['age']) ? (int)$pet['age'] : 0;
$price = isset($pet['price']) ? (float)$pet['price'] : 0;
$image = isset($pet['pet_image']) ? $pet['pet_image'] : '';
$baseUrl = defined('BASE_URL') ? BASE_URL : '/Ria-Pet-Store/';
?>
<div class="pet-card">
    <div class="pet-card-image">
        <?php if (!empty($image)): ?>
            <img src="<?php echo $baseUrl; ?>assets/images/pets/<?php echo urlencode($image); ?>" 
                 alt="<?php echo $name; ?>"
                 onerror="this.src='<?php echo $baseUrl; ?>assets/images/pet-placeholder.jpg'">
        <?php else: ?>
            <div class="no-image">
                <svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <circle cx="12" cy="12" r="2"/>
                    <circle cx="6" cy="8" r="2"/>
                    <circle cx="18" cy="8" r="2"/>
                    <circle cx="6" cy="16" r="2"/>
                    <circle cx="18" cy="16" r="2"/>
                    <path d="M12 20c-2.5 0-4-1.5-4-3s1.5-3 4-3 4 1.5 4 3-1.5 3-4 3z"/>
                </svg>
            </div>
        <?php endif; ?>
        <span class="pet-species-badge"><?php echo $species; ?></span>
    </div>
    <div class="pet-card-body">
        <h3><?php echo $name; ?></h3>
        <?php if (!empty($breed)): ?>
            <p class="pet-breed"><?php echo $breed; ?></p>
        <?php endif; ?>
        <p class="pet-age">
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <rect x="3" y="4" width="18" height="18" rx="2" ry="2"/>
                <line x1="16" y1="2" x2="16" y2="6"/>
                <line x1="8" y1="2" x2="8" y2="6"/>
                <line x1="3" y1="10" x2="21" y2="10"/>
            </svg>
            <?php echo $age; ?> <?php echo $age == 1 ? 'year' : 'years'; ?> old
        </p>
        <?php if ($price > 0): ?>
            <p class="pet-price">₱<?php echo number_format($price, 2); ?></p>
        <?php endif; ?>
    </div>
    <div class="pet-card-footer">
        <a href="<?php echo $baseUrl; ?>pet_details?id=<?php echo $id; ?>" class="btn btn-primary btn-small">
            Meet <?php echo $name; ?> 
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path d="M5 12h14"/>
                <path d="m12 5 7 7-7 7"/>
            </svg>
        </a>
    </div>
</div>