<?php
require_once '../../backend/includes/header.php';

$product_id = (int)($_GET['id'] ?? 0);

if (!$product_id) {
    header('Location: index.php');
    exit;
}

// Get product info
$product = $conn->query("SELECT * FROM products WHERE id = $product_id")->fetch_assoc();
if (!$product) {
    header('Location: index.php');
    exit;
}

// Get reviews
$reviews = $conn->query("SELECT r.*, c.first_name, c.last_name FROM product_reviews r JOIN customers c ON r.customer_id = c.id WHERE r.product_id = $product_id AND r.status = 'approved' ORDER BY r.created_at DESC");

// Calculate average rating
$avg_rating = $conn->query("SELECT AVG(rating) as avg FROM product_reviews WHERE product_id = $product_id AND status = 'approved'")->fetch_assoc()['avg'] ?? 0;
$review_count = $reviews->num_rows;

// Rating distribution
$rating_dist = [];
for ($i = 1; $i <= 5; $i++) {
    $count = $conn->query("SELECT COUNT(*) as count FROM product_reviews WHERE product_id = $product_id AND rating = $i AND status = 'approved'")->fetch_assoc()['count'];
    $rating_dist[$i] = $count;
}
?>

<main>
    <div class="product-header">
        <h1>Reviews for <?= htmlspecialchars($product['product_name']) ?></h1>
        <a href="product_details.php?id=<?= $product_id ?>" class="btn">Back to Product</a>
    </div>

    <div class="reviews-summary">
        <div class="rating-overview">
            <div class="average-rating">
                <span class="rating-number"><?= number_format($avg_rating, 1) ?></span>
                <div class="stars">
                    <?php for ($i = 1; $i <= 5; $i++): ?>
                        <span class="star <?= $i <= round($avg_rating) ? 'filled' : '' ?>">★</span>
                    <?php endfor; ?>
                </div>
                <p class="review-count">Based on <?= $review_count ?> reviews</p>
            </div>
        </div>

        <div class="rating-breakdown">
            <?php for ($i = 5; $i >= 1; $i--): ?>
                <div class="rating-row">
                    <span class="rating-label"><?= $i ?> star</span>
                    <div class="rating-bar">
                        <div class="rating-fill" style="width: <?= $review_count > 0 ? ($rating_dist[$i] / $review_count) * 100 : 0 ?>%"></div>
                    </div>
                    <span class="rating-count">(<?= $rating_dist[$i] ?>)</span>
                </div>
            <?php endfor; ?>
        </div>
    </div>

    <div class="write-review">
        <?php if (isset($_SESSION['user_id'])): ?>
            <a href="write_review.php?id=<?= $product_id ?>" class="btn btn-primary">Write a Review</a>
        <?php else: ?>
            <p><a href="login.php">Login</a> to write a review</p>
        <?php endif; ?>
    </div>

    <div class="reviews-list">
        <h2>Customer Reviews</h2>

        <?php if ($reviews->num_rows === 0): ?>
            <p>No reviews yet. Be the first to review this product!</p>
        <?php else: ?>
            <?php while ($review = $reviews->fetch_assoc()): ?>
                <div class="review-card">
                    <div class="review-header">
                        <span class="reviewer-name"><?= htmlspecialchars($review['first_name'] . ' ' . $review['last_name']) ?></span>
                        <div class="review-rating">
                            <?php for ($i = 1; $i <= 5; $i++): ?>
                                <span class="star <?= $i <= $review['rating'] ? 'filled' : '' ?>">★</span>
                            <?php endfor; ?>
                        </div>
                        <span class="review-date"><?= date('M j, Y', strtotime($review['created_at'])) ?></span>
                    </div>
                    <div class="review-content">
                        <p><?= nl2br(htmlspecialchars($review['review_text'])) ?></p>
                    </div>
                </div>
            <?php endwhile; ?>
        <?php endif; ?>
    </div>
</main>

<link rel="stylesheet" href="../../assets/css/product_reviews.css">

<?php require_once '../../backend/includes/footer.php'; ?>