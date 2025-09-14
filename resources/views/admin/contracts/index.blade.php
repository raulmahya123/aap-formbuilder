@extends('layouts.app')
@section('title','Kontrak')

@section('content')
@php
  $isSuper = auth()->check() && auth()->user()->isSuperAdmin();
@endphp

<div class="max-w-7xl mx-auto space-y-6">

  {{-- Header + Actions --}}
  <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-3">
    <div>
      <h1 class="text-2xl font-bold tracking-tight text-[#1D1C1A]">Daftar Kontrak</h1>
      <p class="text-sm text-coal-500">Kontrak yang kamu miliki atau yang dibagikan ke kamu.</p>
    </div>

    @if($isSuper)
      <div class="flex items-center gap-2">
        <a href="{{ route('admin.contracts.create') }}"
           class="inline-flex items-center gap-2 px-4 py-2 rounded-xl bg-[#7A2C2F] text-white hover:opacity-90">
          <svg class="w-4 h-4" viewBox="0 0 24 24" fill="currentColor"><path d="M11 11V5h2v6h6v2h-6v6h-2v-6H5v-2h6z"/></svg>
          Upload Baru
        </a>
      </div>
    @endif
  </div>

  {{-- Flash --}}
  @if(session('ok'))
    <div class="p-3 rounded-xl bg-emerald-50 text-emerald-800 border border-emerald-200">{{ session('ok') }}</div>
  @endif
  @if(session('err'))
    <div class="p-3 rounded-xl bg-rose-50 text-rose-800 border border-rose-200">{{ session('err') }}</div>
  @endif

  {{-- Toolbar: Search + filter sederhana --}}
  <form method="get" class="flex flex-col sm:flex-row gap-2 items-stretch sm:items-center">
    <div class="relative flex-1">
      <input type="text" name="q" value="{{ request('q') }}" placeholder="Cari judul kontrak…"
             class="w-full border rounded-xl px-3 py-2 pr-9 focus:outline-none focus:border-[#7A2C2F]">
      <span class="absolute right-3 top-2.5 text-coal-400">🔎</span>
    </div>
    <button class="px-4 py-2 rounded-xl border border-coal-300 hover:bg-ivory-100">Filter</button>
    @if(request()->has('q') && request('q')!=='')
      <a href="{{ route('admin.contracts.index') }}" class="px-4 py-2 rounded-xl border border-coal-300 hover:bg-ivory-100">Reset</a>
    @endif
  </form>

  {{-- Desktop: Table --}}
  <div class="hidden md:block">
    <div class="overflow-hidden rounded-2xl border shadow-sm">
      <table class="w-full text-sm">
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
                  <div class="w-8 h-8 rounded-full flex items-center justify-center text-xs font-semibold text-white" style="background:#7A2C2F">
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
                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs border"
                      style="border-color:#7A2C2F;color:#7A2C2F">
                  {{ $vc }} user
                </span>
              </td>
              <td class="p-3 align-top whitespace-nowrap">{{ number_format(($c->size_bytes ?? 0)/1024,1) }} KB</td>
              <td class="p-3 align-top whitespace-nowrap">{{ optional($c->created_at)->format('d M Y H:i') }}</td>
              <td class="p-3 align-top">
                <div class="flex justify-end gap-2">
                  @if($isSuper)
                    <a href="{{ route('admin.contracts.show',$c) }}"
                       class="px-3 py-1.5 rounded-lg border border-coal-300 hover:bg-ivory-100">Detail</a>
                  @endif

                  <a href="{{ route('admin.contracts.download',$c) }}"
                     class="px-3 py-1.5 rounded-lg bg-emerald-600 text-white hover:bg-emerald-700">Download</a>

                  @can('delete', $c)
                    <form action="{{ route('admin.contracts.destroy',$c) }}" method="POST" class="inline"
                          onsubmit="return confirm('Yakin mau hapus kontrak ini? Tindakan ini tidak bisa dibatalkan.')">
                      @csrf
                      @method('DELETE')
                      <button type="submit"
                              class="px-3 py-1.5 rounded-lg bg-rose-600 text-white hover:bg-rose-700">
                        Delete
                      </button>
                    </form>
                  @endcan
                </div>
              </td>
            </tr>
          @empty
            <tr>
              <td colspan="6" class="p-6 text-center text-coal-500">
                Belum ada kontrak.
                @if($isSuper)
                  <a href="{{ route('admin.contracts.create') }}" class="text-[#7A2C2F] underline">Upload sekarang</a>.
                @endif
              </td>
            </tr>
          @endforelse
        </tbody>
      </table>
    </div>
  </div>

  {{-- Mobile: Cards --}}
  <div class="grid gap-3 md:hidden">
    @forelse($contracts as $c)
      <div class="rounded-2xl border shadow-sm p-4">
        <div class="flex items-start justify-between gap-3">
          <div class="min-w-0">
            @if($isSuper)
              <a href="{{ route('admin.contracts.show',$c) }}" class="font-semibold hover:underline">{{ $c->title }}</a>
            @else
              <span class="font-semibold">{{ $c->title }}</span>
            @endif
            <div class="text-xs text-coal-500 mt-0.5">{{ optional($c->created_at)->format('d M Y H:i') }}</div>
          </div>
          <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs border"
                style="border-color:#7A2C2F;color:#7A2C2F">
            {{ method_exists($c,'viewers') ? $c->viewers->count() : ($c->viewers_count ?? 0) }} user
          </span>
        </div>

        <div class="mt-3 flex items-center gap-2">
          <div class="w-8 h-8 rounded-full flex items-center justify-center text-xs font-semibold text-white" style="background:#7A2C2F">
            {{ strtoupper(\Illuminate\Support\Str::of(optional($c->owner)->name)->explode(' ')->map(fn($s)=>\Illuminate\Support\Str::substr($s,0,1))->take(2)->implode('')) ?: 'U' }}
          </div>
          <div class="min-w-0">
            <div class="text-sm truncate">{{ optional($c->owner)->name ?? '—' }}</div>
            <div class="text-xs text-coal-500 truncate">{{ optional($c->owner)->email ?? '' }}</div>
          </div>
        </div>

        <div class="mt-3 flex items-center justify-between text-sm text-coal-600">
          <div>{{ number_format(($c->size_bytes ?? 0)/1024,1) }} KB</div>
          <div class="flex gap-2">
            @if($isSuper)
              <a href="{{ route('admin.contracts.show',$c) }}" class="px-3 py-1.5 rounded-lg border border-coal-300 hover:bg-ivory-100">Detail</a>
            @endif

            <a href="{{ route('admin.contracts.download',$c) }}" class="px-3 py-1.5 rounded-lg bg-emerald-600 text-white hover:bg-emerald-700">Download</a>

            @can('delete', $c)
              <form action="{{ route('admin.contracts.destroy',$c) }}" method="POST" class="inline"
                    onsubmit="return confirm('Hapus kontrak ini?')">
                @csrf
                @method('DELETE')
                <button type="submit" class="px-3 py-1.5 rounded-lg bg-rose-600 text-white hover:bg-rose-700">
                  Delete
                </button>
              </form>
            @endcan
          </div>
        </div>
      </div>
    @empty
      <div class="rounded-2xl border p-6 text-center text-coal-500">
        Belum ada kontrak.
        @if($isSuper)
          <a href="{{ route('admin.contracts.create') }}" class="text-[#7A2C2F] underline">Upload sekarang</a>.
        @endif
      </div>
    @endforelse
  </div>

  {{-- Pagination --}}
  <div>
    {{ $contracts->withQueryString()->links() }}
  </div>

</div>
@endsection
