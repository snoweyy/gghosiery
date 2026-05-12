<?php
declare(strict_types=1);
require_once __DIR__ . '/../../middleware/security.php';
if ($_SERVER['REQUEST_METHOD'] !== 'POST') json_response(['error' => 'Method not allowed'], 405);
$user = require_api_role(['owner', 'admin', 'employee']);
verify_csrf();
if (demo_mode()) json_response(['message' => 'Demo mode: customer preview saved.']);
$customerId = (int) ($_POST['customer_id'] ?? 0);
$name = trim((string) ($_POST['name'] ?? ''));
if ($name === '') json_response(['error' => 'Customer name is required.'], 422);
$values = [$name, $_POST['phone'] ?: null, filter_var($_POST['email'] ?? '', FILTER_VALIDATE_EMAIL) ?: null, $_POST['gstin'] ?: null, $_POST['city'] ?: null, $_POST['address'] ?: null];
if ($customerId > 0) {
    $stmt = db()->prepare('UPDATE customers SET name = ?, phone = ?, email = ?, gstin = ?, city = ?, address = ? WHERE id = ?');
    $stmt->execute([...$values, $customerId]);
    log_activity((int) $user['id'], 'customer_updated', 'Updated customer ' . $name);
    json_response(['message' => 'Customer updated.']);
}
$stmt = db()->prepare('INSERT INTO customers (name, phone, email, gstin, city, address) VALUES (?, ?, ?, ?, ?, ?)');
$stmt->execute($values);
log_activity((int) $user['id'], 'customer_created', 'Created customer ' . $name);
json_response(['message' => 'Customer saved.']);
