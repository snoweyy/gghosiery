<?php
declare(strict_types=1);
require_once __DIR__ . '/../../middleware/security.php';
if ($_SERVER['REQUEST_METHOD'] !== 'POST') json_response(['error' => 'Method not allowed'], 405);
$user = require_api_role(['owner', 'admin']);
verify_csrf();
if (demo_mode()) json_response(['message' => 'Demo mode: product preview saved.']);
$productId = (int) ($_POST['product_id'] ?? 0);
$sku = trim((string) ($_POST['sku'] ?? ''));
$name = trim((string) ($_POST['name'] ?? ''));
$category = trim((string) ($_POST['category'] ?? ''));
if ($sku === '' || $name === '' || $category === '') json_response(['error' => 'SKU, name, and category are required.'], 422);

function product_image_column_exists(): bool
{
    static $exists = null;
    if ($exists !== null) {
        return $exists;
    }
    try {
        $exists = (bool) db_row('SHOW COLUMNS FROM products LIKE "image_path"');
    } catch (Throwable) {
        $exists = false;
    }
    return $exists;
}

function uploaded_product_image(): ?string
{
    if (empty($_FILES['product_image']) || ($_FILES['product_image']['error'] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_NO_FILE) {
        return null;
    }
    if ($_FILES['product_image']['error'] !== UPLOAD_ERR_OK) {
        json_response(['error' => 'Product image upload failed.'], 422);
    }
    if ((int) $_FILES['product_image']['size'] > 2 * 1024 * 1024) {
        json_response(['error' => 'Product image must be under 2MB.'], 422);
    }

    $tmp = (string) $_FILES['product_image']['tmp_name'];
    $mime = mime_content_type($tmp) ?: '';
    $extensions = ['image/jpeg' => 'jpg', 'image/png' => 'png', 'image/webp' => 'webp'];
    if (!isset($extensions[$mime])) {
        json_response(['error' => 'Upload JPG, PNG, or WebP product images only.'], 422);
    }

    $dir = __DIR__ . '/../../uploads/products';
    if (!is_dir($dir)) {
        mkdir($dir, 0775, true);
    }
    $filename = 'product-' . date('YmdHis') . '-' . bin2hex(random_bytes(4)) . '.' . $extensions[$mime];
    $target = $dir . '/' . $filename;
    if (!move_uploaded_file($tmp, $target)) {
        json_response(['error' => 'Product image could not be saved.'], 422);
    }

    return '/uploads/products/' . $filename;
}

try {
    db()->beginTransaction();
    $supplierId = $_POST['supplier_id'] !== '' ? (int) $_POST['supplier_id'] : null;
    $size = $_POST['size'] ?: null;
    $color = $_POST['color'] ?: null;
    $imagePath = uploaded_product_image() ?? ($_POST['existing_image'] ?? null);
    $purchasePrice = (float) ($_POST['purchase_price'] ?? 0);
    $salePrice = (float) ($_POST['sale_price'] ?? 0);
    $gstRate = (float) ($_POST['gst_rate'] ?? 18);
    $stock = (int) ($_POST['quantity'] ?? 0);
    $reorder = (int) ($_POST['reorder_level'] ?? 10);

    if ($productId > 0) {
        if (product_image_column_exists()) {
            $stmt = db()->prepare('UPDATE products SET supplier_id = ?, sku = ?, name = ?, category = ?, size = ?, color = ?, image_path = ?, purchase_price = ?, sale_price = ?, gst_rate = ? WHERE id = ?');
            $stmt->execute([$supplierId, $sku, $name, $category, $size, $color, $imagePath ?: null, $purchasePrice, $salePrice, $gstRate, $productId]);
        } else {
            $stmt = db()->prepare('UPDATE products SET supplier_id = ?, sku = ?, name = ?, category = ?, size = ?, color = ?, purchase_price = ?, sale_price = ?, gst_rate = ? WHERE id = ?');
            $stmt->execute([$supplierId, $sku, $name, $category, $size, $color, $purchasePrice, $salePrice, $gstRate, $productId]);
        }
        $inventoryId = db_value('SELECT id FROM inventory WHERE product_id = ? LIMIT 1', [$productId]);
        if ($inventoryId) {
            db()->prepare('UPDATE inventory SET quantity = ?, reorder_level = ? WHERE id = ?')->execute([$stock, $reorder, $inventoryId]);
        } else {
            db()->prepare('INSERT INTO inventory (product_id, quantity, reorder_level) VALUES (?, ?, ?)')->execute([$productId, $stock, $reorder]);
        }
        log_activity((int) $user['id'], 'product_updated', 'Updated product ' . $sku);
        $message = 'Product updated.';
    } else {
        if (product_image_column_exists()) {
            $stmt = db()->prepare('INSERT INTO products (supplier_id, sku, name, category, size, color, image_path, purchase_price, sale_price, gst_rate) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)');
            $stmt->execute([$supplierId, $sku, $name, $category, $size, $color, $imagePath ?: null, $purchasePrice, $salePrice, $gstRate]);
        } else {
            $stmt = db()->prepare('INSERT INTO products (supplier_id, sku, name, category, size, color, purchase_price, sale_price, gst_rate) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)');
            $stmt->execute([$supplierId, $sku, $name, $category, $size, $color, $purchasePrice, $salePrice, $gstRate]);
        }
        $productId = (int) db()->lastInsertId();
        db()->prepare('INSERT INTO inventory (product_id, quantity, reorder_level) VALUES (?, ?, ?)')->execute([$productId, $stock, $reorder]);
        log_activity((int) $user['id'], 'product_created', 'Created product ' . $sku);
        $message = 'Product saved.';
    }

    db()->commit();
    json_response(['message' => $message]);
} catch (Throwable $e) {
    if (db()->inTransaction()) db()->rollBack();
    json_response(['error' => 'Product could not be saved. SKU may already exist.'], 422);
}
