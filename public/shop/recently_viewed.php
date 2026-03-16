<?php
require_once '../../backend/config/database.php';
require_once '../../backend/includes/header.php';
<link rel="stylesheet" href="../../assets/css/recently_viewed.css">


$recent = $_SESSION['recently_viewed'] ?? [];

?>

<h1>Recently Viewed Products</h1>

<?php if (empty($recent)): ?>
    <p>You haven't viewed any products yet.</p>
    <p><a href="products">Browse Products</a></p>
<?php else: ?>
    <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(200px, 1fr)); gap: 15px;">
        <?php foreach ($recent as $item): ?>
            <div style="border: 1px solid #ddd; padding: 10px; border-radius: 5px; background: white;">
                <?php $img = $item['image'] ? asset('images/' . $item['image']) : 'https://via.placeholder.com/200x150?text=No+Image'; ?>
                <a href="product_details?id=<?php echo $item['id']; ?>"><img src="<?php echo $img; ?>" alt="<?php echo htmlspecialchars($item['name']); ?>" style="width: 100%; height: 120px; object-fit: cover;" /></a>
                <p style="margin: 10px 0 0;"><a href="product_details?id=<?php echo $item['id']; ?>" style="text-decoration: none; color: inherit;"><strong><?php echo htmlspecialchars($item['name']); ?></strong></a></p>
            </div>
        <?php endforeach; ?>
    </div>
<?php endif; ?>

<?php require_once '../../backend/includes/footer.php'; ?>
