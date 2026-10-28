@extends('layouts.app')

@section('title', 'Daftar Inspeksi SAP SHE/HSE')

@section('content')
<div class="ui-page space-y-6">
    <x-ui.page-header>
        <div class="ui-page-header__copy">
            <h1 class="ui-page-title">Daftar Inspeksi SHE/HSE</h1>
            <p class="ui-page-description">Semua inspeksi SAP Inspeksi berdasarkan template SHE-FM</p>
        </div>
        <div class="ui-page-actions">
            <a href="{{ route('hse.dashboard.sap.inspeksi.dashboard') }}" class="px-3 py-2 rounded-lg border border-slate-300 text-xs font-semibold text-slate-700">Dashboard</a>
            <x-ui.link variant="primary" icon="plus" href="{{ route('hse.dashboard.sap.inspeksi.create') }}">Buat Inspeksi</x-ui.link>
        </div>
    </x-ui.page-header>

    <x-ui.table-toolbar>
        <div class="ui-filter-field">
            <label for="inspection-template-filter" class="ui-filter-field__label">Template</label>
            <select id="inspection-template-filter" name="template_id" class="text-xs rounded-lg border border-slate-300 p-2">
                <option value="">Semua</option>
                @foreach($templates as $t)
                    <option value="{{ $t->id }}" @selected(request('template_id')==$t->id)>{{ $t->code }}</option>
                @endforeach
            </select>
        </div>
        <div class="ui-filter-field">
            <label for="inspection-status-filter" class="ui-filter-field__label">Status</label>
            <select id="inspection-status-filter" name="status" class="text-xs rounded-lg border border-slate-300 p-2">
                <option value="">Semua</option>
                @foreach(['Draft','Submitted','Under Review','Approved','Returned','Closed'] as $st)
                    <option value="{{ $st }}" @selected(request('status')==$st)>{{ $st }}</option>
                @endforeach
            </select>
        </div>

    <x-slot:actions><x-ui.button type="submit" variant="primary" icon="filter">Terapkan</x-ui.button><x-ui.link href="{{ url()->current() }}" icon="rotate-ccw">Reset</x-ui.link></x-slot:actions></x-ui.table-toolbar>

    <div class="rounded-xl border border-slate-200 bg-white shadow-sm overflow-hidden">
        <table class="ui-table">
            <thead><tr><th>No. Inspeksi</th><th>Template</th><th>Site/Dept</th><th>Inspektor</th><th>Tanggal</th><th>Status</th><th>Skor</th><th class="text-right">Aksi</th></tr></thead>
            <tbody>
                @forelse($inspections as $i)
                <tr>
                    <td class="font-medium">{{ $i->inspection_no }}</td>
                    <td>{{ $i->template->code }} - {{ Str::limit($i->template->name,40) }}</td>
                    <td>{{ $i->site->name ?? '-' }} / {{ $i->department->name ?? '-' }}</td>
                    <td>{{ $i->inspector->name ?? '-' }}</td>
                    <td>{{ $i->created_at->format('d/m/Y') }}</td>
                    <td>{{ $i->status }}</td>
                    <td>{{ $i->percentage ? number_format($i->percentage,1).'%' : '-' }}</td>
                    <td class="text-right"><x-ui.table-actions>
                        <x-ui.icon-button action="view" label="Detail" href="{{ route('hse.dashboard.sap.inspeksi.show', $i->id) }}" />
                        @if(in_array($i->status, ['Draft','Returned']))
                        <x-ui.icon-button action="checklist" label="Isi Checklist" href="{{ route('hse.dashboard.sap.inspeksi.edit', $i->id) }}" />
                        @endif
                    <x-ui.sap-record-actions module="inspeksi" :record="$i" /></x-ui.table-actions></td>
                </tr>
                @empty
                <tr><td colspan="8" class="text-center text-slate-500 text-xs py-6">Belum ada inspeksi</td></tr>
                @endforelse
            </tbody>
        </table>
        <div class="ui-pagination-footer">{{ $inspections->links() }}</div>
    </div>
</div>
@endsection
