<?php
require_once __DIR__ . '/../components/public_layout.php';
require_once __DIR__ . '/../config/firebase.php';

if (current_website_user()) {
    redirect_to('/');
}

$firebaseConfig = firebase_config();
public_header('User Login', 'Login to GG Hosiery with Google for cart and small order requests.');
?>
<section class="login-page-section" data-aos="fade-up">
    <div class="login-card public-login-card">
        <p class="eyebrow">Customer Login</p>
        <h1>Continue with Google</h1>
        <p class="login-copy">Use Google login for cart and small order requests. ERP access remains private for GG Hosiery owners and employees only.</p>
        <button class="google-login-btn" type="button" id="firebaseUserLogin">
            <span>G</span>
            Continue with Google
        </button>
        <p class="form-status" id="firebaseUserLoginStatus" aria-live="polite"></p>
        <a href="<?= app_url('/products') ?>">Back to products</a>
    </div>
</section>
<script type="module">
    import { initializeApp } from 'https://www.gstatic.com/firebasejs/10.12.5/firebase-app.js';
    import { getAuth, GoogleAuthProvider, signInWithPopup } from 'https://www.gstatic.com/firebasejs/10.12.5/firebase-auth.js';

    const app = initializeApp(<?= json_encode($firebaseConfig, JSON_THROW_ON_ERROR) ?>);
    const auth = getAuth(app);
    const provider = new GoogleAuthProvider();
    provider.setCustomParameters({ prompt: 'select_account' });
    const button = document.getElementById('firebaseUserLogin');
    const status = document.getElementById('firebaseUserLoginStatus');

    button?.addEventListener('click', async () => {
        status.textContent = 'Opening Google...';
        status.classList.remove('error');
        button.disabled = true;
        try {
            const result = await signInWithPopup(auth, provider);
            const idToken = await result.user.getIdToken();
            status.textContent = 'Signing you in...';
            const response = await fetch('<?= app_url('/auth/user-firebase-login.php') ?>', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-Token': '<?= e(csrf_token()) ?>',
                },
                body: JSON.stringify({ idToken }),
            });
            const payload = await response.json();
            if (!response.ok) throw new Error(payload.error || 'Login failed.');
            window.location.href = payload.redirect || '<?= app_url('/') ?>';
        } catch (error) {
            status.textContent = error.message;
            status.classList.add('error');
            button.disabled = false;
        }
    });
</script>
<?php public_footer(); ?>
