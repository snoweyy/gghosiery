<?php
require_once __DIR__ . '/../components/erp_layout.php';
$user = require_role(['owner']);
$employees = db_all('SELECT * FROM employees WHERE status = "active" ORDER BY created_at DESC LIMIT 100');
erp_header('Employees', $user);
?>
<section class="module-grid">
    <form class="panel erp-form" method="post" action="<?= app_url('/api/erp/employee_save') ?>" id="employeeForm">
        <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">
        <input type="hidden" name="employee_id" value="">
        <div class="form-title-row"><h2 data-form-title>Add Employee</h2><button class="ghost-btn" type="button" data-reset-form="#employeeForm" data-reset-title="Add Employee">Clear</button></div>
        <label>Name<input name="name" required placeholder="Employee name"></label>
        <label>Phone<input name="phone" placeholder="9000000000"></label>
        <label>Designation<input name="designation" placeholder="Sales Executive"></label>
        <label>Salary<input type="number" step="0.01" name="salary" value="0" placeholder="18000"></label>
        <button class="primary-btn" type="submit">Save Employee</button>
    </form>
    <div class="panel table-panel">
        <div class="panel-head"><h2>Employees</h2><input class="table-search" placeholder="Search employees"></div>
        <table class="data-table"><thead><tr><th>Name</th><th>Phone</th><th>Designation</th><th>Salary</th><th>Actions</th></tr></thead><tbody>
        <?php foreach ($employees as $eRow): ?>
            <tr data-record='<?= e(json_encode(['employee_id' => (int) $eRow['id'], 'name' => $eRow['name'], 'phone' => $eRow['phone'], 'designation' => $eRow['designation'], 'salary' => $eRow['salary']], JSON_THROW_ON_ERROR)) ?>'>
                <td><?= e($eRow['name']) ?></td><td><?= e($eRow['phone']) ?></td><td><?= e($eRow['designation']) ?></td><td>Rs. <?= number_format((float) $eRow['salary'], 2) ?></td>
                <td><div class="row-actions"><button class="ghost-btn" type="button" data-edit-target="#employeeForm" data-edit-title="Update Employee">Edit</button><form class="delete-form" method="post" action="<?= app_url('/api/erp/employee_delete') ?>"><input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>"><input type="hidden" name="employee_id" value="<?= (int) $eRow['id'] ?>"><button class="danger-btn" type="submit">Delete</button></form></div></td>
            </tr>
        <?php endforeach; ?>
        </tbody></table>
    </div>
</section>
<?php erp_footer(); ?>
