@extends('layouts.app')

@section('title', 'SAP Target Management')

@section('content')
<div class="ui-page space-y-6">
    @include('hse.dashboard.partials.header', [
        'title' => 'SAP Target Management',
        'subtitle' => 'Konfigurasi target bulanan inspeksi, PTO, dan hazard report per karyawan/departemen/site.',
        'filters' => $filters,
        'options' => $options,
    ])

    @if(session('ok'))
        <div class="rounded-lg border border-emerald-200 bg-emerald-50 p-4 text-sm font-medium text-emerald-800">
            {{ session('ok') }}
        </div>
    @endif

    <div class="flex items-center justify-between">
        <div>
            <h2 class="text-base font-bold text-slate-900">Daftar Target SAP</h2>
            <p class="text-xs text-slate-500">Konfigurasi target kewajiban K3 (Inspeksi, PTO, Hazard Report) per bulan.</p>
        </div>
        <a href="{{ route('hse.dashboard.sap.targets.create') }}" class="inline-flex items-center gap-2 rounded-lg bg-[#1646A0] px-4 py-2.5 text-xs font-semibold text-white shadow hover:bg-[#123880] transition">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
            Tetapkan Target SAP Baru
        </a>
    </div>

    {{-- LIST TARGET SAP --}}
    <x-hse.chart-card title="Daftar Target SAP Terdaftar" subtitle="Target kewajiban keselamatan per entitas">
        <div class="ui-table-scroll">
            <table class="ui-table">
                <thead>
                    <tr>
                        <th>Periode</th>
                        <th>Site</th>
                        <th>Department</th>
                        <th>Target Inspeksi</th>
                        <th>Target PTO</th>
                        <th>Target Hazard</th>
                        <th>Dibuat Oleh</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($targets as $row)
                        <tr>
                            <td><strong>{{ $row->year }} - Bulan {{ $row->month }}</strong></td>
                            <td>{{ $row->site?->name ?? 'Semua Site' }}</td>
                            <td>{{ $row->department?->name ?? 'Semua Department' }}</td>
                            <td><span class="font-bold text-[#1646A0]">{{ $row->target_inspection }}</span> inspeksi</td>
                            <td><span class="font-bold text-emerald-600">{{ $row->target_pto }}</span> PTO</td>
                            <td><span class="font-bold text-amber-600">{{ $row->target_hazard }}</span> laporan</td>
                            <td>{{ $row->creator?->name ?? 'System' }}</td>
                        </tr>
                    @empty
                        <x-ui.table-empty-state :colspan="7" title="Belum ada target" message="Belum ada konfigurasi target SAP yang dibuat." />
                    @endforelse
                </tbody>
            </table>
        </div>
    <x-slot:footer>{{ $targets->links() }}</x-slot:footer>
    </x-hse.chart-card>
</div>
@endsection
