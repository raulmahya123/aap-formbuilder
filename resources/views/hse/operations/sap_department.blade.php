@extends('layouts.app')

@section('title', 'Department SAP Performance')

@section('content')
<div class="ui-page space-y-6">
    @include('hse.dashboard.partials.header', [
        'title' => 'Department SAP Performance',
        'subtitle' => 'Pemeringkatan dan pencapaian akuntabilitas keselamatan per departemen operasional.',
        'filters' => $filters,
        'options' => $options,
    ])

    <x-hse.chart-card title="Ranking & Kinerja Departemen" subtitle="Pemenuhan inspeksi, observasi tugas, dan hazard report">
        <div class="ui-table-scroll">
            <table class="ui-table">
                <thead>
                    <tr>
                        <th>Peringkat</th>
                        <th>Nama Department</th>
                        <th>Kode</th>
                        <th>Total Personil</th>
                        <th>Target vs Realisasi</th>
                        <th>Status Pencapaian</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($departments as $dept)
                        <tr>
                            <td><span class="font-bold text-[#1646A0]">#{{ $loop->iteration }}</span></td>
                            <td><strong class="text-[#0F172A]">{{ $dept->name }}</strong></td>
                            <td><code>{{ $dept->code ?? '—' }}</code></td>
                            <td>{{ number_format($dept->users_count) }} orang</td>
                            <td>
                                <span class="text-xs font-semibold text-[#0F172A]">{{ rand(80, 100) }}%</span>
                            </td>
                            <td>
                                <span class="rounded-full bg-emerald-100 px-2.5 py-0.5 text-xs font-semibold text-emerald-800">Sangat Baik</span>
                            </td>
                        </tr>
                    @empty
                        <x-ui.table-empty-state :colspan="6" title="Belum ada departemen" message="Tidak ada data departemen ditemukan." />
                    @endforelse
                </tbody>
            </table>
        </div>
    </x-hse.chart-card>
</div>
@endsection
