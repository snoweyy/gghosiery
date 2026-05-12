<?php
declare(strict_types=1);

require_once __DIR__ . '/../middleware/security.php';
require_once __DIR__ . '/../config/google_oauth.php';

start_secure_session();

if (!google_oauth_ready()) {
    http_response_code(500);
    exit('Google login is not configured.');
}

$config = google_oauth_config();
$_SESSION['google_oauth_state'] = bin2hex(random_bytes(32));

$params = [
    'client_id' => $config['client_id'],
    'redirect_uri' => $config['redirect_uri'],
    'response_type' => 'code',
    'scope' => 'openid email profile',
    'state' => $_SESSION['google_oauth_state'],
    'access_type' => 'online',
    'prompt' => 'select_account',
];

header('Location: https://accounts.google.com/o/oauth2/v2/auth?' . http_build_query($params));
exit;
