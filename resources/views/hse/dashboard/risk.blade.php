@extends('layouts.app')

@section('title', 'HSE Risk & Compliance')

@section('content')
@php $toBars = fn (array $chart) => array_combine($chart['labels'] ?? [], $chart['data'] ?? []); @endphp
<div class="ui-page space-y-6">
    @include('hse.dashboard.partials.header', [
        'title' => 'Risk Assessment & Compliance',
        'subtitle' => 'Matriks risiko 5×5, inherent vs residual, treatment, permit, dan status kepatuhan.',
        'filters' => $filters,
        'options' => $options,
        'export' => 'risk',
    ])

    <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
        @foreach($data['kpis'] as $kpi)
            <x-hse.kpi-card :kpi="$kpi" />
        @endforeach
    </div>

    <div class="grid gap-4 xl:grid-cols-2">
        <x-hse.chart-card title="Risk Matrix 5×5" subtitle="Residual likelihood × consequence">
            <x-hse.risk-matrix :matrix="$data['charts']['matrix']" />
        </x-hse.chart-card>
        <x-hse.chart-card title="Inherent vs Residual" subtitle="Perbandingan skor risiko sebelum dan sesudah kontrol">
            <x-hse.line-chart :chart="$data['charts']['inherent_residual']" />
        </x-hse.chart-card>
    </div>

    <div class="grid gap-4 xl:grid-cols-3">
        <x-hse.chart-card title="Risiko per Kategori" subtitle="Distribusi kategori hazard">
            <x-ui.bar-chart :series="$toBars($data['charts']['by_category'])" label="Risiko per kategori" />
        </x-hse.chart-card>
        <x-hse.chart-card title="Risiko per Site" subtitle="Top 10 site">
            <x-ui.bar-chart :series="$toBars($data['charts']['by_site'])" label="Risiko per site" />
        </x-hse.chart-card>
        <x-hse.chart-card title="Aktivitas Risiko Tinggi" subtitle="Top 10 aktivitas">
            <x-ui.bar-chart :series="$toBars($data['charts']['high_activity'])" label="Aktivitas risiko" />
        </x-hse.chart-card>
    </div>

    <div class="grid gap-4 xl:grid-cols-3">
        <x-hse.chart-card title="Progress Treatment" subtitle="Status treatment risiko">
            <x-hse.donut-chart :chart="$data['charts']['treatment_progress']" title="Treatment" />
        </x-hse.chart-card>
        <x-hse.chart-card title="Status Permit" subtitle="Distribusi status permit">
            <x-hse.donut-chart :chart="$data['charts']['permit_timeline']" title="Status permit" />
        </x-hse.chart-card>
        <x-hse.chart-card title="Status Requirement" subtitle="Status compliance requirement">
            <x-hse.donut-chart :chart="$data['charts']['requirement_status']" title="Requirement" />
        </x-hse.chart-card>
    </div>

    <x-hse.chart-card title="Risiko High & Extreme" subtitle="10 risiko aktif dengan skor tertinggi">
        <div class="ui-table-scroll">
            <table class="ui-table">
                <thead><tr><th>Aktivitas</th><th>Hazard</th><th>Site</th><th>L</th><th>C</th><th>Skor</th><th>Level</th><th>Treatment</th><th>Due</th></tr></thead>
                <tbody>
                    @php $riskRows = array_merge($data['extreme_risk'], $data['high_risk']); @endphp
                    @forelse($riskRows as $row)
                        <tr>
                            <td class="font-medium">{{ $row['activity'] }}</td>
                            <td>{{ $row['hazard'] }}</td>
                            <td>{{ $row['site'] }}</td>
                            <td>{{ $row['likelihood'] }}</td>
                            <td>{{ $row['consequence'] }}</td>
                            <td class="font-semibold">{{ $row['score'] }}</td>
                            <td><x-hse.status :value="$row['level']" /></td>
                            <td><x-hse.status :value="$row['treatment_status']" /></td>
                            <td>{{ $row['due_date'] }}</td>
                        </tr>
                    @empty
                        <x-ui.table-empty-state :colspan="9" title="Tidak ada risiko high/extreme" message="Tidak ada risiko aktif berlevel High atau Extreme." />
                    @endforelse
                </tbody>
            </table>
        </div>
    </x-hse.chart-card>

    <div class="grid gap-4 xl:grid-cols-2">
        <x-hse.chart-card title="Treatment Terlambat" subtitle="Melewati treatment due date">
            <div class="ui-table-scroll">
                <table class="ui-table">
                    <thead><tr><th>Aktivitas</th><th>Site</th><th>Skor</th><th>Due</th><th>Status</th></tr></thead>
                    <tbody>
                        @forelse($data['overdue_treatment'] as $row)
                            <tr>
                                <td class="font-medium">{{ $row['activity'] }}</td>
                                <td>{{ $row['site'] }}</td>
                                <td>{{ $row['score'] }}</td>
                                <td class="text-[#B91C1C]">{{ $row['due_date'] }}</td>
                                <td><x-hse.status :value="$row['treatment_status']" /></td>
                            </tr>
                        @empty
                            <x-ui.table-empty-state :colspan="5" title="Tidak ada treatment terlambat" message="Semua treatment sesuai jadwal." />
                        @endforelse
                    </tbody>
                </table>
            </div>
        </x-hse.chart-card>

        <x-hse.chart-card title="Permit Akan Kedaluwarsa" subtitle="Dalam 60 hari ke depan">
            <div class="ui-table-scroll">
                <table class="ui-table">
                    <thead><tr><th>No Permit</th><th>Tipe</th><th>Site</th><th>Berlaku Sampai</th><th>Status</th></tr></thead>
                    <tbody>
                        @forelse($data['expiring_permits'] as $row)
                            <tr>
                                <td class="font-medium">{{ $row['permit_no'] }}</td>
                                <td>{{ $row['type'] }}</td>
                                <td>{{ $row['site'] }}</td>
                                <td>{{ $row['valid_to'] }}</td>
                                <td><x-hse.status :value="$row['status']" /></td>
                            </tr>
                        @empty
                            <x-ui.table-empty-state :colspan="5" title="Tidak ada permit kedaluwarsa" message="Semua permit masih berlaku." />
                        @endforelse
                    </tbody>
                </table>
            </div>
        </x-hse.chart-card>
    </div>

    <x-hse.chart-card title="Requirement Belum Terpenuhi" subtitle="Finding yang belum closed">
        <div class="ui-table-scroll">
            <table class="ui-table">
                <thead><tr><th>Temuan</th><th>Site</th><th>Severity</th><th>Due Date</th><th>Status</th></tr></thead>
                <tbody>
                    @forelse($data['non_compliance'] as $row)
                        <tr>
                            <td class="font-medium">{{ $row['title'] }}</td>
                            <td>{{ $row['site'] }}</td>
                            <td><x-hse.status :value="$row['severity']" /></td>
                            <td>{{ $row['due_date'] }}</td>
                            <td><x-hse.status :value="$row['status']" /></td>
                        </tr>
                    @empty
                        <x-ui.table-empty-state :colspan="5" title="Semua requirement terpenuhi" message="Tidak ada finding yang terbuka." />
                    @endforelse
                </tbody>
            </table>
        </div>
    </x-hse.chart-card>
</div>
@endsection
