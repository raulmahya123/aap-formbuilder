{{-- resources/views/admin/documents/show.blade.php --}}
@extends('layouts.app')

@section('title', ($document->doc_no ? $document->doc_no.' — ' : '').($document->title ?? 'Preview Dokumen'))

@push('styles')
<style>[x-cloak]{display:none!important}</style>
@endpush

@section('content')
@php
  // Siapkan payload existing document agar aman dari null/undefined
  $existingPayload = [
    'template_id' => $document->template_id ?? null,
    'layout'      => $document->layout_config ?: [
      'page'    => ['width'=>794,'height'=>1123],
      'margins' => ['top'=>40,'right'=>35,'bottom'=>40,'left'=>35],
      'font'    => ['size'=>12],
    ],
    'header'      => $document->header_config ?: ['logo'=>['url'=>'','position'=>'left'], 'title'=>['align'=>'center','text'=>$document->title ?? '']],
    'footer'      => $document->footer_config ?: ['text'=>'','show_page_number'=>true],
    'signatures'  => $document->signature_config ?: ['rows'=>[], 'columns'=>4, 'mode'=>'grid'],
    'sections'    => is_array($document->sections) ? $document->sections : [],
    // fallback sederhana utk judul
    'fallback'    => ['title' => $document->title ?? 'Judul Dokumen']
  ];
@endphp

{{-- Inject existing doc JSON untuk Alpine --}}
<script type="application/json" id="doc-existing-json">
{!! json_encode($existingPayload, JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES) !!}
</script>

<div x-data="docPreview()" x-init="init()" class="max-w-7xl mx-auto p-6 space-y-6">
  {{-- HEADER --}}
  <div class="flex items-center justify-between">
    <div>
      <h1 class="text-xl font-semibold text-[#1D1C1A]">Preview Dokumen</h1>
      <p class="text-sm text-gray-500">
        {{ $document->doc_no ? $document->doc_no.' — ' : '' }}{{ $document->title }}
      </p>
    </div>
    <div class="flex items-center gap-3">
      <a href="{{ route('admin.documents.index') }}" class="px-4 py-2 rounded-xl border text-[#1D1C1A]">← Kembali</a>
      @can('update', $document)
        <a href="{{ route('admin.documents.edit',$document) }}" class="px-4 py-2 rounded-xl bg-[#7A2C2F] text-white hover:opacity-90">Edit</a>
      @endcan
      <button type="button" onclick="window.print()" class="px-4 py-2 rounded-xl border">Print</button>
    </div>
  </div>

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

    <div class="mt-4 rounded-xl border bg-gray-50 p-6 overflow-auto max-h-[80vh] select-none">
      <template x-for="p in preview.pagesCount" :key="'p'+p">
        <div class="mx-auto mb-8 shadow-sm bg-white relative" :style="pageStyle()">
          {{-- garis margin --}}
          <div class="absolute inset-0 pointer-events-none">
            <div class="absolute" :style="marginBoxStyle()"></div>
          </div>

          {{-- blok per halaman --}}
          <template x-for="blk in preview.blocks.filter(b => (b.page||1)===p)" :key="blk.id">
            <div class="absolute ring-1 ring-gray-100"
                 :style="blockStyle(blk)">

              {{-- HEADER --}}
              <template x-if="blk.type==='header'">
                <div class="w-full h-full px-3 py-2 flex items-center justify-between bg-white/95"
                     :style="{ fontSize: (blk.fontSize??12)+'px', textAlign: blk.align||'left' }">
                  <div class="flex items-center gap-2 overflow-hidden">
                    <template x-if="header?.logo?.url">
                      <img :src="header.logo.url" alt="Logo" class="h-6 w-auto object-contain">
                    </template>
                    <div class="truncate font-medium"
                         x-text="(header?.title?.text || fallbackTitle || blk.text || 'Judul Dokumen')"></div>
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

              {{-- HTML --}}
              <template x-if="blk.type==='html'">
                <div class="w-full h-full p-3 overflow-auto text-[13px] leading-relaxed prose prose-sm max-w-none"
                     x-html="blk.html"></div>
              </template>

              {{-- IMAGE --}}
              <template x-if="blk.type==='image'">
                <div class="w-full h-full flex items-center justify-center bg-white">
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

              {{-- SIGNATURE --}}
              <template x-if="blk.type==='signature'">
                <div class="w-full h-full p-2 bg-white/90 rounded">
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

  {{-- Metadata ringkas --}}
  <div class="bg-white border rounded-2xl p-5">
    <dl class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4 text-sm">
      <div>
        <dt class="text-gray-500">Doc.No</dt>
        <dd class="font-medium">{{ $document->doc_no ?: '—' }}</dd>
      </div>
      <div>
        <dt class="text-gray-500">Revision</dt>
        <dd class="font-medium">{{ $document->revision_no }}</dd>
      </div>
      <div>
        <dt class="text-gray-500">Effective Date</dt>
        <dd class="font-medium">{{ optional($document->effective_date)->format('d M Y') ?: '—' }}</dd>
      </div>
      <div>
        <dt class="text-gray-500">Department</dt>
        <dd class="font-medium">{{ optional($document->department)->name ?: '—' }}</dd>
      </div>
      <div>
        <dt class="text-gray-500">Controlled Status</dt>
        <dd class="font-medium">{{ $document->controlled_status ?? '—' }}</dd>
      </div>
      <div>
        <dt class="text-gray-500">Class</dt>
        <dd class="font-medium">{{ $document->class ?? '—' }}</dd>
      </div>
    </dl>
  </div>
</div>

@push('scripts')
<script>
function docPreview(){
  return {
    // STATE (read-only)
    header:     { logo:{url:'',position:'left'}, title:{align:'center', text:''} },
    footer:     { text:'', show_page_number:true },
    signatures: { rows:[], columns:4, mode:'grid' },
    sections:   [],
    fallbackTitle: '',

    preview: {
      layout: { page:{width:794, height:1123}, margins:{top:40,right:35,bottom:40,left:35}, font:{size:12} },
      zoom: 1.1,
      pagesCount: 1,
      blocks: []
    },

    init(){
      // Ambil existing JSON
      const existing = JSON.parse(document.querySelector('#doc-existing-json')?.textContent || '{}');

      // Prefill aman
      this.preview.layout = Object.assign(this.preview.layout, existing.layout || {});
      this.header         = Object.assign(this.header, existing.header || {});
      this.footer         = Object.assign(this.footer, existing.footer || {});
      this.signatures     = Object.assign(this.signatures, existing.signatures || {});
      this.sections       = Array.isArray(existing.sections) ? existing.sections : [];
      this.fallbackTitle  = existing?.fallback?.title || '';

      // Seed blok default + apply config
      this.applyBaseTemplate();
      this.refreshBlocks();
      this.rebuildRepeatingBlocksAcrossPages();
    },

    // Zoom
    zoomIn(){ this.preview.zoom = Math.min(2, this.preview.zoom + 0.1); },
    zoomOut(){ this.preview.zoom = Math.max(0.6, this.preview.zoom - 0.1); },

    // Styles
    pageStyle(){
      const w = this.preview.layout.page.width * this.preview.zoom;
      const h = this.preview.layout.page.height * this.preview.zoom;
      return { width: w+'px', height: h+'px', transformOrigin: 'top left' };
    },
    marginBoxStyle(){
      const z = this.preview.zoom, L=this.preview.layout;
      return {
        top:    (L.margins.top*z)+'px',
        left:   (L.margins.left*z)+'px',
        width:  ((L.page.width  - L.margins.left - L.margins.right)*z)+'px',
        height: ((L.page.height - L.margins.top  - L.margins.bottom)*z)+'px',
        outline: '1px dashed rgba(0,0,0,.08)'
      }
    },
    blockStyle(blk){
      const z = this.preview.zoom;
      return {
        top:    ((blk.top    ?? 0)*z)+'px',
        left:   ((blk.left   ?? 0)*z)+'px',
        width:  ((blk.width  ?? 100)*z)+'px',
        height: ((blk.height ?? 32)*z)+'px',
        zIndex: blk.z ?? 1,
        background: blk.type==='tableCell' ? 'rgba(249,250,251,.9)' : 'transparent'
      };
    },

    // Template dasar untuk header/footer/signature jika belum ada
    applyBaseTemplate(){
      const L = this.preview.layout;
      const blocks = [];

      // HEADER
      blocks.push({
        id: cryptoRandom(),
        type: 'header',
        text: this.header?.title?.text || this.fallbackTitle || 'Judul Dokumen',
        align: this.header?.title?.align || 'left',
        showMeta: false,
        metaRight: '',
        top: Math.max(8, (L.margins.top - 28)),
        left: L.margins.left,
        width: L.page.width - (L.margins.left + L.margins.right),
        height: 36,
        z: 50, page: 1, origin: 'template', repeatEachPage: true
      });

      // FOOTER
      blocks.push({
        id: cryptoRandom(),
        type: 'footer',
        text: this.footer?.text || '',
        showPage: this.footer?.show_page_number ?? true,
        align: 'left',
        top: L.page.height - (L.margins.bottom + 28),
        left: L.margins.left,
        width: L.page.width - (L.margins.left + L.margins.right),
        height: 28,
        z: 50, page: 1, origin: 'template', repeatEachPage: true
      });

      // SIGNATURE (jika ada rows)
      if (Array.isArray(this.signatures?.rows) && this.signatures.rows.length){
        const h = 90;
        const y = Math.max(L.margins.top + 140, (L.page.height - L.margins.bottom - 28 - h - 8));
        blocks.push({
          id: cryptoRandom(),
          type: 'signature',
          role: this.signatures.rows?.[0]?.role || 'Disetujui oleh',
          name: this.signatures.rows?.[0]?.name || '',
          position: this.signatures.rows?.[0]?.position_title || '',
          top: y,
          left: L.margins.left,
          width: L.page.width - (L.margins.left + L.margins.right),
          height: h,
          z: 40, page: 1, origin: 'template', repeatEachPage: true
        });
      }

      // Simpan blok template dasar (hal 1)
      this.preview.blocks = blocks;
      this.preview.pagesCount = 1;
    },

    // Build blok dari sections (read-only)
    refreshBlocks(){
      const L = this.preview.layout;
      const contentTop    = L.margins.top;
      const contentLeft   = L.margins.left;
      const contentWidth  = L.page.width  - L.margins.left - L.margins.right;
      const contentBottom = L.page.height - L.margins.bottom;

      const staticBlocks = (this.preview.blocks||[]).filter(b => b.origin!=='section');

      const sectionBlocks = [];
      const makeId = () => cryptoRandom();

      const pagesNow = Math.max(1, this.preview.pagesCount|0);
      let maxPage = pagesNow;

      (this.sections||[]).forEach((s, idx) => {
        const basePage = Math.max(1, Number(s.page||1));
        const pagesToPaint = s.repeatEachPage ? Array.from({length: pagesNow}, (_,i)=>i+1) : [basePage];

        pagesToPaint.forEach((pg) => {
          const base = {
            id: makeId(),
            top:    Number.isFinite(+s.top)    ? +s.top    : (contentTop + 60 + idx*120),
            left:   Number.isFinite(+s.left)   ? +s.left   : contentLeft,
            width:  Number.isFinite(+s.width)  ? +s.width  : contentWidth,
            height: Number.isFinite(+s.height) ? +s.height : 120,
            z: 20,
            origin: 'section',
            label: s.label || '',
            page:  pg,
            refKey: s.key || s.label,
            repeatEachPage: !!s.repeatEachPage,
          };

          // Auto flow sederhana
          let rect = {...base};
          if (!s.repeatEachPage && s.autoFlow && (rect.top + rect.height) > (contentBottom)) {
            rect.page += 1;
            rect.top = contentTop;
          }
          maxPage = Math.max(maxPage, rect.page);

          if ((s.type || 'text') === 'text') {
            sectionBlocks.push({ ...rect, type:'html', html: s.html || `<p style="color:#666">(${s.label||'Section'})</p>` });
          } else if (s.type === 'table') {
            const rows = Math.max(1, s.rows|0), cols = Math.max(1, s.cols|0);
            const cellW = Math.max(20, Math.floor((rect.width-2) / cols));
            const cellH = Math.max(20, Math.floor((rect.height-2) / rows));
            for (let r=0; r<rows; r++){
              for (let c=0; c<cols; c++){
                const idxCell = r*cols + c;
                sectionBlocks.push({
                  id: makeId(),
                  type: 'tableCell',
                  text: (s.cells?.[idxCell] ?? ''),
                  top:  rect.top + r*cellH,
                  left: rect.left + c*cellW,
                  width: cellW, height: cellH,
                  z: 20, origin: 'section',
                  page: rect.page,
                  refKey: rect.refKey,
                  repeatEachPage: !!s.repeatEachPage,
                });
              }
            }
          }
        });
      });

      this.preview.blocks = [...staticBlocks, ...sectionBlocks];
      this.preview.pagesCount = Math.max(maxPage, 1);
    },

    // Repeat header/footer/signature ke semua halaman
    repeatable(b){
      const t = (b.type||'').toLowerCase();
      const isHFS = (t==='header'||t==='footer'||t==='signature');
      return !!(b.repeatEachPage || b.repeat || isHFS);
    },
    getRepeatingTemplateBlocks(){
      return (this.preview.blocks||[]).filter(b =>
        b.origin==='template' && this.repeatable(b) && ((b.page||1)===1)
      );
    },
    rebuildRepeatingBlocksAcrossPages(){
      const pages = this.preview.pagesCount|0;
      if (pages <= 1) return;
      const base = this.getRepeatingTemplateBlocks();
      // buang blok template di halaman >1
      this.preview.blocks = (this.preview.blocks||[]).filter(b => !(b.origin==='template' && (b.page||1)>1));
      for (let pg = 2; pg <= pages; pg++){
        const clones = base.map(b => {
          const nb = JSON.parse(JSON.stringify(b));
          nb.id = cryptoRandom();
          nb.page = pg;
          return nb;
        });
        this.preview.blocks.push(...clones);
      }
    },
  }
}

// util id random
function cryptoRandom(){
  return (Math.random().toString(36).slice(2,10) + Math.random().toString(36).slice(2,10)).slice(0,12);
}
</script>
@endpush
@endsection
