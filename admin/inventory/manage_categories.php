<?php
require_once '../includes/header.php';
require_once '../includes/sidebar.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['add_category'])) {
        $name = trim($_POST['category_name']);
        $parent_id = !empty($_POST['parent_id']) ? (int)$_POST['parent_id'] : null;

        if (!empty($name)) {
            $stmt = $conn->prepare("INSERT INTO categories (category_name, parent_id) VALUES (?, ?)");
            $stmt->bind_param('si', $name, $parent_id);
            $stmt->execute();
        }
    } elseif (isset($_POST['edit_category'])) {
        $id = (int)$_POST['category_id'];
        $name = trim($_POST['category_name']);
        $parent_id = !empty($_POST['parent_id']) ? (int)$_POST['parent_id'] : null;

        if (!empty($name)) {
            $stmt = $conn->prepare("UPDATE categories SET category_name = ?, parent_id = ? WHERE id = ?");
            $stmt->bind_param('sii', $name, $parent_id, $id);
            $stmt->execute();
        }
    } elseif (isset($_POST['delete_category'])) {
        $id = (int)$_POST['category_id'];

        // Check if category has products or subcategories
        $has_products = $conn->query("SELECT COUNT(*) as count FROM products WHERE category = (SELECT category_name FROM categories WHERE id = $id)")->fetch_assoc()['count'];
        $has_subs = $conn->query("SELECT COUNT(*) as count FROM categories WHERE parent_id = $id")->fetch_assoc()['count'];

        if ($has_products == 0 && $has_subs == 0) {
            $conn->query("DELETE FROM categories WHERE id = $id");
        } else {
            $error = "Cannot delete category with products or subcategories.";
        }
    }
}

$categories = $conn->query("SELECT c.*, (SELECT COUNT(*) FROM products WHERE category = c.category_name) as product_count FROM categories c ORDER BY parent_id, category_name");
$all_categories = $conn->query("SELECT * FROM categories ORDER BY category_name");
?>

<main class="admin-main">
    <h2>Manage Categories</h2>

    <?php if (isset($error)): ?>
        <div class="alert alert-error"><?php echo $error; ?></div>
    <?php endif; ?>

    <div class="category-form">
        <h3>Add New Category</h3>
        <form method="post">
            <div class="form-row">
                <div class="form-group">
                    <label for="category_name">Category Name:</label>
                    <input type="text" id="category_name" name="category_name" required>
                </div>
                <div class="form-group">
                    <label for="parent_id">Parent Category (optional):</label>
                    <select id="parent_id" name="parent_id">
                        <option value="">None (Main Category)</option>
                        <?php
                        $all_cats = $conn->query("SELECT * FROM categories ORDER BY category_name");
                        while ($cat = $all_cats->fetch_assoc()):
                        ?>
                            <option value="<?php echo $cat['id']; ?>"><?php echo htmlspecialchars($cat['category_name']); ?></option>
                        <?php endwhile; ?>
                    </select>
                </div>
            </div>
            <button type="submit" name="add_category" class="btn btn-primary">Add Category</button>
        </form>
    </div>

    <div class="categories-list">
        <h3>All Categories</h3>
        <div class="table-responsive">
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Category Name</th>
                        <th>Parent</th>
                        <th>Products</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($category = $categories->fetch_assoc()): ?>
                        <tr>
                            <td><?php echo $category['id']; ?></td>
                            <td><?php echo htmlspecialchars($category['category_name']); ?></td>
                            <td>
                                <?php
                                if ($category['parent_id']) {
                                    $parent = $conn->query("SELECT category_name FROM categories WHERE id = " . $category['parent_id'])->fetch_assoc();
                                    echo htmlspecialchars($parent['category_name']);
                                } else {
                                    echo '—';
                                }
                                ?>
                            </td>
                            <td><?php echo $category['product_count']; ?></td>
                            <td>
                                <button onclick="editCategory(<?php echo $category['id']; ?>, '<?php echo addslashes($category['category_name']); ?>', <?php echo $category['parent_id'] ?: 'null'; ?>)" class="btn btn-small">Edit</button>
                                <form method="post" style="display: inline;" onsubmit="return confirm('Delete this category?')">
                                    <input type="hidden" name="category_id" value="<?php echo $category['id']; ?>">
                                    <button type="submit" name="delete_category" class="btn btn-small btn-danger">Delete</button>
                                </form>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Edit Modal -->
    <div id="edit-modal" class="modal" style="display: none;">
        <div class="modal-content">
            <span class="close" onclick="closeModal()">&times;</span>
            <h3>Edit Category</h3>
            <form method="post" id="edit-form">
                <input type="hidden" name="category_id" id="edit_category_id">
                <div class="form-group">
                    <label for="edit_category_name">Category Name:</label>
                    <input type="text" id="edit_category_name" name="category_name" required>
                </div>
                <div class="form-group">
                    <label for="edit_parent_id">Parent Category:</label>
                    <select id="edit_parent_id" name="parent_id">
                        <option value="">None (Main Category)</option>
                        <?php
                        $all_cats->data_seek(0);
                        while ($cat = $all_cats->fetch_assoc()):
                        ?>
                            <option value="<?php echo $cat['id']; ?>"><?php echo htmlspecialchars($cat['category_name']); ?></option>
                        <?php endwhile; ?>
                    </select>
                </div>
                <button type="submit" name="edit_category" class="btn btn-primary">Update Category</button>
            </form>
        </div>
    </div>
</main>

<link rel="stylesheet" href="../assets/css/admin_manage_categories.css">

<script>
function editCategory(id, name, parentId) {
    document.getElementById('edit_category_id').value = id;
    document.getElementById('edit_category_name').value = name;
    document.getElementById('edit_parent_id').value = parentId || '';
    document.getElementById('edit-modal').style.display = 'block';
}

function closeModal() {
    document.getElementById('edit-modal').style.display = 'none';
}
</script>

<?php require_once '../includes/footer.php'; ?>