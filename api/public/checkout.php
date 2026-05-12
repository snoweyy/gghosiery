<?php
declare(strict_types=1);

require_once __DIR__ . '/../../middleware/security.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    json_response(['error' => 'Method not allowed'], 405);
}
verify_csrf();

$name = trim((string) ($_POST['name'] ?? ''));
$phone = trim((string) ($_POST['phone'] ?? ''));
$addressLine1 = trim((string) ($_POST['address_line_1'] ?? ''));
$city = trim((string) ($_POST['city'] ?? ''));
$state = trim((string) ($_POST['state'] ?? ''));
$pinCode = trim((string) ($_POST['pin_code'] ?? ''));
$cartPayload = (string) ($_POST['cart_payload'] ?? '');
$items = json_decode($cartPayload, true);

if ($name === '' || $phone === '' || $addressLine1 === '' || $city === '' || $state === '' || $pinCode === '') {
    json_response(['error' => 'Name, phone, address, city, state, and PIN code are required.'], 422);
}
if (!preg_match('/^[0-9]{6}$/', $pinCode)) {
    json_response(['error' => 'Enter a valid 6 digit PIN code.'], 422);
}
if (!is_array($items) || count($items) === 0) {
    json_response(['error' => 'Your cart is empty.'], 422);
}

if (demo_mode()) {
    json_response(['message' => 'Demo mode: buying request received. Connect MySQL to create ERP orders.']);
}

try {
    db()->beginTransaction();

    $addressParts = [
        'Address 1: ' . $addressLine1,
        trim((string) ($_POST['address_line_2'] ?? '')) !== '' ? 'Address 2: ' . trim((string) ($_POST['address_line_2'] ?? '')) : null,
        trim((string) ($_POST['landmark'] ?? '')) !== '' ? 'Landmark: ' . trim((string) ($_POST['landmark'] ?? '')) : null,
        'City: ' . $city,
        'State: ' . $state,
        'PIN: ' . $pinCode,
        trim((string) ($_POST['payment_preference'] ?? '')) !== '' ? 'Payment: ' . trim((string) ($_POST['payment_preference'] ?? '')) : null,
        trim((string) ($_POST['note'] ?? '')) !== '' ? 'Note: ' . trim((string) ($_POST['note'] ?? '')) : null,
    ];
    $fullAddress = implode("\n", array_values(array_filter($addressParts)));

    $customer = db_row('SELECT id FROM customers WHERE phone = ? LIMIT 1', [$phone]);
    if ($customer) {
        $customerId = (int) $customer['id'];
        db()->prepare('UPDATE customers SET name = ?, email = ?, gstin = ?, city = ?, address = ? WHERE id = ?')->execute([
            $name,
            filter_var($_POST['email'] ?? '', FILTER_VALIDATE_EMAIL) ?: null,
            trim((string) ($_POST['gstin'] ?? '')) ?: null,
            $city,
            $fullAddress,
            $customerId,
        ]);
    } else {
        db()->prepare('INSERT INTO customers (name, phone, email, gstin, city, address) VALUES (?, ?, ?, ?, ?, ?)')->execute([
            $name,
            $phone,
            filter_var($_POST['email'] ?? '', FILTER_VALIDATE_EMAIL) ?: null,
            trim((string) ($_POST['gstin'] ?? '')) ?: null,
            $city,
            $fullAddress,
        ]);
        $customerId = (int) db()->lastInsertId();
    }

    $subtotal = 0.0;
    $taxTotal = 0.0;
    $resolved = [];
    foreach ($items as $item) {
        $productId = (int) ($item['id'] ?? 0);
        $qty = max(1, (int) ($item['qty'] ?? 1));
        $product = db_row('SELECT id, sale_price, gst_rate, name FROM products WHERE id = ? AND status = "active"', [$productId]);
        if (!$product) {
            continue;
        }
        $lineSubtotal = (float) $product['sale_price'] * $qty;
        $lineTax = $lineSubtotal * ((float) $product['gst_rate'] / 100);
        $subtotal += $lineSubtotal;
        $taxTotal += $lineTax;
        $resolved[] = [$product, $qty, $lineSubtotal + $lineTax];
    }

    if (!$resolved) {
        throw new RuntimeException('No valid products in cart.');
    }

    $grandTotal = $subtotal + $taxTotal;
    $orderNo = 'WEB-' . date('Ymd') . '-' . strtoupper(bin2hex(random_bytes(3)));
    db()->prepare('INSERT INTO orders (customer_id, order_number, status, payment_status, subtotal, tax_total, grand_total) VALUES (?, ?, "confirmed", "unpaid", ?, ?, ?)')->execute([
        $customerId,
        $orderNo,
        $subtotal,
        $taxTotal,
        $grandTotal,
    ]);
    $orderId = (int) db()->lastInsertId();

    foreach ($resolved as [$product, $qty, $lineTotal]) {
        db()->prepare('INSERT INTO order_items (order_id, product_id, quantity, unit_price, gst_rate, line_total) VALUES (?, ?, ?, ?, ?, ?)')->execute([
            $orderId,
            $product['id'],
            $qty,
            $product['sale_price'],
            $product['gst_rate'],
            $lineTotal,
        ]);
        db()->prepare('UPDATE inventory SET quantity = GREATEST(quantity - ?, 0) WHERE product_id = ?')->execute([$qty, $product['id']]);
    }

    db()->prepare('INSERT INTO notifications (title, body, type) VALUES (?, ?, "info")')->execute([
        'New small order',
        'Website order ' . $orderNo . ' from ' . $name . ' is ready for confirmation.',
    ]);
    log_activity(null, 'website_order_created', 'Created website small order ' . $orderNo);
    db()->commit();

    json_response(['message' => 'Buying request submitted. Order ' . $orderNo . ' is ready for confirmation.']);
} catch (Throwable $e) {
    if (db()->inTransaction()) {
        db()->rollBack();
    }
    json_response(['error' => 'Order could not be submitted. Please try again.'], 422);
}
