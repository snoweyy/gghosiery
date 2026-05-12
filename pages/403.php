<?php
require_once __DIR__ . '/../config/app.php';
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex,nofollow">
    <title>Access Denied | GG Hosiery ERP</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="<?= app_url('/assets/css/style.css') ?>">
</head>
<body class="erp-body">
    <div class="empty-state">
        <h1>Access denied</h1>
        <p>Your role does not have permission to open this area.</p>
        <a class="primary-btn" href="<?= app_url('/erp') ?>">Back to Dashboard</a>
    </div>
</body>
</html>
