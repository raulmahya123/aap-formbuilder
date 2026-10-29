@extends('layouts.app')

@section('title', 'High Risk Activities — MANDALA')

@section('content')
<div class="ui-page space-y-6">
    @include('hse.dashboard.partials.header', [
        'title' => 'High Risk Activities',
        'subtitle' => 'Pemantauan aktivitas berisiko tinggi (High & Extreme) serta kepatuhan Critical Control Management.',
        'filters' => $filters,
        'options' => $options,
        'panel' => $selectedPanel,
        'showDashboardFilters' => ! $selectedPanel,
    ])

    @php
        $panelUrl = fn (string $panel) => route('hse.dashboard.risk.high_activities', array_merge(request()->except(['panel', 'page', 'q']), ['panel' => $panel]));
    @endphp

    <section class="grid gap-4 sm:grid-cols-2 xl:grid-cols-3" aria-label="Pilih daftar monitoring">
        <a href="{{ $panelUrl('high') }}" class="group rounded-lg border border-zinc-300 bg-white p-5 transition hover:border-orange-700 hover:ring-1 hover:ring-orange-700 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-blue-700">
            <div class="flex items-center justify-between gap-3">
                <span class="inline-flex h-10 w-10 items-center justify-center rounded-lg bg-orange-100 text-orange-800"><x-ui.lucide name="alert-triangle" /></span>
                <span class="text-2xl font-semibold text-zinc-900">{{ number_format($highCount) }}</span>
            </div>
            <h2 class="mt-4 text-base font-semibold text-zinc-900">Risiko Tinggi</h2>
            <p class="mt-1 text-sm text-zinc-600">Aktivitas dengan level High yang memerlukan mitigasi.</p>
            <span class="mt-4 inline-flex items-center gap-2 text-sm font-medium text-blue-700">Lihat daftar <x-ui.lucide name="chevron-right" /></span>
        </a>

        <a href="{{ $panelUrl('extreme') }}" class="group rounded-lg border border-zinc-300 bg-white p-5 transition hover:border-red-700 hover:ring-1 hover:ring-red-700 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-blue-700">
            <div class="flex items-center justify-between gap-3">
                <span class="inline-flex h-10 w-10 items-center justify-center rounded-lg bg-red-100 text-red-800"><x-ui.lucide name="alert-triangle" /></span>
                <span class="text-2xl font-semibold text-zinc-900">{{ number_format($extremeCount) }}</span>
            </div>
            <h2 class="mt-4 text-base font-semibold text-zinc-900">Risiko Ekstrem</h2>
            <p class="mt-1 text-sm text-zinc-600">Aktivitas level Extreme dengan prioritas penanganan tertinggi.</p>
            <span class="mt-4 inline-flex items-center gap-2 text-sm font-medium text-blue-700">Lihat daftar <x-ui.lucide name="chevron-right" /></span>
        </a>

        <a href="{{ $panelUrl('ccm') }}" class="group rounded-lg border border-zinc-300 bg-white p-5 transition hover:border-blue-700 hover:ring-1 hover:ring-blue-700 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-blue-700">
            <div class="flex items-center justify-between gap-3">
                <span class="inline-flex h-10 w-10 items-center justify-center rounded-lg bg-blue-100 text-blue-800"><x-ui.lucide name="shield" /></span>
                <span class="text-2xl font-semibold text-zinc-900">{{ number_format($ccmCount) }}</span>
            </div>
            <h2 class="mt-4 text-base font-semibold text-zinc-900">Laporan Critical Control</h2>
            <p class="mt-1 text-sm text-zinc-600">Laporan aktivitas kritis dari modul Critical Control Management.</p>
            <span class="mt-4 inline-flex items-center gap-2 text-sm font-medium text-blue-700">Lihat daftar <x-ui.lucide name="chevron-right" /></span>
        </a>
    </section>

    @if($selectedPanel)
    <div class="flex flex-wrap items-center justify-between gap-3">
        <div>
            <h2 class="text-lg font-semibold text-zinc-900">
                {{ match($selectedPanel) { 'high' => 'Daftar Risiko Tinggi', 'extreme' => 'Daftar Risiko Ekstrem', default => 'Laporan Critical Control' } }}
            </h2>
            <p class="text-sm text-zinc-600">Pilih kartu lain untuk berpindah daftar.</p>
        </div>
        <x-ui.link href="{{ route('hse.dashboard.risk.high_activities', request()->except(['panel', 'page', 'q'])) }}" icon="chevron-left">Kembali ke ringkasan</x-ui.link>
    </div>

    {{-- Filter Search --}}
    <div class="ui-filter-panel ui-module-filter-toolbar">
        <x-ui.table-toolbar action="{{ route('hse.dashboard.risk.high_activities', array_merge(request()->except(['page', 'q']), ['panel' => $selectedPanel])) }}">
            <x-ui.table-search name="q" :value="request('q')" :auto-submit="false" aria-label="Cari data monitoring" :placeholder="$selectedPanel === 'ccm' ? 'Cari jobsite atau pelapor…' : 'Cari aktivitas atau bahaya…'" />
            @if($selectedPanel !== 'ccm')
                <x-hse.module-scope-filters :options="$options" :filters="$filters" />
            @endif
        <x-slot:actions><x-ui.button type="submit" variant="primary" icon="filter">Terapkan</x-ui.button><x-ui.link href="{{ route('hse.dashboard.risk.high_activities', ['panel' => $selectedPanel]) }}" icon="rotate-ccw">Reset</x-ui.link></x-slot:actions></x-ui.table-toolbar>
        <div class="flex items-center gap-2 text-xs">
            <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg bg-rose-50 text-rose-700 border border-rose-200 font-semibold">
                <span class="h-2 w-2 rounded-full bg-rose-600"></span>
                Critical Focus Area
            </span>
        </div>
    </div>
    @endif

    {{-- High Risk Activities Table --}}
    @if(in_array($selectedPanel, ['high', 'extreme'], true))
    <div class="bg-white rounded-xl border border-slate-200 shadow-sm overflow-hidden">
        <div class="p-4 border-b border-slate-100 flex items-center justify-between">
            <div>
                <h3 class="text-base font-semibold text-slate-900">{{ $selectedPanel === 'high' ? 'Aktivitas Risiko Tinggi' : 'Aktivitas Risiko Ekstrem' }}</h3>
                <p class="text-xs text-slate-500">Tabel aktivitas sesuai tingkat risiko yang dipilih.</p>
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
                        <x-ui.table-empty-state :colspan="11" :title="$selectedPanel === 'high' ? 'Tidak ada aktivitas risiko tinggi' : 'Tidak ada aktivitas risiko ekstrem'" message="Tidak ada data pada daftar ini untuk filter yang dipilih." />
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="ui-pagination-footer">
            <x-ui.table-pagination :paginator="$activities" />
        </div>
    </div>
    @endif

    {{-- Critical Control Management (CCM) Reports Integration Feed --}}
    @if($selectedPanel === 'ccm')
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
                            <td><x-ui.table-actions>
                                @if(Route::has('ccm-reports.show'))
                                    <x-ui.icon-button action="view" label="Detail Report" href="{{ route('ccm-reports.show', $ccm->id) }}" />
                                @else
                                    <span class="text-xs text-slate-400">Tercatat</span>
                                @endif
                            </x-ui.table-actions></td>
                        </tr>
                    @empty
                        <x-ui.table-empty-state :colspan="5" title="Belum ada report CCM" message="Data pelaporan Critical Control Management akan tampil di sini." />
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="ui-pagination-footer"><x-ui.table-pagination :paginator="$ccmReports" /></div>
    </div>
    @endif
</div>
@endsection
