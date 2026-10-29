@extends('layouts.app')

@section('title', 'SAP Inspeksi SHE/HSE')

@section('content')
<x-ui.page>
    <x-ui.page-header variant="dashboard">
        <div class="ui-page-header__copy">
            <x-ui.page-title>SAP Inspeksi SHE/HSE</x-ui.page-title>
            <x-ui.page-description>Modul inspeksi SHE dengan template SHE-FM, checklist, penilaian, temuan, dan tindak lanjut.</x-ui.page-description>
        </div>
        <x-ui.page-actions>
            <x-ui.link variant="secondary" href="{{ route('hse.dashboard.sap.inspeksi.index') }}">Daftar Inspeksi</x-ui.link>
            <x-ui.link variant="primary" icon="plus" href="{{ route('hse.dashboard.sap.inspeksi.create') }}">Buat Inspeksi</x-ui.link>
        </x-ui.page-actions>
    </x-ui.page-header>

    <x-ui.filter-panel variant="dashboard">
        <div class="ui-filter-summary">
            <span class="ui-filter-summary__title"><x-ui.lucide name="filter" /> Filter dashboard</span>
            <span>{{ $filters['label'] }} · {{ $filters['from']->format('d M Y') }} – {{ $filters['to']->format('d M Y') }}</span>
        </div>
        <x-hse.filter-bar :filters="$filters" :options="$options" variant="dashboard" />
    </x-ui.filter-panel>

    <nav class="flex flex-wrap justify-end gap-2" aria-label="Navigasi Inspeksi SHE/HSE">
        <x-ui.link variant="secondary" href="{{ route('hse.dashboard.sap.inspeksi.templates') }}">Template SHE-FM</x-ui.link>
        <x-ui.link variant="secondary" href="{{ route('hse.dashboard.sap.inspeksi.schedule.index') }}">Jadwal Inspeksi</x-ui.link>
        <x-ui.link variant="secondary" href="{{ route('hse.dashboard.sap.inspeksi.reports') }}">Laporan</x-ui.link>
    </nav>

    <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
        @foreach($kpis as $kpi)
            <x-ui.metric-card :label="$kpi['label']" :value="trim(number_format($kpi['value']).' '.$kpi['unit'])" icon="chart-no-axes-combined" />
        @endforeach
    </div>

    <div class="grid gap-4 xl:grid-cols-2">
        <x-hse.chart-card title="Status Temuan" subtitle="Distribusi temuan inspeksi">
            <div class="grid grid-cols-2 gap-4 p-4">
                <div class="rounded-lg bg-rose-50 p-3 text-center"><p class="text-xs text-rose-700">Open</p><p class="text-xl font-bold text-rose-900">{{ $findingStats['open'] }}</p></div>
                <div class="rounded-lg bg-amber-50 p-3 text-center"><p class="text-xs text-amber-700">In Progress</p><p class="text-xl font-bold text-amber-900">{{ $findingStats['in_progress'] }}</p></div>
                <div class="rounded-lg bg-blue-50 p-3 text-center"><p class="text-xs text-blue-700">For Verification</p><p class="text-xl font-bold text-blue-900">{{ $findingStats['for_verification'] }}</p></div>
                <div class="rounded-lg bg-emerald-50 p-3 text-center"><p class="text-xs text-emerald-700">Closed/Verified</p><p class="text-xl font-bold text-emerald-900">{{ $findingStats['closed'] }}</p></div>
            </div>
        </x-hse.chart-card>
    </div>
        @php
            $inspectionDashboardSections = [
                ['key' => 'templates', 'title' => 'Template Aktif', 'description' => 'Jumlah inspeksi yang menggunakan tiap template.', 'count' => $templates->count(), 'icon' => 'file-text'],
                ['key' => 'recent', 'title' => 'Inspeksi Terbaru', 'description' => 'Inspeksi yang baru tercatat.', 'count' => count($recent), 'icon' => 'history'],
            ];
        @endphp
        <x-ui.dashboard-section-picker :items="$inspectionDashboardSections">
        <div x-show="active === 'templates'" x-cloak>
        <x-hse.chart-card title="Template Aktif" subtitle="Jumlah inspeksi per template">
            <x-ui.table>
                    <thead><tr><th>Kode</th><th>Nama Template</th><th>Jumlah Inspeksi</th></tr></thead>
                    <tbody>
                        @foreach($templates as $t)
                        <tr><td class="font-medium">{{ $t->code }}</td><td>{{ $t->name }}</td><td>{{ $t->inspections_count }}</td></tr>
                        @endforeach
                    </tbody>
            </x-ui.table>
        </x-hse.chart-card>
        </div>
        <div x-show="active === 'recent'" x-cloak>
    <x-hse.chart-card title="Inspeksi Terbaru" subtitle="10 inspeksi terakhir">
        <x-ui.table>
                <thead><tr><th>No. Inspeksi</th><th>Template</th><th>Site</th><th>Inspektor</th><th>Status</th><th>Skor</th><th class="text-right">Aksi</th></tr></thead>
                <tbody>
                    @forelse($recent as $r)
                    <tr>
                        <td class="font-medium">{{ $r->inspection_no }}</td>
                        <td>{{ $r->template->code ?? '-' }}</td>
                        <td>{{ $r->site->name ?? '-' }}</td>
                        <td>{{ $r->inspector->name ?? '-' }}</td>
                        <td><x-ui.status-badge>{{ $r->status }}</x-ui.status-badge></td>
                        <td>{{ $r->percentage ? number_format($r->percentage,1).'%': '-' }}</td>
                        <td><x-ui.table-actions><x-ui.icon-button action="view" label="Lihat" href="{{ route('hse.dashboard.sap.inspeksi.show', $r->id) }}" /><x-ui.sap-record-actions module="inspeksi" :record="$r" /></x-ui.table-actions></td>
                    </tr>
                    @empty
                    <x-ui.table-empty-state colspan="7" title="Belum ada inspeksi" message="Data inspeksi terbaru akan muncul di sini." />
                    @endforelse
                </tbody>
        </x-ui.table>
    </x-hse.chart-card>
        </div>
        </x-ui.dashboard-section-picker>
</x-ui.page>
@endsection
