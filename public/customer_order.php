<?php
require_once '../backend/config/database.php';
require_once '../backend/includes/header.php';

// Fetch all customers for dropdown
$customers = $conn->query("SELECT id, first_name, last_name FROM customers ORDER BY first_name");
?>

<h1>Customer Orders History</h1>

<div>
    <label for="customer">Select Customer:</label>
    <select id="customer" onchange="loadCustomerOrders(this.value)">
        <option value="">-- Select a customer --</option>
        <?php while($customer = $customers->fetch_assoc()): ?>
        <option value="<?php echo $customer['id']; ?>">
            <?php echo htmlspecialchars($customer['first_name'] . ' ' . $customer['last_name']); ?>
        </option>
        <?php endwhile; ?>
    </select>
</div>

<div id="customer-info" style="margin: 20px 0; padding: 15px; background: #f5f5f5; display: none;"></div>

<div id="orders-container" style="display: none;">
    <h2>Order History</h2>
    <table border="1" cellpadding="5">
        <thead>
            <tr>
                <th>Order ID</th>
                <th>Order Date</th>
                <th>Products</th>
                <th>Total</th>
            </tr>
        </thead>
        <tbody id="orders-table"></tbody>
    </table>
    
    <div id="grand-total" style="margin-top: 20px; font-weight: bold;"></div>
</div>

<br>
<a href="index">Back to Home</a>

<script>
function loadCustomerOrders(customerId) {
    if (!customerId) {
        document.getElementById('customer-info').style.display = 'none';
        document.getElementById('orders-container').style.display = 'none';
        return;
    }
    
    fetch('../backend/api/get_customer_orders?customer_id=' + customerId)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Display customer info
                const infoDiv = document.getElementById('customer-info');
                infoDiv.innerHTML = `
                    <h3>${data.customer.first_name} ${data.customer.last_name}</h3>
                    <p>Email: ${data.customer.email}</p>
                    <p>Phone: ${data.customer.phone}</p>
                `;
                infoDiv.style.display = 'block';
                
                // Display orders
                if (data.orders.length > 0) {
                    let tbody = '';
                    let grandTotal = 0;
                    
                    data.orders.forEach(order => {
                        let productsList = '';
                        order.products.forEach(product => {
                            productsList += `${product.product_name} (x${product.quantity}) - ₱${product.subtotal}<br>`;
                        });
                        
                        tbody += `
                            <tr>
                                <td>#${order.id}</td>
                                <td>${order.order_date}</td>
                                <td>${productsList}</td>
                                <td>₱${order.total}</td>
                            </tr>
                        `;
                        grandTotal += parseFloat(order.total);
                    });
                    
                    document.getElementById('orders-table').innerHTML = tbody;
                    document.getElementById('grand-total').innerHTML = 'Grand Total: ₱' + grandTotal.toFixed(2);
                    document.getElementById('orders-container').style.display = 'block';
                } else {
                    document.getElementById('orders-table').innerHTML = '<tr><td colspan="4">No orders found</td></tr>';
                    document.getElementById('grand-total').innerHTML = '';
                    document.getElementById('orders-container').style.display = 'block';
                }
            }
        });
}
</script>

<?php require_once '../backend/includes/footer.php'; ?>