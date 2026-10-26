@extends('layouts.app')

@section('title', 'Risk Control Monitoring — MANDALA')

@section('content')
<div class="ui-page space-y-6" x-data="{ openEditModal: false, activePica: null }">
    @include('hse.dashboard.partials.header', [
        'title' => 'Risk Control Monitoring',
        'subtitle' => 'Pemantauan implementasi tindakan kontrol mitigasi risiko, bukti evidence, verifikasi, dan status overdue.',
        'filters' => $filters,
        'options' => $options,
    ])

    {{-- Filter Toolbar --}}
    <div class="flex flex-wrap items-center justify-between gap-3 bg-white p-4 rounded-xl border border-slate-200 shadow-sm">
        <form method="GET" action="{{ route('hse.dashboard.risk.controls') }}" class="flex flex-wrap items-center gap-2">
            <select name="status" class="text-xs rounded-lg border-slate-300 focus:border-[#1646A0]" onchange="this.form.submit()">
                <option value="">-- Semua Status --</option>
                <option value="Open" {{ request('status') === 'Open' ? 'selected' : '' }}>Open</option>
                <option value="In Progress" {{ request('status') === 'In Progress' ? 'selected' : '' }}>In Progress</option>
                <option value="Pending Verification" {{ request('status') === 'Pending Verification' ? 'selected' : '' }}>Waiting Verification</option>
                <option value="Closed" {{ request('status') === 'Closed' ? 'selected' : '' }}>Closed</option>
            </select>

            <select name="overdue" class="text-xs rounded-lg border-slate-300 focus:border-[#1646A0]" onchange="this.form.submit()">
                <option value="">-- Filter Due Date --</option>
                <option value="1" {{ request('overdue') == '1' ? 'selected' : '' }}>Hanya yang Overdue</option>
            </select>

            <button type="submit" class="rounded-lg bg-slate-800 px-3 py-2 text-xs font-semibold text-white hover:bg-slate-700 transition">Filter</button>
            @if(request()->anyFilled(['status', 'overdue']))
                <a href="{{ route('hse.dashboard.risk.controls') }}" class="text-xs text-slate-500 hover:text-slate-800 py-2 px-1">Reset</a>
            @endif
        </form>

        <div class="flex items-center gap-2 text-xs text-slate-500">
            <span>Terhubung ke Risk Register & PICA Engine</span>
        </div>
    </div>

    {{-- Table of Controls --}}
    <div class="bg-white rounded-xl border border-slate-200 shadow-sm overflow-hidden">
        <div class="ui-table-scroll">
            <table class="ui-table">
                <thead>
                    <tr>
                        <th>PICA No</th>
                        <th>Mitigasi / Kontrol</th>
                        <th>Sumber</th>
                        <th>Site / Dept</th>
                        <th>PIC (Assignee)</th>
                        <th>Due Date</th>
                        <th>Prioritas</th>
                        <th>Status</th>
                        <th>Evidence</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($controls as $ctrl)
                        @php
                            $isOverdue = $ctrl->due_date && $ctrl->due_date < now() && $ctrl->status !== 'Closed';
                        @endphp
                        <tr class="hover:bg-slate-50/70 transition">
                            <td class="font-bold text-slate-900">{{ $ctrl->action_no }}</td>
                            <td>
                                <div class="font-semibold text-slate-800">{{ $ctrl->title }}</div>
                                <div class="text-xs text-slate-500 truncate max-w-sm" title="{{ $ctrl->description }}">{{ $ctrl->description }}</div>
                                @if($ctrl->progress_notes)
                                    <div class="text-[10px] text-blue-600 bg-blue-50/60 p-1 rounded mt-1 line-clamp-1" title="{{ $ctrl->progress_notes }}">
                                        Notes: {{ $ctrl->progress_notes }}
                                    </div>
                                @endif
                            </td>
                            <td><span class="inline-flex px-2 py-0.5 rounded text-[10px] font-semibold bg-slate-100 text-slate-700">{{ $ctrl->source_module ?: 'Risk Register' }}</span></td>
                            <td>
                                <div class="text-xs font-semibold text-slate-800">{{ $ctrl->site?->code ?? $ctrl->site?->name ?? '—' }}</div>
                                <div class="text-[10px] text-slate-400">{{ $ctrl->department?->name ?? '—' }}</div>
                            </td>
                            <td class="text-xs font-medium text-slate-800">{{ $ctrl->assignee?->name ?? 'Unassigned' }}</td>
                            <td>
                                <span class="{{ $isOverdue ? 'text-rose-600 font-bold bg-rose-50 px-2 py-0.5 rounded' : 'text-slate-700' }}">
                                    {{ $ctrl->due_date ? \Carbon\Carbon::parse($ctrl->due_date)->format('d M Y') : '—' }}
                                    @if($isOverdue)
                                        <span class="block text-[10px] text-rose-500 font-normal">OVERDUE</span>
                                    @endif
                                </span>
                            </td>
                            <td><x-hse.status :value="$ctrl->priority" /></td>
                            <td><x-hse.status :value="$ctrl->status" /></td>
                            <td>
                                @if($ctrl->evidence_path)
                                    <a href="{{ asset('storage/' . $ctrl->evidence_path) }}" target="_blank" class="inline-flex items-center gap-1 text-xs text-blue-600 font-semibold hover:underline">
                                        <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13"/></svg>
                                        Lihat File
                                    </a>
                                @else
                                    <span class="text-xs text-slate-300">Belum Ada</span>
                                @endif
                            </td>
                            <td>
                                <button type="button" @click="activePica = {{ json_encode($ctrl) }}; openEditModal = true" class="inline-flex items-center gap-1 rounded bg-[#1646A0] px-2.5 py-1 text-xs font-semibold text-white hover:bg-[#123880] transition">
                                    Update
                                </button>
                            </td>
                        </tr>
                    @empty
                        <x-ui.table-empty-state :colspan="10" title="Tidak ada tindakan kontrol risiko" message="Belum ada corrective action atau kontrol risiko yang cocok dengan filter." />
                    @endforelse
                </tbody>
            </table>
        </div>

    </div>

    @if($controls->hasPages())
        <div class="mt-4">
            {{ $controls->links() }}
        </div>
    @endif

    {{-- MODAL UPDATE KONTROL (ASSIGN PIC, DUE DATE, EVIDENCE, PROGRESS, VERIFICATION) --}}
    <div x-show="openEditModal" class="fixed inset-0 z-50 overflow-y-auto bg-black/50 backdrop-blur-sm flex items-center justify-center p-4" style="display: none;" x-transition>
        <div class="relative w-full max-w-xl bg-white rounded-xl shadow-2xl p-6" @click.away="openEditModal = false">
            <div class="flex items-center justify-between border-b border-slate-100 pb-3 mb-4">
                <div>
                    <h3 class="text-base font-bold text-slate-900">Update Risk Control</h3>
                    <p class="text-xs text-slate-500" x-text="'PICA: ' + (activePica ? activePica.action_no : '')"></p>
                </div>
                <button type="button" @click="openEditModal = false" class="text-slate-400 hover:text-slate-600 text-xl font-bold">&times;</button>
            </div>

            <form :action="'{{ url('hse/dashboard/actions') }}/' + (activePica ? activePica.id : '') + '/update'" method="POST" enctype="multipart/form-data" class="space-y-4">
                @csrf
                <div class="p-3 bg-slate-50 rounded-lg border border-slate-200">
                    <div class="text-xs font-semibold text-slate-800" x-text="activePica ? activePica.title : ''"></div>
                    <div class="text-xs text-slate-500 mt-1" x-text="activePica ? activePica.description : ''"></div>
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">Assign PIC</label>
                        <select name="assigned_to" class="w-full text-xs rounded-lg border-slate-300 focus:border-[#1646A0]">
                            <option value="">-- Tetap / Pilih PIC --</option>
                            @foreach($users as $u)
                                <option value="{{ $u->id }}">{{ $u->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">Due Date</label>
                        <input type="date" name="due_date" :value="activePica ? (activePica.due_date ? activePica.due_date.substring(0,10) : '') : ''" class="w-full text-xs rounded-lg border-slate-300 focus:border-[#1646A0]">
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Update Status *</label>
                    <select name="status" required class="w-full text-xs rounded-lg border-slate-300 focus:border-[#1646A0]" x-model="activePica ? activePica.status : 'Open'">
                        <option value="Open">Open</option>
                        <option value="In Progress">In Progress (Sedang Dikerjakan)</option>
                        <option value="Pending Verification">Waiting Verification (Menunggu Verifikasi)</option>
                        <option value="Closed">Closed (Verifikasi Selesai)</option>
                    </select>
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Progress Notes / Tindakan Implementasi</label>
                    <textarea name="action_note" rows="2" placeholder="Tuliskan perkembangan implementasi kontrol..." class="w-full text-xs rounded-lg border-slate-300 focus:border-[#1646A0]"></textarea>
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Upload Bukti Implementasi (Evidence)</label>
                    <input type="file" name="evidence_file" class="w-full text-xs rounded-lg border border-slate-300 file:mr-3 file:py-1.5 file:px-3 file:rounded-md file:border-0 file:text-xs file:font-semibold file:bg-blue-50 file:text-[#1646A0] hover:file:bg-blue-100">
                    <p class="text-[10px] text-slate-400 mt-1">Format: PDF, JPG, PNG (Maks 10MB)</p>
                </div>

                <div x-show="activePica && activePica.status === 'In Progress'" class="border-t border-slate-100 pt-3">
                    <label class="block text-xs font-semibold text-rose-600 mb-1">Alasan Penolakan (Jika Evidence Ditolak)</label>
                    <textarea name="rejection_reason" rows="2" placeholder="Isi alasan jika mengembalikan PICA ke In Progress..." class="w-full text-xs rounded-lg border-rose-200 focus:border-rose-400 bg-rose-50/30"></textarea>
                </div>

                <div class="flex items-center justify-end gap-2 border-t border-slate-100 pt-4">
                    <button type="button" @click="openEditModal = false" class="px-4 py-2 text-xs font-medium text-slate-600 hover:bg-slate-100 rounded-lg">Batal</button>
                    <button type="submit" class="px-5 py-2 text-xs font-semibold text-white bg-[#1646A0] hover:bg-[#123880] rounded-lg shadow-sm">Simpan Update Kontrol</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
