<?php
declare(strict_types=1);
require_once __DIR__ . '/../../middleware/security.php';
if ($_SERVER['REQUEST_METHOD'] !== 'POST') json_response(['error' => 'Method not allowed'], 405);
$user = require_api_role(['owner', 'admin']);
verify_csrf();
if (demo_mode()) json_response(['message' => 'Demo mode: supplier preview deleted.']);
$id = (int) ($_POST['supplier_id'] ?? 0);
if ($id <= 0) json_response(['error' => 'Supplier id is required.'], 422);
db()->prepare('UPDATE suppliers SET status = "inactive" WHERE id = ?')->execute([$id]);
log_activity((int) $user['id'], 'supplier_deleted', 'Deleted supplier #' . $id);
json_response(['message' => 'Supplier deleted.']);
