<?php
declare(strict_types=1);
require_once __DIR__ . '/../../middleware/security.php';
if ($_SERVER['REQUEST_METHOD'] !== 'POST') json_response(['error' => 'Method not allowed'], 405);
$user = require_api_role(['owner', 'admin']);
verify_csrf();
if (demo_mode()) json_response(['message' => 'Demo mode: order preview updated.']);
$id = (int) ($_POST['order_id'] ?? 0);
$status = (string) ($_POST['status'] ?? 'confirmed');
$payment = (string) ($_POST['payment_status'] ?? 'unpaid');
if ($id <= 0) json_response(['error' => 'Order id is required.'], 422);
if (!in_array($status, ['draft','confirmed','packed','delivered','cancelled'], true)) json_response(['error' => 'Invalid order status.'], 422);
if (!in_array($payment, ['unpaid','partial','paid'], true)) json_response(['error' => 'Invalid payment status.'], 422);
db()->prepare('UPDATE orders SET status = ?, payment_status = ? WHERE id = ?')->execute([$status, $payment, $id]);
log_activity((int) $user['id'], 'order_updated', 'Updated order #' . $id);
json_response(['message' => 'Order updated.']);
