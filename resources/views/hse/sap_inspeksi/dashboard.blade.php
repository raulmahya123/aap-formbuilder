@extends('layouts.app')

@section('title', 'SAP Inspeksi SHE/HSE')

@section('content')
<div class="ui-page space-y-6">
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        @include('hse.dashboard.partials.header', [
            'title' => 'SAP Inspeksi SHE/HSE',
            'subtitle' => 'Modul Inspeksi SHE dengan template SHE-FM, checklist dinamis, penilaian, temuan & tindakan perbaikan',
            'filters' => $filters,
            'options' => $options,
        ])
    </div>

    <div class="flex flex-wrap justify-end gap-2 -mt-4">
        <a href="{{ route('hse.dashboard.sap.inspeksi.create') }}" class="inline-flex items-center gap-2 px-4 py-2 rounded-lg bg-[#1646A0] text-white text-xs font-semibold shadow-sm hover:bg-[#0B2345] transition-colors">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
            Buat Inspeksi Baru
        </a>
        <a href="{{ route('hse.dashboard.sap.inspeksi.index') }}" class="inline-flex items-center gap-2 px-4 py-2 rounded-lg border border-slate-300 text-xs font-semibold text-slate-700 hover:bg-slate-50">Daftar Inspeksi</a>
        <a href="{{ route('hse.dashboard.sap.inspeksi.templates') }}" class="inline-flex items-center gap-2 px-4 py-2 rounded-lg border border-slate-300 text-xs font-semibold text-slate-700 hover:bg-slate-50">Template SHE-FM</a>
        <a href="{{ route('hse.dashboard.sap.inspeksi.schedule.index') }}" class="inline-flex items-center gap-2 px-4 py-2 rounded-lg border border-slate-300 text-xs font-semibold text-slate-700 hover:bg-slate-50">Jadwal Inspeksi</a>
        <a href="{{ route('hse.dashboard.sap.inspeksi.reports') }}" class="inline-flex items-center gap-2 px-4 py-2 rounded-lg border border-slate-300 text-xs font-semibold text-slate-700 hover:bg-slate-50">Laporan</a>
    </div>

    <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
        @foreach($kpis as $kpi)
            <div class="rounded-xl border border-slate-200 bg-white p-4 shadow-sm">
                <p class="text-xs font-medium text-slate-500">{{ $kpi['label'] }}</p>
                <p class="mt-2 text-2xl font-bold text-slate-900">{{ number_format($kpi['value']) }} <span class="text-sm font-medium text-slate-500">{{ $kpi['unit'] }}</span></p>
            </div>
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
        <x-hse.chart-card title="Template Aktif" subtitle="Jumlah inspeksi per template">
            <div class="ui-table-scroll">
                <table class="ui-table">
                    <thead><tr><th>Kode</th><th>Nama Template</th><th>Jumlah Inspeksi</th></tr></thead>
                    <tbody>
                        @foreach($templates as $t)
                        <tr><td class="font-medium">{{ $t->code }}</td><td>{{ $t->name }}</td><td>{{ $t->inspections_count }}</td></tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </x-hse.chart-card>
    </div>

    <x-hse.chart-card title="Inspeksi Terbaru" subtitle="10 inspeksi terakhir">
        <div class="ui-table-scroll">
            <table class="ui-table">
                <thead><tr><th>No. Inspeksi</th><th>Template</th><th>Site</th><th>Inspektor</th><th>Status</th><th>Skor</th><th></th></tr></thead>
                <tbody>
                    @forelse($recent as $r)
                    <tr>
                        <td class="font-medium">{{ $r->inspection_no }}</td>
                        <td>{{ $r->template->code ?? '-' }}</td>
                        <td>{{ $r->site->name ?? '-' }}</td>
                        <td>{{ $r->inspector->name ?? '-' }}</td>
                        <td><span class="px-2 py-0.5 rounded-full text-xs">{{ $r->status }}</span></td>
                        <td>{{ $r->percentage ? number_format($r->percentage,1).'%': '-' }}</td>
                        <td><x-ui.table-actions><x-ui.icon-button action="view" label="Lihat" href="{{ route('hse.dashboard.sap.inspeksi.show', $r->id) }}" /><x-ui.sap-record-actions module="inspeksi" :record="$r" /></x-ui.table-actions></td>
                    </tr>
                    @empty
                    <tr><td colspan="7" class="text-center text-slate-500 text-xs py-4">Belum ada data</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </x-hse.chart-card>
</div>
@endsection
