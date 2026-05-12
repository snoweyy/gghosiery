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
    $websiteUser = [
        'firebase_uid' => (string) $payload['sub'],
        'name' => (string) ($payload['name'] ?? ''),
        'email' => strtolower(trim((string) $payload['email'])),
        'photo_url' => (string) ($payload['picture'] ?? ''),
    ];

    if (!demo_mode()) {
        try {
            $existing = db_row('SELECT id FROM website_users WHERE firebase_uid = ? OR email = ? LIMIT 1', [$websiteUser['firebase_uid'], $websiteUser['email']]);
            if ($existing) {
                db()->prepare('UPDATE website_users SET firebase_uid = ?, name = ?, email = ?, photo_url = ?, last_login_at = NOW() WHERE id = ?')->execute([
                    $websiteUser['firebase_uid'],
                    $websiteUser['name'] ?: null,
                    $websiteUser['email'],
                    $websiteUser['photo_url'] ?: null,
                    $existing['id'],
                ]);
                $websiteUser['id'] = (int) $existing['id'];
            } else {
                db()->prepare('INSERT INTO website_users (firebase_uid, name, email, photo_url, last_login_at) VALUES (?, ?, ?, ?, NOW())')->execute([
                    $websiteUser['firebase_uid'],
                    $websiteUser['name'] ?: null,
                    $websiteUser['email'],
                    $websiteUser['photo_url'] ?: null,
                ]);
                $websiteUser['id'] = (int) db()->lastInsertId();
            }
        } catch (Throwable) {
            // Public login should still work if the optional website_users table has not been migrated yet.
        }
    }

    session_regenerate_id(true);
    $_SESSION['website_user'] = $websiteUser;
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    json_response(['message' => 'Logged in.', 'redirect' => app_url('/')]);
} catch (Throwable) {
    json_response(['error' => 'Google user login failed. Please try again.'], 401);
}
