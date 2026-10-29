@extends('layouts.app')

@section('title', 'Daftar Inspeksi SAP SHE/HSE')

@section('content')
<x-ui.page>
    <x-ui.page-header>
        <div class="ui-page-header__copy">
            <x-ui.page-title>Daftar Inspeksi SHE/HSE</x-ui.page-title>
            <x-ui.page-description>Semua inspeksi SAP Inspeksi berdasarkan template SHE-FM.</x-ui.page-description>
        </div>
        <x-ui.page-actions>
            <x-ui.link variant="secondary" href="{{ route('hse.dashboard.sap.inspeksi.dashboard') }}">Dashboard</x-ui.link>
            <x-ui.link variant="primary" icon="plus" href="{{ route('hse.dashboard.sap.inspeksi.create') }}">Buat Inspeksi</x-ui.link>
        </x-ui.page-actions>
    </x-ui.page-header>

    <x-ui.filter-panel variant="listing">
      <x-ui.table-toolbar variant="listing">
        <div class="ui-filter-field">
            <label for="inspection-template-filter" class="ui-filter-field__label">Template</label>
            <select id="inspection-template-filter" name="template_id" class="w-full ui-input">
                <option value="">Semua</option>
                @foreach($templates as $t)
                    <option value="{{ $t->id }}" @selected(request('template_id')==$t->id)>{{ $t->code }}</option>
                @endforeach
            </select>
        </div>
        <div class="ui-filter-field">
            <label for="inspection-status-filter" class="ui-filter-field__label">Status</label>
            <select id="inspection-status-filter" name="status" class="w-full ui-input">
                <option value="">Semua</option>
                @foreach(['Draft','Submitted','Under Review','Approved','Returned','Closed'] as $st)
                    <option value="{{ $st }}" @selected(request('status')==$st)>{{ $st }}</option>
                @endforeach
            </select>
        </div>

    <x-slot:actions><x-ui.button type="submit" variant="primary" icon="filter">Terapkan</x-ui.button><x-ui.link variant="secondary" href="{{ route('hse.dashboard.sap.inspeksi.index') }}" icon="rotate-ccw">Reset</x-ui.link></x-slot:actions></x-ui.table-toolbar>
    </x-ui.filter-panel>

        <x-ui.table :paginator="$inspections">
            <thead><tr><th>No. Inspeksi</th><th>Template</th><th>Site/Dept</th><th>Inspektor</th><th>Tanggal</th><th>Status</th><th>Skor</th><th class="text-right">Aksi</th></tr></thead>
            <tbody>
                @forelse($inspections as $i)
                <tr>
                    <td class="font-medium">{{ $i->inspection_no }}</td>
                    <td>{{ $i->template->code }} - {{ Str::limit($i->template->name,40) }}</td>
                    <td>{{ $i->site->name ?? '-' }} / {{ $i->department->name ?? '-' }}</td>
                    <td>{{ $i->inspector->name ?? '-' }}</td>
                    <td>{{ $i->created_at->format('d/m/Y') }}</td>
                    <td><x-ui.status-badge>{{ $i->status }}</x-ui.status-badge></td>
                    <td>{{ $i->percentage ? number_format($i->percentage,1).'%' : '-' }}</td>
                    <td class="text-right"><x-ui.table-actions>
                        <x-ui.icon-button action="view" label="Detail" href="{{ route('hse.dashboard.sap.inspeksi.show', $i->id) }}" />
                        @if(in_array($i->status, ['Draft','Returned']))
                        <x-ui.icon-button action="checklist" label="Isi Checklist" href="{{ route('hse.dashboard.sap.inspeksi.edit', $i->id) }}" />
                        @endif
                    <x-ui.sap-record-actions module="inspeksi" :record="$i" /></x-ui.table-actions></td>
                </tr>
                @empty
                <x-ui.table-empty-state colspan="8" title="Belum ada inspeksi" message="Ubah filter atau buat inspeksi baru." />
                @endforelse
            </tbody>
        </x-ui.table>
</x-ui.page>
@endsection
