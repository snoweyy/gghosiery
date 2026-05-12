<?php
declare(strict_types=1);
require_once __DIR__ . '/../../middleware/security.php';
if ($_SERVER['REQUEST_METHOD'] !== 'POST') json_response(['error' => 'Method not allowed'], 405);
$user = require_api_role(['owner', 'admin']);
verify_csrf();
if (demo_mode()) json_response(['message' => 'Demo mode: customer preview deleted.']);
$id = (int) ($_POST['customer_id'] ?? 0);
if ($id <= 0) json_response(['error' => 'Customer id is required.'], 422);
db()->prepare('UPDATE customers SET status = "inactive" WHERE id = ?')->execute([$id]);
log_activity((int) $user['id'], 'customer_deleted', 'Deleted customer #' . $id);
json_response(['message' => 'Customer deleted.']);
