const provider = document.querySelector('[data-toast-provider]');
const timers = new WeakMap();
function activateToast(toast) {
    const lifetime = toast.dataset.type === 'error' ? 0 : 5000;
    let remaining = lifetime, started;
    const pause = () => { clearTimeout(timers.get(toast)); remaining -= Date.now() - started; };
    const resume = () => { if (!lifetime) return; started = Date.now(); timers.set(toast, setTimeout(() => toast.remove(), Math.max(0, remaining))); };
    toast.addEventListener('mouseenter', pause);
    toast.addEventListener('mouseleave', resume);
    toast.addEventListener('focusin', pause);
    toast.addEventListener('focusout', resume);
    toast.querySelector('[data-toast-close]')?.addEventListener('click', () => { clearTimeout(timers.get(toast)); toast.remove(); });
    resume();
}
provider?.querySelectorAll('[data-toast]').forEach(activateToast);

// AJAX callers dispatch ui:toast with a user-facing message, never an exception/response body.
window.addEventListener('ui:toast', event => {
    if (!provider) return;
    const type = ['success', 'error', 'warning', 'info'].includes(event.detail?.type) ? event.detail.type : 'info';
    let message = event.detail?.message;
    if (typeof message !== 'string' || /SQLSTATE|Stack trace|Exception:|<[^>]+>|\bselect\s+.+\s+from\b|(?:password|token|secret)\s*[=:]/is.test(message)) message = 'Terjadi masalah. Silakan coba lagi.';
    const toast = document.createElement('div');
    toast.className = `ui-toast ui-toast--${type}`;
    toast.dataset.type = type;
    toast.setAttribute('role', type === 'error' ? 'alert' : 'status');
    toast.setAttribute('aria-atomic', 'true');
    const text = document.createElement('p'); text.textContent = message.slice(0, 400);
    const close = document.createElement('button'); close.type = 'button'; close.textContent = '×'; close.setAttribute('aria-label', 'Tutup notifikasi'); close.dataset.toastClose = '';
    toast.append(text, close); provider.append(toast); activateToast(toast);
});

let errorCounter = 0;
const fieldErrors = new WeakMap();
const serverSnapshots = new WeakMap();
function describeError(field, error) {
    if (!error.id) error.id = `ui-field-error-${++errorCounter}`;
    const ids = new Set((field.getAttribute('aria-describedby') || '').split(' ').filter(Boolean));
    ids.add(error.id); field.setAttribute('aria-describedby', [...ids].join(' '));
    field.setAttribute('aria-invalid', 'true'); fieldErrors.set(field, error);
}
function nativeError(field) {
    let error = fieldErrors.get(field);
    const group = field.type === 'radio' ? [...field.form.elements].filter(el => el.type === 'radio' && el.name === field.name) : [field];
    if (!error) error = group.map(el => fieldErrors.get(el)).find(Boolean);
    if (!error) {
        error = document.createElement('p'); error.className = 'form-error'; error.dataset.nativeError = ''; error.setAttribute('role', 'alert');
        (field.closest('[data-form-field],fieldset') || (field.type === 'radio' ? field.closest('label')?.parentElement : null) || field.parentElement).append(error);
    }
    group.forEach(el => describeError(el, error));
    error.hidden = false;
    error.textContent = field.validity.valueMissing ? (field.type === 'radio' ? 'Pilih salah satu pilihan.' : 'Isian ini wajib diisi.')
        : field.validity.typeMismatch && field.type === 'email' ? 'Masukkan alamat email yang valid.' : field.validationMessage;
}
// Capture invalid events because native constraint validation happens before submit.
document.addEventListener('invalid', event => {
    const field = event.target;
    if (!field.matches('input,select,textarea')) return;
    event.preventDefault();
    nativeError(field);
    queueMicrotask(() => {
        const first = [...field.form.elements].find(el => el.willValidate && !el.validity.valid);
        first?.focus();
    });
}, true);
document.addEventListener('input', event => {
    const field = event.target, error = fieldErrors.get(field);
    if (!error) return;
    if (!field.validity.valid) { if (error.dataset.nativeError !== undefined) nativeError(field); return; }
    // Server-only constraints cannot be revalidated locally. Keep their message until resubmission.
    if (serverSnapshots.has(field)) return;
    error.hidden = true;
    const group = field.type === 'radio' ? [...field.form.elements].filter(el => el.type === 'radio' && el.name === field.name) : [field];
    group.forEach(el => el.removeAttribute('aria-invalid'));
});
document.addEventListener('change', event => event.target.dispatchEvent(new Event('input', { bubbles: true })));

function connectServerErrors() {
    document.querySelectorAll('input:not([type="hidden"]),select,textarea').forEach(field => {
        if (field.labels?.length || field.hasAttribute('aria-label')) return;
        const label = field.closest('div')?.querySelector('label:not([for])');
        if (!label || label.contains(field)) return;
        if (!field.id) field.id = `ui-field-${++errorCounter}`;
        label.htmlFor = field.id;
        if (field.required && !label.textContent.includes('*')) { const marker = document.createElement('span'); marker.className = 'ui-required'; marker.setAttribute('aria-hidden','true'); marker.textContent = ' *'; label.append(marker); }
    });
    const source = document.querySelector('[data-form-errors]');
    if (!source) return;
    const bags = JSON.parse(source.textContent || '{}');
    const restore = document.querySelector('[data-modal-restore]');
    const restoringModal = restore && JSON.parse(restore.textContent || '{}').url;
    for (const [bag, messages] of Object.entries(bags)) {
        for (const [name, errors] of Object.entries(messages)) {
            const htmlName = name.replace(/\.([^.]*)/g, '[$1]');
            let fields = [...document.querySelectorAll('input[name],select[name],textarea[name]')].filter(field => (field.name === name || field.name === htmlName) && (field.closest('[data-error-bag]')?.dataset.errorBag || 'default') === bag);
            if (restoringModal) fields = fields.filter(field => field.closest('[data-remote-form]'));
            else if (fields.some(field => field.form?.method.toUpperCase() === 'POST')) fields = fields.filter(field => field.form?.method.toUpperCase() === 'POST');
            for (const field of fields) {
                const wrapper = field.closest('[data-form-field],fieldset') || (field.type === 'radio' ? field.closest('label')?.parentElement : null) || field.parentElement;
                let error = [...wrapper.querySelectorAll('.form-error,[data-field-error],.text-red-600')].find(el => errors.some(message => el.textContent.includes(message)));
                if (!error) { error = document.createElement('p'); error.className = 'form-error'; error.textContent = errors.join(' '); wrapper.append(error); }
                describeError(field, error); serverSnapshots.set(field, field.value);
            }
        }
    }
    requestAnimationFrame(() => {
        const first = [...document.querySelectorAll('[aria-invalid="true"]')].find(field => field.getClientRects().length && !field.closest('dialog:not([open]),[hidden],[x-cloak]'));
        first?.focus();
    });
    document.querySelectorAll('[data-form-field]').forEach(wrapper => {
        const field = wrapper.querySelector('input,select,textarea');
        const helper = wrapper.querySelector('.ui-form-helper');
        if (field && helper?.id) field.setAttribute('aria-describedby', `${field.getAttribute('aria-describedby') || ''} ${helper.id}`.trim());
    });
}
connectServerErrors();
window.addEventListener('ui:form-mounted', connectServerErrors);
