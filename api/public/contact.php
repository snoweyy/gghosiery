<?php
declare(strict_types=1);

require_once __DIR__ . '/../../middleware/security.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    json_response(['error' => 'Method not allowed'], 405);
}
verify_csrf();

$name = trim((string) ($_POST['name'] ?? ''));
$message = trim((string) ($_POST['message'] ?? ''));
if ($name === '' || $message === '') {
    json_response(['error' => 'Name and message are required.'], 422);
}

$stmt = db()->prepare('INSERT INTO contact_messages (name, phone, email, subject, message) VALUES (?, ?, ?, ?, ?)');
$stmt->execute([
    $name,
    trim((string) ($_POST['phone'] ?? '')) ?: null,
    filter_var($_POST['email'] ?? '', FILTER_VALIDATE_EMAIL) ?: null,
    trim((string) ($_POST['subject'] ?? '')) ?: null,
    $message,
]);

json_response(['message' => 'Message received.']);
