<?php
declare(strict_types=1);

require_once __DIR__ . '/../middleware/security.php';

function erp_header(string $title, array $user): void
{
    ?>
    <!doctype html>
    <html lang="en">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title><?= e($title) ?> | GG Hosiery ERP</title>
        <meta name="robots" content="noindex,nofollow">
        <link rel="preconnect" href="https://fonts.googleapis.com">
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
        <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&family=Poppins:wght@600;700&display=swap" rel="stylesheet">
        <script src="https://cdn.tailwindcss.com"></script>
        <link rel="stylesheet" href="<?= app_url('/assets/css/style.css') ?>">
    </head>
    <body class="erp-body">
    <div class="erp-shell">
        <aside class="erp-sidebar" id="erpSidebar">
            <a href="<?= app_url('/erp') ?>" class="erp-brand"><span>GG</span><b>Hosiery ERP</b></a>
            <nav>
                <?php
                $links = [
                    ['/erp', 'layout-dashboard', 'Dashboard'],
                    ['/erp/inventory', 'package', 'Inventory'],
                    ['/erp/orders', 'shopping-cart', 'Orders'],
                    ['/erp/customers', 'users', 'Customers'],
                    ['/erp/billing', 'receipt-indian-rupee', 'Billing'],
                    ['/erp/suppliers', 'truck', 'Suppliers'],
                    ['/erp/employees', 'badge', 'Employees'],
                    ['/erp/reports', 'bar-chart-3', 'Reports'],
                    ['/erp/notifications', 'bell', 'Notifications'],
                ];
                foreach ($links as [$href, $icon, $label]) {
                    echo '<a class="' . active_nav($href) . '" href="' . app_url($href) . '"><i data-lucide="' . e($icon) . '"></i>' . e($label) . '</a>';
                }
                ?>
            </nav>
        </aside>
        <section class="erp-main">
            <header class="erp-topbar">
                <button class="icon-btn" id="sidebarToggle" aria-label="Toggle sidebar"><i data-lucide="menu"></i></button>
                <div>
                    <p class="eyebrow">Private ERP</p>
                    <h1><?= e($title) ?></h1>
                </div>
                <div class="topbar-actions">
                    <button class="icon-btn" id="themeToggle" aria-label="Toggle theme"><i data-lucide="sun-moon"></i></button>
                    <span><?= e($user['name']) ?> · <?= e(ucfirst($user['role'])) ?></span>
                    <a class="logout-btn" href="<?= app_url('/auth/logout.php') ?>">Logout</a>
                </div>
            </header>
            <div class="toast" id="toast"></div>
    <?php
}

function erp_footer(): void
{
    ?>
        </section>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="https://unpkg.com/lucide@latest"></script>
    <script src="<?= app_url('/assets/js/erp.js') ?>"></script>
    </body>
    </html>
    <?php
}
