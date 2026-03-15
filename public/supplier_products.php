<?php
require_once '../backend/config/database.php';
require_once '../backend/includes/header.php';

// Get all suppliers with their products
$query = "
    SELECT 
        s.id as supplier_id,
        s.supplier_name,
        s.contact_person,
        s.phone,
        p.id as product_id,
        p.product_name,
        p.category,
        p.price,
        p.quantity_in_stock
    FROM suppliers s
    LEFT JOIN products p ON s.id = p.supplier
    ORDER BY s.supplier_name, p.category
";

$result = $conn->query($query);

// Organize data by supplier
$suppliers = [];
while ($row = $result->fetch_assoc()) {
    $supplier_id = $row['supplier_id'];
    
    if (!isset($suppliers[$supplier_id])) {
        $suppliers[$supplier_id] = [
            'name' => $row['supplier_name'],
            'contact_person' => $row['contact_person'],
            'phone' => $row['phone'],
            'products' => [],
            'total_value' => 0
        ];
    }
    
    if ($row['product_id']) {
        $product_value = $row['price'] * $row['quantity_in_stock'];
        $suppliers[$supplier_id]['products'][] = [
            'name' => $row['product_name'],
            'category' => $row['category'],
            'price' => $row['price'],
            'stock' => $row['quantity_in_stock'],
            'value' => $product_value
        ];
        $suppliers[$supplier_id]['total_value'] += $product_value;
    }
}
?>

<h1>Supplier Products</h1>

<?php foreach($suppliers as $id => $supplier): ?>
<div style="margin-bottom: 30px; padding: 15px; border: 1px solid #ddd; border-radius: 5px;">
    <h2><?php echo htmlspecialchars($supplier['name']); ?></h2>
    <p>
        <strong>Contact:</strong> <?php echo htmlspecialchars($supplier['contact_person']); ?><br>
        <strong>Phone:</strong> <?php echo htmlspecialchars($supplier['phone']); ?>
    </p>
    
    <?php if (!empty($supplier['products'])): ?>
    <table border="1" cellpadding="5" style="width:100%;">
        <tr>
            <th>Product</th>
            <th>Category</th>
            <th>Price</th>
            <th>In Stock</th>
            <th>Total Value</th>
        </tr>
        <?php foreach($supplier['products'] as $product): ?>
        <tr>
            <td><?php echo htmlspecialchars($product['name']); ?></td>
            <td><?php echo htmlspecialchars($product['category']); ?></td>
            <td>₱<?php echo number_format($product['price'], 2); ?></td>
            <td><?php echo $product['stock']; ?></td>
            <td>₱<?php echo number_format($product['value'], 2); ?></td>
        </tr>
        <?php endforeach; ?>
        <tr>
            <td colspan="4" style="text-align: right;"><strong>Total Inventory Value:</strong></td>
            <td><strong>₱<?php echo number_format($supplier['total_value'], 2); ?></strong></td>
        </tr>
    </table>
    <?php else: ?>
    <p>No products from this supplier yet.</p>
    <?php endif; ?>
</div>
<?php endforeach; ?>

<br>
<a href="index">Back to Home</a>

<?php require_once '../backend/includes/footer.php'; ?>