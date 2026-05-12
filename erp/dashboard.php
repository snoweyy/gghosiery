<?php
require_once __DIR__ . '/../components/erp_layout.php';
$user = require_auth();

$stats = [
    'Revenue' => (float) db_value('SELECT COALESCE(SUM(amount),0) FROM payments'),
    'Orders' => (int) db_value('SELECT COUNT(*) FROM orders'),
    'Customers' => (int) db_value('SELECT COUNT(*) FROM customers WHERE status = "active"'),
    'Low Stock' => (int) db_value('SELECT COUNT(*) FROM inventory WHERE quantity <= reorder_level'),
];
$activities = db_all('SELECT a.*, u.name FROM activity_logs a LEFT JOIN users u ON u.id = a.user_id ORDER BY a.created_at DESC LIMIT 8');
$lowStock = db_all('SELECT p.name, p.sku, i.quantity, i.reorder_level FROM inventory i JOIN products p ON p.id = i.product_id WHERE i.quantity <= i.reorder_level ORDER BY i.quantity ASC LIMIT 6');
$inquiries = db_all('SELECT business_name, contact_name, phone, created_at FROM wholesale_inquiries ORDER BY created_at DESC LIMIT 5');
erp_header('Dashboard', $user);
?>
<section class="stat-grid">
    <?php foreach ($stats as $label => $value): ?>
        <article class="stat-card">
            <p><?= e($label) ?></p>
            <strong><?= $label === 'Revenue' ? '₹' . number_format($value, 2) : number_format($value) ?></strong>
        </article>
    <?php endforeach; ?>
</section>
<section class="dashboard-grid">
    <article class="panel wide">
        <div class="panel-head"><h2>Sales Snapshot</h2><span>Chart.js</span></div>
        <canvas id="salesChart" height="110"></canvas>
    </article>
    <article class="panel">
        <div class="panel-head"><h2>Low Stock</h2><span><?= count($lowStock) ?></span></div>
        <div class="mini-list">
            <?php foreach ($lowStock as $row): ?>
                <div><span><?= e($row['name']) ?></span><b><?= (int) $row['quantity'] ?> left</b></div>
            <?php endforeach; ?>
            <?php if (!$lowStock): ?><p>No low-stock products.</p><?php endif; ?>
        </div>
    </article>
    <article class="panel">
        <div class="panel-head"><h2>Wholesale Inquiries</h2><span>New</span></div>
        <div class="mini-list">
            <?php foreach ($inquiries as $row): ?>
                <div><span><?= e($row['business_name']) ?></span><b><?= e($row['phone']) ?></b></div>
            <?php endforeach; ?>
            <?php if (!$inquiries): ?><p>No inquiries yet.</p><?php endif; ?>
        </div>
    </article>
    <article class="panel wide">
        <div class="panel-head"><h2>Recent Activity</h2><span>Audit</span></div>
        <div class="activity-feed">
            <?php foreach ($activities as $activity): ?>
                <div><b><?= e($activity['action']) ?></b><span><?= e($activity['description']) ?></span><time><?= e($activity['created_at']) ?></time></div>
            <?php endforeach; ?>
        </div>
    </article>
</section>
<script>
window.dashboardSales = [12000, 18000, 24000, 21000, 32000, 42000];
</script>
<?php erp_footer(); ?>
