@extends('layouts.app')

@section('title', 'Dashboard PTO')

@section('content')
<x-ui.page>
    <x-ui.page-header variant="dashboard">
        <div class="ui-page-header__copy">
            <x-ui.page-title>Dashboard PTO</x-ui.page-title>
            <x-ui.page-description>Ringkasan observasi Planned Task Observation dan tindak lanjutnya.</x-ui.page-description>
        </div>
        <x-ui.page-actions>
            <x-ui.link variant="secondary" href="{{ route('hse.dashboard.sap.pto.index') }}">Daftar PTO</x-ui.link>
            <x-ui.link variant="primary" icon="plus" href="{{ route('hse.dashboard.sap.pto.create') }}">Tambah PTO</x-ui.link>
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
            <x-ui.metric-card :label="$k['label']" :value="trim($k['value'].' '.$k['unit'])" icon="chart-no-axes-combined" />
        @endforeach
    </div>

    <x-hse.chart-card title="PTO Terbaru" subtitle="10 observasi terakhir">
        <x-ui.table>
            <thead><tr><th>No PTO</th><th>Tanggal</th><th>Pengamat</th><th>Diamati</th><th>Pekerjaan</th><th>Status</th><th>Safe Rate</th><th class="text-right">Aksi</th></tr></thead>
            <tbody>
                @forelse($recent as $r)
                    <tr>
                        <td class="font-medium"><a class="text-blue-700 hover:underline" href="{{ route('hse.dashboard.sap.pto.show', $r->id) }}">{{ $r->pto_no }}</a></td>
                        <td class="whitespace-nowrap">{{ $r->observation_date?->format('d/m/Y') ?? '—' }}</td>
                        <td>{{ $r->observer?->name ?? '—' }}</td>
                        <td>{{ $r->observedEmployee?->name ?? '—' }}</td>
                        <td><x-ui.truncated-text :value="$r->work_activity" /></td>
                        <td><x-ui.status-badge>{{ $r->status }}</x-ui.status-badge></td>
                        <td class="tabular-nums">{{ $r->safe_rate ? $r->safe_rate.'%' : '—' }}</td>
                        <td class="ui-action-cell"><x-ui.table-actions><x-ui.icon-button action="view" label="Lihat PTO" href="{{ route('hse.dashboard.sap.pto.show', $r->id) }}" /><x-ui.sap-record-actions module="pto" :record="$r" /></x-ui.table-actions></td>
                    </tr>
                @empty
                    <x-ui.table-empty-state colspan="8" title="Belum ada data PTO" message="Data observasi PTO terbaru akan muncul di sini." />
                @endforelse
            </tbody>
        </x-ui.table>
    </x-hse.chart-card>
</x-ui.page>
@endsection
