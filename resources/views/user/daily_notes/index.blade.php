@extends('layouts.app')
@section('title','Catatan Harian')

@section('content')
@php
  use Illuminate\Support\Str;
@endphp

<div class="max-w-6xl mx-auto space-y-6">
  <div class="flex flex-col sm:flex-row sm:items-end gap-3 justify-between">
    <div>
      <h1 class="text-2xl font-bold text-maroon-700">📝 Catatan Harian</h1>
      <p class="text-sm text-coal-600 dark:text-coal-300">Rekap judul, deskripsi, tanggal & waktu (WIB), dan penulis.</p>
    </div>

    <div class="flex items-center gap-2">
      <form method="get" class="flex flex-wrap items-center gap-2">
        {{-- Filter tanggal (harian). Default: hari ini WIB dari controller ($targetDate) --}}
        <input type="date" name="date"
               value="{{ $targetDate ?? request('date') ?? now('Asia/Jakarta')->format('Y-m-d') }}"
               class="border rounded-lg px-3 py-2 bg-white dark:bg-coal-900">
        <input type="text" name="q" value="{{ request('q') }}" placeholder="Cari isi…"
               class="border rounded-lg px-3 py-2 bg-white dark:bg-coal-900" />
        <button class="px-3 py-2 rounded-lg border border-coal-300 dark:border-coal-700 hover:bg-ivory-100 dark:hover:bg-coal-900">
          Filter
        </button>
        @if(request()->hasAny(['date','q']))
          <a href="{{ route('user.daily_notes.index') }}" class="text-sm underline">Reset</a>
        @endif
      </form>

      @if(Route::has('user.daily_notes.create'))
        <a href="{{ route('user.daily_notes.create') }}"
           class="inline-flex items-center gap-2 px-3 py-2 rounded-lg border border-maroon-700 text-maroon-700 hover:bg-maroon-50
                  dark:border-maroon-600 dark:text-maroon-300 dark:hover:bg-maroon-900/20">
          ➕ Catatan Baru
        </a>
      @endif
    </div>
  </div>

  <div class="overflow-hidden bg-white dark:bg-coal-950 rounded-xl border border-gray-200 dark:border-coal-800 shadow-sm">
    <table class="min-w-full divide-y divide-gray-200 dark:divide-coal-800">
      <thead class="bg-ivory-100 dark:bg-coal-900">
        <tr class="text-left text-sm">
          <th class="px-4 py-3 font-semibold">Judul</th>
          <th class="px-4 py-3 font-semibold">Deskripsi</th>
          <th class="px-4 py-3 font-semibold whitespace-nowrap">Tanggal (WIB)</th>
          <th class="px-4 py-3 font-semibold whitespace-nowrap">Waktu (WIB)</th>
          <th class="px-4 py-3 font-semibold">Oleh</th>
        </tr>
      </thead>

      <tbody class="divide-y divide-gray-200 dark:divide-coal-800">
        @forelse($notes as $n)
          @php
            // gunakan note_time sebagai sumber waktu
            $wib = $n->note_time?->timezone('Asia/Jakarta');
          @endphp
          <tr class="text-sm align-top">
            <td class="px-4 py-3 font-medium">{{ $n->title}}</td>
            <td class="px-4 py-3 text-coal-700 dark:text-coal-300">
              {{ Str::limit($n->content, 160) }}
            </td>
            <td class="px-4 py-3 whitespace-nowrap">
              {{ $wib ? $wib->format('d/m/Y') : '—' }}
            </td>
            <td class="px-4 py-3 whitespace-nowrap">
              {{ $wib ? $wib->format('H:i') : '—' }}
            </td>
            <td class="px-4 py-3 whitespace-nowrap">
              {{ $n->user->name ?? '—' }}
            </td>
          </tr>
        @empty
          <tr>
            <td colspan="5" class="px-4 py-6 text-center text-coal-500 dark:text-coal-300">
              Belum ada catatan untuk tanggal ini.
            </td>
          </tr>
        @endforelse
      </tbody>
    </table>
  </div>

  @if(method_exists($notes, 'hasPages') && $notes->hasPages())
    <div>
      {{ $notes->links() }}
    </div>
  @endif
</div>
@endsection
