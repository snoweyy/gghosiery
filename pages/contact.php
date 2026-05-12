<?php
require_once __DIR__ . '/../components/public_layout.php';
public_header('Contact', 'Contact GG Hosiery for wholesale hosiery and ladies undergarment supply.');
?>
<section class="form-page" data-aos="fade-up">
    <div>
        <p class="eyebrow">Contact</p>
        <h1>Talk to GG Hosiery</h1>
        <p>Send a business message or contact directly for wholesale hosiery and ladies undergarment requirements.</p>
        <div class="direct-contact-grid">
            <a class="direct-contact-card" href="<?= BUSINESS_WHATSAPP_URL ?>" target="_blank" rel="noopener">
                <i data-lucide="message-circle"></i>
                <span>WhatsApp</span>
                <strong><?= e(BUSINESS_PHONE_DISPLAY) ?></strong>
            </a>
            <a class="direct-contact-card" href="tel:<?= e(BUSINESS_PHONE_E164) ?>">
                <i data-lucide="phone"></i>
                <span>Call Directly</span>
                <strong><?= e(BUSINESS_PHONE_DISPLAY) ?></strong>
            </a>
        </div>
    </div>
    <div class="contact-focus-card" data-aos="fade-left">
        <p class="eyebrow">Fastest Response</p>
        <h2>Contact directly for wholesale orders.</h2>
        <p>For product availability, pricing, small orders, or wholesale supply questions, message or call GG Hosiery directly.</p>
        <div class="hero-actions">
            <a class="primary-btn" href="<?= BUSINESS_WHATSAPP_URL ?>" target="_blank" rel="noopener">WhatsApp Now</a>
            <a class="secondary-btn" href="tel:<?= e(BUSINESS_PHONE_E164) ?>">Call <?= e(BUSINESS_PHONE_DISPLAY) ?></a>
        </div>
    </div>
</section>
<?php public_footer(); ?>
