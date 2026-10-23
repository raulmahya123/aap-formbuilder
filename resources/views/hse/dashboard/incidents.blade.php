@extends('layouts.app')

@section('title', 'HSE Incident & Near Miss')

@section('content')
@php $toBars = fn (array $chart) => array_combine($chart['labels'] ?? [], $chart['data'] ?? []); @endphp
<div class="ui-page space-y-6">
    @include('hse.dashboard.partials.header', [
        'title' => 'Incident & Near Miss Analytics',
        'subtitle' => 'Distribusi insiden, near miss, severity, aktivitas, waktu kejadian, investigasi, dan root cause.',
        'filters' => $filters,
        'options' => $options,
        'export' => 'incidents',
    ])

    <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
        @foreach($data['kpis'] as $kpi)
            <x-hse.kpi-card :kpi="$kpi" />
        @endforeach
    </div>

    <x-hse.chart-card title="Tren Insiden & Near Miss" subtitle="Perbandingan bulanan dengan tahun sebelumnya">
        <x-hse.line-chart :chart="$data['charts']['trend']" />
    </x-hse.chart-card>

    <div class="grid gap-4 xl:grid-cols-3">
        <x-hse.chart-card title="Insiden per Tipe" subtitle="Distribusi tipe kejadian">
            <x-hse.donut-chart :chart="$data['charts']['by_type']" title="Tipe insiden" />
        </x-hse.chart-card>
        <x-hse.chart-card title="Insiden per Severity" subtitle="Distribusi tingkat keparahan">
            <x-hse.donut-chart :chart="$data['charts']['by_severity']" title="Severity" />
        </x-hse.chart-card>
        <x-hse.chart-card title="Status Investigasi" subtitle="Distribusi status investigasi">
            <x-hse.donut-chart :chart="$data['charts']['investigation']" title="Status investigasi" />
        </x-hse.chart-card>
    </div>

    <div class="grid gap-4 xl:grid-cols-2">
        <x-hse.chart-card title="Insiden per Department" subtitle="Top 10 department">
            <x-ui.bar-chart :series="$toBars($data['charts']['by_department'])" label="Insiden per department" />
        </x-hse.chart-card>
        <x-hse.chart-card title="Insiden per Site" subtitle="Top 10 site">
            <x-ui.bar-chart :series="$toBars($data['charts']['by_site'])" label="Insiden per site" />
        </x-hse.chart-card>
        <x-hse.chart-card title="Insiden per Aktivitas" subtitle="Top aktivitas saat kejadian">
            <x-ui.bar-chart :series="$toBars($data['charts']['by_activity'])" label="Insiden per aktivitas" />
        </x-hse.chart-card>
        <x-hse.chart-card title="Waktu Kejadian" subtitle="Distribusi per rentang jam">
            <x-ui.bar-chart :series="$toBars($data['charts']['time_of_day'])" label="Insiden per waktu" />
        </x-hse.chart-card>
    </div>

    <x-hse.chart-card title="Root Cause" subtitle="Klasifikasi root cause berdasarkan teks investigasi">
        <x-ui.bar-chart :series="$toBars($data['root_cause'])" label="Distribusi root cause" />
    </x-hse.chart-card>

    <x-hse.chart-card title="Daftar Insiden" subtitle="50 laporan terakhir pada periode">
        <div class="ui-table-scroll">
            <table class="ui-table">
                <thead>
                    <tr><th>No</th><th>Tanggal</th><th>Site</th><th>Department</th><th>Kategori</th><th>Tipe</th><th>Severity</th><th>Investigasi</th><th>Investigator</th><th>Status</th></tr>
                </thead>
                <tbody>
                    @forelse($data['rows'] as $row)
                        <tr>
                            <td class="font-medium">{{ $row['incident_no'] }}</td>
                            <td>{{ $row['date'] }}</td>
                            <td>{{ $row['site'] }}</td>
                            <td>{{ $row['department'] }}</td>
                            <td>{{ $row['category'] }}</td>
                            <td>{{ $row['type'] }}</td>
                            <td><x-hse.status :value="$row['severity']" /></td>
                            <td><x-hse.status :value="$row['investigation_status']" /></td>
                            <td>{{ $row['investigator'] }}</td>
                            <td><x-hse.status :value="$row['status']" /></td>
                        </tr>
                    @empty
                        <x-ui.table-empty-state :colspan="10" title="Belum ada insiden" message="Tidak ada laporan pada periode ini." />
                    @endforelse
                </tbody>
            </table>
        </div>
    </x-hse.chart-card>
</div>
@endsection
