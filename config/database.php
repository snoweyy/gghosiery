<?php
declare(strict_types=1);

require_once __DIR__ . '/app.php';

function db(): PDO
{
    static $pdo = null;
    static $failed = false;
    if ($pdo instanceof PDO) {
        return $pdo;
    }
    if ($failed) {
        throw new RuntimeException('Database connection unavailable.');
    }

    $host = getenv('DB_HOST') ?: '127.0.0.1';
    $name = getenv('DB_NAME') ?: 'gg_hosiery';
    $user = getenv('DB_USER') ?: 'root';
    $pass = getenv('DB_PASS') ?: '';
    $charset = 'utf8mb4';

    $dsn = "mysql:host={$host};dbname={$name};charset={$charset}";
    try {
        $pdo = new PDO($dsn, $user, $pass, [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
        ]);
    } catch (Throwable $e) {
        $failed = true;
        throw new RuntimeException('Database connection unavailable.', 0, $e);
    }

    return $pdo;
}

function demo_mode(): bool
{
    $explicit = strtolower((string) (getenv('APP_DEMO_MODE') ?: ''));
    $demoAllowed = in_array($explicit, ['1', 'true', 'yes'], true) || (app_env() !== 'production' && is_local_request());
    if (!$demoAllowed) {
        return false;
    }

    try {
        db();
        return false;
    } catch (Throwable) {
        return true;
    }
}

function demo_user(): array
{
    return [
        'id' => 1,
        'name' => 'Demo Owner',
        'email' => 'owner@gghosiery.local',
        'role' => 'owner',
        'status' => 'active',
    ];
}

function db_value(string $sql, array $params = []): mixed
{
    if (demo_mode()) {
        if (str_contains($sql, 'SUM(amount)')) return 186500;
        if (str_contains($sql, 'COUNT(*) FROM orders')) return 24;
        if (str_contains($sql, 'COUNT(*) FROM customers')) return 18;
        if (str_contains($sql, 'COUNT(*) FROM inventory')) return 3;
        return 0;
    }
    $stmt = db()->prepare($sql);
    $stmt->execute($params);
    return $stmt->fetchColumn();
}

function db_row(string $sql, array $params = []): ?array
{
    if (demo_mode()) {
        if (str_contains($sql, 'company_settings')) {
            return ['invoice_prefix' => 'GGH'];
        }
        if (str_contains($sql, 'SELECT COALESCE(SUM(cgst)')) {
            return ['cgst' => 12750, 'sgst' => 12750, 'igst' => 0];
        }
        return null;
    }
    $stmt = db()->prepare($sql);
    $stmt->execute($params);
    $row = $stmt->fetch();
    return $row ?: null;
}

function db_all(string $sql, array $params = []): array
{
    if (demo_mode()) {
        return demo_rows($sql);
    }
    $stmt = db()->prepare($sql);
    $stmt->execute($params);
    return $stmt->fetchAll();
}

function demo_rows(string $sql): array
{
    if (str_contains($sql, 'FROM products')) {
        return [
            ['id' => 1, 'sku' => 'GG-VEST-001', 'name' => 'Cotton Vest Pack', 'category' => 'Hosiery', 'size' => 'M-L-XL', 'color' => 'White', 'image_path' => '', 'purchase_price' => 120, 'sale_price' => 165, 'gst_rate' => 5, 'quantity' => 48, 'reorder_level' => 12, 'supplier_name' => 'Demo Supplier'],
            ['id' => 2, 'sku' => 'GG-LAD-014', 'name' => 'Ladies Comfort Innerwear', 'category' => 'Ladies Undergarments', 'size' => 'Assorted', 'color' => 'Assorted', 'image_path' => '', 'purchase_price' => 95, 'sale_price' => 140, 'gst_rate' => 5, 'quantity' => 9, 'reorder_level' => 15, 'supplier_name' => 'Demo Supplier'],
        ];
    }
    if (str_contains($sql, 'FROM suppliers')) {
        return [['id' => 1, 'name' => 'Demo Supplier', 'phone' => '9999999999', 'email' => 'supplier@example.com', 'gstin' => '']];
    }
    if (str_contains($sql, 'FROM customers')) {
        return [['id' => 1, 'name' => 'Sharma Garments', 'phone' => '9876543210', 'email' => 'buyer@example.com', 'gstin' => '', 'city' => 'Delhi']];
    }
    if (str_contains($sql, 'FROM orders')) {
        return [['id' => 1, 'order_number' => 'ORD-20260512-A1B2C3', 'customer_name' => 'Sharma Garments', 'status' => 'confirmed', 'payment_status' => 'partial', 'grand_total' => 18500, 'created_at' => date('Y-m-d H:i:s')]];
    }
    if (str_contains($sql, 'FROM invoices')) {
        return [['id' => 1, 'invoice_number' => 'GGH-20260512-A1B2C3', 'customer_name' => 'Sharma Garments', 'invoice_date' => date('Y-m-d'), 'status' => 'sent', 'grand_total' => 18500, 'day' => date('Y-m-d'), 'invoices' => 1, 'total' => 18500]];
    }
    if (str_contains($sql, 'FROM payments')) {
        return [['method' => 'upi', 'total' => 52000], ['method' => 'cash', 'total' => 34000]];
    }
    if (str_contains($sql, 'FROM employees')) {
        return [['id' => 1, 'name' => 'Demo Employee', 'phone' => '9000000000', 'designation' => 'Sales', 'salary' => 18000]];
    }
    if (str_contains($sql, 'FROM notifications')) {
        return [['title' => 'Demo mode active', 'body' => 'Connect MySQL for production data.', 'created_at' => date('Y-m-d H:i:s')]];
    }
    if (str_contains($sql, 'FROM wholesale_inquiries')) {
        return [['business_name' => 'Retail Buyer Demo', 'contact_name' => 'Amit', 'phone' => '9888888888', 'created_at' => date('Y-m-d H:i:s')]];
    }
    if (str_contains($sql, 'FROM inventory')) {
        return [['name' => 'Ladies Comfort Innerwear', 'sku' => 'GG-LAD-014', 'quantity' => 9, 'reorder_level' => 15]];
    }
    if (str_contains($sql, 'FROM activity_logs')) {
        return [['action' => 'demo_login', 'description' => 'Demo ERP session started.', 'created_at' => date('Y-m-d H:i:s'), 'name' => 'Demo Owner']];
    }
    return [];
}
