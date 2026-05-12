<?php
declare(strict_types=1);

require_once __DIR__ . '/../config/database.php';

function start_secure_session(): void
{
    if (session_status() === PHP_SESSION_ACTIVE) {
        return;
    }

    $secure = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off');
    session_set_cookie_params([
        'lifetime' => 0,
        'path' => '/',
        'domain' => '',
        'secure' => $secure,
        'httponly' => true,
        'samesite' => 'Lax',
    ]);
    session_start();
}

function csrf_token(): string
{
    start_secure_session();
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function verify_csrf(): void
{
    start_secure_session();
    $token = $_POST['csrf_token'] ?? $_SERVER['HTTP_X_CSRF_TOKEN'] ?? '';
    if (!is_string($token) || !hash_equals($_SESSION['csrf_token'] ?? '', $token)) {
        http_response_code(419);
        exit('Invalid CSRF token.');
    }
}

function current_user(): ?array
{
    start_secure_session();
    if (!empty($_SESSION['demo_user'])) {
        return demo_user();
    }
    if (empty($_SESSION['user_id'])) {
        return null;
    }
    return db_row('SELECT id, name, email, role, status FROM users WHERE id = ? AND status = "active"', [$_SESSION['user_id']]);
}

function current_website_user(): ?array
{
    start_secure_session();
    return $_SESSION['website_user'] ?? null;
}

function require_auth(): array
{
    $user = current_user();
    if (!$user) {
        redirect_to('/erp/login');
    }
    return $user;
}

function require_api_auth(): array
{
    $user = current_user();
    if (!$user) {
        json_response(['error' => 'Authentication required'], 401);
    }
    return $user;
}

function role_rank(string $role): int
{
    return ['employee' => 1, 'admin' => 2, 'owner' => 3][$role] ?? 0;
}

function require_role(array $roles): array
{
    $user = require_auth();
    if (!in_array($user['role'], $roles, true)) {
        log_activity((int) $user['id'], 'permission_denied', 'Blocked page access: ' . current_path());
        http_response_code(403);
        include __DIR__ . '/../pages/403.php';
        exit;
    }
    return $user;
}

function require_api_role(array $roles): array
{
    $user = require_api_auth();
    if (!in_array($user['role'], $roles, true)) {
        log_activity((int) $user['id'], 'permission_denied', 'Blocked API access: ' . current_path());
        json_response(['error' => 'Permission denied'], 403);
    }
    return $user;
}

function json_response(array $payload, int $status = 200): never
{
    http_response_code($status);
    header('Content-Type: application/json');
    echo json_encode($payload, JSON_THROW_ON_ERROR);
    exit;
}

function log_activity(?int $userId, string $action, string $description): void
{
    if (demo_mode()) {
        return;
    }
    try {
        $stmt = db()->prepare('INSERT INTO activity_logs (user_id, action, description, ip_address, user_agent) VALUES (?, ?, ?, ?, ?)');
        $stmt->execute([
            $userId,
            $action,
            $description,
            $_SERVER['REMOTE_ADDR'] ?? null,
            substr($_SERVER['HTTP_USER_AGENT'] ?? '', 0, 250),
        ]);
    } catch (Throwable) {
        // Activity logging must not break the primary workflow.
    }
}

function active_nav(string $route): string
{
    return str_starts_with(current_path(), $route) ? 'is-active' : '';
}
