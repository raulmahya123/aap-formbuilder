@extends('layouts.app')

@section('title', $document->document_code . ' — Detail Dokumen')

@section('content')
<div class="ui-page space-y-6" x-data="{ openReviseModal: false, openApprovalModal: false, approvalAction: 'approve_publish' }">
    {{-- Header --}}
    <x-ui.page-header>
        <div class="ui-page-header__copy">
            <div class="flex items-center gap-2">
                <a href="{{ route('hse.dashboard.documents.index') }}" class="text-xs font-semibold text-[#1646A0] hover:underline flex items-center gap-1">
                    ← Kembali ke Repository
                </a>
                <span class="text-slate-300">|</span>
                <span class="px-2.5 py-0.5 rounded text-xs font-bold bg-blue-50 text-[#1646A0] border border-blue-200">
                    {{ $document->category }}
                </span>
            </div>
            <h1 class="ui-page-title">{{ $document->document_code }} — {{ $document->title }}</h1>
            <p class="ui-page-description">Versi Saat Ini: <strong>v{{ $document->version }}</strong> (Rev #{{ $document->revision_number }}) • Status: <strong>{{ $document->status }}</strong></p>
        </div>

        <div class="ui-page-actions">
            @if($document->file_path)
                <a href="{{ route('hse.dashboard.documents.preview', $document->id) }}" target="_blank" class="inline-flex items-center gap-1.5 rounded-lg border border-slate-300 bg-white px-3.5 py-2 text-xs font-semibold text-slate-700 hover:bg-slate-50 transition">
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                    Preview File
                </a>
                <a href="{{ route('hse.dashboard.documents.download', $document->id) }}" class="inline-flex items-center gap-1.5 rounded-lg bg-emerald-600 px-3.5 py-2 text-xs font-semibold text-white shadow-sm hover:bg-emerald-700 transition">
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
                    Unduh Dokumen
                </a>
            @endif

            @if(in_array($document->status, ['Submitted', 'In Review']))
                <button type="button" @click="approvalAction = 'approve_publish'; openApprovalModal = true" class="inline-flex items-center gap-1.5 rounded-lg bg-[#1646A0] px-3.5 py-2 text-xs font-semibold text-white shadow-sm hover:bg-[#123880] transition">
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                    Review / Setujui
                </button>
                <button type="button" @click="approvalAction = 'reject'; openApprovalModal = true" class="inline-flex items-center gap-1.5 rounded-lg bg-rose-600 px-3.5 py-2 text-xs font-semibold text-white shadow-sm hover:bg-rose-700 transition">
                    Tolak / Kembalikan
                </button>
            @endif

            @if($document->status === 'Published')
                <button type="button" @click="openReviseModal = true" class="inline-flex items-center gap-1.5 rounded-lg bg-blue-600 px-3.5 py-2 text-xs font-semibold text-white shadow-sm hover:bg-blue-700 transition">
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                    Ajukan Revisi Versi Baru
                </button>
                <form action="{{ route('hse.dashboard.documents.obsolete', $document->id) }}" method="POST" onsubmit="return confirm('Tandai dokumen ini sebagai Obsolete (Usang)?')">
                    @csrf
                    <button type="submit" class="inline-flex items-center gap-1.5 rounded-lg border border-slate-300 bg-white px-3 py-2 text-xs font-medium text-slate-600 hover:text-rose-600 hover:border-rose-300 transition">
                        Mark Obsolete
                    </button>
                </form>
            @endif
        </div>
    </x-ui.page-header>

    {{-- Workflow Status Banner --}}
    @php
        $wfSteps = ['Draft', 'Submitted', 'In Review', 'Approved', 'Published'];
        $wfCurrent = array_search($document->status, $wfSteps);
        if ($wfCurrent === false && $document->status === 'Obsolete') $wfCurrent = 5;
    @endphp
    <div class="bg-white p-5 rounded-xl border border-slate-200 shadow-sm">
        <h3 class="text-xs font-semibold text-slate-500 uppercase tracking-wider mb-4">Document Lifecycle & Approval Workflow</h3>
        <div class="grid grid-cols-5 gap-2">
            @foreach($wfSteps as $idx => $step)
                @php
                    $isPass = $idx <= $wfCurrent;
                    $isThis = $idx === $wfCurrent;
                @endphp
                <div class="p-3 rounded-lg border {{ $isThis ? 'border-[#1646A0] bg-blue-50/60' : ($isPass ? 'border-emerald-200 bg-emerald-50/30' : 'border-slate-200 bg-slate-50/50') }}">
                    <div class="flex items-center justify-between">
                        <span class="text-[11px] font-bold {{ $isPass ? ($isThis ? 'text-[#1646A0]' : 'text-emerald-700') : 'text-slate-400' }}">
                            Langkah {{ $idx + 1 }}
                        </span>
                        @if($isPass && !$isThis)
                            <svg class="h-4 w-4 text-emerald-600" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                        @endif
                    </div>
                    <p class="text-xs font-bold text-slate-800 mt-1">{{ $step }}</p>
                </div>
            @endforeach
        </div>
    </div>

    {{-- Details & Revisions Grid --}}
    <div class="grid gap-6 lg:grid-cols-12">
        {{-- Left: Metadata Details (6 cols) --}}
        <div class="lg:col-span-6 bg-white rounded-xl border border-slate-200 p-5 shadow-sm space-y-4">
            <h3 class="text-sm font-bold text-slate-900 border-b border-slate-100 pb-3">Informasi Metadata Dokumen</h3>

            <div class="grid grid-cols-2 gap-4 text-xs">
                <div>
                    <span class="text-slate-400 block mb-0.5">Kode Dokumen</span>
                    <span class="font-bold text-slate-900 text-sm">{{ $document->document_code }}</span>
                </div>
                <div>
                    <span class="text-slate-400 block mb-0.5">Kategori</span>
                    <span class="font-semibold text-slate-800">{{ $document->category }}</span>
                </div>
                <div>
                    <span class="text-slate-400 block mb-0.5">Departemen Pemilik (Owner)</span>
                    <span class="font-semibold text-slate-800">{{ $document->department?->name ?? '—' }}</span>
                </div>
                <div>
                    <span class="text-slate-400 block mb-0.5">Site / Lokasi</span>
                    <span class="font-semibold text-slate-800">{{ $document->site?->name ?? 'Seluruh Site' }}</span>
                </div>
                <div>
                    <span class="text-slate-400 block mb-0.5">Versi Aktif</span>
                    <span class="font-bold text-slate-900">v{{ $document->version }}</span>
                </div>
                <div>
                    <span class="text-slate-400 block mb-0.5">Status Dokumen</span>
                    <x-hse.status :value="$document->status" />
                </div>
                <div>
                    <span class="text-slate-400 block mb-0.5">Tanggal Efektif Berlaku</span>
                    <span class="font-medium text-slate-800">{{ $document->effective_date ? $document->effective_date->format('d M Y') : '—' }}</span>
                </div>
                <div>
                    <span class="text-slate-400 block mb-0.5">Jadwal Review Ulang</span>
                    <span class="font-bold {{ $document->review_date && $document->review_date < now() ? 'text-rose-600' : 'text-slate-800' }}">
                        {{ $document->review_date ? $document->review_date->format('d M Y') : '—' }}
                    </span>
                </div>
                <div>
                    <span class="text-slate-400 block mb-0.5">Diupload Oleh</span>
                    <span class="font-medium text-slate-800">{{ $document->uploader?->name ?? 'System' }}</span>
                </div>
                <div>
                    <span class="text-slate-400 block mb-0.5">Disetujui Oleh</span>
                    <span class="font-medium text-slate-800">{{ $document->approver?->name ?? 'Menunggu Approval' }}</span>
                </div>
            </div>

            <div class="border-t border-slate-100 pt-3">
                <span class="text-xs font-semibold text-slate-500 uppercase tracking-wider block mb-1">Deskripsi & Ruang Lingkup</span>
                <p class="text-xs text-slate-700 leading-relaxed bg-slate-50 p-3 rounded-lg border border-slate-100">
                    {{ $document->description ?: 'Tidak ada deskripsi tambahan.' }}
                </p>
            </div>

            @if($document->rejection_reason)
                <div class="border-t border-slate-100 pt-3 bg-rose-50 p-3 rounded-lg border border-rose-200">
                    <span class="text-xs font-bold text-rose-700 block mb-1">Catatan Penolakan Terakhir</span>
                    <p class="text-xs text-rose-800">{{ $document->rejection_reason }}</p>
                </div>
            @endif
        </div>

        {{-- Right: Revision History (6 cols) --}}
        <div class="lg:col-span-6 bg-white rounded-xl border border-slate-200 p-5 shadow-sm space-y-4">
            <h3 class="text-sm font-bold text-slate-900 border-b border-slate-100 pb-3">Riwayat Revisi (Revision History)</h3>

            <div class="space-y-3">
                @forelse($document->revisions->sortByDesc('revision_number') as $rev)
                    <div class="p-3.5 rounded-lg border border-slate-100 bg-slate-50/70 hover:bg-white hover:border-slate-300 transition">
                        <div class="flex items-center justify-between">
                            <span class="font-bold text-xs text-slate-900">Versi {{ $rev->version }} (Rev #{{ $rev->revision_number }})</span>
                            <span class="text-[11px] text-slate-400">{{ $rev->created_at?->format('d M Y H:i') }}</span>
                        </div>
                        <p class="text-xs text-slate-700 mt-1.5">{{ $rev->change_summary }}</p>
                        <div class="mt-2.5 flex items-center justify-between text-[11px] text-slate-500 border-t border-slate-200/60 pt-2">
                            <span>Direvisi oleh: <strong>{{ $rev->revisor?->name ?? 'User' }}</strong></span>
                            @if($rev->file_path)
                                <x-ui.link variant="secondary" icon="download" href="{{ asset('storage/' . $rev->file_path) }}" target="_blank">Unduh File Arsip</x-ui.link>
                            @endif
                        </div>
                    </div>
                @empty
                    <p class="text-xs text-slate-400 py-6 text-center">Belum ada riwayat revisi tercatat.</p>
                @endforelse
            </div>
        </div>
    </div>

    {{-- MODAL AJUKAN REVISI BARU --}}
    <div x-show="openReviseModal" class="fixed inset-0 z-50 overflow-y-auto bg-black/50 backdrop-blur-sm flex items-center justify-center p-4" style="display: none;" x-transition>
        <div class="relative w-full max-w-lg bg-white rounded-xl shadow-2xl p-6" @click.away="openReviseModal = false">
            <div class="flex items-center justify-between border-b border-slate-100 pb-3 mb-4">
                <h3 class="text-base font-bold text-slate-900">Ajukan Revisi Dokumen Versi Baru</h3>
                <button type="button" @click="openReviseModal = false" class="text-slate-400 hover:text-slate-600 text-xl font-bold">&times;</button>
            </div>

            <form action="{{ route('hse.dashboard.documents.revise', $document->id) }}" method="POST" enctype="multipart/form-data" class="space-y-4">
                @csrf
                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Nomor Versi Baru *</label>
                    <input type="text" name="version" required placeholder="Contoh: 2.0" class="w-full text-xs rounded-lg border-slate-300 focus:border-[#1646A0]">
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Ringkasan Perubahan (Change Summary) *</label>
                    <textarea name="change_summary" rows="3" required placeholder="Jelaskan pasal atau poin yang direvisi..." class="w-full text-xs rounded-lg border-slate-300 focus:border-[#1646A0]"></textarea>
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">Tanggal Efektif Baru *</label>
                        <input type="date" name="effective_date" required value="{{ date('Y-m-d') }}" class="w-full text-xs rounded-lg border-slate-300 focus:border-[#1646A0]">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">Jadwal Review Selanjutnya</label>
                        <input type="date" name="review_date" value="{{ date('Y-m-d', strtotime('+1 year')) }}" class="w-full text-xs rounded-lg border-slate-300 focus:border-[#1646A0]">
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Unggah Dokumen Revisi (PDF, DOCX) *</label>
                    <input type="file" name="file_document" required class="w-full text-xs rounded-lg border border-slate-300 file:mr-3 file:py-1.5 file:px-3 file:rounded-md file:border-0 file:text-xs file:font-semibold file:bg-blue-50 file:text-[#1646A0]">
                </div>

                <div class="flex items-center justify-end gap-2 border-t border-slate-100 pt-4">
                    <button type="button" @click="openReviseModal = false" class="px-4 py-2 text-xs font-medium text-slate-600 hover:bg-slate-100 rounded-lg">Batal</button>
                    <button type="submit" class="px-5 py-2 text-xs font-semibold text-white bg-[#1646A0] hover:bg-[#123880] rounded-lg shadow-sm">Submit Revisi untuk Review</button>
                </div>
            </form>
        </div>
    </div>

    {{-- MODAL APPROVAL / REJECTION --}}
    <div x-show="openApprovalModal" class="fixed inset-0 z-50 overflow-y-auto bg-black/50 backdrop-blur-sm flex items-center justify-center p-4" style="display: none;" x-transition>
        <div class="relative w-full max-w-md bg-white rounded-xl shadow-2xl p-6" @click.away="openApprovalModal = false">
            <div class="flex items-center justify-between border-b border-slate-100 pb-3 mb-4">
                <h3 class="text-base font-bold text-slate-900" x-text="approvalAction === 'approve_publish' ? 'Setujui & Publikasikan Dokumen' : 'Tolak & Kembalikan Dokumen ke Draft'"></h3>
                <button type="button" @click="openApprovalModal = false" class="text-slate-400 hover:text-slate-600 text-xl font-bold">&times;</button>
            </div>

            <form action="{{ route('hse.dashboard.documents.approve', $document->id) }}" method="POST" class="space-y-4">
                @csrf
                <input type="hidden" name="action" :value="approvalAction">

                <div class="p-3 rounded-lg border text-xs" :class="approvalAction === 'approve_publish' ? 'bg-emerald-50 border-emerald-200 text-emerald-800' : 'bg-rose-50 border-rose-200 text-rose-800'">
                    <span x-text="approvalAction === 'approve_publish' ? 'Dokumen akan berstatus Published dan resmi berlaku bagi seluruh personil.' : 'Dokumen akan dikembalikan ke uploader untuk direvisi ulang.'"></span>
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1" x-text="approvalAction === 'approve_publish' ? 'Catatan Persetujuan (Optional)' : 'Alasan Penolakan *'"></label>
                    <textarea name="notes" rows="3" placeholder="Tuliskan catatan atau alasan penolakan..." class="w-full text-xs rounded-lg border-slate-300 focus:border-[#1646A0]"></textarea>
                </div>

                <div class="flex items-center justify-end gap-2 border-t border-slate-100 pt-4">
                    <button type="button" @click="openApprovalModal = false" class="px-4 py-2 text-xs font-medium text-slate-600 hover:bg-slate-100 rounded-lg">Batal</button>
                    <button type="submit" class="px-5 py-2 text-xs font-semibold text-white rounded-lg shadow-sm" :class="approvalAction === 'approve_publish' ? 'bg-emerald-600 hover:bg-emerald-700' : 'bg-rose-600 hover:bg-rose-700'">
                        Konfirmasi Keputusan
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
