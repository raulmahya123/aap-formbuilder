{{-- resources/views/user/daily/index.blade.php --}}
@extends('layouts.app')

@section('title','Dashboard — Input Harian')

@php
  use Illuminate\Support\Arr;
  use Illuminate\Support\Str;
  use Illuminate\Support\Carbon;

  // ===== Fallback agar view tidak error =====
  /** @var \Illuminate\Support\Collection $sites */
  $sites = ($sites ?? collect())->values();

  /** @var \Illuminate\Support\Collection|\Illuminate\Contracts\Pagination\Paginator $entries */
  $entries = $entries ?? collect();

  // KPI totals (bulan/period aktif)
  $kpis = array_merge([
    'FATALITY'        => 0,
    'LTI'             => 0,
    'INJURY_NON_LTI'  => 0,
    'PD'              => 0,
    'MAN_HOURS'       => 0,
  ], (array)($kpis ?? []));

  $today         = now();
  $period        = ($period ?? request('period')) ?: $today->format('Y-m'); // format input[type=month]
  $periodLabel   = Carbon::createFromFormat('Y-m', $period)->isoFormat('MMMM Y'); // ex: September 2025
  $activeSite    = $activeSite ?? null;
  $activeSiteTxt = $activeSite?->code ?? $activeSite?->name ?? 'ALL';

  // daftar indikator yg mau ditampilkan sebagai kolom tabel (kalau available)
  $indicatorColumns = $indicatorColumns ?? ['FATALITY','LTI','INJURY_NON_LTI','PD','MAN_HOURS'];

  // Helper untuk ambil angka dari entry yang mungkin bentuknya beragam
  $getVal = function ($entry, $key, $default = 0) {
      // coba beberapa tempat umum
      $v = data_get($entry, "totals.$key");
      $v = $v ?? data_get($entry, "metrics.$key");
      $v = $v ?? data_get($entry, Str::lower($key));
      $v = $v ?? data_get($entry, Str::snake($key));
      return is_numeric($v) ? (int)$v : ($v ?: $default);
  };

@endphp

@section('breadcrumbs')
  <nav class="text-sm text-coal-600 dark:text-coal-300">
    <a href="{{ url('/') }}" class="hover:underline">Home</a>
    <span class="mx-1">/</span>
    <span class="font-medium">Input Harian</span>
  </nav>
@endsection

{{-- Aksi kanan di header --}}
@section('actions')
  <div class="flex items-center gap-2">
    @if(Route::has('daily.create'))
      <a href="{{ route('daily.create') }}"
         class="inline-flex items-center gap-2 px-3 py-2 rounded-lg border border-maroon-700 text-maroon-700 hover:bg-maroon-50
                dark:border-maroon-600 dark:text-maroon-300 dark:hover:bg-maroon-900/20 text-sm">
        ✍️ Catat Hari Ini
      </a>
    @elseif(Route::has('admin.daily.create'))
      <a href="{{ route('admin.daily.create') }}"
         class="inline-flex items-center gap-2 px-3 py-2 rounded-lg border border-maroon-700 text-maroon-700 hover:bg-maroon-50
                dark:border-maroon-600 dark:text-maroon-300 dark:hover:bg-maroon-900/20 text-sm">
        ✍️ Catat Hari Ini
      </a>
    @endif

    @if(Route::has('admin.reports.monthly'))
      <a href="{{ route('admin.reports.monthly', ['period'=>$period]) }}"
         class="inline-flex items-center gap-2 px-3 py-2 rounded-lg border border-coal-300 dark:border-coal-700 hover:bg-ivory-100 dark:hover:bg-coal-900 text-sm">
        📈 Rekap Bulanan
      </a>
    @endif
  </div>
@endsection

@section('content')
  {{-- Header --}}
  <div class="flex items-center justify-between mb-4">
    <h1 class="text-2xl font-extrabold tracking-tight">Input Harian</h1>
    {{-- actions dirender dari @section('actions') --}}
  </div>

  {{-- Filter Bar --}}
  <form method="get" class="mb-6 grid grid-cols-1 md:grid-cols-6 gap-3">
    <div class="md:col-span-2">
      <label class="block text-xs mb-1 text-coal-600 dark:text-coal-300">Periode (Bulan)</label>
      <input type="month" name="period" value="{{ $period }}"
             class="w-full px-3 py-2 rounded-lg border border-coal-300 dark:border-coal-700 bg-white dark:bg-coal-900">
    </div>

    <div class="md:col-span-2">
      <label class="block text-xs mb-1 text-coal-600 dark:text-coal-300">Site</label>
      <select name="site_id"
              class="w-full px-3 py-2 rounded-lg border border-coal-300 dark:border-coal-700 bg-white dark:bg-coal-900">
        <option value="">ALL</option>
        @foreach($sites as $s)
          <option value="{{ $s->id }}"
            @selected((string)request('site_id') === (string)$s->id)>
            {{ $s->code ?? $s->name ?? ('Site #'.$s->id) }}
          </option>
        @endforeach
      </select>
    </div>

    <div class="md:col-span-2 flex items-end">
      <button class="px-4 py-2 rounded-lg border border-coal-300 dark:border-coal-700 hover:bg-ivory-100 dark:hover:bg-coal-900 w-full">
        Terapkan
      </button>
    </div>
  </form>

  {{-- Badge konteks --}}
  <div class="mb-6 flex flex-wrap items-center gap-2 text-sm">
    <span class="inline-flex items-center gap-1 px-2 py-1 rounded-md border
                 border-maroon-700 text-maroon-800 dark:text-maroon-300 dark:border-maroon-600 bg-maroon-50/70
                 dark:bg-maroon-900/20">
      📍 {{ $activeSiteTxt }}
    </span>
    <span class="inline-flex items-center gap-1 px-2 py-1 rounded-md border border-coal-300 dark:border-coal-700">
      🗓️ {{ $periodLabel }}
    </span>
  </div>

  {{-- KPI Cards --}}
  <div class="grid sm:grid-cols-2 lg:grid-cols-5 gap-3 mb-6">
    <div class="rounded-2xl border border-coal-200 dark:border-coal-800 bg-ivory-50 dark:bg-coal-950 p-4">
      <div class="text-xs text-coal-500 dark:text-coal-400">Fatality</div>
      <div class="mt-1 text-2xl font-bold">{{ (int)$kpis['FATALITY'] }}</div>
    </div>
    <div class="rounded-2xl border border-coal-200 dark:border-coal-800 bg-ivory-50 dark:bg-coal-950 p-4">
      <div class="text-xs text-coal-500 dark:text-coal-400">LTI</div>
      <div class="mt-1 text-2xl font-bold">{{ (int)$kpis['LTI'] }}</div>
    </div>
    <div class="rounded-2xl border border-coal-200 dark:border-coal-800 bg-ivory-50 dark:bg-coal-950 p-4">
      <div class="text-xs text-coal-500 dark:text-coal-400">Injury Non LTI</div>
      <div class="mt-1 text-2xl font-bold">{{ (int)$kpis['INJURY_NON_LTI'] }}</div>
    </div>
    <div class="rounded-2xl border border-coal-200 dark:border-coal-800 bg-ivory-50 dark:bg-coal-950 p-4">
      <div class="text-xs text-coal-500 dark:text-coal-400">Property Damage (PD)</div>
      <div class="mt-1 text-2xl font-bold">{{ (int)$kpis['PD'] }}</div>
    </div>
    <div class="rounded-2xl border border-coal-200 dark:border-coal-800 bg-ivory-50 dark:bg-coal-950 p-4">
      <div class="text-xs text-coal-500 dark:text-coal-400">Man Hours</div>
      <div class="mt-1 text-2xl font-bold">{{ number_format((int)$kpis['MAN_HOURS']) }}</div>
    </div>
  </div>

  {{-- Shortcuts --}}
  <div class="grid sm:grid-cols-2 lg:grid-cols-3 gap-3 mb-8">
    <div class="rounded-2xl border border-coal-200 dark:border-coal-800 p-4 bg-white dark:bg-coal-900">
      <div class="font-semibold mb-1">Catat Hari Ini</div>
      <p class="text-sm text-coal-600 dark:text-coal-400 mb-3">Input data harian untuk periode aktif.</p>
      @if(Route::has('daily.create'))
        <a href="{{ route('daily.create') }}"
           class="inline-flex items-center gap-2 px-3 py-2 rounded-lg border border-maroon-700 text-maroon-700 hover:bg-maroon-50
                  dark:border-maroon-600 dark:text-maroon-300 dark:hover:bg-maroon-900/20">✍️ Mulai</a>
      @elseif(Route::has('admin.daily.create'))
        <a href="{{ route('admin.daily.create') }}"
           class="inline-flex items-center gap-2 px-3 py-2 rounded-lg border border-maroon-700 text-maroon-700 hover:bg-maroon-50
                  dark:border-maroon-600 dark:text-maroon-300 dark:hover:bg-maroon-900/20">✍️ Mulai</a>
      @endif
    </div>

    <div class="rounded-2xl border border-coal-200 dark:border-coal-800 p-4 bg-white dark:bg-coal-900">
      <div class="font-semibold mb-1">Rekap Bulanan</div>
      <p class="text-sm text-coal-600 dark:text-coal-400 mb-3">Lihat ringkasan indikator per bulan.</p>
      @if(Route::has('admin.reports.monthly'))
        <a href="{{ route('admin.reports.monthly', ['period'=>$period]) }}"
           class="inline-flex items-center gap-2 px-3 py-2 rounded-lg border border-coal-300 dark:border-coal-700 hover:bg-ivory-100 dark:hover:bg-coal-800">📈 Buka</a>
      @endif
    </div>

    <div class="rounded-2xl border border-coal-200 dark:border-coal-800 p-4 bg-white dark:bg-coal-900">
      <div class="font-semibold mb-1">Form</div>
      <p class="text-sm text-coal-600 dark:text-coal-400 mb-3">Kumpulkan data/permintaan dengan form.</p>
      @if(Route::has('front.forms.index'))
        <a href="{{ route('front.forms.index') }}"
           class="inline-flex items-center gap-2 px-3 py-2 rounded-lg border border-coal-300 dark:border-coal-700 hover:bg-ivory-100 dark:hover:bg-coal-800">🧾 Lihat Form</a>
      @endif
    </div>
  </div>

  {{-- Tabel Entri Terbaru --}}
  <div class="rounded-2xl border border-coal-200 dark:border-coal-800 overflow-hidden">
    <div class="px-4 py-3 border-b border-coal-200 dark:border-coal-800 bg-ivory-50 dark:bg-coal-950">
      <div class="font-semibold">Entri Terbaru</div>
    </div>

    <div class="overflow-auto">
      <table class="min-w-full text-sm">
        <thead>
          <tr class="text-left border-b border-coal-200 dark:border-coal-800">
            <th class="px-4 py-2">Tanggal</th>
            <th class="px-4 py-2">Site</th>
            <th class="px-4 py-2">Pelapor</th>
            @foreach($indicatorColumns as $col)
              <th class="px-4 py-2">{{ Str::headline(str_replace('_',' ', $col)) }}</th>
            @endforeach
            <th class="px-4 py-2">Status</th>
            <th class="px-4 py-2"></th>
          </tr>
        </thead>
        <tbody>
          @forelse($entries as $e)
            @php
              $date     = $e->date ?? optional($e->created_at)->format('Y-m-d') ?? '';
              $siteCode = data_get($e, 'site.code') ?? data_get($e, 'site.name') ?? '—';
              $userName = data_get($e, 'user.name') ?? '—';
              $status   = data_get($e, 'status') ?? (data_get($e, 'submitted_at') ? 'submitted' : 'draft');
              $showUrl  = '#';
              if (Route::has('daily.show')) {
                $showUrl = route('daily.show', $e->id);
              } elseif (Route::has('admin.daily.show')) {
                $showUrl = route('admin.daily.show', $e->id);
              }
            @endphp
            <tr class="border-b border-coal-100 dark:border-coal-900">
              <td class="px-4 py-2 whitespace-nowrap">{{ $date }}</td>
              <td class="px-4 py-2 whitespace-nowrap">{{ $siteCode }}</td>
              <td class="px-4 py-2 whitespace-nowrap">{{ $userName }}</td>

              @foreach($indicatorColumns as $col)
                <td class="px-4 py-2">
                  {{ number_format($getVal($e, $col, 0)) }}
                </td>
              @endforeach

              <td class="px-4 py-2">
                @if(Str::startsWith($status, 'sub'))
                  <span class="text-xs rounded px-2 py-0.5 bg-emerald-100 text-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-300 border border-emerald-200 dark:border-emerald-800">Submitted</span>
                @else
                  <span class="text-xs rounded px-2 py-0.5 bg-amber-100 text-amber-700 dark:bg-amber-900/30 dark:text-amber-300 border border-amber-200 dark:border-amber-800">Draft</span>
                @endif
              </td>
              <td class="px-4 py-2 text-right">
                @if($showUrl !== '#')
                  <a href="{{ $showUrl }}" class="inline-flex items-center gap-1 px-2 py-1 rounded border border-coal-300 dark:border-coal-700 hover:bg-ivory-100 dark:hover:bg-coal-900">
                    👁️
                  </a>
                @endif
              </td>
            </tr>
          @empty
            <tr>
              <td class="px-4 py-6 text-center text-coal-600 dark:text-coal-400" colspan="{{ 5 + count($indicatorColumns) }}">
                Belum ada entri pada periode ini.
              </td>
            </tr>
          @endforelse
        </tbody>
      </table>
    </div>

    {{-- Pagination jika tersedia --}}
    @if(method_exists($entries, 'links'))
      <div class="px-4 py-3 border-t border-coal-200 dark:border-coal-800">
        {{ $entries->withQueryString()->links() }}
      </div>
    @endif
  </div>

@endsection
