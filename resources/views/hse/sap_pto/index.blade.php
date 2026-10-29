@extends('layouts.app')

@section('title', 'Daftar PTO')

@section('content')
<x-ui.page>
    <x-ui.page-header>
        <div class="ui-page-header__copy">
            <x-ui.page-title>Daftar PTO</x-ui.page-title>
            <x-ui.page-description>Daftar observasi pekerjaan dan checklist Planned Task Observation.</x-ui.page-description>
        </div>
        <x-ui.page-actions>
            <x-ui.link variant="primary" icon="plus" href="{{ route('hse.dashboard.sap.pto.create') }}">Tambah PTO</x-ui.link>
        </x-ui.page-actions>
    </x-ui.page-header>

    <x-ui.filter-panel variant="listing">
        <x-ui.table-toolbar variant="listing">
            <x-hse.module-scope-filters :options="$options" :filters="$filters" />
            <x-ui.table-filter name="status" :value="request('status')" :options="['Draft'=>'Draft','Submitted'=>'Submitted','Under Review'=>'Under Review','Approved'=>'Approved','Returned'=>'Returned','Revised'=>'Revised','Closed'=>'Closed']" label="Status" placeholder="Semua status" />
            <x-slot:actions>
                <x-ui.button type="submit" variant="primary" icon="filter">Terapkan</x-ui.button>
                <x-ui.link variant="secondary" icon="rotate-ccw" href="{{ route('hse.dashboard.sap.pto.index') }}">Reset</x-ui.link>
            </x-slot:actions>
        </x-ui.table-toolbar>
    </x-ui.filter-panel>

    <x-ui.table :paginator="$ptos">
        <thead><tr><th>No PTO</th><th>Tanggal</th><th>Site</th><th>Pengamat</th><th>Diamati</th><th>Pekerjaan</th><th class="text-right">Risky</th><th>Status</th><th class="text-right">Aksi</th></tr></thead>
        <tbody>
            @forelse($ptos as $p)
                <tr>
                    <td class="font-medium">{{ $p->pto_no }}</td>
                    <td class="whitespace-nowrap">{{ $p->observation_date?->format('d/m/Y') ?? '—' }}</td>
                    <td>{{ $p->site?->name ?? '—' }}</td>
                    <td>{{ $p->observer?->name ?? '—' }}</td>
                    <td>{{ $p->observedEmployee?->name ?? '—' }}</td>
                    <td><x-ui.truncated-text :value="$p->work_activity" /></td>
                    <td class="text-right tabular-nums">{{ $p->risky_behaviors_count }}</td>
                    <td><x-ui.status-badge>{{ $p->status }}</x-ui.status-badge></td>
                    <td class="ui-action-cell"><x-ui.table-actions>
                        <x-ui.icon-button action="view" label="Lihat PTO" href="{{ route('hse.dashboard.sap.pto.show', $p->id) }}" />
                        @if(in_array($p->status, ['Draft','Returned','Revised']))
                            <x-ui.icon-button action="edit" label="Edit PTO" href="{{ route('hse.dashboard.sap.pto.edit', $p->id) }}" />
                        @endif
                        <x-ui.sap-record-actions module="pto" :record="$p" />
                    </x-ui.table-actions></td>
                </tr>
            @empty
                <x-ui.table-empty-state colspan="9" title="Belum ada laporan PTO" message="Ubah filter atau tambahkan observasi PTO baru." />
            @endforelse
        </tbody>
    </x-ui.table>
</x-ui.page>
@endsection
