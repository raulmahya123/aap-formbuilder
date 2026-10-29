@extends('layouts.app')

@section('title', 'HSE Environmental')

@section('content')
@php $toBars = fn (array $chart) => array_combine($chart['labels'] ?? [], $chart['data'] ?? []); @endphp
<div class="ui-page space-y-6">
    @include('hse.dashboard.partials.header', [
            'showDashboardFilters' => true,
            'title' => 'Environmental Performance',
            'subtitle' => 'Waste, hazardous waste, recycling rate, energy, water, fuel, insiden lingkungan, dan target lingkungan.',
            'filters' => $filters,
            'options' => $options,
            'export' => 'environment',
            'create' => ['route' => 'hse.dashboard.environment.create', 'label' => 'Form Lingkungan & Limbah'],
        ])

    @if(session('ok'))
        <div class="rounded-lg bg-emerald-50 border border-emerald-200 p-3 text-xs text-emerald-800 font-medium">
            {{ session('ok') }}
        </div>
    @endif

    <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-3">
        @foreach($data['kpis'] as $kpi)
            <x-hse.kpi-card :kpi="$kpi" />
        @endforeach
    </div>

    <div class="grid gap-4 xl:grid-cols-2">
        <x-hse.chart-card title="Tren Waste Generated" subtitle="Total waste per bulan">
            <x-hse.line-chart :chart="$data['charts']['waste_trend']" />
        </x-hse.chart-card>
        <x-hse.chart-card title="Komposisi Waste" subtitle="Hazardous, non-hazardous, dan recycled">
            <x-hse.donut-chart :chart="$data['charts']['waste_type']" title="Komposisi waste" />
        </x-hse.chart-card>
    </div>

    <div class="grid gap-4 xl:grid-cols-2">
        <x-hse.chart-card title="Recycling Rate" subtitle="Persentase waste yang didaur ulang per bulan">
            <x-hse.line-chart :chart="$data['charts']['recycling']" />
        </x-hse.chart-card>
        <x-hse.chart-card title="Target Lingkungan" subtitle="Actual vs target per metrik">
            <x-hse.line-chart :chart="$data['charts']['targets']" />
        </x-hse.chart-card>
    </div>

    <div class="grid gap-4 xl:grid-cols-3">
        <x-hse.chart-card title="Energy Consumption" subtitle="Tren bulanan">
            <x-hse.line-chart :chart="$data['charts']['energy']" />
        </x-hse.chart-card>
        <x-hse.chart-card title="Water Consumption" subtitle="Tren bulanan">
            <x-hse.line-chart :chart="$data['charts']['water']" />
        </x-hse.chart-card>
        <x-hse.chart-card title="Fuel Consumption" subtitle="Tren bulanan">
            <x-hse.line-chart :chart="$data['charts']['fuel']" />
        </x-hse.chart-card>
    </div>

    <div class="grid gap-4 xl:grid-cols-2">
        <x-hse.chart-card title="Insiden Lingkungan per Site" subtitle="Top 10 site">
            <x-ui.bar-chart :series="$toBars($data['charts']['incidents_by_site'])" label="Insiden lingkungan per site" />
        </x-hse.chart-card>
        <x-hse.chart-card title="Total Metrik Lingkungan" subtitle="Akumulasi periode terpilih">
            @if(!empty($data['totals']))
                <x-ui.bar-chart :series="collect($data['totals'])->mapWithKeys(fn ($row, $metric) => [$metric => $row['value'] ?? 0])->all()" label="Total metrik lingkungan" />
            @else
                <x-ui.empty-state title="Belum ada data lingkungan" message="Data akan tampil setelah environmental record tersedia." />
            @endif
        </x-hse.chart-card>
    </div>
</div>

<!-- Modal Catat Pemantauan Lingkungan -->

@endsection
