@extends('layouts.app')

@section('title','Buat Document Template (Drag, Resize, Custom Page)')

@push('styles')
<style>
  [x-cloak]{display:none!important}

  /* Hilangkan garis/border default pada input/select/textarea */
  input, select, textarea {
    border: 0 !important;
    outline: 0 !important;
    box-shadow: none !important;
  }
  /* Pastikan saat fokus tetap tidak ada ring */
  .focus\:ring-0:focus { box-shadow: none !important; }

  /* ===== Panel lebih besar ===== */
  .panel-lg { font-size: 0.95rem; } /* ~15.2px */
  .panel-lg .panel-title { font-size: 1rem; font-weight: 600; }
  .panel-lg .field-grid { gap: .625rem; } /* 10px */
  .panel-lg label { font-size: 0.95rem; }
  .panel-lg input,
  .panel-lg select,
  .panel-lg textarea {
    padding: .6rem .7rem;
    font-size: 0.95rem;
    border-radius: .75rem; /* rounded-xl */
    border-width: 1px;
  }
  .panel-lg .hint { font-size: .8rem; color: #6b7280; } /* gray-500 */

  /* Garis panduan & label jarak */
  .guide-line {
    position: absolute;
    pointer-events: none;
  }
  .guide-badge {
    position: absolute;
    font-size: 10px;
    line-height: 1;
    padding: 2px 4px;
    border-radius: 4px;
    background: rgba(17,17,17,.85);
    color: #fff;
    pointer-events: none;
    transform: translate(-50%, -50%);
    white-space: nowrap;
  }
</style>
@endpush

@section('content')
<div x-data="docDesigner()" x-init="init()" class="max-w-6xl mx-auto p-6 space-y-6 select-none">
  {{-- HEADER --}}
  <div class="flex items-center justify-between">
    <h1 class="text-xl font-semibold text-[#1D1C1A]">Buat Template — Drag, Drop, Resize & Custom Page</h1>
    <a href="{{ route('admin.document_templates.index') }}" class="px-4 py-2 rounded-xl border text-[#1D1C1A]">← Kembali</a>
  </div>

  {{-- FORM --}}
  <form method="POST"
        action="{{ route('admin.document_templates.store') }}"
        enctype="multipart/form-data"
        x-ref="form"
        @submit.prevent="beforeSubmit($event)"
        class="bg-white border rounded-xl">
    @csrf

    {{-- INPUT: Nama Template --}}
    <div class="px-4 pt-4">
      <label class="block text-sm font-medium text-[#1D1C1A]">Nama Template</label>
      <input type="text" name="name" required
             class="mt-1 w-full rounded-xl px-3 py-2 border-2 border-gray-400 focus:border-[#7A2C2F] focus:ring-0 focus:outline-none"
             placeholder="Contoh: Template Surat Jalan"
             value="{{ old('name') }}">
      @error('name') <p class="text-sm text-red-600 mt-1">{{ $message }}</p> @enderror
    </div>

    {{-- INPUT: Foto Template (photo_path) --}}
    <div class="px-4 pt-3">
      <label class="block text-sm font-medium text-[#1D1C1A]">Foto Template (opsional)</label>
      <div class="mt-2 flex items-center gap-3">
        <input type="file"
               name="photo_path"
               x-ref="photoInput"
               accept="image/*"
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
        Gambar ini disimpan ke field <code>photo_path</code> pada template dan bisa dipakai sebagai logo default saat membuat dokumen dari template ini.
      </p>
      @error('photo_path') <p class="text-sm text-red-600 mt-1">{{ $message }}</p> @enderror
    </div>

    {{-- HIDDEN JSONS --}}
    <input type="hidden" name="blocks_config" x-ref="blocksInput">
    <input type="hidden" name="layout_config" x-ref="layoutInput">
    <input type="hidden" name="header_config" x-ref="headerInput">
    <input type="hidden" name="footer_config" x-ref="footerInput">
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
      <button type="button" @click="openTableBuilder()" class="px-3 py-1.5 rounded border text-sm">+ Tabel (grid)</button>
      <button type="button" @click="addFooter()" class="px-3 py-1.5 rounded border text-sm">+ Footer</button>
      <button type="button" @click="addSignature()" class="px-3 py-1.5 rounded border text-sm">+ TTD</button>

      <div class="ml-auto flex flex-wrap items-center gap-3">
        <label class="inline-flex items-center gap-2 text-sm">
          <input type="checkbox" x-model="snap.enabled"> Snap
        </label>
        <div class="flex items-center gap-1 text-sm">
          Grid:
          <input type="number" min="1" class="w-16 border rounded px-2 py-1" x-model.number="snap.grid"> px
        </div>

        <label class="inline-flex items-center gap-2 text-sm">
          <input type="checkbox" x-model="guides"> Show guides
        </label>

        {{-- Colors --}}
        <div class="flex items-center gap-2 text-sm">
          <span>Guide Color</span>
          <input type="color" x-model="colors.guide" class="w-8 h-6 p-0 border rounded">
        </div>
        <div class="flex items-center gap-2 text-sm">
          <span>Page Border</span>
          <input type="color" x-model="colors.pageBorder" class="w-8 h-6 p-0 border rounded">
        </div>

        <div class="h-5 w-px bg-gray-200 mx-1"></div>
        <button type="button" @click="centerSelectedH()" :disabled="selectedId===null" class="px-3 py-1.5 rounded border text-sm disabled:opacity-50">Center H</button>
        <button type="button" @click="centerSelectedV()" :disabled="selectedId===null" class="px-3 py-1.5 rounded border text-sm disabled:opacity-50">Center V</button>
        <button type="button" @click="centerSelectedBoth()" :disabled="selectedId===null" class="px-3 py-1.5 rounded border text-sm disabled:opacity-50">Center Both</button>
        <button type="button" @click="toggleBorderSelected()" :disabled="selectedId===null" class="px-3 py-1.5 rounded border text-sm disabled:opacity-50">Toggle Border</button>
        <button type="button" @click="bringToFront()" :disabled="selectedId===null" class="px-3 py-1.5 rounded border text-sm disabled:opacity-50">Bring Front</button>
        <button type="button" @click="sendToBack()" :disabled="selectedId===null" class="px-3 py-1.5 rounded border text-sm disabled:opacity-50">Send Back</button>

        {{-- Duplicate, Copy, Paste --}}
        <button type="button" @click="duplicateSelected()" :disabled="selectedId===null" class="px-3 py-1.5 rounded border text-sm disabled:opacity-50">Duplicate</button>
        <button type="button" @click="copySelected()" :disabled="selectedId===null" class="px-3 py-1.5 rounded border text-sm disabled:opacity-50">Copy</button>
        <button type="button" @click="pasteClipboard()" :disabled="!clipboard" class="px-3 py-1.5 rounded border text-sm disabled:opacity-50">Paste</button>

        <button type="button" @click="deleteSelected()" :disabled="selectedId===null" class="px-3 py-1.5 rounded border text-sm disabled:opacity-50">Hapus Blok</button>
      </div>
    </div>

    {{-- SIDEPANEL + CANVAS --}}
    <div class="grid lg:grid-cols-[380px,1fr] gap-0">
      {{-- Side Panel --}}
      <div class="border-r p-5 space-y-5 panel-lg">
        <div class="panel-title">Properti Halaman</div>
        <div class="grid grid-cols-2 field-grid">
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

          {{-- Default font family halaman --}}
          <label class="col-span-2">Font family default
            <select class="mt-1 w-full border rounded px-2 py-1" x-model="layout.font.family">
              <option value="Poppins, sans-serif">Poppins (sans)</option>
              <option value="Inter, sans-serif">Inter (sans)</option>
              <option value="Roboto, sans-serif">Roboto (sans)</option>
              <option value="Arial, Helvetica, sans-serif">Arial</option>
              <option value="'Times New Roman', Times, serif">Times New Roman</option>
              <option value="Georgia, serif">Georgia</option>
              <option value="'Courier New', Courier, monospace">Courier New</option>
              <option value="system-ui, -apple-system, Segoe UI, Roboto, Ubuntu, Cantarell, Noto Sans, Helvetica Neue, Arial, 'Apple Color Emoji', 'Segoe UI Emoji'">System UI</option>
            </select>
          </label>

          <label class="col-span-2">Font size default (pt)
            <input type="number" min="8" class="mt-1 w-full border rounded px-2 py-1" x-model.number="layout.font.size">
          </label>
        </div>

        <template x-if="selected">
          <div class="pt-5 border-t">
            <div class="panel-title">Properti Blok Terpilih</div>
            <div class="hint mb-3" x-text="'ID: '+selected.id+' — '+selected.type"></div>

            <div class="grid grid-cols-2 field-grid">
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
              <label class="col-span-2 inline-flex items-center gap-2">
                <input type="checkbox" x-model="selected.border"> Border
              </label>
            </div>

            {{-- TEXT --}}
            <template x-if="selected.type==='text'">
              <div class="mt-4 space-y-3">
                <label>Isi Teks
                  <textarea rows="4" class="mt-1 w-full border rounded-xl px-3 py-2" x-model="selected.text"></textarea>
                </label>

                <div class="grid grid-cols-2 field-grid">
                  <label>Align
                    <select class="mt-1 w-full border rounded-xl px-3 py-2" x-model="selected.align">
                      <option>left</option>
                      <option>center</option>
                      <option>right</option>
                    </select>
                  </label>

                  <label>Ukuran (pt)
                    <input type="number" min="8"
                           class="mt-1 w-full border rounded-xl px-3 py-2"
                           x-model.number="selected.fontSize">
                  </label>
                </div>

                {{-- Font family per-blok teks --}}
                <div class="grid grid-cols-2 field-grid">
                  <label>Font family
                    <select class="mt-1 w-full border rounded-xl px-3 py-2"
                            x-model="selected.__fontSelect"
                            @change="selected.fontFamily = (selected.__fontSelect==='__custom__' ? (selected.__fontCustom||layout.font.family) : selected.__fontSelect)">
                      <option value="Poppins, sans-serif">Poppins (sans)</option>
                      <option value="Inter, sans-serif">Inter (sans)</option>
                      <option value="Roboto, sans-serif">Roboto (sans)</option>
                      <option value="Arial, Helvetica, sans-serif">Arial</option>
                      <option value="'Times New Roman', Times, serif">Times New Roman</option>
                      <option value="Georgia, serif">Georgia</option>
                      <option value="'Courier New', Courier, monospace">Courier New</option>
                      <option value="system-ui, -apple-system, Segoe UI, Roboto, Ubuntu, Cantarell, Noto Sans, Helvetica Neue, Arial, 'Apple Color Emoji', 'Segoe UI Emoji'">System UI</option>
                      <option value="__custom__">Custom…</option>
                    </select>
                  </label>
                  <label x-show="selected.__fontSelect==='__custom__'">
                    Custom CSS font-family
                    <input class="mt-1 w-full border rounded-xl px-3 py-2"
                           placeholder="e.g. 'Playfair Display', serif"
                           x-model="selected.__fontCustom"
                           @input="selected.fontFamily = selected.__fontCustom || layout.font.family">
                  </label>
                </div>

                {{-- Warna teks & border --}}
                <div class="grid grid-cols-2 field-grid">
                  <label>Warna Teks
                    <input type="color" class="mt-1 w-full border rounded-xl px-2 py-1" x-model="selected.color">
                  </label>
                  <label>Warna Border
                    <input type="color" class="mt-1 w-full border rounded-xl px-2 py-1" x-model="selected.borderColor">
                  </label>
                </div>

                <label>Bold
                  <input type="checkbox" class="ml-2" x-model="selected.bold">
                </label>
              </div>
            </template>

            {{-- SIGNATURE (TTD) --}}
            <template x-if="selected.type==='signature'">
              <div class="mt-3 space-y-3">
                <div class="grid grid-cols-2 field-grid">
                  <label>Role
                    <input class="mt-1 w-full border rounded-xl px-3 py-2" x-model="selected.role" placeholder="Disiapkan / Diperiksa / ...">
                  </label>
                  <label>Nama
                    <input class="mt-1 w-full border rounded-xl px-3 py-2" x-model="selected.name">
                  </label>
                  <label>Jabatan
                    <input class="mt-1 w-full border rounded-xl px-3 py-2" x-model="selected.position">
                  </label>
                  <label>Tanda Tangan (Teks, opsional)
                    <input class="mt-1 w-full border rounded-xl px-3 py-2" x-model="selected.signatureText" placeholder="tulis tanda tangan">
                  </label>

                  {{-- NEW: Align TTD --}}
                  <label>Align
                    <select class="mt-1 w-full border rounded-xl px-3 py-2" x-model="selected.align">
                      <option>left</option>
                      <option>center</option>
                      <option>right</option>
                    </select>
                  </label>
                </div>

                <div class="flex items-center gap-2">
                  <button type="button" class="px-3 py-1.5 border rounded-xl text-sm" @click="openPad(selected.id)">Gambar TTD</button>
                  <button type="button" class="px-3 py-1.5 border rounded-xl text-sm" @click="selected.src=''">Hapus Gambar</button>
                </div>

                <div class="grid grid-cols-2 field-grid">
                  <label>Font family (TTD)
                    <select class="mt-1 w-full border rounded-xl px-3 py-2"
                            x-model="selected.__sigFontSelect"
                            @change="
                              selected.fontFamily = (selected.__sigFontSelect==='__custom__'
                                ? (selected.__sigFontCustom||layout.font.family)
                                : selected.__sigFontSelect)
                            ">
                      <option value="Poppins, sans-serif">Poppins (sans)</option>
                      <option value="Inter, sans-serif">Inter (sans)</option>
                      <option value="Roboto, sans-serif">Roboto (sans)</option>
                      <option value="Arial, Helvetica, sans-serif">Arial</option>
                      <option value="'Times New Roman', Times, serif">Times New Roman</option>
                      <option value="Georgia, serif">Georgia</option>
                      <option value="'Courier New', Courier, monospace">Courier New</option>
                      <option value="system-ui, -apple-system, Segoe UI, Roboto, Ubuntu, Cantarell, Noto Sans, Helvetica Neue, Arial, 'Apple Color Emoji', 'Segoe UI Emoji'">System UI</option>
                      <option value="__custom__">Custom…</option>
                    </select>
                  </label>

                  <label x-show="selected.__sigFontSelect==='__custom__'">
                    Custom CSS font-family
                    <input class="mt-1 w-full border rounded-xl px-3 py-2"
                           placeholder="e.g. 'Playfair Display', serif"
                           x-model="selected.__sigFontCustom"
                           @input="selected.fontFamily = selected.__sigFontCustom || layout.font.family">
                  </label>

                  <label>Signature Text Size (px)
                    <input type="number" min="9" class="mt-1 w-full border rounded-xl px-3 py-2" x-model.number="selected.signatureFontSize">
                  </label>

                  <label>Role/Nama/Jabatan Size (px)
                    <input type="number" min="9" class="mt-1 w-full border rounded-xl px-3 py-2" x-model.number="selected.infoFontSize">
                  </label>
                </div>
              </div>
            </template>

            {{-- IMAGE (non-signature) --}}
            <template x-if="selected.type==='image'">
              <div class="mt-3 space-y-2">
                <label>Sumber Gambar (URL / data URL)
                  <input class="mt-1 w-full border rounded-xl px-3 py-2" x-model="selected.src" placeholder="/uploads/logo.png atau data:image/jpeg;base64,...">
                </label>
                <div class="grid grid-cols-2 field-grid">
                  <label>Warna Border
                    <input type="color" class="mt-1 w-full border rounded-xl px-2 py-1" x-model="selected.borderColor">
                  </label>
                </div>
              </div>
            </template>

            {{-- TABLE CELL --}}
            <template x-if="selected.type==='tableCell'">
              <div class="mt-3 space-y-2">
                <label>Label/Value
                  <input class="mt-1 w-full border rounded-xl px-3 py-2" x-model="selected.text" placeholder="Doc.No / (otomatis)">
                </label>
                <div class="grid grid-cols-2 field-grid">
                  <label>Bold <input type="checkbox" class="ml-2" x-model="selected.bold"></label>
                  <label>Warna Border
                    <input type="color" class="mt-1 w-full border rounded-xl px-2 py-1" x-model="selected.borderColor">
                  </label>
                </div>
              </div>
            </template>

            {{-- FOOTER --}}
            <template x-if="selected.type==='footer'">
              <div class="mt-3 space-y-2">
                <label>Teks Footer
                  <input class="mt-1 w-full border rounded-xl px-3 py-2" x-model="selected.text" placeholder="© Perusahaan 2025">
                </label>
                <label class="inline-flex items-center gap-2">
                  <input type="checkbox" x-model="selected.showPage">
                  <span>Tampilkan nomor halaman</span>
                </label>
                <div class="grid grid-cols-2 field-grid">
                  <label>Align
                    <select class="mt-1 w-full border rounded-xl px-3 py-2" x-model="selected.align">
                      <option>left</option>
                      <option>center</option>
                      <option>right</option>
                    </select>
                  </label>
                  <label>Ukuran font (px)
                    <input type="number" min="9" class="mt-1 w-full border rounded-xl px-3 py-2" x-model.number="selected.fontSize">
                  </label>
                </div>
                <div class="grid grid-cols-2 field-grid">
                  <label>Warna Teks
                    <input type="color" class="mt-1 w-full border rounded-xl px-2 py-1" x-model="selected.color">
                  </label>
                  <label>Warna Border
                    <input type="color" class="mt-1 w-full border rounded-xl px-2 py-1" x-model="selected.borderColor">
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

        {{-- Wrapper kanvas --}}
        <div class="relative bg-white rounded-b-xl shadow"
             :style="{ width: layout.page.width+'px', height: layout.page.height+'px', outline: pageBorder ? ('1px solid '+colors.pageBorder) : 'none' }"
             x-ref="page"
             @mousedown="pointerDown($event)"
             @mousemove.window="pointerMove($event)"
             @mouseup.window="pointerUp()"
             @mouseleave="pointerUp()"
             @keydown.window="handleKey($event)"
             tabindex="0">

          {{-- Margin guides --}}
          <div class="absolute inset-0 pointer-events-none" x-show="guides">
            <div class="absolute"
                 :style="{
                   top: layout.margins.top+'px',
                   left: layout.margins.left+'px',
                   width: (layout.page.width - layout.margins.left - layout.margins.right)+'px',
                   height:(layout.page.height - layout.margins.top - layout.margins.bottom)+'px',
                   outline:'1px dashed rgba(0,0,0,.06)'
                 }"></div>
          </div>

          {{-- SMART GUIDES OVERLAY --}}
          <template x-if="guides">
            <div class="absolute inset-0 pointer-events-none" x-ref="guidesOverlay">
              <!-- garis vertikal -->
              <div class="guide-line" x-show="gl.v.show"
                   :style="{left: gl.v.x+'px', top: 0, height: layout.page.height+'px', width:'1px', background: colors.guide}"></div>
              <!-- garis horizontal -->
              <div class="guide-line" x-show="gl.h.show"
                   :style="{top: gl.h.y+'px', left: 0, width: layout.page.width+'px', height:'1px', background: colors.guide}"></div>
              <!-- badge jarak vertikal -->
              <div class="guide-badge" x-show="gl.badgeV.show"
                   :style="{left: gl.badgeV.x+'px', top: gl.badgeV.y+'px'}"
                   x-text="gl.badgeV.text"></div>
              <!-- badge jarak horizontal -->
              <div class="guide-badge" x-show="gl.badgeH.show"
                   :style="{left: gl.badgeH.x+'px', top: gl.badgeH.y+'px'}"
                   x-text="gl.badgeH.text"></div>
            </div>
          </template>

          {{-- Blocks --}}
          <template x-for="blk in blocks" :key="blk.id">
            <div
              class="absolute group"
              :style="{
                top: blk.top+'px', left: blk.left+'px',
                width: blk.width+'px', height: blk.height+'px',
                zIndex: blk.z || 1
              }"
              :class="selectedId===blk.id ? 'ring-2 ring-sky-500' : 'ring-0'"
              @mousedown.stop="select(blk.id); startMove(blk, $event)"
              @dblclick="if(blk.type==='signature'){ openPad(blk.id) }">

              {{-- CONTENT: TEXT --}}
              <template x-if="blk.type==='text'">
                <div class="w-full h-full p-2 overflow-hidden"
                     :class="blk.border ? 'border rounded' : ''"
                     :style="{
                       borderColor: (blk.borderColor || '#e5e7eb'),
                       textAlign: blk.align,
                       fontWeight: blk.bold ? '700':'400',
                       fontSize: (blk.fontSize + 'pt'),
                       fontFamily: (blk.fontFamily ?? layout.font.family),
                       color: (blk.color || '#111')
                     }"
                     x-text="blk.text"></div>
              </template>

              {{-- CONTENT: IMAGE --}}
              <template x-if="blk.type==='image'">
                <div class="w-full h-full flex items-center justify-center bg-white"
                     :class="blk.border ? 'border rounded' : ''"
                     :style="{ borderColor: (blk.borderColor || '#e5e7eb') }">
                  <template x-if="blk.src"><img :src="blk.src" class="max-w-full max-h-full object-contain"></template>
                  <template x-if="!blk.src"><span class="text-xs text-gray-400">[Gambar]</span></template>
                </div>
              </template>

              {{-- CONTENT: TABLE CELL --}}
              <template x-if="blk.type==='tableCell'">
                <div class="w-full h-full px-2 py-1 overflow-hidden"
                     :class="(blk.border ?? true) ? 'border bg-gray-50/60' : 'bg-white'"
                     :style="{
                       borderColor: (blk.borderColor || '#e5e7eb'),
                       fontWeight: blk.bold ? '700':'400',
                       fontSize: (blk.fontSize ?? 12) + 'px',
                       display:'flex', alignItems:'center'
                     }"
                     x-text="blk.text || '—'"></div>
              </template>

              {{-- CONTENT: FOOTER --}}
              <template x-if="blk.type==='footer'">
                <div class="w-full h-full px-2 py-1 flex items-center justify-between bg-white"
                     :class="blk.border ? 'border' : ''"
                     :style="{ borderColor: (blk.borderColor || '#e5e7eb'), fontSize: (blk.fontSize||11)+'px', textAlign: blk.align || 'left', color: (blk.color || '#111') }">
                  <div class="truncate" x-text="blk.text || '© Perusahaan 2025'"></div>
                  <div class="text-xs text-gray-600" x-show="blk.showPage">Halaman 1 / N</div>
                </div>
              </template>

              {{-- CONTENT: SIGNATURE (TTD) --}}
              <template x-if="blk.type==='signature'">
                <div class="w-full h-full p-2 bg-white/90 backdrop-blur rounded"
                     :class="blk.border ? 'border' : ''"
                     :style="{
                        borderColor: (blk.borderColor || '#e5e7eb'),
                        fontFamily: (blk.fontFamily ?? layout.font.family),
                        textAlign: (blk.align || 'center')
                      }">
                  <div class="text-[11px] text-gray-600"
                       :style="{fontSize: (blk.infoFontSize)+'px'}"
                       x-text="blk.role || 'Role'"></div>

                  <div class="mt-1 w-full flex-1 border border-dashed rounded flex items-center"
                       :style="{
                         borderColor: (blk.borderColor || '#e5e7eb'),
                         justifyContent: (blk.align==='right' ? 'flex-end' : (blk.align==='left' ? 'flex-start' : 'center'))
                       }">
                    <template x-if="blk.src"><img :src="blk.src" class="max-h-full object-contain"></template>
                    <template x-if="!blk.src && blk.signatureText">
                      <span class="italic" :style="{fontSize: (blk.signatureFontSize)+'px'}" x-text="blk.signatureText"></span>
                    </template>
                    <template x-if="!blk.src && !blk.signatureText"><span class="text-[10px] text-gray-400"></span></template>
                  </div>

                  <div class="mt-1" :style="{fontSize: (blk.infoFontSize)+'px'}">
                    <div class="text-xs font-medium truncate" x-text="blk.name || 'Nama'"></div>
                    <div class="text-[11px] text-gray-600 truncate" x-text="blk.position || 'Jabatan'"></div>
                  </div>
                </div>
              </template>

              {{-- Resize handle --}}
              <div class="absolute right-0 bottom-0 translate-x-1/2 translate-y-1/2 w-3 h-3 bg-sky-500 rounded-sm cursor-se-resize opacity-0 group-hover:opacity-100"
                   @mousedown.stop="startResize(blk, $event)"></div>
            </div>
          </template>
        </div>

        <div class="text-[11px] text-gray-500 mt-2">
          Drag untuk memindah blok. Tarik handle biru di pojok kanan-bawah untuk resize. Aktifkan “Snap” untuk menempel ke grid. Shortcuts: Ctrl/Cmd+C, Ctrl/Cmd+V, Ctrl/Cmd+D, Delete/Backspace.
        </div>
      </div>
    </div>

    <div class="px-4 py-3 border-t flex items-center justify-end gap-3">
      <a href="{{ route('admin.document_templates.index') }}" class="px-4 py-2 rounded-xl border text-[#1D1C1A]">Batal</a>
      <button class="px-4 py-2 rounded-xl bg-[#7A2C2F] text-white hover:opacity-90">Simpan</button>
    </div>
  </form>

  {{-- DRAW PAD MODAL --}}
  <div x-show="pad.open" x-cloak @keydown.escape.window="closePad()" class="fixed inset-0 z-50 flex items-center justify-center">
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
function docDesigner() {
  return {
    // ====== STATE ======
    layout: {
      page: { width: 794, height: 1123 },
      margins: { top: 30, right: 25, bottom: 25, left: 25 },
      font: { size: 11, family: 'Poppins, sans-serif' }
    },
    colors: {
      guide: '#22c55e',       // hijau default
      pageBorder: '#e5e7eb'   // gray-200 default
    },
    blocks: [],
    selectedId: null,
    moving: { active: false, id: null, ox: 0, oy: 0 },
    resizing: { active: false, id: null, startW: 0, startH: 0, startX: 0, startY: 0 },
    snap: { enabled: true, grid: 8 },
    guides: true,
    pageBorder: true,

    // smart guides state
    gl: {
      v: { show:false, x:0 },
      h: { show:false, y:0 },
      badgeV: { show:false, x:0, y:0, text:'' },
      badgeH: { show:false, x:0, y:0, text:'' },
    },

    // clipboard untuk copy/paste
    clipboard: null,

    // preview foto template (photo_path)
    photo: { url: '' },

    // PAD
    pad: { open: false, id: null, ctx: null, drawing: false, stroke: 3, last: { x: 0, y: 0 } },

    // ====== INIT ======
    init() {
      this.blocks = [
        // text default numeric font size (editable langsung)
        this.mkText('Judul Dokumen', this.layout.margins.top + 8,  this.layout.margins.left + 160, 400, 40, 'center', true, this.layout.font.size, null, false),
        this.mkImage('',            this.layout.margins.top + 4,  this.layout.margins.left + 0,   120, 48, true),
        this.mkTableCell('Doc.No',  this.layout.margins.top + 60, this.layout.margins.left + 0,   160, 32, true),
        this.mkTableCell('(otomatis)', this.layout.margins.top + 60, this.layout.margins.left + 160, 420, 32, false),
        this.mkFooter('© Perusahaan 2025',
          this.layout.page.height - this.layout.margins.bottom - 36,
          this.layout.margins.left,
          this.layout.page.width - this.layout.margins.left - this.layout.margins.right,
          36, true, 'left', 11, false),
        // Signature awal: align default center
        this.mkSignature('Disiapkan', '', '', this.layout.page.height - 300, this.layout.margins.left + 0,   160, 70, false, null, 11, 16, 'center'),
        this.mkSignature('Diperiksa', '', '', this.layout.page.height - 300, this.layout.margins.left + 180, 160, 70, false, null, 11, 16, 'center'),
      ];
      // Init helper untuk dropdown font
      this.blocks.forEach(b => {
        if (b.type==='text') {
          b.__fontSelect = b.fontFamily ?? this.layout.font.family;
          b.__fontCustom = '';
        }
        if (b.type==='signature') {
          b.__sigFontSelect = b.fontFamily ?? this.layout.font.family;
          b.__sigFontCustom = '';
        }
      });
    },

    // ====== BUILDERS ======
    uid() { return Math.random().toString(36).slice(2, 10); },

    mkText(text, top, left, w, h, align='left', bold=false, fontSize=11, fontFamily=null, border=false) {
      return {
        id: this.uid(), type: 'text', text, align, bold,
        fontSize, fontFamily, border, top, left, width: w, height: h, z: 10,
        color: '#111', borderColor: '#e5e7eb',
        __fontSelect: fontFamily ?? this.layout?.font?.family ?? 'Poppins, sans-serif',
        __fontCustom: ''
      };
    },
    mkImage(src, top, left, w, h, border=true) {
      return { id: this.uid(), type: 'image', src, border, top, left, width: w, height: h, z: 10, borderColor:'#e5e7eb' };
    },
    mkTableCell(text, top, left, w, h, bold=false, border=true) {
      return { id: this.uid(), type: 'tableCell', text, bold, border, top, left, width: w, height: h, z: 10, borderColor:'#e5e7eb' };
    },
    mkFooter(text, top, left, w, h, border=true, align='left', fontSize=11, showPage=false) {
      return { id: this.uid(), type:'footer', text, top, left, width:w, height:h, border, align, fontSize, showPage, z:10, color:'#111', borderColor:'#e5e7eb' };
    },
    // NEW: tambah param align (default 'center')
    mkSignature(role, name, position, top, left, w, h, border=false, fontFamily=null, infoFontSize=11, signatureFontSize=16, align='center') {
      return {
        id: this.uid(), type: 'signature',
        role, name, position,
        signatureText: '', src: '',
        border, top, left, width: w, height: h, z: 10,
        fontFamily,       // font untuk seluruh teks di blok TTD
        infoFontSize,     // px untuk Role/Nama/Jabatan
        signatureFontSize,// px untuk teks tanda tangan
        align,            // left / center / right
        borderColor:'#e5e7eb',
        __sigFontSelect: fontFamily ?? this.layout?.font?.family ?? 'Poppins, sans-serif',
        __sigFontCustom: ''
      };
    },

    // ====== ACTIONS ======
    addText(t='Teks') {
      const b = this.mkText(t, 120, 60, 240, 40, 'left', false, this.layout.font.size, this.layout.font.family);
      b.__fontSelect = this.layout.font.family;
      this.blocks.push(b);
    },
    addImageFromFile(e) {
      const f = e.target.files?.[0]; if (!f) return;
      const url = URL.createObjectURL(f);
      this.blocks.push(this.mkImage(url, 120, 320, 160, 80, true));
      e.target.value = '';
    },
    addHeaderRow() {
      const y = this.layout.margins.top + 60 + (this.blocks.filter(b => b.type === 'tableCell').length / 2) * 34;
      this.blocks.push(this.mkTableCell('Label', y, this.layout.margins.left + 0, 160, 32, true));
      this.blocks.push(this.mkTableCell('(otomatis)', y, this.layout.margins.left + 160, 420, 32, false));
    },
    openTableBuilder() {
      const r = Number(prompt('Jumlah baris?', '2')); if (!r || r < 1) return;
      const c = Number(prompt('Jumlah kolom?', '3')); if (!c || c < 1) return;
      const startY = this.layout.margins.top + 100;
      const startX = this.layout.margins.left;
      const cellH = 30, cellW = 140, gap = 0;
      for (let i = 0; i < r; i++) {
        for (let j = 0; j < c; j++) {
          const y = startY + i * (cellH + gap);
          const x = startX + j * (cellW + gap);
          const label = i === 0 ? `Header ${j+1}` : '';
          this.blocks.push(this.mkTableCell(label, y, x, cellW, cellH, i === 0, true));
        }
      }
    },
    addFooter() {
      this.blocks.push(this.mkFooter('© Perusahaan 2025',
        this.layout.page.height - this.layout.margins.bottom - 36,
        this.layout.margins.left,
        this.layout.page.width - this.layout.margins.left - this.layout.margins.right,
        36, true, 'left', 11));
    },
    addSignature() {
      const b = this.mkSignature('Signer', '', '', this.layout.page.height - 260, this.layout.margins.left + 60, 160, 70, false, this.layout.font.family, 11, 16, 'center');
      this.blocks.push(b);
    },

    // foto template (preview + ke kanvas)
    onPhotoChange(e) {
      const f = e.target.files?.[0];
      if (!f) { this.photo.url = ''; return; }
      this.photo.url = URL.createObjectURL(f);
    },
    clearPhoto() {
      this.photo.url = '';
      if (this.$refs.photoInput) this.$refs.photoInput.value = null;
    },
    addPhotoToCanvas() {
      if (!this.photo.url) return;
      this.blocks.push(this.mkImage(this.photo.url, this.layout.margins.top + 4, this.layout.margins.left, 120, 48, true));
    },

    // selection helpers
    get selected() { return this.blocks.find(b => b.id === this.selectedId) || null; },
    select(id) { this.selectedId = id; },
    deleteSelected() {
      if (this.selectedId === null) return;
      const i = this.blocks.findIndex(b => b.id === this.selectedId);
      if (i >= 0) this.blocks.splice(i, 1);
      this.selectedId = null;
    },

    // copy/duplicate/paste
    deepClone(obj){ return JSON.parse(JSON.stringify(obj)); },
    copySelected() {
      const b = this.selected; if (!b) return;
      this.clipboard = this.deepClone(b);
    },
    pasteClipboard() {
      if (!this.clipboard) return;
      const b = this.deepClone(this.clipboard);
      b.id = this.uid();
      b.left = Math.min(b.left + 8, this.layout.page.width - b.width);
      b.top  = Math.min(b.top + 8,  this.layout.page.height - b.height);
      this.blocks.push(b);
      this.selectedId = b.id;
    },
    duplicateSelected() {
      this.copySelected();
      this.pasteClipboard();
    },

    // positioning helpers
    centerSelectedH() {
      const b = this.selected; if (!b) return;
      b.left = Math.round((this.layout.page.width - b.width) / 2);
      if (this.snap.enabled) b.left = Math.round(b.left / this.snap.grid) * this.snap.grid;
    },
    centerSelectedV() {
      const b = this.selected; if (!b) return;
      b.top = Math.round((this.layout.page.height - b.height) / 2);
      if (this.snap.enabled) b.top = Math.round(b.top / this.snap.grid) * this.snap.grid;
    },
    centerSelectedBoth() { this.centerSelectedH(); this.centerSelectedV(); },
    toggleBorderSelected() { const b = this.selected; if (!b) return; b.border = !b.border; },
    bringToFront() {
      const b = this.selected; if (!b) return;
      const maxZ = Math.max(...this.blocks.map(x => x.z || 1));
      b.z = maxZ + 1;
    },
    sendToBack() {
      const b = this.selected; if (!b) return;
      const minZ = Math.min(...this.blocks.map(x => x.z || 1));
      b.z = minZ - 1;
    },

    // hotkeys
    handleKey(e) {
      const meta = e.ctrlKey || e.metaKey;
      if (meta && e.key.toLowerCase()==='c') { e.preventDefault(); this.copySelected(); }
      if (meta && e.key.toLowerCase()==='v') { e.preventDefault(); this.pasteClipboard(); }
      if (meta && e.key.toLowerCase()==='d') { e.preventDefault(); this.duplicateSelected(); }
    },

    // drag & resize
    pointerDown() {},
    startMove(blk, evt) {
      this.select(blk.id);
      this.moving.active = true; this.moving.id = blk.id;
      const r = this.$refs.page.getBoundingClientRect();
      this.moving.ox = (evt.clientX - r.left) - blk.left;
      this.moving.oy = (evt.clientY - r.top) - blk.top;
      // reset guides
      this.hideGuides();
    },
    pointerMove(evt) {
      if (this.moving.active) {
        const blk = this.blocks.find(b => b.id === this.moving.id); if (!blk) return;
        const r = this.$refs.page.getBoundingClientRect();
        let x = (evt.clientX - r.left) - this.moving.ox,
            y = (evt.clientY - r.top)  - this.moving.oy;
        x = Math.max(0, Math.min(x, this.layout.page.width  - blk.width));
        y = Math.max(0, Math.min(y, this.layout.page.height - blk.height));
        if (this.snap.enabled) {
          x = Math.round(x / this.snap.grid) * this.snap.grid;
          y = Math.round(y / this.snap.grid) * this.snap.grid;
        }
        blk.left = x; blk.top = y;

        if (this.guides) this.updateGuidesWhileMoving(blk);
        return;
      }
      if (this.resizing.active) {
        const blk = this.blocks.find(b => b.id === this.resizing.id); if (!blk) return;
        const dx = evt.clientX - this.resizing.startX,
              dy = evt.clientY - this.resizing.startY;
        let w = Math.max(24, this.resizing.startW + dx),
            h = Math.max(24, this.resizing.startH + dy);
        if (this.snap.enabled) {
          w = Math.round(w / this.snap.grid) * this.snap.grid;
          h = Math.round(h / this.snap.grid) * this.snap.grid;
        }
        blk.width = w; blk.height = h;
        // saat resize kita reset guides (opsional bisa dihitung juga)
        this.hideGuides();
      }
    },
    pointerUp() {
      this.moving.active = false; this.moving.id = null;
      this.resizing.active = false; this.resizing.id = null;
      this.hideGuides();
    },
    startResize(blk, evt) {
      this.select(blk.id);
      this.resizing.active = true; this.resizing.id = blk.id;
      this.resizing.startW = blk.width; this.resizing.startH = blk.height;
      this.resizing.startX = evt.clientX; this.resizing.startY = evt.clientY;
    },

    // SMART GUIDES logic
    hideGuides(){
      this.gl.v.show = this.gl.h.show = false;
      this.gl.badgeV.show = this.gl.badgeH.show = false;
    },
    updateGuidesWhileMoving(curr){
      const others = this.blocks.filter(b => b.id !== curr.id);
      const centerX = curr.left + curr.width/2;
      const centerY = curr.top + curr.height/2;

      // nearest vertical align to other centers/edges
      let vMatch = {dist: Infinity, x: 0};
      let hMatch = {dist: Infinity, y: 0};

      // distance badges (vertical gap above/below with horizontal overlap)
      let vGap = {dist: Infinity, midX: centerX, midY: 0, text:'', show:false};
      // horizontal gap (left/right with vertical overlap)
      let hGap = {dist: Infinity, midY: centerY, midX: 0, text:'', show:false};

      const overlap = (a1,a2,b1,b2) => Math.max(a1,b1) < Math.min(a2,b2);

      others.forEach(o => {
        const oCenterX = o.left + o.width/2;
        const oCenterY = o.top  + o.height/2;

        // vertical guide candidates (x positions)
        const xCandidates = [o.left, oCenterX, o.left + o.width];
        xCandidates.forEach(xc => {
          const d = Math.abs(xc - centerX);
          if (d < vMatch.dist) vMatch = {dist: d, x: xc};
        });

        // horizontal guide candidates (y positions)
        const yCandidates = [o.top, oCenterY, o.top + o.height];
        yCandidates.forEach(yc => {
          const d = Math.abs(yc - centerY);
          if (d < hMatch.dist) hMatch = {dist: d, y: yc};
        });

        // vertical gap badge (atas-bawah)
        if (overlap(curr.left, curr.left+curr.width, o.left, o.left+o.width)) {
          // o di atas curr
          if (o.top + o.height <= curr.top) {
            const gap = curr.top - (o.top + o.height);
            if (gap < vGap.dist) {
              vGap = {dist: gap, midX: centerX, midY: (curr.top + (o.top+o.height))/2, text: gap + ' px', show:true};
            }
          }
          // o di bawah curr
          if (curr.top + curr.height <= o.top) {
            const gap = o.top - (curr.top + curr.height);
            if (gap < vGap.dist) {
              vGap = {dist: gap, midX: centerX, midY: (o.top + (curr.top+curr.height))/2, text: gap + ' px', show:true};
            }
          }
        }

        // horizontal gap badge (kiri-kanan)
        if (overlap(curr.top, curr.top+curr.height, o.top, o.top+o.height)) {
          // o di kiri curr
          if (o.left + o.width <= curr.left) {
            const gap = curr.left - (o.left + o.width);
            if (gap < hGap.dist) {
              hGap = {dist: gap, midY: centerY, midX: (curr.left + (o.left+o.width))/2, text: gap + ' px', show:true};
            }
          }
          // o di kanan curr
          if (curr.left + curr.width <= o.left) {
            const gap = o.left - (curr.left + curr.width);
            if (gap < hGap.dist) {
              hGap = {dist: gap, midY: centerY, midX: (o.left + (curr.left+curr.width))/2, text: gap + ' px', show:true};
            }
          }
        }
      });

      // snap threshold untuk guides (terasa "lengket")
      const th = 6;

      // apply vertical line (center-aligned)
      if (vMatch.dist <= th) {
        this.gl.v.show = true; this.gl.v.x = Math.round(vMatch.x);
      } else {
        this.gl.v.show = false;
      }

      // apply horizontal line (center-aligned)
      if (hMatch.dist <= th) {
        this.gl.h.show = true; this.gl.h.y = Math.round(hMatch.y);
      } else {
        this.gl.h.show = false;
      }

      // badges
      if (vGap.show) {
        this.gl.badgeV.show = true;
        this.gl.badgeV.x = Math.round(vGap.midX);
        this.gl.badgeV.y = Math.round(vGap.midY);
        this.gl.badgeV.text = vGap.text;
      } else {
        this.gl.badgeV.show = false;
      }

      if (hGap.show) {
        this.gl.badgeH.show = true;
        this.gl.badgeH.x = Math.round(hGap.midX);
        this.gl.badgeH.y = Math.round(hGap.midY);
        this.gl.badgeH.text = hGap.text;
      } else {
        this.gl.badgeH.show = false;
      }
    },

    // PAD
    openPad(id) {
      this.pad.open = true; this.pad.id = id;
      if (this.$refs.sigCanvas) {
        this.$refs.sigCanvas.onmousedown = this.$refs.sigCanvas.onmousemove = this.$refs.sigCanvas.onmouseup = this.$refs.sigCanvas.onmouseleave = null;
        this.$refs.sigCanvas.ontouchstart = this.$refs.sigCanvas.ontouchmove = this.$refs.sigCanvas.ontouchend = null;
      }
      this.$nextTick(() => {
        const c = this.$refs.sigCanvas, ctx = c.getContext('2d');
        ctx.lineCap = 'round'; ctx.lineJoin = 'round';
        ctx.strokeStyle = '#111'; ctx.lineWidth = this.pad.stroke;
        this.pad.ctx = ctx;
        ctx.fillStyle = '#fff'; ctx.fillRect(0, 0, c.width, c.height);
        const getPos = (e) => {
          const r = c.getBoundingClientRect(); const t = e.touches ? e.touches[0] : e;
          return { x: t.clientX - r.left, y: t.clientY - r.top };
        };
        const down = (e) => { e.preventDefault(); this.pad.drawing = true; this.pad.last = getPos(e); };
        const mv   = (e) => {
          if (!this.pad.drawing) return; e.preventDefault();
          const p = getPos(e); ctx.lineWidth = this.pad.stroke;
          ctx.beginPath(); ctx.moveTo(this.pad.last.x, this.pad.last.y); ctx.lineTo(p.x, p.y); ctx.stroke();
          this.pad.last = p;
        };
        const up = () => { this.pad.drawing = false; };
        c.onmousedown = down; c.onmousemove = mv; c.onmouseup = up; c.onmouseleave = up;
        c.ontouchstart = down; c.ontouchmove = mv; c.ontouchend = up;
      });
    },
    closePad() { this.pad.open = false; },
    padClear() {
      const c = this.$refs.sigCanvas, ctx = this.pad.ctx;
      ctx.fillStyle = '#fff'; ctx.fillRect(0, 0, c.width, c.height);
    },
    padSave() {
      const dataURL = this.$refs.sigCanvas.toDataURL('image/jpeg', 0.7);
      const blk = this.blocks.find(b => b.id === this.pad.id);
      if (blk && blk.type === 'signature') { blk.src = dataURL; }
      this.closePad();
    },

    // ====== SUBMIT ======
    beforeSubmit() {
      const header = {
        mode: 'absolute',
        items: this.blocks
          .filter(b => ['text','image','tableCell'].includes(b.type))
          .map(({ type, top, left, width, height, text, src, bold, fontSize, fontFamily, align, border, color, borderColor }) => ({
            type, top, left, width, height,
            text: text || '',
            src: src || '',
            bold: !!bold,
            font_size: fontSize,
            font_family: (type==='text' ? (fontFamily ?? this.layout.font.family) : undefined),
            align: align || 'left',
            border: !!border,
            color: color || '#111',
            border_color: borderColor || '#e5e7eb',
          }))
      };
      const footer = {
        items: this.blocks.filter(b => b.type === 'footer')
          .map(({ text, showPage, align, fontSize, top, left, width, height, border, color, borderColor }) => ({
            text: text || '',
            show_page_number: !!showPage,
            align: align || 'left',
            font_size: fontSize || 11,
            top, left, width, height,
            border: !!border,
            color: color || '#111',
            border_color: borderColor || '#e5e7eb',
          }))
      };
      const signature = {
        mode: 'absolute',
        rows: this.blocks.filter(b => b.type === 'signature')
          .map(({ role, name, position, signatureText, src, top, left, width, height, border, fontFamily, infoFontSize, signatureFontSize, borderColor, align }) => ({
            role: role || '',
            name: name || '',
            position_title: position || '',
            signature_text: signatureText || '',
            image_path: src || '',
            top, left, width, height,
            border: !!border,
            font_family: (fontFamily ?? this.layout.font.family),
            info_font_size: infoFontSize,
            signature_font_size: signatureFontSize,
            border_color: borderColor || '#e5e7eb',
            align: align || 'center', // NEW: kirim align ke backend
          }))
      };

      this.$refs.blocksInput.value   = JSON.stringify(this.blocks);
      this.$refs.layoutInput.value   = JSON.stringify(this.layout);
      this.$refs.headerInput.value   = JSON.stringify(header);
      this.$refs.footerInput.value   = JSON.stringify(footer);
      this.$refs.signatureInput.value= JSON.stringify(signature);
      this.$refs.form.submit();
    },
  }
}
</script>
@endpush
