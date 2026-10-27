@extends('layouts.app')

@section('title', 'PICA Dashboard — MANDALA')

@section('content')
<div class="ui-page space-y-6">
    @include('hse.dashboard.partials.header', [
        'title' => 'PICA Management Dashboard',
        'subtitle' => 'Pusat kendali tindakan korektif & preventif terpadu (Audit, Investigasi, Inspeksi, PTO, Hazard Report).',
        'filters' => $filters,
        'options' => $options,
    ])

    {{-- Top Action Toolbar --}}
    <div class="flex flex-wrap items-center justify-between gap-3 bg-white p-4 rounded-xl border border-slate-200 shadow-sm">
        <div class="flex items-center gap-2">
            <span class="inline-flex h-3 w-3 rounded-full bg-[#1646A0]"></span>
            <span class="text-sm font-semibold text-slate-800">Unified Corrective Action Engine</span>
        </div>
        <div class="flex items-center gap-2">
            <a href="{{ route('hse.dashboard.pica.list') }}" class="inline-flex items-center gap-1.5 rounded-lg border border-slate-300 bg-white px-3.5 py-2 text-xs font-semibold text-slate-700 hover:bg-slate-50 transition">
                Semua Tindakan PICA
            </a>
            <a href="{{ route('hse.dashboard.pica.list', 'verification') }}" class="inline-flex items-center gap-1.5 rounded-lg bg-[#1646A0] px-4 py-2 text-xs font-semibold text-white shadow-sm hover:bg-[#123880] transition">
                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                Antrian Verifikasi ({{ $waitingVerification }})
            </a>
        </div>
    </div>

    {{-- 8 KPI CARDS --}}
    <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4 xl:grid-cols-8">
        {{-- Total PICA --}}
        <a href="{{ route('hse.dashboard.pica.list') }}" class="group block rounded-xl border border-slate-200 bg-white p-4 shadow-sm hover:border-[#1646A0] transition">
            <p class="text-[11px] font-medium text-slate-500 uppercase tracking-wider">Total PICA</p>
            <p class="mt-2 text-2xl font-bold text-slate-900 group-hover:text-[#1646A0]">{{ number_format($totalPica) }}</p>
            <p class="mt-1 text-[11px] text-slate-400">Seluruh sumber</p>
        </a>

        {{-- Open --}}
        <a href="{{ route('hse.dashboard.pica.list', ['status' => 'Open']) }}" class="group block rounded-xl border border-slate-200 bg-white p-4 shadow-sm hover:border-slate-500 transition">
            <div class="flex items-center justify-between">
                <p class="text-[11px] font-medium text-slate-500 uppercase tracking-wider">Open</p>
                <span class="h-2 w-2 rounded-full bg-slate-400"></span>
            </div>
            <p class="mt-2 text-2xl font-bold text-slate-700">{{ number_format($open) }}</p>
            <p class="mt-1 text-[11px] text-slate-400">Baru diterbitkan</p>
        </a>

        {{-- In Progress --}}
        <a href="{{ route('hse.dashboard.pica.list', ['status' => 'In Progress']) }}" class="group block rounded-xl border border-slate-200 bg-white p-4 shadow-sm hover:border-blue-500 transition">
            <div class="flex items-center justify-between">
                <p class="text-[11px] font-medium text-slate-500 uppercase tracking-wider">In Progress</p>
                <span class="h-2 w-2 rounded-full bg-blue-500"></span>
            </div>
            <p class="mt-2 text-2xl font-bold text-blue-600">{{ number_format($inProgress) }}</p>
            <p class="mt-1 text-[11px] text-slate-400">Tindakan berjalan</p>
        </a>

        {{-- Waiting Verification --}}
        <a href="{{ route('hse.dashboard.pica.list', 'verification') }}" class="group block rounded-xl border border-slate-200 bg-white p-4 shadow-sm hover:border-amber-500 transition">
            <div class="flex items-center justify-between">
                <p class="text-[11px] font-medium text-slate-500 uppercase tracking-wider">Waiting Verif</p>
                <span class="h-2 w-2 rounded-full bg-amber-500"></span>
            </div>
            <p class="mt-2 text-2xl font-bold text-amber-600">{{ number_format($waitingVerification) }}</p>
            <p class="mt-1 text-[11px] text-slate-400">Evidence disubmit</p>
        </a>

        {{-- Closed --}}
        <a href="{{ route('hse.dashboard.pica.list', ['status' => 'Closed']) }}" class="group block rounded-xl border border-slate-200 bg-white p-4 shadow-sm hover:border-emerald-500 transition">
            <div class="flex items-center justify-between">
                <p class="text-[11px] font-medium text-slate-500 uppercase tracking-wider">Closed</p>
                <span class="h-2 w-2 rounded-full bg-emerald-500"></span>
            </div>
            <p class="mt-2 text-2xl font-bold text-emerald-600">{{ number_format($closed) }}</p>
            <p class="mt-1 text-[11px] text-slate-400">Telah terverifikasi</p>
        </a>

        {{-- Overdue --}}
        <a href="{{ route('hse.dashboard.pica.list', 'overdue') }}" class="group block rounded-xl border border-slate-200 bg-white p-4 shadow-sm hover:border-rose-500 transition">
            <div class="flex items-center justify-between">
                <p class="text-[11px] font-medium text-rose-600 uppercase tracking-wider">Overdue</p>
                <span class="h-2 w-2 rounded-full bg-rose-600"></span>
            </div>
            <p class="mt-2 text-2xl font-bold text-rose-600">{{ number_format($overdue) }}</p>
            <p class="mt-1 text-[11px] text-rose-400">Lewat target waktu</p>
        </a>

        {{-- Closure Rate --}}
        <div class="rounded-xl border border-slate-200 bg-white p-4 shadow-sm">
            <p class="text-[11px] font-medium text-slate-500 uppercase tracking-wider">Closure Rate</p>
            <p class="mt-2 text-2xl font-bold text-slate-900">{{ $closureRate }}%</p>
            <p class="mt-1 text-[11px] text-slate-400">Target ≥ 90%</p>
        </div>

        {{-- Average Completion Time --}}
        <div class="rounded-xl border border-slate-200 bg-white p-4 shadow-sm">
            <p class="text-[11px] font-medium text-slate-500 uppercase tracking-wider">Avg Closure</p>
            <p class="mt-2 text-2xl font-bold text-slate-900">{{ $avgDays }} <span class="text-xs font-normal text-slate-500">hari</span></p>
            <p class="mt-1 text-[11px] text-slate-400">Waktu penyelesaian</p>
        </div>
    </div>

    {{-- GRAFIK ANALISIS PICA --}}
    <div class="grid gap-6 lg:grid-cols-12">
        {{-- PICA by Status (4 cols) --}}
        <div class="lg:col-span-4 bg-white rounded-xl border border-slate-200 p-5 shadow-sm">
            <h3 class="text-sm font-semibold text-slate-900">PICA by Status</h3>
            <p class="text-xs text-slate-500 mb-4">Proporsi status tindakan korektif saat ini.</p>
            <div class="space-y-3">
                @php
                    $statusColors = [
                        'Open' => 'bg-slate-400',
                        'In Progress' => 'bg-blue-600',
                        'Waiting Verification' => 'bg-amber-500',
                        'Closed' => 'bg-emerald-600',
                    ];
                @endphp
                @foreach($byStatus['labels'] as $idx => $label)
                    @php
                        $val = $byStatus['data'][$idx] ?? 0;
                        $pct = $totalPica > 0 ? round(($val / $totalPica) * 100, 1) : 0;
                    @endphp
                    <div>
                        <div class="flex justify-between text-xs font-medium text-slate-700 mb-1">
                            <span>{{ $label }}</span>
                            <span>{{ $val }} ({{ $pct }}%)</span>
                        </div>
                        <div class="w-full bg-slate-100 h-2 rounded-full overflow-hidden">
                            <div class="h-2 rounded-full {{ $statusColors[$label] ?? 'bg-blue-600' }}" style="width: {{ $pct }}%"></div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>

        {{-- PICA by Source (4 cols) --}}
        <div class="lg:col-span-4 bg-white rounded-xl border border-slate-200 p-5 shadow-sm">
            <h3 class="text-sm font-semibold text-slate-900">PICA by Source</h3>
            <p class="text-xs text-slate-500 mb-4">Distribusi sumber temuan & ketidaksesuaian.</p>
            <div class="space-y-3">
                @forelse($bySource['labels'] as $idx => $label)
                    @php
                        $val = $bySource['data'][$idx] ?? 0;
                        $pct = $totalPica > 0 ? round(($val / $totalPica) * 100, 1) : 0;
                    @endphp
                    <div>
                        <div class="flex justify-between text-xs font-medium text-slate-700 mb-1">
                            <span class="truncate max-w-[180px]">{{ $label }}</span>
                            <span>{{ $val }}</span>
                        </div>
                        <div class="w-full bg-slate-100 h-2 rounded-full overflow-hidden">
                            <div class="h-2 rounded-full bg-[#1646A0]" style="width: {{ $pct }}%"></div>
                        </div>
                    </div>
                @empty
                    <p class="text-xs text-slate-400 py-6 text-center">Belum ada data sumber PICA.</p>
                @endforelse
            </div>
        </div>

        {{-- Overdue Aging (4 cols) --}}
        <div class="lg:col-span-4 bg-white rounded-xl border border-slate-200 p-5 shadow-sm">
            <h3 class="text-sm font-semibold text-slate-900">Overdue Aging</h3>
            <p class="text-xs text-slate-500 mb-4">Keterlambatan tindakan korektif berdasarkan kelompok hari.</p>
            <div class="space-y-3">
                @php
                    $agingColors = ['bg-amber-400', 'bg-orange-500', 'bg-rose-500', 'bg-rose-700'];
                @endphp
                @foreach($overdueAging['labels'] as $idx => $label)
                    @php
                        $val = $overdueAging['data'][$idx] ?? 0;
                        $pct = $overdue > 0 ? round(($val / $overdue) * 100, 1) : 0;
                    @endphp
                    <div>
                        <div class="flex justify-between text-xs font-medium text-slate-700 mb-1">
                            <span>{{ $label }}</span>
                            <span class="font-bold text-rose-600">{{ $val }}</span>
                        </div>
                        <div class="w-full bg-slate-100 h-2 rounded-full overflow-hidden">
                            <div class="h-2 rounded-full {{ $agingColors[$idx] ?? 'bg-rose-500' }}" style="width: {{ $pct }}%"></div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </div>

    {{-- SECOND ROW OF CHARTS --}}
    <div class="grid gap-6 lg:grid-cols-12">
        {{-- PICA by Department (4 cols) --}}
        <div class="lg:col-span-4 bg-white rounded-xl border border-slate-200 p-5 shadow-sm">
            <h3 class="text-sm font-semibold text-slate-900">PICA by Department</h3>
            <p class="text-xs text-slate-500 mb-3">Departemen penanggung jawab tindakan.</p>
            <div class="space-y-2.5">
                @forelse($byDept['labels'] as $idx => $label)
                    <div class="flex items-center justify-between text-xs py-1 border-b border-slate-50">
                        <span class="text-slate-700 font-medium">{{ $label }}</span>
                        <span class="px-2 py-0.5 rounded bg-blue-50 text-[#1646A0] font-bold">{{ $byDept['data'][$idx] }}</span>
                    </div>
                @empty
                    <p class="text-xs text-slate-400 py-4 text-center">Belum ada data departemen.</p>
                @endforelse
            </div>
        </div>

        {{-- Monthly Closure Trend (4 cols) --}}
        <div class="lg:col-span-4 bg-white rounded-xl border border-slate-200 p-5 shadow-sm">
            <h3 class="text-sm font-semibold text-slate-900">Monthly Closure Trend</h3>
            <p class="text-xs text-slate-500 mb-3">Tren jumlah PICA selesai per bulan (6 bulan terakhir).</p>
            <div class="space-y-2.5">
                @foreach($monthlyTrend['labels'] as $idx => $label)
                    <div class="flex items-center justify-between text-xs py-1 border-b border-slate-50">
                        <span class="text-slate-700 font-medium">{{ $label }}</span>
                        <span class="px-2 py-0.5 rounded bg-emerald-50 text-emerald-700 font-bold">{{ $monthlyTrend['data'][$idx] }} Closed</span>
                    </div>
                @endforeach
            </div>
        </div>

        {{-- PIC Workload (4 cols) --}}
        <div class="lg:col-span-4 bg-white rounded-xl border border-slate-200 p-5 shadow-sm">
            <h3 class="text-sm font-semibold text-slate-900">PIC Workload (Active PICA)</h3>
            <p class="text-xs text-slate-500 mb-3">Jumlah PICA terbuka yang sedang diampu per PIC.</p>
            <div class="space-y-2.5">
                @forelse($picWorkload['labels'] as $idx => $label)
                    <div class="flex items-center justify-between text-xs py-1 border-b border-slate-50">
                        <span class="text-slate-700 font-medium">{{ $label }}</span>
                        <span class="px-2 py-0.5 rounded bg-amber-50 text-amber-800 font-bold">{{ $picWorkload['data'][$idx] }} Active</span>
                    </div>
                @empty
                    <p class="text-xs text-slate-400 py-4 text-center">Semua PIC telah menyelesaikan tugas.</p>
                @endforelse
            </div>
        </div>
    </div>

    {{-- RECENT PICA TABLE --}}
    <div class="bg-white rounded-xl border border-slate-200 shadow-sm overflow-hidden">
        <div class="p-4 border-b border-slate-100 flex items-center justify-between">
            <div>
                <h3 class="text-base font-semibold text-slate-900">Tindakan Korektif Terbaru</h3>
                <p class="text-xs text-slate-500">Pemberitahuan PICA aktif yang memerlukan tindak lanjut atau verifikasi.</p>
            </div>
            <a href="{{ route('hse.dashboard.pica.list') }}" class="text-xs font-semibold text-[#1646A0] hover:underline">
                Lihat Seluruh PICA →
            </a>
        </div>

        <div class="ui-table-scroll">
            <table class="ui-table">
                <thead>
                    <tr>
                        <th>PICA Number</th>
                        <th>Sumber (Source)</th>
                        <th>Judul / Temuan</th>
                        <th>Site / Dept</th>
                        <th>PIC</th>
                        <th>Prioritas</th>
                        <th>Due Date</th>
                        <th>Status</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($recentPica as $p)
                        <tr class="hover:bg-slate-50/70 transition">
                            <td class="font-bold text-slate-900">
                                <a href="{{ route('hse.dashboard.pica.detail', $p->id) }}" class="text-[#1646A0] hover:underline">
                                    {{ $p->action_no }}
                                </a>
                            </td>
                            <td><span class="px-2 py-0.5 rounded text-[10px] font-semibold bg-slate-100 text-slate-700">{{ $p->source_module }}</span></td>
                            <td class="font-medium text-slate-800 max-w-xs truncate">{{ $p->title }}</td>
                            <td>{{ $p->site?->code ?? $p->site?->name ?? '—' }}</td>
                            <td>{{ $p->assignee?->name ?? 'Unassigned' }}</td>
                            <td><x-hse.status :value="$p->priority" /></td>
                            <td class="{{ $p->due_date && $p->due_date < now() && $p->status !== 'Closed' ? 'text-rose-600 font-bold' : '' }}">
                                {{ $p->due_date ? \Carbon\Carbon::parse($p->due_date)->format('d M Y') : '—' }}
                            </td>
                            <td><x-hse.status :value="$p->status" /></td>
                            <td><x-ui.table-actions>
                                <x-ui.icon-button action="view" label="Detail" href="{{ route('hse.dashboard.pica.detail', $p->id) }}" />
                            </x-ui.table-actions></td>
                        </tr>
                    @empty
                        <x-ui.table-empty-state :colspan="9" title="Belum ada PICA" message="Tindakan korektif akan muncul saat temuan audit, insiden, atau inspeksi dibuat." />
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
