@extends('layouts.app')

@section('title', 'Jadwal Inspeksi')

@section('content')
<x-ui.page>
    <x-ui.page-header>
        <div class="ui-page-header__copy">
            <x-ui.page-title>Jadwal Inspeksi SHE/HSE</x-ui.page-title>
            <x-ui.page-description>Perencanaan inspeksi berdasarkan template, site, dan department.</x-ui.page-description>
        </div>
        <x-ui.page-actions>
            <x-ui.link variant="secondary" href="{{ route('hse.dashboard.sap.inspeksi.dashboard') }}">Dashboard</x-ui.link>
            <x-ui.link variant="primary" icon="plus" href="{{ route('hse.dashboard.sap.inspeksi.schedule.create') }}">Buat Jadwal</x-ui.link>
        </x-ui.page-actions>
    </x-ui.page-header>

    <x-ui.filter-panel variant="listing">
        <x-ui.table-toolbar variant="listing">
            <x-hse.module-scope-filters :options="$options" :filters="$filters" />
            <x-slot:actions>
                <x-ui.button type="submit" variant="primary" icon="filter">Terapkan</x-ui.button>
                <x-ui.link variant="secondary" icon="rotate-ccw" href="{{ route('hse.dashboard.sap.inspeksi.schedule.index') }}">Reset</x-ui.link>
            </x-slot:actions>
        </x-ui.table-toolbar>
    </x-ui.filter-panel>

    <x-ui.table :paginator="$schedules">
            <thead><tr><th>Template</th><th>Site / Department</th><th>Ditugaskan</th><th>Periode</th><th>Jatuh tempo</th><th>Status</th></tr></thead>
            <tbody>
                @forelse($schedules as $s)
                <tr>
                    <td class="font-medium">{{ $s->template->code }} - {{ $s->template->name }}</td>
                    <td>{{ $s->site->name ?? '-' }} / {{ $s->department->name ?? '-' }}</td>
                    <td>{{ $s->assignee->name ?? '-' }}</td>
                    <td>{{ $s->period ?? '-' }} {{ $s->year }}{{ $s->month ? '/'.$s->month : '' }}</td>
                    <td class="whitespace-nowrap">{{ $s->due_date?->format('d/m/Y') ?? '-' }}</td>
                    <td><x-ui.status-badge :variant="match($s->status) { 'Completed', 'Approved' => 'success', 'Cancelled', 'Rejected' => 'danger', 'In Progress' => 'info', default => 'warning' }">{{ $s->status }}</x-ui.status-badge></td>
                </tr>
                @empty
                <x-ui.table-empty-state colspan="6" title="Belum ada jadwal inspeksi" message="Atur site atau department, lalu buat jadwal inspeksi." />
                @endforelse
            </tbody>
    </x-ui.table>
</x-ui.page>
@endsection
