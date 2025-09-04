{{-- resources/views/admin/document_templates/show.blade.php --}}
@extends('layouts.app')

@section('title','Preview Template')

@push('styles')
<style>[x-cloak]{display:none!important}</style>
@endpush

@section('content')
@php
  use Illuminate\Support\Str;
  use Illuminate\Support\Facades\Storage;

  // Foto template → normalize jadi URL publik
  $raw = $template->photo_path ?? null;
  $photoUrl = null;
  if ($raw) {
    if (Str::startsWith($raw, ['http://','https://','/storage/'])) {
      $photoUrl = $raw;
    } else {
      $photoUrl = Storage::url(ltrim($raw, '/')); // "/storage/xxx"
    }
  }

  // Karena controller SUDAH kirim $layout dan $blocks sebagai array,
  // JANGAN json_decode lagi.
  // Tapi tetap kasih fallback bila variabel tidak dikirim.
  $layout = isset($layout) && is_array($layout) ? $layout : [
    'page'    => ['width' => 794, 'height' => 1123],
    'margins' => ['top' => 30, 'right' => 25, 'bottom' => 25, 'left' => 25],
    'font'    => ['size' => 11, 'family' => 'Poppins, sans-serif'],
  ];
  $layout['page']    = $layout['page']    ?? ['width'=>794,'height'=>1123];
  $layout['margins'] = $layout['margins'] ?? ['top'=>30,'right'=>25,'bottom'=>25,'left'=>25];
  $layout['font']    = $layout['font']    ?? ['size'=>11,'family'=>'Poppins, sans-serif'];

  $blocks = isset($blocks) && is_array($blocks) ? $blocks : [];

  // (opsional) kalau mau tetap punya variabel ini, kosongkan saja
  $header   = [];
  $footer   = [];
  $signConf = [];

  $defFontFamily = $layout['font']['family'] ?? 'Poppins, sans-serif';
@endphp


<div
  x-data="previewer({
    layout: @js($layout),
    blocks: @js($blocks),
    photoUrl: @js($photoUrl),
  })"
  x-init="init()"
  class="max-w-6xl mx-auto p-6 space-y-6 select-none"
>
  {{-- HEADER --}}
  <div class="flex items-center justify-between">
    <div>
      <h1 class="text-xl font-semibold text-[#1D1C1A]">Preview Template</h1>
      <p class="text-sm text-gray-600">#{{ $template->id }} — {{ $template->name }}</p>
    </div>
    <a href="{{ route('admin.document_templates.index') }}" class="px-4 py-2 rounded-xl border text-[#1D1C1A]">← Kembali</a>
  </div>

  {{-- BAR TOOLS (read-only helper) --}}
  <div class="bg-white border rounded-xl px-4 py-3 flex flex-wrap items-center gap-3">
    <div class="text-sm">Guides:</div>
    <label class="inline-flex items-center gap-2 text-sm">
      <input type="checkbox" x-model="guides"> Show
    </label>
    <div class="flex items-center gap-2 text-sm">
      <span>Guide Color</span>
      <input type="color" x-model="colors.guide" class="w-8 h-6 p-0 border rounded">
    </div>
    <div class="h-5 w-px bg-gray-200 mx-2"></div>
    <label class="inline-flex items-center gap-2 text-sm">
      <input type="checkbox" x-model="pageBorder"> Page Border
    </label>
    <div class="flex items-center gap-2 text-sm">
      <span>Page Border</span>
      <input type="color" x-model="colors.pageBorder" class="w-8 h-6 p-0 border rounded">
    </div>

    <div class="ml-auto text-sm text-gray-600">
      Font default: <span class="font-medium" x-text="layout.font.family"></span>, size <span class="font-medium" x-text="layout.font.size"></span> pt
    </div>
  </div>

  {{-- KANVAS --}}
  <div class="p-4 bg-transparent">
    <div class="mb-2 text-sm text-gray-600">
      Kanvas — <span x-text="layout.page.width"></span>×<span x-text="layout.page.height"></span> px
    </div>

    <div class="relative bg-white rounded-b-xl shadow overflow-hidden"
         :style="{ width: layout.page.width+'px', height: layout.page.height+'px', outline: pageBorder ? ('1px solid '+colors.pageBorder) : 'none' }"
         x-ref="page"
         tabindex="-1">

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

      {{-- Render semua blok --}}
      <template x-for="blk in blocks" :key="blk.id">
        <div class="absolute"
             :style="{
               top: blk.top+'px',
               left: blk.left+'px',
               width: blk.width+'px',
               height: blk.height+'px',
               zIndex: blk.z || 1
             }">

          {{-- TEXT --}}
          <template x-if="blk.type==='text'">
            <div class="w-full h-full p-2 overflow-hidden"
                 :class="blk.border ? 'border rounded' : ''"
                 :style="{
                   borderColor: (blk.borderColor || '#e5e7eb'),
                   textAlign: blk.align || 'left',
                   fontWeight: blk.bold ? '700':'400',
                   fontSize: (blk.fontSize ? blk.fontSize : (layout.font.size || 11)) + 'pt',
                   fontFamily: (blk.fontFamily || layout.font.family || 'Poppins, sans-serif'),
                   color: (blk.color || '#111')
                 }"
                 x-text="blk.text || ''"></div>
          </template>

          {{-- IMAGE --}}
          <template x-if="blk.type==='image'">
            <div class="w-full h-full flex items-center justify-center bg-white"
                 :class="blk.border ? 'border rounded' : ''"
                 :style="{ borderColor: (blk.borderColor || '#e5e7eb') }">
              <template x-if="blk.src"><img :src="blk.src" class="max-w-full max-h-full object-contain" loading="lazy"></template>
              <template x-if="!blk.src"><span class="text-xs text-gray-400">[Gambar]</span></template>
            </div>
          </template>

          {{-- TABLE CELL --}}
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

          {{-- FOOTER --}}
          <template x-if="blk.type==='footer'">
            <div class="w-full h-full px-2 py-1 flex items-center justify-between bg-white"
                 :class="blk.border ? 'border' : ''"
                 :style="{
                   borderColor: (blk.borderColor || '#e5e7eb'),
                   fontSize: (blk.fontSize||11)+'px',
                   textAlign: blk.align || 'left',
                   color: (blk.color || '#111')
                 }">
              <div class="truncate" x-text="blk.text || '© Perusahaan 2025'"></div>
              <div class="text-xs text-gray-600" x-show="blk.showPage">Halaman 1 / N</div>
            </div>
          </template>

          {{-- SIGNATURE (TTD) --}}
          <template x-if="blk.type==='signature'">
            <div class="w-full h-full p-2 bg-white/90 backdrop-blur rounded"
                 :class="blk.border ? 'border' : ''"
                 :style="{
                   borderColor: (blk.borderColor || '#e5e7eb'),
                   fontFamily: (blk.fontFamily || layout.font.family || 'Poppins, sans-serif'),
                   textAlign: (blk.align || 'center')
                 }">
              <div class="text-[11px] text-gray-600"
                   :style="{fontSize: (blk.infoFontSize ?? 11)+'px'}"
                   x-text="blk.role || 'Role'"></div>

              <div class="mt-1 w-full flex-1 border border-dashed rounded flex items-center"
                   :style="{
                      borderColor: (blk.borderColor || '#e5e7eb'),
                      justifyContent: (blk.align==='right' ? 'flex-end' : (blk.align==='left' ? 'flex-start' : 'center'))
                   }">
                <template x-if="blk.src">
                  <img :src="blk.src" class="max-h-full object-contain" loading="lazy">
                </template>
                <template x-if="!blk.src && blk.signatureText">
                  <span class="italic"
                        :style="{fontSize: (blk.signatureFontSize ?? 16)+'px'}"
                        x-text="blk.signatureText"></span>
                </template>
                <template x-if="!blk.src && !blk.signatureText">
                  <span class="text-[10px] text-gray-400"></span>
                </template>
              </div>

              <div class="mt-1" :style="{fontSize: (blk.infoFontSize ?? 11)+'px'}">
                <div class="text-xs font-medium truncate" x-text="blk.name || 'Nama'"></div>
                <div class="text-[11px] text-gray-600 truncate" x-text="blk.position || 'Jabatan'"></div>
              </div>
            </div>
          </template>
        </div>
      </template>
    </div>

    <div class="text-[11px] text-gray-500 mt-2">
      Ini adalah tampilan baca-saja (preview). Aktifkan “Guides” untuk mengecek jarak & margin. Border halaman bisa dinyalakan untuk memastikan ukuran.
    </div>
  </div>
</div>
@endsection

@push('scripts')
<script>
function previewer(passed) {
  return {
    layout: passed.layout || {
      page: { width: 794, height: 1123 },
      margins: { top: 30, right: 25, bottom: 25, left: 25 },
      font: { size: 11, family: 'Poppins, sans-serif' }
    },
    blocks: Array.isArray(passed.blocks) ? passed.blocks : [],
    photoUrl: passed.photoUrl || '',
    guides: true,
    pageBorder: true,
    colors: { guide: '#22c55e', pageBorder: '#e5e7eb' },

    init() {
      // PATCH: pastikan setiap blok punya properti minimum agar aman saat render
      this.blocks = (this.blocks || []).map(b => {
        const blk = Object.assign({
          id: cryptoRandomId(),
          top: 0, left: 0, width: 80, height: 24, z: 1, border: false,
          borderColor: '#e5e7eb'
        }, b || {});

        // Normalisasi jenis blok
        if (blk.type === 'text') {
          blk.align      = blk.align ?? 'left';
          blk.bold       = !!blk.bold;
          blk.fontSize   = (blk.fontSize ?? (this.layout?.font?.size ?? 11));
          blk.fontFamily = blk.fontFamily ?? (this.layout?.font?.family ?? 'Poppins, sans-serif');
          blk.color      = blk.color || '#111';
        }
        if (blk.type === 'tableCell') {
          blk.fontSize = blk.fontSize ?? 12;
        }
        if (blk.type === 'footer') {
          blk.align = blk.align ?? 'left';
          blk.fontSize = blk.fontSize ?? 11;
          blk.color = blk.color || '#111';
          blk.showPage = !!blk.showPage;
        }
        if (blk.type === 'signature') {
          blk.align = blk.align ?? 'center';                     // penting: default center
          blk.fontFamily = blk.fontFamily ?? (this.layout?.font?.family ?? 'Poppins, sans-serif');
          blk.infoFontSize = blk.infoFontSize ?? 11;
          blk.signatureFontSize = blk.signatureFontSize ?? 16;
        }
        return blk;
      });
    },
  }
}

// Simple random id jika blok dari DB belum punya id
function cryptoRandomId() {
  try {
    return ([1e7]+-1e3+-4e3+-8e3+-1e11)
      .replace(/[018]/g, c=>(c ^ crypto.getRandomValues(new Uint8Array(1))[0] & 15 >> c/4).toString(16));
  } catch {
    return Math.random().toString(36).slice(2,10);
  }
}
</script>
@endpush
