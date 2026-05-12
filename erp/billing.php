<?php
require_once __DIR__ . '/../components/erp_layout.php';
$user = require_role(['owner', 'admin']);
$invoices = db_all('SELECT i.*, c.name customer_name FROM invoices i JOIN customers c ON c.id = i.customer_id ORDER BY i.created_at DESC LIMIT 100');
$customers = db_all('SELECT id, name FROM customers WHERE status = "active" ORDER BY name');
$products = db_all('SELECT id, sku, name, sale_price, gst_rate FROM products WHERE status = "active" ORDER BY name');
erp_header('Billing', $user);
?>
<section class="module-grid">
    <form class="panel erp-form" method="post" action="<?= app_url('/api/erp/invoice_save') ?>">
        <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">
        <h2>Create GST Invoice</h2>
        <label>Customer<select name="customer_id" required><option value="">Select</option><?php foreach ($customers as $c): ?><option value="<?= (int) $c['id'] ?>"><?= e($c['name']) ?></option><?php endforeach; ?></select></label>
        <label>Product<select name="product_id" required><option value="">Select</option><?php foreach ($products as $p): ?><option value="<?= (int) $p['id'] ?>"><?= e($p['sku'] . ' - ' . $p['name']) ?></option><?php endforeach; ?></select></label>
        <label>Quantity<input type="number" name="quantity" value="1" min="1"></label>
        <button class="primary-btn" type="submit">Generate Invoice</button>
    </form>
    <div class="panel table-panel">
        <div class="panel-head"><h2>Invoices</h2><input class="table-search" placeholder="Search invoices"></div>
        <table class="data-table"><thead><tr><th>Invoice</th><th>Customer</th><th>Date</th><th>Status</th><th>Total</th><th>Actions</th></tr></thead><tbody>
        <?php foreach ($invoices as $i): ?>
            <tr><td><?= e($i['invoice_number']) ?></td><td><?= e($i['customer_name']) ?></td><td><?= e($i['invoice_date']) ?></td><td><?= e($i['status']) ?></td><td>Rs. <?= number_format((float) $i['grand_total'], 2) ?></td>
                <td><div class="row-actions">
                    <form class="inline-update-form" method="post" action="<?= app_url('/api/erp/invoice_update') ?>">
                        <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>"><input type="hidden" name="invoice_id" value="<?= (int) $i['id'] ?>">
                        <select name="status"><option value="draft" <?= $i['status'] === 'draft' ? 'selected' : '' ?>>Draft</option><option value="sent" <?= $i['status'] === 'sent' ? 'selected' : '' ?>>Sent</option><option value="paid" <?= $i['status'] === 'paid' ? 'selected' : '' ?>>Paid</option><option value="cancelled" <?= $i['status'] === 'cancelled' ? 'selected' : '' ?>>Cancelled</option></select>
                        <button class="ghost-btn" type="submit">Update</button>
                    </form>
                    <form class="delete-form" method="post" action="<?= app_url('/api/erp/invoice_delete') ?>"><input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>"><input type="hidden" name="invoice_id" value="<?= (int) $i['id'] ?>"><button class="danger-btn" type="submit">Cancel</button></form>
                </div></td>
            </tr>
        <?php endforeach; ?>
        </tbody></table>
    </div>
</section>
<?php erp_footer(); ?>
