<?php
declare(strict_types=1);
require_once __DIR__ . '/../../middleware/security.php';
if ($_SERVER['REQUEST_METHOD'] !== 'POST') json_response(['error' => 'Method not allowed'], 405);
$user = require_api_role(['owner', 'admin']);
verify_csrf();
if (demo_mode()) json_response(['message' => 'Demo mode: invoice preview cancelled.']);
$id = (int) ($_POST['invoice_id'] ?? 0);
if ($id <= 0) json_response(['error' => 'Invoice id is required.'], 422);
db()->prepare('UPDATE invoices SET status = "cancelled" WHERE id = ?')->execute([$id]);
log_activity((int) $user['id'], 'invoice_cancelled', 'Cancelled invoice #' . $id);
json_response(['message' => 'Invoice cancelled.']);
