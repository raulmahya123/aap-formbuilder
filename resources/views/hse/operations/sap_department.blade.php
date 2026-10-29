@extends('layouts.app')

@section('title', 'Department SAP Performance')

@section('content')
<div class="ui-page space-y-6">
    @include('hse.dashboard.partials.header', [
        'filterMode' => 'listing',
        'showDashboardFilters' => true,
        'title' => 'Department SAP Performance',
        'subtitle' => 'Pemeringkatan dan pencapaian akuntabilitas keselamatan per departemen operasional berdasarkan data riil.',
        'filters' => $filters,
        'options' => $options,
    ])

    @php
        $totalDeptCount = count($rows);
        $totalActivities = collect($rows)->sum('total_actual');
        $totalTarget = collect($rows)->sum('target');
        $overallRate = $totalTarget > 0 ? round(($totalActivities / $totalTarget) * 100, 1) : null;
    @endphp

    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
        <div class="bg-white rounded-xl border border-slate-200 p-4 shadow-sm">
            <p class="text-xs font-medium text-slate-500 uppercase tracking-wider">Total Departemen Terdata</p>
            <p class="text-2xl font-bold text-[#0F172A] mt-1">{{ number_format($totalDeptCount) }}</p>
            <p class="text-xs text-slate-400 mt-0.5">Departemen aktif operasional</p>
        </div>
        <div class="bg-white rounded-xl border border-slate-200 p-4 shadow-sm">
            <p class="text-xs font-medium text-slate-500 uppercase tracking-wider">Total Realisasi Kegiatan</p>
            <p class="text-2xl font-bold text-[#1646A0] mt-1">{{ number_format($totalActivities) }}</p>
            <p class="text-xs text-slate-400 mt-0.5">Inspeksi, PTO, dan Laporan Bahaya (HIPO)</p>
        </div>
        <div class="bg-white rounded-xl border border-slate-200 p-4 shadow-sm">
            <p class="text-xs font-medium text-slate-500 uppercase tracking-wider">Rata-rata Pencapaian Target</p>
            <p class="text-2xl font-bold {{ $overallRate !== null && $overallRate >= 80 ? 'text-emerald-600' : 'text-slate-700' }} mt-1">
                {{ $overallRate !== null ? $overallRate . '%' : '—' }}
            </p>
            <p class="text-xs text-slate-400 mt-0.5">{{ $totalTarget > 0 ? "Target akumulatif: {$totalTarget}" : 'Target belum dikonfigurasi' }}</p>
        </div>
    </div>

    <x-hse.chart-card title="Ranking & Kinerja Departemen" subtitle="Pemenuhan inspeksi, observasi tugas terencana (PTO), dan hazard report aktual">
        <div class="ui-table-scroll">
            <table class="ui-table">
                <thead>
                    <tr>
                        <th class="w-16">Peringkat</th>
                        <th>Nama Department</th>
                        <th>Kode</th>
                        <th>Personil</th>
                        <th>Breakdown Kegiatan (Aktual)</th>
                        <th>Target vs Realisasi</th>
                        <th>Status Pencapaian</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($rows as $row)
                        @php $dept = $row['department']; @endphp
                        <tr>
                            <td>
                                <span class="inline-flex items-center justify-center w-7 h-7 rounded-full text-xs font-bold {{ $loop->iteration <= 3 ? 'bg-amber-100 text-amber-800 border border-amber-300' : 'bg-slate-100 text-slate-700' }}">
                                    #{{ $loop->iteration }}
                                </span>
                            </td>
                            <td>
                                <strong class="text-[#0F172A] block">{{ $dept->name }}</strong>
                            </td>
                            <td><code>{{ $dept->code ?? '—' }}</code></td>
                            <td>{{ number_format($dept->users_count) }} orang</td>
                            <td>
                                <div class="flex items-center gap-2 text-xs">
                                    <span class="inline-flex items-center px-1.5 py-0.5 rounded bg-blue-50 text-blue-700 font-medium" title="Inspeksi">
                                        Insp: {{ $row['actual_insp'] }}
                                    </span>
                                    <span class="inline-flex items-center px-1.5 py-0.5 rounded bg-indigo-50 text-indigo-700 font-medium" title="PTO">
                                        PTO: {{ $row['actual_pto'] }}
                                    </span>
                                    <span class="inline-flex items-center px-1.5 py-0.5 rounded bg-amber-50 text-amber-700 font-medium" title="Hazard/HIPO">
                                        HZR: {{ $row['actual_hazard'] }}
                                    </span>
                                </div>
                            </td>
                            <td>
                                @if($row['target'] > 0)
                                    <div>
                                        <div class="flex items-center justify-between text-xs mb-1">
                                            <span class="font-semibold text-[#0F172A]">{{ $row['total_actual'] }} / {{ $row['target'] }}</span>
                                            <span class="font-bold {{ ($row['achievement_rate'] ?? 0) >= 80 ? 'text-emerald-700' : 'text-slate-600' }}">{{ $row['achievement_rate'] }}%</span>
                                        </div>
                                        <div class="w-28 bg-slate-100 rounded-full h-1.5 overflow-hidden">
                                            <div class="h-1.5 rounded-full {{ ($row['achievement_rate'] ?? 0) >= 80 ? 'bg-emerald-500' : (($row['achievement_rate'] ?? 0) >= 50 ? 'bg-blue-500' : 'bg-amber-500') }}" style="width: {{ min(100, $row['achievement_rate'] ?? 0) }}%"></div>
                                        </div>
                                    </div>
                                @else
                                    <span class="text-xs text-slate-500">{{ $row['total_actual'] }} kegiatan <span class="text-slate-400">(Tanpa target)</span></span>
                                @endif
                            </td>
                            <td>
                                <span class="rounded-full px-2.5 py-0.5 text-xs font-semibold {{ $row['status_color'] }}">
                                    {{ $row['status_label'] }}
                                </span>
                            </td>
                        </tr>
                    @empty
                        <x-ui.table-empty-state :colspan="7" title="Belum ada departemen" message="Tidak ada data departemen ditemukan pada filter ini." />
                    @endforelse
                </tbody>
            </table>
        </div>
    </x-hse.chart-card>
</div>
@endsection
