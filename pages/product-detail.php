<?php
require_once __DIR__ . '/../components/public_layout.php';

$productId = (int) ($_GET['id'] ?? 0);
$product = null;
if ($productId > 0) {
    $product = db_row('SELECT p.*, i.quantity, i.reorder_level, s.name supplier_name FROM products p LEFT JOIN inventory i ON i.product_id = p.id LEFT JOIN suppliers s ON s.id = p.supplier_id WHERE p.id = ? AND p.status = "active"', [$productId]);
    if (!$product && demo_mode()) {
        foreach (db_all('SELECT p.*, i.quantity FROM products p LEFT JOIN inventory i ON i.product_id = p.id WHERE p.status = "active"') as $demoProduct) {
            if ((int) $demoProduct['id'] === $productId) {
                $product = $demoProduct;
                break;
            }
        }
    }
}

if (!$product) {
    http_response_code(404);
    require __DIR__ . '/404.php';
    exit;
}

public_header($product['name'], 'View product details, image, price, and availability for ' . $product['name'] . '.');
$cartPayload = [
    'id' => (int) $product['id'],
    'sku' => $product['sku'],
    'name' => $product['name'],
    'category' => $product['category'],
    'price' => (float) $product['sale_price'],
    'stock' => (int) ($product['quantity'] ?? 0),
];
?>
<section class="product-detail-page" data-aos="fade-up">
    <div class="product-detail-media">
        <?php if (!empty($product['image_path'])): ?>
            <img src="<?= e(app_url($product['image_path'])) ?>" alt="<?= e($product['name']) ?>">
        <?php else: ?>
            <div class="product-detail-placeholder"><i data-lucide="shirt"></i></div>
        <?php endif; ?>
    </div>
    <article class="product-detail-info" data-cart-product='<?= e(json_encode($cartPayload, JSON_THROW_ON_ERROR)) ?>'>
        <p class="eyebrow"><?= e($product['category']) ?></p>
        <h1><?= e($product['name']) ?></h1>
        <p class="product-detail-copy">Wholesale-ready product from GG Hosiery. Confirm quantity, sizes, colors, and delivery details before final billing.</p>
        <div class="product-detail-price">
            <strong>Rs. <?= number_format((float) $product['sale_price'], 2) ?></strong>
            <span><?= (int) ($product['quantity'] ?? 0) ?> in stock</span>
        </div>
        <div class="product-spec-grid">
            <div><span>SKU</span><b><?= e($product['sku']) ?></b></div>
            <div><span>Size</span><b><?= e($product['size'] ?: 'Assorted') ?></b></div>
            <div><span>Color</span><b><?= e($product['color'] ?: 'Assorted') ?></b></div>
            <div><span>GST</span><b><?= number_format((float) $product['gst_rate'], 2) ?>%</b></div>
        </div>
        <div class="hero-actions">
            <button class="primary-btn add-to-cart" type="button">Add to Cart</button>
            <a class="secondary-btn" href="<?= app_url('/cart') ?>">Go to Cart</a>
            <a class="secondary-btn" href="<?= app_url('/products') ?>">Back to Products</a>
        </div>
    </article>
</section>
<?php public_footer(); ?>
