<?php
require_once __DIR__ . '/../components/public_layout.php';
public_header('Privacy Policy', 'GG Hosiery privacy policy for website visitors, customers, and ERP users.');

$sections = [
    ['collected', 'Information Collected', 'GG Hosiery may collect business name, contact name, phone number, email, city, delivery address, GSTIN, inquiry details, cart items, order preferences, and messages submitted through the website.'],
    ['google', 'Google Login Data Usage', 'Private ERP login may use Firebase/Google authentication. GG Hosiery uses the verified Google email only to confirm whether the account is authorized for ERP access. The ERP role and permissions remain controlled by GG Hosiery records.'],
    ['forms', 'Contact Forms and Orders', 'Wholesale inquiries, contact messages, and small order requests are stored to respond to business requirements, confirm product availability, prepare billing, and manage customer communication.'],
    ['cookies', 'Cookies and Analytics', 'The website may use essential session cookies for security and cart behavior. Analytics or performance tools may be used to understand traffic and improve the website experience without selling personal data.'],
    ['protection', 'Data Protection', 'GG Hosiery uses secure sessions, CSRF protection, prepared database queries, access controls, activity logs, and private ERP authentication to protect operational and customer information.'],
    ['rights', 'User Rights', 'Customers and business contacts may request correction, update, or deletion of their contact information where legally and operationally appropriate. Some billing or order records may need to be retained for business, audit, or legal reasons.'],
    ['contact', 'Contact Details', 'For privacy or data questions, contact GG Hosiery directly by WhatsApp or phone at ' . BUSINESS_PHONE_DISPLAY . '.'],
];
?>
<section class="legal-hero" data-aos="fade-up">
    <p class="eyebrow">Privacy</p>
    <h1>Privacy Policy</h1>
    <p>How GG Hosiery collects, uses, and protects website, order, inquiry, and private ERP login information.</p>
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
                <i data-lucide="lock-keyhole"></i>
                <h2><?= e($section[1]) ?></h2>
                <p><?= e($section[2]) ?></p>
            </article>
        <?php endforeach; ?>
        <article class="legal-card accent" data-aos="fade-up">
            <i data-lucide="shield-check"></i>
            <h2>Private ERP Notice</h2>
            <p>The ERP system is private. Unauthorized access to ERP pages, APIs, customer data, invoices, employee records, or business reports is prohibited and may be logged for security review.</p>
        </article>
    </div>
</section>
<?php public_footer(); ?>
