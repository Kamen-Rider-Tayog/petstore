<?php
require_once '../../backend/includes/header.php';
<link rel="stylesheet" href="../../assets/css/services.css">

require_once '../../backend/config/database.php';

// Fetch services
$services = [];
$serviceStmt = $conn->prepare('SELECT * FROM services ORDER BY category, service_name');
$serviceStmt->execute();
$serviceResult = $serviceStmt->get_result();
while ($row = $serviceResult->fetch_assoc()) {
    $services[] = $row;
}

function formatPrice($value) {
    return '₱' . number_format($value, 2);
}

$categories = array_unique(array_map(fn($s) => $s['category'] ?? 'Uncategorized', $services));
$categories = array_filter($categories);
sort($categories);
?>

<h1>Services</h1>

<div style="display:flex; flex-wrap:wrap; gap:12px; align-items:center; margin-bottom:20px;">
    <div>
        <label for="serviceSearch" style="font-weight:600;">Search:</label>
        <input id="serviceSearch" type="search" placeholder="Search services..." style="padding:6px 10px; width:220px;" />
    </div>
    <div>
        <label for="categoryFilter" style="font-weight:600;">Category:</label>
        <select id="categoryFilter" style="padding:6px 10px;">
            <option value="">All</option>
            <?php foreach ($categories as $cat): ?>
                <option value="<?php echo htmlspecialchars($cat); ?>"><?php echo htmlspecialchars(ucfirst($cat)); ?></option>
            <?php endforeach; ?>
        </select>
    </div>
</div>

<div id="servicesGrid" style="display:grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 18px;">
    <?php foreach ($services as $service): ?>
        <div class="card" style="border:1px solid rgba(0,0,0,0.1); border-radius:8px; padding:16px; background:#fff; display:flex; flex-direction:column;">
            <div style="display:flex; justify-content:space-between; align-items:flex-start; gap:10px;">
                <h3 style="margin:0 0 8px; font-size:1.1rem;"><?php echo htmlspecialchars($service['service_name']); ?></h3>
                <span style="font-size:0.8rem; padding:4px 10px; background:#f1f1f1; border-radius:999px;">
                    <?php echo htmlspecialchars(ucfirst($service['category'] ?? '')); ?>
                </span>
            </div>
            <p style="flex:1; margin:0 0 12px; color:#555;">
                <?php echo nl2br(htmlspecialchars(substr($service['description'] ?? '-', 0, 150))); ?>
                <?php if (strlen($service['description'] ?? '') > 150): ?>…<?php endif; ?>
            </p>
            <div style="display:flex; justify-content:space-between; align-items:center; gap:10px; flex-wrap:wrap;">
                <div style="font-size:0.9rem; color:#333;">
                    <strong>Duration:</strong> <?php echo (int)$service['duration_minutes']; ?> min
                </div>
                <div style="font-size:0.9rem; font-weight:600;">
                    <?php echo formatPrice($service['price']); ?>
                </div>
            </div>
            <a href="book_appointment.php?service_id=<?php echo (int)$service['id']; ?>" class="btn" style="margin-top:14px; align-self:flex-start;">Book Now</a>
        </div>
    <?php endforeach; ?>
</div>

<script>
(function() {
    const servicesGrid = document.getElementById('servicesGrid');
    const searchInput = document.getElementById('serviceSearch');
    const categoryFilter = document.getElementById('categoryFilter');

    function filterServices() {
        const search = searchInput.value.toLowerCase().trim();
        const category = categoryFilter.value;

        Array.from(servicesGrid.children).forEach(card => {
            const title = card.querySelector('h3').textContent.toLowerCase();
            const cat = card.querySelector('span').textContent.toLowerCase();
            const matchesSearch = !search || title.includes(search);
            const matchesCategory = !category || cat === category.toLowerCase();
            card.style.display = (matchesSearch && matchesCategory) ? 'flex' : 'none';
        });
    }

    searchInput.addEventListener('input', filterServices);
    categoryFilter.addEventListener('change', filterServices);
})();
</script>

<?php require_once '../../backend/includes/footer.php'; ?>
