@extends('layouts.app')
@section('title','Catatan Harian')

@section('content')
<div class="max-w-6xl mx-auto space-y-6">
  <div class="flex flex-col sm:flex-row sm:items-end gap-3 justify-between">
    <div>
      <h1 class="text-2xl font-bold text-maroon-700">📝 Catatan Harian</h1>
      <p class="text-sm text-coal-600 dark:text-coal-300">Rekap judul, deskripsi, tanggal & waktu (WIB), dan penulis.</p>
    </div>

    <div class="flex items-center gap-2">
      <form method="get" class="flex flex-wrap items-center gap-2">
        {{-- Filter tanggal (default: $targetDate dari controller) --}}
        <input type="date" name="date"
               value="{{ $targetDate ?? now('Asia/Jakarta')->format('Y-m-d') }}"
               class="border rounded-lg px-3 py-2 bg-white dark:bg-coal-900">

        {{-- Pencarian --}}
        <input type="text" name="q" value="{{ $query ?? '' }}" placeholder="Cari judul / isi…"
               class="border rounded-lg px-3 py-2 bg-white dark:bg-coal-900" />

        {{-- Pilihan jumlah data per halaman --}}
        <select name="perPage" class="border rounded-lg px-3 py-2 bg-white dark:bg-coal-900">
          @foreach([10,25,50,100] as $opt)
            <option value="{{ $opt }}" {{ ($perPage ?? 10) == $opt ? 'selected' : '' }}>
              {{ $opt }}/hal
            </option>
          @endforeach
        </select>

        <button class="px-3 py-2 rounded-lg border border-coal-300 dark:border-coal-700 hover:bg-ivory-100 dark:hover:bg-coal-900">
          Filter
        </button>

        @if(request()->hasAny(['date','q','perPage']))
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

  <!-- (Opsional) Alpine.js CDN jika belum ada -->
  <script>
    if (!window.alpineLoaded) {
      var s = document.createElement('script');
      s.defer = true;
      s.src = 'https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js';
      s.onload = () => window.alpineLoaded = true;
      document.head.appendChild(s);
    }
  </script>

  <!-- Clamp util biar rapi (2 baris) -->
  <style>
    .clamp-2 {
      line-height: 1.4;
      max-height: calc(1.4em * 2);
      overflow: hidden;
      text-overflow: ellipsis;
      display: -webkit-box;
      -webkit-line-clamp: 2;
      -webkit-box-orient: vertical;
      word-break: break-word;
    }
  </style>

  <div x-data="{
        descSize: Number(localStorage.getItem('descSize') ?? 14),
        save(){ localStorage.setItem('descSize', this.descSize) }
      }"
      class="space-y-3">

    <!-- Kontrol ukuran khusus Deskripsi -->
    <div class="flex items-center gap-2">
      <span class="text-sm text-gray-600 dark:text-coal-300">
        Ukuran teks Deskripsi: <span class="font-medium" x-text="descSize + 'px'"></span>
      </span>

      <button @click="descSize = Math.max(10, descSize - 1); save()"
        class="px-2 py-1 border rounded hover:bg-gray-50 dark:border-coal-700" aria-label="Perkecil">−</button>

      <input type="range" min="10" max="24" step="1" x-model.number="descSize" @input="save()"
        class="w-40 accent-[--maroon]" aria-label="Geser ukuran deskripsi" />

      <button @click="descSize = Math.min(24, descSize + 1); save()"
        class="px-2 py-1 border rounded hover:bg-gray-50 dark:border-coal-700" aria-label="Perbesar">+</button>

      <button @click="descSize = 14; save()"
        class="ml-2 px-3 py-1 rounded bg-ivory-100 dark:bg-coal-900 border border-gray-300 dark:border-coal-700 text-sm">
        Reset
      </button>
    </div>

    <!-- Tabel -->
    <div class="overflow-hidden bg-white dark:bg-coal-950 rounded-xl border border-gray-200 dark:border-coal-800 shadow-sm">
      <table class="min-w-full divide-y divide-gray-200 dark:divide-coal-800">
        <thead class="bg-ivory-100 dark:bg-coal-900">
          <tr class="text-left text-sm">
            <th class="px-4 py-3 font-semibold">Judul</th>
            <th class="px-4 py-3 font-semibold" :style="`font-size:${descSize + 2}px`">Deskripsi</th>
            <th class="px-4 py-3 font-semibold whitespace-nowrap">Tanggal (WIB)</th>
            <th class="px-4 py-3 font-semibold whitespace-nowrap">Waktu (WIB)</th>
            <th class="px-4 py-3 font-semibold">Oleh</th>
          </tr>
        </thead>

        <tbody class="divide-y divide-gray-200 dark:divide-coal-800">
          @forelse($notes as $n)
            @php
              $wib = $n->note_time?->timezone('Asia/Jakarta');
            @endphp
            <tr class="text-sm align-top">
              <td class="px-4 py-3 font-medium">{{ $n->title }}</td>

              <!-- Deskripsi: ukuran dinamis + clamp 2 baris -->
              <td class="px-4 py-3 align-top" :style="`font-size:${descSize}px`">
  <textarea
    readonly
    rows="2"
    class="w-full resize-y overflow-auto min-h-[2.6em] max-h-[32rem]
           border border-gray-200 dark:border-coal-700 rounded-md p-2
           bg-transparent text-coal-700 dark:text-coal-300"
    :style="`font-size:${descSize}px; line-height:1.4;`"
  >{{ $n->content }}</textarea>
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
  </div>

  @if(method_exists($notes, 'hasPages') && $notes->hasPages())
    <div>
      {{ $notes->links() }}
    </div>
  @endif
</div>
@endsection
