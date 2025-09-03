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

  // Normalisasi URL foto dari kolom photo_path:
  // - Jika sudah http(s) atau /storage/... → pakai apa adanya
  // - Jika relatif (mis. "templates/foo.jpg") → jadikan URL publik via Storage::url()
  $raw = $template->photo_path ?? null;
  $photoUrl = null;
  if ($raw) {
      if (Str::startsWith($raw, ['http://','https://','/storage/'])) {
          $photoUrl = $raw;
      } else {
          $photoUrl = Storage::url(ltrim($raw, '/')); // -> "/storage/xxx"
      }
  }
@endphp

{{-- Inject data sebagai JSON murni --}}
<script type="application/json" id="doc-template-json">
{!! json_encode([
    'name'     => $name ?? ($template->name ?? ''),
    'layout'   => $layout ?? [
        'page'    => ['width'=>794,'height'=>1123],
        'margins' => ['top'=>30,'right'=>25,'bottom'=>25,'left'=>25],
        'font'    => ['size'=>11],
    ],
    'blocks'   => $blocks ?? [],
    'photoUrl' => $photoUrl, // ← kirim URL foto ke Alpine
], JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES) !!}
</script>

<div
  x-data="loadDocTemplate('#doc-template-json')"
  x-init="init()"
  x-cloak
  class="max-w-6xl mx-auto p-6 space-y-4"
>
  {{-- Header --}}
  <div class="flex items-center justify-between">
    <div>
      <h1 class="text-xl font-semibold" x-text="name"></h1>
      <div class="text-sm text-gray-500">
        ID #{{ $template->id }} · Updated {{ optional($template->updated_at)->format('d M Y H:i') }}
      </div>
    </div>
    <a href="{{ route('admin.document_templates.index') }}" class="px-4 py-2 rounded-xl border">← Kembali</a>
  </div>

  {{-- Foto Template (jika ada) --}}
  <template x-if="photoUrl">
    <div class="bg-white border rounded-xl p-3 flex items-center gap-3">
      <div class="text-sm font-medium">Foto Template:</div>
      <img :src="photoUrl" alt="Foto Template" class="h-20 w-auto rounded border bg-white">
      <div class="text-xs text-gray-500">Disimpan pada kolom <code>photo_path</code>.</div>
    </div>
  </template>

  {{-- Canvas --}}
  <div>
    <div class="bg-[#1D1C1A] text-white px-2 py-2 text-sm rounded-t-xl">
      Preview — <span x-text="layout.page.width"></span>×<span x-text="layout.page.height"></span> px
    </div>

    <div class="relative bg-white border rounded-b-xl shadow"
         :style="{ width: layout.page.width + 'px', height: layout.page.height + 'px' }"
         x-ref="page">

      {{-- Margin Guides --}}
      <div class="absolute inset-0 pointer-events-none">
        <div class="absolute"
             :style="{
               top:    layout.margins.top + 'px',
               left:   layout.margins.left + 'px',
               width:  (layout.page.width  - layout.margins.left - layout.margins.right) + 'px',
               height: (layout.page.height - layout.margins.top  - layout.margins.bottom) + 'px',
               outline: '1px dashed rgba(0,0,0,.06)'
             }"></div>
      </div>

      {{-- Blocks --}}
      <template x-for="blk in blocks" :key="blk.id">
        <div class="absolute ring-1 ring-gray-200"
             :style="{
               top:    (blk.top    ?? 0)   + 'px',
               left:   (blk.left   ?? 0)   + 'px',
               width:  (blk.width  ?? 100) + 'px',
               height: (blk.height ?? 32)  + 'px',
               zIndex: blk.z ?? 1
             }">

          {{-- TEXT --}}
          <template x-if="blk.type === 'text'">
            <div class="w-full h-full p-2 overflow-hidden"
                 :style="{
                   textAlign:  blk.align || 'left',
                   fontWeight: blk.bold ? '700' : '400',
                   fontSize: ((blk.fontSize ?? layout.font.size) + 'pt')
                 }"
                 x-text="blk.text || ''"></div>
          </template>

          {{-- IMAGE --}}
          <template x-if="blk.type === 'image'">
            <div class="w-full h-full flex items-center justify-center bg-white">
              <template x-if="blk.src">
                <img :src="blk.src" class="max-w-full max-h-full object-contain">
              </template>
              <template x-if="!blk.src">
                <span class="text-xs text-gray-400">[Gambar]</span>
              </template>
            </div>
          </template>

          {{-- TABLE CELL --}}
          <template x-if="blk.type === 'tableCell'">
            <div class="w-full h-full px-2 py-1 border border-gray-300 bg-gray-50/60 overflow-hidden"
                 :style="{
                   display:'flex', alignItems:'center',
                   fontWeight: blk.bold ? '700' : '400',
                   fontSize:  (blk.fontSize ?? 12) + 'px'
                 }"
                 x-text="blk.text || '—'"></div>
          </template>

          {{-- FOOTER --}}
          <template x-if="blk.type === 'footer'">
            <div class="w-full h-full px-2 py-1 flex items-center justify-between bg-white"
                 :style="{ fontSize: (blk.fontSize ?? 11) + 'px', textAlign: blk.align || 'left' }">
              <div class="truncate" x-text="blk.text || '© Perusahaan 2025'"></div>
              <div class="text-xs text-gray-600" x-show="blk.showPage">Halaman 1 / N</div>
            </div>
          </template>

          {{-- SIGNATURE --}}
          <template x-if="blk.type === 'signature'">
            <div class="w-full h-full p-2 bg-white/90 backdrop-blur rounded">
              <div class="text-[11px] text-gray-600" x-text="blk.role || 'Role'"></div>
              <div class="mt-1 w-full flex-1 border border-dashed rounded flex items-center justify-center" style="height:38px;">
                <template x-if="blk.src">
                  <img :src="blk.src" class="max-h-full object-contain">
                </template>
                <template x-if="!blk.src && blk.signatureText">
                  <span class="italic" x-text="blk.signatureText"></span>
                </template>
                <template x-if="!blk.src && !blk.signatureText">
                  <span class="text-[10px] text-gray-400">TTD</span>
                </template>
              </div>
              <div class="mt-1">
                <div class="text-xs font-medium truncate" x-text="blk.name || 'Nama'"></div>
                <div class="text-[11px] text-gray-600 truncate" x-text="blk.position || 'Jabatan'"></div>
              </div>
            </div>
          </template>

        </div>
      </template>
    </div>
  </div>
</div>
@endsection

@push('scripts')
<script>
  function loadDocTemplate(selector) {
    const rawEl = document.querySelector(selector);
    let payload = { name: '', layout: {}, blocks: [], photoUrl: '' };
    try {
      payload = JSON.parse(rawEl?.textContent || '{}');
    } catch (e) {
      console.error('Invalid JSON payload for document template', e);
    }

    // Default layout
    const layout = Object.assign({
      page:    { width: 794, height: 1123 },
      margins: { top: 30, right: 25, bottom: 25, left: 25 },
      font:    { size: 11 }
    }, payload.layout || {});

    // Normalisasi blocks
    const normBlocks = Array.isArray(payload.blocks) ? payload.blocks.map((b) => {
      const type = (b.type || '').toLowerCase();
      const out  = Object.assign({}, b);

      if (type === 'tablecell') out.type = 'tableCell';
      if (type === 'footer')    out.type = 'footer';
      if (type === 'image')     out.type = 'image';
      if (type === 'signature') out.type = 'signature';
      if (type === 'text')      out.type = 'text';

      if (Object.prototype.hasOwnProperty.call(b, 'fontsize')) out.fontSize = b.fontsize;
      if (Object.prototype.hasOwnProperty.call(b, 'showpage')) out.showPage = !!b.showpage;

      return out;
    }) : [];

    return {
      name: payload.name ?? '',
      layout,
      blocks: normBlocks,
      photoUrl: payload.photoUrl || '',
      init(){ /* no-op */ }
    };
  }
</script>
@endpush
