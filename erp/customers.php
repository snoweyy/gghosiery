<?php
require_once __DIR__ . '/../components/erp_layout.php';
$user = require_role(['owner', 'admin', 'employee']);
$customers = db_all('SELECT * FROM customers WHERE status = "active" ORDER BY created_at DESC LIMIT 100');
erp_header('Customers', $user);
?>
<section class="module-grid">
    <form class="panel erp-form" method="post" action="<?= app_url('/api/erp/customer_save') ?>" id="customerForm">
        <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">
        <input type="hidden" name="customer_id" value="">
        <div class="form-title-row"><h2 data-form-title>Add Customer</h2><button class="ghost-btn" type="button" data-reset-form="#customerForm" data-reset-title="Add Customer">Clear</button></div>
        <label>Name<input name="name" required placeholder="Sharma Garments"></label>
        <label>Phone<input name="phone" placeholder="9876543210"></label>
        <label>Email<input type="email" name="email" placeholder="buyer@example.com"></label>
        <label>GSTIN<input name="gstin" placeholder="Optional"></label>
        <label>City<input name="city" placeholder="Delhi"></label>
        <label>Address<textarea name="address" rows="3" placeholder="Shop address"></textarea></label>
        <button class="primary-btn" type="submit">Save Customer</button>
    </form>
    <div class="panel table-panel">
        <div class="panel-head"><h2>Customers</h2><input class="table-search" placeholder="Search customers"></div>
        <table class="data-table"><thead><tr><th>Name</th><th>Phone</th><th>City</th><th>GSTIN</th><th>Actions</th></tr></thead><tbody>
        <?php foreach ($customers as $c): ?>
            <tr data-record='<?= e(json_encode(['customer_id' => (int) $c['id'], 'name' => $c['name'], 'phone' => $c['phone'], 'email' => $c['email'], 'gstin' => $c['gstin'], 'city' => $c['city'], 'address' => $c['address']], JSON_THROW_ON_ERROR)) ?>'>
                <td><?= e($c['name']) ?></td><td><?= e($c['phone']) ?></td><td><?= e($c['city']) ?></td><td><?= e($c['gstin']) ?></td>
                <td><div class="row-actions"><button class="ghost-btn" type="button" data-edit-target="#customerForm" data-edit-title="Update Customer">Edit</button><form class="delete-form" method="post" action="<?= app_url('/api/erp/customer_delete') ?>"><input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>"><input type="hidden" name="customer_id" value="<?= (int) $c['id'] ?>"><button class="danger-btn" type="submit">Delete</button></form></div></td>
            </tr>
        <?php endforeach; ?>
        </tbody></table>
    </div>
</section>
<?php erp_footer(); ?>
