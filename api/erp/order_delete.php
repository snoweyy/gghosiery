<?php
declare(strict_types=1);
require_once __DIR__ . '/../../middleware/security.php';
if ($_SERVER['REQUEST_METHOD'] !== 'POST') json_response(['error' => 'Method not allowed'], 405);
$user = require_api_role(['owner', 'admin']);
verify_csrf();
if (demo_mode()) json_response(['message' => 'Demo mode: order preview cancelled.']);
$id = (int) ($_POST['order_id'] ?? 0);
if ($id <= 0) json_response(['error' => 'Order id is required.'], 422);
db()->prepare('UPDATE orders SET status = "cancelled" WHERE id = ?')->execute([$id]);
log_activity((int) $user['id'], 'order_cancelled', 'Cancelled order #' . $id);
json_response(['message' => 'Order cancelled.']);
