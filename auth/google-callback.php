<?php
declare(strict_types=1);

require_once __DIR__ . '/../middleware/security.php';
require_once __DIR__ . '/../config/google_oauth.php';

start_secure_session();

function google_http_post(string $url, array $payload): array
{
    $body = http_build_query($payload);
    if (function_exists('curl_init')) {
        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => $body,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HTTPHEADER => ['Content-Type: application/x-www-form-urlencoded'],
            CURLOPT_TIMEOUT => 12,
        ]);
        $response = curl_exec($ch);
        $status = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
    } else {
        $context = stream_context_create([
            'http' => [
                'method' => 'POST',
                'header' => "Content-Type: application/x-www-form-urlencoded\r\n",
                'content' => $body,
                'timeout' => 12,
            ],
        ]);
        $response = file_get_contents($url, false, $context);
        $status = isset($http_response_header[0]) && preg_match('/\s(\d{3})\s/', $http_response_header[0], $m) ? (int) $m[1] : 0;
    }

    if ($response === false || $status < 200 || $status >= 300) {
        throw new RuntimeException('Google token request failed.');
    }

    return json_decode((string) $response, true, 512, JSON_THROW_ON_ERROR);
}

function google_http_get(string $url, string $accessToken): array
{
    if (function_exists('curl_init')) {
        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HTTPHEADER => ['Authorization: Bearer ' . $accessToken],
            CURLOPT_TIMEOUT => 12,
        ]);
        $response = curl_exec($ch);
        $status = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
    } else {
        $context = stream_context_create([
            'http' => [
                'method' => 'GET',
                'header' => 'Authorization: Bearer ' . $accessToken . "\r\n",
                'timeout' => 12,
            ],
        ]);
        $response = file_get_contents($url, false, $context);
        $status = isset($http_response_header[0]) && preg_match('/\s(\d{3})\s/', $http_response_header[0], $m) ? (int) $m[1] : 0;
    }

    if ($response === false || $status < 200 || $status >= 300) {
        throw new RuntimeException('Google userinfo request failed.');
    }

    return json_decode((string) $response, true, 512, JSON_THROW_ON_ERROR);
}

try {
    $state = (string) ($_GET['state'] ?? '');
    $code = (string) ($_GET['code'] ?? '');
    if ($state === '' || $code === '' || !hash_equals($_SESSION['google_oauth_state'] ?? '', $state)) {
        throw new RuntimeException('Invalid Google login state.');
    }
    unset($_SESSION['google_oauth_state']);

    $config = google_oauth_config();
    $token = google_http_post('https://oauth2.googleapis.com/token', [
        'client_id' => $config['client_id'],
        'client_secret' => $config['client_secret'],
        'redirect_uri' => $config['redirect_uri'],
        'grant_type' => 'authorization_code',
        'code' => $code,
    ]);

    $accessToken = (string) ($token['access_token'] ?? '');
    if ($accessToken === '') {
        throw new RuntimeException('Google did not return an access token.');
    }

    $profile = google_http_get('https://openidconnect.googleapis.com/v1/userinfo', $accessToken);
    $email = strtolower(trim((string) ($profile['email'] ?? '')));
    $verified = (bool) ($profile['email_verified'] ?? false);
    if ($email === '' || !$verified) {
        throw new RuntimeException('Google email is missing or unverified.');
    }
    if (!erp_email_allowed($email)) {
        log_activity(null, 'google_login_denied', 'Denied non-allowlisted Google login for ' . $email);
        $_SESSION['login_error'] = 'This Google account is not allowed for GG Hosiery ERP.';
        redirect_to('/erp/login');
    }

    $user = db_row('SELECT id, name, email, role, status FROM users WHERE LOWER(email) = ? AND status = "active"', [$email]);
    if (!$user) {
        log_activity(null, 'google_login_denied', 'Denied Google login for ' . $email);
        $_SESSION['login_error'] = 'This Google account is not authorized for GG Hosiery ERP.';
        redirect_to('/erp/login');
    }

    session_regenerate_id(true);
    $_SESSION['user_id'] = (int) $user['id'];
    $_SESSION['auth_provider'] = 'google';
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    db()->prepare('UPDATE users SET last_login_at = NOW() WHERE id = ?')->execute([$user['id']]);
    log_activity((int) $user['id'], 'google_login', 'User logged in with Google.');
    redirect_to('/erp');
} catch (Throwable $e) {
    log_activity(null, 'google_login_failed', $e->getMessage());
    $_SESSION['login_error'] = 'Google login failed. Please try again or contact the owner.';
    redirect_to('/erp/login');
}
