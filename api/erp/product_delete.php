<?php
declare(strict_types=1);
require_once __DIR__ . '/../../middleware/security.php';
if ($_SERVER['REQUEST_METHOD'] !== 'POST') json_response(['error' => 'Method not allowed'], 405);
$user = require_api_role(['owner', 'admin']);
verify_csrf();
if (demo_mode()) json_response(['message' => 'Demo mode: product preview deleted.']);

$productId = (int) ($_POST['product_id'] ?? 0);
if ($productId <= 0) {
    json_response(['error' => 'Product id is required.'], 422);
}

$product = db_row('SELECT sku FROM products WHERE id = ?', [$productId]);
if (!$product) {
    json_response(['error' => 'Product not found.'], 404);
}

try {
    db()->beginTransaction();
    db()->prepare('DELETE FROM inventory WHERE product_id = ?')->execute([$productId]);
    db()->prepare('UPDATE products SET status = "inactive" WHERE id = ?')->execute([$productId]);
    db()->commit();
    log_activity((int) $user['id'], 'product_deleted', 'Deleted product ' . $product['sku']);
    json_response(['message' => 'Product deleted.']);
} catch (Throwable) {
    if (db()->inTransaction()) db()->rollBack();
    json_response(['error' => 'Product could not be deleted because it is linked to business records.'], 422);
}
