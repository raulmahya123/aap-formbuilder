@extends('layouts.app')

@section('title', 'PICA #' . $pica->action_no . ' — Detail')

@section('content')
<div class="ui-page space-y-6" x-data="{ openVerifyModal: false, decision: 'approve' }">
    {{-- Header --}}
    <x-ui.page-header>
        <div class="ui-page-header__copy">
            <div class="flex items-center gap-2">
                <a href="{{ route('hse.dashboard.pica.list') }}" class="text-xs font-semibold text-[#1646A0] hover:underline flex items-center gap-1">
                    ← Kembali ke Daftar PICA
                </a>
                <span class="text-slate-300">|</span>
                <span class="px-2.5 py-0.5 rounded text-xs font-bold bg-blue-50 text-[#1646A0] border border-blue-200">
                    {{ $pica->source_module ?: 'Corrective Action' }}
                </span>
            </div>
            <h1 class="ui-page-title">{{ $pica->action_no }} — {{ $pica->title }}</h1>
            <p class="ui-page-description">Dibuat pada {{ $pica->created_at?->format('d M Y H:i') }} • Status Terkini: <strong>{{ $pica->status }}</strong></p>
        </div>

        <div class="ui-page-actions">
            @if($pica->status !== 'Closed')
                <button type="button" @click="decision = 'approve'; openVerifyModal = true" class="inline-flex items-center gap-1.5 rounded-lg bg-emerald-600 px-4 py-2 text-xs font-semibold text-white shadow-sm hover:bg-emerald-700 transition">
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                    Verifikasi & Selesai (Close)
                </button>
                <button type="button" @click="decision = 'reject'; openVerifyModal = true" class="inline-flex items-center gap-1.5 rounded-lg bg-rose-600 px-4 py-2 text-xs font-semibold text-white shadow-sm hover:bg-rose-700 transition">
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                    Tolak Evidence (Reopen)
                </button>
            @else
                <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg bg-emerald-50 text-emerald-700 border border-emerald-200 text-xs font-bold">
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    PICA CLOSED & VERIFIED
                </span>
            @endif
        </div>
    </x-ui.page-header>

    {{-- Workflow Progress Indicator --}}
    @php
        $steps = ['Open', 'In Progress', 'Pending Verification', 'Closed'];
        $currentIdx = array_search($pica->status, $steps);
        if ($currentIdx === false) $currentIdx = 1;
    @endphp
    <div class="bg-white p-5 rounded-xl border border-slate-200 shadow-sm">
        <h3 class="text-xs font-semibold text-slate-500 uppercase tracking-wider mb-4">PICA Workflow Lifecycle</h3>
        <div class="grid grid-cols-4 gap-2">
            @foreach($steps as $idx => $step)
                @php
                    $isActive = $idx <= $currentIdx;
                    $isCurrent = $idx === $currentIdx;
                @endphp
                <div class="relative p-3 rounded-lg border {{ $isCurrent ? 'border-[#1646A0] bg-blue-50/50' : ($isActive ? 'border-emerald-200 bg-emerald-50/30' : 'border-slate-200 bg-slate-50/50') }}">
                    <div class="flex items-center justify-between">
                        <span class="text-[11px] font-bold {{ $isActive ? ($isCurrent ? 'text-[#1646A0]' : 'text-emerald-700') : 'text-slate-400' }}">
                            Step {{ $idx + 1 }}
                        </span>
                        @if($isActive && !$isCurrent)
                            <svg class="h-4 w-4 text-emerald-600" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                        @endif
                    </div>
                    <p class="text-xs font-bold text-slate-800 mt-1">{{ $step === 'Pending Verification' ? 'Waiting Verification' : $step }}</p>
                </div>
            @endforeach
        </div>
    </div>

    {{-- 2 Columns Grid: Details vs Actions & History --}}
    <div class="grid gap-6 lg:grid-cols-12">
        {{-- Left: PICA Data Fields (7 cols) --}}
        <div class="lg:col-span-7 space-y-6">
            <div class="bg-white rounded-xl border border-slate-200 p-5 shadow-sm space-y-4">
                <h3 class="text-sm font-bold text-slate-900 border-b border-slate-100 pb-3">Informasi Utama PICA</h3>

                <div class="grid grid-cols-2 gap-4 text-xs">
                    <div>
                        <span class="text-slate-400 block mb-0.5">PICA Number</span>
                        <span class="font-bold text-slate-900 text-sm">{{ $pica->action_no }}</span>
                    </div>
                    <div>
                        <span class="text-slate-400 block mb-0.5">Source Type & Reference</span>
                        <span class="font-semibold text-slate-800">{{ $pica->source_module }} (Ref #{{ $pica->source_id ?: 'Direct' }})</span>
                    </div>
                    <div>
                        <span class="text-slate-400 block mb-0.5">Site & Department</span>
                        <span class="font-semibold text-slate-800">{{ $pica->site?->name ?? '—' }} ({{ $pica->department?->name ?? '—' }})</span>
                    </div>
                    <div>
                        <span class="text-slate-400 block mb-0.5">Prioritas & Status</span>
                        <div class="flex items-center gap-1.5 mt-0.5">
                            <x-hse.status :value="$pica->priority" />
                            <x-hse.status :value="$pica->status" />
                        </div>
                    </div>
                    <div>
                        <span class="text-slate-400 block mb-0.5">Penanggung Jawab (PIC)</span>
                        <span class="font-semibold text-slate-900">{{ $pica->assignee?->name ?? 'Unassigned' }}</span>
                    </div>
                    <div>
                        <span class="text-slate-400 block mb-0.5">Due Date (Batas Waktu)</span>
                        <span class="font-bold {{ $pica->due_date && $pica->due_date < now() && $pica->status !== 'Closed' ? 'text-rose-600' : 'text-slate-800' }}">
                            {{ $pica->due_date ? $pica->due_date->format('d M Y') : '—' }}
                        </span>
                    </div>
                </div>

                <div class="border-t border-slate-100 pt-3">
                    <span class="text-xs font-semibold text-slate-500 uppercase tracking-wider block mb-1">Finding Description</span>
                    <p class="text-xs text-slate-800 leading-relaxed bg-slate-50 p-3 rounded-lg border border-slate-100">
                        {{ $pica->description }}
                    </p>
                </div>

                <div class="border-t border-slate-100 pt-3">
                    <span class="text-xs font-semibold text-slate-500 uppercase tracking-wider block mb-1">Root Cause (Akar Masalah)</span>
                    <p class="text-xs text-slate-800 leading-relaxed bg-slate-50 p-3 rounded-lg border border-slate-100">
                        {{ $pica->root_cause ?: 'Belum dianalisa / Tidak tersedia.' }}
                    </p>
                </div>

                <div class="border-t border-slate-100 pt-3">
                    <span class="text-xs font-semibold text-slate-500 uppercase tracking-wider block mb-1">Corrective Action (Tindakan Korektif)</span>
                    <p class="text-xs text-slate-800 leading-relaxed bg-slate-50 p-3 rounded-lg border border-slate-100">
                        {{ $pica->corrective_action ?: 'Tindakan perbaikan langsung.' }}
                    </p>
                </div>

                <div class="border-t border-slate-100 pt-3">
                    <span class="text-xs font-semibold text-slate-500 uppercase tracking-wider block mb-1">Preventive Action (Tindakan Pencegahan)</span>
                    <p class="text-xs text-slate-800 leading-relaxed bg-slate-50 p-3 rounded-lg border border-slate-100">
                        {{ $pica->preventive_action ?: 'Tindakan pencegahan jangka panjang.' }}
                    </p>
                </div>

                @if($pica->rejection_reason)
                    <div class="border-t border-slate-100 pt-3 bg-rose-50/60 p-3 rounded-lg border border-rose-200">
                        <span class="text-xs font-bold text-rose-700 block mb-1">Alasan Penolakan Evidence Terakhir</span>
                        <p class="text-xs text-rose-800">{{ $pica->rejection_reason }}</p>
                    </div>
                @endif
            </div>
        </div>

        {{-- Right: Evidence, Progress, History (5 cols) --}}
        <div class="lg:col-span-5 space-y-6">
            {{-- Evidence File Card --}}
            <div class="bg-white rounded-xl border border-slate-200 p-5 shadow-sm">
                <h3 class="text-sm font-bold text-slate-900 border-b border-slate-100 pb-3">Evidence (Bukti Implementasi)</h3>
                <div class="mt-4">
                    @if($pica->evidence_path)
                        <div class="p-4 rounded-lg border border-slate-200 bg-slate-50/70 text-center">
                            <svg class="mx-auto h-8 w-8 text-[#1646A0]" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                            <p class="text-xs font-semibold text-slate-800 mt-2">File Evidence Terlampir</p>
                            <a href="{{ asset('storage/' . $pica->evidence_path) }}" target="_blank" class="mt-3 inline-flex items-center gap-1.5 px-4 py-1.5 rounded-lg bg-[#1646A0] text-xs font-semibold text-white hover:bg-[#123880] transition">
                                <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
                                Unduh / Lihat Bukti
                            </a>
                        </div>
                    @else
                        <div class="p-6 rounded-lg border border-dashed border-slate-300 text-center text-slate-400">
                            <p class="text-xs font-medium">Belum ada bukti yang diunggah.</p>
                            <p class="text-[11px] text-slate-400 mt-0.5">PIC dapat mengunggah file bukti perbaikan di bawah.</p>
                        </div>
                    @endif
                </div>

                {{-- Upload Evidence Form --}}
                @if($pica->status !== 'Closed')
                    <form action="{{ route('hse.dashboard.actions.update', $pica->id) }}" method="POST" enctype="multipart/form-data" class="mt-4 pt-4 border-t border-slate-100 space-y-3">
                        @csrf
                        <input type="hidden" name="status" value="Pending Verification">
                        <div>
                            <label class="block text-xs font-semibold text-slate-700 mb-1">Unggah / Ganti Bukti Implementasi</label>
                            <input type="file" name="evidence_file" required class="w-full text-xs rounded-lg border border-slate-300 file:mr-2 file:py-1 file:px-2.5 file:rounded file:border-0 file:text-xs file:font-semibold file:bg-blue-50 file:text-[#1646A0]">
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-slate-700 mb-1">Catatan Progres</label>
                            <textarea name="action_note" rows="2" placeholder="Jelaskan implementasi yang telah dilakukan..." class="w-full text-xs rounded-lg border-slate-300 focus:border-[#1646A0]"></textarea>
                        </div>
                        <button type="submit" class="w-full rounded-lg bg-[#1646A0] py-2 text-xs font-semibold text-white hover:bg-[#123880] transition shadow-sm">
                            Submit Evidence & Ajukan Verifikasi
                        </button>
                    </form>
                @endif
            </div>

            {{-- Activity History --}}
            <div class="bg-white rounded-xl border border-slate-200 p-5 shadow-sm">
                <h3 class="text-sm font-bold text-slate-900 border-b border-slate-100 pb-3">Activity History & Audit Trail</h3>
                <div class="mt-3 space-y-3 text-xs">
                    @if($pica->progress_notes)
                        <pre class="font-sans whitespace-pre-wrap text-xs text-slate-700 bg-slate-50 p-3 rounded-lg border border-slate-100 leading-relaxed">{{ $pica->progress_notes }}</pre>
                    @else
                        <p class="text-slate-400 py-3 text-center">Belum ada riwayat aktivitas.</p>
                    @endif
                </div>

                <div class="mt-4 pt-3 border-t border-slate-100 text-[11px] text-slate-400 space-y-1">
                    <div>Diverifikasi oleh: <strong>{{ $pica->verifier?->name ?? 'Belum diverifikasi' }}</strong></div>
                    <div>Waktu Verifikasi: <strong>{{ $pica->verified_at ? $pica->verified_at->format('d M Y H:i') : '—' }}</strong></div>
                    <div>Waktu Closure: <strong>{{ $pica->closed_at ? $pica->closed_at->format('d M Y H:i') : '—' }}</strong></div>
                </div>
            </div>
        </div>
    </div>

    {{-- MODAL VERIFIKASI (APPROVE OR REJECT) --}}
    <div x-show="openVerifyModal" class="ui-modal-overlay" style="display: none;" x-transition>
        <div class="ui-modal-panel ui-modal-panel--md ui-modal-panel--form" @click.away="openVerifyModal = false">
            <div class="flex items-center justify-between border-b border-slate-100 pb-3 mb-4">
                <h3 class="text-base font-bold text-slate-900" x-text="decision === 'approve' ? 'Verifikasi & Setujui PICA (Close)' : 'Tolak Evidence & Kembalikan ke In Progress'"></h3>
                <button type="button" @click="openVerifyModal = false" class="text-slate-400 hover:text-slate-600 text-xl font-bold">&times;</button>
            </div>

            <form action="{{ route('hse.dashboard.pica.verify', $pica->id) }}" method="POST" class="space-y-4">
                @csrf
                <input type="hidden" name="decision" :value="decision">

                <div class="p-3 rounded-lg border" :class="decision === 'approve' ? 'bg-emerald-50 border-emerald-200 text-emerald-800' : 'bg-rose-50 border-rose-200 text-rose-800'">
                    <p class="text-xs font-medium" x-text="decision === 'approve' ? 'PICA akan ditandai CLOSED secara permanen dan tercatat verifikatornya.' : 'Evidence akan ditolak, PICA kembali berstatus In Progress dan PIC akan menerima notifikasi alasan penolakan.'"></p>
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1" x-text="decision === 'approve' ? 'Catatan Verifikasi (Optional)' : 'Alasan Penolakan Evidence *'"></label>
                    <textarea name="notes" rows="3" required placeholder="Tuliskan catatan verifikasi atau alasan penolakan..." class="w-full text-xs rounded-lg border-slate-300 focus:border-[#1646A0]"></textarea>
                </div>

                <div class="flex items-center justify-end gap-2 border-t border-slate-100 pt-4">
                    <button type="button" @click="openVerifyModal = false" class="px-4 py-2 text-xs font-medium text-slate-600 hover:bg-slate-100 rounded-lg">Batal</button>
                    <button type="submit" class="px-5 py-2 text-xs font-semibold text-white rounded-lg shadow-sm" :class="decision === 'approve' ? 'bg-emerald-600 hover:bg-emerald-700' : 'bg-rose-600 hover:bg-rose-700'">
                        Konfirmasi Keputusan
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
