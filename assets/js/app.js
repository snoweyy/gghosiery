window.addEventListener('load', () => {
    document.getElementById('siteLoader')?.classList.add('hide');
});

if (window.Lenis) {
    const lenis = new Lenis({ lerp: 0.08, smoothWheel: true });
    function raf(time) {
        lenis.raf(time);
        requestAnimationFrame(raf);
    }
    requestAnimationFrame(raf);
}

if (window.AOS) {
    AOS.init({ duration: 700, once: true, offset: 80 });
}

if (window.gsap) {
    gsap.from('.public-nav', { y: -20, opacity: 0, duration: 0.7, ease: 'power2.out' });
    gsap.from('.metric-card', { y: 24, opacity: 0, duration: 0.65, stagger: 0.12, delay: 0.2 });
    gsap.from('.about-stat-card', { y: 26, opacity: 0, duration: 0.7, stagger: 0.12, delay: 0.15, ease: 'power2.out' });
    gsap.utils.toArray('.offer-card, .trust-card, .timeline-card').forEach((card) => {
        gsap.from(card, {
            y: 28,
            opacity: 0,
            duration: 0.65,
            ease: 'power2.out',
            scrollTrigger: { trigger: card, start: 'top 86%' },
        });
    });
}

if (window.lucide) {
    lucide.createIcons();
}

document.querySelectorAll('.ajax-form').forEach((form) => {
    form.addEventListener('submit', async (event) => {
        event.preventDefault();
        const status = form.querySelector('.form-status');
        status.textContent = 'Sending...';
        status.classList.remove('error');
        try {
            const response = await fetch(form.action, { method: 'POST', body: new FormData(form) });
            const payload = await response.json();
            if (!response.ok) throw new Error(payload.error || 'Request failed.');
            status.textContent = payload.message || 'Saved.';
            form.reset();
        } catch (error) {
            status.textContent = error.message;
            status.classList.add('error');
        }
    });
});

const CART_KEY = 'gg_public_cart';

function readCart() {
    try {
        return JSON.parse(localStorage.getItem(CART_KEY) || '[]');
    } catch {
        return [];
    }
}

function writeCart(cart) {
    localStorage.setItem(CART_KEY, JSON.stringify(cart));
    updateCartCount();
}

function updateCartCount() {
    const count = readCart().reduce((sum, item) => sum + Number(item.qty || 0), 0);
    document.querySelectorAll('[data-cart-count]').forEach((node) => {
        node.textContent = String(count);
        node.classList.toggle('has-items', count > 0);
    });
}

function money(value) {
    return `Rs. ${Number(value || 0).toLocaleString('en-IN', { minimumFractionDigits: 2, maximumFractionDigits: 2 })}`;
}

document.querySelectorAll('.add-to-cart').forEach((button) => {
    button.addEventListener('click', () => {
        const card = button.closest('[data-cart-product]');
        if (!card) return;
        const product = JSON.parse(card.dataset.cartProduct || '{}');
        const cart = readCart();
        const existing = cart.find((item) => Number(item.id) === Number(product.id));
        if (existing) {
            existing.qty += 1;
        } else {
            cart.push({ ...product, qty: 1 });
        }
        writeCart(cart);
        button.textContent = 'Added';
        setTimeout(() => { button.textContent = 'Add to Cart'; }, 900);
    });
});

function renderCartPage() {
    const list = document.getElementById('cartItems');
    const empty = document.getElementById('cartEmpty');
    const summary = document.getElementById('cartSummary');
    const totalNode = document.getElementById('cartTotal');
    const payload = document.getElementById('cartPayload');
    if (!list) return;

    const cart = readCart();
    list.innerHTML = '';
    empty?.classList.toggle('show', cart.length === 0);
    summary?.classList.toggle('show', cart.length > 0);
    if (payload) payload.value = JSON.stringify(cart);

    let total = 0;
    cart.forEach((item) => {
        total += Number(item.price) * Number(item.qty);
        const row = document.createElement('article');
        row.className = 'cart-row';
        row.innerHTML = `
            <div>
                <span>${item.category || 'Product'}</span>
                <h3>${item.name}</h3>
                <p>SKU ${item.sku}</p>
            </div>
            <div class="qty-control">
                <button type="button" data-cart-dec="${item.id}">-</button>
                <strong>${item.qty}</strong>
                <button type="button" data-cart-inc="${item.id}">+</button>
            </div>
            <b>${money(Number(item.price) * Number(item.qty))}</b>
            <button class="ghost-btn" type="button" data-cart-remove="${item.id}">Remove</button>
        `;
        list.appendChild(row);
    });
    if (totalNode) totalNode.textContent = money(total);
}

document.addEventListener('click', (event) => {
    const target = event.target;
    if (!(target instanceof HTMLElement)) return;
    const cart = readCart();
    const inc = target.dataset.cartInc;
    const dec = target.dataset.cartDec;
    const remove = target.dataset.cartRemove;
    if (!inc && !dec && !remove) return;

    const id = Number(inc || dec || remove);
    const item = cart.find((entry) => Number(entry.id) === id);
    if (item && inc) item.qty += 1;
    if (item && dec) item.qty = Math.max(1, item.qty - 1);
    const next = remove ? cart.filter((entry) => Number(entry.id) !== id) : cart;
    writeCart(next);
    renderCartPage();
});

document.getElementById('clearCart')?.addEventListener('click', () => {
    writeCart([]);
    renderCartPage();
});

document.querySelector('.cart-checkout-form')?.addEventListener('submit', async (event) => {
    event.preventDefault();
    const form = event.currentTarget;
    const status = form.querySelector('.form-status');
    const cart = readCart();
    if (!cart.length) {
        status.textContent = 'Add at least one product before checkout.';
        status.classList.add('error');
        return;
    }
    document.getElementById('cartPayload').value = JSON.stringify(cart);
    status.textContent = 'Submitting order...';
    status.classList.remove('error');
    try {
        const response = await fetch(form.action, { method: 'POST', body: new FormData(form) });
        const payload = await response.json();
        if (!response.ok) throw new Error(payload.error || 'Checkout failed.');
        status.textContent = payload.message || 'Order submitted.';
        writeCart([]);
        renderCartPage();
        form.reset();
    } catch (error) {
        status.textContent = error.message;
        status.classList.add('error');
    }
});

updateCartCount();
renderCartPage();
