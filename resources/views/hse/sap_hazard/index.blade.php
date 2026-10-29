@extends('layouts.app')

@section('title', 'Daftar Hazard Report')

@section('content')
<x-ui.page>
    <x-ui.page-header>
        <div class="ui-page-header__copy">
            <x-ui.page-title>Daftar Hazard Report</x-ui.page-title>
            <x-ui.page-description>Daftar laporan bahaya, tingkat risiko, dan progres tindak lanjut.</x-ui.page-description>
        </div>
        <x-ui.page-actions>
            <x-ui.link variant="primary" icon="plus" href="{{ route('hse.dashboard.sap.hazard.create') }}">Tambah Laporan</x-ui.link>
        </x-ui.page-actions>
    </x-ui.page-header>

    <x-ui.filter-panel variant="listing">
        <x-ui.table-toolbar variant="listing">
            <x-hse.module-scope-filters :options="$options" :filters="$filters" />
            <x-ui.table-filter name="status" :value="request('status')" :options="['Draft'=>'Draft','Submitted'=>'Submitted','Under Review'=>'Under Review','Approved'=>'Approved','Returned'=>'Returned','Revised'=>'Revised','Closed'=>'Closed']" label="Status" placeholder="Semua status" />
            <x-ui.table-filter name="risk_level" :value="request('risk_level')" :options="['Low'=>'Low','Medium'=>'Medium','High'=>'High','Extreme'=>'Extreme']" label="Tingkat risiko" placeholder="Semua tingkat" />
            <x-slot:actions>
                <x-ui.button type="submit" variant="primary" icon="filter">Terapkan</x-ui.button>
                <x-ui.link variant="secondary" icon="rotate-ccw" href="{{ route('hse.dashboard.sap.hazard.index') }}">Reset</x-ui.link>
            </x-slot:actions>
        </x-ui.table-toolbar>
    </x-ui.filter-panel>

    <x-ui.table :paginator="$hazards">
        <thead><tr><th>No Hazard</th><th>Tanggal</th><th>Pelapor</th><th>Lokasi</th><th>Tipe</th><th>Tingkat risiko</th><th class="text-right">Skor</th><th>Status</th><th class="text-right">Aksi</th></tr></thead>
        <tbody>
            @forelse($hazards as $h)
                <tr>
                    <td class="font-medium">{{ $h->hazard_no }}</td>
                    <td class="whitespace-nowrap">{{ $h->report_date?->format('d/m/Y') ?? '—' }}</td>
                    <td>{{ $h->reporter?->name ?? '—' }}</td>
                    <td><x-ui.truncated-text :value="$h->location" /></td>
                    <td>{{ $h->hazard_type ?: '—' }}</td>
                    <td><x-ui.status-badge :variant="match(strtolower((string) $h->risk_level)) { 'low'=>'success','medium'=>'warning','high','extreme'=>'danger',default=>'neutral' }">{{ $h->risk_level ?: '—' }}</x-ui.status-badge></td>
                    <td class="text-right tabular-nums">{{ $h->risk_score ?? '—' }}</td>
                    <td><x-ui.status-badge>{{ $h->status }}</x-ui.status-badge></td>
                    <td class="ui-action-cell"><x-ui.table-actions>
                        <x-ui.icon-button action="view" label="Lihat laporan" href="{{ route('hse.dashboard.sap.hazard.show', $h->id) }}" />
                        @if(in_array($h->status, ['Draft','Returned','Revised']))
                            <x-ui.icon-button action="edit" label="Edit laporan" href="{{ route('hse.dashboard.sap.hazard.edit', $h->id) }}" />
                        @endif
                        <x-ui.sap-record-actions module="hazard" :record="$h" />
                    </x-ui.table-actions></td>
                </tr>
            @empty
                <x-ui.table-empty-state colspan="9" title="Belum ada hazard report" message="Ubah filter atau tambahkan laporan bahaya baru." />
            @endforelse
        </tbody>
    </x-ui.table>
</x-ui.page>
@endsection
