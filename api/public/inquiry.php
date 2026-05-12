<?php
declare(strict_types=1);

require_once __DIR__ . '/../../middleware/security.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    json_response(['error' => 'Method not allowed'], 405);
}
verify_csrf();

$business = trim((string) ($_POST['business_name'] ?? ''));
$contact = trim((string) ($_POST['contact_name'] ?? ''));
$phone = trim((string) ($_POST['phone'] ?? ''));
if ($business === '' || $contact === '' || $phone === '') {
    json_response(['error' => 'Business name, contact name, and phone are required.'], 422);
}

$stmt = db()->prepare('INSERT INTO wholesale_inquiries (business_name, contact_name, phone, email, city, product_interest, monthly_volume, message) VALUES (?, ?, ?, ?, ?, ?, ?, ?)');
$stmt->execute([
    $business,
    $contact,
    $phone,
    filter_var($_POST['email'] ?? '', FILTER_VALIDATE_EMAIL) ?: null,
    trim((string) ($_POST['city'] ?? '')) ?: null,
    trim((string) ($_POST['product_interest'] ?? '')) ?: null,
    trim((string) ($_POST['monthly_volume'] ?? '')) ?: null,
    trim((string) ($_POST['message'] ?? '')) ?: null,
]);

json_response(['message' => 'Wholesale inquiry submitted.']);
