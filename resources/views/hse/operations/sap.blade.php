@extends('layouts.app')

@section('title', 'Safety Accountability Program (SAP)')

@section('content')
<div class="ui-page space-y-6">
    @include('hse.dashboard.partials.header', [
        'title' => 'Safety Accountability Program (SAP)',
        'subtitle' => 'Monitoring kepatuhan dan pencapaian inspeksi pengawas, PTO, dan hazard report seluruh karyawan.',
        'filters' => $filters,
        'options' => $options,
    ])

    {{-- SAP KPI CARDS --}}
    <section>
        <h2 class="mb-3 text-sm font-semibold uppercase tracking-wide text-[#64748B]">Indikator SAP</h2>
        <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-6">
            @foreach($kpis as $kpi)
                <div class="rounded-xl border border-[#E2E8F0] bg-white p-4 shadow-sm">
                    <span class="text-xs font-semibold uppercase tracking-wider text-[#64748B]">{{ $kpi['label'] }}</span>
                    <div class="mt-2 text-2xl font-bold text-[#1646A0]">{{ is_numeric($kpi['value']) ? number_format($kpi['value'], 0, ',', '.') : $kpi['value'] }} <span class="text-xs font-normal text-[#64748B]">{{ $kpi['unit'] }}</span></div>
                </div>
            @endforeach
        </div>
    </section>

    {{-- PROGRESS COMPARISON --}}
    <div class="grid gap-4 lg:grid-cols-3">
        <div class="rounded-xl border border-[#E2E8F0] bg-white p-6 shadow-sm">
            <h3 class="text-base font-semibold text-[#0F172A]">Inspeksi Pengawas</h3>
            <p class="mt-1 text-xs text-[#64748B]">Target bulanan vs realisasi inspeksi terverifikasi.</p>
            <div class="mt-4 flex items-baseline justify-between">
                <span class="text-3xl font-extrabold text-[#1646A0]">{{ $inspectionsCount }}</span>
                <span class="text-sm font-medium text-[#64748B]">Target: {{ $targetInsp }}</span>
            </div>
            <div class="mt-3 w-full bg-slate-100 rounded-full h-2.5">
                <div class="bg-blue-600 h-2.5 rounded-full" style="width: {{ min(100, $targetInsp > 0 ? ($inspectionsCount / $targetInsp) * 100 : 0) }}%"></div>
            </div>
            <span class="mt-2 block text-xs text-[#64748B]">{{ $targetInsp > 0 ? round(($inspectionsCount / $targetInsp) * 100, 1) : 0 }}% tercapai</span>
        </div>

        <div class="rounded-xl border border-[#E2E8F0] bg-white p-6 shadow-sm">
            <h3 class="text-base font-semibold text-[#0F172A]">Planned Task Observation (PTO)</h3>
            <p class="mt-1 text-xs text-[#64748B]">Observasi tugas terencana dan kepatuhan prosedur.</p>
            <div class="mt-4 flex items-baseline justify-between">
                <span class="text-3xl font-extrabold text-emerald-600">{{ $ptoCount }}</span>
                <span class="text-sm font-medium text-[#64748B]">Target: {{ $targetPto }}</span>
            </div>
            <div class="mt-3 w-full bg-slate-100 rounded-full h-2.5">
                <div class="bg-emerald-600 h-2.5 rounded-full" style="width: {{ min(100, $targetPto > 0 ? ($ptoCount / $targetPto) * 100 : 0) }}%"></div>
            </div>
            <span class="mt-2 block text-xs text-[#64748B]">{{ $targetPto > 0 ? round(($ptoCount / $targetPto) * 100, 1) : 0 }}% tercapai</span>
        </div>

        <div class="rounded-xl border border-[#E2E8F0] bg-white p-6 shadow-sm">
            <h3 class="text-base font-semibold text-[#0F172A]">Hazard Report</h3>
            <p class="mt-1 text-xs text-[#64748B]">Pelaporan bahaya, KTA & TTA seluruh karyawan.</p>
            <div class="mt-4 flex items-baseline justify-between">
                <span class="text-3xl font-extrabold text-amber-600">{{ $hazardCount }}</span>
                <span class="text-sm font-medium text-[#64748B]">Target: {{ $targetHazard }}</span>
            </div>
            <div class="mt-3 w-full bg-slate-100 rounded-full h-2.5">
                <div class="bg-amber-500 h-2.5 rounded-full" style="width: {{ min(100, $targetHazard > 0 ? ($hazardCount / $targetHazard) * 100 : 0) }}%"></div>
            </div>
            <span class="mt-2 block text-xs text-[#64748B]">{{ $targetHazard > 0 ? round(($hazardCount / $targetHazard) * 100, 1) : 0 }}% tercapai</span>
        </div>
    </div>

    {{-- INTERACTIVE CHARTS --}}
    @php $toBars = fn (array $chart) => array_combine($chart['labels'] ?? [], $chart['data'] ?? []); @endphp
    <div class="grid gap-4 xl:grid-cols-2">
        <x-hse.chart-card title="Monthly SAP Achievement (Actual vs Target)" subtitle="Tren pemenuhan SAP bulanan">
            <x-hse.line-chart :chart="$charts['actual_vs_target']" />
        </x-hse.chart-card>
        <x-hse.chart-card title="Department Ranking" subtitle="Top pencapaian aktivitas SAP antar departemen">
            <x-ui.bar-chart :series="$toBars($charts['department_ranking'])" label="Total kegiatan per department" />
        </x-hse.chart-card>
    </div>

    <div class="grid gap-4 xl:grid-cols-2">
        <x-hse.chart-card title="Site Comparison" subtitle="Perbandingan kepatuhan SAP antar lokasi operasional">
            <x-ui.bar-chart :series="$toBars($charts['site_comparison'])" label="Total kegiatan per site" />
        </x-hse.chart-card>
        <div class="rounded-xl border border-[#E2E8F0] bg-white p-6 shadow-sm">
            <h3 class="text-base font-semibold text-[#0F172A]">Akses Cepat Submenu SAP</h3>
            <p class="mt-1 text-xs text-[#64748B]">Lihat detail pencapaian dan kinerja berdasarkan entitas.</p>
            <div class="mt-4 grid grid-cols-2 gap-3">
                <a href="{{ route('hse.dashboard.sap.achievement', 'inspection') }}" class="rounded-lg border border-slate-200 p-3 hover:bg-slate-50 transition">
                    <span class="block text-xs font-semibold text-blue-700">Inspection Achievement</span>
                    <span class="text-[11px] text-slate-500">Lihat riwayat inspeksi</span>
                </a>
                <a href="{{ route('hse.dashboard.sap.achievement', 'pto') }}" class="rounded-lg border border-slate-200 p-3 hover:bg-slate-50 transition">
                    <span class="block text-xs font-semibold text-emerald-700">PTO Achievement</span>
                    <span class="text-[11px] text-slate-500">Lihat observasi tugas</span>
                </a>
                <a href="{{ route('hse.dashboard.sap.achievement', 'hazard') }}" class="rounded-lg border border-slate-200 p-3 hover:bg-slate-50 transition">
                    <span class="block text-xs font-semibold text-amber-700">Hazard Report Achievement</span>
                    <span class="text-[11px] text-slate-500">Lihat laporan bahaya</span>
                </a>
                <a href="{{ route('hse.dashboard.sap.employee') }}" class="rounded-lg border border-slate-200 p-3 hover:bg-slate-50 transition">
                    <span class="block text-xs font-semibold text-indigo-700">Employee SAP Performance</span>
                    <span class="text-[11px] text-slate-500">Kinerja individu</span>
                </a>
                <a href="{{ route('hse.dashboard.sap.department') }}" class="rounded-lg border border-slate-200 p-3 hover:bg-slate-50 transition">
                    <span class="block text-xs font-semibold text-teal-700">Department Performance</span>
                    <span class="text-[11px] text-slate-500">Ranking departemen</span>
                </a>
                <a href="{{ route('hse.dashboard.sap.targets.index') }}" class="rounded-lg border border-slate-200 p-3 hover:bg-slate-50 transition">
                    <span class="block text-xs font-semibold text-purple-700">SAP Target Management</span>
                    <span class="text-[11px] text-slate-500">Konfigurasi kuota target</span>
                </a>
            </div>
        </div>
    </div>

    {{-- INTEGRATION NOTICE --}}
    <div class="rounded-lg border border-blue-200 bg-blue-50 p-4 text-xs text-blue-900 leading-relaxed">
        <strong>Integrasi Form Builder Terpasang:</strong> Seluruh form checklist inspeksi (SHE-FM-016 s.d SHE-FM-035), Planned Task Observation (SHE-FM-019), dan formulir pelaporan Hazard otomatis dicatat sebagai SAP actual begitu submission divalidasi. Setiap temuan atau penyimpangan otomatis menghasilkan antrean PICA.
    </div>
</div>
@endsection
