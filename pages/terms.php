<?php
require_once __DIR__ . '/../components/public_layout.php';
public_header('Terms & Conditions', 'GG Hosiery website and ERP terms and conditions.');

$sections = [
    ['usage', 'Website Usage', 'This website is provided for business information, product discovery, wholesale inquiries, and small order requests related to GG Hosiery. Visitors must use the website lawfully and must not attempt to interfere with security, availability, or normal operation.'],
    ['inquiry', 'Wholesale Inquiry Rules', 'Inquiry forms and cart requests are not automatic purchase confirmations. Product availability, pricing, delivery, GST billing, and payment terms are confirmed directly by GG Hosiery before an order is finalized.'],
    ['ip', 'Intellectual Property', 'Website design, product presentation, text, images, brand elements, and software interfaces belong to GG Hosiery or its licensors. Visitors may not copy, resell, scrape, or reuse content without written permission.'],
    ['erp', 'ERP Access Restrictions', 'The ERP system is private and reserved only for authorized GG Hosiery owners, administrators, and employees. Unauthorized access, attempted access, credential sharing, probing, or API abuse is strictly prohibited.'],
    ['liability', 'Limitation of Liability', 'GG Hosiery aims to keep information accurate, but product availability, prices, and operational information may change. The website is provided on an as-is basis, and GG Hosiery is not liable for indirect losses caused by website use.'],
    ['accounts', 'Account Responsibilities', 'Authorized ERP users are responsible for keeping login access secure, using Google/Firebase authentication responsibly, and reporting suspicious access immediately. Actions performed through ERP accounts may be logged for security and audit purposes.'],
    ['changes', 'Changes to Terms', 'GG Hosiery may update these terms as the business, website, ERP, or legal requirements evolve. Continued use of the website or ERP after changes means acceptance of the updated terms.'],
];
?>
<section class="legal-hero" data-aos="fade-up">
    <p class="eyebrow">Legal</p>
    <h1>Terms & Conditions</h1>
    <p>Clear usage rules for the GG Hosiery public website, wholesale inquiry flow, and private ERP system.</p>
</section>
<section class="legal-layout">
    <aside class="legal-toc" data-aos="fade-right">
        <span>On this page</span>
        <?php foreach ($sections as $section): ?>
            <a href="#<?= e($section[0]) ?>"><?= e($section[1]) ?></a>
        <?php endforeach; ?>
    </aside>
    <div class="legal-content">
        <?php foreach ($sections as $section): ?>
            <article class="legal-card" id="<?= e($section[0]) ?>" data-aos="fade-up">
                <i data-lucide="file-check-2"></i>
                <h2><?= e($section[1]) ?></h2>
                <p><?= e($section[2]) ?></p>
            </article>
        <?php endforeach; ?>
        <article class="legal-card accent" data-aos="fade-up">
            <i data-lucide="shield-alert"></i>
            <h2>Unauthorized ERP Access Is Prohibited</h2>
            <p>Any attempt to access `/erp`, ERP APIs, dashboards, invoices, customer data, or employee records without permission is prohibited and may be logged, blocked, and escalated.</p>
        </article>
    </div>
</section>
<?php public_footer(); ?>
