@extends('layouts.app')
@section('title','Kontrak')
@section('content')
@php
  $isSuper = auth()->check() && auth()->user()->isSuperAdmin();
@endphp
<div class="w-full min-w-0 ui-module-stack space-y-5">
  {{-- Header + Actions --}}
  <x-ui.page-header>
  <div class="ui-page-header__copy"><div>
      <x-ui.page-title>Daftar Kontrak</x-ui.page-title>
      <x-ui.page-description>Kontrak yang kamu miliki atau yang dibagikan ke kamu.</x-ui.page-description>
    </div></div>
  <x-ui.page-actions>@if($isSuper)
      <div class="flex items-center gap-2">
        <x-ui.link data-modal-form icon="plus" variant="primary" href="{{ route('admin.contracts.create') }}"
           class="inline-flex items-center gap-2 px-4 py-2 rounded-lg hover:opacity-90">
          Upload Baru
        </x-ui.link>
      </div>
    @endif</x-ui.page-actions>

<x-slot:filters>
<x-ui.table-toolbar >
    <div class="relative flex-1">
      <x-ui.table-search   name="q" value="{{ request('q') }}" placeholder="Cari judul kontrak…"
             class="w-full" />

    </div>

<x-slot:actions>
<x-ui.button variant="secondary" >Filter</x-ui.button>
    @if(request()->has('q') && request('q')!=='')
      <x-ui.link variant="secondary" href="{{ route('admin.contracts.index') }}" >Reset</x-ui.link>
    @endif

</x-slot:actions>
</x-ui.table-toolbar>
</x-slot:filters>
</x-ui.page-header>
  {{-- Flash --}}
  @if(session('err'))
    <div class="p-3 rounded-lg bg-rose-50 text-rose-800 border border-rose-200">{{ session('err') }}</div>
  @endif
  {{-- Toolbar: Search + filter sederhana --}}

  <x-ui.table :paginator="$contracts" class="w-full text-sm">
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
                @if($isSuper)
                  <a href="{{ route('admin.contracts.show',$c) }}" class="font-medium hover:underline text-[#1D1C1A]">
                    {{ $c->title }}
                  </a>
                @else
                  <span class="font-medium text-[#1D1C1A]">{{ $c->title }}</span>
                @endif
                @if(!empty($c->uuid))
                  <div class="text-[11px] text-coal-500 font-mono">#{{ $c->uuid }}</div>
                @endif
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
                @php $vc = method_exists($c,'viewers') ? $c->viewers->count() : ($c->viewers_count ?? 0); @endphp
                <span class="inline-flex items-center px-2 py-1 rounded-full text-xs border"
                      style="border-color:#713F12;color:#713F12">
                  {{ $vc }} user
                </span>
              </td>
              <td class="p-3 align-top whitespace-nowrap">{{ number_format(($c->size_bytes ?? 0)/1024,1) }} KB</td>
              <td class="p-3 align-top whitespace-nowrap">{{ optional($c->created_at)->format('d M Y H:i') }}</td>
              <td class="ui-action-cell p-3 align-top"><x-ui.table-actions>
                <div class="flex justify-end gap-2">
                  @if($isSuper)
                    <x-ui.icon-button action="view" label="Lihat detail" href="{{ route('admin.contracts.show',$c) }}" />
                  @endif
                  <x-ui.icon-button action="download" label="Unduh" href="{{ route('admin.contracts.download',$c) }}" />
                  @can('delete', $c)
                    <form action="{{ route('admin.contracts.destroy',$c) }}" method="POST" class="inline"
                          data-confirm="Yakin mau hapus kontrak ini? Tindakan ini tidak bisa dibatalkan.">
                      @csrf
                      @method('DELETE')
                      <x-ui.icon-button action="delete" label="Hapus" type="submit" />
                    </form>
                  @endcan
                </div>
              </x-ui.table-actions></td>
            </tr>
          @empty
<x-ui.table-empty-state colspan="6"></x-ui.table-empty-state>
@endforelse
        </tbody>
      </x-ui.table>

</div>
@endsection