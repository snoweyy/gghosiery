<?php
declare(strict_types=1);

require_once __DIR__ . '/../middleware/security.php';
require_once __DIR__ . '/../config/firebase.php';

start_secure_session();
if (current_user()) {
    redirect_to('/erp');
}

$error = (string) ($_SESSION['login_error'] ?? '');
unset($_SESSION['login_error']);
$showPasswordLogin = (isset($_GET['password']) && is_local_request()) || strtolower((string) getenv('ALLOW_PASSWORD_LOGIN')) === 'true';
$firebaseConfig = firebase_config();
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();
    $email = trim((string) ($_POST['email'] ?? ''));
    $password = (string) ($_POST['password'] ?? '');
    if (demo_mode() && $email === 'owner@gghosiery.local' && $password === 'demo12345') {
        session_regenerate_id(true);
        $_SESSION['demo_user'] = true;
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        redirect_to('/erp');
    }

    $user = db_row('SELECT * FROM users WHERE email = ? AND status = "active"', [$email]);

    if ($user && password_verify($password, $user['password_hash'])) {
        session_regenerate_id(true);
        $_SESSION['user_id'] = (int) $user['id'];
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        db()->prepare('UPDATE users SET last_login_at = NOW() WHERE id = ?')->execute([$user['id']]);
        log_activity((int) $user['id'], 'login', 'User logged in.');
        redirect_to('/erp');
    }

    log_activity($user['id'] ?? null, 'login_failed', 'Failed login attempt for ' . $email);
    $error = 'Invalid email or password.';
}
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex,nofollow">
    <title>ERP Login | GG Hosiery</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="<?= app_url('/assets/css/style.css') ?>">
</head>
<body class="login-body">
    <div class="login-card">
        <p class="eyebrow">Private ERP</p>
        <h1>GG Hosiery Login</h1>
        <p class="login-copy">Continue securely with an authorized Google account.</p>
        <button class="google-login-btn" type="button" id="firebaseGoogleLogin">
            <span>G</span>
            Continue with Google
        </button>
        <p class="form-status" id="firebaseLoginStatus" aria-live="polite"></p>
        <?php if ($error): ?><p class="form-status error"><?= e($error) ?></p><?php endif; ?>
        <?php if ($showPasswordLogin): ?>
            <form class="password-fallback-form" method="post">
                <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">
                <?php if (demo_mode()): ?>
                    <p class="form-status">Demo login: owner@gghosiery.local / demo12345</p>
                <?php endif; ?>
                <label>Email<input type="email" name="email" required autocomplete="email" placeholder="owner@gghosiery.local"></label>
                <label>Password<input type="password" name="password" required autocomplete="current-password" placeholder="Enter password"></label>
                <button class="secondary-btn" type="submit">Emergency Password Login</button>
            </form>
        <?php elseif (is_local_request()): ?>
            <a class="fallback-link" href="<?= app_url('/erp/login') ?>?password=1">Use local password fallback</a>
        <?php endif; ?>
        <a href="<?= app_url('/') ?>">Return to website</a>
    </div>
    <script type="module">
        import { initializeApp } from 'https://www.gstatic.com/firebasejs/10.12.5/firebase-app.js';
        import { getAuth, GoogleAuthProvider, signInWithPopup } from 'https://www.gstatic.com/firebasejs/10.12.5/firebase-auth.js';

        const firebaseConfig = <?= json_encode($firebaseConfig, JSON_THROW_ON_ERROR) ?>;
        const app = initializeApp(firebaseConfig);
        const auth = getAuth(app);
        const provider = new GoogleAuthProvider();
        provider.setCustomParameters({ prompt: 'select_account' });

        const button = document.getElementById('firebaseGoogleLogin');
        const status = document.getElementById('firebaseLoginStatus');
        button?.addEventListener('click', async () => {
            status.textContent = 'Opening Google...';
            status.classList.remove('error');
            button.disabled = true;
            try {
                const result = await signInWithPopup(auth, provider);
                const idToken = await result.user.getIdToken();
                status.textContent = 'Verifying ERP access...';
                const response = await fetch('<?= app_url('/auth/firebase-login.php') ?>', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-Token': '<?= e(csrf_token()) ?>',
                    },
                    body: JSON.stringify({ idToken }),
                });
                const payload = await response.json();
                if (!response.ok) throw new Error(payload.error || 'Login failed.');
                window.location.href = payload.redirect || '<?= app_url('/erp') ?>';
            } catch (error) {
                const messages = {
                    'auth/internal-error': 'Firebase popup was blocked by browser/security headers. Refresh and try again.',
                    'auth/popup-closed-by-user': 'Google login popup was closed before completing sign in.',
                    'auth/unauthorized-domain': 'This domain is not authorized in Firebase Authentication settings.',
                    'auth/operation-not-allowed': 'Google sign-in is not enabled in Firebase Authentication.',
                };
                status.textContent = messages[error.code] || error.message;
                status.classList.add('error');
                button.disabled = false;
            }
        });
    </script>
</body>
</html>
