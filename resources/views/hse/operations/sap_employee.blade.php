@extends('layouts.app')

@section('title', 'Employee SAP Performance')

@section('content')
<div class="ui-page space-y-6">
    @include('hse.dashboard.partials.header', [
        'title' => 'Employee SAP Performance',
        'subtitle' => 'Kinerja dan keterlibatan setiap karyawan dalam program akuntabilitas keselamatan.',
        'filters' => $filters,
        'options' => $options,
    ])

    <x-hse.chart-card title="Keterlibatan Karyawan (SAP)" subtitle="Statistik pemenuhan SAP per individu">
        <div class="ui-table-scroll">
            <table class="ui-table">
                <thead>
                    <tr>
                        <th>Nama Karyawan</th>
                        <th>Email / NIK</th>
                        <th>Department</th>
                        <th>Peran (Role)</th>
                        <th>Status Akun</th>
                        <th>Pencapaian SAP</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($employees as $emp)
                        <tr>
                            <td><strong class="text-[#0F172A]">{{ $emp->name }}</strong></td>
                            <td>{{ $emp->email }}</td>
                            <td>{{ $emp->department?->name ?? '—' }}</td>
                            <td><span class="rounded bg-slate-100 px-2 py-0.5 text-xs font-semibold text-slate-700">{{ $emp->roleLabel() }}</span></td>
                            <td>
                                @if($emp->is_active)
                                    <span class="rounded-full bg-emerald-100 px-2 py-0.5 text-xs font-semibold text-emerald-800">Aktif</span>
                                @else
                                    <span class="rounded-full bg-rose-100 px-2 py-0.5 text-xs font-semibold text-rose-800">Non-Aktif</span>
                                @endif
                            </td>
                            <td>
                                <div class="flex items-center gap-2">
                                    <div class="w-24 bg-slate-100 rounded-full h-2">
                                        <div class="bg-blue-600 h-2 rounded-full" style="width: 85%"></div>
                                    </div>
                                    <span class="text-xs font-semibold text-[#1646A0]">Aktif Mengisi</span>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <x-ui.table-empty-state :colspan="6" title="Belum ada karyawan" message="Tidak ada data karyawan ditemukan." />
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="mt-4">
            {{ $employees->links() }}
        </div>
    </x-hse.chart-card>
</div>
@endsection
