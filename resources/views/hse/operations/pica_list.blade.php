@extends('layouts.app')

@section('title', $sourceTitle . ' — PICA Management')

@section('content')
<div class="ui-page space-y-6">
    @include('hse.dashboard.partials.header', [
        'title' => $sourceTitle,
        'subtitle' => 'Daftar tindakan korektif dan preventif (PICA) dengan pelacakan status implementasi dan verifikasi evidence.',
        'filters' => $filters,
        'options' => $options,
        'create' => ['route' => 'hse.dashboard.pica.create', 'label' => 'Buat PICA'],
        'showDashboardFilters' => false,
    ])

    {{-- Filter Toolbar --}}
    <div class="ui-filter-panel ui-module-filter-toolbar">
        <x-ui.table-toolbar action="{{ route('hse.dashboard.pica.list', $source ?: '') }}">
            <x-ui.table-search name="q" :value="request('q')" :auto-submit="false" aria-label="Cari PICA" placeholder="Cari nomor PICA, judul, temuan, atau root cause…" />
            <x-hse.module-scope-filters :options="$options" :filters="$filters" />

            <div class="ui-filter-field"><label class="ui-filter-field__label" for="pica-status-filter">Status</label><select id="pica-status-filter" name="status" class="text-xs rounded-lg border-slate-300 focus:border-[#1646A0]" onchange="this.form.submit()">
                <option value="">-- Semua Status --</option>
                <option value="Open" {{ request('status') === 'Open' ? 'selected' : '' }}>Open</option>
                <option value="In Progress" {{ request('status') === 'In Progress' ? 'selected' : '' }}>In Progress</option>
                <option value="Pending Verification" {{ request('status') === 'Pending Verification' ? 'selected' : '' }}>Waiting Verification</option>
                <option value="Closed" {{ request('status') === 'Closed' ? 'selected' : '' }}>Closed</option>
            </select></div>

            <div class="ui-filter-field"><label class="ui-filter-field__label" for="pica-priority-filter">Prioritas</label><select id="pica-priority-filter" name="priority" class="text-xs rounded-lg border-slate-300 focus:border-[#1646A0]" onchange="this.form.submit()">
                <option value="">-- Semua Prioritas --</option>
                <option value="Low" {{ request('priority') === 'Low' ? 'selected' : '' }}>Low</option>
                <option value="Medium" {{ request('priority') === 'Medium' ? 'selected' : '' }}>Medium</option>
                <option value="High" {{ request('priority') === 'High' ? 'selected' : '' }}>High</option>
                <option value="Critical" {{ request('priority') === 'Critical' ? 'selected' : '' }}>Critical</option>
            </select></div>



        <x-slot:actions><x-ui.button type="submit" variant="primary" icon="filter">Terapkan</x-ui.button><x-ui.link href="{{ url()->current() }}" icon="rotate-ccw">Reset</x-ui.link></x-slot:actions></x-ui.table-toolbar>

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
                            <td><x-ui.table-actions>
                                <x-ui.icon-button action="view" label="Detail / Verifikasi" href="{{ route('hse.dashboard.pica.detail', $pica->id) }}" />
                            </x-ui.table-actions></td>
                        </tr>
                    @empty
                        <x-ui.table-empty-state :colspan="10" title="Tidak ada data PICA" message="Belum ada tindakan korektif yang sesuai dengan kriteria filter." />
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="ui-pagination-footer">
            {{ $records->links() }}
        </div>
    </div>

</div>
@endsection
