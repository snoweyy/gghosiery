<?php
require_once __DIR__ . '/../components/erp_layout.php';
$user = require_role(['owner', 'admin', 'employee']);
$products = db_all('SELECT p.*, i.quantity, i.reorder_level, s.name supplier_name FROM products p LEFT JOIN inventory i ON i.product_id = p.id LEFT JOIN suppliers s ON s.id = p.supplier_id WHERE p.status = "active" ORDER BY p.created_at DESC LIMIT 100');
$suppliers = db_all('SELECT id, name FROM suppliers WHERE status = "active" ORDER BY name');
erp_header('Inventory', $user);
?>
<section class="module-grid">
    <form class="panel erp-form" method="post" action="<?= app_url('/api/erp/product_save') ?>" id="productForm" enctype="multipart/form-data">
        <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">
        <input type="hidden" name="product_id" value="">
        <input type="hidden" name="existing_image" value="">
        <div class="form-title-row">
            <h2 id="productFormTitle">Add Product</h2>
            <button class="ghost-btn" type="button" id="productFormReset">Clear</button>
        </div>
        <label>SKU<input name="sku" required placeholder="GG-VEST-001"></label>
        <label>Name<input name="name" required placeholder="Cotton Vest Pack"></label>
        <label>Category<input name="category" required value="Hosiery" placeholder="Hosiery"></label>
        <label>Size<input name="size" placeholder="M, L, XL or assorted"></label>
        <label>Color<input name="color" placeholder="White, black, assorted"></label>
        <label>Product Image<input type="file" name="product_image" accept="image/jpeg,image/png,image/webp"></label>
        <label>Supplier<select name="supplier_id"><option value="">None</option><?php foreach ($suppliers as $s): ?><option value="<?= (int) $s['id'] ?>"><?= e($s['name']) ?></option><?php endforeach; ?></select></label>
        <label>Purchase Price<input type="number" step="0.01" name="purchase_price" value="0" placeholder="120.00"></label>
        <label>Sale Price<input type="number" step="0.01" name="sale_price" value="0" placeholder="165.00"></label>
        <label>GST %<input type="number" step="0.01" name="gst_rate" value="18" placeholder="5 or 18"></label>
        <label>Stock<input type="number" name="quantity" value="0" placeholder="48"></label>
        <label>Reorder Level<input type="number" name="reorder_level" value="10" placeholder="12"></label>
        <button class="primary-btn" type="submit">Save Product</button>
    </form>
    <div class="panel table-panel">
        <div class="panel-head"><h2>Product Catalog</h2><input class="table-search" placeholder="Search inventory"></div>
        <table class="data-table">
            <thead><tr><th>Image</th><th>SKU</th><th>Product</th><th>Category</th><th>Stock</th><th>Price</th><th>Actions</th></tr></thead>
            <tbody>
            <?php foreach ($products as $p): ?>
                <?php
                $payload = [
                    'product_id' => (int) $p['id'],
                    'sku' => $p['sku'],
                    'name' => $p['name'],
                    'category' => $p['category'],
                    'size' => $p['size'] ?? '',
                    'color' => $p['color'] ?? '',
                    'existing_image' => $p['image_path'] ?? '',
                    'supplier_id' => $p['supplier_id'] ?? '',
                    'purchase_price' => $p['purchase_price'],
                    'sale_price' => $p['sale_price'],
                    'gst_rate' => $p['gst_rate'],
                    'quantity' => $p['quantity'] ?? 0,
                    'reorder_level' => $p['reorder_level'] ?? 10,
                ];
                ?>
                <tr data-product='<?= e(json_encode($payload, JSON_THROW_ON_ERROR)) ?>'>
                    <td>
                        <?php if (!empty($p['image_path'])): ?>
                            <img class="product-thumb" src="<?= e(app_url($p['image_path'])) ?>" alt="<?= e($p['name']) ?>">
                        <?php else: ?>
                            <span class="product-thumb empty"><i data-lucide="image"></i></span>
                        <?php endif; ?>
                    </td>
                    <td><?= e($p['sku']) ?></td>
                    <td><?= e($p['name']) ?></td>
                    <td><?= e($p['category']) ?></td>
                    <td><?= (int) ($p['quantity'] ?? 0) ?></td>
                    <td>Rs. <?= number_format((float) $p['sale_price'], 2) ?></td>
                    <td>
                        <div class="row-actions">
                            <button class="ghost-btn edit-product" type="button">Edit</button>
                            <form class="delete-form" method="post" action="<?= app_url('/api/erp/product_delete') ?>">
                                <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">
                                <input type="hidden" name="product_id" value="<?= (int) $p['id'] ?>">
                                <button class="danger-btn" type="submit">Delete</button>
                            </form>
                        </div>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</section>
<?php erp_footer(); ?>
