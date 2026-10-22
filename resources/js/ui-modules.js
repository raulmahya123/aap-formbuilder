import { openDialog } from './ui-dialogs';
const dialog = document.getElementById('ui-remote-modal');
let controller, returnFocus;
const body = dialog?.querySelector('[data-remote-body]');
const save = dialog?.querySelector('[data-remote-save]');
let sourceUrl;
function initMandalaForm(form, restoreSite = null) {
    const dataset = form.querySelector('[data-form-sites]');
    if (!dataset || form.dataset.mandalaBound) return;
    form.dataset.mandalaBound = 'true';
    const map = JSON.parse(dataset.textContent || '{}');
    const company = form.elements.company_id, site = form.elements.site_id;
    const populate = keep => {
        const rows = map[String(company.value)] || [];
        site.replaceChildren(new Option('Pilih site…', ''));
        rows.forEach(row => site.add(new Option(row.name, row.id, String(row.id) === String(keep), String(row.id) === String(keep))));
        site.disabled = !company.value;
    };
    populate(restoreSite ?? site.value);
    company.addEventListener('change', () => populate(null));
    const type = form.elements.type, pdf = form.querySelector('#pdfBox'), builder = form.querySelector('#builderBox'), file = form.elements.pdf;
    if (pdf && builder) {
        const sync = () => { const isFile = type.value === 'pdf'; pdf.classList.toggle('hidden', !isFile); builder.classList.toggle('hidden', isFile); file.required = isFile; if (!isFile) file.value = ''; };
        type.addEventListener('change', sync); sync();
    }
}
document.querySelectorAll('form').forEach(form => initMandalaForm(form));
document.addEventListener('change', event => {
    const select = event.target;
    if (select.matches('[data-assignment-role]')) {
        const form = select.form;
        const isHo = ['admin_ho', 'super_admin'].includes(select.value);
        const sites = form.querySelector('[data-assignment-sites]');
        if (sites) sites.disabled = isHo;
        const customOption = form.querySelector('[name="feature_mode"] option[value="custom"]');
        if (customOption) customOption.disabled = isHo;
        if (isHo && form.elements.feature_mode) {
            form.elements.feature_mode.value = 'keep';
            form.elements.feature_mode.dispatchEvent(new Event('change', { bubbles: true }));
        }
        return;
    }
    if (!select.matches('[data-feature-user-select]')) return;
    const form = select.form;
    const settings = form?.querySelector('[data-feature-settings]');
    if (!settings) return;
    const allowed = JSON.parse(select.selectedOptions[0]?.dataset.features || 'null');
    const mode = form.elements.feature_mode;
    mode.value = allowed === null ? 'keep' : 'custom';
    mode.dispatchEvent(new Event('change', { bubbles: true }));
    form.querySelectorAll('input[name="feature_access[]"]').forEach(field => { field.checked = Array.isArray(allowed) && allowed.includes(field.value); });
    const assigned = JSON.parse(select.selectedOptions[0]?.dataset.sites || '[]').map(String);
    form.querySelectorAll('input[name="site_ids[]"]').forEach(field => { field.checked = assigned.includes(field.value); });
    if (form.elements.role) {
        form.elements.role.value = select.selectedOptions[0]?.dataset.role || '';
        form.elements.role.dispatchEvent(new Event('change', { bubbles: true }));
    }
});
async function openRemote(url, restore = null) {
    if (!dialog) { location.assign(url); return; }
    const target = new URL(url, location.href);
    if (target.origin !== location.origin || !['/admin/', '/ccm-reports/'].some(prefix => target.pathname.startsWith(prefix))) return;
    if (!openDialog(dialog)) return;
    sourceUrl = target.href;
    controller?.abort(); controller = new AbortController();
    const requestController = controller;
    const timeout = setTimeout(() => requestController.abort('timeout'), 15000);
    body.replaceChildren(); body.setAttribute('aria-busy', 'true');
    const loading = document.createElement('p'); loading.textContent = 'Memuat formulir…'; loading.setAttribute('role', 'status'); body.append(loading);
    save.hidden = true;
    dialog.querySelector('[data-remote-edit]').hidden = true;
    dialog.querySelector('.ui-modal__title').textContent = 'Memuat…';
    try {
        const response = await fetch(target, { headers: { 'X-Requested-With': 'XMLHttpRequest' }, signal: requestController.signal });
        if (!response.ok || response.redirected) throw new Error(response.status === 403 ? 'Anda tidak memiliki akses ke data ini.' : 'Formulir gagal dimuat. Silakan coba lagi.');
        const parsed = new DOMParser().parseFromString(await response.text(), 'text/html');
        if (controller !== requestController || !dialog.open) return;
        const title = target.searchParams.has('edit') ? 'Edit HIPO / Nearmiss' : parsed.querySelector('.ui-page-title')?.textContent.trim() || 'Detail';
        dialog.querySelector('.ui-modal__title').textContent = title;
        const main = parsed.querySelector('main');
        const form = main?.querySelector('#page-primary-form') || main?.querySelector('form[method="POST"],form[method="post"]');
        body.replaceChildren();
        if (form && !target.searchParams.has('detail')) {
            form.id = 'ui-remote-form'; form.dataset.remoteForm = '';
            form.classList.remove('ui-card');
            const marker = document.createElement('input'); marker.type = 'hidden'; marker.name = '_ui_modal_url'; marker.value = target.href; form.append(marker);
            // Keep the existing inputs, CSRF, method, action and Alpine bindings intact.
            const imported = document.importNode(form, true); body.append(imported);
            const count = new Set([...imported.elements].filter(field => field.name && field.type !== 'hidden').map(field => field.name)).size;
            dialog.classList.toggle('ui-modal--lg', count > 4);
            dialog.classList.toggle('ui-modal--md', count <= 4);
            let description = dialog.querySelector('.ui-modal__description');
            if (!description) { description = document.createElement('p'); description.className = 'ui-modal__description'; dialog.querySelector('.ui-modal__header-content').append(description); }
            description.textContent = parsed.querySelector('.ui-page-description')?.textContent.trim() || '';
            const site = imported.querySelector('select[name="site_id"][onchange]');
            if (site && target.pathname.includes('/qa/create')) {
                site.removeAttribute('onchange');
                site.addEventListener('change', () => {
                    const next = new URL(target); next.searchParams.set('site_id', site.value);
                    const values = Object.fromEntries(new FormData(imported)); delete values['recipient_ids[]'];
                    if (imported.querySelector('input[type=file]')?.files.length && !confirm('Lampiran perlu dipilih ulang setelah mengganti site. Lanjutkan?')) return;
                    openRemote(next.href, values);
                });
            }
            const derived = imported.querySelector('#chkDerived'), formula = imported.querySelector('#formulaWrap');
            if (derived && formula) { const sync = () => formula.style.display = derived.checked ? '' : 'none'; derived.addEventListener('change', sync); sync(); }
            imported.querySelectorAll('button:not([type="button"]):not([type="reset"]),input[type="submit"]').forEach(button => { if (!button.hasAttribute('form') || button.getAttribute('form') === 'ui-remote-form') button.hidden = true; });
            main.querySelectorAll('form[data-modal-auxiliary]').forEach(aux => body.append(document.importNode(aux, true)));
            const builderLink = main.querySelector('.ui-page-actions a[href*="builder"]');
            if (builderLink) imported.prepend(document.importNode(builderLink, true));
            const update = imported.querySelector('[name="_method"]')?.value;
            save.textContent = update && update.toUpperCase() !== 'POST' ? 'Simpan Perubahan' : 'Simpan Data'; save.hidden = false;
            if (restore) for (const field of imported.elements) {
                const key = field.name?.replace(/\[\]$/, '');
                if (!field.name || ['file','password','hidden','submit'].includes(field.type) || (!(field.name in restore) && !(key in restore))) continue;
                const value = restore[field.name] ?? restore[key];
                if (field instanceof HTMLSelectElement && field.multiple) [...field.options].forEach(option => option.selected = (Array.isArray(value) ? value : [value]).map(String).includes(option.value));
                else if (['checkbox','radio'].includes(field.type)) field.checked = (Array.isArray(value) ? value : [value]).map(String).includes(field.value);
                else field.value = value ?? '';
            }
            initMandalaForm(imported, restore?.site_id);
            window.dispatchEvent(new CustomEvent('ui:form-mounted'));
            requestAnimationFrame(() => (imported.querySelector('[aria-invalid="true"]') || imported.querySelector('input:not([type=hidden]),select,textarea'))?.focus());
        } else {
            if (!main) throw new Error('Detail belum dapat ditampilkan.');
            const edit = parsed.querySelector('.ui-page-actions a[href*="/edit"]');
            main.querySelectorAll('.ui-page-header,script').forEach(el => el.remove());
            body.append(...[...main.childNodes].map(node => document.importNode(node, true)));
            if (edit) { const control = dialog.querySelector('[data-remote-edit]'); control.href = edit.href; control.hidden = false; }
        }
        dialog.querySelector('.ui-modal__footer [data-dialog-cancel]').textContent = save.hidden ? 'Tutup' : 'Batal';
    } catch (error) {
        if (controller !== requestController || !dialog.open || (requestController.signal.aborted && requestController.signal.reason !== 'timeout')) return;
        dialog.querySelector('.ui-modal__title').textContent = 'Gagal memuat';
        save.hidden = true;
        dialog.querySelector('[data-remote-edit]').hidden = true;
        const message = document.createElement('p'); message.textContent = requestController.signal.reason === 'timeout' ? 'Waktu memuat habis. Periksa koneksi lalu coba lagi.' : 'Data gagal dimuat. Silakan coba lagi.'; message.setAttribute('role', 'alert');
        const retry = document.createElement('button'); retry.type = 'button'; retry.className = 'ui-button ui-button--secondary'; retry.textContent = 'Coba lagi'; retry.addEventListener('click', () => openRemote(sourceUrl, restore));
        body.replaceChildren(message, retry);
    } finally { clearTimeout(timeout); if (controller === requestController) body.removeAttribute('aria-busy'); }
}
document.addEventListener('click', event => {
    const opener = event.target.closest('[data-dialog-open]');
    if (opener) {
        const target = document.getElementById(opener.dataset.dialogOpen);
        if (openDialog(target)) target.addEventListener('close', () => opener.focus(), { once: true });
        return;
    }
    const link = event.target.closest('a[data-modal-form],a[data-modal-detail]');
    if (!link || event.ctrlKey || event.metaKey || event.shiftKey || event.altKey || event.button !== 0) return;
    event.preventDefault(); returnFocus = link;
    const url = new URL(link.href); if (link.hasAttribute('data-modal-detail')) url.searchParams.set('detail', '1');
    openRemote(url.href);
});
dialog?.addEventListener('close', () => { controller?.abort(); body.replaceChildren(); returnFocus?.focus(); });
const restore = document.querySelector('[data-modal-restore]');
if (restore) { const data = JSON.parse(restore.textContent); if (data.url) openRemote(data.url, data.input); }
document.querySelectorAll('dialog[data-auto-open]').forEach(modal => openDialog(modal));
