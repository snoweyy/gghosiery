<?php
declare(strict_types=1);
require_once __DIR__ . '/../../middleware/security.php';
if ($_SERVER['REQUEST_METHOD'] !== 'POST') json_response(['error' => 'Method not allowed'], 405);
$user = require_api_role(['owner']);
verify_csrf();
if (demo_mode()) json_response(['message' => 'Demo mode: employee preview saved.']);
$employeeId = (int) ($_POST['employee_id'] ?? 0);
$name = trim((string) ($_POST['name'] ?? ''));
if ($name === '') json_response(['error' => 'Employee name is required.'], 422);
$values = [$name, $_POST['phone'] ?: null, $_POST['designation'] ?: null, (float) ($_POST['salary'] ?? 0)];
if ($employeeId > 0) {
    $stmt = db()->prepare('UPDATE employees SET name = ?, phone = ?, designation = ?, salary = ? WHERE id = ?');
    $stmt->execute([...$values, $employeeId]);
    log_activity((int) $user['id'], 'employee_updated', 'Updated employee ' . $name);
    json_response(['message' => 'Employee updated.']);
}
$stmt = db()->prepare('INSERT INTO employees (name, phone, designation, salary) VALUES (?, ?, ?, ?)');
$stmt->execute($values);
log_activity((int) $user['id'], 'employee_created', 'Created employee ' . $name);
json_response(['message' => 'Employee saved.']);
