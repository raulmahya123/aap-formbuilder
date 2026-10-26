@extends('layouts.app')

@section('title', 'Employee SAP Performance')

@section('content')
<div class="ui-page space-y-6">
    @include('hse.dashboard.partials.header', [
        'title' => 'Employee SAP Performance',
        'subtitle' => 'Kinerja dan keterlibatan setiap karyawan dalam program akuntabilitas keselamatan (SAP) berdasarkan data riil.',
        'filters' => $filters,
        'options' => $options,
    ])

    <x-hse.chart-card title="Keterlibatan Karyawan (SAP)" subtitle="Statistik pemenuhan SAP per individu berdasarkan aktivitas inspeksi, PTO, dan pelaporan bahaya">
        <div class="ui-table-scroll">
            <table class="ui-table">
                <thead>
                    <tr>
                        <th>Nama Karyawan</th>
                        <th>Email / NIK</th>
                        <th>Department</th>
                        <th>Peran (Role)</th>
                        <th>Status Akun</th>
                        <th>Aktivitas Terlaksana</th>
                        <th>Pencapaian SAP</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($employees as $emp)
                        @php
                            $st = $stats[$emp->id] ?? [
                                'actual_insp' => 0,
                                'actual_pto' => 0,
                                'actual_hazard' => 0,
                                'total_actual' => 0,
                                'target' => null,
                                'achievement_pct' => null,
                            ];
                            $pct = $st['achievement_pct'];
                        @endphp
                        <tr>
                            <td>
                                <strong class="text-[#0F172A] block">{{ $emp->name }}</strong>
                                @if($emp->employee_id)
                                    <span class="text-xs text-slate-400">NIK: {{ $emp->employee_id }}</span>
                                @endif
                            </td>
                            <td>{{ $emp->email }}</td>
                            <td>{{ $emp->department?->name ?? '—' }}</td>
                            <td>
                                <span class="rounded bg-slate-100 px-2 py-0.5 text-xs font-semibold text-slate-700">
                                    {{ $emp->roleLabel() }}
                                </span>
                            </td>
                            <td>
                                @if($emp->is_active)
                                    <span class="rounded-full bg-emerald-100 px-2 py-0.5 text-xs font-semibold text-emerald-800">Aktif</span>
                                @else
                                    <span class="rounded-full bg-rose-100 px-2 py-0.5 text-xs font-semibold text-rose-800">Non-Aktif</span>
                                @endif
                            </td>
                            <td>
                                <div class="flex items-center gap-1.5 text-xs">
                                    <span class="inline-flex items-center px-1.5 py-0.5 rounded bg-blue-50 text-blue-700 font-medium" title="Inspeksi Mandiri">
                                        Insp: {{ $st['actual_insp'] }}
                                    </span>
                                    <span class="inline-flex items-center px-1.5 py-0.5 rounded bg-indigo-50 text-indigo-700 font-medium" title="Planned Task Observation">
                                        PTO: {{ $st['actual_pto'] }}
                                    </span>
                                    <span class="inline-flex items-center px-1.5 py-0.5 rounded bg-amber-50 text-amber-700 font-medium" title="Hazard / HIPO Report">
                                        HZR: {{ $st['actual_hazard'] }}
                                    </span>
                                </div>
                            </td>
                            <td>
                                @if($st['target'] !== null && $st['target'] > 0)
                                    <div>
                                        <div class="flex items-center justify-between text-xs mb-1">
                                            <span class="font-semibold text-[#0F172A]">{{ $st['total_actual'] }} / {{ $st['target'] }}</span>
                                            <span class="font-bold {{ $pct >= 80 ? 'text-emerald-700' : ($pct >= 50 ? 'text-blue-700' : 'text-amber-700') }}">{{ $pct }}%</span>
                                        </div>
                                        <div class="w-28 bg-slate-100 rounded-full h-1.5 overflow-hidden">
                                            <div class="h-1.5 rounded-full {{ $pct >= 80 ? 'bg-emerald-500' : ($pct >= 50 ? 'bg-blue-500' : 'bg-amber-500') }}" style="width: {{ min(100, $pct ?? 0) }}%"></div>
                                        </div>
                                    </div>
                                @elseif($st['total_actual'] > 0)
                                    <div class="flex items-center gap-2">
                                        <span class="rounded-full bg-blue-50 px-2 py-0.5 text-xs font-medium text-blue-700">
                                            {{ $st['total_actual'] }} kegiatan terlaksana
                                        </span>
                                    </div>
                                @else
                                    <span class="text-xs text-slate-400">Belum ada aktivitas</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <x-ui.table-empty-state :colspan="7" title="Belum ada karyawan" message="Tidak ada data karyawan ditemukan pada filter ini." />
                    @endforelse
                </tbody>
            </table>
        </div>
    </x-hse.chart-card>

    <div class="mt-4">
        {{ $employees->links() }}
    </div>
</div>
@endsection
