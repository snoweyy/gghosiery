<?php
require_once __DIR__ . '/../components/erp_layout.php';
$user = require_role(['owner', 'admin']);
$suppliers = db_all('SELECT * FROM suppliers WHERE status = "active" ORDER BY created_at DESC LIMIT 100');
erp_header('Suppliers', $user);
?>
<section class="module-grid">
    <form class="panel erp-form" method="post" action="<?= app_url('/api/erp/supplier_save') ?>" id="supplierForm">
        <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">
        <input type="hidden" name="supplier_id" value="">
        <div class="form-title-row"><h2 data-form-title>Add Supplier</h2><button class="ghost-btn" type="button" data-reset-form="#supplierForm" data-reset-title="Add Supplier">Clear</button></div>
        <label>Name<input name="name" required placeholder="Supplier business name"></label>
        <label>Phone<input name="phone" placeholder="9999999999"></label>
        <label>Email<input type="email" name="email" placeholder="supplier@example.com"></label>
        <label>GSTIN<input name="gstin" placeholder="Optional"></label>
        <label>Address<textarea name="address" rows="3" placeholder="Supplier address"></textarea></label>
        <button class="primary-btn" type="submit">Save Supplier</button>
    </form>
    <div class="panel table-panel">
        <div class="panel-head"><h2>Suppliers</h2><input class="table-search" placeholder="Search suppliers"></div>
        <table class="data-table"><thead><tr><th>Name</th><th>Phone</th><th>Email</th><th>GSTIN</th><th>Actions</th></tr></thead><tbody>
        <?php foreach ($suppliers as $s): ?>
            <tr data-record='<?= e(json_encode(['supplier_id' => (int) $s['id'], 'name' => $s['name'], 'phone' => $s['phone'], 'email' => $s['email'], 'gstin' => $s['gstin'], 'address' => $s['address']], JSON_THROW_ON_ERROR)) ?>'>
                <td><?= e($s['name']) ?></td><td><?= e($s['phone']) ?></td><td><?= e($s['email']) ?></td><td><?= e($s['gstin']) ?></td>
                <td><div class="row-actions"><button class="ghost-btn" type="button" data-edit-target="#supplierForm" data-edit-title="Update Supplier">Edit</button><form class="delete-form" method="post" action="<?= app_url('/api/erp/supplier_delete') ?>"><input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>"><input type="hidden" name="supplier_id" value="<?= (int) $s['id'] ?>"><button class="danger-btn" type="submit">Delete</button></form></div></td>
            </tr>
        <?php endforeach; ?>
        </tbody></table>
    </div>
</section>
<?php erp_footer(); ?>
