<?php
declare(strict_types=1);

require_once __DIR__ . '/firebase.php';

function base64url_decode_json(string $value): array
{
    $decoded = base64_decode(strtr($value, '-_', '+/'), true);
    if ($decoded === false) {
        throw new RuntimeException('Invalid token encoding.');
    }
    return json_decode($decoded, true, 512, JSON_THROW_ON_ERROR);
}

function firebase_public_keys(): array
{
    $cache = sys_get_temp_dir() . '/gg_firebase_certs.json';
    if (is_file($cache) && filemtime($cache) > time() - 3600) {
        return json_decode((string) file_get_contents($cache), true, 512, JSON_THROW_ON_ERROR);
    }

    $json = file_get_contents('https://www.googleapis.com/robot/v1/metadata/x509/securetoken@system.gserviceaccount.com');
    if ($json === false) {
        throw new RuntimeException('Could not fetch Firebase public keys.');
    }
    file_put_contents($cache, $json);
    return json_decode($json, true, 512, JSON_THROW_ON_ERROR);
}

function verify_firebase_id_token(string $idToken): array
{
    $parts = explode('.', $idToken);
    if (count($parts) !== 3) {
        throw new RuntimeException('Malformed Firebase token.');
    }

    [$header64, $payload64, $signature64] = $parts;
    $header = base64url_decode_json($header64);
    $payload = base64url_decode_json($payload64);
    $kid = (string) ($header['kid'] ?? '');
    if (($header['alg'] ?? '') !== 'RS256' || $kid === '') {
        throw new RuntimeException('Invalid Firebase token header.');
    }

    $keys = firebase_public_keys();
    if (empty($keys[$kid])) {
        throw new RuntimeException('Firebase token key is unknown.');
    }

    $signature = base64_decode(strtr($signature64, '-_', '+/'), true);
    if ($signature === false || openssl_verify($header64 . '.' . $payload64, $signature, $keys[$kid], OPENSSL_ALGO_SHA256) !== 1) {
        throw new RuntimeException('Firebase token signature failed.');
    }

    $projectId = firebase_config()['projectId'];
    $now = time();
    if (($payload['aud'] ?? '') !== $projectId || ($payload['iss'] ?? '') !== 'https://securetoken.google.com/' . $projectId) {
        throw new RuntimeException('Firebase token project mismatch.');
    }
    if (($payload['exp'] ?? 0) < $now || ($payload['iat'] ?? 0) > $now + 60 || empty($payload['sub'])) {
        throw new RuntimeException('Firebase token timing is invalid.');
    }
    if (empty($payload['email']) || empty($payload['email_verified'])) {
        throw new RuntimeException('Firebase email is missing or unverified.');
    }

    return $payload;
}
