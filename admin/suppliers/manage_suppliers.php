<?php
require_once '../includes/header.php';
require_once '../includes/sidebar.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['add_supplier'])) {
        $name = trim($_POST['supplier_name']);
        $contact = trim($_POST['contact_person']);
        $email = trim($_POST['email']);
        $phone = trim($_POST['phone']);
        $address = trim($_POST['address']);

        if (!empty($name) && !empty($email)) {
            $stmt = $conn->prepare("INSERT INTO suppliers (supplier_name, contact_person, email, phone, address) VALUES (?, ?, ?, ?, ?)");
            $stmt->bind_param('sssss', $name, $contact, $email, $phone, $address);
            $stmt->execute();
        }
    } elseif (isset($_POST['edit_supplier'])) {
        $id = (int)$_POST['supplier_id'];
        $name = trim($_POST['supplier_name']);
        $contact = trim($_POST['contact_person']);
        $email = trim($_POST['email']);
        $phone = trim($_POST['phone']);
        $address = trim($_POST['address']);

        if (!empty($name) && !empty($email)) {
            $stmt = $conn->prepare("UPDATE suppliers SET supplier_name = ?, contact_person = ?, email = ?, phone = ?, address = ? WHERE id = ?");
            $stmt->bind_param('sssssi', $name, $contact, $email, $phone, $address, $id);
            $stmt->execute();
        }
    } elseif (isset($_POST['delete_supplier'])) {
        $id = (int)$_POST['supplier_id'];

        // Check if supplier has products
        $has_products = $conn->query("SELECT COUNT(*) as count FROM products WHERE supplier_id = $id")->fetch_assoc()['count'];

        if ($has_products == 0) {
            $conn->query("DELETE FROM suppliers WHERE id = $id");
        } else {
            $error = "Cannot delete supplier with associated products.";
        }
    }
}

$suppliers = $conn->query("SELECT s.*, (SELECT COUNT(*) FROM products WHERE supplier_id = s.id) as product_count FROM suppliers s ORDER BY supplier_name");
?>

<main class="admin-main">
    <h2>Manage Suppliers</h2>

    <?php if (isset($error)): ?>
        <div class="alert alert-error"><?php echo $error; ?></div>
    <?php endif; ?>

    <div class="supplier-form">
        <h3>Add New Supplier</h3>
        <form method="post">
            <div class="form-row">
                <div class="form-group">
                    <label for="supplier_name">Supplier Name:</label>
                    <input type="text" id="supplier_name" name="supplier_name" required>
                </div>
                <div class="form-group">
                    <label for="contact_person">Contact Person:</label>
                    <input type="text" id="contact_person" name="contact_person">
                </div>
            </div>
            <div class="form-row">
                <div class="form-group">
                    <label for="email">Email:</label>
                    <input type="email" id="email" name="email" required>
                </div>
                <div class="form-group">
                    <label for="phone">Phone:</label>
                    <input type="tel" id="phone" name="phone">
                </div>
            </div>
            <div class="form-group">
                <label for="address">Address:</label>
                <textarea id="address" name="address" rows="3"></textarea>
            </div>
            <button type="submit" name="add_supplier" class="btn btn-primary">Add Supplier</button>
        </form>
    </div>

    <div class="suppliers-list">
        <h3>All Suppliers</h3>
        <div class="table-responsive">
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Supplier Name</th>
                        <th>Contact Person</th>
                        <th>Email</th>
                        <th>Phone</th>
                        <th>Products</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($supplier = $suppliers->fetch_assoc()): ?>
                        <tr>
                            <td><?php echo $supplier['id']; ?></td>
                            <td><?php echo htmlspecialchars($supplier['supplier_name']); ?></td>
                            <td><?php echo htmlspecialchars($supplier['contact_person'] ?: '—'); ?></td>
                            <td><?php echo htmlspecialchars($supplier['email']); ?></td>
                            <td><?php echo htmlspecialchars($supplier['phone'] ?: '—'); ?></td>
                            <td><?php echo $supplier['product_count']; ?></td>
                            <td>
                                <button onclick="editSupplier(<?php echo $supplier['id']; ?>, '<?php echo addslashes($supplier['supplier_name']); ?>', '<?php echo addslashes($supplier['contact_person']); ?>', '<?php echo addslashes($supplier['email']); ?>', '<?php echo addslashes($supplier['phone']); ?>', '<?php echo addslashes($supplier['address']); ?>')" class="btn btn-small">Edit</button>
                                <form method="post" style="display: inline;" onsubmit="return confirm('Delete this supplier?')">
                                    <input type="hidden" name="supplier_id" value="<?php echo $supplier['id']; ?>">
                                    <button type="submit" name="delete_supplier" class="btn btn-small btn-danger">Delete</button>
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
            <h3>Edit Supplier</h3>
            <form method="post" id="edit-form">
                <input type="hidden" name="supplier_id" id="edit_supplier_id">
                <div class="form-row">
                    <div class="form-group">
                        <label for="edit_supplier_name">Supplier Name:</label>
                        <input type="text" id="edit_supplier_name" name="supplier_name" required>
                    </div>
                    <div class="form-group">
                        <label for="edit_contact_person">Contact Person:</label>
                        <input type="text" id="edit_contact_person" name="contact_person">
                    </div>
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label for="edit_email">Email:</label>
                        <input type="email" id="edit_email" name="email" required>
                    </div>
                    <div class="form-group">
                        <label for="edit_phone">Phone:</label>
                        <input type="tel" id="edit_phone" name="phone">
                    </div>
                </div>
                <div class="form-group">
                    <label for="edit_address">Address:</label>
                    <textarea id="edit_address" name="address" rows="3"></textarea>
                </div>
                <button type="submit" name="edit_supplier" class="btn btn-primary">Update Supplier</button>
            </form>
        </div>
    </div>
</main>

<link rel="stylesheet" href="../assets/css/admin_manage_suppliers.css">

<script>
function editSupplier(id, name, contact, email, phone, address) {
    document.getElementById('edit_supplier_id').value = id;
    document.getElementById('edit_supplier_name').value = name;
    document.getElementById('edit_contact_person').value = contact;
    document.getElementById('edit_email').value = email;
    document.getElementById('edit_phone').value = phone;
    document.getElementById('edit_address').value = address;
    document.getElementById('edit-modal').style.display = 'block';
}

function closeModal() {
    document.getElementById('edit-modal').style.display = 'none';
}
</script>

<?php require_once '../includes/footer.php'; ?>