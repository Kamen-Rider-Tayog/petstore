<?php
$page_title = 'Available Pets';
$page_description = 'Browse our available pets - dogs, cats, rabbits, birds and more.';

require_once __DIR__ . '/../../backend/includes/header.php';

// Get distinct species for filter - using store_pets
$speciesResult = $conn->query("SELECT DISTINCT species FROM store_pets WHERE pet_status = 'available' ORDER BY species");
$speciesList = [];
while ($row = $speciesResult->fetch_assoc()) {
    $speciesList[] = $row['species'];
}
?>

<link rel="stylesheet" href="http://localhost/Ria-Pet-Store/assets/css/pets/pets.css?v=<?php echo ASSET_VERSION; ?>">

<div class="pets-page">
    <!-- Hero Section -->
    <section class="pets-hero">
        <div class="container">
            <h1>Available Pets</h1>
            <p>Find your new best friend among our loving pets</p>
        </div>
    </section>

    <!-- Filter Section -->
    <section class="filter-section">
        <div class="container">
            <div class="filter-bar">
                <!-- Species Filter (custom dropdown) -->
                <div class="filter-group">
                    <label for="species">Filter by species:</label>
                    <div class="custom-dropdown" id="speciesDropdown">
                        <div class="selected" data-value="all">
                            All Species
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <polyline points="6 9 12 15 18 9"/>
                            </svg>
                        </div>
                        <div class="options">
                            <div class="option selected" data-value="all">All Species</div>
                            <?php foreach ($speciesList as $species): ?>
                                <div class="option" data-value="<?php echo e($species); ?>">
                                    <?php echo ucfirst(e($species)); ?>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>
                
                <!-- Search Filter -->
                <div class="filter-group">
                    <label for="search">Search by name:</label>
                    <input type="text" id="search" class="form-control filter-input" placeholder="Enter pet name...">
                </div>
                
                <!-- Clear Button -->
                <div class="filter-actions">
                    <button id="clearFilters" class="btn btn-outline btn-small">Clear</button>
                </div>
            </div>
            
            <!-- Results Count -->
            <div id="pets-count" class="results-count"></div>
        </div>
    </section>

    <!-- Pets Grid -->
    <section class="pets-section">
        <div class="container">
            <div id="pets-results-area" class="pets-grid">
                <!-- Results loaded via AJAX -->
            </div>
            
            <!-- Load More Container -->
            <div id="loadMoreContainer" class="load-more-container">
                <button id="loadMoreBtn" class="btn btn-outline btn-large">Load More</button>
            </div>
        </div>
    </section>
</div>

<script>
// Clear filters functionality (minimal, since filter.js handles most)
document.getElementById('clearFilters')?.addEventListener('click', function() {
    // Reset species dropdown
    const dropdownSelected = document.querySelector('#speciesDropdown .selected');
    const allOption = document.querySelector('#speciesDropdown .option[data-value="all"]');
    
    if (dropdownSelected && allOption) {
        dropdownSelected.innerHTML = 'All Species <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="6 9 12 15 18 9"/></svg>';
        dropdownSelected.dataset.value = 'all';
        
        document.querySelectorAll('#speciesDropdown .option').forEach(opt => {
            opt.classList.remove('selected');
        });
        allOption.classList.add('selected');
    }
    
    // Clear search
    document.getElementById('search').value = '';
    
    // Trigger filter update
    if (window.filterManager) {
        window.filterManager.resetAndLoad();
    }
});
</script>

<?php require_once __DIR__ . '/../../backend/includes/footer.php'; ?>