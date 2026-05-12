<?php
require_once __DIR__ . '/../components/erp_layout.php';
$user = require_auth();
$notifications = db_all('SELECT * FROM notifications WHERE user_id IS NULL OR user_id = ? ORDER BY created_at DESC LIMIT 100', [$user['id']]);
erp_header('Notifications', $user);
?>
<section class="panel">
    <div class="panel-head"><h2>Notifications</h2><span><?= count($notifications) ?></span></div>
    <div class="activity-feed">
        <?php foreach ($notifications as $n): ?><div><b><?= e($n['title']) ?></b><span><?= e($n['body']) ?></span><time><?= e($n['created_at']) ?></time></div><?php endforeach; ?>
        <?php if (!$notifications): ?><p>No notifications yet.</p><?php endif; ?>
    </div>
</section>
<?php erp_footer(); ?>
