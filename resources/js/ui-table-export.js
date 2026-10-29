const template = document.getElementById('ui-table-export-template');
const tables = new WeakMap();

function collectTable(table) {
    const header = table.tHead?.rows[table.tHead.rows.length - 1];
    if (!header) throw new Error('Tabel belum siap dicetak.');
    const bodyRows = [...table.tBodies].flatMap(body => [...body.rows]);
    const indexes = [...header.cells].flatMap((cell, index) => {
        const label = cell.textContent.trim();
        const isAction = /^(aksi|action|actions)$/i.test(label)
            || (!label && bodyRows.some(row => row.cells[index]?.querySelector('.ui-icon-button, .ui-table-actions')));
        return isAction ? [] : [index];
    });
    const columns = indexes.map(index => header.cells[index].textContent.trim() || `Kolom ${index + 1}`);
    const rows = [...table.tBodies].flatMap(body => [...body.rows])
        .filter(row => row.cells.length === header.cells.length && !row.querySelector('[colspan]'))
        .filter(row => !row.hidden && getComputedStyle(row).display !== 'none')
        .map(row => indexes.map(index => {
            const cell = row.cells[index].cloneNode(true);
            cell.querySelectorAll('button, .ui-table-actions, .ui-action-menu, dialog, [x-cloak], [hidden], input[type="hidden"], input[type="file"], script, style').forEach(el => el.remove());
            cell.querySelectorAll('input, select, textarea').forEach(input => {
                const original = row.cells[index].querySelector(`[name="${CSS.escape(input.name)}"]`);
                const value = original || input;
                input.replaceWith(document.createTextNode(value.tagName === 'SELECT'
                    ? value.selectedOptions[0]?.textContent || '' : value.value || ''));
            });
            return cell.textContent.replace(/\s+/g, ' ').trim().slice(0, 4000);
        }));
    if (!rows.length) throw new Error('Belum ada data untuk dicetak.');
    if (rows.length > 100) throw new Error('Pilih maksimal 100 baris per halaman untuk ekspor.');
    return { title: (document.querySelector('main h1, main h2')?.textContent.trim() || 'Laporan Mandala').slice(0, 200), columns, rows };
}

function printTable(data) {
    const frame = document.createElement('iframe');
    frame.title = 'Pratinjau cetak tabel';
    frame.style.cssText = 'position:fixed;width:0;height:0;border:0;';
    document.body.append(frame);
    const doc = frame.contentDocument;
    doc.open();
    const letterheadUrl = template.dataset.letterheadUrl;
    doc.write(`<!doctype html><html lang="id"><head><meta charset="utf-8"><style>
      @page{size:A4 portrait;margin:32mm 16mm 39mm}
      *{box-sizing:border-box}html,body{margin:0;padding:0}
      body{font:9pt/1.45 Arial,sans-serif;color:#18181b;-webkit-print-color-adjust:exact;print-color-adjust:exact}
      .letterhead{position:fixed;top:0;left:0;width:210mm;height:297mm;z-index:-1}
      h1{font-size:17pt;line-height:1.2;margin:0 0 2mm}p{font-size:8pt;color:#52525b;margin:0 0 5mm}
      table{width:100%;table-layout:fixed;border-collapse:collapse}thead{display:table-header-group}
      th,td{padding:1.6mm 1.8mm;border:1px solid #d4d4d8;text-align:left;vertical-align:top;overflow-wrap:anywhere;word-break:break-word;font-size:${data.columns.length > 5 ? '6.5pt' : '8pt'}}
      th{background:#f4f4f5;font-weight:700}tr{break-inside:avoid}
    </style></head><body><img class="letterhead" src="${letterheadUrl}" alt=""></body></html>`);
    doc.close();
    doc.title = data.title;
    const heading = doc.createElement('h1'); heading.textContent = data.title;
    const note = doc.createElement('p'); note.textContent = `MANDALA · Halaman aktif · ${data.rows.length} baris`;
    const table = doc.createElement('table');
    const head = table.createTHead().insertRow();
    data.columns.forEach(value => { const th = doc.createElement('th'); th.textContent = value; head.append(th); });
    const body = table.createTBody();
    data.rows.forEach(values => { const row = body.insertRow(); values.forEach(value => { row.insertCell().textContent = value; }); });
    doc.body.append(heading, note, table);
    frame.contentWindow.addEventListener('afterprint', () => frame.remove(), { once: true });
    requestAnimationFrame(() => { frame.contentWindow.focus(); frame.contentWindow.print(); });
}

async function exportPdf(data) {
    const response = await fetch(template.dataset.pdfUrl, {
        method: 'POST', credentials: 'same-origin',
        headers: { 'Content-Type': 'application/json', Accept: 'application/pdf, application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || '' },
        body: JSON.stringify(data),
    });
    if (!response.ok || !response.headers.get('content-type')?.includes('application/pdf'))
        throw new Error(response.status === 419 || response.status === 401 ? 'Sesi berakhir. Muat ulang halaman lalu coba lagi.' : 'PDF belum dapat dibuat. Silakan coba lagi.');
    const url = URL.createObjectURL(await response.blob());
    const link = document.createElement('a'); link.href = url;
    link.download = `${data.title.replace(/[^\p{L}\p{N} -]/gu, '').trim() || 'mandala'}.pdf`;
    link.click(); setTimeout(() => URL.revokeObjectURL(url), 1000);
}

function enhanceTables() {
    if (!template) return;
    document.querySelectorAll('main table').forEach(table => {
        if (table.dataset.exportReady || !table.tHead || table.closest('dialog, [data-no-export]')) return;
        table.dataset.exportReady = 'true';
        const toolbar = template.content.firstElementChild.cloneNode(true);
        const wrapper = table.closest('.ui-table-scroll, .overflow-x-auto');
        (wrapper || table).before(toolbar);
        tables.set(toolbar, table);
    });
}

document.addEventListener('click', async event => {
    const button = event.target.closest('[data-table-export]');
    if (!button) return;
    const toolbar = button.closest('.ui-table-export');
    const table = tables.get(toolbar);
    if (!table || button.disabled) return;
    const status = toolbar.querySelector('[role="status"]');
    const buttons = toolbar.querySelectorAll('button');
    buttons.forEach(item => item.disabled = true);
    toolbar.setAttribute('aria-busy', 'true');
    try {
        const data = collectTable(table);
        status.textContent = button.dataset.tableExport === 'pdf' ? 'Menyiapkan PDF…' : '';
        if (button.dataset.tableExport === 'pdf') await exportPdf(data); else printTable(data);
        status.textContent = '';
    } catch (error) {
        status.textContent = error.message;
        window.dispatchEvent(new CustomEvent('ui:toast', { detail: { type: 'error', message: error.message } }));
    } finally {
        buttons.forEach(item => item.disabled = false);
        toolbar.removeAttribute('aria-busy');
    }
});

enhanceTables();
const main = document.querySelector('main');
if (template && main) new MutationObserver(enhanceTables).observe(main, { childList: true, subtree: true });
