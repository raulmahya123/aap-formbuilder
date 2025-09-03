@extends('layouts.app')

@section('title','Edit Template')

@push('styles')
<style>[x-cloak]{display:none!important}</style>
@endpush

@section('content')
@php
  // Normalisasi kolom JSON
  $blocks     = is_array($template->blocks_config)    ? $template->blocks_config    : (json_decode($template->blocks_config   ?? '[]', true) ?: []);
  $layout     = is_array($template->layout_config)    ? $template->layout_config    : (json_decode($template->layout_config   ?? '[]', true) ?: []);
  $header     = is_array($template->header_config)    ? $template->header_config    : (json_decode($template->header_config   ?? '[]', true) ?: []);
  $footer     = is_array($template->footer_config)    ? $template->footer_config    : (json_decode($template->footer_config   ?? '[]', true) ?: []);
  $signature  = is_array($template->signature_config) ? $template->signature_config : (json_decode($template->signature_config?? '[]', true) ?: []);

  // URL foto saat ini (butuh accessor photo_url di model; fallback asset())
  $photoUrl = method_exists($template, 'getPhotoUrlAttribute')
    ? ($template->photo_url ?? null)
    : ($template->photo_path ? asset('storage/'.$template->photo_path) : null);

  // Payload ops untuk Alpine
  $opts = [
    'initialName'       => old('name', $template->name),
    'initialBlocks'     => $blocks,
    'initialLayout'     => $layout,
    'initialHeader'     => $header,
    'initialFooter'     => $footer,
    'initialSignature'  => $signature,
    'initialPhotoUrl'   => $photoUrl, // ← tambahkan untuk preview foto
  ];
@endphp

<div x-data="docDesigner(@js($opts))"
     x-init="init()"
     class="max-w-6xl mx-auto p-6 space-y-6 select-none"
     x-cloak>

  {{-- HEADER --}}
  <div class="flex items-center justify-between">
    <h1 class="text-xl font-semibold text-[#1D1C1A]">Edit Template #{{ $template->id }}</h1>
    <a href="{{ route('admin.document_templates.index') }}" class="px-4 py-2 rounded-xl border">← Kembali</a>
  </div>

  {{-- FORM (UPDATE) --}}
  <form method="POST"
        action="{{ route('admin.document_templates.update',$template) }}"
        enctype="multipart/form-data"
        x-ref="form"
        @submit.prevent="beforeSubmit($event)"
        class="bg-white border rounded-xl">
    @csrf @method('PUT')

    {{-- INPUT: Nama Template --}}
    <div class="px-4 pt-4">
      <label class="block text-sm font-medium text-[#1D1C1A]">Nama Template</label>
      <input type="text" name="name" required
             class="mt-1 w-full border rounded-xl px-3 py-2"
             placeholder="Nama Template"
             x-model="name">
      @error('name')<p class="text-sm text-red-600 mt-1">{{ $message }}</p>@enderror
    </div>

    {{-- INPUT: Foto Template (photo_path) --}}
    <div class="px-4 pt-3">
      <label class="block text-sm font-medium text-[#1D1C1A]">Foto Template (opsional)</label>
      <div class="mt-2 flex items-center gap-3">
        <input type="file"
               name="photo_path"
               accept="image/*"
               x-ref="photoInput"
               @change="onPhotoChange($event)"
               class="block text-sm">
        <template x-if="photo.url">
          <img :src="photo.url" alt="Preview" class="h-16 w-auto rounded border">
        </template>
        <button type="button"
                class="px-3 py-1.5 rounded border text-sm"
                @click="clearPhoto()"
                x-show="photo.url"
                x-cloak>Hapus</button>
        <button type="button"
                class="px-3 py-1.5 rounded border text-sm"
                @click="addPhotoToCanvas()"
                x-show="photo.url"
                x-cloak>Tampilkan di Kanvas</button>
      </div>
      <p class="text-xs text-gray-500 mt-1">
        Jika memilih file baru, foto lama akan diganti saat simpan. Tombol “Hapus” di atas hanya membersihkan preview lokal (tidak menghapus di server).
      </p>
      @error('photo_path')<p class="text-sm text-red-600 mt-1">{{ $message }}</p>@enderror
    </div>

    {{-- HIDDEN JSONS --}}
    <input type="hidden" name="blocks_config"    x-ref="blocksInput">
    <input type="hidden" name="layout_config"    x-ref="layoutInput">
    <input type="hidden" name="header_config"    x-ref="headerInput">
    <input type="hidden" name="footer_config"    x-ref="footerInput">
    <input type="hidden" name="signature_config" x-ref="signatureInput">

    {{-- TOOLBAR --}}
    <div class="border-b px-4 py-3 flex flex-wrap items-center gap-2">
      <div class="font-medium mr-3">Tambah Blok:</div>
      <button type="button" @click="addText('Judul Dokumen')" class="px-3 py-1.5 rounded border text-sm">+ Teks</button>
      <label class="px-3 py-1.5 rounded border text-sm cursor-pointer">
        + Logo / Gambar
        <input type="file" class="hidden" accept="image/*" @change="addImageFromFile($event)">
      </label>
      <button type="button" @click="addHeaderRow()" class="px-3 py-1.5 rounded border text-sm">+ Row Tabel Header (2 sel)</button>
      <button type="button" @click="addFooter()" class="px-3 py-1.5 rounded border text-sm">+ Footer</button>
      <button type="button" @click="addSignature()" class="px-3 py-1.5 rounded border text-sm">+ TTD</button>

      <div class="ml-auto flex items-center gap-3">
        <label class="inline-flex items-center gap-2 text-sm">
          <input type="checkbox" x-model="snap.enabled"> Snap
        </label>
        <div class="flex items-center gap-1 text-sm">
          Grid:
          <input type="number" min="1" class="w-16 border rounded px-2 py-1" x-model.number="snap.grid"> px
        </div>
        <button type="button" @click="deleteSelected()" :disabled="selectedId===null"
                class="px-3 py-1.5 rounded border text-sm disabled:opacity-50">Hapus Blok</button>
      </div>
    </div>

    {{-- SIDEPANEL + CANVAS --}}
    <div class="grid lg:grid-cols-[320px,1fr] gap-0">
      {{-- Side Panel --}}
      <div class="border-r p-4 space-y-4">
        <div class="font-medium">Properti Halaman</div>
        <div class="grid grid-cols-2 gap-2 text-sm">
          <label class="col-span-2">Lebar Halaman (px)
            <input type="number" min="100" class="mt-1 w-full border rounded px-2 py-1" x-model.number="layout.page.width">
          </label>
          <label class="col-span-2">Tinggi Halaman (px)
            <input type="number" min="100" class="mt-1 w-full border rounded px-2 py-1" x-model.number="layout.page.height">
          </label>
          <label class="col-span-2">Margin Top (px)
            <input type="number" class="mt-1 w-full border rounded px-2 py-1" x-model.number="layout.margins.top">
          </label>
          <label>Left
            <input type="number" class="mt-1 w-full border rounded px-2 py-1" x-model.number="layout.margins.left">
          </label>
          <label>Right
            <input type="number" class="mt-1 w-full border rounded px-2 py-1" x-model.number="layout.margins.right">
          </label>
          <label class="col-span-2">Bottom
            <input type="number" class="mt-1 w-full border rounded px-2 py-1" x-model.number="layout.margins.bottom">
          </label>
          <label class="col-span-2">Font size default (pt)
            <input type="number" min="8" class="mt-1 w-full border rounded px-2 py-1" x-model.number="layout.font.size">
          </label>
        </div>

        <template x-if="selected">
          <div class="pt-4 border-t">
            <div class="font-medium">Properti Blok Terpilih</div>
            <div class="text-xs text-gray-500 mb-2" x-text="'ID: '+selected.id+' — '+selected.type"></div>

            <div class="grid grid-cols-2 gap-2 text-sm">
              <label>X (px)
                <input type="number" class="mt-1 w-full border rounded px-2 py-1" x-model.number="selected.left">
              </label>
              <label>Y (px)
                <input type="number" class="mt-1 w-full border rounded px-2 py-1" x-model.number="selected.top">
              </label>
              <label>W (px)
                <input type="number" class="mt-1 w-full border rounded px-2 py-1" x-model.number="selected.width">
              </label>
              <label>H (px)
                <input type="number" class="mt-1 w-full border rounded px-2 py-1" x-model.number="selected.height">
              </label>
              <label class="col-span-2">Z-Index
                <input type="number" class="mt-1 w-full border rounded px-2 py-1" x-model.number="selected.z">
              </label>
            </div>

            {{-- TEXT --}}
            <template x-if="selected.type==='text'">
              <div class="mt-3 space-y-2 text-sm">
                <label>Isi Teks
                  <textarea rows="3" class="mt-1 w-full border rounded px-2 py-1" x-model="selected.text"></textarea>
                </label>
                <label>Align
                  <select class="mt-1 w-full border rounded px-2 py-1" x-model="selected.align">
                    <option>left</option><option>center</option><option>right</option>
                  </select>
                </label>
                <label>Ukuran (pt)
                  <input type="number" min="8" class="mt-1 w-full border rounded px-2 py-1" x-model.number="selected.fontSize">
                </label>
                <label>Bold <input type="checkbox" class="ml-2" x-model="selected.bold"></label>
              </div>
            </template>

            {{-- IMAGE / SIGNATURE: source --}}
            <template x-if="selected.type==='image' || selected.type==='signature'">
              <div class="mt-3 space-y-2 text-sm">
                <label>Sumber Gambar (URL / data URL)
                  <input class="mt-1 w-full border rounded px-2 py-1" x-model="selected.src" placeholder="/uploads/logo.png atau data:image/jpeg;base64,...">
                </label>
                <template x-if="selected.type==='signature'">
                  <div class="flex items-center gap-2">
                    <button type="button" class="px-3 py-1.5 border rounded text-sm" @click="openPad(selected.id)">Gambar TTD</button>
                    <button type="button" class="px-3 py-1.5 border rounded text-sm" @click="selected.src=''">Hapus Gambar</button>
                  </div>
                </template>
              </div>
            </template>

            {{-- SIGNATURE --}}
            <template x-if="selected.type==='signature'">
              <div class="mt-3 space-y-2 text-sm">
                <label>Role
                  <input class="mt-1 w-full border rounded px-2 py-1" x-model="selected.role" placeholder="Disiapkan / Diperiksa / ...">
                </label>
                <label>Nama
                  <input class="mt-1 w-full border rounded px-2 py-1" x-model="selected.name">
                </label>
                <label>Jabatan
                  <input class="mt-1 w-full border rounded px-2 py-1" x-model="selected.position">
                </label>
                <label>Tanda Tangan (Teks, opsional)
                  <input class="mt-1 w-full border rounded px-2 py-1" x-model="selected.signatureText" placeholder="tulis tanda tangan">
                </label>
              </div>
            </template>

            {{-- TABLE CELL --}}
            <template x-if="selected.type==='tableCell'">
              <div class="mt-3 space-y-2 text-sm">
                <label>Label/Value
                  <input class="mt-1 w-full border rounded px-2 py-1" x-model="selected.text" placeholder="Doc.No / (otomatis)">
                </label>
                <label>Bold <input type="checkbox" class="ml-2" x-model="selected.bold"></label>
              </div>
            </template>

            {{-- FOOTER --}}
            <template x-if="selected.type==='footer'">
              <div class="mt-3 space-y-2 text-sm">
                <label>Teks Footer
                  <input class="mt-1 w-full border rounded px-2 py-1" x-model="selected.text" placeholder="© Perusahaan 2025">
                </label>
                <label class="inline-flex items-center gap-2">
                  <input type="checkbox" x-model="selected.showPage">
                  <span>Tampilkan nomor halaman</span>
                </label>
                <div class="grid grid-cols-2 gap-2">
                  <label>Align
                    <select class="mt-1 w-full border rounded px-2 py-1" x-model="selected.align">
                      <option>left</option><option>center</option><option>right</option>
                    </select>
                  </label>
                  <label>Ukuran font (px)
                    <input type="number" min="9" class="mt-1 w-full border rounded px-2 py-1" x-model.number="selected.fontSize">
                  </label>
                </div>
              </div>
            </template>
          </div>
        </template>
      </div>

      {{-- Canvas --}}
      <div class="p-4">
        <div class="bg-[#1D1C1A] text-white px-3 py-2 text-sm rounded-t-xl">
          Kanvas (Custom) — <span x-text="layout.page.width"></span>×<span x-text="layout.page.height"></span> px
        </div>
        <div class="relative bg-white border rounded-b-xl shadow"
             :style="{ width: layout.page.width+'px', height: layout.page.height+'px' }"
             x-ref="page"
             @mousedown="pointerDown($event)"
             @mousemove.window="pointerMove($event)"
             @mouseup.window="pointerUp()"
             @mouseleave="pointerUp()">

          {{-- Margin guides --}}
          <div class="absolute inset-0 pointer-events-none">
            <div class="absolute"
                 :style="{
                   top: layout.margins.top+'px',
                   left: layout.margins.left+'px',
                   width: (layout.page.width - layout.margins.left - layout.margins.right)+'px',
                   height:(layout.page.height - layout.margins.top - layout.margins.bottom)+'px',
                   outline:'1px dashed rgba(0,0,0,.06)'
                 }"></div>
          </div>

          {{-- Blocks --}}
          <template x-for="blk in blocks" :key="blk.id">
            <div
              class="absolute group"
              :style="{ top: blk.top+'px', left: blk.left+'px', width: blk.width+'px', height: blk.height+'px', zIndex: blk.z || 1 }"
              :class="selectedId===blk.id ? 'ring-2 ring-sky-500' : 'ring-1 ring-gray-200'"
              @mousedown.stop="select(blk.id); startMove(blk, $event)"
              @dblclick="if(blk.type==='signature'){ openPad(blk.id) }">

              <template x-if="blk.type==='text'">
                <div class="w-full h-full p-2 overflow-hidden"
                     :style="{ textAlign: blk.align, fontWeight: blk.bold ? '700':'400', fontSize: (blk.fontSize||layout.font.size)+'pt' }"
                     x-text="blk.text"></div>
              </template>

              <template x-if="blk.type==='image'">
                <div class="w-full h-full flex items-center justify-center bg-white">
                  <template x-if="blk.src"><img :src="blk.src" class="max-w-full max-h-full object-contain"></template>
                  <template x-if="!blk.src"><span class="text-xs text-gray-400">[Gambar]</span></template>
                </div>
              </template>

              <template x-if="blk.type==='tableCell'">
                <div class="w-full h-full px-2 py-1 border border-gray-300 bg-gray-50/60 overflow-hidden"
                     :style="{ fontWeight: blk.bold ? '700':'400', fontSize: (blk.fontSize||12)+'px', display:'flex', alignItems:'center' }"
                     x-text="blk.text || '—'"></div>
              </template>

              <template x-if="blk.type==='footer'">
                <div class="w-full h-full px-2 py-1 flex items-center justify-between bg-white"
                     :style="{ fontSize: (blk.fontSize||11)+'px', textAlign: blk.align || 'left' }">
                  <div class="truncate" x-text="blk.text || '© Perusahaan 2025'"></div>
                  <div class="text-xs text-gray-600" x-show="blk.showPage">Halaman 1 / N</div>
                </div>
              </template>

              <template x-if="blk.type==='signature'">
                <div class="w-full h-full p-2 bg-white/90 backdrop-blur rounded">
                  <div class="text-[11px] text-gray-600" x-text="blk.role || 'Role'"></div>
                  <div class="mt-1 w-full flex-1 border border-dashed rounded flex items-center justify-center" style="height:38px;">
                    <template x-if="blk.src"><img :src="blk.src" class="max-h-full object-contain"></template>
                    <template x-if="!blk.src && blk.signatureText"><span class="italic" x-text="blk.signatureText"></span></template>
                    <template x-if="!blk.src && !blk.signatureText"><span class="text-[10px] text-gray-400">TTD</span></template>
                  </div>
                  <div class="mt-1">
                    <div class="text-xs font-medium truncate" x-text="blk.name || 'Nama'"></div>
                    <div class="text-[11px] text-gray-600 truncate" x-text="blk.position || 'Jabatan'"></div>
                  </div>
                </div>
              </template>

              <div class="absolute right-0 bottom-0 translate-x-1/2 translate-y-1/2 w-3 h-3 bg-sky-500 rounded-sm cursor-se-resize opacity-0 group-hover:opacity-100"
                   @mousedown.stop="startResize(blk, $event)"></div>
            </div>
          </template>
        </div>

        <div class="text-[11px] text-gray-500 mt-2">
          Drag untuk memindah blok. Tarik handle biru di pojok kanan-bawah untuk resize. Aktifkan “Snap” untuk menempel ke grid.
        </div>
      </div>
    </div>

    <div class="px-4 py-3 border-t flex items-center justify-between gap-3">
      <button type="button" class="px-3 py-2 rounded-xl border" @click="resetToInitial()">Reset dari DB</button>
      <div class="flex items-center gap-2">
        <a href="{{ route('admin.document_templates.index') }}" class="px-4 py-2 rounded-xl border">Batal</a>
        <button class="px-4 py-2 rounded-xl bg-[#7A2C2F] text-white hover:opacity-90">Simpan</button>
      </div>
    </div>
  </form>

  {{-- DRAW PAD MODAL --}}
  <div x-show="pad.open" x-cloak @keydown.escape="closePad()"
       class="fixed inset-0 z-50 flex items-center justify-center">
    <div class="absolute inset-0 bg-black/40" @click="closePad()"></div>
    <div class="relative bg-white rounded-xl shadow-xl w-[720px] max-w-[95vw]" x-transition.scale.origin.center>
      <div class="px-4 py-3 border-b flex items-center justify-between">
        <div class="font-semibold">Gambar TTD</div>
        <button class="text-sm" @click="closePad()">✕</button>
      </div>
      <div class="p-4 space-y-3">
        <div class="flex items-center gap-3">
          <label class="text-sm">Ketebalan</label>
          <input type="range" min="1" max="8" x-model.number="pad.stroke" class="w-48">
          <span class="text-sm" x-text="pad.stroke + ' px'"></span>
          <button type="button" class="ml-auto px-3 py-2 border rounded" @click="padClear()">Clear</button>
        </div>
        <div class="relative border rounded-lg bg-gray-50">
          <canvas x-ref="sigCanvas" width="640" height="200" class="block w-full h-auto touch-none cursor-crosshair bg-white rounded"></canvas>
        </div>
        <div class="flex items-center justify-end gap-3">
          <button type="button" class="px-4 py-2 rounded border" @click="closePad()">Batal</button>
          <button type="button" class="px-4 py-2 rounded bg-[#7A2C2F] text-white" @click="padSave()">Simpan ke Blok</button>
        </div>
      </div>
    </div>
  </div>
  {{-- /MODAL --}}
</div>
@endsection

@push('scripts')
<script>
// ===== Helpers =====
const DEFAULT_LAYOUT = { page:{ width:794, height:1123 }, margins:{ top:30, right:25, bottom:25, left:25 }, font:{ size:11 } };

function deepMerge(base, patch){
  const out = JSON.parse(JSON.stringify(base));
  if (!patch || typeof patch !== 'object') return out;
  for (const k in patch){
    if (patch[k] && typeof patch[k] === 'object' && !Array.isArray(patch[k])) out[k] = deepMerge(out[k] ?? {}, patch[k]);
    else out[k] = patch[k];
  }
  return out;
}

// ===== Alpine component =====
function docDesigner(opts = {}){
  const initialLayout    = (opts.initialLayout && typeof opts.initialLayout==='object') ? opts.initialLayout : {};
  const initialBlocks    = Array.isArray(opts.initialBlocks) ? opts.initialBlocks : [];
  const initialHeader    = (opts.initialHeader && typeof opts.initialHeader==='object') ? opts.initialHeader : {};
  const initialFooter    = (opts.initialFooter && typeof opts.initialFooter==='object') ? opts.initialFooter : {};
  const initialSignature = (opts.initialSignature && typeof opts.initialSignature==='object') ? opts.initialSignature : {};

  return {
    // STATE
    name: opts.initialName || '',
    layout: deepMerge(DEFAULT_LAYOUT, initialLayout),
    blocks: initialBlocks.slice(),
    selectedId: null,
    moving:{active:false,id:null,ox:0,oy:0},
    resizing:{active:false,id:null,startW:0,startH:0,startX:0,startY:0},
    snap:{enabled:true,grid:8},
    pad:{open:false,id:null,ctx:null,drawing:false,stroke:3,last:{x:0,y:0}},

    // preview foto template
    photo: { url: opts.initialPhotoUrl || '' },

    _initial:{
      name: opts.initialName || '',
      layout: deepMerge(DEFAULT_LAYOUT, initialLayout),
      blocks: initialBlocks.slice(),
      header: initialHeader,
      footer: initialFooter,
      signature: initialSignature,
      photoUrl: opts.initialPhotoUrl || '',
    },

    // INIT
    init(){
      // Import dari legacy bila tidak ada blocks
      if (!this.blocks.length) this.importFromLegacy({header:initialHeader, footer:initialFooter, signature:initialSignature});
      // Seed default jika tetap kosong
      if (!this.blocks.length){
        this.blocks = [
          this.mkText('Judul Dokumen',  this.layout.margins.top + 8,  this.layout.margins.left + 160, 400, 40, 'center', true, 18),
          this.mkImage('',               this.layout.margins.top + 4,  this.layout.margins.left +  0,  120, 48),
          this.mkTableCell('Doc.No',     this.layout.margins.top + 60, this.layout.margins.left + 0,  160, 32, true),
          this.mkTableCell('(otomatis)', this.layout.margins.top + 60, this.layout.margins.left + 160, 420, 32, false),
          this.mkFooter('© Perusahaan 2025',
            this.layout.page.height - this.layout.margins.bottom - 36,
            this.layout.margins.left,
            this.layout.page.width - this.layout.margins.left - this.layout.margins.right,
            36, true, 'left', 11),
        ];
      }
    },

    // Import dari payload legacy (opsional)
    importFromLegacy({header={}, footer={}, signature={}}={}){
      if (header && Array.isArray(header.items)){
        header.items.forEach(it=>{
          if (it.type==='text') this.blocks.push(this.mkText(it.text||'', it.top||0, it.left||0, it.width||200, it.height||30, it.align||'left', !!it.bold, it.font_size||this.layout.font.size));
          else if (it.type==='image') this.blocks.push(this.mkImage(it.src||'', it.top||0, it.left||0, it.width||100, it.height||40));
          else if (it.type==='tableCell') this.blocks.push(this.mkTableCell(it.text||'', it.top||0, it.left||0, it.width||160, it.height||32, !!it.bold));
        });
      }
      if (footer && Array.isArray(footer.items)){
        footer.items.forEach(it=>{
          this.blocks.push(this.mkFooter(it.text||'', it.top||0, it.left||0, it.width||300, it.height||28, !!it.show_page_number, it.align||'left', it.font_size||11));
        });
      }
      if (signature && Array.isArray(signature.rows)){
        signature.rows.forEach(r=>{
          const blk=this.mkSignature(r.role||'', r.name||'', r.position_title||'', r.top||0, r.left||0, r.width||160, r.height||70);
          blk.signatureText = r.signature_text||'';
          blk.src = r.image_path||'';
          this.blocks.push(blk);
        });
      }
    },

    // Builders
    uid(){ return Math.random().toString(36).slice(2,10); },
    mkText(text,top,left,w,h,align='left',bold=false,fontSize=14){ return { id:this.uid(), type:'text', text, align, bold, fontSize, top,left,width:w,height:h, z:10 }; },
    mkImage(src,top,left,w,h){ return { id:this.uid(), type:'image', src, top,left,width:w,height:h, z:10 }; },
    mkTableCell(text,top,left,w,h,bold=false){ return { id:this.uid(), type:'tableCell', text, bold, top,left,width:w,height:h, z:10 }; },
    mkFooter(text,top,left,w,h,showPage=true,align='left',fontSize=11){ return { id:this.uid(), type:'footer', text, showPage, align, fontSize, top,left,width:w,height:h, z:5 }; },
    mkSignature(role,name,position,top,left,w,h){ return { id:this.uid(), type:'signature', role, name, position, signatureText:'', src:'', top,left,width:w,height:h, z:10 }; },

    // Actions
    addText(t='Teks'){ this.blocks.push(this.mkText(t, 120, 60, 240, 40)); },
    addImageFromFile(e){ const f=e.target.files?.[0]; if(!f) return; const url=URL.createObjectURL(f); this.blocks.push(this.mkImage(url,120,320,160,80)); e.target.value=''; },
    addHeaderRow(){ const y=this.layout.margins.top+60+(this.blocks.filter(b=>b.type==='tableCell').length/2)*34; this.blocks.push(this.mkTableCell('Label', y, this.layout.margins.left,160,32,true)); this.blocks.push(this.mkTableCell('(otomatis)', y, this.layout.margins.left+160,420,32,false)); },
    addFooter(){ this.blocks.push(this.mkFooter('© Perusahaan 2025', this.layout.page.height-this.layout.margins.bottom-36, this.layout.margins.left, this.layout.page.width-this.layout.margins.left-this.layout.margins.right, 36, true, 'left', 11)); },
    addSignature(){ this.blocks.push(this.mkSignature('Signer','', '', this.layout.page.height-260, this.layout.margins.left+60, 160, 70)); },

    // Foto template (preview + ke kanvas)
    onPhotoChange(e){ const f=e.target.files?.[0]; if(!f){ this.photo.url=this._initial.photoUrl || ''; return; } this.photo.url=URL.createObjectURL(f); },
    clearPhoto(){ this.photo.url=this._initial.photoUrl || ''; if(this.$refs.photoInput) this.$refs.photoInput.value=null; },
    addPhotoToCanvas(){ if(!this.photo.url) return; this.blocks.push(this.mkImage(this.photo.url, this.layout.margins.top + 4, this.layout.margins.left, 120, 48)); },

    // Select / Drag / Resize
    get selected(){ return this.blocks.find(b=>b.id===this.selectedId)||null; },
    select(id){ this.selectedId=id; },
    deleteSelected(){ if(this.selectedId===null) return; const i=this.blocks.findIndex(b=>b.id===this.selectedId); if(i>=0) this.blocks.splice(i,1); this.selectedId=null; },
    pointerDown(){},
    startMove(blk,evt){ this.select(blk.id); this.moving.active=true; this.moving.id=blk.id; const r=this.$refs.page.getBoundingClientRect(); this.moving.ox=(evt.clientX-r.left)-blk.left; this.moving.oy=(evt.clientY-r.top)-blk.top; },
    pointerMove(evt){
      if(this.moving.active){
        const blk=this.blocks.find(b=>b.id===this.moving.id); if(!blk) return;
        const r=this.$refs.page.getBoundingClientRect();
        let x=(evt.clientX-r.left)-this.moving.ox, y=(evt.clientY-r.top)-this.moving.oy;
        x=Math.max(0, Math.min(x, this.layout.page.width-blk.width));
        y=Math.max(0, Math.min(y, this.layout.page.height-blk.height));
        if(this.snap.enabled){ x=Math.round(x/this.snap.grid)*this.snap.grid; y=Math.round(y/this.snap.grid)*this.snap.grid; }
        blk.left=x; blk.top=y; return;
      }
      if(this.resizing.active){
        const blk=this.blocks.find(b=>b.id===this.resizing.id); if(!blk) return;
        const dx=evt.clientX-this.resizing.startX, dy=evt.clientY-this.resizing.startY;
        let w=Math.max(24, this.resizing.startW+dx), h=Math.max(24, this.resizing.startH+dy);
        if(this.snap.enabled){ w=Math.round(w/this.snap.grid)*this.snap.grid; h=Math.round(h/this.snap.grid)*this.snap.grid; }
        blk.width=w; blk.height=h;
      }
    },
    pointerUp(){ this.moving.active=false; this.moving.id=null; this.resizing.active=false; this.resizing.id=null; },
    startResize(blk,evt){ this.select(blk.id); this.resizing.active=true; this.resizing.id=blk.id; this.resizing.startW=blk.width; this.resizing.startH=blk.height; this.resizing.startX=evt.clientX; this.resizing.startY=evt.clientY; },

    // RESET
    resetToInitial(){
      this.name = this._initial.name;
      this.layout = deepMerge(DEFAULT_LAYOUT, this._initial.layout||{});
      this.blocks = (this._initial.blocks && this._initial.blocks.length) ? this._initial.blocks.slice() : [];
      this.photo.url = this._initial.photoUrl || '';
      this.selectedId=null;
    },

    // SUBMIT
    beforeSubmit(){
      const header={ mode:'absolute', items:this.blocks.filter(b=>['text','image','tableCell'].includes(b.type)).map(({type,top,left,width,height,text,src,bold,fontSize,align})=>({ type, top,left,width,height, text:text||'', src:src||'', bold:!!bold, font_size: fontSize || this.layout.font.size, align: align || 'left' })) };
      const footer={ items:this.blocks.filter(b=>b.type==='footer').map(({text,showPage,align,fontSize,top,left,width,height})=>({ text:text||'', show_page_number:!!showPage, align:align||'left', font_size:fontSize||11, top,left,width,height })) };
      const signature={ mode:'absolute', rows:this.blocks.filter(b=>b.type==='signature').map(({role,name,position,signatureText,src,top,left,width,height})=>({ role:role||'', name:name||'', position_title:position||'', signature_text:signatureText||'', image_path:src||'', top,left,width,height })) };
      this.$refs.blocksInput.value    = JSON.stringify(this.blocks);
      this.$refs.layoutInput.value    = JSON.stringify(this.layout);
      this.$refs.headerInput.value    = JSON.stringify(header);
      this.$refs.footerInput.value    = JSON.stringify(footer);
      this.$refs.signatureInput.value = JSON.stringify(signature);
      this.$refs.form.submit();
    },
  };
}
</script>
@endpush
