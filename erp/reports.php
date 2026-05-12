<?php
require_once __DIR__ . '/../components/erp_layout.php';
$user = require_role(['owner', 'admin']);
$sales = db_all('SELECT DATE(created_at) day, COUNT(*) invoices, SUM(grand_total) total FROM invoices GROUP BY DATE(created_at) ORDER BY day DESC LIMIT 14');
$gst = db_row('SELECT COALESCE(SUM(cgst),0) cgst, COALESCE(SUM(sgst),0) sgst, COALESCE(SUM(igst),0) igst FROM invoices');
$payments = db_all('SELECT method, SUM(amount) total FROM payments GROUP BY method');
erp_header('Reports', $user);
?>
<section class="dashboard-grid">
    <article class="panel"><h2>GST Summary</h2><div class="report-stat"><span>CGST</span><b>₹<?= number_format((float) $gst['cgst'], 2) ?></b></div><div class="report-stat"><span>SGST</span><b>₹<?= number_format((float) $gst['sgst'], 2) ?></b></div><div class="report-stat"><span>IGST</span><b>₹<?= number_format((float) $gst['igst'], 2) ?></b></div></article>
    <article class="panel"><h2>Payments</h2><?php foreach ($payments as $p): ?><div class="report-stat"><span><?= e($p['method']) ?></span><b>₹<?= number_format((float) $p['total'], 2) ?></b></div><?php endforeach; ?></article>
    <article class="panel wide table-panel"><h2>Daily Sales</h2><table class="data-table"><thead><tr><th>Date</th><th>Invoices</th><th>Total</th></tr></thead><tbody><?php foreach ($sales as $s): ?><tr><td><?= e($s['day']) ?></td><td><?= (int) $s['invoices'] ?></td><td>₹<?= number_format((float) $s['total'], 2) ?></td></tr><?php endforeach; ?></tbody></table></article>
</section>
<?php erp_footer(); ?>
