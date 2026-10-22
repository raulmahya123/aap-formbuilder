@extends('layouts.app')
@section('title','Kontrak Saya')
@section('content')
<x-ui.page class="max-w-7xl mx-auto">
  {{-- Header --}}
  <x-ui.page-header>
  <div class="ui-page-header__copy"><div>
      <x-ui.page-title>Kontrak</x-ui.page-title>
      <x-ui.page-description>Menampilkan kontrak milikmu atau yang dibagikan ke akunmu.</x-ui.page-description>
    </div></div>
  <x-ui.page-actions>{{-- Search --}}
    </x-ui.page-actions>

<x-slot:filters>
<x-ui.table-toolbar class="w-full md:w-auto">
      <div class="relative">
        <x-ui.table-search   name="q" value="{{ request('q') }}" placeholder="Cari judul kontrak…"
               class="w-full md:w-80 border rounded-lg px-3 py-2 pr-8 focus:outline-none focus:border-[#713F12]" />

      </div>
    </x-ui.table-toolbar>
</x-slot:filters>
</x-ui.page-header>

  {{-- Flash --}}
  @if(session('err'))
    <div class="p-3 rounded-lg bg-rose-50 text-rose-800 border border-rose-200">{{ session('err') }}</div>
  @endif
  {{-- Desktop: Table --}}
  <div class="hidden md:block">
    <div class="overflow-hidden rounded-lg border shadow-none">
      <x-ui.table class="w-full text-sm">
        <thead class="bg-ivory-100 text-left">
          <tr>
            <th class="p-3">Judul</th>
            <th class="p-3">Owner</th>
            <th class="p-3 text-center">Viewer</th>
            <th class="p-3">Ukuran</th>
            <th class="p-3">Dibuat</th>
            <th class="p-3 text-right">Aksi</th>
          </tr>
        </thead>
        <tbody>
          @forelse($contracts as $c)
            <tr class="border-t">
              <td class="p-3 align-top">
                <a href="{{ route('user.contracts.show',$c) }}" class="font-medium hover:underline text-[#1D1C1A]">
                  {{ $c->title }}
                </a>
              </td>
              <td class="p-3 align-top">
                <div class="flex items-center gap-2 min-w-0">
                  <div class="w-8 h-8 rounded-full flex items-center justify-center text-xs font-semibold text-white" style="background:#713F12">
                    {{ strtoupper(\Illuminate\Support\Str::of(optional($c->owner)->name)->explode(' ')->map(fn($s)=>\Illuminate\Support\Str::substr($s,0,1))->take(2)->implode('')) ?: 'U' }}
                  </div>
                  <div class="min-w-0">
                    <div class="truncate">{{ optional($c->owner)->name ?? '—' }}</div>
                    <div class="text-[11px] text-coal-500 truncate">{{ optional($c->owner)->email ?? '' }}</div>
                  </div>
                </div>
              </td>
              <td class="p-3 align-top text-center">
                @php $vc = $c->viewers_count ?? (method_exists($c,'viewers') ? $c->viewers->count() : 0); @endphp
                <span class="inline-flex items-center px-2 py-1 rounded-full text-xs border"
                      style="border-color:#713F12;color:#713F12">
                  {{ $vc }} user
                </span>
              </td>
              <td class="p-3 align-top whitespace-nowrap">{{ number_format(($c->size_bytes ?? 0)/1024,1) }} KB</td>
              <td class="p-3 align-top whitespace-nowrap">{{ optional($c->created_at)->format('d M Y H:i') }}</td>
              <td class="ui-action-cell p-3 align-top"><x-ui.table-actions>
                <div class="flex justify-end gap-2">
                  <x-ui.icon-button action="view" label="Lihat detail" href="{{ route('user.contracts.show',$c) }}" />
                  <x-ui.icon-button action="download" label="Unduh" href="{{ route('user.contracts.download',$c) }}" />
                </div>
              </x-ui.table-actions></td>
            </tr>
          @empty
<x-ui.table-empty-state colspan="6"></x-ui.table-empty-state>
@endforelse
        </tbody>
      </x-ui.table>
    </div>
  </div>
  {{-- Mobile: Cards --}}
  <div class="grid gap-3 md:hidden">
    @forelse($contracts as $c)
      <div class="rounded-lg border shadow-none p-4">
        <div class="flex items-start justify-between gap-3">
          <div class="min-w-0">
            <a href="{{ route('user.contracts.show',$c) }}" class="font-semibold hover:underline">{{ $c->title }}</a>
            <div class="text-xs text-coal-500 mt-1">{{ optional($c->created_at)->format('d M Y H:i') }}</div>
          </div>
          <x-ui.link variant="primary" href="{{ route('user.contracts.download',$c) }}"
             class="shrink-0 px-3 py-2 rounded-lg">Download</x-ui.link>
        </div>
        <div class="mt-3 flex items-center gap-2">
          <div class="w-8 h-8 rounded-full flex items-center justify-center text-xs font-semibold text-white" style="background:#713F12">
            {{ strtoupper(\Illuminate\Support\Str::of(optional($c->owner)->name)->explode(' ')->map(fn($s)=>\Illuminate\Support\Str::substr($s,0,1))->take(2)->implode('')) ?: 'U' }}
          </div>
          <div class="min-w-0">
            <div class="text-sm truncate">{{ optional($c->owner)->name ?? '—' }}</div>
            <div class="text-xs text-coal-500 truncate">{{ optional($c->owner)->email ?? '' }}</div>
          </div>
        </div>
        <div class="mt-3 text-sm text-coal-600">
          {{ number_format(($c->size_bytes ?? 0)/1024,1) }} KB
        </div>
      </div>
    @empty
      <div class="rounded-lg border p-6 text-center text-coal-500">Tidak ada kontrak untukmu.</div>
    @endforelse
  </div>
  {{-- Pagination --}}
  <div>
    {{ $contracts->withQueryString()->links() }}
  </div>
</x-ui.page>
@endsection