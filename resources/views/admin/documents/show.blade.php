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
    <div class="flex items-center gap-3">
      <a href="{{ route('admin.documents.index') }}" class="px-4 py-2 rounded-xl border text-[#1D1C1A]">← Kembali</a>
      <button type="button" onclick="window.print()" class="px-4 py-2 rounded-xl bg-[#7A2C2F] text-white hover:opacity-90">Cetak</button>
    </div>
  </div>

  {{-- Inject: templates (opsional) & data dokumen --}}
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

  {{-- ====== PREVIEW (read-only) ====== --}}
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
          {{-- garis margin --}}
          <div class="absolute inset-0 pointer-events-none">
            <div class="absolute" :style="marginBoxStyle()"></div>
          </div>

          {{-- blok per halaman --}}
          <template x-for="blk in preview.blocks.filter(b => (b.page||1)===p)" :key="blk.id">
            <div class="absolute ring-1 ring-gray-100" :style="blockStyle(blk)">

              {{-- HEADER --}}
              <template x-if="blk.type==='header'">
                <div class="w-full h-full px-3 py-2 flex items-center justify-between bg-white/95"
                     :style="{ fontSize: (blk.fontSize??12)+'px', textAlign: blk.align||'left' }">
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
                     :style="{ textAlign: blk.align||'left', fontWeight: blk.bold?'700':'400', fontSize: (blk.fontSize??preview.layout.font.size)+'pt' }"
                     x-text="blk.text||''"></div>
              </template>

              {{-- HTML (section) --}}
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
                     :style="{ fontWeight: blk.bold?'700':'400', fontSize: (blk.fontSize??12)+'px' }"
                     x-text="blk.text || ' '"></div>
              </template>

              {{-- FOOTER --}}
              <template x-if="blk.type==='footer'">
                <div class="w-full h-full px-2 py-1 flex items-center justify-between bg-white/95"
                     :style="{ fontSize: (blk.fontSize??11)+'px', textAlign: blk.align||'left' }">
                  <div class="truncate" x-text="blk.text || footer.text || '© Perusahaan'"></div>
                  <div class="text-xs text-gray-600" x-show="blk.showPage || footer.show_page_number">
                    Halaman <span x-text="p"></span> / <span x-text="preview.pagesCount"></span>
                  </div>
                </div>
              </template>

              {{-- SIGNATURE (honor align center/left/right) --}}
              <template x-if="blk.type==='signature'">
                <div class="w-full h-full p-2 bg-white/90 rounded"
                     :style="{ textAlign: (blk.align || 'center') }">
                  <div class="text-[11px] text-gray-600" x-text="blk.role||'Role'"></div>
                  <div class="mt-1 w-full flex-1 border border-dashed rounded flex items-center justify-center" style="height:38px;">
                    <template x-if="blk.src"><img :src="blk.src" class="max-h-full object-contain"></template>
                    <template x-if="!blk.src && blk.signatureText"><span class="italic" x-text="blk.signatureText"></span></template>
                    <template x-if="!blk.src && !blk.signatureText"><span class="text-[10px] text-gray-400">TTD</span></template>
                  </div>
                  <div class="mt-1">
                    <div class="text-xs font-medium truncate" x-text="blk.name||'Nama'"></div>
                    <div class="text-[11px] text-gray-600 truncate" x-text="blk.position||'Jabatan'"></div>
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
    signatures: { rows:[], columns:4, mode:'grid' },
    sections:   [],
    templates:  [],
    selectedTemplateId: '',

    preview: {
      layout: { page:{width:794, height:1123}, margins:{top:40,right:35,bottom:40,left:35}, font:{size:12} },
      zoom: 1.1,
      pagesCount: 1,
      blocks: []
    },

    init(){
      // JSON
      try { this.templates = JSON.parse(document.querySelector('#doc-templates-json')?.textContent || '[]'); } catch(e){}
      const existing = JSON.parse(document.querySelector('#doc-existing-json')?.textContent || '{}');

      // Prefill
      if (existing.layout)        this.preview.layout = Object.assign(this.preview.layout, existing.layout || {});
      if (existing.header)        this.header         = Object.assign(this.header, existing.header || {});
      if (existing.footer)        this.footer         = Object.assign(this.footer, existing.footer || {});
      if (existing.signatures)    this.signatures     = Object.assign({ rows:[], columns:4, mode:'grid' }, existing.signatures || {});
      if (Array.isArray(existing.sections)) this.sections = existing.sections;
      this.selectedTemplateId = existing.template_id ?? '';

      // Terapkan template yang sama seperti di create/edit
      if (this.selectedTemplateId){
        const tpl = this.templates.find(x => String(x.id) === String(this.selectedTemplateId));
        tpl ? this.applyTemplate(tpl) : this.applyTemplate({ layout:this.preview.layout, header:this.header, footer:this.footer, signature:{ rows:this.signatures.rows||[] }, blocks: [] });
      } else {
        this.applyTemplate({ layout:this.preview.layout, header:this.header, footer:this.footer, signature:{ rows:this.signatures.rows||[] }, blocks: [] });
      }
    },

    // ===== UI helpers =====
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

    // ====== BAGIAN YANG DISAMAKAN DENGAN CREATE/EDIT ======
    applyTemplate(tpl){
      // Layout + config
      this.preview.layout = Object.assign(
        { page:{width:794,height:1123}, margins:{top:40,right:35,bottom:40,left:35}, font:{size:12} },
        tpl.layout || {}
      );
      this.header     = Object.assign({ logo:{url:'',position:'left'}, title:{align:'center', text:''} }, tpl.header || this.header);
      this.footer     = Object.assign({ text:'', show_page_number:true }, tpl.footer || this.footer);
      this.signatures = Object.assign({ rows:[], columns:4, mode:'grid' }, tpl.signature ? { rows:(tpl.signature.rows||[]) } : this.signatures);

      // Blocks dari template (jika ada)
      let blocks = Array.isArray(tpl.blocks) ? tpl.blocks.slice() : [];
      this.preview.blocks = blocks.map((b) => {
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
        return out;
      });

      // HEADER default (hal 1)
      const hasHeader = this.preview.blocks.some(b => b.type==='header' && b.page===1);
      if (!hasHeader) {
        const L = this.preview.layout;
        this.preview.blocks.push({
          id: rnd(), type:'header',
          text: this.header?.title?.text || 'Judul Dokumen',
          align: this.header?.title?.align || 'left',
          showMeta:false, metaRight:'',
          top: Math.max(8, (L.margins.top - 28)),
          left: L.margins.left,
          width: L.page.width - (L.margins.left + L.margins.right),
          height:36, z:50, page:1, origin:'template', repeatEachPage:true
        });
      }

      // FOOTER default (hal 1)
      const hasFooter = this.preview.blocks.some(b => b.type==='footer' && b.page===1);
      if (!hasFooter) {
        const L = this.preview.layout;
        this.preview.blocks.push({
          id: rnd(), type:'footer',
          text: this.footer?.text || '', showPage: this.footer?.show_page_number ?? true,
          align:'left',
          top: L.page.height - (L.margins.bottom + 28),
          left: L.margins.left,
          width: L.page.width - (L.margins.left + L.margins.right),
          height:28, z:50, page:1, origin:'template', repeatEachPage:true
        });
      }

      // SIGNATURE default (hal 1) bila ada rows
      const hasSig = this.preview.blocks.some(b => b.type==='signature' && b.page===1);

      if (!hasSig && Array.isArray(this.signatures?.rows) && this.signatures.rows.length) {
        const L = this.preview.layout;
        const h = 90, y = Math.max(L.margins.top + 140, (L.page.height - L.margins.bottom - 28 - h - 8));
        this.preview.blocks.push({
          id: rnd(), type:'signature',
          role:'Disetujui oleh',
          name: this.signatures.rows?.[0]?.name || '',
          position: this.signatures.rows?.[0]?.position_title || '',
          align: 'center',
          top: y, left: L.margins.left,
          width: L.page.width - (L.margins.left + L.margins.right),
          height: h, z:40, page:1, origin:'template', repeatEachPage:true
        });
      }

      this.refreshBlocks();
      this.rebuildRepeatingBlocksAcrossPages();
    },

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
          const rect = {
            id: makeId(),
            top:    Number.isFinite(+s.top)    ? +s.top    : (contentTop + 60 + idx*120),
            left:   Number.isFinite(+s.left)   ? +s.left   : contentLeft,
            width:  Number.isFinite(+s.width)  ? +s.width  : contentWidth,
            height: Number.isFinite(+s.height) ? +s.height : 120,
            z: 20, origin:'section', label:s.label || '', page:pg,
            refKey: s.key || s.label, repeatEachPage: !!s.repeatEachPage,
          };

          let r = {...rect};
          if (!s.repeatEachPage && s.autoFlow && (r.top + r.height) > (contentBottom)) {
            r.page += 1; r.top = contentTop;
          }
          maxPage = Math.max(maxPage, r.page);

          if ((s.type || 'text') === 'text') {
            const title    = s.label ? `<div style="font-weight:600;margin-bottom:2px">${s.label}</div>` : '';
            const subtitle = s.subtitle ? `<div style="color:#6b7280;font-size:12px;margin-bottom:6px">${s.subtitle}</div>` : '';
            const body     = s.html ? s.html : `<p style="color:#666">(${s.label||'Section'})</p>`;
            sectionBlocks.push({ ...r, type:'html', html: `${title}${subtitle}${body}` });
          } else if (s.type === 'table') {
            const rows = Math.max(1, s.rows|0), cols = Math.max(1, s.cols|0);
            const cellW = Math.max(20, Math.floor((r.width-2) / cols));
            const cellH = Math.max(20, Math.floor((r.height-2) / rows));
            for (let rr=0; rr<rows; rr++){
              for (let cc=0; cc<cols; cc++){
                const idxCell = rr*cols + cc;
                sectionBlocks.push({
                  id: makeId(), type:'tableCell', text:(s.cells?.[idxCell] ?? ''),
                  top: r.top + rr*cellH, left: r.left + cc*cellW,
                  width: cellW, height: cellH, z:20, origin:'section',
                  page: r.page, refKey: r.refKey, repeatEachPage: !!r.repeatEachPage,
                });
              }
            }
          }
        });
      });

      this.preview.blocks = [...staticBlocks, ...sectionBlocks];
      this.preview.pagesCount = Math.max(maxPage, 1);
    },

    getRepeatingTemplateBlocks(){
      return (this.preview.blocks||[]).filter(b =>
        b.origin==='template' && (b.repeatEachPage || ['header','footer','signature'].includes((b.type||'').toLowerCase())) && ((b.page||1)===1)
      );
    },
    rebuildRepeatingBlocksAcrossPages(){
      const pages = this.preview.pagesCount|0;
      if (pages <= 1) return;
      const base = this.getRepeatingTemplateBlocks();
      this.preview.blocks = (this.preview.blocks||[]).filter(b => !(b.origin==='template' && (b.page||1)>1));
      for (let pg = 2; pg <= pages; pg++){
        const clones = base.map(b => { const nb = JSON.parse(JSON.stringify(b)); nb.id = rnd(); nb.page = pg; return nb; });
        this.preview.blocks.push(...clones);
      }
    },
  }
}
function rnd(){ return Math.random().toString(36).slice(2,10) + Math.random().toString(36).slice(2,6); }
</script>
@endpush
@endsection
