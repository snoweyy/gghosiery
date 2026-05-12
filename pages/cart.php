<?php
require_once __DIR__ . '/../components/public_layout.php';
$websiteUser = current_website_user();
public_header('Cart', 'Review small orders and submit a GG Hosiery purchase request.');
?>
<section class="page-hero" data-aos="fade-up">
    <p class="eyebrow">Small Orders</p>
    <h1>Review your cart and submit a buying request.</h1>
    <p>For small orders, submit your details and the GG Hosiery team will confirm availability, delivery, and payment.</p>
</section>

<section class="cart-page">
    <div class="cart-panel" data-aos="fade-up">
        <div class="panel-head">
            <h2>Your Cart</h2>
            <button class="ghost-btn" type="button" id="clearCart">Clear Cart</button>
        </div>
        <div class="cart-items" id="cartItems"></div>
        <div class="cart-empty" id="cartEmpty">
            <i data-lucide="shopping-bag"></i>
            <h3>Your cart is empty</h3>
            <p>Add products from the catalog to create a small order.</p>
            <a class="secondary-btn" href="<?= app_url('/products') ?>">View Products</a>
        </div>
        <div class="cart-summary" id="cartSummary">
            <span>Estimated Total</span>
            <strong id="cartTotal">Rs. 0.00</strong>
        </div>
    </div>

    <form class="glass-form cart-checkout-form" method="post" action="<?= app_url('/api/public/checkout') ?>" data-aos="fade-up">
        <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">
        <input type="hidden" name="cart_payload" id="cartPayload">
        <h2>Buyer Details</h2>
        <?php if ($websiteUser): ?>
            <p class="form-status">Logged in as <?= e($websiteUser['email']) ?></p>
        <?php else: ?>
            <p class="form-status">Optional: <a href="<?= app_url('/user/login') ?>">login with Google</a> before placing the request.</p>
        <?php endif; ?>
        <label>Name<input name="name" required maxlength="160" placeholder="Buyer or shop owner name" value="<?= e($websiteUser['name'] ?? '') ?>"></label>
        <label>Phone<input name="phone" required maxlength="40" placeholder="9876543210"></label>
        <label>Email<input type="email" name="email" maxlength="190" placeholder="buyer@example.com" value="<?= e($websiteUser['email'] ?? '') ?>"></label>
        <label>Address Line 1<input name="address_line_1" required maxlength="180" placeholder="Shop number, building, street"></label>
        <label>Address Line 2<input name="address_line_2" maxlength="180" placeholder="Area, market, colony, floor"></label>
        <label>Landmark<input name="landmark" maxlength="160" placeholder="Near main road or known place"></label>
        <div class="form-row">
            <label>City<input name="city" required maxlength="100" placeholder="Delhi"></label>
            <label>State<input name="state" required maxlength="100" placeholder="Delhi"></label>
        </div>
        <div class="form-row">
            <label>PIN Code<input name="pin_code" required maxlength="6" pattern="[0-9]{6}" inputmode="numeric" placeholder="110001"></label>
            <label>Payment Preference
                <select name="payment_preference">
                    <option value="Confirm on call">Confirm on call</option>
                    <option value="UPI">UPI</option>
                    <option value="Cash on delivery">Cash on delivery</option>
                    <option value="Bank transfer">Bank transfer</option>
                </select>
            </label>
        </div>
        <label>GSTIN<input name="gstin" maxlength="32" placeholder="Optional for billing"></label>
        <label>Order Note<textarea name="note" rows="3" placeholder="Any size, delivery, or payment preference"></textarea></label>
        <button class="primary-btn" type="submit">Submit Buying Request</button>
        <p class="form-status" aria-live="polite"></p>
    </form>
</section>
<?php public_footer(); ?>
