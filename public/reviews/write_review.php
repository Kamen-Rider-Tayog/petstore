<?php
require_once '../../backend/includes/auth.php';
require_once '../../backend/includes/header.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

$product_id = (int)($_GET['id'] ?? 0);
$user_id = $_SESSION['user_id'];

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

// Check if user already reviewed
$existing = $conn->query("SELECT id FROM product_reviews WHERE product_id = $product_id AND customer_id = $user_id")->fetch_assoc();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $rating = (int)($_POST['rating'] ?? 0);
    $review_text = trim($_POST['review_text'] ?? '');

    if ($rating >= 1 && $rating <= 5 && !empty($review_text)) {
        if ($existing) {
            // Update existing review
            $sql = "UPDATE product_reviews SET rating = ?, review_text = ?, created_at = NOW() WHERE product_id = ? AND customer_id = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param('isii', $rating, $review_text, $product_id, $user_id);
        } else {
            // Insert new review
            $sql = "INSERT INTO product_reviews (product_id, customer_id, rating, review_text) VALUES (?, ?, ?, ?)";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param('iiis', $product_id, $user_id, $rating, $review_text);
        }

        if ($stmt->execute()) {
            // Update product rating and review count
            $avg_rating = $conn->query("SELECT AVG(rating) as avg FROM product_reviews WHERE product_id = $product_id AND status = 'approved'")->fetch_assoc()['avg'] ?? 0;
            $review_count = $conn->query("SELECT COUNT(*) as count FROM product_reviews WHERE product_id = $product_id AND status = 'approved'")->fetch_assoc()['count'];

            $conn->query("UPDATE products SET rating = $avg_rating, review_count = $review_count WHERE id = $product_id");

            header('Location: product_reviews.php?id=' . $product_id);
            exit;
        }
    }
}

// Get existing review if any
$review_data = null;
if ($existing) {
    $review_data = $conn->query("SELECT * FROM product_reviews WHERE product_id = $product_id AND customer_id = $user_id")->fetch_assoc();
}
?>

<main>
    <h1>Write a Review</h1>

    <div class="product-info">
        <h2>Reviewing: <?= htmlspecialchars($product['product_name']) ?></h2>
        <a href="product_details.php?id=<?= $product_id ?>" class="btn">Back to Product</a>
    </div>

    <form method="post" class="review-form">
        <div class="form-group">
            <label for="rating">Rating:</label>
            <div class="rating-input">
                <?php for ($i = 1; $i <= 5; $i++): ?>
                    <input type="radio" id="star<?= $i ?>" name="rating" value="<?= $i ?>" <?= ($review_data && $review_data['rating'] == $i) ? 'checked' : '' ?>>
                    <label for="star<?= $i ?>" class="star">★</label>
                <?php endfor; ?>
            </div>
        </div>

        <div class="form-group">
            <label for="review_text">Your Review:</label>
            <textarea id="review_text" name="review_text" rows="6" placeholder="Share your thoughts about this product..." required><?php echo $review_data ? htmlspecialchars($review_data['review_text']) : ''; ?></textarea>
        </div>

        <button type="submit" class="btn btn-primary">
            <?php echo $existing ? 'Update Review' : 'Submit Review'; ?>
        </button>
    </form>
</main>

<link rel="stylesheet" href="../../assets/css/write_review.css">

<script>
document.querySelectorAll('.star').forEach(star => {
    star.addEventListener('click', function() {
        const rating = this.previousElementSibling.value;
        document.querySelectorAll('input[name="rating"]').forEach(input => {
            input.checked = input.value <= rating;
        });
    });
});
</script>

<?php require_once '../../backend/includes/footer.php'; ?>