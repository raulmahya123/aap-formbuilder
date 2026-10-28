@extends('layouts.app')

@section('title', 'Laporan SAP Inspeksi')

@section('content')
<div class="ui-page space-y-6">
    <x-ui.page-header>
        <div class="ui-page-header__copy">
            <h1 class="ui-page-title">Laporan SAP Inspeksi SHE/HSE</h1>
            <p class="ui-page-description">Rekap inspeksi, skor, temuan & status persetujuan</p>
        </div>
        <div class="ui-page-actions">
            <a href="{{ route('hse.dashboard.sap.inspeksi.dashboard') }}" class="px-3 py-2 rounded-lg border border-slate-300 text-xs font-semibold text-slate-700">Dashboard</a>
        </div>
    </x-ui.page-header>

    <x-ui.table-toolbar>
        <div>
            <label class="block text-xs font-semibold text-slate-700 mb-1">Template</label>
            <select name="template_id" class="text-xs rounded-lg border border-slate-300 p-2">
                <option value="">Semua</option>
                @foreach($templates as $t)
                    <option value="{{ $t->id }}" @selected(request('template_id')==$t->id)>{{ $t->code }}</option>
                @endforeach
            </select>
        </div>

    <x-slot:actions><x-ui.button type="submit" variant="primary" icon="filter">Terapkan</x-ui.button><x-ui.link href="{{ url()->current() }}" icon="rotate-ccw">Reset</x-ui.link></x-slot:actions></x-ui.table-toolbar>

    <div class="rounded-xl border border-slate-200 bg-white shadow-sm overflow-hidden">
        <table class="ui-table">
            <thead><tr><th>No. Inspeksi</th><th>Template</th><th>Site</th><th>Dept</th><th>Inspektor</th><th>Diajukan</th><th>Skor</th><th>%</th><th>Status</th><th class="text-right">Aksi</th></tr></thead>
            <tbody>
                @forelse($inspections as $i)
                <tr>
                    <td class="font-medium">{{ $i->inspection_no }}</td>
                    <td>{{ $i->template->code }}</td>
                    <td>{{ $i->site->name ?? '-' }}</td>
                    <td>{{ $i->department->name ?? '-' }}</td>
                    <td>{{ $i->inspector->name ?? '-' }}</td>
                    <td>{{ $i->submitted_at ? $i->submitted_at->format('d/m/Y') : '-' }}</td>
                    <td>{{ $i->score ?? '-' }}</td>
                    <td>{{ $i->percentage ? number_format($i->percentage,1).'%' : '-' }}</td>
                    <td>{{ $i->status }}</td>
                    <td><x-ui.table-actions><x-ui.icon-button action="view" label="Detail" href="{{ route('hse.dashboard.sap.inspeksi.show', $i->id) }}" /><x-ui.sap-record-actions module="inspeksi" :record="$i" /></x-ui.table-actions></td>
                </tr>
                @empty
                <tr><td colspan="10" class="text-center text-slate-500 text-xs py-6">Tidak ada data</td></tr>
                @endforelse
            </tbody>
        </table>
        <div class="ui-pagination-footer">{{ $inspections->links() }}</div>
    </div>
</div>
@endsection
