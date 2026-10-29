@extends('layouts.app')

@section('title', 'HSE Safety Performance')

@section('content')
@php $toBars = fn (array $chart) => array_combine($chart['labels'] ?? [], $chart['data'] ?? []); @endphp
<div class="ui-page space-y-6">
    @include('hse.dashboard.partials.header', [
        'showDashboardFilters' => true,
        'title' => 'Safety Performance',
        'subtitle' => 'LTI, recordable cases, exposure hours, LTIFR, TRIR, severity rate, dan detail insiden keselamatan.',
        'filters' => $filters,
        'options' => $options,
        'export' => 'safety',
    ])

    <div class="rounded-lg border border-[#E2E8F0] bg-white p-3 text-xs text-[#64748B]">
        Formula: LTIFR = LTI / exposure hours × {{ number_format($data['meta']['ltifr_multiplier'], 0, ',', '.') }} ·
        TRIR = recordable / exposure hours × {{ number_format($data['meta']['trir_multiplier'], 0, ',', '.') }} ·
        Exposure hours periode ini: <strong class="text-[#0F172A]">{{ number_format($data['meta']['exposure_hours'], 0, ',', '.') }}</strong> jam.
        Nilai rate menjadi N/A bila exposure hours = 0.
    </div>

    <section>
        <h2 class="mb-3 text-sm font-semibold uppercase tracking-wide text-[#64748B]">Safety KPIs</h2>
        <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
            @foreach($data['kpis'] as $kpi)
                <x-hse.kpi-card :kpi="$kpi" />
            @endforeach
        </div>
    </section>

    <div class="grid gap-4 xl:grid-cols-2">
        <x-hse.chart-card title="Tren LTI & Recordable" subtitle="Bulanan pada tahun terpilih">
            <x-hse.line-chart :chart="$data['charts']['trend']" />
        </x-hse.chart-card>
        <x-hse.chart-card title="Severity Distribution" subtitle="Distribusi tingkat keparahan insiden">
            <x-hse.donut-chart :chart="$data['charts']['severity']" title="Severity" />
        </x-hse.chart-card>
    </div>

    <div class="grid gap-4 xl:grid-cols-2">
        <x-hse.chart-card title="Insiden per Department" subtitle="Top 10 department">
            <x-ui.bar-chart :series="$toBars($data['charts']['by_department'])" label="Insiden per department" />
        </x-hse.chart-card>
        <x-hse.chart-card title="Insiden per Site" subtitle="Top 10 site">
            <x-ui.bar-chart :series="$toBars($data['charts']['by_site'])" label="Insiden per site" />
        </x-hse.chart-card>
    </div>

    <x-hse.chart-card title="Detail Insiden" subtitle="50 insiden terakhir pada periode">
        <div class="ui-table-scroll">
            <table class="ui-table">
                <thead>
                    <tr><th>Tanggal</th><th>Site</th><th>Tipe</th><th>Severity</th><th>Recordable</th><th>Lost Days</th><th>Status</th></tr>
                </thead>
                <tbody>
                    @forelse($data['table'] as $row)
                        <tr>
                            <td>{{ $row['date'] }}</td>
                            <td>{{ $row['site'] }}</td>
                            <td>{{ $row['type'] }}</td>
                            <td><x-hse.status :value="$row['severity']" /></td>
                            <td>{{ $row['recordable'] ? 'Ya' : 'Tidak' }}</td>
                            <td>{{ $row['lost_days'] }}</td>
                            <td><x-hse.status :value="$row['status']" /></td>
                        </tr>
                    @empty
                        <x-ui.table-empty-state :colspan="7" title="Belum ada insiden" message="Tidak ada data insiden pada periode ini." />
                    @endforelse
                </tbody>
            </table>
        </div>
    </x-hse.chart-card>
</div>
@endsection
