<?php
declare(strict_types=1);
require_once __DIR__ . '/../../middleware/security.php';
if ($_SERVER['REQUEST_METHOD'] !== 'POST') json_response(['error' => 'Method not allowed'], 405);
$user = require_api_role(['owner', 'admin', 'employee']);
verify_csrf();
if (demo_mode()) json_response(['message' => 'Demo mode: order preview saved.']);
$customerId = (int) ($_POST['customer_id'] ?? 0);
$productId = (int) ($_POST['product_id'] ?? 0);
$qty = max(1, (int) ($_POST['quantity'] ?? 1));
$product = db_row('SELECT id, sale_price, gst_rate FROM products WHERE id = ? AND status = "active"', [$productId]);
if (!$customerId || !$product) json_response(['error' => 'Valid customer and product are required.'], 422);

$subtotal = (float) $product['sale_price'] * $qty;
$tax = $subtotal * ((float) $product['gst_rate'] / 100);
$total = $subtotal + $tax;
$orderNo = 'ORD-' . date('Ymd') . '-' . strtoupper(bin2hex(random_bytes(3)));

try {
    db()->beginTransaction();
    db()->prepare('INSERT INTO orders (customer_id, order_number, status, subtotal, tax_total, grand_total) VALUES (?, ?, "confirmed", ?, ?, ?)')->execute([$customerId, $orderNo, $subtotal, $tax, $total]);
    $orderId = (int) db()->lastInsertId();
    db()->prepare('INSERT INTO order_items (order_id, product_id, quantity, unit_price, gst_rate, line_total) VALUES (?, ?, ?, ?, ?, ?)')->execute([$orderId, $productId, $qty, $product['sale_price'], $product['gst_rate'], $total]);
    db()->prepare('UPDATE inventory SET quantity = GREATEST(quantity - ?, 0) WHERE product_id = ?')->execute([$qty, $productId]);
    db()->commit();
    log_activity((int) $user['id'], 'order_created', 'Created order ' . $orderNo);
    json_response(['message' => 'Order saved.']);
} catch (Throwable) {
    if (db()->inTransaction()) db()->rollBack();
    json_response(['error' => 'Order could not be saved.'], 422);
}
