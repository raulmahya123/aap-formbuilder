@extends('layouts.app')

@section('title', 'HSE Corrective Action')

@section('content')
@php
    $toBars = fn (array $chart) => array_combine($chart['labels'] ?? [], $chart['data'] ?? []);
    $closureBars = collect($data['charts']['monthly_closure'])->mapWithKeys(fn ($value, $month) => [\Carbon\Carbon::createFromDate(2000, (int) $month, 1)->translatedFormat('M') => $value])->all();
@endphp
<div class="ui-page space-y-6">
    @include('hse.dashboard.partials.header', [
        'title' => 'Corrective Action Tracking',
        'subtitle' => 'Status action, overdue, SLA, aging, closure trend, sumber temuan, dan prioritas.',
        'filters' => $filters,
        'options' => $options,
        'export' => 'actions',
    ])

    <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
        @foreach($data['kpis'] as $kpi)
            <x-hse.kpi-card :kpi="$kpi" />
        @endforeach
    </div>

    <div class="grid gap-4 xl:grid-cols-2">
        <x-hse.chart-card title="Opened vs Closed" subtitle="Tren action per bulan">
            <x-hse.line-chart :chart="$data['charts']['trend']" />
        </x-hse.chart-card>
        <x-hse.chart-card title="Status Action" subtitle="Distribusi status corrective action">
            <x-hse.donut-chart :chart="$data['charts']['status']" title="Status action" />
        </x-hse.chart-card>
    </div>

    <div class="grid gap-4 xl:grid-cols-3">
        <x-hse.chart-card title="Action per Sumber" subtitle="Distribusi source module">
            <x-hse.donut-chart :chart="$data['charts']['by_source']" title="Sumber action" />
        </x-hse.chart-card>
        <x-hse.chart-card title="Action per Prioritas" subtitle="Distribusi priority">
            <x-hse.donut-chart :chart="$data['charts']['by_priority']" title="Prioritas action" />
        </x-hse.chart-card>
        <x-hse.chart-card title="SLA Compliance" subtitle="On time vs late">
            <x-hse.donut-chart :chart="$data['charts']['sla']" title="SLA" />
        </x-hse.chart-card>
    </div>

    <div class="grid gap-4 xl:grid-cols-2">
        <x-hse.chart-card title="Action per Department" subtitle="Top 10 department">
            <x-ui.bar-chart :series="$toBars($data['charts']['by_department'])" label="Action per department" />
        </x-hse.chart-card>
        <x-hse.chart-card title="Aging Action" subtitle="Berdasarkan due date">
            <x-ui.bar-chart :series="$toBars($data['charts']['aging'])" label="Aging action" />
        </x-hse.chart-card>
    </div>

    <x-hse.chart-card title="Closure per Bulan" subtitle="Action yang closed pada tahun terpilih">
        <x-ui.bar-chart :series="$closureBars" label="Closure per bulan" />
    </x-hse.chart-card>

    <x-hse.chart-card title="Daftar Corrective Action" subtitle="100 action terakhir">
        <div class="ui-table-scroll">
            <table class="ui-table">
                <thead><tr><th>No</th><th>Sumber</th><th>Judul</th><th>Prioritas</th><th>PIC</th><th>Due Date</th><th>Overdue</th><th>Verifikasi</th><th>Status</th></tr></thead>
                <tbody>
                    @forelse($data['rows'] as $row)
                        <tr>
                            <td class="font-medium">{{ $row['action_no'] }}</td>
                            <td>{{ $row['source'] }}</td>
                            <td>{{ $row['title'] }}</td>
                            <td><x-hse.status :value="$row['priority']" /></td>
                            <td>{{ $row['pic'] }}</td>
                            <td>{{ $row['due_date'] }}</td>
                            <td>
                                @if($row['days_overdue'] > 0)
                                    <span class="font-semibold text-[#B91C1C]">{{ $row['days_overdue'] }} hari</span>
                                @else
                                    <span class="text-[#94A3B8]">—</span>
                                @endif
                            </td>
                            <td><x-hse.status :value="$row['verification']" /></td>
                            <td><x-hse.status :value="$row['status']" /></td>
                        </tr>
                    @empty
                        <x-ui.table-empty-state :colspan="9" title="Belum ada corrective action" message="Tidak ada action pada periode ini." />
                    @endforelse
                </tbody>
            </table>
        </div>
    </x-hse.chart-card>
</div>
@endsection
