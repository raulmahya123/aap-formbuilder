@extends('layouts.app')

@section('title', 'Risk Dashboard — Risk Register')

@section('content')
<div class="ui-page space-y-6">
    @include('hse.dashboard.partials.header', [
        'showDashboardFilters' => true,
        'title' => 'Risk Dashboard',
        'subtitle' => 'Executive monitoring profil risiko, matriks 5×5 interaktif, dan status mitigasi kontrol.',
        'filters' => $filters,
        'options' => $options,
    ])

    {{-- Top Action Toolbar --}}
    <div class="flex flex-wrap items-center justify-between gap-3 bg-white p-4 rounded-xl border border-slate-200 shadow-sm">
        <div class="flex items-center gap-2">
            <span class="inline-flex h-3 w-3 rounded-full bg-[#1646A0]"></span>
            <span class="text-sm font-semibold text-slate-800">Risk Register Core Overview</span>
        </div>
        <div class="flex items-center gap-2">
            <x-ui.link variant="primary" icon="plus" href="{{ route('hse.dashboard.risk.create') }}">Tambah Risk Assessment</x-ui.link>
            <a href="{{ route('hse.dashboard.risk.list') }}" class="inline-flex items-center gap-1.5 rounded-lg border border-slate-300 bg-white px-3.5 py-2 text-xs font-medium text-slate-700 hover:bg-slate-50 transition">
                Lihat Semua Daftar
            </a>
        </div>
    </div>

    {{-- KPI Cards --}}
    <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4 xl:grid-cols-8">
        {{-- Total Risks --}}
        <a href="{{ route('hse.dashboard.risk.list') }}" class="group block rounded-xl border border-slate-200 bg-white p-4 shadow-sm hover:border-[#1646A0] transition">
            <p class="text-[11px] font-medium text-slate-500 uppercase tracking-wider">Total Risks</p>
            <p class="mt-2 text-2xl font-bold text-slate-900 group-hover:text-[#1646A0]">{{ number_format($totalRisks) }}</p>
            <p class="mt-1 text-[11px] text-slate-400">Seluruh asesmen aktif</p>
        </a>

        {{-- Low risks --}}
        <a href="{{ route('hse.dashboard.risk.list', ['level' => 'Low']) }}" class="group block rounded-xl border border-slate-200 bg-white p-4 shadow-sm hover:border-emerald-500 transition">
            <div class="flex items-center justify-between">
                <p class="text-[11px] font-medium text-slate-500 uppercase tracking-wider">Low</p>
                <span class="h-2 w-2 rounded-full bg-emerald-500"></span>
            </div>
            <p class="mt-2 text-2xl font-bold text-emerald-600">{{ number_format($lowRisks) }}</p>
            <p class="mt-1 text-[11px] text-slate-400">Skor 1 - 3</p>
        </a>

        {{-- Medium risks --}}
        <a href="{{ route('hse.dashboard.risk.list', ['level' => 'Medium']) }}" class="group block rounded-xl border border-slate-200 bg-white p-4 shadow-sm hover:border-amber-500 transition">
            <div class="flex items-center justify-between">
                <p class="text-[11px] font-medium text-slate-500 uppercase tracking-wider">Medium</p>
                <span class="h-2 w-2 rounded-full bg-amber-400"></span>
            </div>
            <p class="mt-2 text-2xl font-bold text-amber-600">{{ number_format($mediumRisks) }}</p>
            <p class="mt-1 text-[11px] text-slate-400">Skor 4 - 8</p>
        </a>

        {{-- High risks --}}
        <a href="{{ route('hse.dashboard.risk.list', ['level' => 'High']) }}" class="group block rounded-xl border border-slate-200 bg-white p-4 shadow-sm hover:border-orange-500 transition">
            <div class="flex items-center justify-between">
                <p class="text-[11px] font-medium text-slate-500 uppercase tracking-wider">High</p>
                <span class="h-2 w-2 rounded-full bg-orange-500"></span>
            </div>
            <p class="mt-2 text-2xl font-bold text-orange-600">{{ number_format($highRisks) }}</p>
            <p class="mt-1 text-[11px] text-slate-400">Skor 9 - 14</p>
        </a>

        {{-- Extreme risks --}}
        <a href="{{ route('hse.dashboard.risk.list', ['level' => 'Extreme']) }}" class="group block rounded-xl border border-slate-200 bg-white p-4 shadow-sm hover:border-rose-600 transition">
            <div class="flex items-center justify-between">
                <p class="text-[11px] font-medium text-slate-500 uppercase tracking-wider">Extreme</p>
                <span class="h-2 w-2 rounded-full bg-rose-600"></span>
            </div>
            <p class="mt-2 text-2xl font-bold text-rose-700">{{ number_format($extremeRisks) }}</p>
            <p class="mt-1 text-[11px] text-slate-400">Skor 15 - 25</p>
        </a>

        {{-- Open risk treatments --}}
        <a href="{{ route('hse.dashboard.risk.controls', ['status' => 'In Progress']) }}" class="group block rounded-xl border border-slate-200 bg-white p-4 shadow-sm hover:border-blue-500 transition">
            <p class="text-[11px] font-medium text-slate-500 uppercase tracking-wider">Open Treatments</p>
            <p class="mt-2 text-2xl font-bold text-[#1646A0]">{{ number_format($openTreatments) }}</p>
            <p class="mt-1 text-[11px] text-slate-400">Mitigasi berjalan</p>
        </a>

        {{-- Completed controls --}}
        <a href="{{ route('hse.dashboard.risk.controls', ['status' => 'Closed']) }}" class="group block rounded-xl border border-slate-200 bg-white p-4 shadow-sm hover:border-emerald-500 transition">
            <p class="text-[11px] font-medium text-slate-500 uppercase tracking-wider">Completed Controls</p>
            <p class="mt-2 text-2xl font-bold text-emerald-600">{{ number_format($completedControls) }}</p>
            <p class="mt-1 text-[11px] text-slate-400">Kontrol terverifikasi</p>
        </a>

        {{-- Overdue controls --}}
        <a href="{{ route('hse.dashboard.risk.controls', ['overdue' => 1]) }}" class="group block rounded-xl border border-slate-200 bg-white p-4 shadow-sm hover:border-red-600 transition">
            <p class="text-[11px] font-medium text-rose-600 uppercase tracking-wider">Overdue Controls</p>
            <p class="mt-2 text-2xl font-bold text-rose-600">{{ number_format($overdueControls) }}</p>
            <p class="mt-1 text-[11px] text-rose-500">Melewati due date</p>
        </a>
    </div>

    {{-- Interactive 5x5 Matrix & High Risk Activities Overview --}}
    <div class="grid gap-6 lg:grid-cols-12">
        {{-- 5x5 Matrix (7 cols) --}}
        <div class="lg:col-span-7 bg-white rounded-xl border border-slate-200 p-5 shadow-sm">
            <div class="flex items-center justify-between border-b border-slate-100 pb-4 mb-4">
                <div>
                    <h3 class="text-base font-semibold text-slate-900">Interactive 5×5 Risk Matrix</h3>
                    <p class="text-xs text-slate-500">Likelihood (L) vs Consequence (C). Klik sel matriks untuk memfilter detail risiko.</p>
                </div>
                <div class="flex items-center gap-2 text-xs">
                    <span class="inline-flex items-center gap-1"><span class="h-2.5 w-2.5 rounded bg-[#16A34A]"></span>L</span>
                    <span class="inline-flex items-center gap-1"><span class="h-2.5 w-2.5 rounded bg-[#FCD34D]"></span>M</span>
                    <span class="inline-flex items-center gap-1"><span class="h-2.5 w-2.5 rounded bg-[#F59E0B]"></span>H</span>
                    <span class="inline-flex items-center gap-1"><span class="h-2.5 w-2.5 rounded bg-[#DC2626]"></span>E</span>
                </div>
            </div>

            @php
                $cellBg = function (int $score) {
                    return match (true) {
                        $score >= 15 => ['#DC2626', '#ffffff', 'Extreme'],
                        $score >= 9 => ['#F59E0B', '#111827', 'High'],
                        $score >= 4 => ['#FCD34D', '#111827', 'Medium'],
                        default => ['#16A34A', '#ffffff', 'Low'],
                    };
                };
            @endphp

            <div class="overflow-x-auto">
                <div class="min-w-[420px]">
                    <div class="grid gap-1.5" style="grid-template-columns: 80px repeat(5, minmax(0, 1fr));">
                        <div></div>
                        @foreach(range(1, 5) as $c)
                            <div class="text-center text-xs font-semibold text-slate-600 pb-1">
                                C{{ $c }}
                                <span class="block text-[10px] font-normal text-slate-400">
                                    {{ ['Insignif', 'Minor', 'Moderate', 'Major', 'Catastr'][$c-1] }}
                                </span>
                            </div>
                        @endforeach

                        @for($l = 5; $l >= 1; $l--)
                            <div class="flex flex-col justify-center text-right pr-2 text-xs font-semibold text-slate-600">
                                <span>L{{ $l }}</span>
                                <span class="text-[10px] font-normal text-slate-400">
                                    {{ ['Rare', 'Unlikely', 'Possible', 'Likely', 'Almost'][$l-1] }}
                                </span>
                            </div>
                            @foreach(range(1, 5) as $c)
                                @php
                                    $score = $l * $c;
                                    [$bg, $fg, $cat] = $cellBg($score);
                                    $count = (int) ($matrix[$l][$c] ?? 0);
                                @endphp
                                <a href="{{ route('hse.dashboard.risk.list', ['likelihood' => $l, 'consequence' => $c]) }}"
                                   class="group relative flex aspect-square flex-col items-center justify-center rounded-lg border border-black/5 p-2 font-bold transition hover:scale-105 hover:shadow-md cursor-pointer"
                                   style="background-color: {{ $bg }}; color: {{ $fg }}; opacity: {{ $count > 0 ? '1' : '0.4' }}"
                                   title="Likelihood {{ $l }} × Consequence {{ $c }} = Skor {{ $score }} ({{ $cat }}): {{ $count }} risiko">
                                    <span class="text-lg leading-none">{{ $count }}</span>
                                    <span class="text-[10px] opacity-80 mt-1 font-medium">S: {{ $score }}</span>
                                    <div class="absolute inset-0 rounded-lg bg-black/10 opacity-0 group-hover:opacity-100 transition flex items-center justify-center">
                                        <span class="text-[9px] bg-black/80 text-white px-1.5 py-0.5 rounded shadow">View</span>
                                    </div>
                                </a>
                            @endforeach
                        @endfor

                        <div></div>
                        <div class="col-span-5 text-center text-xs font-semibold text-slate-500 pt-2 tracking-wide uppercase">
                            ← Consequence / Dampak Keparahan →
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- High Risk Activities Quick Feed (5 cols) --}}
        <div class="lg:col-span-5 bg-white rounded-xl border border-slate-200 p-5 shadow-sm flex flex-col justify-between">
            <div>
                <div class="flex items-center justify-between border-b border-slate-100 pb-4 mb-4">
                    <div>
                        <h3 class="text-base font-semibold text-slate-900">High & Extreme Risks</h3>
                        <p class="text-xs text-slate-500">Risiko berprioritas tinggi yang memerlukan tindakan mitigasi cepat.</p>
                    </div>
                    <a href="{{ route('hse.dashboard.risk.high_activities') }}" class="text-xs font-semibold text-[#1646A0] hover:underline">
                        Lihat Selengkapnya →
                    </a>
                </div>

                <div class="space-y-3">
                    @forelse($highRiskRows as $risk)
                        <div class="p-3 rounded-lg border border-slate-100 bg-slate-50/70 hover:bg-white hover:border-slate-300 transition">
                            <div class="flex items-start justify-between gap-2">
                                <div>
                                    <span class="text-xs font-bold text-slate-800">{{ $risk->activity }}</span>
                                    <p class="text-xs text-slate-500 mt-0.5">Bahaya: {{ $risk->hazard }}</p>
                                </div>
                                <span class="px-2 py-0.5 rounded text-[11px] font-bold text-white {{ $risk->risk_level === 'Extreme' ? 'bg-[#DC2626]' : 'bg-[#F59E0B]' }}">
                                    {{ $risk->risk_level }} ({{ $risk->inherent_score }})
                                </span>
                            </div>
                            <div class="mt-2.5 flex items-center justify-between text-[11px] text-slate-500 border-t border-slate-200/60 pt-2">
                                <span>Site: <strong>{{ $risk->site?->code ?? $risk->site?->name ?? '—' }}</strong></span>
                                <span>PIC: <strong>{{ $risk->owner?->name ?? 'Belum Ditugaskan' }}</strong></span>
                                <span class="text-slate-400">{{ $risk->treatment_status ?: 'Open' }}</span>
                            </div>
                        </div>
                    @empty
                        <div class="py-10 text-center text-slate-400">
                            <svg class="mx-auto h-8 w-8 text-slate-300" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                            <p class="mt-2 text-xs font-medium">Tidak ada risiko High atau Extreme yang terdeteksi.</p>
                        </div>
                    @endforelse
                </div>
            </div>

            <div class="mt-4 pt-3 border-t border-slate-100 flex items-center justify-between text-xs text-slate-500">
                <span>Sinkronisasi otomatis dari Hazard Report & Form Risk Assessment.</span>
            </div>
        </div>
    </div>

    {{-- Recent Risk Controls Monitoring Table --}}
    <div class="bg-white rounded-xl border border-slate-200 shadow-sm overflow-hidden">
        <div class="flex flex-wrap items-center justify-between gap-3 p-4 border-b border-slate-100">
            <div>
                <h3 class="text-base font-semibold text-slate-900">Risk Control Monitoring (PICA Actions)</h3>
                <p class="text-xs text-slate-500">Pelaksanaan kontrol risiko, due date, bukti implementasi dan verifikasi.</p>
            </div>
            <a href="{{ route('hse.dashboard.risk.controls') }}" class="text-xs font-semibold text-[#1646A0] hover:underline">
                Monitoring Lengkap →
            </a>
        </div>

        <div class="ui-table-scroll">
            <table class="ui-table">
                <thead>
                    <tr>
                        <th>PICA No</th>
                        <th>Judul Kontrol / Mitigasi</th>
                        <th>Site</th>
                        <th>PIC (Assignee)</th>
                        <th>Due Date</th>
                        <th>Prioritas</th>
                        <th>Status</th>
                        <th>Evidence</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($recentControls as $ctrl)
                        <tr>
                            <td class="font-bold text-slate-900">{{ $ctrl->action_no }}</td>
                            <td>
                                <div class="font-medium text-slate-800">{{ $ctrl->title }}</div>
                                <div class="text-xs text-slate-400 truncate max-w-xs">{{ $ctrl->description }}</div>
                            </td>
                            <td>{{ $ctrl->site?->code ?? $ctrl->site?->name ?? '—' }}</td>
                            <td>{{ $ctrl->assignee?->name ?? '—' }}</td>
                            <td class="{{ $ctrl->due_date && $ctrl->due_date < now() && $ctrl->status !== 'Closed' ? 'text-rose-600 font-bold' : '' }}">
                                {{ $ctrl->due_date ? \Carbon\Carbon::parse($ctrl->due_date)->format('d M Y') : '—' }}
                            </td>
                            <td><x-hse.status :value="$ctrl->priority" /></td>
                            <td><x-hse.status :value="$ctrl->status" /></td>
                            <td><x-ui.table-actions>
                                @if($ctrl->evidence_path)
                                    <x-ui.icon-button action="view" label="Bukti" href="{{ asset('storage/' . $ctrl->evidence_path) }}" target="_blank" />
                                @else
                                    <span class="text-slate-300 text-xs">—</span>
                                @endif
                            </x-ui.table-actions></td>
                        </tr>
                    @empty
                        <x-ui.table-empty-state :colspan="8" title="Belum ada tindakan kontrol risiko" message="Kontrol akan muncul otomatis saat asesmen High/Extreme dibuat." />
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

</div>
@endsection
