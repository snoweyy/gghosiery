<?php
declare(strict_types=1);

require_once __DIR__ . '/config/app.php';

$requestPath = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?: '/';
$staticPath = realpath(__DIR__ . $requestPath);
$rootPath = realpath(__DIR__);
if (
    PHP_SAPI === 'cli-server'
    && $staticPath
    && $rootPath
    && is_file($staticPath)
    && ($staticPath === $rootPath || str_starts_with($staticPath, $rootPath . DIRECTORY_SEPARATOR))
) {
    return false;
}

$path = current_path();
$routes = [
    '/' => __DIR__ . '/pages/home.php',
    '/about' => __DIR__ . '/pages/about.php',
    '/products' => __DIR__ . '/pages/products.php',
    '/product' => __DIR__ . '/pages/product-detail.php',
    '/cart' => __DIR__ . '/pages/cart.php',
    '/user/login' => __DIR__ . '/pages/user-login.php',
    '/contact' => __DIR__ . '/pages/contact.php',
    '/inquiry' => __DIR__ . '/pages/inquiry.php',
    '/terms' => __DIR__ . '/pages/terms.php',
    '/privacy' => __DIR__ . '/pages/privacy.php',
    '/install' => __DIR__ . '/auth/install.php',
    '/auth/firebase-login.php' => __DIR__ . '/auth/firebase-login.php',
    '/auth/user-firebase-login.php' => __DIR__ . '/auth/user-firebase-login.php',
    '/auth/user-logout.php' => __DIR__ . '/auth/user-logout.php',
    '/auth/google.php' => __DIR__ . '/auth/google.php',
    '/auth/google-callback.php' => __DIR__ . '/auth/google-callback.php',
    '/erp' => __DIR__ . '/erp/dashboard.php',
    '/erp/login' => __DIR__ . '/auth/login.php',
    '/erp/inventory' => __DIR__ . '/erp/inventory.php',
    '/erp/orders' => __DIR__ . '/erp/orders.php',
    '/erp/customers' => __DIR__ . '/erp/customers.php',
    '/erp/billing' => __DIR__ . '/erp/billing.php',
    '/erp/suppliers' => __DIR__ . '/erp/suppliers.php',
    '/erp/employees' => __DIR__ . '/erp/employees.php',
    '/erp/reports' => __DIR__ . '/erp/reports.php',
    '/erp/notifications' => __DIR__ . '/erp/notifications.php',
];

if (isset($routes[$path])) {
    require $routes[$path];
    exit;
}

if (str_starts_with($path, '/api/public/')) {
    $file = __DIR__ . '/api/public/' . basename($path) . '.php';
    if (is_file($file)) {
        require $file;
        exit;
    }
}

if (str_starts_with($path, '/api/erp/')) {
    $file = __DIR__ . '/api/erp/' . basename($path) . '.php';
    if (is_file($file)) {
        require $file;
        exit;
    }
    require_once __DIR__ . '/middleware/security.php';
    require_api_auth();
    json_response(['error' => 'ERP API endpoint not found'], 404);
}

if (str_starts_with($path, '/erp/')) {
    require_once __DIR__ . '/middleware/security.php';
    require_auth();
    http_response_code(404);
    require __DIR__ . '/pages/404.php';
    exit;
}

http_response_code(404);
require __DIR__ . '/pages/404.php';
