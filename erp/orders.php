<?php
require_once __DIR__ . '/../components/erp_layout.php';
$user = require_role(['owner', 'admin', 'employee']);
$orders = db_all('SELECT o.*, c.name customer_name FROM orders o JOIN customers c ON c.id = o.customer_id ORDER BY o.created_at DESC LIMIT 100');
$customers = db_all('SELECT id, name FROM customers WHERE status = "active" ORDER BY name');
$products = db_all('SELECT id, sku, name, sale_price, gst_rate FROM products WHERE status = "active" ORDER BY name');
erp_header('Orders', $user);
?>
<section class="module-grid">
    <form class="panel erp-form" method="post" action="<?= app_url('/api/erp/order_save') ?>">
        <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">
        <h2>Create Order</h2>
        <label>Customer<select name="customer_id" required><option value="">Select</option><?php foreach ($customers as $c): ?><option value="<?= (int) $c['id'] ?>"><?= e($c['name']) ?></option><?php endforeach; ?></select></label>
        <label>Product<select name="product_id" required><option value="">Select</option><?php foreach ($products as $p): ?><option value="<?= (int) $p['id'] ?>"><?= e($p['sku'] . ' - ' . $p['name']) ?></option><?php endforeach; ?></select></label>
        <label>Quantity<input type="number" name="quantity" value="1" min="1"></label>
        <button class="primary-btn" type="submit">Save Order</button>
    </form>
    <div class="panel table-panel">
        <div class="panel-head"><h2>Orders</h2><input class="table-search" placeholder="Search orders"></div>
        <table class="data-table"><thead><tr><th>Order</th><th>Customer</th><th>Status</th><th>Payment</th><th>Total</th><th>Actions</th></tr></thead><tbody>
        <?php foreach ($orders as $o): ?>
            <tr><td><?= e($o['order_number']) ?></td><td><?= e($o['customer_name']) ?></td><td><?= e($o['status']) ?></td><td><?= e($o['payment_status']) ?></td><td>Rs. <?= number_format((float) $o['grand_total'], 2) ?></td>
                <td><div class="row-actions">
                    <form class="inline-update-form" method="post" action="<?= app_url('/api/erp/order_update') ?>">
                        <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>"><input type="hidden" name="order_id" value="<?= (int) $o['id'] ?>">
                        <select name="status"><option value="draft" <?= $o['status'] === 'draft' ? 'selected' : '' ?>>Draft</option><option value="confirmed" <?= $o['status'] === 'confirmed' ? 'selected' : '' ?>>Confirmed</option><option value="packed" <?= $o['status'] === 'packed' ? 'selected' : '' ?>>Packed</option><option value="delivered" <?= $o['status'] === 'delivered' ? 'selected' : '' ?>>Delivered</option><option value="cancelled" <?= $o['status'] === 'cancelled' ? 'selected' : '' ?>>Cancelled</option></select>
                        <select name="payment_status"><option value="unpaid" <?= $o['payment_status'] === 'unpaid' ? 'selected' : '' ?>>Unpaid</option><option value="partial" <?= $o['payment_status'] === 'partial' ? 'selected' : '' ?>>Partial</option><option value="paid" <?= $o['payment_status'] === 'paid' ? 'selected' : '' ?>>Paid</option></select>
                        <button class="ghost-btn" type="submit">Update</button>
                    </form>
                    <form class="delete-form" method="post" action="<?= app_url('/api/erp/order_delete') ?>"><input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>"><input type="hidden" name="order_id" value="<?= (int) $o['id'] ?>"><button class="danger-btn" type="submit">Cancel</button></form>
                </div></td>
            </tr>
        <?php endforeach; ?>
        </tbody></table>
    </div>
</section>
<?php erp_footer(); ?>
