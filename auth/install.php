<?php
declare(strict_types=1);

require_once __DIR__ . '/../components/public_layout.php';

$installed = is_file(INSTALL_LOCK);
$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && !$installed) {
    verify_csrf();
    $name = trim((string) ($_POST['name'] ?? ''));
    $email = trim((string) ($_POST['email'] ?? ''));
    $password = (string) ($_POST['password'] ?? '');

    if ($name === '' || !filter_var($email, FILTER_VALIDATE_EMAIL) || strlen($password) < 10) {
        $message = 'Use a valid name, email, and password of at least 10 characters.';
    } else {
        $stmt = db()->prepare('INSERT INTO users (name, email, password_hash, role) VALUES (?, ?, ?, "owner")');
        $stmt->execute([$name, $email, password_hash($password, PASSWORD_DEFAULT)]);
        file_put_contents(INSTALL_LOCK, 'Installed at ' . date('c'));
        log_activity((int) db()->lastInsertId(), 'owner_installed', 'Initial owner account created.');
        redirect_to('/erp/login');
    }
}

public_header('Install Owner', 'Create the first private ERP owner account.');
?>
<section class="form-page">
    <div>
        <p class="eyebrow">One-time setup</p>
        <h1>Create the first Owner account.</h1>
        <p><?= $installed ? 'Setup is already locked. Remove config/installed.lock only if you are intentionally reinstalling.' : 'After creation, this installer locks itself.' ?></p>
    </div>
    <?php if (!$installed): ?>
        <form class="glass-form" method="post">
            <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">
            <label>Name<input name="name" required placeholder="Owner name"></label>
            <label>Email<input type="email" name="email" required placeholder="owner@gghosiery.com"></label>
            <label>Password<input type="password" name="password" required minlength="10" placeholder="Minimum 10 characters"></label>
            <button class="primary-btn" type="submit">Create Owner</button>
            <?php if ($message): ?><p class="form-status error"><?= e($message) ?></p><?php endif; ?>
        </form>
    <?php endif; ?>
</section>
<?php public_footer(); ?>
