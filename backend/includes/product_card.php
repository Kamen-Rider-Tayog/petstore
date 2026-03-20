<?php
// Product card template for filter results
$product = $product ?? []; // Ensure $product is defined

// Safely get values with defaults
$id = isset($product['id']) ? (int)$product['id'] : 0;
$name = isset($product['product_name']) ? htmlspecialchars($product['product_name']) : '';
$category = isset($product['category']) ? ucfirst(htmlspecialchars($product['category'])) : '';
$price = isset($product['price']) ? (float)$product['price'] : 0;
$salePrice = isset($product['sale_price']) ? (float)$product['sale_price'] : 0;
$onSale = !empty($product['on_sale']) && $salePrice > 0;
$quantity = isset($product['quantity_in_stock']) ? (int)$product['quantity_in_stock'] : 0;
$inStock = $quantity > 0;
$baseUrl = defined('BASE_URL') ? BASE_URL : '/Ria-Pet-Store/';
?>
<div class="product-card">
    <div class="product-info">
        <h3>
            <a href="<?php echo $baseUrl; ?>product_details?id=<?php echo $id; ?>">
                <?php echo $name; ?>
            </a>
        </h3>
        <p class="product-category"><?php echo $category; ?></p>
        <div class="product-price">
            <?php if ($onSale): ?>
                <span class="original-price">₱<?php echo number_format($price, 2); ?></span>
                <span class="sale-price">₱<?php echo number_format($salePrice, 2); ?></span>
            <?php else: ?>
                <span class="regular-price">₱<?php echo number_format($price, 2); ?></span>
            <?php endif; ?>
        </div>
        <p class="<?php echo $inStock ? 'in-stock' : 'out-of-stock'; ?>">
            <?php echo $inStock
                ? 'In Stock (' . $quantity . ')'
                : 'Out of Stock'; ?>
        </p>
    </div>
    <div class="product-actions">
        <a href="<?php echo $baseUrl; ?>product_details?id=<?php echo $id; ?>"
           class="btn btn-outline btn-small">
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <circle cx="12" cy="12" r="3"/>
                <path d="M22 12c-2.667 4.667-6 7-10 7s-7.333-2.333-10-7c2.667-4.667 6-7 10-7s7.333 2.333 10 7z"/>
            </svg>
            View
        </a>
        <?php if ($inStock): ?>
            <select id="qty_<?php echo $id; ?>" class="qty-select" aria-label="Quantity">
                <?php for ($i = 1; $i <= min(10, $quantity); $i++): ?>
                    <option value="<?php echo $i; ?>"><?php echo $i; ?></option>
                <?php endfor; ?>
            </select>
            <button data-add-to-cart="<?php echo $id; ?>"
                    class="btn btn-primary btn-small">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <circle cx="8" cy="21" r="1"/>
                    <circle cx="19" cy="21" r="1"/>
                    <path d="M2.05 2.05h2l2.7 12.5a2 2 0 0 0 2 1.5h9.7a2 2 0 0 0 2-1.5l1.6-7.5H5.55"/>
                </svg>
            </button>
        <?php endif; ?>
    </div>
</div>