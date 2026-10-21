@extends('layouts.app')

@section('title', 'Dashboard Operasional')

@section('content')
<div class="space-y-6">
  <div class="flex flex-col justify-between gap-4 rounded-2xl border border-coal-100 bg-white p-6 shadow-soft lg:flex-row lg:items-end">
    <div>
      <p class="text-xs font-semibold uppercase tracking-[0.18em] text-maroon-700">Operational Overview</p>
      <h1 class="mt-1 text-3xl font-serif">Dashboard Operasional</h1>
      <p class="mt-2 text-sm text-coal-500">Ringkasan aktivitas HSE, Wellness, KPI, dan laporan operasional berdasarkan site.</p>
    </div>
    <div class="rounded-xl bg-ivory-100 px-4 py-3 text-sm text-coal-600">
      Site aktif: <span class="font-semibold text-coal-900">{{ $selectedSite?->name ?? 'Semua site yang diizinkan' }}</span>
    </div>
  </div>

  <form method="GET" class="flex flex-col gap-3 rounded-2xl border border-coal-100 bg-white p-4 shadow-soft sm:flex-row sm:items-end">
    <div class="min-w-0 flex-1">
      <label for="site_id" class="mb-1 block text-xs font-semibold uppercase tracking-wider text-coal-500">Filter site</label>
      <select id="site_id" name="site_id" class="w-full rounded-xl border border-coal-200 bg-white px-3 py-2 text-sm">
        <option value="">Semua site</option>
        @foreach($sites as $site)
          <option value="{{ $site->id }}" @selected($selectedSiteId === $site->id)>{{ $site->code }} — {{ $site->name }}</option>
        @endforeach
      </select>
    </div>
    <button class="inline-flex items-center justify-center gap-2 rounded-xl bg-maroon-700 px-4 py-2 text-sm font-medium text-white hover:bg-maroon-800">
      <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M3 4h18M6 12h12M10 20h4"/></svg>
      Terapkan
    </button>
    @if($selectedSiteId)
      <a href="{{ route('admin.dashboard') }}" class="inline-flex items-center justify-center rounded-xl border border-coal-200 px-4 py-2 text-sm hover:bg-ivory-100">Reset</a>
    @endif
  </form>

  <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-5">
    @foreach([
      ['Wellness', $metrics['wellness'], route('admin.wellness.index'), 'wellness'],
      ['HIPO / Nearmiss', $metrics['hipo'], route('admin.hipo.index'), 'warning'],
      ['Input KPI', $metrics['kpi'], route('admin.daily.index'), 'chart'],
      ['Catatan Harian', $metrics['notes'], route('user.daily_notes.index'), 'note'],
      ['Critical Control', $metrics['ccm'], route('ccm-reports.index'), 'access'],
    ] as [$label, $value, $url, $icon])
      <a href="{{ $url }}{{ $selectedSiteId && !str_contains($url, '?') ? '?site_id='.$selectedSiteId : '' }}" class="group rounded-2xl border border-coal-100 bg-white p-5 shadow-soft transition hover:-translate-y-0.5 hover:border-maroon-300">
        <div class="flex items-start justify-between gap-3">
          <span class="text-sm text-coal-500">{{ $label }}</span>
          <span class="text-maroon-700" aria-hidden="true"><x-ui-icon name="{{ $icon }}" class="h-6 w-6"/></span>
        </div>
        <div class="mt-3 text-3xl font-semibold text-coal-900">{{ number_format($value, 0, ',', '.') }}</div>
        <div class="mt-2 text-xs font-medium text-maroon-700 group-hover:underline">Buka fitur →</div>
      </a>
    @endforeach
  </div>

  <div class="grid gap-6 lg:grid-cols-2">
    <section class="rounded-2xl border border-coal-100 bg-white p-6 shadow-soft">
      <div class="flex items-start justify-between gap-4">
        <div>
          <h2 class="font-semibold text-coal-900">Akses cepat</h2>
          <p class="mt-1 text-sm text-coal-500">Fitur operasional yang digunakan sehari-hari.</p>
        </div>
        <svg class="h-6 w-6 text-maroon-700" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M13 3H6a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V10m-7-7 7 7m-7-7v7h7"/></svg>
      </div>
      <div class="mt-5 grid gap-3 sm:grid-cols-2">
        <a href="{{ route('wellness.create') }}" target="_blank" class="rounded-xl border border-coal-200 p-4 text-sm hover:bg-ivory-100">Buka form Wellness publik</a>
        <a href="{{ route('admin.reports.monthly') }}" class="rounded-xl border border-coal-200 p-4 text-sm hover:bg-ivory-100">Lihat rekap KPI bulanan</a>
        <a href="{{ route('admin.sites.index') }}" class="rounded-xl border border-coal-200 p-4 text-sm hover:bg-ivory-100">Kelola site</a>
        @if(auth()->user()->isSuperAdmin())
          <a href="{{ route('admin.site_access.index') }}" class="rounded-xl border border-coal-200 p-4 text-sm hover:bg-ivory-100">Assign akses site</a>
        @endif
      </div>
    </section>

    <section class="rounded-2xl border border-coal-100 bg-white p-6 shadow-soft">
      <h2 class="font-semibold text-coal-900">Keterangan akses</h2>
      <p class="mt-2 text-sm leading-6 text-coal-500">Data di dashboard mengikuti site yang ditugaskan ke akun. HO dapat memilih seluruh site, sedangkan akun site hanya dapat melihat data miliknya.</p>
      <div class="mt-5 rounded-xl bg-ivory-100 p-4 text-sm text-coal-700">
        <span class="font-semibold">{{ $sites->count() }}</span> site tersedia untuk akun ini.
      </div>
    </section>
  </div>
</div>
@endsection
