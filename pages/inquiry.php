<?php
require_once __DIR__ . '/../components/public_layout.php';
public_header('Wholesale Inquiry', 'Submit a GG Hosiery wholesale inquiry.');
?>
<section class="form-page" data-aos="fade-up">
    <div>
        <p class="eyebrow">Wholesale Inquiry</p>
        <h1>Request trade availability.</h1>
        <p>Share your business details, product interest, and expected monthly volume.</p>
    </div>
    <form class="glass-form ajax-form" method="post" action="<?= app_url('/api/public/inquiry') ?>">
        <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">
        <label>Business Name<input name="business_name" required maxlength="160" placeholder="Your shop or company"></label>
        <label>Contact Name<input name="contact_name" required maxlength="160" placeholder="Buyer name"></label>
        <label>Phone<input name="phone" required maxlength="40" placeholder="9876543210"></label>
        <label>Email<input type="email" name="email" maxlength="190" placeholder="buyer@example.com"></label>
        <label>City<input name="city" maxlength="100" placeholder="Delhi"></label>
        <label>Product Interest<input name="product_interest" maxlength="180" placeholder="Hosiery, ladies undergarments, wholesale packs"></label>
        <label>Monthly Volume<input name="monthly_volume" maxlength="80" placeholder="Approx. pieces or cartons"></label>
        <label>Message<textarea name="message" rows="4" placeholder="Share sizes, categories, or delivery requirements"></textarea></label>
        <button class="primary-btn" type="submit">Submit Inquiry</button>
        <p class="form-status" aria-live="polite"></p>
    </form>
</section>
<?php public_footer(); ?>
