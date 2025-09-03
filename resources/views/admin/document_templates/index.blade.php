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
          <th class="p-3 text-left">Foto</th> {{-- ← kolom baru untuk photo_path --}}
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
          // ===== FOTO (photo_path → url)
          // Jika ada accessor getPhotoUrlAttribute, pakai itu; jika tidak, fallback ke asset('storage/...').
          $photoUrl = method_exists($tpl, 'getPhotoUrlAttribute')
            ? ($tpl->photo_url ?? null)
            : ($tpl->photo_path ? asset('storage/'.$tpl->photo_path) : null);

          // ===== HEADER/FOOTER PREVIEW (aman utk cast/JSON)
          $headerCfg = is_string($tpl->header_config) ? (json_decode($tpl->header_config, true) ?: []) : ($tpl->header_config ?? []);
          $footerCfg = is_string($tpl->footer_config) ? (json_decode($tpl->footer_config, true) ?: []) : ($tpl->footer_config ?? []);

          // HEADER (skema baru & legacy)
          $hItems = data_get($headerCfg, 'items', []);
          $hCount = is_array($hItems) ? count($hItems) : 0;
          $hFirstText = collect($hItems ?? [])->firstWhere('type','text')['text'] ?? null;
          $hFirstImg  = collect($hItems ?? [])->firstWhere('type','image')['src'] ?? null;
          $hFirstCell = collect($hItems ?? [])->firstWhere('type','tableCell')['text'] ?? null;

          $legacyLogo  = data_get($headerCfg,'logo.url');
          $legacyAlign = data_get($headerCfg,'title.align');

          if ($hCount > 0) {
            $headerSummary = 'items: '.$hCount;
            if ($hFirstText)      { $headerSummary .= ' · “'.\Illuminate\Support\Str::limit($hFirstText, 28).'”'; }
            elseif ($hFirstCell)  { $headerSummary .= ' · cell: “'.\Illuminate\Support\Str::limit($hFirstCell, 28).'”'; }
          } elseif ($legacyLogo || $legacyAlign) {
            $headerSummary = ($legacyLogo ? 'logo' : 'no-logo') . ' · align: '.($legacyAlign ?? '-');
          } else {
            $headerSummary = '—';
          }

          // FOOTER (skema baru & legacy)
          $fItems = data_get($footerCfg,'items', []);
          if (is_array($fItems) && count($fItems)) {
            $f = $fItems[0];
            $footerText = $f['text'] ?? '-';
            $footerShow = !empty($f['show_page_number']) ? ' · pg no' : '';
            $footerSummary = \Illuminate\Support\Str::limit($footerText, 40).$footerShow;
          } else {
            $legacyFooterText = data_get($footerCfg,'text');
            $legacyFooterShow = data_get($footerCfg,'show_page_number') ? ' · pg no' : '';
            $footerSummary = $legacyFooterText ? (\Illuminate\Support\Str::limit($legacyFooterText, 40).$legacyFooterShow) : '—';
          }
        @endphp

        <tr class="border-b hover:bg-[#7A2C2F]/5">
          <td class="p-3 align-middle">{{ $tpl->id }}</td>

          {{-- FOTO --}}
          <td class="p-3 align-middle">
            @if($photoUrl)
              <img src="{{ $photoUrl }}" alt="foto" class="h-10 w-10 rounded object-cover border bg-white">
            @else
              <div class="h-10 w-10 rounded border bg-gray-100 grid place-items-center text-[10px] text-gray-500">No\nPhoto</div>
            @endif
          </td>

          {{-- NAMA + (logo header kecil jika ada) --}}
          <td class="p-3 font-medium align-middle">
            <div class="flex items-center gap-2">
              <span class="truncate max-w-[280px]">{{ $tpl->name }}</span>
            </div>
          </td>

          <td class="p-3 text-xs text-gray-700 align-middle">
            {{ $headerSummary }}
          </td>

          <td class="p-3 text-xs text-gray-600 align-middle">
            {{ $footerSummary }}
          </td>

          <td class="p-3 text-xs text-gray-500 align-middle">
            {{ optional($tpl->updated_at)->format('d M Y H:i') }}
          </td>

          <td class="p-3 align-middle">
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
          <td colspan="7" class="p-6 text-center text-gray-500">Belum ada template</td>
        </tr>
      @endforelse
      </tbody>
    </table>
  </div>

  {{-- PAGINATION --}}
  <div>{{ $templates->links() }}</div>
</div>
@endsection
