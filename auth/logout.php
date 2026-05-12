<?php
declare(strict_types=1);

require_once __DIR__ . '/../middleware/security.php';

start_secure_session();
$userId = $_SESSION['user_id'] ?? null;
if ($userId && !demo_mode()) {
    log_activity((int) $userId, 'logout', 'User logged out.');
}
$_SESSION = [];
if (ini_get('session.use_cookies')) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000, $params['path'], $params['domain'], (bool) $params['secure'], (bool) $params['httponly']);
}
session_destroy();
redirect_to('/erp/login');
