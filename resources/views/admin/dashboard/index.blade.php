@extends('layouts.app')
@section('title', 'Dashboard Operasional')
@section('content')
<x-ui.page class="">
  <x-ui.page-header>
  <div class="ui-page-header__copy"><div>
      <x-ui.page-title>Dashboard Operasional</x-ui.page-title>
      <x-ui.page-description>Ringkasan aktivitas HSE, Wellness, KPI, dan laporan operasional berdasarkan site.</x-ui.page-description>
    </div></div>
  <x-ui.page-actions><div class="rounded-lg bg-ivory-100 px-4 py-3 text-sm text-coal-600">
      Site aktif: <span class="font-semibold text-coal-900">{{ $selectedSite?->name ?? 'Semua site yang diizinkan' }}</span>
    </div></x-ui.page-actions>

<x-slot:filters>
<x-ui.table-toolbar class="ui-card flex flex-col gap-3 rounded-lg border border-coal-100 bg-white shadow-none sm:flex-row sm:items-end">
    <div class="min-w-0 flex-1">
      <label for="site_id" class="mb-1 block text-xs font-semibold normal-case tracking-normal text-coal-500">Filter site</label>
      <select id="site_id" name="site_id" data-submit-on-change class="w-full rounded-lg border border-coal-200 bg-white px-3 py-2 text-sm">
        <option value="">Semua site</option>
        @foreach($sites as $site)
          <option value="{{ $site->id }}" @selected($selectedSiteId === $site->id)>{{ $site->code }} — {{ $site->name }}</option>
        @endforeach
      </select>
    </div>

<x-slot:actions>
<x-ui.button type="submit" variant="primary" class="inline-flex items-center justify-center gap-2 rounded-lg px-4 py-2 text-sm font-medium">
      <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M3 4h18M6 12h12M10 20h4"/></svg>
      Terapkan
    </x-ui.button>
    @if($selectedSiteId)
      <x-ui.link variant="secondary" href="{{ route('admin.dashboard').'?site_id=' }}" class="inline-flex items-center justify-center rounded-lg border px-4 py-2 text-sm">Reset</x-ui.link>
    @endif

</x-slot:actions>
</x-ui.table-toolbar>
</x-slot:filters>
</x-ui.page-header>
  <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-5">
    @foreach([
      ['Wellness', $metrics['wellness'], route('admin.wellness.index'), 'wellness'],
      ['HIPO / Nearmiss', $metrics['hipo'], route('admin.hipo.index'), 'warning'],
      ['Input KPI', $metrics['kpi'], route('admin.daily.index'), 'chart'],
      ['Catatan Harian', $metrics['notes'], route('user.daily_notes.index'), 'note'],
      ['Critical Control', $metrics['ccm'], route('ccm-reports.index'), 'access'],
    ] as [$label, $value, $url, $icon])
      <x-ui.metric-card :label="$label" :value="$value" :href="$url.'?site_id='.($selectedSiteId ?? '')" icon="circle-check" />
    @endforeach
  </div>
  <div class="grid gap-5 lg:grid-cols-2">
    <section class="ui-card rounded-lg border border-coal-100 bg-white shadow-none">
      <div class="flex items-start justify-between gap-4">
        <div>
          <x-ui.section-title class="font-semibold text-coal-900">Akses cepat</x-ui.section-title>
          <p class="mt-1 text-sm text-coal-500">Fitur operasional yang digunakan sehari-hari.</p>
        </div>
        <svg class="h-6 w-6 text-maroon-700" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M13 3H6a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V10m-7-7 7 7m-7-7v7h7"/></svg>
      </div>
      <div class="mt-5 grid gap-3 sm:grid-cols-2">
        <a href="{{ route('wellness.create') }}" target="_blank" class="rounded-lg border border-coal-200 p-4 text-sm hover:bg-ivory-100">Buka form Wellness publik</a>
        <a href="{{ route('admin.reports.monthly') }}" class="rounded-lg border border-coal-200 p-4 text-sm hover:bg-ivory-100">Lihat rekap KPI bulanan</a>
        <a href="{{ route('admin.sites.index') }}" class="rounded-lg border border-coal-200 p-4 text-sm hover:bg-ivory-100">Kelola site</a>
        @if(auth()->user()->isSuperAdmin())
          <a href="{{ route('admin.site_access.index') }}" class="rounded-lg border border-coal-200 p-4 text-sm hover:bg-ivory-100">Assign akses site</a>
        @endif
      </div>
    </section>
    <section class="ui-card rounded-lg border border-coal-100 bg-white"><x-ui.section-title>Data per modul</x-ui.section-title><p class="mt-1 mb-5 text-sm text-zinc-600">Jumlah data pada site yang dipilih.</p><x-ui.bar-chart :series="['Wellness'=>$metrics['wellness'],'HIPO / Nearmiss'=>$metrics['hipo'],'Input KPI'=>$metrics['kpi'],'Catatan Harian'=>$metrics['notes'],'Critical Control'=>$metrics['ccm']]" /></section>
  </div>
</x-ui.page>
@endsection
