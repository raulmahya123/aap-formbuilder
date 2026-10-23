@extends('layouts.app')

@section('title', 'HSE Inspection & Audit')

@section('content')
@php $toBars = fn (array $chart) => array_combine($chart['labels'] ?? [], $chart['data'] ?? []); @endphp
<div class="ui-page space-y-6">
    @include('hse.dashboard.partials.header', [
        'title' => 'Inspection & Audit Findings',
        'subtitle' => 'Jadwal dan closure inspeksi, skor, temuan audit, severity, aging finding, dan status perbaikan.',
        'filters' => $filters,
        'options' => $options,
        'export' => 'inspections',
    ])

    <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-3">
        @foreach($data['kpis'] as $kpi)
            <x-hse.kpi-card :kpi="$kpi" />
        @endforeach
    </div>

    <div class="grid gap-4 xl:grid-cols-2">
        <x-hse.chart-card title="Scheduled vs Completed" subtitle="Inspeksi per bulan">
            <x-hse.line-chart :chart="$data['charts']['completion']" />
        </x-hse.chart-card>
        <x-hse.chart-card title="Tren Skor Inspeksi" subtitle="Rata-rata skor inspeksi selesai">
            <x-hse.line-chart :chart="$data['charts']['score_trend']" />
        </x-hse.chart-card>
    </div>

    <div class="grid gap-4 xl:grid-cols-3">
        <x-hse.chart-card title="Skor per Site" subtitle="Rata-rata skor inspeksi selesai">
            <x-ui.bar-chart :series="$toBars($data['charts']['score_by_site'])" label="Skor inspeksi per site" />
        </x-hse.chart-card>
        <x-hse.chart-card title="Severity Temuan" subtitle="Distribusi severity finding">
            <x-hse.donut-chart :chart="$data['charts']['findings_severity']" title="Severity temuan" />
        </x-hse.chart-card>
        <x-hse.chart-card title="Status Closure" subtitle="Distribusi status finding">
            <x-hse.donut-chart :chart="$data['charts']['closure']" title="Status closure" />
        </x-hse.chart-card>
    </div>

    <div class="grid gap-4 xl:grid-cols-2">
        <x-hse.chart-card title="Temuan per Department" subtitle="Top 10 department">
            <x-ui.bar-chart :series="$toBars($data['charts']['findings_department'])" label="Temuan per department" />
        </x-hse.chart-card>
        <x-hse.chart-card title="Aging Temuan" subtitle="Berdasarkan due date">
            <x-ui.bar-chart :series="$toBars($data['charts']['aging'])" label="Aging temuan" />
        </x-hse.chart-card>
    </div>

    <div class="grid gap-4 xl:grid-cols-2">
        <x-hse.chart-card title="Inspeksi Mendatang" subtitle="10 jadwal terdekat">
            <div class="ui-table-scroll">
                <table class="ui-table">
                    <thead><tr><th>Inspeksi</th><th>Site</th><th>Jadwal</th><th>Status</th></tr></thead>
                    <tbody>
                        @forelse($data['upcoming'] as $row)
                            <tr>
                                <td class="font-medium">{{ $row['title'] }}</td>
                                <td>{{ $row['site'] }}</td>
                                <td>{{ $row['date'] }}</td>
                                <td><x-hse.status :value="$row['status']" /></td>
                            </tr>
                        @empty
                            <x-ui.table-empty-state :colspan="4" title="Tidak ada jadwal" message="Belum ada inspeksi terjadwal." />
                        @endforelse
                    </tbody>
                </table>
            </div>
        </x-hse.chart-card>
        <x-hse.chart-card title="Riwayat Inspeksi" subtitle="50 inspeksi terakhir">
            <div class="ui-table-scroll">
                <table class="ui-table">
                    <thead><tr><th>Inspeksi</th><th>Site</th><th>Tanggal</th><th>Skor</th><th>Status</th></tr></thead>
                    <tbody>
                        @forelse($data['history'] as $row)
                            <tr>
                                <td class="font-medium">{{ $row['title'] }}</td>
                                <td>{{ $row['site'] }}</td>
                                <td>{{ $row['date'] }}</td>
                                <td>{{ $row['score'] === null ? '—' : number_format((float) $row['score'], 1, ',', '.') }}</td>
                                <td><x-hse.status :value="$row['status']" /></td>
                            </tr>
                        @empty
                            <x-ui.table-empty-state :colspan="5" title="Belum ada riwayat" message="Belum ada inspeksi pada periode ini." />
                        @endforelse
                    </tbody>
                </table>
            </div>
        </x-hse.chart-card>
    </div>

    <x-hse.chart-card title="Temuan Audit & Inspeksi" subtitle="50 temuan terakhir">
        <div class="ui-table-scroll">
            <table class="ui-table">
                <thead><tr><th>No</th><th>Temuan</th><th>Site</th><th>Severity</th><th>Due Date</th><th>Assignee</th><th>Status</th></tr></thead>
                <tbody>
                    @forelse($data['findings'] as $row)
                        <tr>
                            <td class="font-medium">{{ $row['finding_no'] }}</td>
                            <td>{{ $row['title'] }}</td>
                            <td>{{ $row['site'] }}</td>
                            <td><x-hse.status :value="$row['severity']" /></td>
                            <td>{{ $row['due_date'] }}</td>
                            <td>{{ $row['assignee'] }}</td>
                            <td><x-hse.status :value="$row['status']" /></td>
                        </tr>
                    @empty
                        <x-ui.table-empty-state :colspan="7" title="Belum ada temuan" message="Tidak ada finding pada periode ini." />
                    @endforelse
                </tbody>
            </table>
        </div>
    </x-hse.chart-card>
</div>
@endsection
