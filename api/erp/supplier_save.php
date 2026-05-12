<?php
declare(strict_types=1);
require_once __DIR__ . '/../../middleware/security.php';
if ($_SERVER['REQUEST_METHOD'] !== 'POST') json_response(['error' => 'Method not allowed'], 405);
$user = require_api_role(['owner', 'admin']);
verify_csrf();
if (demo_mode()) json_response(['message' => 'Demo mode: supplier preview saved.']);
$supplierId = (int) ($_POST['supplier_id'] ?? 0);
$name = trim((string) ($_POST['name'] ?? ''));
if ($name === '') json_response(['error' => 'Supplier name is required.'], 422);
$values = [$name, $_POST['phone'] ?: null, filter_var($_POST['email'] ?? '', FILTER_VALIDATE_EMAIL) ?: null, $_POST['gstin'] ?: null, $_POST['address'] ?: null];
if ($supplierId > 0) {
    $stmt = db()->prepare('UPDATE suppliers SET name = ?, phone = ?, email = ?, gstin = ?, address = ? WHERE id = ?');
    $stmt->execute([...$values, $supplierId]);
    log_activity((int) $user['id'], 'supplier_updated', 'Updated supplier ' . $name);
    json_response(['message' => 'Supplier updated.']);
}
$stmt = db()->prepare('INSERT INTO suppliers (name, phone, email, gstin, address) VALUES (?, ?, ?, ?, ?)');
$stmt->execute($values);
log_activity((int) $user['id'], 'supplier_created', 'Created supplier ' . $name);
json_response(['message' => 'Supplier saved.']);
