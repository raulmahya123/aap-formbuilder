// Shared action controls preserve native forms, validation, named submitters and policy-gated markup.
import { openDialog } from './ui-dialogs';
const originalButtonContent = new WeakMap();
document.addEventListener('change', event => {
    if (event.target.matches('[data-site-selector] select')) event.target.form.requestSubmit();
});
let pendingConfirmation = null;
let resetTrigger = null;

export function isFormDirty(form) {
    if (!form || !(form instanceof HTMLFormElement)) return false;
    for (const el of form.elements) {
        if (!el.name || el.disabled || el.type === 'submit' || el.type === 'button' || el.type === 'hidden') continue;
        if (el.type === 'checkbox' || el.type === 'radio') {
            if (el.checked !== el.defaultChecked) return true;
        } else if (el.type === 'file') {
            if (el.files && el.files.length > 0) return true;
        } else if (el instanceof HTMLSelectElement) {
            if ([...el.options].some(option => option.selected !== option.defaultSelected)) return true;
        } else if (el.value !== el.defaultValue) {
            return true;
        }
    }
    return false;
}

document.addEventListener('cancel', event => {
    const dialog = event.target;
    if (dialog.matches?.('dialog[data-dirty-check="true"]') && isFormDirty(dialog.querySelector('form')) && !window.confirm('Ada perubahan yang belum disimpan. Yakin ingin menutup?')) event.preventDefault();
}, true);

document.addEventListener('submit', event => {
    const form = event.target;
    if (!(form instanceof HTMLFormElement) || event.defaultPrevented) return;
    const submitter = event.submitter;
    if (form.dataset.uiSubmitting === 'true') {
        event.preventDefault();
        return;
    }
    if (form.dataset.confirm && form.dataset.uiConfirmed !== 'true') {
        const dialog = document.getElementById('ui-action-confirm');
        if (dialog) {
            event.preventDefault();
            pendingConfirmation = { form, submitter };
            const destructive = form.querySelector('input[name="_method"]')?.value.toUpperCase() === 'DELETE'
                || submitter?.classList.contains('ui-icon-button--delete');
            const confirmMsg = form.dataset.confirm || 'Yakin ingin menghapus data ini?';

            let objectName = '';
            const match = confirmMsg.match(/^Hapus\s+(.*?)\s*(\?|$)/i);
            if (match && match[1]) {
                objectName = match[1];
            }

            const titleEl = dialog.querySelector('[data-delete-title]') || dialog.querySelector('#ui-confirm-title');
            if (titleEl) {
                if (destructive) {
                    titleEl.textContent = objectName ? `Hapus ${objectName.charAt(0).toUpperCase() + objectName.slice(1)}?` : 'Konfirmasi Penghapusan';
                } else {
                    titleEl.textContent = 'Konfirmasi Tindakan';
                }
            }

            const descEl = dialog.querySelector('[data-delete-desc]') || dialog.querySelector('#ui-confirm-message');
            if (descEl) {
                if (destructive && objectName) {
                    const remaining = match ? confirmMsg.slice(match[0].length).trim() : '';
                    descEl.textContent = `${objectName.charAt(0).toUpperCase() + objectName.slice(1)} akan dihapus.${remaining ? ' '+remaining : ''}`;
                } else {
                    descEl.textContent = confirmMsg;
                }
            }

            const warningEl = dialog.querySelector('[data-delete-warning]');
            if (warningEl) {
                warningEl.style.display = destructive ? 'flex' : 'none';
            }

            const btnTextEl = dialog.querySelector('[data-delete-btn-text]');
            if (btnTextEl) {
                btnTextEl.textContent = destructive && objectName ? `Hapus ${objectName.split(' ')[0]}` : (destructive ? 'Hapus' : 'Lanjutkan');
            }

            const confirmButton = dialog.querySelector('[data-dialog-confirm]');
            if (confirmButton) {
                confirmButton.classList.toggle('ui-button--danger', destructive);
                confirmButton.classList.toggle('ui-button--primary', !destructive);
            }

            openDialog(dialog);
            dialog.querySelector('[data-dialog-cancel]')?.focus();
            return;
        }
        // Keep confirmation functional for a standalone view without the shared dialog host.
        if (!window.confirm(form.dataset.confirm)) { event.preventDefault(); return; }
    }
    form.dataset.uiSubmitting = 'true';
    if (submitter) {
        originalButtonContent.set(submitter, submitter.innerHTML);
        submitter.setAttribute('aria-busy', 'true');
        submitter.classList.add('is-loading');
        submitter.innerHTML = '<span class="loading-spinner" aria-hidden="true"></span>'
            + (submitter.classList.contains('ui-icon-button') ? '' : '<span>Memproses…</span>');
        // Do not disable the submitter: native form serialization must retain its name/value.
    }
});

document.addEventListener('click', event => {
    const cancel = event.target.closest('[data-dialog-cancel]');
    if (cancel) {
        const dialog = cancel.closest('dialog');
        if (dialog) {
            const form = dialog.querySelector('form');
            if (dialog.dataset.dirtyCheck === 'true' && form && isFormDirty(form)) {
                if (!window.confirm('Ada perubahan yang belum disimpan. Yakin ingin menutup?')) return;
            }
            dialog.close();
        }
        return;
    }
    if (event.target.closest('[data-dialog-confirm]')) {
        const btn = event.target.closest('[data-dialog-confirm]');
        if (btn.classList.contains('is-loading')) return;
        btn.classList.add('is-loading');
        const pending = pendingConfirmation;
        pendingConfirmation = null;
        const dialog = document.getElementById('ui-action-confirm');
        if (dialog) dialog.close();
        btn.classList.remove('is-loading');
        if (!pending) return;
        pending.form.dataset.uiConfirmed = 'true';
        try { pending.form.requestSubmit(pending.submitter || undefined); }
        finally { delete pending.form.dataset.uiConfirmed; }
        return;
    }
    const trigger = event.target.closest('[data-reset-url]');
    if (trigger) {
        const dialog = document.getElementById('ui-password-reset');
        const form = dialog.querySelector('form');
        resetTrigger = trigger;
        form.reset();
        delete form.dataset.uiSubmitting;
        form.action = trigger.dataset.resetUrl;
        dialog.querySelector('[data-reset-name]').textContent = trigger.dataset.resetUser;
        openDialog(dialog);
        form.querySelector('[name="password"]').focus();
    }
});

document.addEventListener('input', event => {
    const form = event.target.closest('[data-reset-form]');
    if (!form) return;
    const confirmation = form.elements.password_confirmation;
    confirmation.setCustomValidity(confirmation.value && confirmation.value !== form.elements.password.value ? 'Konfirmasi password tidak sama.' : '');
});

document.addEventListener('close', event => {
    if (event.target.id === 'ui-action-confirm') {
        const trigger = pendingConfirmation?.submitter;
        pendingConfirmation = null;
        trigger?.focus();
    }
    if (event.target.id === 'ui-password-reset') {
        event.target.querySelector('form').reset();
        event.target.querySelector('[name="password_confirmation"]').setCustomValidity('');
        resetTrigger?.focus();
    }
}, true);

document.addEventListener('toggle', event => {
    const menu = event.target;
    if (!menu.matches?.('.ui-action-menu')) return;
    const trigger = document.querySelector(`[popovertarget="${menu.id}"]`);
    trigger?.setAttribute('aria-expanded', event.newState === 'open' ? 'true' : 'false');
    if (event.newState !== 'open') return;
    const rect = trigger.getBoundingClientRect();
    const width = menu.offsetWidth, height = menu.offsetHeight;
    menu.style.setProperty('--menu-left', `${Math.max(12, Math.min(rect.right - width, innerWidth - width - 12))}px`);
    menu.style.setProperty('--menu-top', `${rect.bottom + height + 12 > innerHeight ? Math.max(12, rect.top - height - 4) : rect.bottom + 4}px`);
    const items = [...menu.querySelectorAll('a,button')].filter(el => !el.disabled);
    items.forEach(el => { el.setAttribute('role', 'menuitem'); el.tabIndex = -1; });
    if (items[0]) { items[0].tabIndex = 0; items[0].focus(); }
}, true);

document.addEventListener('keydown', event => {
    const menu = event.target.closest('.ui-action-menu');
    if (!menu) return;
    const trigger = document.querySelector(`[popovertarget="${menu.id}"]`);
    if (event.key === 'Escape' || event.key === 'Tab') {
        menu.hidePopover();
        trigger?.focus();
        if (event.key === 'Escape') event.preventDefault();
        return;
    }
    if (!['ArrowDown','ArrowUp','Home','End'].includes(event.key)) return;
    event.preventDefault();
    const items = [...menu.querySelectorAll('a,button')].filter(el => !el.disabled);
    const index = items.indexOf(document.activeElement);
    const next = event.key === 'Home' ? 0 : event.key === 'End' ? items.length - 1 : (index + (event.key === 'ArrowDown' ? 1 : -1) + items.length) % items.length;
    items.forEach((el,i) => el.tabIndex = i === next ? 0 : -1);
    items[next]?.focus();
});

window.addEventListener('pageshow', () => {
    document.querySelectorAll('form[data-ui-submitting]').forEach(form => delete form.dataset.uiSubmitting);
    document.querySelectorAll('[aria-busy="true"].is-loading').forEach(button => {
        if (originalButtonContent.has(button)) button.innerHTML = originalButtonContent.get(button);
        button.removeAttribute('aria-busy');
        button.classList.remove('is-loading');
    });
});

window.addEventListener('resize', () => {
    document.querySelectorAll('.ui-action-menu:popover-open').forEach(menu => menu.hidePopover());
});
