@extends('layouts.app')

@section('title', 'HSE Training & Competency')

@section('content')
@php $toBars = fn (array $chart) => array_combine($chart['labels'] ?? [], $chart['data'] ?? []); @endphp
<div class="ui-page space-y-6">
    @include('hse.dashboard.partials.header', [
        'title' => 'Training & Competency',
        'subtitle' => 'Completion, mandatory compliance, sertifikasi kedaluwarsa, attendance, dan training matrix per karyawan.',
        'filters' => $filters,
        'options' => $options,
        'export' => 'training',
    ])

    <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
        @foreach($data['kpis'] as $kpi)
            <x-hse.kpi-card :kpi="$kpi" />
        @endforeach
    </div>

    <div class="grid gap-4 xl:grid-cols-2">
        <x-hse.chart-card title="Scheduled vs Completed" subtitle="Record training per bulan">
            <x-hse.line-chart :chart="$data['charts']['completion_by_month']" />
        </x-hse.chart-card>
        <x-hse.chart-card title="Status Attendance" subtitle="Distribusi status record training">
            <x-hse.donut-chart :chart="$data['charts']['attendance']" title="Status attendance" />
        </x-hse.chart-card>
    </div>

    <div class="grid gap-4 xl:grid-cols-2">
        <x-hse.chart-card title="Training per Kategori" subtitle="Distribusi record per kategori course">
            <x-ui.bar-chart :series="$toBars($data['charts']['by_category'])" label="Training per kategori" />
        </x-hse.chart-card>
        <x-hse.chart-card title="Training per Department" subtitle="Top 10 department">
            <x-ui.bar-chart :series="$toBars($data['charts']['by_department'])" label="Training per department" />
        </x-hse.chart-card>
    </div>

    <x-hse.chart-card title="Sertifikasi Akan Kedaluwarsa" subtitle="Dalam 120 hari ke depan">
        <div class="ui-table-scroll">
            <table class="ui-table">
                <thead><tr><th>Karyawan</th><th>Course</th><th>Berlaku Sampai</th></tr></thead>
                <tbody>
                    @forelse($data['charts']['expiring'] as $row)
                        <tr>
                            <td class="font-medium">{{ $row['label'] }}</td>
                            <td>{{ $row['name'] }}</td>
                            <td>{{ $row['date'] }}</td>
                        </tr>
                    @empty
                        <x-ui.table-empty-state :colspan="3" title="Tidak ada sertifikasi kedaluwarsa" message="Semua sertifikasi masih berlaku." />
                    @endforelse
                </tbody>
            </table>
        </div>
    </x-hse.chart-card>

    <x-hse.chart-card title="Training Matrix" subtitle="Status training per karyawan dan course">
        @if(empty($data['matrix']['rows']))
            <x-ui.empty-state title="Belum ada data training" message="Matrix akan tampil setelah record training tersedia." />
        @else
            <div class="ui-table-scroll">
                <table class="ui-table">
                    <thead>
                        <tr>
                            <th class="sticky left-0 bg-[#F8FAFC]">Karyawan</th>
                            @foreach($data['matrix']['courses'] as $course)
                                <th class="whitespace-nowrap" title="{{ $course->category }}">{{ $course->name }}</th>
                            @endforeach
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($data['matrix']['rows'] as $row)
                            <tr>
                                <td class="sticky left-0 bg-white font-medium">{{ $row['user'] }}</td>
                                @foreach($data['matrix']['courses'] as $course)
                                    @php $cell = $row['cells'][$course->id] ?? 'Not applicable'; @endphp
                                    <td>
                                        <x-hse.status :value="$cell" />
                                    </td>
                                @endforeach
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </x-hse.chart-card>
</div>
@endsection
