@extends('layouts.app')

@section('title', $title . ' — MANDALA')

@section('content')
<div class="ui-page space-y-6">
    @include('hse.dashboard.partials.header', [
        'title' => $title,
        'subtitle' => 'Repository dokumen terkontrol: pencarian kode, versi, pemilik departemen, dan masa berlaku.',
        'filters' => $filters,
        'options' => $options,
        'create' => ['route' => 'hse.dashboard.documents.create', 'label' => 'Upload Dokumen'],
        'showDashboardFilters' => false,
    ])

    {{-- Filter Toolbar --}}
    <div class="ui-filter-panel ui-module-filter-toolbar">
        <x-ui.table-toolbar action="{{ route('hse.dashboard.documents.index', $category ?: '') }}">
            <x-ui.table-search name="q" :value="request('q')" :auto-submit="false" aria-label="Cari dokumen" placeholder="Cari kode, judul, atau deskripsi dokumen…" />
            <x-hse.module-scope-filters :options="$options" :filters="$filters" :show-department="false" />

            <div class="ui-filter-field"><label class="ui-filter-field__label" for="document-status-filter">Status</label><select id="document-status-filter" name="status" class="w-full ui-input" onchange="this.form.submit()">
                <option value="">-- Semua Status --</option>
                <option value="Published" {{ request('status') === 'Published' ? 'selected' : '' }}>Published (Aktif)</option>
                <option value="In Review" {{ request('status') === 'In Review' ? 'selected' : '' }}>In Review</option>
                <option value="Submitted" {{ request('status') === 'Submitted' ? 'selected' : '' }}>Submitted</option>
                <option value="Draft" {{ request('status') === 'Draft' ? 'selected' : '' }}>Draft</option>
                <option value="Obsolete" {{ request('status') === 'Obsolete' ? 'selected' : '' }}>Obsolete (Usang)</option>
            </select></div>

            <div class="ui-filter-field"><label class="ui-filter-field__label" for="document-department-filter">Department</label><select id="document-department-filter" name="department_id" class="w-full ui-input" onchange="this.form.submit()">
                <option value="">-- Semua Departemen --</option>
                @foreach($departments as $dept)
                    <option value="{{ $dept->id }}" {{ request('department_id') == $dept->id ? 'selected' : '' }}>{{ $dept->name }}</option>
                @endforeach
            </select></div>



        <x-slot:actions><x-ui.button type="submit" variant="primary" icon="filter">Terapkan</x-ui.button><x-ui.link href="{{ url()->current() }}" icon="rotate-ccw">Reset</x-ui.link></x-slot:actions></x-ui.table-toolbar>

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
                            <td><x-ui.table-actions>
                                <div class="flex items-center gap-2">
                                    @if($doc->file_path)
                                        <x-ui.icon-button action="view" label="Preview" href="{{ route('hse.dashboard.documents.preview', $doc->id) }}" target="_blank" />
                                        <x-ui.icon-button action="download" label="Unduh" href="{{ route('hse.dashboard.documents.download', $doc->id) }}" />
                                    @else
                                        <span class="text-xs text-slate-300">—</span>
                                    @endif
                                </div>
                            </x-ui.table-actions></td>
                            <td><x-ui.table-actions>
                                <x-ui.icon-button action="view" label="Detail / Kelola" href="{{ route('hse.dashboard.documents.show', $doc->id) }}" />
                            </x-ui.table-actions></td>
                        </tr>
                    @empty
                        <x-ui.table-empty-state :colspan="10" title="Tidak ada dokumen ditemukan" message="Belum ada dokumen yang sesuai dengan kategori atau filter yang dipilih." />
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="ui-pagination-footer">
            <x-ui.table-pagination :paginator="$documents" />
        </div>
    </div>

</div>
@endsection
