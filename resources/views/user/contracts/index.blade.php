@extends('layouts.app')
@section('title','Kontrak Saya')

@section('content')
<div class="max-w-7xl mx-auto space-y-6">

  {{-- Header --}}
  <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-3">
    <div>
      <h1 class="text-2xl font-bold tracking-tight text-[#1D1C1A]">Kontrak</h1>
      <p class="text-sm text-coal-500">Menampilkan kontrak milikmu atau yang dibagikan ke akunmu.</p>
    </div>

    {{-- Search --}}
    <form method="get" class="w-full md:w-auto">
      <div class="relative">
        <input type="text" name="q" value="{{ request('q') }}" placeholder="Cari judul kontrak…"
               class="w-full md:w-80 border rounded-xl px-3 py-2 pr-9 focus:outline-none focus:border-[#7A2C2F]">
        <span class="absolute right-3 top-2.5 text-coal-400">🔎</span>
      </div>
    </form>
  </div>

  {{-- Flash --}}
  @if(session('ok'))
    <div class="p-3 rounded-xl bg-emerald-50 text-emerald-800 border border-emerald-200">{{ session('ok') }}</div>
  @endif
  @if(session('err'))
    <div class="p-3 rounded-xl bg-rose-50 text-rose-800 border border-rose-200">{{ session('err') }}</div>
  @endif

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
                <a href="{{ route('user.contracts.show',$c) }}" class="font-medium hover:underline text-[#1D1C1A]">
                  {{ $c->title }}
                </a>
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
                @php $vc = $c->viewers_count ?? (method_exists($c,'viewers') ? $c->viewers->count() : 0); @endphp
                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs border"
                      style="border-color:#7A2C2F;color:#7A2C2F">
                  {{ $vc }} user
                </span>
              </td>
              <td class="p-3 align-top whitespace-nowrap">{{ number_format(($c->size_bytes ?? 0)/1024,1) }} KB</td>
              <td class="p-3 align-top whitespace-nowrap">{{ optional($c->created_at)->format('d M Y H:i') }}</td>
              <td class="p-3 align-top">
                <div class="flex justify-end gap-2">
                  <a href="{{ route('user.contracts.show',$c) }}"
                     class="px-3 py-1.5 rounded-lg border border-coal-300 hover:bg-ivory-100">Detail</a>
                  <a href="{{ route('user.contracts.download',$c) }}"
                     class="px-3 py-1.5 rounded-lg bg-emerald-600 text-white hover:bg-emerald-700">Download</a>
                </div>
              </td>
            </tr>
          @empty
            <tr><td colspan="6" class="p-6 text-center text-coal-500">Tidak ada kontrak untukmu.</td></tr>
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
            <a href="{{ route('user.contracts.show',$c) }}" class="font-semibold hover:underline">{{ $c->title }}</a>
            <div class="text-xs text-coal-500 mt-0.5">{{ optional($c->created_at)->format('d M Y H:i') }}</div>
          </div>
          <a href="{{ route('user.contracts.download',$c) }}"
             class="shrink-0 px-3 py-1.5 rounded-lg bg-emerald-600 text-white hover:bg-emerald-700">Download</a>
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

        <div class="mt-3 text-sm text-coal-600">
          {{ number_format(($c->size_bytes ?? 0)/1024,1) }} KB
        </div>
      </div>
    @empty
      <div class="rounded-2xl border p-6 text-center text-coal-500">Tidak ada kontrak untukmu.</div>
    @endforelse
  </div>

  {{-- Pagination --}}
  <div>
    {{ $contracts->withQueryString()->links() }}
  </div>

</div>
@endsection
