<?php
require_once __DIR__ . '/../components/public_layout.php';
public_header('Wholesale Hosiery Platform', 'GG Hosiery supplies wholesale hosiery and ladies undergarments with a modern private ERP backbone.');
?>
<section class="hero-section">
    <div class="hero-copy" data-aos="fade-up">
        <p class="eyebrow">Wholesale hosiery · Ladies undergarments · Trade supply</p>
        <h1>GG HOSIERY</h1>
        <p class="hero-text">A premium wholesale business platform for dependable product supply, faster inquiry handling, and private operational control.</p>
        <div class="hero-actions">
            <a class="primary-btn" href="<?= app_url('/inquiry') ?>">Start Wholesale Inquiry</a>
            <a class="secondary-btn" href="<?= app_url('/products') ?>">View Products</a>
        </div>
    </div>
    <div class="hero-panel" data-aos="zoom-in">
        <div class="metric-card"><span>4</span><p>Core product categories</p></div>
        <div class="metric-card"><span>GST</span><p>Invoice-ready internal ERP</p></div>
        <div class="metric-card"><span>Private</span><p>Owner/admin employee access</p></div>
    </div>
</section>
<section class="section-grid">
    <?php foreach ([
        ['Hosiery Essentials', 'Reliable daily-wear stock for retail counters and distributors.'],
        ['Ladies Undergarments', 'Curated wholesale range focused on comfort, sizing, and repeat demand.'],
        ['Wholesale Operations', 'Inquiry capture, customer tracking, order flow, and billing readiness.'],
    ] as $item): ?>
        <article class="feature-card" data-aos="fade-up">
            <i data-lucide="sparkles"></i>
            <h2><?= e($item[0]) ?></h2>
            <p><?= e($item[1]) ?></p>
        </article>
    <?php endforeach; ?>
</section>
<?php public_footer(); ?>
