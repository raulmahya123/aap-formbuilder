@extends('layouts.app')

@section('title', 'High Risk Activities — MANDALA')

@section('content')
<div class="ui-page space-y-6">
    @include('hse.dashboard.partials.header', [
        'title' => 'High Risk Activities',
        'subtitle' => 'Pemantauan aktivitas berisiko tinggi (High & Extreme) serta kepatuhan Critical Control Management.',
        'filters' => $filters,
        'options' => $options,
    ])

    {{-- Filter Search --}}
    <div class="flex flex-wrap items-center justify-between gap-3 bg-white p-4 rounded-xl border border-slate-200 shadow-sm">
        <form method="GET" action="{{ route('hse.dashboard.risk.high_activities') }}" class="flex items-center gap-2">
            <input type="text" name="q" value="{{ request('q') }}" placeholder="Cari aktivitas atau bahaya..." class="text-xs rounded-lg border-slate-300 focus:border-[#1646A0] w-64">
            <button type="submit" class="rounded-lg bg-slate-800 px-3 py-2 text-xs font-semibold text-white hover:bg-slate-700 transition">Cari</button>
            @if(request('q'))
                <a href="{{ route('hse.dashboard.risk.high_activities') }}" class="text-xs text-slate-500 hover:text-slate-800 py-2 px-1">Reset</a>
            @endif
        </form>
        <div class="flex items-center gap-2 text-xs">
            <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg bg-rose-50 text-rose-700 border border-rose-200 font-semibold">
                <span class="h-2 w-2 rounded-full bg-rose-600"></span>
                Critical Focus Area
            </span>
        </div>
    </div>

    {{-- High Risk Activities Table --}}
    <div class="bg-white rounded-xl border border-slate-200 shadow-sm overflow-hidden">
        <div class="p-4 border-b border-slate-100 flex items-center justify-between">
            <div>
                <h3 class="text-base font-semibold text-slate-900">Aktivitas Risiko Tinggi & Ekstrem</h3>
                <p class="text-xs text-slate-500">Tabel monitoring aktivitas dengan skor risiko inherent/residual ≥ 9.</p>
            </div>
            <span class="text-xs font-semibold text-slate-600">Total: {{ $activities->total() }} aktivitas</span>
        </div>

        <div class="ui-table-scroll">
            <table class="ui-table">
                <thead>
                    <tr>
                        <th>Activity ID</th>
                        <th>Activity Name</th>
                        <th>Department</th>
                        <th>Site</th>
                        <th>Hazard</th>
                        <th>Consequence</th>
                        <th>Initial Risk (Score/Level)</th>
                        <th>Control Measure</th>
                        <th>Residual Risk</th>
                        <th>Responsible PIC</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($activities as $act)
                        @php
                            $initColor = $act->risk_level === 'Extreme' ? 'bg-rose-100 text-rose-800 border-rose-300' : 'bg-orange-100 text-orange-800 border-orange-300';
                            $resLevel = \App\Models\HseRiskAssessment::levelForScore($act->residual_score ?: $act->inherent_score);
                            $resColor = match($resLevel) {
                                'Extreme' => 'bg-rose-100 text-rose-800 border-rose-300',
                                'High' => 'bg-orange-100 text-orange-800 border-orange-300',
                                'Medium' => 'bg-amber-100 text-amber-800 border-amber-300',
                                default => 'bg-emerald-100 text-emerald-800 border-emerald-300',
                            };
                        @endphp
                        <tr class="hover:bg-slate-50/70 transition">
                            <td class="font-bold text-slate-900">ACT-{{ str_pad($act->id, 4, '0', STR_PAD_LEFT) }}</td>
                            <td class="font-medium text-slate-900 max-w-xs">{{ $act->activity }}</td>
                            <td>{{ $act->department?->name ?? '—' }}</td>
                            <td>{{ $act->site?->code ?? $act->site?->name ?? '—' }}</td>
                            <td class="text-slate-600 max-w-xs">{{ $act->hazard }}</td>
                            <td class="text-xs font-semibold text-slate-700">Level C{{ $act->consequence }}</td>
                            <td>
                                <span class="inline-flex items-center px-2 py-0.5 rounded text-[11px] font-bold border {{ $initColor }}">
                                    {{ $act->risk_level }} ({{ $act->inherent_score }})
                                </span>
                            </td>
                            <td class="text-xs text-slate-600 max-w-xs">
                                <div class="font-medium text-slate-700">{{ $act->treatment ?: $act->existing_controls ?: '—' }}</div>
                            </td>
                            <td>
                                <span class="inline-flex items-center px-2 py-0.5 rounded text-[11px] font-bold border {{ $resColor }}">
                                    {{ $resLevel }} ({{ $act->residual_score ?: $act->inherent_score }})
                                </span>
                            </td>
                            <td class="text-xs font-medium text-slate-800">{{ $act->owner?->name ?? 'Unassigned' }}</td>
                            <td><x-hse.status :value="$act->treatment_status ?: $act->status" /></td>
                        </tr>
                    @empty
                        <x-ui.table-empty-state :colspan="11" title="Tidak ada aktivitas risiko tinggi" message="Tidak ditemukan asesmen risiko aktif dengan tingkat High atau Extreme." />
                    @endforelse
                </tbody>
            </table>
        </div>

    </div>

    @if($activities->hasPages())
        <div class="mt-4">
            {{ $activities->links() }}
        </div>
    @endif

    {{-- Critical Control Management (CCM) Reports Integration Feed --}}
    <div class="bg-white rounded-xl border border-slate-200 shadow-sm overflow-hidden">
        <div class="p-4 border-b border-slate-100 flex items-center justify-between">
            <div>
                <h3 class="text-base font-semibold text-slate-900">Critical Control Management (CCM) Monitoring Feed</h3>
                <p class="text-xs text-slate-500">Pekerjaan kritis operasional (Kendaraan, Izin Kerja, Tebing/Disposal, Air/Lumpur, Chainsaw, LOTO, Lifting, Blasting).</p>
            </div>
            @if(Route::has('ccm-reports.index'))
                <a href="{{ route('ccm-reports.index') }}" class="text-xs font-semibold text-[#1646A0] hover:underline">
                    Buka Modul CCM Lengkap →
                </a>
            @endif
        </div>

        <div class="ui-table-scroll">
            <table class="ui-table">
                <thead>
                    <tr>
                        <th>Waktu Pelaporan</th>
                        <th>Jobsite</th>
                        <th>Pelapor</th>
                        <th>Pekerjaan Kritis Terdeteksi</th>
                        <th>Tindakan Lanjut</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($ccmReports as $ccm)
                        @php
                            $kritisList = [];
                            if ($ccm->kendaraan_ada_kegiatan) $kritisList[] = 'Kendaraan & Alat Berat';
                            if ($ccm->izin_kerja_ada) $kritisList[] = 'Izin Kerja Khusus';
                            if ($ccm->tebing_ada) $kritisList[] = 'Tebing / Disposal';
                            if ($ccm->air_lumpur_ada) $kritisList[] = 'Air & Lumpur';
                            if ($ccm->chainsaw_ada) $kritisList[] = 'Chainsaw';
                            if ($ccm->loto_ada) $kritisList[] = 'LOTO / Isolasi';
                            if ($ccm->lifting_ada) $kritisList[] = 'Lifting Operation';
                            if ($ccm->blasting_ada) $kritisList[] = 'Blasting / Peledakan';
                        @endphp
                        <tr>
                            <td class="font-medium text-slate-900">{{ $ccm->waktu_pelaporan ? \Carbon\Carbon::parse($ccm->waktu_pelaporan)->format('d M Y H:i') : '—' }}</td>
                            <td>{{ $ccm->jobsite }}</td>
                            <td>{{ $ccm->nama_pelapor }}</td>
                            <td>
                                <div class="flex flex-wrap gap-1">
                                    @forelse($kritisList as $k)
                                        <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-semibold bg-blue-50 text-[#1646A0] border border-blue-200">
                                            {{ $k }}
                                        </span>
                                    @empty
                                        <span class="text-xs text-slate-400">Pekerjaan Umum</span>
                                    @endforelse
                                </div>
                            </td>
                            <td>
                                @if(Route::has('ccm-reports.show'))
                                    <a href="{{ route('ccm-reports.show', $ccm->id) }}" class="text-xs font-medium text-[#1646A0] hover:underline">Detail Report</a>
                                @else
                                    <span class="text-xs text-slate-400">Tercatat</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <x-ui.table-empty-state :colspan="5" title="Belum ada report CCM" message="Data pelaporan Critical Control Management akan tampil di sini." />
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
