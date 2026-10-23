@extends('layouts.app')

@section('title', 'SHE Report Center — MANDALA')

@section('content')
<div class="ui-page space-y-6">
    @include('hse.dashboard.partials.header', [
        'title' => 'SHE Report Center',
        'subtitle' => 'Pusat unduhan dan pratinjau seluruh laporan resmi SHE. Nilai konsisten dengan metrik dashboard.',
        'filters' => $filters,
        'options' => $options,
    ])

    {{-- Filter Toolbar Reminder --}}
    <div class="flex flex-wrap items-center justify-between gap-3 bg-white p-4 rounded-xl border border-slate-200 shadow-sm">
        <div class="flex items-center gap-2">
            <span class="inline-flex h-3 w-3 rounded-full bg-[#1646A0]"></span>
            <span class="text-xs font-semibold text-slate-800">Filter Aktif:</span>
            <span class="text-xs text-slate-600 bg-slate-100 px-2.5 py-1 rounded-md">{{ $filters['label'] ?? 'Semua Periode' }}</span>
            @if(!empty($filters['site_id']))
                <span class="text-xs text-slate-600 bg-slate-100 px-2.5 py-1 rounded-md">Site Filtered</span>
            @endif
        </div>
        <div class="text-xs text-slate-500">
            Export otomatis menyertakan filter tanggal kustom, site, dan departemen yang sedang dipilih.
        </div>
    </div>

    {{-- 9 REPORT CATALOG CARDS --}}
    <section>
        <div class="flex items-center justify-between mb-3">
            <h2 class="text-sm font-bold uppercase tracking-wider text-slate-700">9 Laporan Resmi SHE</h2>
            <span class="text-xs text-slate-400">Tersedia format CSV / Excel & Preview</span>
        </div>

        <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-3">
            @foreach($data['catalog'] as $report)
                <article class="flex h-full flex-col justify-between rounded-xl border border-slate-200 bg-white p-5 shadow-sm hover:border-[#1646A0] hover:shadow-md transition">
                    <div>
                        <div class="flex items-center justify-between gap-2">
                            <h3 class="text-sm font-bold text-slate-900">{{ $report['title'] }}</h3>
                            <span class="p-1.5 rounded-lg bg-blue-50 text-[#1646A0]">
                                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                            </span>
                        </div>
                        <p class="mt-2 text-xs text-slate-500 leading-relaxed">{{ $report['description'] }}</p>
                    </div>

                    <div class="mt-4 pt-4 border-t border-slate-100 flex flex-wrap items-center justify-between gap-2">
                        <div class="flex items-center gap-1.5">
                            <a class="inline-flex items-center gap-1 rounded-lg bg-[#1646A0] px-3 py-1.5 text-xs font-semibold text-white shadow-sm hover:bg-[#123880] transition" href="{{ route('hse.dashboard.reports.export', ['report' => $report['key']] + request()->query()) }}">
                                <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
                                Download Excel/CSV
                            </a>
                            <button type="button" onclick="window.print()" class="inline-flex items-center gap-1 rounded-lg border border-slate-300 bg-white px-2.5 py-1.5 text-xs font-semibold text-slate-700 hover:bg-slate-50 transition" title="Cetak / Simpan sebagai PDF">
                                PDF
                            </button>
                        </div>
                        <a class="text-xs font-semibold text-[#1646A0] hover:underline" href="{{ route($report['route'], request()->query()) }}">
                            Preview Dashboard →
                        </a>
                    </div>
                </article>
            @endforeach
        </div>
    </section>

    {{-- REPORT RUN HISTORY TABLE --}}
    <div class="bg-white rounded-xl border border-slate-200 shadow-sm overflow-hidden">
        <div class="p-4 border-b border-slate-100 flex items-center justify-between">
            <div>
                <h3 class="text-base font-semibold text-slate-900">Riwayat Unduhan Laporan (Report History)</h3>
                <p class="text-xs text-slate-500">20 aktivitas ekspor laporan terakhir yang tercatat dalam sistem.</p>
            </div>
        </div>

        <div class="ui-table-scroll">
            <table class="ui-table">
                <thead>
                    <tr>
                        <th>Nama Laporan</th>
                        <th>Periode Data</th>
                        <th>Format File</th>
                        <th>Status</th>
                        <th>Diekspor Oleh</th>
                        <th>Waktu Ekspor</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($data['history'] as $run)
                        <tr class="hover:bg-slate-50/70 transition">
                            <td class="font-bold text-slate-900">{{ $run['title'] }}</td>
                            <td>{{ $run['period'] }}</td>
                            <td><span class="inline-flex px-2 py-0.5 rounded text-[10px] font-bold bg-slate-100 text-slate-800">{{ $run['format'] }}</span></td>
                            <td><x-hse.status :value="$run['status']" /></td>
                            <td>{{ $run['generated_by'] }}</td>
                            <td>{{ $run['created_at'] }}</td>
                        </tr>
                    @empty
                        <x-ui.table-empty-state :colspan="6" title="Belum ada riwayat" message="Riwayat ekspor laporan akan otomatis tersimpan setelah diunduh." />
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
