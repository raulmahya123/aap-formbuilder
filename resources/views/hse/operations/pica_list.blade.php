@extends('layouts.app')

@section('title', $sourceTitle . ' — PICA Management')

@section('content')
<div class="ui-page space-y-6" x-data="{ openCreateModal: false }">
    @include('hse.dashboard.partials.header', [
        'title' => $sourceTitle,
        'subtitle' => 'Daftar tindakan korektif dan preventif (PICA) dengan pelacakan status implementasi dan verifikasi evidence.',
        'filters' => $filters,
        'options' => $options,
    ])

    {{-- Filter Toolbar --}}
    <div class="flex flex-wrap items-center justify-between gap-3 bg-white p-4 rounded-xl border border-slate-200 shadow-sm">
        <form method="GET" action="{{ route('hse.dashboard.pica.list', $source ?: '') }}" class="flex flex-wrap items-center gap-2">
            <input type="text" name="q" value="{{ request('q') }}" placeholder="Cari No PICA, judul, temuan, root cause..." class="text-xs rounded-lg border-slate-300 focus:border-[#1646A0] w-64">

            <select name="status" class="text-xs rounded-lg border-slate-300 focus:border-[#1646A0]" onchange="this.form.submit()">
                <option value="">-- Semua Status --</option>
                <option value="Open" {{ request('status') === 'Open' ? 'selected' : '' }}>Open</option>
                <option value="In Progress" {{ request('status') === 'In Progress' ? 'selected' : '' }}>In Progress</option>
                <option value="Pending Verification" {{ request('status') === 'Pending Verification' ? 'selected' : '' }}>Waiting Verification</option>
                <option value="Closed" {{ request('status') === 'Closed' ? 'selected' : '' }}>Closed</option>
            </select>

            <select name="priority" class="text-xs rounded-lg border-slate-300 focus:border-[#1646A0]" onchange="this.form.submit()">
                <option value="">-- Semua Prioritas --</option>
                <option value="Low" {{ request('priority') === 'Low' ? 'selected' : '' }}>Low</option>
                <option value="Medium" {{ request('priority') === 'Medium' ? 'selected' : '' }}>Medium</option>
                <option value="High" {{ request('priority') === 'High' ? 'selected' : '' }}>High</option>
                <option value="Critical" {{ request('priority') === 'Critical' ? 'selected' : '' }}>Critical</option>
            </select>

            <button type="submit" class="rounded-lg bg-slate-800 px-3 py-2 text-xs font-semibold text-white hover:bg-slate-700 transition">Filter</button>
            @if(request()->anyFilled(['q', 'status', 'priority']))
                <a href="{{ route('hse.dashboard.pica.list', $source ?: '') }}" class="text-xs text-slate-500 hover:text-slate-800 py-2 px-1">Reset</a>
            @endif
        </form>

        <div class="flex items-center gap-2">
            <button type="button" @click="openCreateModal = true" class="inline-flex items-center gap-1.5 rounded-lg bg-[#1646A0] px-4 py-2 text-xs font-semibold text-white shadow-sm hover:bg-[#123880] transition">
                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                Buat PICA Manual
            </button>
        </div>
    </div>

    {{-- Main Table --}}
    <div class="bg-white rounded-xl border border-slate-200 shadow-sm overflow-hidden">
        <div class="ui-table-scroll">
            <table class="ui-table">
                <thead>
                    <tr>
                        <th>PICA Number</th>
                        <th>Source Type / Ref</th>
                        <th>Judul Finding / Tindakan</th>
                        <th>Site / Dept</th>
                        <th>PIC (Assignee)</th>
                        <th>Prioritas</th>
                        <th>Due Date</th>
                        <th>Progress</th>
                        <th>Status</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($records as $pica)
                        @php
                            $isOverdue = $pica->due_date && $pica->due_date < now() && $pica->status !== 'Closed';
                        @endphp
                        <tr class="hover:bg-slate-50/70 transition">
                            <td class="font-bold text-slate-900">
                                <a href="{{ route('hse.dashboard.pica.detail', $pica->id) }}" class="text-[#1646A0] hover:underline">
                                    {{ $pica->action_no }}
                                </a>
                            </td>
                            <td>
                                <span class="px-2 py-0.5 rounded text-[10px] font-semibold bg-slate-100 text-slate-700">
                                    {{ $pica->source_module ?: 'Manual' }}
                                </span>
                                @if($pica->source_id)
                                    <div class="text-[10px] text-slate-400 mt-0.5">Ref #{{ $pica->source_id }}</div>
                                @endif
                            </td>
                            <td>
                                <div class="font-semibold text-slate-800 max-w-sm">{{ $pica->title }}</div>
                                <div class="text-xs text-slate-500 truncate max-w-sm" title="{{ $pica->description }}">{{ $pica->description }}</div>
                            </td>
                            <td>
                                <div class="text-xs font-semibold text-slate-800">{{ $pica->site?->code ?? $pica->site?->name ?? '—' }}</div>
                                <div class="text-[10px] text-slate-400">{{ $pica->department?->name ?? '—' }}</div>
                            </td>
                            <td class="text-xs font-medium text-slate-800">{{ $pica->assignee?->name ?? 'Unassigned' }}</td>
                            <td><x-hse.status :value="$pica->priority" /></td>
                            <td>
                                <span class="{{ $isOverdue ? 'text-rose-600 font-bold bg-rose-50 px-2 py-0.5 rounded' : 'text-slate-700' }}">
                                    {{ $pica->due_date ? \Carbon\Carbon::parse($pica->due_date)->format('d M Y') : '—' }}
                                    @if($isOverdue)
                                        <span class="block text-[10px] text-rose-500 font-normal">OVERDUE</span>
                                    @endif
                                </span>
                            </td>
                            <td>
                                @if($pica->evidence_path)
                                    <span class="inline-flex items-center gap-1 text-[11px] font-semibold text-emerald-700 bg-emerald-50 px-2 py-0.5 rounded">
                                        <svg class="h-3 w-3" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                                        Evidence Ada
                                    </span>
                                @else
                                    <span class="text-xs text-slate-400">Belum Ada Bukti</span>
                                @endif
                            </td>
                            <td><x-hse.status :value="$pica->status" /></td>
                            <td>
                                <a href="{{ route('hse.dashboard.pica.detail', $pica->id) }}" class="inline-flex items-center gap-1 rounded bg-[#1646A0] px-2.5 py-1 text-xs font-semibold text-white hover:bg-[#123880] transition">
                                    Detail / Verifikasi
                                </a>
                            </td>
                        </tr>
                    @empty
                        <x-ui.table-empty-state :colspan="10" title="Tidak ada data PICA" message="Belum ada tindakan korektif yang sesuai dengan kriteria filter." />
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($records->hasPages())
            <div class="p-4 border-t border-slate-100">
                {{ $records->links() }}
            </div>
        @endif
    </div>

    {{-- MODAL BUAT PICA MANUAL --}}
    <div x-show="openCreateModal" class="fixed inset-0 z-50 overflow-y-auto bg-black/50 backdrop-blur-sm flex items-center justify-center p-4" style="display: none;" x-transition>
        <div class="relative w-full max-w-2xl bg-white rounded-xl shadow-2xl p-6" @click.away="openCreateModal = false">
            <div class="flex items-center justify-between border-b border-slate-100 pb-3 mb-4">
                <h3 class="text-base font-bold text-slate-900">Buat Tindakan Korektif (PICA) Manual</h3>
                <button type="button" @click="openCreateModal = false" class="text-slate-400 hover:text-slate-600 text-xl font-bold">&times;</button>
            </div>

            <form action="{{ route('hse.dashboard.pica.store') }}" method="POST" class="space-y-4">
                @csrf
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">Sumber Tindakan (Source Module) *</label>
                        <select name="source_module" required class="w-full text-xs rounded-lg border-slate-300 focus:border-[#1646A0]">
                            <option value="PICA Audit">PICA Audit</option>
                            <option value="PICA Investigation">PICA Investigation</option>
                            <option value="PICA Inspection">PICA Inspection</option>
                            <option value="PICA PTO">PICA PTO</option>
                            <option value="PICA Hazard Report">PICA Hazard Report</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">Prioritas *</label>
                        <select name="priority" required class="w-full text-xs rounded-lg border-slate-300 focus:border-[#1646A0]">
                            <option value="Low">Low</option>
                            <option value="Medium" selected>Medium</option>
                            <option value="High">High</option>
                            <option value="Critical">Critical</option>
                        </select>
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">Site *</label>
                        <select name="site_id" required class="w-full text-xs rounded-lg border-slate-300 focus:border-[#1646A0]">
                            @foreach($options['sites'] ?? [] as $site)
                                <option value="{{ $site->id }}">{{ $site->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">Department</label>
                        <select name="department_id" class="w-full text-xs rounded-lg border-slate-300 focus:border-[#1646A0]">
                            <option value="">-- Pilih Department --</option>
                            @foreach($departments as $dept)
                                <option value="{{ $dept->id }}">{{ $dept->name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Judul PICA / Finding Summary *</label>
                    <input type="text" name="title" required placeholder="Contoh: Perbaikan Pengaman Conveyor Line 2" class="w-full text-xs rounded-lg border-slate-300 focus:border-[#1646A0]">
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Deskripsi Temuan (Finding Description) *</label>
                    <textarea name="description" rows="2" required placeholder="Deskripsi ketidaksesuaian atau deviasi..." class="w-full text-xs rounded-lg border-slate-300 focus:border-[#1646A0]"></textarea>
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">Root Cause (Akar Masalah)</label>
                        <textarea name="root_cause" rows="2" placeholder="Analisa penyebab utama..." class="w-full text-xs rounded-lg border-slate-300 focus:border-[#1646A0]"></textarea>
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">Corrective Action (Tindakan Korektif)</label>
                        <textarea name="corrective_action" rows="2" placeholder="Tindakan langsung perbaikan..." class="w-full text-xs rounded-lg border-slate-300 focus:border-[#1646A0]"></textarea>
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Preventive Action (Tindakan Pencegahan)</label>
                    <textarea name="preventive_action" rows="2" placeholder="Tindakan pencegahan agar tidak berulang..." class="w-full text-xs rounded-lg border-slate-300 focus:border-[#1646A0]"></textarea>
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">Penanggung Jawab (PIC)</label>
                        <select name="assigned_to" class="w-full text-xs rounded-lg border-slate-300 focus:border-[#1646A0]">
                            <option value="">-- Pilih PIC --</option>
                            @foreach($users as $u)
                                <option value="{{ $u->id }}">{{ $u->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">Batas Waktu (Due Date) *</label>
                        <input type="date" name="due_date" required class="w-full text-xs rounded-lg border-slate-300 focus:border-[#1646A0]">
                    </div>
                </div>

                <div class="flex items-center justify-end gap-2 border-t border-slate-100 pt-4">
                    <button type="button" @click="openCreateModal = false" class="px-4 py-2 text-xs font-medium text-slate-600 hover:bg-slate-100 rounded-lg">Batal</button>
                    <button type="submit" class="px-5 py-2 text-xs font-semibold text-white bg-[#1646A0] hover:bg-[#123880] rounded-lg shadow-sm">Terbitkan PICA</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
