<?php
declare(strict_types=1);

const APP_NAME = 'GG Hosiery';
const BUSINESS_PHONE_DISPLAY = '+91 98105 04848';
const BUSINESS_PHONE_E164 = '+919810504848';
const BUSINESS_WHATSAPP_URL = 'https://wa.me/919810504848';
const ERP_PATH = '/erp';
const INSTALL_LOCK = __DIR__ . '/../config/installed.lock';

date_default_timezone_set('Asia/Kolkata');

function app_env(): string
{
    return strtolower((string) (getenv('APP_ENV') ?: 'development'));
}

function is_local_request(): bool
{
    $host = $_SERVER['HTTP_HOST'] ?? '';
    $addr = $_SERVER['REMOTE_ADDR'] ?? '';
    return str_starts_with($host, 'localhost')
        || str_starts_with($host, '127.0.0.1')
        || $addr === '127.0.0.1'
        || $addr === '::1';
}

function apply_security_headers(): void
{
    if (headers_sent()) {
        return;
    }

    header('X-Frame-Options: SAMEORIGIN');
    header('X-Content-Type-Options: nosniff');
    header('Referrer-Policy: strict-origin-when-cross-origin');
    header('Permissions-Policy: camera=(), microphone=(), geolocation=()');
    header('Cross-Origin-Opener-Policy: same-origin-allow-popups');
    header('X-Permitted-Cross-Domain-Policies: none');

    if (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') {
        header('Strict-Transport-Security: max-age=31536000; includeSubDomains; preload');
    }

    $csp = [
        "default-src 'self'",
        "base-uri 'self'",
        "form-action 'self'",
        "frame-ancestors 'self'",
        "object-src 'none'",
        "img-src 'self' data: https:",
        "font-src 'self' https://fonts.gstatic.com data:",
        "style-src 'self' 'unsafe-inline' https://fonts.googleapis.com https://unpkg.com",
        "script-src 'self' 'unsafe-inline' https://cdn.tailwindcss.com https://unpkg.com https://cdnjs.cloudflare.com https://cdn.jsdelivr.net https://www.gstatic.com https://apis.google.com",
        "connect-src 'self' https://identitytoolkit.googleapis.com https://securetoken.googleapis.com https://www.googleapis.com https://firebaseinstallations.googleapis.com https://www.gstatic.com https://apis.google.com",
        "frame-src 'self' https://accounts.google.com https://alwayskhubsoorat-75d0e.firebaseapp.com",
    ];
    header('Content-Security-Policy: ' . implode('; ', $csp));
}

apply_security_headers();

function erp_allowed_login_emails(): array
{
    $raw = getenv('ERP_ALLOWED_LOGIN_EMAILS') ?: 'gandasarthak@gmail.com';
    return array_values(array_filter(array_map(
        static fn (string $email): string => strtolower(trim($email)),
        explode(',', $raw)
    )));
}

function erp_email_allowed(string $email): bool
{
    $allowed = erp_allowed_login_emails();
    return $allowed === [] || in_array(strtolower(trim($email)), $allowed, true);
}

function app_url(string $path = ''): string
{
    $path = '/' . ltrim($path, '/');
    return app_base_path() . ($path === '/' ? '/' : $path);
}

function app_base_path(): string
{
    $configured = getenv('APP_BASE_PATH');
    if (is_string($configured) && $configured !== '') {
        return rtrim('/' . trim($configured, '/'), '/');
    }

    $script = str_replace('\\', '/', $_SERVER['SCRIPT_NAME'] ?? '');
    if (basename($script) !== 'index.php') {
        return '';
    }

    $dir = rtrim(str_replace('\\', '/', dirname($script)), '/');
    if ($dir === '' || $dir === '.') {
        return '';
    }

    return $dir;
}

function redirect_to(string $path): never
{
    if (preg_match('#^https?://#i', $path) || str_starts_with($path, '//')) {
        $path = '/';
    }
    header('Location: ' . app_url($path));
    exit;
}

function e(?string $value): string
{
    return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
}

function current_path(): string
{
    $path = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH);
    $path = rtrim($path ?: '/', '/') ?: '/';
    $base = app_base_path();

    if ($base !== '' && ($path === $base || str_starts_with($path, $base . '/'))) {
        $path = substr($path, strlen($base)) ?: '/';
    }

    return rtrim($path, '/') ?: '/';
}
