@extends('layouts.app')

@section('title','Edit Document')

@section('content')
<div x-data="docBuilder()" x-init="init()" class="max-w-7xl mx-auto p-6 space-y-6">
  {{-- HEADER --}}
  <div class="flex items-center justify-between">
    <h1 class="text-xl font-semibold text-[#1D1C1A]">Edit Dokumen</h1>
    <div class="flex items-center gap-3">
      <a href="{{ route('admin.documents.index') }}" class="px-4 py-2 rounded-xl border text-[#1D1C1A]">← Kembali</a>
      <button form="docForm" class="px-4 py-2 rounded-xl bg-[#7A2C2F] text-white hover:opacity-90">Update</button>
    </div>
  </div>

  {{-- Inject templates & existing doc configs as JSON for Alpine --}}
  <script type="application/json" id="doc-templates-json">
    {!! json_encode($templatesPayload, JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES) !!}
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

  <form id="docForm" method="POST" action="{{ route('admin.documents.update',$document) }}" class="bg-white/60 backdrop-blur border rounded-2xl p-5">
    @csrf
    @method('PUT')

    {{-- HIDDEN: JSON yang disimpan ke documents --}}
    <input type="hidden" name="template_id"      :value="selectedTemplateId">
    <input type="hidden" name="layout_config"    :value="JSON.stringify(preview.layout)">
    <input type="hidden" name="header_config"    :value="JSON.stringify(header)">
    <input type="hidden" name="footer_config"    :value="JSON.stringify(footer)">
    <input type="hidden" name="signature_config" :value="JSON.stringify(signatures)">
    <input type="hidden" name="sections"         :value="JSON.stringify(sections)">

    <div class="grid lg:grid-cols-[2fr_1fr] gap-6">
      {{-- ====== KIRI: PREVIEW ====== --}}
      <div class="bg-white border rounded-2xl p-5">
        <div class="flex items-center justify-between">
          <h2 class="font-semibold text-[#1D1C1A]">Preview</h2>
          <div class="flex items-center gap-2 text-sm">
            <button type="button" class="px-2 py-1 border rounded" @click="addPage()">+ Halaman</button>
            <button type="button" class="px-2 py-1 border rounded" @click="zoomOut()">–</button>
            <span x-text="Math.round(preview.zoom*100)+'%'"></span>
            <button type="button" class="px-2 py-1 border rounded" @click="zoomIn()">+</button>
          </div>
        </div>

        <div class="mt-4 rounded-xl border bg-gray-50 p-6 overflow-auto max-h-[75vh] select-none">
          <template x-for="p in preview.pagesCount" :key="'p'+p">
            <div class="mx-auto mb-8 shadow-sm bg-white relative" :style="pageStyle()">
              {{-- garis margin --}}
              <div class="absolute inset-0 pointer-events-none">
                <div class="absolute" :style="marginBoxStyle()"></div>
              </div>

              {{-- blok (per halaman) --}}
              <template x-for="blk in preview.blocks.filter(b => (b.page||1)===p)" :key="blk.id">
                <div class="absolute ring-1 ring-gray-200 group"
                     :style="blockStyle(blk)"
                     @mousedown="blk.origin==='section' && onBlockMouseDown($event, blk)">

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

                  {{-- SIGNATURE (center-aware) --}}
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

                  {{-- HANDLE drag/resize utk blok dari section --}}
                  <template x-if="blk.origin==='section'">
                    <div class="absolute -bottom-1 -right-1 w-3 h-3 border border-gray-400 bg-white rounded-sm cursor-se-resize opacity-90"
                         @mousedown.stop="onResizeMouseDown($event, blk, 'br')"></div>
                  </template>
                  <template x-if="blk.origin==='section'">
                    <div class="absolute -top-1 -left-1 w-3 h-3 border border-gray-400 bg-white rounded-sm cursor-nw-resize opacity-90"
                         @mousedown.stop="onResizeMouseDown($event, blk, 'tl')"></div>
                  </template>
                </div>
              </template>
            </div>
          </template>
        </div>
      </div>

      {{-- ====== KANAN: FORM META + SECTIONS ====== --}}
      <div class="bg-white border rounded-2xl p-5 space-y-5">
        <div class="grid gap-3">
          {{-- Template --}}
          <div>
            <label class="text-sm font-medium">Document Template</label>
            <select x-model.number="selectedTemplateId" name="template_id_live" class="mt-1 w-full border rounded-lg px-3 py-2">
              <option value="">— Tanpa Template —</option>
              @foreach($templates as $t)
                <option value="{{ $t->id }}">#{{ $t->id }} — {{ $t->name }}</option>
              @endforeach
            </select>
            <p class="mt-1 text-xs text-gray-600" x-show="selectedTemplateId">
              Template: <span class="font-medium" x-text="templateName"></span>
            </p>
          </div>

          {{-- Judul --}}
          <div>
            <label class="text-sm font-medium">Judul</label>
            <input name="title" value="{{ old('title',$document->title) }}" class="mt-1 w-full border rounded-lg px-3 py-2" required>
          </div>

          {{-- Dept / Type / Project --}}
          <div class="grid grid-cols-1 md:grid-cols-3 gap-3">
            <div>
              <label class="text-sm font-medium">Dept Code</label>
              <input name="dept_code" value="{{ old('dept_code',$document->dept_code) }}" class="mt-1 w-full border rounded-lg px-3 py-2" placeholder="PLT/SHE/ENG">
            </div>
            <div>
              <label class="text-sm font-medium">Doc Type</label>
              <input name="doc_type" value="{{ old('doc_type',$document->doc_type) }}" class="mt-1 w-full border rounded-lg px-3 py-2" placeholder="SOP/IK/ST...">
            </div>
            <div>
              <label class="text-sm font-medium">Project Code</label>
              <input name="project_code" value="{{ old('project_code',$document->project_code) }}" class="mt-1 w-full border rounded-lg px-3 py-2" placeholder="CT-001 / PROJ-ABC">
            </div>
          </div>

          {{-- Tanggal & Class --}}
          <div class="grid grid-cols-2 gap-3">
            <div>
              <label class="text-sm font-medium">Effective Date</label>
              <input type="date" name="effective_date" value="{{ old('effective_date',optional($document->effective_date)->format('Y-m-d')) }}" class="mt-1 w-full border rounded-lg px-3 py-2">
            </div>
            <div>
              <label class="text-sm font-medium">Class</label>
              @php $cls = old('class',$document->class); @endphp
              <select name="class" class="mt-1 w-full border rounded-lg px-3 py-2">
                <option value="">-</option>
                <option @selected($cls==='I')>I</option>
                <option @selected($cls==='II')>II</option>
                <option @selected($cls==='III')>III</option>
                <option @selected($cls==='IV')>IV</option>
              </select>
            </div>
          </div>

          {{-- Status & Owner --}}
          <div class="grid grid-cols-2 gap-3">
            <div>
              <label class="text-sm font-medium">Controlled Status</label>
              @php $st = old('controlled_status',$document->controlled_status); @endphp
              <select name="controlled_status" class="mt-1 w-full border rounded-lg px-3 py-2">
                <option value="controlled"   @selected($st==='controlled')>Controlled</option>
                <option value="uncontrolled" @selected($st==='uncontrolled')>Uncontrolled</option>
                <option value="obsolete"     @selected($st==='obsolete')>Obsolete</option>
              </select>
            </div>
            <div>
              <label class="text-sm font-medium">Department Owner</label>
              <select name="department_id" class="mt-1 w-full border rounded-lg px-3 py-2">
                <option value="">-</option>
                @foreach($departments as $dp)
                  <option value="{{ $dp->id }}" @selected(old('department_id',$document->department_id)==$dp->id)>{{ $dp->name }}</option>
                @endforeach
              </select>
            </div>
          </div>

          {{-- Nomor & Revisi (UI aja) --}}
          <div class="grid grid-cols-2 gap-3">
            <div>
              <label class="text-sm font-medium">Doc.No</label>
              <input name="doc_no" value="{{ old('doc_no',$document->doc_no) }}" class="mt-1 w-full border rounded-lg px-3 py-2">
            </div>
            <div>
              <label class="text-sm font-medium">Revision</label>
              <input value="{{ $document->revision_no }}" class="mt-1 w-full border rounded-lg px-3 py-2 bg-gray-50" readonly>
              <p class="text-xs text-gray-500 mt-1">Naik otomatis saat disimpan.</p>
            </div>
          </div>
        </div>

        {{-- Sections --}}
        <div class="pt-2">
          <div class="flex items-center justify-between">
            <h2 class="font-semibold text-[#1D1C1A]">Sections</h2>
            <button type="button" @click="addSection()" class="text-[#7A2C2F]">+ Tambah</button>
          </div>

          <template x-for="(s,i) in sections" :key="s.key">
            <div class="mt-3 p-3 border rounded-lg space-y-2">
              <div class="flex flex-wrap gap-2 items-center">
                <input x-model="s.label" class="border rounded px-2 py-1 w-56" placeholder="Nama Section">
                <input x-model="s.subtitle" class="border rounded px-2 py-1 w-56" placeholder="Subjudul (opsional)">
                <select x-model="s.type" class="border rounded px-2 py-1">
                  <option value="text">Teks/HTML</option>
                  <option value="table">Tabel</option>
                </select>

                {{-- Posisi & ukuran --}}
                <input x-model.number="s.top"    type="number" class="border rounded px-2 py-1 w-24"  placeholder="top">
                <input x-model.number="s.left"   type="number" class="border rounded px-2 py-1 w-24"  placeholder="left">
                <input x-model.number="s.width"  type="number" class="border rounded px-2 py-1 w-24"  placeholder="width">
                <input x-model.number="s.height" type="number" class="border rounded px-2 py-1 w-24"  placeholder="height">

                {{-- Halaman & Auto flow --}}
                <input x-model.number="s.page" type="number" min="1" class="border rounded px-2 py-1 w-20" title="Halaman" placeholder="Pg">
                <label class="flex items-center gap-1 text-sm">
                  <input type="checkbox" x-model="s.autoFlow">
                  Auto flow
                </label>

                {{-- Repeat --}}
                <label class="flex items-center gap-1 text-sm">
                  <input type="checkbox" x-model="s.repeatEachPage">
                  Repeat semua halaman
                </label>

                <button type="button" @click="sections.splice(i,1); refreshBlocks()" class="text-red-600">hapus</button>
              </div>

              {{-- TEXT/HTML --}}
              <div x-show="s.type==='text'">
                <textarea x-model="s.html" class="mt-1 w-full border rounded-lg px-3 py-2" rows="4"
                  placeholder="Ketik teks atau HTML (p, ul/li, b, i, dll)"></textarea>
              </div>

              {{-- TABLE --}}
              <div x-show="s.type==='table'" class="space-y-2">
                <div class="flex gap-2 items-center">
                  <input x-model.number="s.rows" type="number" min="1" class="border rounded px-2 py-1 w-24" placeholder="rows">
                  <input x-model.number="s.cols" type="number" min="1" class="border rounded px-2 py-1 w-24" placeholder="cols">
                  <button type="button" class="px-2 py-1 border rounded" @click="initTable(i)">Buat grid</button>
                </div>
                <div class="overflow-auto" x-show="s.cells?.length">
                  <table class="border-collapse">
                    <template x-for="r in s.rows">
                      <tr>
                        <template x-for="c in s.cols">
                          <td class="border p-1">
                            <input class="border rounded px-1 py-0.5" x-model="s.cells[(r-1)*s.cols + (c-1)]" placeholder="cell">
                          </td>
                        </template>
                      </tr>
                    </template>
                  </table>
                </div>
              </div>
            </div>
          </template>
        </div>

        {{-- QR & Barcode --}}
        <div class="bg-white border rounded-xl p-4 space-y-3">
          <h2 class="font-semibold text-[#1D1C1A]">QR & Barcode</h2>
          <div>
            <label class="text-sm font-medium">QR Text</label>
            <input type="text" name="qr_text" value="{{ old('qr_text',$document->qr_text) }}"
                   class="mt-1 w-full border rounded-lg px-3 py-2" placeholder="https://intra/verify/{{ '{doc_no}' }}">
          </div>
          <div>
            <label class="text-sm font-medium">Barcode Text</label>
            <input type="text" name="barcode_text" value="{{ old('barcode_text',$document->barcode_text) }}"
                   class="mt-1 w-full border rounded-lg px-3 py-2" placeholder="PLT-SOP-003-REV1">
          </div>
          @if($document->qr_image_path || $document->barcode_image_path)
          <div class="flex gap-6 mt-3">
            @if($document->qr_image_path)
              <div><img src="{{ $document->qr_image_path }}" class="h-28"><div class="text-xs mt-1">QR Sekarang</div></div>
            @endif
            @if($document->barcode_image_path)
              <div><img src="{{ $document->barcode_image_path }}" class="h-20"><div class="text-xs mt-1">Barcode Sekarang</div></div>
            @endif
          </div>
          @endif
        </div>

        {{-- Signatures list (opsional) --}}
        @if(is_array($document->signatures))
          <div class="border rounded-xl p-4 space-y-4">
            <h2 class="font-semibold text-[#1D1C1A]">Pengesahan (TTD)</h2>
            @foreach($document->signatures as $i => $sig)
              <div class="p-3 border rounded-lg space-y-3">
                <div class="grid md:grid-cols-3 gap-3">
                  <div>
                    <label class="text-sm">Role</label>
                    <input type="text" name="signatures[{{ $i }}][role]" value="{{ old("signatures.$i.role",$sig['role'] ?? '') }}" class="mt-1 w-full border rounded-lg px-3 py-2">
                  </div>
                  <div>
                    <label class="text-sm">Nama</label>
                    <input type="text" name="signatures[{{ $i }}][name]" value="{{ old("signatures.$i.name",$sig['name'] ?? '') }}" class="mt-1 w-full border rounded-lg px-3 py-2">
                  </div>
                  <div>
                    <label class="text-sm">Jabatan</label>
                    <input type="text" name="signatures[{{ $i }}][position_title]" value="{{ old("signatures.$i.position_title",$sig['position_title'] ?? '') }}" class="mt-1 w-full border rounded-lg px-3 py-2">
                  </div>
                </div>
                <div>
                  <label class="text-sm">TTD (URL)</label>
                  <input type="text" name="signatures[{{ $i }}][image_path]" value="{{ old("signatures.$i.image_path",$sig['image_path'] ?? '') }}" class="mt-1 w-full border rounded-lg px-3 py-2">
                </div>
              </div>
            @endforeach
          </div>
        @endif

        <div class="pt-2 flex items-center justify-end gap-3">
          <a href="{{ route('admin.documents.index') }}" class="px-4 py-2 rounded-xl border text-[#1D1C1A]">Batal</a>
          <button class="px-4 py-2 rounded-xl bg-[#7A2C2F] text-white hover:opacity-90">Update</button>
        </div>
      </div>
      {{-- /KANAN --}}
    </div>
  </form>
</div>

@push('scripts')
<script>
function docBuilder(){
  return {
    // STATE awal
    header:     { logo:{url:'',position:'left'}, title:{align:'center', text:''} },
    footer:     { text:'', show_page_number:true },
    signatures: { rows:[], columns:4, mode:'grid' },
    sections:   [],
    templates: [], templateName: '', selectedTemplateId: '',

    preview: {
      layout: { page:{width:794, height:1123}, margins:{top:40,right:35,bottom:40,left:35}, font:{size:12} },
      zoom: 1.1,
      pagesCount: 1,
      blocks: []
    },

    // Drag/resize
    drag: { active:false, mode:null, handle:null, blk:null, startX:0, startY:0, startTop:0, startLeft:0, startWidth:0, startHeight:0 },

    init(){
      // Read JSON
      try { this.templates = JSON.parse(document.querySelector('#doc-templates-json')?.textContent || '[]'); } catch(e){}
      const existing = JSON.parse(document.querySelector('#doc-existing-json')?.textContent || '{}');

      // Prefill from existing doc
      if (existing.layout)        this.preview.layout = Object.assign(this.preview.layout, existing.layout || {});
      if (existing.header)        this.header         = Object.assign(this.header, existing.header || {});
      if (existing.footer)        this.footer         = Object.assign(this.footer, existing.footer || {});
      if (existing.signatures)    this.signatures     = Object.assign({ rows:[], columns:4, mode:'grid' }, existing.signatures || {});
      if (Array.isArray(existing.sections)) this.sections = existing.sections;

      this.selectedTemplateId = existing.template_id ?? '';

      // Seed blocks (dummy)
      if (!this.preview.blocks.length) {
        this.preview.blocks = [{ id:'dummy', type:'text', text:'Preview Dokumen', top:120, left:120, width:420, height:42, fontSize:16, bold:true, z:10, page:1, origin:'template', repeatEachPage:false }];
      }

      // Template watcher
      this.$watch('selectedTemplateId', (val) => {
        const t = this.templates.find(x => String(x.id) === String(val));
        this.templateName = t ? t.name : '';
        t ? this.applyTemplate(t) : this.resetPreview();
      });

      // Section watcher
      this.$watch('sections', () => this.refreshBlocks(), { deep:true });

      // Apply awal
      if (this.selectedTemplateId) {
        const t = this.templates.find(x => String(x.id) === String(this.selectedTemplateId));
        t ? this.applyTemplate(t) : this.refreshBlocks();
      } else {
        this.applyTemplate({ layout:this.preview.layout, header:this.header, footer:this.footer, signature:{ rows:this.signatures.rows||[] }, blocks: [] });
      }
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
        background: blk.type==='tableCell' ? 'rgba(249,250,251,.9)' : 'transparent',
        cursor: blk.origin==='section' ? (this.drag.mode ? 'grabbing' : 'grab') : 'default'
      };
    },

    resetPreview(){
      this.preview.layout = { page:{width:794, height:1123}, margins:{top:40,right:35,bottom:40,left:35}, font:{size:12} };
      this.preview.zoom = 1.1;
      this.preview.blocks = [{ id:'dummy', type:'text', text:'Preview Dokumen', top:120, left:120, width:420, height:42, fontSize:16, bold:true, z:10, page:1, origin:'template', repeatEachPage:false }];
      this.preview.pagesCount = 1;
      this.refreshBlocks();
    },

    // Hitung left berdasarkan align (left|center|right)
    computeLeftByAlign(blk) {
      const L = this.preview.layout;
      const contentW = L.page.width - L.margins.left - L.margins.right;
      const w = blk.width ?? 100;
      const a = (blk.align || '').toLowerCase();
      if (a === 'center') {
        return Math.round(L.margins.left + (contentW - w) / 2);
      }
      if (a === 'right') {
        return Math.max(L.margins.left, Math.round(L.page.width - L.margins.right - w));
      }
      return blk.left ?? L.margins.left;
    },

    applyTemplate(tpl){
      // Layout + config
      this.preview.layout = Object.assign(
        { page:{width:794,height:1123}, margins:{top:40,right:35,bottom:40,left:35}, font:{size:12} },
        tpl.layout || {}
      );
      this.header     = Object.assign({ logo:{url:'',position:'left'}, title:{align:'center', text:''} }, tpl.header || {});
      this.footer     = Object.assign({ text:'', show_page_number:true }, tpl.footer || {});
      this.signatures = Object.assign({ rows:[], columns:4, mode:'grid' }, tpl.signature ? { rows:(tpl.signature.rows||[]) } : this.signatures);

      // Blocks dari template (opsional)
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

      // Sesuaikan left berdasarkan align
      this.preview.blocks = this.preview.blocks.map(b => {
        if (['header','footer','signature','text','image','html'].includes(b.type) && b.align) {
          b.left = this.computeLeftByAlign(b);
        }
        return b;
      });

      // HEADER default (page 1)
      const hasHeader = this.preview.blocks.some(b => b.type==='header' && b.page===1);
      if (!hasHeader) {
        const L = this.preview.layout;
        const hdr = {
          id: Math.random().toString(36).slice(2,10),
          type: 'header',
          text: this.header?.title?.text || 'Judul Dokumen',
          align: this.header?.title?.align || 'left',
          showMeta: false,
          metaRight: '',
          top: Math.max(8, (L.margins.top - 28)),
          left: L.margins.left,
          width: L.page.width - (L.margins.left + L.margins.right),
          height: 36,
          z: 50, page: 1, origin: 'template', repeatEachPage: true
        };
        hdr.left = this.computeLeftByAlign(hdr);
        this.preview.blocks.push(hdr);
      }

      // FOOTER default (page 1)
      const hasFooter = this.preview.blocks.some(b => b.type==='footer' && b.page===1);
      if (!hasFooter) {
        const L = this.preview.layout;
        const ftr = {
          id: Math.random().toString(36).slice(2,10),
          type: 'footer',
          text: this.footer?.text || '',
          showPage: this.footer?.show_page_number ?? true,
          align: 'left',
          top: L.page.height - (L.margins.bottom + 28),
          left: L.margins.left,
          width: L.page.width - (L.margins.left + L.margins.right),
          height: 28,
          z: 50, page: 1, origin: 'template', repeatEachPage: true
        };
        ftr.left = this.computeLeftByAlign(ftr);
        this.preview.blocks.push(ftr);
      }

      // SIGNATURE default (page 1) bila ada rows
      const hasSig = this.preview.blocks.some(b => b.type==='signature' && b.page===1);
      if (!hasSig && Array.isArray(this.signatures?.rows) && this.signatures.rows.length) {
        const L = this.preview.layout;
        const h = 90;
        const y = Math.max(L.margins.top + 140, (L.page.height - L.margins.bottom - 28 - h - 8));
        const sig = {
          id: Math.random().toString(36).slice(2,10),
          type: 'signature',
          role: 'Disetujui oleh',
          name: this.signatures.rows?.[0]?.name || '',
          position: this.signatures.rows?.[0]?.position_title || '',
          align: 'center',
          top: y,
          left: L.margins.left,
          width: L.page.width - (L.margins.left + L.margins.right),
          height: h,
          z: 40, page: 1, origin: 'template', repeatEachPage: true
        };
        sig.left = this.computeLeftByAlign(sig);
        this.preview.blocks.push(sig);
      }

      this.refreshBlocks();
      this.rebuildRepeatingBlocksAcrossPages();
    },

    // Rebuild blocks (template + sections)
    refreshBlocks(){
      const L = this.preview.layout;
      const contentTop    = L.margins.top;
      const contentLeft   = L.margins.left;
      const contentWidth  = L.page.width  - L.margins.left - L.margins.right;
      const contentBottom = L.page.height - L.margins.bottom;

      const staticBlocks = (this.preview.blocks||[]).filter(b => b.origin!=='section');

      const sectionBlocks = [];
      const makeId = () => Math.random().toString(36).slice(2,10);

      const pagesNow = Math.max(1, this.preview.pagesCount|0);
      let maxPage = pagesNow;

      this.sections.forEach((s, idx) => {
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

          // AUTO FLOW sederhana (geser ke halaman berikut kalau nabrak margin bawah)
          let rect = {...base};
          if (!s.repeatEachPage && s.autoFlow && (rect.top + rect.height) > (contentBottom)) {
            rect.page += 1;
            rect.top = contentTop;
          }
          maxPage = Math.max(maxPage, rect.page);

          if ((s.type || 'text') === 'text') {
            const title    = s.label ? `<div style="font-weight:600;margin-bottom:2px">${s.label}</div>` : '';
            const subtitle = s.subtitle ? `<div style="color:#6b7280;font-size:12px;margin-bottom:6px">${s.subtitle}</div>` : '';
            const body     = s.html ? s.html : `<p style="color:#6b7280">(${s.label||'Section'})</p>`;
            sectionBlocks.push({ ...rect, type:'html', html: `${title}${subtitle}${body}` });
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

    addSection(){
      this.sections.push({
        key: 'sec_'+Date.now(),
        label: 'Section Baru',
        subtitle: '',
        type: 'text',
        html: '',
        rows: 2, cols: 2, cells: [],
        top: null, left: null, width: null, height: null,
        page: 1,
        autoFlow: true,
        repeatEachPage: false
      });
    },

    initTable(i){
      const s = this.sections[i];
      const total = Math.max(1,(s.rows|0)) * Math.max(1,(s.cols|0));
      s.cells = Array.from({length: total}, (_,k) => s.cells?.[k] ?? '');
      if (!Number.isFinite(+s.width))  s.width  = 520;
      if (!Number.isFinite(+s.height)) s.height = 140;
      this.refreshBlocks();
    },

    // Drag & Resize
    onBlockMouseDown(e, blk){
      this.drag.active = true; this.drag.mode = 'move'; this.drag.handle = null; this.drag.blk = blk;
      this.drag.startX = e.clientX; this.drag.startY = e.clientY;
      this.drag.startTop = blk.top ?? 0; this.drag.startLeft = blk.left ?? 0;

      const onMove = (ev) => {
        if (!this.drag.active || this.drag.mode!=='move') return;
        const scale = this.preview.zoom || 1;
        const dx = (ev.clientX - this.drag.startX) / scale;
        const dy = (ev.clientY - this.drag.startY) / scale;

        blk.top  = Math.max(0, Math.round(this.drag.startTop  + dy));
        blk.left = Math.max(0, Math.round(this.drag.startLeft + dx));

        this.updateSectionRectFromBlock(blk, { top: blk.top, left: blk.left }, { silent:true });
      };
      const onUp = () => {
        this.endDrag();
        window.removeEventListener('mousemove', onMove);
        window.removeEventListener('mouseup', onUp);
        this.updateSectionRectFromBlock(blk, { top: blk.top, left: blk.left });
      };
      window.addEventListener('mousemove', onMove);
      window.addEventListener('mouseup', onUp);
    },

    onResizeMouseDown(e, blk, handle){
      this.drag.active = true; this.drag.mode = 'resize'; this.drag.handle = handle;
      this.drag.blk = blk;
      this.drag.startX = e.clientX; this.drag.startY = e.clientY;
      this.drag.startWidth  = blk.width  ?? 100;
      this.drag.startHeight = blk.height ?? 32;
      this.drag.startTop    = blk.top    ?? 0;
      this.drag.startLeft   = blk.left   ?? 0;

      const onMove = (ev) => {
        if (!this.drag.active || this.drag.mode!=='resize') return;
        const scale = this.preview.zoom || 1;
        const dx = (ev.clientX - this.drag.startX) / scale;
        const dy = (ev.clientY - this.drag.startY) / scale;

        if (this.drag.handle === 'br') {
          blk.width  = Math.max(20, Math.round(this.drag.startWidth  + dx));
          blk.height = Math.max(20, Math.round(this.drag.startHeight + dy));
          this.updateSectionRectFromBlock(blk, { width: blk.width, height: blk.height }, { silent:true });
        } else if (this.drag.handle === 'tl') {
          const newLeft = Math.max(0, Math.round(this.drag.startLeft + dx));
          const newTop  = Math.max(0, Math.round(this.drag.startTop  + dy));
          const newW = Math.max(20, Math.round(this.drag.startWidth  - dx));
          const newH = Math.max(20, Math.round(this.drag.startHeight - dy));
          blk.left = newLeft; blk.top = newTop; blk.width = newW; blk.height = newH;
          this.updateSectionRectFromBlock(blk, { left:newLeft, top:newTop, width:newW, height:newH }, { silent:true });
        }
      };
      const onUp = () => {
        this.endDrag();
        window.removeEventListener('mousemove', onMove);
        window.removeEventListener('mouseup', onUp);
        this.updateSectionRectFromBlock(blk, { top: blk.top, left: blk.left, width: blk.width, height: blk.height });
      };
      window.addEventListener('mousemove', onMove);
      window.addEventListener('mouseup', onUp);
    },

    endDrag(){ this.drag.active = false; this.drag.mode = null; this.drag.handle = null; this.drag.blk = null; },

    updateSectionRectFromBlock(blk, part, opts = {}){
      if (!blk || blk.origin!=='section') return;
      const key = blk.refKey || blk.label;
      const i = this.sections.findIndex(s => (s.key === key) || (s.label===key));
      if (i === -1) return;
      const s = this.sections[i];
      if (part.top    !== undefined) s.top    = Math.round(part.top);
      if (part.left   !== undefined) s.left   = Math.round(part.left);
      if (part.width  !== undefined) s.width  = Math.round(part.width);
      if (part.height !== undefined) s.height = Math.round(part.height);
      if (!opts.silent) this.refreshBlocks();
    },

    // Helpers repeat template
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
          nb.id = Math.random().toString(36).slice(2,10);
          nb.page = pg;
          return nb;
        });
        this.preview.blocks.push(...clones);
      }
    },

    // Halaman baru
    addPage(){
      this.preview.pagesCount = (this.preview.pagesCount|0) + 1;
      const newPage = this.preview.pagesCount;

      const base = this.getRepeatingTemplateBlocks();
      const clones = base.map(b => {
        const nb = JSON.parse(JSON.stringify(b));
        nb.id = Math.random().toString(36).slice(2,10);
        nb.page = newPage;
        return nb;
      });

      const sectionBlocks = (this.preview.blocks||[]).filter(b => b.origin==='section'); // diganti oleh refresh
      const staticBlocks  = (this.preview.blocks||[]).filter(b => b.origin!=='section');
      this.preview.blocks = [...staticBlocks, ...clones, ...sectionBlocks];

      this.refreshBlocks(); // sections repeatEachPage juga ikut
    },
  }
}
</script>
@endpush
@endsection
