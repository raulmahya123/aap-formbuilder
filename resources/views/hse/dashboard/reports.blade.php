@extends('layouts.app')

@section('title', 'HSE Reports')

@section('content')
<div class="ui-page space-y-6">
    @include('hse.dashboard.partials.header', [
        'title' => 'HSE Reports',
        'subtitle' => 'Katalog laporan HSE dengan periode dan filter yang sama. Unduh CSV atau buka dashboard terkait.',
        'filters' => $filters,
        'options' => $options,
    ])

    <section>
        <h2 class="mb-3 text-sm font-semibold uppercase tracking-wide text-[#64748B]">Katalog Laporan</h2>
        <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-3">
            @foreach($data['catalog'] as $report)
                <article class="flex h-full flex-col rounded-lg border border-[#E2E8F0] bg-white p-4">
                    <h3 class="text-sm font-semibold text-[#0F172A]">{{ $report['title'] }}</h3>
                    <p class="mt-1 flex-1 text-xs text-[#64748B]">{{ $report['description'] }}</p>
                    <div class="mt-3 flex flex-wrap items-center gap-2">
                        <a class="ui-button ui-button--primary" href="{{ route('hse.dashboard.reports.export', ['report' => $report['key']] + request()->query()) }}">
                            <x-ui.lucide name="download" /> CSV
                        </a>
                        <a class="ui-button" href="{{ route($report['route'], request()->query()) }}">Buka</a>
                    </div>
                </article>
            @endforeach
        </div>
    </section>

    <x-hse.chart-card title="Riwayat Generate Laporan" subtitle="20 aktivitas terakhir">
        <div class="ui-table-scroll">
            <table class="ui-table">
                <thead><tr><th>Laporan</th><th>Periode</th><th>Format</th><th>Status</th><th>Dibuat Oleh</th><th>Waktu</th></tr></thead>
                <tbody>
                    @forelse($data['history'] as $run)
                        <tr>
                            <td class="font-medium">{{ $run['title'] }}</td>
                            <td>{{ $run['period'] }}</td>
                            <td>{{ $run['format'] }}</td>
                            <td><x-hse.status :value="$run['status']" /></td>
                            <td>{{ $run['generated_by'] }}</td>
                            <td>{{ $run['created_at'] }}</td>
                        </tr>
                    @empty
                        <x-ui.table-empty-state :colspan="6" title="Belum ada riwayat" message="Riwayat akan tercatat setelah laporan diunduh." />
                    @endforelse
                </tbody>
            </table>
        </div>
    </x-hse.chart-card>
</div>
@endsection
