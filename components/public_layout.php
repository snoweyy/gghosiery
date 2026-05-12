<?php
declare(strict_types=1);

require_once __DIR__ . '/../middleware/security.php';

function public_header(string $title, string $description = ''): void
{
    $description = $description ?: 'GG Hosiery wholesale hosiery and ladies undergarment business platform.';
    $websiteUser = current_website_user();
    ?>
    <!doctype html>
    <html lang="en">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title><?= e($title) ?> | GG Hosiery</title>
        <meta name="description" content="<?= e($description) ?>">
        <link rel="preconnect" href="https://fonts.googleapis.com">
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
        <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&family=Poppins:wght@600;700&display=swap" rel="stylesheet">
        <script src="https://cdn.tailwindcss.com"></script>
        <link rel="stylesheet" href="https://unpkg.com/aos@2.3.1/dist/aos.css">
        <link rel="stylesheet" href="<?= app_url('/assets/css/style.css') ?>">
    </head>
    <body class="bg-[#0A0A0A] text-white antialiased">
    <div class="site-loader" id="siteLoader"><span>GG</span></div>
    <nav class="public-nav">
        <a class="brand" href="<?= app_url('/') ?>">GG HOSIERY</a>
        <div class="nav-links">
            <a href="<?= app_url('/') ?>">Home</a>
            <a href="<?= app_url('/about') ?>">About</a>
            <a href="<?= app_url('/products') ?>">Products</a>
            <a href="<?= app_url('/contact') ?>">Contact</a>
            <a class="nav-cta" href="<?= app_url('/inquiry') ?>">Wholesale Inquiry</a>
        </div>
        <div class="public-actions">
            <a class="cart-link" href="<?= app_url('/cart') ?>"><i data-lucide="shopping-bag"></i><span>Cart</span><b data-cart-count>0</b></a>
            <?php if ($websiteUser): ?>
                <a class="user-pill" href="<?= app_url('/auth/user-logout.php') ?>"><i data-lucide="circle-user-round"></i><span><?= e($websiteUser['name'] ?: $websiteUser['email']) ?></span></a>
            <?php else: ?>
                <a class="user-pill" href="<?= app_url('/user/login') ?>"><i data-lucide="circle-user-round"></i><span>User Login</span></a>
            <?php endif; ?>
        </div>
    </nav>
    <main>
    <?php
}

function public_footer(): void
{
    ?>
    </main>
    <footer class="footer-band">
        <div>
            <strong>GG Hosiery</strong>
            <p>Wholesale hosiery and ladies undergarment supply, built for reliable trade relationships.</p>
        </div>
        <div class="footer-links">
            <a href="<?= app_url('/inquiry') ?>">Start wholesale inquiry</a>
            <a href="<?= app_url('/terms') ?>">Terms</a>
            <a href="<?= app_url('/privacy') ?>">Privacy</a>
        </div>
    </footer>
    <script src="https://unpkg.com/lenis@1.1.20/dist/lenis.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/gsap/3.12.5/gsap.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/gsap/3.12.5/ScrollTrigger.min.js"></script>
    <script src="https://unpkg.com/aos@2.3.1/dist/aos.js"></script>
    <script src="https://unpkg.com/lucide@latest"></script>
    <script src="<?= app_url('/assets/js/app.js') ?>"></script>
    </body>
    </html>
    <?php
}
