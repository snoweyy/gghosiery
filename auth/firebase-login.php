<?php
declare(strict_types=1);

require_once __DIR__ . '/../middleware/security.php';
require_once __DIR__ . '/../config/firebase_auth.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    json_response(['error' => 'Method not allowed'], 405);
}

start_secure_session();
$csrf = $_SERVER['HTTP_X_CSRF_TOKEN'] ?? '';
if (!is_string($csrf) || !hash_equals($_SESSION['csrf_token'] ?? '', $csrf)) {
    json_response(['error' => 'Invalid CSRF token. Refresh and try again.'], 419);
}
$body = json_decode((string) file_get_contents('php://input'), true);
$idToken = (string) ($body['idToken'] ?? '');
if ($idToken === '') {
    json_response(['error' => 'Firebase token is required.'], 422);
}

try {
    $payload = verify_firebase_id_token($idToken);
    $email = strtolower(trim((string) $payload['email']));
    if (!erp_email_allowed($email)) {
        log_activity(null, 'firebase_login_denied', 'Denied non-allowlisted Firebase login for ' . $email);
        json_response(['error' => 'This Google account is not allowed for GG Hosiery ERP.'], 403);
    }

    if (demo_mode()) {
        session_regenerate_id(true);
        $_SESSION['demo_user'] = true;
        $_SESSION['auth_provider'] = 'firebase';
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        json_response(['message' => 'Logged in with Firebase demo session.', 'redirect' => app_url('/erp')]);
    }

    $user = db_row('SELECT id, name, email, role, status FROM users WHERE LOWER(email) = ? AND status = "active"', [$email]);
    if (!$user) {
        log_activity(null, 'firebase_login_denied', 'Denied Firebase login for ' . $email);
        json_response(['error' => 'This Google account is not authorized for GG Hosiery ERP.'], 403);
    }

    session_regenerate_id(true);
    $_SESSION['user_id'] = (int) $user['id'];
    $_SESSION['auth_provider'] = 'firebase';
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    db()->prepare('UPDATE users SET last_login_at = NOW() WHERE id = ?')->execute([$user['id']]);
    log_activity((int) $user['id'], 'firebase_login', 'User logged in with Firebase Google Auth.');

    json_response(['message' => 'Logged in.', 'redirect' => app_url('/erp')]);
} catch (Throwable $e) {
    log_activity(null, 'firebase_login_failed', $e->getMessage());
    json_response(['error' => 'Firebase login failed. Please try again.'], 401);
}
