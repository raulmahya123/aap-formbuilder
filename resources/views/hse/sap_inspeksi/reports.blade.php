@extends('layouts.app')

@section('title', 'Laporan SAP Inspeksi')

@section('content')
<x-ui.page>
    <x-ui.page-header>
        <div class="ui-page-header__copy">
            <x-ui.page-title>Laporan SAP Inspeksi SHE/HSE</x-ui.page-title>
            <x-ui.page-description>Rekap inspeksi, skor, temuan, dan status persetujuan.</x-ui.page-description>
        </div>
        <x-ui.page-actions><x-ui.link variant="secondary" href="{{ route('hse.dashboard.sap.inspeksi.dashboard') }}">Dashboard</x-ui.link></x-ui.page-actions>
    </x-ui.page-header>

    <x-ui.filter-panel variant="listing">
      <x-ui.table-toolbar variant="listing">
        <div class="ui-filter-field">
            <label for="inspection-report-template" class="ui-filter-field__label">Template</label>
            <select id="inspection-report-template" name="template_id" class="w-full ui-input">
                <option value="">Semua</option>
                @foreach($templates as $t)
                    <option value="{{ $t->id }}" @selected(request('template_id')==$t->id)>{{ $t->code }}</option>
                @endforeach
            </select>
        </div>

    <x-slot:actions><x-ui.button type="submit" variant="primary" icon="filter">Terapkan</x-ui.button><x-ui.link variant="secondary" href="{{ route('hse.dashboard.sap.inspeksi.reports') }}" icon="rotate-ccw">Reset</x-ui.link></x-slot:actions></x-ui.table-toolbar>
    </x-ui.filter-panel>

        <x-ui.table :paginator="$inspections">
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
                    <td><x-ui.status-badge>{{ $i->status }}</x-ui.status-badge></td>
                    <td><x-ui.table-actions><x-ui.icon-button action="view" label="Detail" href="{{ route('hse.dashboard.sap.inspeksi.show', $i->id) }}" /><x-ui.sap-record-actions module="inspeksi" :record="$i" /></x-ui.table-actions></td>
                </tr>
                @empty
                <x-ui.table-empty-state colspan="10" title="Belum ada laporan inspeksi" message="Sesuaikan filter untuk melihat hasil lainnya." />
                @endforelse
            </tbody>
        </x-ui.table>
</x-ui.page>
@endsection
