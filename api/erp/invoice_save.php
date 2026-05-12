<?php
declare(strict_types=1);
require_once __DIR__ . '/../../middleware/security.php';
if ($_SERVER['REQUEST_METHOD'] !== 'POST') json_response(['error' => 'Method not allowed'], 405);
$user = require_api_role(['owner', 'admin']);
verify_csrf();
if (demo_mode()) json_response(['message' => 'Demo mode: invoice preview generated.']);
$customerId = (int) ($_POST['customer_id'] ?? 0);
$productId = (int) ($_POST['product_id'] ?? 0);
$qty = max(1, (int) ($_POST['quantity'] ?? 1));
$product = db_row('SELECT id, name, sale_price, gst_rate FROM products WHERE id = ? AND status = "active"', [$productId]);
if (!$customerId || !$product) json_response(['error' => 'Valid customer and product are required.'], 422);

$settings = db_row('SELECT invoice_prefix FROM company_settings ORDER BY id LIMIT 1') ?: ['invoice_prefix' => 'GGH'];
$invoiceNo = $settings['invoice_prefix'] . '-' . date('Ymd') . '-' . strtoupper(bin2hex(random_bytes(3)));
$subtotal = (float) $product['sale_price'] * $qty;
$tax = $subtotal * ((float) $product['gst_rate'] / 100);
$cgst = $tax / 2;
$sgst = $tax / 2;
$total = $subtotal + $tax;

try {
    db()->beginTransaction();
    db()->prepare('INSERT INTO invoices (customer_id, invoice_number, invoice_date, subtotal, cgst, sgst, igst, grand_total, status) VALUES (?, ?, CURDATE(), ?, ?, ?, 0, ?, "sent")')->execute([$customerId, $invoiceNo, $subtotal, $cgst, $sgst, $total]);
    $invoiceId = (int) db()->lastInsertId();
    db()->prepare('INSERT INTO invoice_items (invoice_id, description, quantity, unit_price, gst_rate, line_total) VALUES (?, ?, ?, ?, ?, ?)')->execute([$invoiceId, $product['name'], $qty, $product['sale_price'], $product['gst_rate'], $total]);
    db()->prepare('UPDATE inventory SET quantity = GREATEST(quantity - ?, 0) WHERE product_id = ?')->execute([$qty, $productId]);
    db()->prepare('INSERT INTO notifications (title, body, type) VALUES (?, ?, "success")')->execute(['Invoice generated', 'Invoice ' . $invoiceNo . ' was created.']);
    db()->commit();
    log_activity((int) $user['id'], 'invoice_created', 'Created invoice ' . $invoiceNo);
    json_response(['message' => 'Invoice generated.']);
} catch (Throwable) {
    if (db()->inTransaction()) db()->rollBack();
    json_response(['error' => 'Invoice could not be generated.'], 422);
}
