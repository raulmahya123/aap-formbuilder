@extends('layouts.app')

@section('title', 'Document Dashboard — MANDALA')

@section('content')
<div class="ui-page space-y-6" x-data="{ openUploadModal: false }">
    @include('hse.dashboard.partials.header', [
        'title' => 'Document Management Dashboard',
        'subtitle' => 'Repository resmi dokumen kebijakan SHE, SOP, Instruksi Kerja, Form digital, revisi dan peninjauan berkala.',
        'filters' => $filters,
        'options' => $options,
    ])

    {{-- Top Action Toolbar --}}
    <div class="flex flex-wrap items-center justify-between gap-3 bg-white p-4 rounded-xl border border-slate-200 shadow-sm">
        <div class="flex items-center gap-2">
            <span class="inline-flex h-3 w-3 rounded-full bg-[#1646A0]"></span>
            <span class="text-sm font-semibold text-slate-800">Controlled SHE Document Repository</span>
        </div>
        <div class="flex items-center gap-2">
            <a href="{{ route('hse.dashboard.documents.index', 'approval') }}" class="inline-flex items-center gap-1.5 rounded-lg border border-slate-300 bg-white px-3.5 py-2 text-xs font-semibold text-slate-700 hover:bg-slate-50 transition">
                Antrian Approval ({{ $inReview }})
            </a>
            <button type="button" @click="openUploadModal = true" class="inline-flex items-center gap-1.5 rounded-lg bg-[#1646A0] px-4 py-2 text-xs font-semibold text-white shadow-sm hover:bg-[#123880] transition">
                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                Unggah Dokumen Baru
            </button>
        </div>
    </div>

    {{-- 5 KPI CARDS --}}
    <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-5">
        {{-- Total Documents --}}
        <a href="{{ route('hse.dashboard.documents.index') }}" class="group block rounded-xl border border-slate-200 bg-white p-4 shadow-sm hover:border-[#1646A0] transition">
            <p class="text-[11px] font-medium text-slate-500 uppercase tracking-wider">Total Documents</p>
            <p class="mt-2 text-2xl font-bold text-slate-900 group-hover:text-[#1646A0]">{{ number_format($totalDocs) }}</p>
            <p class="mt-1 text-[11px] text-slate-400">Seluruh kategori terkontrol</p>
        </a>

        {{-- Published --}}
        <a href="{{ route('hse.dashboard.documents.index', ['status' => 'Published']) }}" class="group block rounded-xl border border-slate-200 bg-white p-4 shadow-sm hover:border-emerald-500 transition">
            <div class="flex items-center justify-between">
                <p class="text-[11px] font-medium text-slate-500 uppercase tracking-wider">Published</p>
                <span class="h-2 w-2 rounded-full bg-emerald-500"></span>
            </div>
            <p class="mt-2 text-2xl font-bold text-emerald-600">{{ number_format($published) }}</p>
            <p class="mt-1 text-[11px] text-slate-400">Dokumen aktif berlaku</p>
        </a>

        {{-- In Review / Submitted --}}
        <a href="{{ route('hse.dashboard.documents.index', 'approval') }}" class="group block rounded-xl border border-slate-200 bg-white p-4 shadow-sm hover:border-blue-500 transition">
            <div class="flex items-center justify-between">
                <p class="text-[11px] font-medium text-slate-500 uppercase tracking-wider">In Review</p>
                <span class="h-2 w-2 rounded-full bg-blue-500"></span>
            </div>
            <p class="mt-2 text-2xl font-bold text-blue-600">{{ number_format($inReview) }}</p>
            <p class="mt-1 text-[11px] text-slate-400">Menunggu persetujuan</p>
        </a>

        {{-- Expiring / Review Due --}}
        <a href="{{ route('hse.dashboard.documents.index', 'expiring') }}" class="group block rounded-xl border border-slate-200 bg-white p-4 shadow-sm hover:border-amber-500 transition">
            <div class="flex items-center justify-between">
                <p class="text-[11px] font-medium text-amber-600 uppercase tracking-wider">Expiring / Review Due</p>
                <span class="h-2 w-2 rounded-full bg-amber-500"></span>
            </div>
            <p class="mt-2 text-2xl font-bold text-amber-600">{{ number_format($expiring) }}</p>
            <p class="mt-1 text-[11px] text-amber-500">Review ≤ 30 hari</p>
        </a>

        {{-- Obsolete --}}
        <a href="{{ route('hse.dashboard.documents.index', ['status' => 'Obsolete']) }}" class="group block rounded-xl border border-slate-200 bg-white p-4 shadow-sm hover:border-slate-400 transition">
            <div class="flex items-center justify-between">
                <p class="text-[11px] font-medium text-slate-500 uppercase tracking-wider">Obsolete</p>
                <span class="h-2 w-2 rounded-full bg-slate-400"></span>
            </div>
            <p class="mt-2 text-2xl font-bold text-slate-600">{{ number_format($obsolete) }}</p>
            <p class="mt-1 text-[11px] text-slate-400">Arsip tidak berlaku</p>
        </a>
    </div>

    {{-- KATEGORI REPOSITORY CARDS (Policy, SOP, IK, Form) --}}
    <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
        {{-- Policy --}}
        <a href="{{ route('hse.dashboard.documents.index', 'policy') }}" class="group block rounded-xl border border-slate-200 bg-white p-5 shadow-sm hover:border-[#1646A0] hover:shadow-md transition">
            <div class="flex items-center justify-between">
                <span class="p-2.5 rounded-lg bg-blue-50 text-[#1646A0]">
                    <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                </span>
                <span class="text-xl font-bold text-slate-900 group-hover:text-[#1646A0]">{{ $byCategory['Policy'] }}</span>
            </div>
            <h4 class="mt-3 text-sm font-bold text-slate-800">Kebijakan (Policy)</h4>
            <p class="text-xs text-slate-500 mt-1">Komitmen puncak manajemen SHE & Lingkungan Hidup.</p>
        </a>

        {{-- SOP --}}
        <a href="{{ route('hse.dashboard.documents.index', 'sop') }}" class="group block rounded-xl border border-slate-200 bg-white p-5 shadow-sm hover:border-[#1646A0] hover:shadow-md transition">
            <div class="flex items-center justify-between">
                <span class="p-2.5 rounded-lg bg-emerald-50 text-emerald-700">
                    <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/></svg>
                </span>
                <span class="text-xl font-bold text-slate-900 group-hover:text-[#1646A0]">{{ $byCategory['SOP'] }}</span>
            </div>
            <h4 class="mt-3 text-sm font-bold text-slate-800">Prosedur Standar (SOP)</h4>
            <p class="text-xs text-slate-500 mt-1">Standar operasional prosedur lintas departemen.</p>
        </a>

        {{-- Instruksi Kerja (IK) --}}
        <a href="{{ route('hse.dashboard.documents.index', 'ik') }}" class="group block rounded-xl border border-slate-200 bg-white p-5 shadow-sm hover:border-[#1646A0] hover:shadow-md transition">
            <div class="flex items-center justify-between">
                <span class="p-2.5 rounded-lg bg-amber-50 text-amber-700">
                    <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"/></svg>
                </span>
                <span class="text-xl font-bold text-slate-900 group-hover:text-[#1646A0]">{{ $byCategory['IK'] }}</span>
            </div>
            <h4 class="mt-3 text-sm font-bold text-slate-800">Instruksi Kerja (IK)</h4>
            <p class="text-xs text-slate-500 mt-1">Petunjuk teknis pelaksanaan tugas spesifik operasional.</p>
        </a>

        {{-- Form Documents --}}
        <a href="{{ route('hse.dashboard.documents.index', 'form') }}" class="group block rounded-xl border border-slate-200 bg-white p-5 shadow-sm hover:border-[#1646A0] hover:shadow-md transition">
            <div class="flex items-center justify-between">
                <span class="p-2.5 rounded-lg bg-indigo-50 text-indigo-700">
                    <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                </span>
                <span class="text-xl font-bold text-slate-900 group-hover:text-[#1646A0]">{{ $byCategory['Form'] }}</span>
            </div>
            <h4 class="mt-3 text-sm font-bold text-slate-800">Form Documents</h4>
            <p class="text-xs text-slate-500 mt-1">Template dokumen form standar (bukan form digital builder).</p>
        </a>
    </div>

    {{-- RECENT PUBLISHED & EXPIRING DOCUMENTS --}}
    <div class="grid gap-6 lg:grid-cols-12">
        {{-- Recent Published Documents (7 cols) --}}
        <div class="lg:col-span-7 bg-white rounded-xl border border-slate-200 shadow-sm overflow-hidden">
            <div class="p-4 border-b border-slate-100 flex items-center justify-between">
                <div>
                    <h3 class="text-base font-semibold text-slate-900">Dokumen Terbaru</h3>
                    <p class="text-xs text-slate-500">Dokumen resmi yang baru diterbitkan atau diperbarui.</p>
                </div>
                <a href="{{ route('hse.dashboard.documents.index') }}" class="text-xs font-semibold text-[#1646A0] hover:underline">
                    Lihat Semua Dokumen →
                </a>
            </div>

            <div class="ui-table-scroll">
                <table class="ui-table">
                    <thead>
                        <tr>
                            <th>Kode</th>
                            <th>Judul Dokumen</th>
                            <th>Kategori</th>
                            <th>Dept Owner</th>
                            <th>Versi</th>
                            <th>Status</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($recentDocs as $doc)
                            <tr class="hover:bg-slate-50/70 transition">
                                <td class="font-bold text-slate-900">{{ $doc->document_code }}</td>
                                <td class="font-semibold text-slate-800 max-w-xs truncate">{{ $doc->title }}</td>
                                <td><span class="px-2 py-0.5 rounded text-[10px] font-semibold bg-blue-50 text-[#1646A0] border border-blue-200">{{ $doc->category }}</span></td>
                                <td>{{ $doc->department?->name ?? '—' }}</td>
                                <td class="font-bold text-slate-700">v{{ $doc->version }}</td>
                                <td><x-hse.status :value="$doc->status" /></td>
                                <td>
                                    <div class="flex items-center gap-1.5">
                                        <a href="{{ route('hse.dashboard.documents.show', $doc->id) }}" class="p-1 rounded text-slate-600 hover:text-[#1646A0]" title="Detail & Riwayat">
                                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                        </a>
                                        <a href="{{ route('hse.dashboard.documents.download', $doc->id) }}" class="p-1 rounded text-slate-600 hover:text-emerald-600" title="Download File">
                                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <x-ui.table-empty-state :colspan="7" title="Belum ada dokumen" message="Silakan unggah dokumen baru melalui tombol Unggah Dokumen." />
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        {{-- Expiring & Review Due List (5 cols) --}}
        <div class="lg:col-span-5 bg-white rounded-xl border border-slate-200 p-5 shadow-sm flex flex-col justify-between">
            <div>
                <div class="flex items-center justify-between border-b border-slate-100 pb-3 mb-3">
                    <div>
                        <h3 class="text-base font-semibold text-slate-900">Jadwal Review & Expiring</h3>
                        <p class="text-xs text-slate-500">Dokumen yang perlu ditinjau ulang (Review Due ≤ 30 hari).</p>
                    </div>
                    <a href="{{ route('hse.dashboard.documents.index', 'expiring') }}" class="text-xs font-semibold text-[#1646A0] hover:underline">
                        Lihat Semua →
                    </a>
                </div>

                <div class="space-y-3">
                    @forelse($expiringDocs as $exp)
                        @php
                            $isOverdue = $exp->review_date < now();
                        @endphp
                        <div class="p-3 rounded-lg border {{ $isOverdue ? 'border-rose-200 bg-rose-50/50' : 'border-amber-200 bg-amber-50/40' }} flex items-start justify-between gap-3">
                            <div>
                                <span class="text-xs font-bold text-slate-900">{{ $exp->document_code }} — {{ $exp->title }}</span>
                                <div class="text-[11px] text-slate-500 mt-1">
                                    Owner: <strong>{{ $exp->department?->name ?? '—' }}</strong> • Versi: v{{ $exp->version }}
                                </div>
                            </div>
                            <div class="text-right flex-shrink-0">
                                <span class="inline-flex px-2 py-0.5 rounded text-[10px] font-bold {{ $isOverdue ? 'bg-rose-100 text-rose-800' : 'bg-amber-100 text-amber-800' }}">
                                    {{ $isOverdue ? 'Lewat Review' : 'Perlu Review' }}
                                </span>
                                <div class="text-[11px] font-bold mt-1 {{ $isOverdue ? 'text-rose-600' : 'text-amber-700' }}">
                                    {{ $exp->review_date?->format('d M Y') }}
                                </div>
                            </div>
                        </div>
                    @empty
                        <div class="py-8 text-center text-slate-400">
                            <svg class="mx-auto h-8 w-8 text-slate-300" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                            <p class="mt-2 text-xs font-medium">Semua dokumen masih dalam masa berlaku review aktif.</p>
                        </div>
                    @endforelse
                </div>
            </div>

            <div class="mt-4 pt-3 border-t border-slate-100 text-[11px] text-slate-500 flex items-center justify-between">
                <span>Notifikasi otomatis dikirim ke Departemen Owner saat H-30 review date.</span>
            </div>
        </div>
    </div>

    {{-- MODAL UPLOAD DOKUMEN BARU --}}
    <div x-show="openUploadModal" class="fixed inset-0 z-50 overflow-y-auto bg-black/50 backdrop-blur-sm flex items-center justify-center p-4" style="display: none;" x-transition>
        <div class="relative w-full max-w-2xl bg-white rounded-xl shadow-2xl p-6" @click.away="openUploadModal = false">
            <div class="flex items-center justify-between border-b border-slate-100 pb-3 mb-4">
                <h3 class="text-base font-bold text-slate-900">Unggah Dokumen SHE Baru</h3>
                <button type="button" @click="openUploadModal = false" class="text-slate-400 hover:text-slate-600 text-xl font-bold">&times;</button>
            </div>

            <form action="{{ route('hse.dashboard.documents.store') }}" method="POST" enctype="multipart/form-data" class="space-y-4">
                @csrf
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">Kode Dokumen *</label>
                        <input type="text" name="document_code" required placeholder="Contoh: SOP-SHE-001" class="w-full text-xs rounded-lg border-slate-300 focus:border-[#1646A0]">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">Kategori Dokumen *</label>
                        <select name="category" required class="w-full text-xs rounded-lg border-slate-300 focus:border-[#1646A0]">
                            <option value="Policy">Policy (Kebijakan)</option>
                            <option value="SOP" selected>SOP (Standard Operating Procedure)</option>
                            <option value="IK">IK (Instruksi Kerja)</option>
                            <option value="Form">Form Dokumen</option>
                            <option value="Other">Lainnya</option>
                        </select>
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Judul Dokumen *</label>
                    <input type="text" name="title" required placeholder="Contoh: Prosedur Pengendalian Bahaya Bekerja di Ketinggian" class="w-full text-xs rounded-lg border-slate-300 focus:border-[#1646A0]">
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">Department Owner *</label>
                        <select name="department_id" required class="w-full text-xs rounded-lg border-slate-300 focus:border-[#1646A0]">
                            @foreach($departments as $dept)
                                <option value="{{ $dept->id }}">{{ $dept->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">Versi Dokumen *</label>
                        <input type="text" name="version" required value="1.0" placeholder="1.0" class="w-full text-xs rounded-lg border-slate-300 focus:border-[#1646A0]">
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Deskripsi & Ruang Lingkup</label>
                    <textarea name="description" rows="2" placeholder="Ruang lingkup penerapan dokumen..." class="w-full text-xs rounded-lg border-slate-300 focus:border-[#1646A0]"></textarea>
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">Tanggal Efektif (Effective Date) *</label>
                        <input type="date" name="effective_date" required value="{{ date('Y-m-d') }}" class="w-full text-xs rounded-lg border-slate-300 focus:border-[#1646A0]">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">Tanggal Review Ulang (Review Date)</label>
                        <input type="date" name="review_date" value="{{ date('Y-m-d', strtotime('+1 year')) }}" class="w-full text-xs rounded-lg border-slate-300 focus:border-[#1646A0]">
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Pilih File Dokumen (PDF, DOCX) *</label>
                    <input type="file" name="file_document" required class="w-full text-xs rounded-lg border border-slate-300 file:mr-3 file:py-1.5 file:px-3 file:rounded-md file:border-0 file:text-xs file:font-semibold file:bg-blue-50 file:text-[#1646A0] hover:file:bg-blue-100">
                    <p class="text-[10px] text-slate-400 mt-1">Maksimum ukuran file: 20MB.</p>
                </div>

                <div class="flex items-center justify-end gap-2 border-t border-slate-100 pt-4">
                    <button type="button" @click="openUploadModal = false" class="px-4 py-2 text-xs font-medium text-slate-600 hover:bg-slate-100 rounded-lg">Batal</button>
                    <button type="submit" class="px-5 py-2 text-xs font-semibold text-white bg-[#1646A0] hover:bg-[#123880] rounded-lg shadow-sm">Simpan & Ajukan Dokumen</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
