<?php
require_once __DIR__ . '/../components/public_layout.php';
public_header('Products', 'Wholesale hosiery and ladies undergarment product showcase.');

$catalog = db_all('SELECT p.*, i.quantity FROM products p LEFT JOIN inventory i ON i.product_id = p.id WHERE p.status = "active" ORDER BY p.category, p.name LIMIT 24');
$categories = [
    ['Hosiery Essentials', 'Socks, vests, briefs, and fast-moving basics for daily retail demand.'],
    ['Ladies Undergarments', 'Comfort-oriented wholesale range across common sizes and repeat categories.'],
    ['Wholesale Packs', 'Bulk-ready assortments for retailers, distributors, and local shops.'],
    ['Seasonal Stock', 'High-demand products planned around weather, festivals, and retail cycles.'],
];
?>
<section class="page-hero" data-aos="fade-up">
    <p class="eyebrow">Product Showcase</p>
    <h1>Wholesale categories designed for movement.</h1>
    <p>Explore product photos, details, pricing, and stock before adding small orders to cart.</p>
</section>

<section class="product-grid">
    <?php foreach ($categories as $category): ?>
        <article class="product-card" data-aos="fade-up">
            <div class="product-visual"><i data-lucide="package-check"></i></div>
            <h2><?= e($category[0]) ?></h2>
            <p><?= e($category[1]) ?></p>
            <a href="<?= app_url('/inquiry') ?>">Ask for availability</a>
        </article>
    <?php endforeach; ?>
</section>

<section class="catalog-section">
    <div class="panel-head">
        <div>
            <p class="eyebrow">Live Catalog</p>
            <h2>Products available for wholesale inquiry</h2>
        </div>
        <a class="secondary-btn" href="<?= app_url('/inquiry') ?>">Wholesale Inquiry</a>
    </div>
    <div class="catalog-grid">
        <?php foreach ($catalog as $product): ?>
            <?php
            $detailUrl = app_url('/product?id=' . (int) $product['id']);
            $cartData = [
                'id' => (int) $product['id'],
                'sku' => $product['sku'],
                'name' => $product['name'],
                'category' => $product['category'],
                'price' => (float) $product['sale_price'],
                'stock' => (int) ($product['quantity'] ?? 0),
            ];
            ?>
            <article class="catalog-card" data-aos="fade-up" data-cart-product='<?= e(json_encode($cartData, JSON_THROW_ON_ERROR)) ?>'>
                <a class="catalog-media-link" href="<?= e($detailUrl) ?>">
                    <?php if (!empty($product['image_path'])): ?>
                        <img class="catalog-image" src="<?= e(app_url($product['image_path'])) ?>" alt="<?= e($product['name']) ?>">
                    <?php else: ?>
                        <div class="catalog-icon"><i data-lucide="shirt"></i></div>
                    <?php endif; ?>
                </a>
                <div>
                    <span><?= e($product['category']) ?></span>
                    <h3><a href="<?= e($detailUrl) ?>"><?= e($product['name']) ?></a></h3>
                    <p>SKU <?= e($product['sku']) ?><?= !empty($product['size']) ? ' - Size ' . e($product['size']) : '' ?><?= !empty($product['color']) ? ' - ' . e($product['color']) : '' ?></p>
                </div>
                <div class="catalog-meta">
                    <b>Rs. <?= number_format((float) $product['sale_price'], 2) ?></b>
                    <small><?= (int) ($product['quantity'] ?? 0) ?> in stock</small>
                </div>
                <div class="catalog-actions">
                    <a class="secondary-btn" href="<?= e($detailUrl) ?>">View Details</a>
                    <button class="primary-btn add-to-cart" type="button">Add to Cart</button>
                </div>
            </article>
        <?php endforeach; ?>
        <?php if (!$catalog): ?>
            <article class="catalog-card">
                <div class="catalog-icon"><i data-lucide="package-plus"></i></div>
                <div>
                    <span>Catalog</span>
                    <h3>Products will appear here</h3>
                    <p>Add products from the ERP Inventory module to publish them on this page.</p>
                </div>
            </article>
        <?php endif; ?>
    </div>
</section>
<?php public_footer(); ?>
