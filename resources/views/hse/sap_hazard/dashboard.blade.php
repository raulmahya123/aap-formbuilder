@extends('layouts.app')

@section('title', 'Dashboard Hazard Report')

@section('content')
<x-ui.page>
    <x-ui.page-header variant="dashboard">
        <div class="ui-page-header__copy">
            <x-ui.page-title>Dashboard Hazard Report</x-ui.page-title>
            <x-ui.page-description>Ringkasan laporan bahaya SHE-FM-007 dan tindak lanjutnya.</x-ui.page-description>
        </div>
        <x-ui.page-actions>
            <x-ui.link variant="secondary" href="{{ route('hse.dashboard.sap.hazard.index') }}">Daftar Hazard</x-ui.link>
            <x-ui.link variant="primary" icon="plus" href="{{ route('hse.dashboard.sap.hazard.create') }}">Tambah Laporan</x-ui.link>
        </x-ui.page-actions>
    </x-ui.page-header>

    <x-ui.filter-panel variant="dashboard">
        <div class="ui-filter-summary">
            <span class="ui-filter-summary__title"><x-ui.lucide name="filter" /> Filter dashboard</span>
            <span>{{ $filters['label'] }} · {{ $filters['from']->format('d M Y') }} – {{ $filters['to']->format('d M Y') }}</span>
        </div>
        <x-hse.filter-bar :filters="$filters" :options="$options" variant="dashboard" />
    </x-ui.filter-panel>

    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-3">
        @foreach($kpis as $k)
            <x-ui.metric-card :label="$k['label']" :value="trim($k['value'].' '.$k['unit'])" icon="alert-triangle" />
        @endforeach
    </div>

    <x-hse.chart-card title="Hazard Report Terbaru" subtitle="10 laporan bahaya terakhir">
        <x-ui.table>
            <thead><tr><th>No Hazard</th><th>Tanggal</th><th>Pelapor</th><th>Lokasi</th><th>Deskripsi</th><th>Risiko</th><th>Status</th><th class="text-right">Aksi</th></tr></thead>
            <tbody>
                @forelse($recent as $r)
                    <tr>
                        <td class="font-medium"><a class="text-blue-700 hover:underline" href="{{ route('hse.dashboard.sap.hazard.show', $r->id) }}">{{ $r->hazard_no }}</a></td>
                        <td class="whitespace-nowrap">{{ $r->report_date?->format('d/m/Y') ?? '—' }}</td>
                        <td>{{ $r->reporter?->name ?? '—' }}</td>
                        <td><x-ui.truncated-text :value="$r->location" /></td>
                        <td><x-ui.truncated-text :value="$r->hazard_description" /></td>
                        <td><x-ui.status-badge :variant="match(strtolower((string) $r->risk_level)) { 'low'=>'success','medium'=>'warning','high','extreme'=>'danger',default=>'neutral' }">{{ $r->risk_level ?: '—' }}{{ $r->risk_score !== null ? ' ('.$r->risk_score.')' : '' }}</x-ui.status-badge></td>
                        <td><x-ui.status-badge>{{ $r->status }}</x-ui.status-badge></td>
                        <td class="ui-action-cell"><x-ui.table-actions><x-ui.icon-button action="view" label="Lihat laporan" href="{{ route('hse.dashboard.sap.hazard.show', $r->id) }}" /><x-ui.sap-record-actions module="hazard" :record="$r" /></x-ui.table-actions></td>
                    </tr>
                @empty
                    <x-ui.table-empty-state colspan="8" title="Belum ada hazard report" message="Laporan bahaya terbaru akan muncul di sini." />
                @endforelse
            </tbody>
        </x-ui.table>
    </x-hse.chart-card>
</x-ui.page>
@endsection
