@extends('layouts.app')

@section('title', 'HSE Overview')

@section('content')
@php
    $toBars = fn (array $chart) => array_combine($chart['labels'] ?? [], $chart['data'] ?? []);
    $environment = collect($data['charts']['environment'] ?? [])->mapWithKeys(fn ($row, $metric) => [$metric => $row['value'] ?? 0])->all();
    $compliance = $data['charts']['compliance'] ?? [];
@endphp
<div class="ui-page space-y-6">
    @include('hse.dashboard.partials.header', [
        'showDashboardFilters' => true,
        'title' => 'HSE Executive Overview',
        'subtitle' => 'Ringkasan kinerja K3LH, insiden, inspeksi, training, lingkungan, risiko, dan corrective action.',
        'filters' => $filters,
        'options' => $options,
        'export' => 'executive',
    ])

    @if(!empty($data['alerts']))
        <div class="grid gap-2 sm:grid-cols-2 xl:grid-cols-3">
            @foreach($data['alerts'] as $alert)
                <a href="{{ $alert['href'] }}" class="flex items-start gap-2 rounded-lg border border-[#E2E8F0] bg-white p-3 text-sm hover:border-[#94A3B8]">
                    <span class="mt-0.5 inline-flex h-5 w-5 shrink-0 items-center justify-center rounded-full text-xs font-bold text-white" style="background:{{ $alert['level'] === 'danger' ? '#DC2626' : '#F59E0B' }}">!</span>
                    <span class="text-[#334155]">{{ $alert['title'] }}</span>
                </a>
            @endforeach
        </div>
    @endif

    <section>
        <h2 class="mb-3 text-sm font-semibold uppercase tracking-wide text-[#64748B]">Key Performance Indicators</h2>
        <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
            @foreach($data['kpis'] as $kpi)
                <x-hse.kpi-card :kpi="$kpi" />
            @endforeach
        </div>
    </section>

    <div class="grid gap-4 xl:grid-cols-3">
        <x-hse.chart-card title="Tren Insiden & Near Miss" subtitle="Perbandingan bulanan dengan tahun sebelumnya" class="xl:col-span-2">
            <x-hse.line-chart :chart="$data['charts']['incident_trend']" />
        </x-hse.chart-card>
        <x-hse.chart-card title="Insiden per Tipe" subtitle="Distribusi tipe kejadian">
            <x-hse.donut-chart :chart="$data['charts']['by_type']" title="Insiden per tipe" />
        </x-hse.chart-card>
    </div>

    <div class="grid gap-4 xl:grid-cols-3">
        <x-hse.chart-card title="Lokasi dengan Insiden Terbanyak" subtitle="Top 10 site">
            <x-ui.bar-chart :series="$toBars($data['charts']['top_locations'])" label="Insiden per site" />
        </x-hse.chart-card>
        <x-hse.chart-card title="Tren Skor Inspeksi" subtitle="Rata-rata skor inspeksi selesai per bulan">
            <x-hse.line-chart :chart="$data['charts']['inspection_trend']" />
        </x-hse.chart-card>
        <x-hse.chart-card title="Status Training" subtitle="Distribusi status record training">
            <x-hse.donut-chart :chart="$data['charts']['training_status']" title="Status training" />
        </x-hse.chart-card>
    </div>

    <div class="grid gap-4 xl:grid-cols-3">
        <x-hse.chart-card title="Status Corrective Action" subtitle="Distribusi status action">
            <x-hse.donut-chart :chart="$data['charts']['action_status']" title="Status corrective action" />
        </x-hse.chart-card>
        <x-hse.chart-card title="Risk Matrix" subtitle="Risiko aktif berdasarkan residual likelihood × consequence">
            <x-hse.risk-matrix :matrix="$data['charts']['risk_matrix']" />
        </x-hse.chart-card>
        <x-hse.chart-card title="Kepatuhan & Lingkungan" subtitle="Ringkasan compliance dan metrik lingkungan">
            <div class="grid gap-3">
                <div class="rounded-lg bg-[#F8FAFC] p-3">
                    <div class="flex items-center justify-between text-xs text-[#64748B]">
                        <span>Compliance (finding closed)</span>
                        <strong class="text-[#0F172A]">{{ number_format((float) ($compliance['percentage'] ?? 0), 1, ',', '.') }}%</strong>
                    </div>
                    <div class="mt-2 h-2 overflow-hidden rounded-full bg-[#E2E8F0]">
                        <div class="h-full rounded-full bg-[#16A34A]" style="width:{{ min(100, (float) ($compliance['percentage'] ?? 0)) }}%"></div>
                    </div>
                    <p class="mt-1 text-[11px] text-[#94A3B8]">{{ $compliance['compliant'] ?? 0 }} compliant · {{ $compliance['non_compliant'] ?? 0 }} non-compliant · {{ $compliance['expired'] ?? 0 }} expired</p>
                </div>
                @if(!empty($environment))
                    <x-ui.bar-chart :series="$environment" label="Metrik lingkungan" />
                @else
                    <x-ui.empty-state title="Belum ada data lingkungan" message="Data akan tampil setelah environmental record tersedia." />
                @endif
            </div>
        </x-hse.chart-card>
    </div>

    @php
        $overviewSections = [
            ['key' => 'incidents', 'title' => 'Insiden Terbaru', 'description' => 'Laporan insiden pada periode terpilih.', 'count' => count($data['recent']), 'icon' => 'alert-triangle'],
            ['key' => 'inspections', 'title' => 'Inspeksi Mendatang', 'description' => 'Jadwal inspeksi terdekat.', 'count' => count($data['upcoming_inspections']), 'icon' => 'calendar-days'],
            ['key' => 'actions', 'title' => 'Corrective Action Terlambat', 'description' => 'Action yang melewati due date dan belum selesai.', 'count' => count($data['overdue_actions']), 'icon' => 'clipboard-list'],
        ];
    @endphp
    <x-ui.dashboard-section-picker :items="$overviewSections">
        <div x-show="active === 'incidents'" x-cloak>
        <x-hse.chart-card title="Insiden Terbaru" subtitle="6 laporan terakhir">
            <div class="ui-table-scroll">
                <table class="ui-table">
                    <thead>
                        <tr><th>No</th><th>Tanggal</th><th>Site</th><th>Tipe</th><th>Severity</th><th>Status</th></tr>
                    </thead>
                    <tbody>
                        @forelse($data['recent'] as $row)
                            <tr>
                                <td class="font-medium">{{ $row['incident_no'] }}</td>
                                <td>{{ $row['date'] }}</td>
                                <td>{{ $row['site'] }}</td>
                                <td>{{ $row['type'] }}</td>
                                <td><x-hse.status :value="$row['severity']" /></td>
                                <td><x-hse.status :value="$row['status']" /></td>
                            </tr>
                        @empty
                            <x-ui.table-empty-state :colspan="6" title="Belum ada insiden" message="Tidak ada insiden pada periode ini." />
                        @endforelse
                    </tbody>
                </table>
            </div>
        </x-hse.chart-card>
        </div>
        <div x-show="active === 'inspections'" x-cloak>
        <x-hse.chart-card title="Inspeksi Mendatang" subtitle="Jadwal terdekat">
            <div class="ui-table-scroll">
                <table class="ui-table">
                    <thead>
                        <tr><th>Inspeksi</th><th>Site</th><th>Jadwal</th><th>Status</th></tr>
                    </thead>
                    <tbody>
                        @forelse($data['upcoming_inspections'] as $row)
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
        </div>
        <div x-show="active === 'actions'" x-cloak>
    <x-hse.chart-card title="Corrective Action Terlambat" subtitle="Action yang melewati due date dan belum closed">
        <div class="ui-table-scroll">
            <table class="ui-table">
                <thead>
                    <tr><th>No</th><th>Judul</th><th>PIC</th><th>Due Date</th><th>Status</th></tr>
                </thead>
                <tbody>
                    @forelse($data['overdue_actions'] as $row)
                        <tr>
                            <td class="font-medium">{{ $row['action_no'] }}</td>
                            <td>{{ $row['title'] }}</td>
                            <td>{{ $row['pic'] }}</td>
                            <td class="text-[#B91C1C]">{{ $row['due_date'] }}</td>
                            <td><x-hse.status :value="$row['status']" /></td>
                        </tr>
                    @empty
                        <x-ui.table-empty-state :colspan="5" title="Tidak ada action terlambat" message="Semua corrective action sesuai jadwal." />
                    @endforelse
                </tbody>
            </table>
        </div>
    </x-hse.chart-card>
        </div>
    </x-ui.dashboard-section-picker>
</div>
@endsection
