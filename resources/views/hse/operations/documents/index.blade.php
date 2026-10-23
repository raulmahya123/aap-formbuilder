@extends('layouts.app')

@section('title', $title . ' — MANDALA')

@section('content')
<div class="ui-page space-y-6" x-data="{ openUploadModal: false }">
    @include('hse.dashboard.partials.header', [
        'title' => $title,
        'subtitle' => 'Repository dokumen terkontrol: pencarian kode, versi, pemilik departemen, dan masa berlaku.',
        'filters' => $filters,
        'options' => $options,
    ])

    {{-- Filter Toolbar --}}
    <div class="flex flex-wrap items-center justify-between gap-3 bg-white p-4 rounded-xl border border-slate-200 shadow-sm">
        <form method="GET" action="{{ route('hse.dashboard.documents.index', $category ?: '') }}" class="flex flex-wrap items-center gap-2">
            <input type="text" name="q" value="{{ request('q') }}" placeholder="Cari kode dokumen, judul, deskripsi..." class="text-xs rounded-lg border-slate-300 focus:border-[#1646A0] w-64">

            <select name="status" class="text-xs rounded-lg border-slate-300 focus:border-[#1646A0]" onchange="this.form.submit()">
                <option value="">-- Semua Status --</option>
                <option value="Published" {{ request('status') === 'Published' ? 'selected' : '' }}>Published (Aktif)</option>
                <option value="In Review" {{ request('status') === 'In Review' ? 'selected' : '' }}>In Review</option>
                <option value="Submitted" {{ request('status') === 'Submitted' ? 'selected' : '' }}>Submitted</option>
                <option value="Draft" {{ request('status') === 'Draft' ? 'selected' : '' }}>Draft</option>
                <option value="Obsolete" {{ request('status') === 'Obsolete' ? 'selected' : '' }}>Obsolete (Usang)</option>
            </select>

            <select name="department_id" class="text-xs rounded-lg border-slate-300 focus:border-[#1646A0]" onchange="this.form.submit()">
                <option value="">-- Semua Departemen --</option>
                @foreach($departments as $dept)
                    <option value="{{ $dept->id }}" {{ request('department_id') == $dept->id ? 'selected' : '' }}>{{ $dept->name }}</option>
                @endforeach
            </select>

            <button type="submit" class="rounded-lg bg-slate-800 px-3 py-2 text-xs font-semibold text-white hover:bg-slate-700 transition">Filter</button>
            @if(request()->anyFilled(['q', 'status', 'department_id']))
                <a href="{{ route('hse.dashboard.documents.index', $category ?: '') }}" class="text-xs text-slate-500 hover:text-slate-800 py-2 px-1">Reset</a>
            @endif
        </form>

        <div class="flex items-center gap-2">
            <button type="button" @click="openUploadModal = true" class="inline-flex items-center gap-1.5 rounded-lg bg-[#1646A0] px-4 py-2 text-xs font-semibold text-white shadow-sm hover:bg-[#123880] transition">
                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                Unggah Dokumen
            </button>
        </div>
    </div>

    {{-- Main Documents Table --}}
    <div class="bg-white rounded-xl border border-slate-200 shadow-sm overflow-hidden">
        <div class="ui-table-scroll">
            <table class="ui-table">
                <thead>
                    <tr>
                        <th>Kode Dokumen</th>
                        <th>Judul Dokumen</th>
                        <th>Kategori</th>
                        <th>Dept Owner</th>
                        <th>Versi</th>
                        <th>Effective Date</th>
                        <th>Review Date</th>
                        <th>Status</th>
                        <th>File</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($documents as $doc)
                        @php
                            $isOverdue = $doc->review_date && $doc->review_date < now() && $doc->status === 'Published';
                        @endphp
                        <tr class="hover:bg-slate-50/70 transition">
                            <td class="font-bold text-slate-900">
                                <a href="{{ route('hse.dashboard.documents.show', $doc->id) }}" class="text-[#1646A0] hover:underline">
                                    {{ $doc->document_code }}
                                </a>
                            </td>
                            <td>
                                <div class="font-semibold text-slate-800 max-w-sm">{{ $doc->title }}</div>
                                <div class="text-[11px] text-slate-400 truncate max-w-sm" title="{{ $doc->description }}">{{ $doc->description }}</div>
                            </td>
                            <td><span class="px-2 py-0.5 rounded text-[10px] font-semibold bg-blue-50 text-[#1646A0] border border-blue-200">{{ $doc->category }}</span></td>
                            <td>{{ $doc->department?->name ?? '—' }}</td>
                            <td class="font-bold text-slate-700">v{{ $doc->version }}</td>
                            <td>{{ $doc->effective_date ? $doc->effective_date->format('d M Y') : '—' }}</td>
                            <td>
                                <span class="{{ $isOverdue ? 'text-rose-600 font-bold bg-rose-50 px-2 py-0.5 rounded' : 'text-slate-700' }}">
                                    {{ $doc->review_date ? $doc->review_date->format('d M Y') : '—' }}
                                    @if($isOverdue)
                                        <span class="block text-[10px] text-rose-500 font-normal">REVIEW OVERDUE</span>
                                    @endif
                                </span>
                            </td>
                            <td><x-hse.status :value="$doc->status" /></td>
                            <td>
                                <div class="flex items-center gap-2">
                                    @if($doc->file_path)
                                        <a href="{{ route('hse.dashboard.documents.preview', $doc->id) }}" target="_blank" class="text-xs font-semibold text-blue-600 hover:underline inline-flex items-center gap-1">
                                            Preview
                                        </a>
                                        <a href="{{ route('hse.dashboard.documents.download', $doc->id) }}" class="text-xs font-semibold text-emerald-600 hover:underline">
                                            Unduh
                                        </a>
                                    @else
                                        <span class="text-xs text-slate-300">—</span>
                                    @endif
                                </div>
                            </td>
                            <td>
                                <a href="{{ route('hse.dashboard.documents.show', $doc->id) }}" class="inline-flex items-center gap-1 rounded bg-[#1646A0] px-2.5 py-1 text-xs font-semibold text-white hover:bg-[#123880] transition">
                                    Detail / Kelola
                                </a>
                            </td>
                        </tr>
                    @empty
                        <x-ui.table-empty-state :colspan="10" title="Tidak ada dokumen ditemukan" message="Belum ada dokumen yang sesuai dengan kategori atau filter yang dipilih." />
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($documents->hasPages())
            <div class="p-4 border-t border-slate-100">
                {{ $documents->links() }}
            </div>
        @endif
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
                            <option value="Policy" {{ $category === 'policy' ? 'selected' : '' }}>Policy (Kebijakan)</option>
                            <option value="SOP" {{ $category === 'sop' ? 'selected' : '' }}>SOP (Standard Operating Procedure)</option>
                            <option value="IK" {{ $category === 'ik' ? 'selected' : '' }}>IK (Instruksi Kerja)</option>
                            <option value="Form" {{ $category === 'form' ? 'selected' : '' }}>Form Dokumen</option>
                            <option value="Other">Lainnya</option>
                        </select>
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Judul Dokumen *</label>
                    <input type="text" name="title" required placeholder="Judul resmi dokumen..." class="w-full text-xs rounded-lg border-slate-300 focus:border-[#1646A0]">
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
                        <input type="text" name="version" required value="1.0" class="w-full text-xs rounded-lg border-slate-300 focus:border-[#1646A0]">
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Deskripsi / Ruang Lingkup</label>
                    <textarea name="description" rows="2" placeholder="Ruang lingkup penerapan dokumen..." class="w-full text-xs rounded-lg border-slate-300 focus:border-[#1646A0]"></textarea>
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">Tanggal Efektif *</label>
                        <input type="date" name="effective_date" required value="{{ date('Y-m-d') }}" class="w-full text-xs rounded-lg border-slate-300 focus:border-[#1646A0]">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">Tanggal Review Ulang</label>
                        <input type="date" name="review_date" value="{{ date('Y-m-d', strtotime('+1 year')) }}" class="w-full text-xs rounded-lg border-slate-300 focus:border-[#1646A0]">
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">File Dokumen (PDF, DOCX) *</label>
                    <input type="file" name="file_document" required class="w-full text-xs rounded-lg border border-slate-300 file:mr-3 file:py-1.5 file:px-3 file:rounded-md file:border-0 file:text-xs file:font-semibold file:bg-blue-50 file:text-[#1646A0] hover:file:bg-blue-100">
                </div>

                <div class="flex items-center justify-end gap-2 border-t border-slate-100 pt-4">
                    <button type="button" @click="openUploadModal = false" class="px-4 py-2 text-xs font-medium text-slate-600 hover:bg-slate-100 rounded-lg">Batal</button>
                    <button type="submit" class="px-5 py-2 text-xs font-semibold text-white bg-[#1646A0] hover:bg-[#123880] rounded-lg shadow-sm">Simpan Dokumen</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
