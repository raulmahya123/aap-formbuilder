{{-- resources/views/admin/documents/show.blade.php --}}
@extends('layouts.app')

@section('title','Detail Dokumen')

@section('content')
<div x-data="docShow()" x-init="init()" class="max-w-7xl mx-auto p-6 space-y-6">
  {{-- HEADER --}}
  <div class="flex items-center justify-between">
    <div>
      <h1 class="text-xl font-semibold text-[#1D1C1A]">Detail Dokumen</h1>
      <p class="text-sm text-gray-600 mt-1">
        {{ $document->doc_no ?? '—' }} · Rev {{ $document->revision_no ?? 0 }} ·
        {{ $document->dept_code ?? '—' }} / {{ $document->doc_type ?? '—' }} / {{ $document->project_code ?? '—' }}
      </p>
    </div>

    @php
      $pdfRoute = \Illuminate\Support\Facades\Route::has('admin.documents.download')
                  ? route('admin.documents.download', $document)
                  : (\Illuminate\Support\Facades\Route::has('admin.documents.pdf')
                      ? route('admin.documents.pdf', $document)
                      : null);
    @endphp

    <div class="flex items-center gap-3">
      <a href="{{ route('admin.documents.index') }}" class="px-4 py-2 rounded-xl border text-[#1D1C1A]">← Kembali</a>
      @if($pdfRoute)
        <a href="{{ $pdfRoute }}" class="px-4 py-2 rounded-xl border text-[#1D1C1A]" target="_blank" rel="noopener">Unduh PDF</a>
      @endif
      <button type="button" onclick="window.print()" class="px-4 py-2 rounded-xl bg-[#7A2C2F] text-white hover:opacity-90">Cetak</button>
    </div>
  </div>

  {{-- Inject: templates & data dokumen --}}
  <script type="application/json" id="doc-templates-json">
    {!! json_encode($templatesPayload ?? [], JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES) !!}
  </script>
  <script type="application/json" id="doc-existing-json">
    {!! json_encode([
      'template_id'   => $document->template_id,
      'layout'        => $document->layout_config,
      'header'        => $document->header_config,
      'footer'        => $document->footer_config,
      'signatures'    => $document->signature_config,
      'sections'      => $document->sections,
    ], JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES) !!}
  </script>

  {{-- PREVIEW --}}
  <div class="bg-white border rounded-2xl p-5">
    <div class="flex items-center justify-between">
      <h2 class="font-semibold text-[#1D1C1A]">Preview</h2>
      <div class="flex items-center gap-2 text-sm">
        <button type="button" class="px-2 py-1 border rounded" @click="zoomOut()">–</button>
        <span x-text="Math.round(preview.zoom*100)+'%'"></span>
        <button type="button" class="px-2 py-1 border rounded" @click="zoomIn()">+</button>
      </div>
    </div>

    <div class="mt-4 rounded-xl border bg-gray-50 p-6 overflow-auto max-h-[80vh]">
      <template x-for="p in preview.pagesCount" :key="'p'+p">
        <div class="mx-auto mb-8 shadow-sm bg-white relative" :style="pageStyle()">
          {{-- margin box --}}
          <div class="absolute inset-0 pointer-events-none">
            <div class="absolute" :style="marginBoxStyle()"></div>
          </div>

          {{-- blocks per page --}}
          <template x-for="blk in preview.blocks.filter(b => (b.page||1)===p)" :key="blk.id">
            <div class="absolute ring-1 ring-gray-100" :style="blockStyle(blk)">

              {{-- HEADER --}}
              <template x-if="blk.type==='header'">
                <div class="w-full h-full px-3 py-2 flex items-center justify-between bg-white/95"
                     :style="{ fontSize: (blk.fontSize??12)+'px', textAlign: blk.align||'left', color:'#000' }">
                  <div class="flex items-center gap-2 overflow-hidden">
                    <template x-if="header?.logo?.url">
                      <img :src="header.logo.url" alt="Logo" class="h-6 w-auto object-contain">
                    </template>
                    <div class="truncate font-medium" x-text="header?.title?.text || blk.text || 'Judul Dokumen'"></div>
                  </div>
                  <div class="text-xs text-gray-600" x-show="blk.showMeta">
                    <span x-text="blk.metaRight || ''"></span>
                  </div>
                </div>
              </template>

              {{-- TEXT --}}
              <template x-if="blk.type==='text'">
                <div class="w-full h-full p-2 overflow-hidden"
                     :style="{ textAlign: blk.align||'left', fontWeight: blk.bold?'700':'400', fontSize: (blk.fontSize??preview.layout.font.size)+'pt', color:'#000' }"
                     x-text="blk.text||''"></div>
              </template>

              {{-- HTML --}}
              <template x-if="blk.type==='html'">
                <div class="w-full h-full p-3 overflow-auto text-[13px] leading-relaxed prose prose-sm max-w-none" x-html="blk.html"></div>
              </template>

              {{-- IMAGE --}}
              <template x-if="blk.type==='image'">
                <div class="w-full h-full flex items-center justify-center">
                  <template x-if="blk.src"><img :src="blk.src" class="max-w-full max-h-full object-contain"></template>
                  <template x-if="!blk.src"><span class="text-xs text-gray-400">[Gambar]</span></template>
                </div>
              </template>

              {{-- TABLE CELL --}}
              <template x-if="blk.type==='tableCell'">
                <div class="w-full h-full px-2 py-1 border border-gray-300 overflow-hidden flex items-center"
                     :style="{ fontWeight: blk.bold?'700':'400', fontSize: (blk.fontSize??12)+'px', color:'#000' }"
                     x-text="blk.text || ' '"></div>
              </template>

              {{-- FOOTER --}}
              <template x-if="blk.type==='footer'">
                <div class="w-full h-full px-2 py-1 flex items-center justify-between bg-white/95"
                     :style="{ fontSize: (blk.fontSize??11)+'px', textAlign: blk.align||'left', color:'#000' }">
                  <div class="truncate" x-text="blk.text || footer.text || '© Perusahaan'"></div>
                  <div class="text-xs text-gray-600" x-show="blk.showPage || footer.show_page_number">
                    Halaman <span x-text="p"></span> / <span x-text="preview.pagesCount"></span>
                  </div>
                </div>
              </template>

              {{-- SIGNATURE (honor align, from template/grid/fallback) --}}
              <template x-if="blk.type==='signature'">
                <div class="w-full h-full p-2 bg-white/90 rounded" :style="{ textAlign: (blk.align || 'center'), color:'#000' }">
                  <div class="text-[11px]" x-text="blk.role||'Role'"></div>
                  <div class="mt-1 w-full flex-1 rounded flex items-center justify-center p-1"
                       :style="{
                         height: (blk.boxHeight ?? 96) + 'px',
                         borderStyle: (blk.borderStyle ?? 'solid'),
                         borderWidth: ((blk.borderWidth ?? 2)) + 'px',
                         borderColor: (blk.borderColor ?? '#9CA3AF'),
                         boxSizing: 'border-box',
                         backgroundColor: '#fff'
                       }">
                    <template x-if="blk.src"><img :src="blk.src" class="max-h-full max-w-full object-contain"></template>
                    <template x-if="!blk.src && blk.signatureText"><span class="italic" x-text="blk.signatureText"></span></template>
                    <template x-if="!blk.src && !blk.signatureText"><span class="text-[11px]">Tanda Tangan</span></template>
                  </div>
                  <div class="mt-1">
                    <div class="text-xs font-medium truncate" x-text="blk.name||'Nama'"></div>
                    <div class="text-[11px] truncate" x-text="blk.position||'Jabatan'"></div>
                  </div>
                </div>
              </template>

            </div>
          </template>
        </div>
      </template>
    </div>
  </div>
</div>

@push('scripts')
<script>
function docShow(){
  return {
    // STATE
    header:     { logo:{url:'',position:'left'}, title:{align:'center', text:''} },
    footer:     { text:'', show_page_number:true },
    signatures: { rows:[], columns:4, mode:'grid' },   // dari dokumen
    sections:   [],
    templates:  [],
    selectedTemplateId: '',

    preview: {
      layout: { page:{width:794, height:1123}, margins:{top:40,right:35,bottom:40,left:35}, font:{size:12} },
      zoom: 1.1,
      pagesCount: 1,
      templatePages: [1],
      blocks: []
    },

    init(){
      try { this.templates = JSON.parse(document.querySelector('#doc-templates-json')?.textContent || '[]'); } catch(e){}
      const existing = JSON.parse(document.querySelector('#doc-existing-json')?.textContent || '{}');

      // Prefill dari dokumen (PENTING: rows dari dokumen jangan ditimpa)
      if (existing.layout)        this.preview.layout = Object.assign(this.preview.layout, existing.layout || {});
      if (existing.header)        this.header         = Object.assign(this.header, existing.header || {});
      if (existing.footer)        this.footer         = Object.assign(this.footer, existing.footer || {});
      if (existing.signatures)    this.signatures     = Object.assign({ rows:[], columns:4, mode:'grid' }, existing.signatures || {});
      if (Array.isArray(existing.sections)) this.sections = existing.sections;
      this.selectedTemplateId = existing.template_id ?? '';

      const tpl = this.selectedTemplateId
        ? this.templates.find(x => String(x.id) === String(this.selectedTemplateId))
        : null;

      this.applyTemplate(tpl || { layout:this.preview.layout, header:this.header, footer:this.footer, signature:{}, blocks: [] });
    },

    // UI helpers
    zoomIn(){ this.preview.zoom = Math.min(2, this.preview.zoom + 0.1); },
    zoomOut(){ this.preview.zoom = Math.max(0.6, this.preview.zoom - 0.1); },

    pageStyle(){
      const w = this.preview.layout.page.width * this.preview.zoom;
      const h = this.preview.layout.page.height * this.preview.zoom;
      return { width: w+'px', height: h+'px', transformOrigin: 'top left' };
    },
    marginBoxStyle(){
      const z = this.preview.zoom, L=this.preview.layout;
      return {
        top: (L.margins.top*z)+'px',
        left: (L.margins.left*z)+'px',
        width: ((L.page.width-L.margins.left-L.margins.right)*z)+'px',
        height: ((L.page.height-L.margins.top-L.margins.bottom)*z)+'px',
        outline: '1px dashed rgba(0,0,0,.08)'
      }
    },
    blockStyle(blk){
      const z = this.preview.zoom;
      return {
        top: ((blk.top??0)*z)+'px',
        left: ((blk.left??0)*z)+'px',
        width: ((blk.width??100)*z)+'px',
        height: ((blk.height??32)*z)+'px',
        zIndex: blk.z ?? 1,
        background: blk.type==='tableCell' ? 'rgba(249,250,251,.9)' : 'transparent'
      };
    },

    // === Utilities ===
    computeLeftByAlign(blk) {
      // Hanya pakai align kalau left belum diset
      if (Number.isFinite(+blk.left)) return blk.left;
      const L = this.preview.layout;
      const contentW = L.page.width - L.margins.left - L.margins.right;
      const w = blk.width ?? 100;
      const a = (blk.align || '').toLowerCase();
      if (a === 'center') return Math.round(L.margins.left + (contentW - w) / 2);
      if (a === 'right')  return Math.max(L.margins.left, Math.round(L.page.width - L.margins.right - w));
      return blk.left ?? L.margins.left;
    },
    dedupeHeaderFooter(){
      const seen = new Set();
      this.preview.blocks = (this.preview.blocks||[]).filter(b => {
        if (b.type==='header' || b.type==='footer'){
          const key = `${b.type}@${b.page||1}`;
          if (seen.has(key)) return false;
          seen.add(key);
        }
        return true;
      });
    },

    // ==== SIGNATURE helpers ====
    fillSignatureFromRow(block, row) {
      if (!row) return block;
      block.role     = row.role || row.role_title || block.role || '';
      block.name     = row.name || block.name || '';
      block.position = row.position || row.position_title || block.position || '';
      block.src      = row.image_path || row.signature_url || row.image_url || block.src || '';
      return block;
    },

    // 1) PRIORITAS: blok signature yang tersimpan di dokumen (document.signature_config.blocks)
    buildSignaturesFromDocBlocks(docSig, rows) {
      const docBlocks = Array.isArray(docSig?.blocks) ? docSig.blocks : [];
      if (!docBlocks.length) return [];
      return docBlocks.map((b, i) => {
        const nb = { ...b };
        nb.id = rnd();
        nb.type = 'signature';
        nb.origin = 'template';                 // tetap tandai sebagai template-origin agar ikut repeat rule kalau ada
        nb.page = Number(nb.page) || 1;
        nb.repeatEachPage = Boolean(nb.repeatEachPage ?? true);
        nb.color = nb.color || '#000';
        nb.left = this.computeLeftByAlign(nb);
        this.fillSignatureFromRow(nb, rows[i]);
        return nb;
      });
    },

    // 2) fallback: template punya blok 'signature' fixed
    buildSignaturesFromTplBlocks(tplBlocks, rows) {
      const sigBlks = (Array.isArray(tplBlocks) ? tplBlocks : []).filter(b => (b.type||'').toLowerCase()==='signature');
      if (!sigBlks.length || !rows.length) return [];
      return sigBlks.map((b, i) => {
        const nb = { ...b };
        nb.id = rnd();
        nb.type = 'signature';
        nb.origin = 'template';
        nb.page = Number(nb.page) || 1;
        nb.repeatEachPage = Boolean(nb.repeatEachPage ?? true);
        nb.color = nb.color || '#000';
        nb.left = this.computeLeftByAlign(nb);
        this.fillSignatureFromRow(nb, rows[i]);
        return nb;
      });
    },

    // 3) fallback terakhir: grid dari template.signature (columns + layout.area)
    buildSignaturesFromTplGrid(tplSignatureCfg, rows) {
      const cfg = tplSignatureCfg || {};
      const rowsData = Array.isArray(rows) ? rows : [];
      if (!rowsData.length) return [];

      const area = cfg.layout?.area || {};
      const cols = Number(cfg.columns);
      if (!Number.isFinite(cols) || cols < 1) return [];
      if (![area.top, area.left, area.width, area.height].every(v => Number.isFinite(+v))) return [];

      const page = Number(area.page) || 1;
      const gap    = Number.isFinite(+cfg.layout?.gap)       ? +cfg.layout.gap    : 0;
      const rowGap = Number.isFinite(+cfg.layout?.rowGap)    ? +cfg.layout.rowGap : 0;
      const cellH  = Number.isFinite(+cfg.layout?.cellHeight)? +cfg.layout.cellHeight : Math.floor(area.height);
      const boxH   = Number.isFinite(+cfg.layout?.boxHeight) ? +cfg.layout.boxHeight  : Math.max(0, cellH - 24);

      const borderW = Number.isFinite(+cfg.layout?.borderWidth) ? +cfg.layout.borderWidth : 0;
      const borderSt= cfg.layout?.borderStyle || 'solid';
      const borderCo= cfg.layout?.borderColor || '#9CA3AF';
      const align   = cfg.align || 'center';
      const z       = cfg.z ?? 40;

      const cellW = Math.floor((+area.width - gap * (cols - 1)) / cols);

      const blocks = [];
      rowsData.forEach((person, idx) => {
        const r = Math.floor(idx / cols);
        const c = idx % cols;
        const blk = {
          id: rnd(),
          type: 'signature',
          origin: 'template',
          repeatEachPage: Boolean(cfg.repeatEachPage ?? true),
          page,
          top:  +area.top + r * (cellH + rowGap),
          left: +area.left + c * (cellW + gap),
          width: cellW,
          height: cellH,
          z,
          align,
          boxHeight: boxH,
          borderStyle: borderSt,
          borderWidth: borderW,
          borderColor: borderCo,
          color: '#000',
        };
        this.fillSignatureFromRow(blk, person);
        blk.left = this.computeLeftByAlign(blk);
        blocks.push(blk);
      });
      return blocks;
    },

    // === Apply Template ===
    applyTemplate(tpl){
      // Layout & basic config
      this.preview.layout = Object.assign(
        { page:{width:794,height:1123}, margins:{top:40,right:35,bottom:40,left:35}, font:{size:12} },
        tpl.layout || {}
      );
      this.header = Object.assign({ logo:{url:'',position:'left'}, title:{align:'center', text:''} }, this.header, tpl.header || {});
      this.footer = Object.assign({ text:'', show_page_number:true }, this.footer, tpl.footer || {});

      // Jangan timpa rows dari dokumen; ambil values non-row dari template jika ada
      const docSig = this.signatures || {};
      const tplSig = tpl.signature || {};
      this.signatures = Object.assign({}, tplSig, docSig, { rows: (docSig.rows ?? tplSig.rows ?? []) });

      // Blocks lain dari template
      const rawBlocks = Array.isArray(tpl.blocks) ? tpl.blocks.slice() : [];
      this.preview.blocks = rawBlocks.map((b) => {
        const t = (b.type||'').toLowerCase();
        const out = Object.assign({}, b, {
          type: t==='tablecell' ? 'tableCell' : (t||'text'),
          page: b.page || 1,
          origin: 'template',
          repeatEachPage: (typeof b.repeat!=='undefined') ? !!b.repeat
                        : (typeof b.repeatEachPage!=='undefined') ? !!b.repeatEachPage
                        : (t==='footer' || t==='header' || t==='signature')
        });
        if (b.hasOwnProperty('fontsize') && !b.hasOwnProperty('fontSize')) out.fontSize = b.fontsize;
        if (b.hasOwnProperty('showpage')  && !b.hasOwnProperty('showPage')) out.showPage = !!b.showpage;
        // Hanya hitung align kalau left memang tidak ada
        out.left = this.computeLeftByAlign(out);
        if (!out.color) out.color = '#000';
        return out;
      });

      // HEADER default kalau template nggak punya
      const hasHeader = this.preview.blocks.some(b => b.type==='header' && (b.page||1)===1);
      if (!hasHeader) {
        const L = this.preview.layout;
        const hdr = {
          id: rnd(),
          type: 'header',
          text: this.header?.title?.text || 'Judul Dokumen',
          align: this.header?.title?.align || 'left',
          showMeta:false, metaRight:'',
          top: Math.max(8, (L.margins.top - 28)),
          left: L.margins.left,
          width: L.page.width - (L.margins.left + L.margins.right),
          height:36, z:50, page:1, origin:'template', repeatEachPage:true
        };
        hdr.left = this.computeLeftByAlign(hdr);
        this.preview.blocks.push(hdr);
      }

      // FOOTER default kalau template nggak punya
      const hasFooter = this.preview.blocks.some(b => b.type==='footer' && (b.page||1)===1);
      if (!hasFooter) {
        const L = this.preview.layout;
        const ftr = {
          id: rnd(), type:'footer',
          text: this.footer?.text || '', showPage: this.footer?.show_page_number ?? true,
          align:'left',
          top: L.page.height - (L.margins.bottom + 28),
          left: L.margins.left,
          width: L.page.width - (L.margins.left + L.margins.right),
          height:28, z:50, page:1, origin:'template', repeatEachPage:true
        };
        ftr.left = this.computeLeftByAlign(ftr);
        this.preview.blocks.push(ftr);
      }

      // ===== SIGNATURE: PRIORITAS SESUAI DOKUMEN/TEMPLATE =====
      // buang signature template lama (akan dibangun ulang)
      this.preview.blocks = this.preview.blocks.filter(b => b.type!=='signature');

      const rows = Array.isArray(this.signatures?.rows) ? this.signatures.rows : [];
      let sigBlocks = [];

      // 1) doc.signature_config.blocks (kalau ada)
      sigBlocks = this.buildSignaturesFromDocBlocks(this.signatures, rows);

      // 2) kalau gak ada -> pakai template blocks signature
      if (!sigBlocks.length)
        sigBlocks = this.buildSignaturesFromTplBlocks(rawBlocks, rows);

      // 3) kalau masih gak ada -> pakai grid dari template.signature
      if (!sigBlocks.length)
        sigBlocks = this.buildSignaturesFromTplGrid(tpl.signature, rows);

      if (sigBlocks.length) this.preview.blocks.push(...sigBlocks);

      // Pages minimal 1
      if (!Array.isArray(this.preview.templatePages) || !this.preview.templatePages.length) {
        this.preview.templatePages = [1];
      }
      if ((this.preview.pagesCount|0) < 1) this.preview.pagesCount = 1;

      this.dedupeHeaderFooter();
      this.refreshBlocks();
      this.rebuildRepeatingBlocksAcrossPages();
    },

    // Sections → blocks (read-only)
    refreshBlocks(){
      const L = this.preview.layout;
      const contentTop = L.margins.top, contentLeft = L.margins.left;
      const contentWidth = L.page.width - L.margins.left - L.margins.right;
      const contentBottom = L.page.height - L.margins.bottom;

      const staticBlocks = (this.preview.blocks||[]).filter(b => b.origin!=='section');

      const sectionBlocks = [];
      const makeId = () => rnd();
      const pagesNow = Math.max(1, this.preview.pagesCount|0);
      let maxPage = pagesNow;

      (this.sections||[]).forEach((s, idx) => {
        const basePage = Math.max(1, Number(s.page||1));
        const pagesToPaint = s.repeatEachPage ? Array.from({length: pagesNow}, (_,i)=>i+1) : [basePage];

        pagesToPaint.forEach((pg) => {
          const rct = {
            id: makeId(),
            top:    Number.isFinite(+s.top)    ? +s.top    : (contentTop + 60 + idx*120),
            left:   Number.isFinite(+s.left)   ? +s.left   : contentLeft,
            width:  Number.isFinite(+s.width)  ? +s.width  : contentWidth,
            height: Number.isFinite(+s.height) ? +s.height : 120,
            z: 20, origin:'section', label:s.label || '', page:pg,
            refKey: s.key || s.label, repeatEachPage: !!s.repeatEachPage,
          };

          let rr = {...rct};
          if (!s.repeatEachPage && s.autoFlow && (rr.top + rr.height) > (contentBottom)) {
            rr.page += 1; rr.top = contentTop;
          }
          maxPage = Math.max(maxPage, rr.page);

          if ((s.type || 'text') === 'text') {
            const title    = (s.title || s.label || '').trim();
            const subtitle = (s.subtitle || '').trim();
            const body     = (s.html || '').trim();
            const html = `
              ${title ? `<div style="font-weight:600;color:#000;margin-bottom:2px;">${title}</div>` : ''}
              ${subtitle ? `<div style="font-size:11px;color:#000;margin-bottom:6px;">${subtitle}</div>` : ''}
              ${body || ``}
            `;
            sectionBlocks.push({ ...rr, type:'html', html });
          } else if (s.type === 'table') {
            const rows = Math.max(1, s.rows|0), cols = Math.max(1, s.cols|0);
            const cellW = Math.max(20, Math.floor((rr.width-2) / cols));
            const cellH = Math.max(20, Math.floor((rr.height-2) / rows));
            for (let r=0; r<rows; r++){
              for (let c=0; c<cols; c++){
                const idxCell = r*cols + c;
                sectionBlocks.push({
                  id: makeId(), type:'tableCell', text:(s.cells?.[idxCell] ?? ''),
                  top: rr.top + r*cellH, left: rr.left + c*cellW,
                  width: cellW, height: cellH, z:20, origin:'section',
                  page: rr.page, refKey: rr.refKey, repeatEachPage: !!rr.repeatEachPage,
                });
              }
            }
          }
        });
      });

      this.preview.blocks = [...staticBlocks, ...sectionBlocks];
      this.preview.pagesCount = Math.max(maxPage, 1);
    },

    // Clone repeating template blocks ke halaman lain
    getRepeatingTemplateBlocks(){
      return (this.preview.blocks||[]).filter(b =>
        b.origin==='template' && ((b.repeatEachPage || ['header','footer','signature'].includes((b.type||'').toLowerCase()))) && ((b.page||1)===1)
      );
    },
    rebuildRepeatingBlocksAcrossPages(){
      const pages = this.preview.pagesCount|0;
      if (pages <= 1) return;
      const base = this.getRepeatingTemplateBlocks();
      this.preview.blocks = this.preview.blocks.filter(b => !(b.origin==='template' && (b.page||1)>1));
      for (let pg = 2; pg <= pages; pg++){
        const clones = base.map(b => { const id = rnd(); const cp = JSON.parse(JSON.stringify(b)); cp.id = id; cp.page = pg; return cp; });
        this.preview.blocks.push(...clones);
      }
      this.dedupeHeaderFooter();
    },
  }
}
function rnd(){ return Math.random().toString(36).slice(2,10) + Math.random().toString(36).slice(2,6); }
</script>
@endpush

@endsection
