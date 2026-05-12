if (window.lucide) {
    lucide.createIcons();
}

const sidebar = document.getElementById('erpSidebar');
document.getElementById('sidebarToggle')?.addEventListener('click', () => {
    sidebar?.classList.toggle('open');
});

document.getElementById('themeToggle')?.addEventListener('click', () => {
    document.body.classList.toggle('light');
    localStorage.setItem('gg-theme', document.body.classList.contains('light') ? 'light' : 'dark');
});

if (localStorage.getItem('gg-theme') === 'light') {
    document.body.classList.add('light');
}

function toast(message) {
    const box = document.getElementById('toast');
    if (!box) return;
    box.textContent = message;
    box.classList.add('show');
    setTimeout(() => box.classList.remove('show'), 2600);
}

document.querySelectorAll('.erp-form').forEach((form) => {
    form.addEventListener('submit', async (event) => {
        event.preventDefault();
        const button = form.querySelector('button[type="submit"]');
        const original = button?.textContent;
        if (button) button.textContent = 'Saving...';
        try {
            const response = await fetch(form.action, { method: 'POST', body: new FormData(form) });
            const payload = await response.json();
            if (!response.ok) throw new Error(payload.error || 'Save failed.');
            toast(payload.message || 'Saved.');
            setTimeout(() => window.location.reload(), 700);
        } catch (error) {
            toast(error.message);
        } finally {
            if (button) button.textContent = original;
        }
    });
});

document.querySelectorAll('.table-search').forEach((input) => {
    input.addEventListener('input', () => {
        const table = input.closest('.table-panel')?.querySelector('tbody');
        const term = input.value.toLowerCase();
        table?.querySelectorAll('tr').forEach((row) => {
            row.style.display = row.textContent.toLowerCase().includes(term) ? '' : 'none';
        });
    });
});

const productForm = document.getElementById('productForm');
const productFormTitle = document.getElementById('productFormTitle');

function fillManagedForm(button, selector, title) {
    const row = button.closest('tr');
    const form = document.querySelector(selector);
    if (!row || !form) return;
    const data = JSON.parse(row.dataset.record || row.dataset.product || '{}');
    Object.entries(data).forEach(([key, value]) => {
        const field = form.querySelector(`[name="${key}"]`);
        if (field) field.value = value ?? '';
    });
    const titleNode = form.querySelector('[data-form-title]');
    if (titleNode && title) titleNode.textContent = title;
    form.scrollIntoView({ behavior: 'smooth', block: 'start' });
    toast('Editing record. Update fields and save.');
}

document.querySelectorAll('.edit-product').forEach((button) => {
    button.addEventListener('click', () => {
        const row = button.closest('tr');
        if (!row || !productForm) return;
        const data = JSON.parse(row.dataset.product || '{}');
        Object.entries(data).forEach(([key, value]) => {
            const field = productForm.querySelector(`[name="${key}"]`);
            if (field) field.value = value ?? '';
        });
        if (productFormTitle) productFormTitle.textContent = 'Update Product';
        productForm.scrollIntoView({ behavior: 'smooth', block: 'start' });
        toast('Editing product. Update fields and save.');
    });
});

document.getElementById('productFormReset')?.addEventListener('click', () => {
    productForm?.reset();
    const idField = productForm?.querySelector('[name="product_id"]');
    if (idField) idField.value = '';
    if (productFormTitle) productFormTitle.textContent = 'Add Product';
});

document.querySelectorAll('.delete-form').forEach((form) => {
    form.addEventListener('submit', async (event) => {
        event.preventDefault();
        if (!confirm('Delete this product from the active catalog?')) return;
        try {
            const response = await fetch(form.action, { method: 'POST', body: new FormData(form) });
            const payload = await response.json();
            if (!response.ok) throw new Error(payload.error || 'Delete failed.');
            toast(payload.message || 'Deleted.');
            setTimeout(() => window.location.reload(), 700);
        } catch (error) {
            toast(error.message);
        }
    });
});

document.querySelectorAll('.inline-update-form').forEach((form) => {
    form.addEventListener('submit', async (event) => {
        event.preventDefault();
        try {
            const response = await fetch(form.action, { method: 'POST', body: new FormData(form) });
            const payload = await response.json();
            if (!response.ok) throw new Error(payload.error || 'Update failed.');
            toast(payload.message || 'Updated.');
            setTimeout(() => window.location.reload(), 700);
        } catch (error) {
            toast(error.message);
        }
    });
});

document.querySelectorAll('[data-edit-target]').forEach((button) => {
    button.addEventListener('click', () => {
        fillManagedForm(button, button.dataset.editTarget, button.dataset.editTitle || 'Update Record');
    });
});

document.querySelectorAll('[data-reset-form]').forEach((button) => {
    button.addEventListener('click', () => {
        const form = document.querySelector(button.dataset.resetForm);
        if (!form) return;
        form.reset();
        form.querySelectorAll('input[type="hidden"][name$="_id"], input[type="hidden"][name="id"]').forEach((field) => {
            field.value = '';
        });
        const titleNode = form.querySelector('[data-form-title]');
        if (titleNode) titleNode.textContent = button.dataset.resetTitle || 'Add Record';
    });
});

if (window.Chart && document.getElementById('salesChart')) {
    new Chart(document.getElementById('salesChart'), {
        type: 'line',
        data: {
            labels: ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun'],
            datasets: [{
                data: window.dashboardSales || [],
                borderColor: '#00FFD1',
                backgroundColor: 'rgba(0,255,209,0.12)',
                fill: true,
                tension: 0.42,
            }],
        },
        options: {
            plugins: { legend: { display: false } },
            scales: {
                x: { grid: { color: 'rgba(255,255,255,0.06)' }, ticks: { color: 'rgba(255,255,255,0.62)' } },
                y: { grid: { color: 'rgba(255,255,255,0.06)' }, ticks: { color: 'rgba(255,255,255,0.62)' } },
            },
        },
    });
}
