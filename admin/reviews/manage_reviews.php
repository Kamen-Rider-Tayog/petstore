<?php
require_once '../includes/header.php';
require_once '../includes/sidebar.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['approve'])) {
        $review_id = (int)$_POST['review_id'];
        $conn->query("UPDATE product_reviews SET status = 'approved' WHERE id = $review_id");

        // Update product rating
        $product_id = $conn->query("SELECT product_id FROM product_reviews WHERE id = $review_id")->fetch_assoc()['product_id'];
        updateProductRating($product_id);
    } elseif (isset($_POST['reject'])) {
        $review_id = (int)$_POST['review_id'];
        $conn->query("UPDATE product_reviews SET status = 'rejected' WHERE id = $review_id");
    } elseif (isset($_POST['delete'])) {
        $review_id = (int)$_POST['review_id'];
        $product_id = $conn->query("SELECT product_id FROM product_reviews WHERE id = $review_id")->fetch_assoc()['product_id'];
        $conn->query("DELETE FROM product_reviews WHERE id = $review_id");
        updateProductRating($product_id);
    }
}

function updateProductRating($product_id) {
    global $conn;
    $avg_rating = $conn->query("SELECT AVG(rating) as avg FROM product_reviews WHERE product_id = $product_id AND status = 'approved'")->fetch_assoc()['avg'] ?? 0;
    $review_count = $conn->query("SELECT COUNT(*) as count FROM product_reviews WHERE product_id = $product_id AND status = 'approved'")->fetch_assoc()['count'];
    $conn->query("UPDATE products SET rating = $avg_rating, review_count = $review_count WHERE id = $product_id");
}

$reviews = $conn->query("SELECT r.*, p.product_name, c.first_name, c.last_name FROM product_reviews r JOIN products p ON r.product_id = p.id JOIN customers c ON r.customer_id = c.id ORDER BY r.created_at DESC");
?>

<main class="admin-main">
    <h2>Manage Reviews</h2>

    <div class="table-responsive">
        <table>
            <thead>
                <tr>
                    <th>Product</th>
                    <th>Customer</th>
                    <th>Rating</th>
                    <th>Review</th>
                    <th>Status</th>
                    <th>Date</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($review = $reviews->fetch_assoc()): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($review['product_name']); ?></td>
                        <td><?php echo htmlspecialchars($review['first_name'] . ' ' . $review['last_name']); ?></td>
                        <td>
                            <div class="rating">
                                <?php for ($i = 1; $i <= 5; $i++): ?>
                                    <span class="star <?php echo $i <= $review['rating'] ? 'filled' : ''; ?>">★</span>
                                <?php endfor; ?>
                            </div>
                        </td>
                        <td><?php echo htmlspecialchars(substr($review['review_text'], 0, 100)) . (strlen($review['review_text']) > 100 ? '...' : ''); ?></td>
                        <td>
                            <span class="status status-<?php echo $review['status']; ?>">
                                <?php echo ucfirst($review['status']); ?>
                            </span>
                        </td>
                        <td><?php echo date('M j, Y', strtotime($review['created_at'])); ?></td>
                        <td>
                            <?php if ($review['status'] === 'pending'): ?>
                                <form method="post" style="display: inline;">
                                    <input type="hidden" name="review_id" value="<?php echo $review['id']; ?>">
                                    <button type="submit" name="approve" class="btn btn-small btn-success">Approve</button>
                                    <button type="submit" name="reject" class="btn btn-small btn-warning">Reject</button>
                                </form>
                            <?php endif; ?>
                            <form method="post" style="display: inline;" onsubmit="return confirm('Delete this review?')">
                                <input type="hidden" name="review_id" value="<?php echo $review['id']; ?>">
                                <button type="submit" name="delete" class="btn btn-small btn-danger">Delete</button>
                            </form>
                        </td>
                    </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>
</main>

<link rel="stylesheet" href="../assets/css/admin_manage_reviews.css">

<?php require_once '../includes/footer.php'; ?>