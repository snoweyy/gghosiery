<?php
require_once __DIR__ . '/../components/public_layout.php';
public_header('About', 'GG Hosiery is an Indian wholesale hosiery and ladies undergarment business with 20+ years of trusted industry experience.');

$stats = [
    ['20+', 'Years Experience'],
    ['1000+', 'Retail Partners'],
    ['500+', 'Products'],
    ['Pan India', 'Delivery'],
];

$timeline = [
    ['Family roots', 'GG Hosiery began as a family-driven wholesale business built on direct relationships, daily discipline, and a deep understanding of retailer needs.'],
    ['Trust-led growth', 'The business grew through consistent service, dependable stock, and customer satisfaction instead of short-term selling.'],
    ['Expanded catalog', 'Over time, GG Hosiery expanded into ladies undergarments, bras, panties, lingerie, matching sets, nightwear, and wholesale hosiery products.'],
    ['Modern operations', 'Today, the company combines traditional business values with technology-backed inventory, billing, and customer workflows.'],
];

$offers = [
    ['heart', 'Bras', 'Wholesale bra collections focused on comfort, practical sizing, and retailer demand.'],
    ['badge-check', 'Panties', 'Everyday essentials and fast-moving stock for repeat customer purchases.'],
    ['sparkles', 'Matching Sets', 'Coordinated sets for shops looking to offer premium-looking collections.'],
    ['moon', 'Nightwear', 'Comfort-led nightwear options for dependable wholesale rotation.'],
    ['boxes', 'Wholesale Collections', 'Assorted hosiery and ladies undergarment selections for retailers across India.'],
];

$trust = [
    ['shield-check', 'Consistent Quality', 'Products selected for comfort, finish, and reliable repeat sales.'],
    ['indian-rupee', 'Affordable Wholesale Pricing', 'Trade-focused pricing designed for healthy retailer margins.'],
    ['package-check', 'Reliable Stock Availability', 'A business model built around consistent supply and product continuity.'],
    ['messages-square', 'Fast Response Support', 'Clear communication for inquiries, orders, and stock requirements.'],
    ['handshake', 'Long-Term Relationships', 'A relationship-first approach shaped by two decades of wholesale experience.'],
    ['truck', 'Pan India Supply', 'Serving retailers and shop owners across India with dependable wholesale support.'],
];
?>

<section class="about-hero">
    <div class="about-hero-copy" data-aos="fade-up">
        <p class="eyebrow">About GG Hosiery</p>
        <h1>20+ Years of Trust in India's Hosiery Industry</h1>
        <p>Building long-term relationships with retailers through quality products, trusted service, and wholesale excellence.</p>
        <div class="hero-actions">
            <a class="primary-btn" href="<?= app_url('/inquiry') ?>">Start Wholesale Inquiry</a>
            <a class="secondary-btn" href="<?= app_url('/products') ?>">View Products</a>
        </div>
    </div>
    <div class="about-stat-grid" data-aos="fade-left">
        <?php foreach ($stats as $stat): ?>
            <article class="about-stat-card">
                <strong class="counter"><?= e($stat[0]) ?></strong>
                <span><?= e($stat[1]) ?></span>
            </article>
        <?php endforeach; ?>
    </div>
</section>

<section class="about-story">
    <div class="section-kicker" data-aos="fade-up">
        <p class="eyebrow">Our Story</p>
        <h2>Built through trust, consistency, and quality.</h2>
        <p>GG Hosiery has always believed that wholesale business is not only about products. It is about being reliable when retailers need stock, clarity, and long-term support.</p>
    </div>
    <div class="timeline">
        <?php foreach ($timeline as $index => $item): ?>
            <article class="timeline-card" data-aos="fade-up" data-aos-delay="<?= $index * 90 ?>">
                <span><?= str_pad((string) ($index + 1), 2, '0', STR_PAD_LEFT) ?></span>
                <div>
                    <h3><?= e($item[0]) ?></h3>
                    <p><?= e($item[1]) ?></p>
                </div>
            </article>
        <?php endforeach; ?>
    </div>
</section>

<section class="about-section">
    <div class="section-kicker" data-aos="fade-up">
        <p class="eyebrow">What We Offer</p>
        <h2>Wholesale categories made for everyday retail movement.</h2>
    </div>
    <div class="offer-grid">
        <?php foreach ($offers as $item): ?>
            <article class="offer-card" data-aos="fade-up">
                <i data-lucide="<?= e($item[0]) ?>"></i>
                <h3><?= e($item[1]) ?></h3>
                <p><?= e($item[2]) ?></p>
            </article>
        <?php endforeach; ?>
    </div>
</section>

<section class="about-section">
    <div class="section-kicker" data-aos="fade-up">
        <p class="eyebrow">Why Retailers Trust Us</p>
        <h2>Wholesale service designed for confidence.</h2>
    </div>
    <div class="trust-grid">
        <?php foreach ($trust as $item): ?>
            <article class="trust-card" data-aos="fade-up">
                <i data-lucide="<?= e($item[0]) ?>"></i>
                <h3><?= e($item[1]) ?></h3>
                <p><?= e($item[2]) ?></p>
            </article>
        <?php endforeach; ?>
    </div>
</section>

<section class="vision-band" data-aos="fade-up">
    <div>
        <p class="eyebrow">Our Vision</p>
        <h2>Modern wholesale, grounded in traditional trust.</h2>
    </div>
    <p>GG Hosiery aims to modernize the wholesale hosiery industry by combining traditional business values with modern technology and customer-focused service.</p>
    <div class="vision-points">
        <span>Digital expansion</span>
        <span>ERP-powered operations</span>
        <span>Faster wholesale ordering</span>
        <span>Improved customer experience</span>
    </div>
</section>

<section class="about-cta" data-aos="zoom-in">
    <p class="eyebrow">Work With Us</p>
    <h2>Partner With GG HOSIERY</h2>
    <p>For retailers and shop owners looking for dependable hosiery and ladies undergarment supply across India.</p>
    <div class="hero-actions">
        <a class="primary-btn" href="<?= app_url('/contact') ?>">Contact Us</a>
        <a class="secondary-btn" href="<?= app_url('/products') ?>">View Products</a>
        <a class="secondary-btn" href="<?= BUSINESS_WHATSAPP_URL ?>" target="_blank" rel="noopener">WhatsApp Inquiry</a>
    </div>
</section>

<?php public_footer(); ?>
