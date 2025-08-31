@extends('layouts.app')

@section('title','Document Templates')

@section('content')
<div class="max-w-7xl mx-auto p-6 space-y-6">

  {{-- HEADER --}}
  <div class="flex items-center justify-between">
    <h1 class="text-xl font-semibold text-[#1D1C1A]">Document Templates</h1>
    <a href="{{ route('admin.document_templates.create') }}"
       class="px-4 py-2 rounded-xl bg-[#7A2C2F] text-white hover:opacity-90">+ Template</a>
  </div>

  {{-- LIST --}}
  <div class="bg-white border rounded-xl overflow-hidden">
    <table class="w-full text-sm">
      <thead class="bg-[#1D1C1A] text-white">
        <tr>
          <th class="p-3 text-left">#</th>
          <th class="p-3 text-left">Nama</th>
          <th class="p-3 text-left">Header</th>
          <th class="p-3 text-left">Footer</th>
          <th class="p-3 text-left">Updated</th>
          <th class="p-3 text-left">Aksi</th>
        </tr>
      </thead>
      <tbody>
      @forelse($templates as $tpl)
        @php
          // Pastikan array (model sudah di-cast array; tetap aman kalau string)
          $headerCfg = is_string($tpl->header_config) ? (json_decode($tpl->header_config, true) ?: []) : ($tpl->header_config ?? []);
          $footerCfg = is_string($tpl->footer_config) ? (json_decode($tpl->footer_config, true) ?: []) : ($tpl->footer_config ?? []);

          // ===== HEADER PREVIEW (dukungan skema baru & lama)
          // Baru: items[] dengan type: text/image/tableCell
          $hItems = data_get($headerCfg, 'items', []);
          $hCount = is_array($hItems) ? count($hItems) : 0;

          // Ambil contoh: prioritas text -> image -> tableCell
          $hFirstText = collect($hItems ?? [])->firstWhere('type','text')['text'] ?? null;
          $hFirstImg  = collect($hItems ?? [])->firstWhere('type','image')['src'] ?? null;
          $hFirstCell = collect($hItems ?? [])->firstWhere('type','tableCell')['text'] ?? null;

          // Legacy: logo.url + title.align
          $legacyLogo = data_get($headerCfg,'logo.url');
          $legacyAlign = data_get($headerCfg,'title.align');

          // String ringkasan header
          if ($hCount > 0) {
            $headerSummary = 'items: '.$hCount;
            if ($hFirstText) { $headerSummary .= ' · “'.\Illuminate\Support\Str::limit($hFirstText, 28).'”'; }
            elseif ($hFirstCell) { $headerSummary .= ' · cell: “'.\Illuminate\Support\Str::limit($hFirstCell, 28).'”'; }
          } elseif ($legacyLogo || $legacyAlign) {
            $headerSummary = ($legacyLogo ? 'logo' : 'no-logo') . ' · align: '.($legacyAlign ?? '-');
          } else {
            $headerSummary = '—';
          }

          // ===== FOOTER PREVIEW (baru & lama)
          $fItems = data_get($footerCfg,'items', []);
          if (is_array($fItems) && count($fItems)) {
            $f = $fItems[0];
            $footerText = $f['text'] ?? '-';
            $footerShow = !empty($f['show_page_number']) ? ' · pg no' : '';
            $footerSummary = \Illuminate\Support\Str::limit($footerText, 40).$footerShow;
          } else {
            // legacy: { text, show_page_number }
            $legacyFooterText = data_get($footerCfg,'text');
            $legacyFooterShow = data_get($footerCfg,'show_page_number') ? ' · pg no' : '';
            $footerSummary = $legacyFooterText ? (\Illuminate\Support\Str::limit($legacyFooterText, 40).$legacyFooterShow) : '—';
          }
        @endphp

        <tr class="border-b hover:bg-[#7A2C2F]/5">
          <td class="p-3">{{ $tpl->id }}</td>
          <td class="p-3 font-medium">
            <div class="flex items-center gap-2">
              {{-- Tampilkan gambar header (baru: image/src, lama: logo.url) kalau ada --}}
              @if(!empty($hFirstImg))
                <img src="{{ $hFirstImg }}" class="h-5 w-auto rounded border bg-white" alt="logo">
              @elseif(!empty($legacyLogo))
                <img src="{{ $legacyLogo }}" class="h-5 w-auto rounded border bg-white" alt="logo">
              @endif
              <span>{{ $tpl->name }}</span>
            </div>
          </td>

          <td class="p-3 text-xs text-gray-700">
            {{ $headerSummary }}
          </td>

          <td class="p-3 text-xs text-gray-600">
            {{ $footerSummary }}
          </td>

          <td class="p-3 text-xs text-gray-500">
            {{ optional($tpl->updated_at)->format('d M Y H:i') }}
          </td>

          <td class="p-3">
            <div class="flex items-center gap-2">

               <a href="{{ route('admin.document_templates.show', $tpl) }}"
       class="px-3 py-1.5 rounded-lg border text-[#1D1C1A] hover:bg-gray-100">
      Preview
    </a>
              <a href="{{ route('admin.document_templates.edit', $tpl) }}"
                 class="px-3 py-1.5 rounded-lg border border-[#7A2C2F] text-[#7A2C2F] hover:bg-[#7A2C2F] hover:text-white">
                Edit
              </a>
              <form method="POST" action="{{ route('admin.document_templates.destroy', $tpl) }}"
                    onsubmit="return confirm('Hapus template ini?')">
                @csrf @method('DELETE')
                <button class="px-3 py-1.5 rounded-lg bg-red-600 text-white hover:bg-red-700">
                  Delete
                </button>
              </form>
            </div>
          </td>
        </tr>
      @empty
        <tr>
          <td colspan="6" class="p-6 text-center text-gray-500">Belum ada template</td>
        </tr>
      @endforelse
      </tbody>
    </table>
  </div>

  {{-- PAGINATION --}}
  <div>{{ $templates->links() }}</div>
</div>
@endsection
