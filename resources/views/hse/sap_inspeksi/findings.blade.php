@extends('layouts.app')

@section('title', 'Temuan & Tindak Lanjut')

@section('content')
<div class="ui-page space-y-6">
    <x-ui.page-header>
        <div class="ui-page-header__copy">
            <h1 class="ui-page-title">Temuan & Tindak Lanjut (PICA)</h1>
            <p class="ui-page-description">{{ $inspection->inspection_no }} - {{ $inspection->template->code }}</p>
        </div>
        <div class="ui-page-actions">
            <a href="{{ route('hse.dashboard.sap.inspeksi.show', $inspection->id) }}" class="px-3 py-2 rounded-lg border border-slate-300 text-xs font-semibold text-slate-700">Kembali ke Inspeksi</a>
        </div>
    </x-ui.page-header>

    @if(session('ok'))<div class="rounded-lg bg-emerald-50 border border-emerald-200 p-3 text-xs text-emerald-800">{{ session('ok') }}</div>@endif

    <div class="rounded-xl border border-slate-200 bg-white shadow-sm overflow-hidden">
        <div class="px-6 py-4 border-b border-slate-200 flex items-center justify-between">
            <h2 class="text-base font-bold text-slate-900">Daftar Temuan</h2>
        </div>
        <div class="divide-y divide-slate-100">
            @forelse($inspection->findings as $f)
            <div class="p-6 space-y-4">
                <div class="flex items-start justify-between">
                    <div>
                        <p class="font-semibold text-slate-900">{{ $f->finding_no }} - {{ $f->title }}</p>
                        <p class="text-sm text-slate-600 mt-1">{{ $f->description }}</p>
                        <div class="flex items-center gap-3 mt-2 text-xs text-slate-500">
                            <span>Risk: <strong>{{ $f->risk_class }}</strong></span>
                            <span>PIC: {{ $f->pic->name ?? 'Belum ditentukan' }}</span>
                            <span>Due: {{ $f->due_date ? $f->due_date->format('d/m/Y') : '-' }}</span>
                            <span>Status: <strong>{{ $f->status }}</strong></span>
                        </div>
                    </div>
                </div>

                <div class="rounded-lg border border-slate-200 p-4">
                    <p class="text-sm font-semibold text-slate-900 mb-3">Tindakan Korektif & Pencegahan (CA)</p>
                    @forelse($f->cas as $ca)
                    <div class="border-b border-slate-100 pb-3 mb-3 last:border-0 last:pb-0 last:mb-0">
                        <p class="text-sm text-slate-800"><strong>Korektif:</strong> {{ $ca->corrective_action }}</p>
                        @if($ca->preventive_action)<p class="text-sm text-slate-800 mt-1"><strong>Pencegahan:</strong> {{ $ca->preventive_action }}</p>@endif
                        <div class="flex items-center gap-4 mt-2 text-xs text-slate-500">
                            <span>Diajukan oleh: {{ $ca->actionBy->name ?? '-' }}</span>
                            <span>Status: {{ $ca->status }}</span>
                            @if($ca->verified_at)<span>Diverifikasi: {{ $ca->verified_at->format('d/m/Y H:i') }}</span>@endif
                        </div>
                        @if($ca->status === 'Submitted')
                        <form action="{{ route('hse.dashboard.sap.inspeksi.ca.verify', $ca->id) }}" method="POST" class="flex items-center gap-2 mt-2">
                            @csrf
                            <input type="text" name="verification_notes" placeholder="Catatan verifikasi" class="text-xs rounded-lg border border-slate-300 p-2 flex-1 max-w-md">
                            <button name="action" value="reject" class="px-3 py-1.5 rounded-lg border border-rose-300 text-rose-700 text-xs">Tolak</button>
                            <button name="action" value="verify" class="px-3 py-1.5 rounded-lg bg-emerald-600 text-white text-xs">Verifikasi</button>
                        </form>
                        @endif
                    </div>
                    @empty
                    <p class="text-xs text-slate-500">Belum ada tindakan perbaikan diajukan.</p>
                    @endforelse

                    <form action="{{ route('hse.dashboard.sap.inspeksi.ca.store', $f->id) }}" method="POST" class="mt-4 space-y-2 border-t border-slate-200 pt-4">
                        @csrf
                        <div>
                            <label class="block text-xs font-semibold text-slate-700 mb-1">Tindakan Korektif <span class="text-rose-500">*</span></label>
                            <textarea name="corrective_action" required rows="2" class="w-full text-sm rounded-lg border border-slate-300 p-2" placeholder="Tindakan untuk mengatasi temuan saat ini"></textarea>
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-slate-700 mb-1">Tindakan Pencegahan</label>
                            <textarea name="preventive_action" rows="2" class="w-full text-sm rounded-lg border border-slate-300 p-2" placeholder="Tindakan untuk mencegah terulang"></textarea>
                        </div>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                            <div>
                                <label class="block text-xs font-semibold text-slate-700 mb-1">Target Penyelesaian</label>
                                <input type="date" name="target_completion_date" class="w-full text-sm rounded-lg border border-slate-300 p-2">
                            </div>
                        </div>
                        <button class="px-4 py-2 rounded-lg bg-[#1646A0] text-white text-xs font-semibold">Ajukan Tindakan Perbaikan</button>
                    </form>
                </div>

                @if($f->status === 'For Verification')
                <form action="{{ route('hse.dashboard.sap.inspeksi.finding.update', $f->id) }}" method="POST" class="flex items-center gap-2">
                    @csrf
                    <input type="hidden" name="status" value="Closed">
                    <input type="text" name="closure_notes" placeholder="Catatan penutupan/verifikasi" class="text-xs rounded-lg border border-slate-300 p-2 flex-1 max-w-md">
                    <button class="px-4 py-2 rounded-lg bg-emerald-600 text-white text-xs font-semibold">Tutup Temuan (Verified/Closed)</button>
                </form>
                @endif
            </div>
            @empty
            <div class="p-8 text-center text-slate-500 text-sm">Belum ada temuan. Temuan otomatis akan dibuat saat inspeksi diajukan jika ada ketidaksesuaian.</div>
            @endforelse
        </div>
    </div>
</div>
@endsection
