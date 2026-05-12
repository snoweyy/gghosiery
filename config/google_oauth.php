<?php
declare(strict_types=1);

require_once __DIR__ . '/app.php';

function google_oauth_config(): array
{
    $local = __DIR__ . '/google_oauth.local.php';
    $fileConfig = is_file($local) ? require $local : [];

    return [
        'client_id' => getenv('GOOGLE_CLIENT_ID') ?: ($fileConfig['client_id'] ?? ''),
        'client_secret' => getenv('GOOGLE_CLIENT_SECRET') ?: ($fileConfig['client_secret'] ?? ''),
        'redirect_uri' => getenv('GOOGLE_REDIRECT_URI') ?: ($fileConfig['redirect_uri'] ?? 'http://localhost:8000/auth/google-callback.php'),
    ];
}

function google_oauth_ready(): bool
{
    $config = google_oauth_config();
    return $config['client_id'] !== '' && $config['client_secret'] !== '' && $config['redirect_uri'] !== '';
}
