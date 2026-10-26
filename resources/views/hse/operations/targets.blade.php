@extends('layouts.app')

@section('title', 'SHE KPI Target Management')

@section('content')
<div class="ui-page space-y-6">
    @include('hse.dashboard.partials.header', [
        'title' => 'KPI Target Management',
        'subtitle' => 'Konfigurasi target tahunan & per-site untuk indikator keselamatan dan kinerja operasional.',
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
            <h2 class="text-base font-bold text-slate-900">Daftar Target KPI</h2>
            <p class="text-xs text-slate-500">Konfigurasi target tahunan &amp; per-site untuk indikator keselamatan dan kinerja operasional.</p>
        </div>
        <a href="{{ route('hse.dashboard.targets.create') }}" class="inline-flex items-center gap-2 rounded-lg bg-[#1646A0] px-4 py-2.5 text-xs font-semibold text-white shadow hover:bg-[#123880] transition">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
            Tetapkan Target KPI Baru
        </a>
    </div>

    {{-- LIST TARGETS --}}
    <x-hse.chart-card title="Daftar Target KPI Terdaftar (Tahun {{ $year }})" subtitle="Kompilasi target performa keselamatan">
        <div class="ui-table-scroll">
            <table class="ui-table">
                <thead>
                    <tr>
                        <th>Key KPI</th>
                        <th>Indikator</th>
                        <th>Cakupan Site</th>
                        <th>Tahun</th>
                        <th>Nilai Target</th>
                        <th>Arah Target</th>
                        <th>Terakhir Diperbarui</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($targets as $t)
                        <tr>
                            <td><code>{{ $t->kpi_key }}</code></td>
                            <td><strong class="text-[#0F172A]">{{ $kpiKeys[$t->kpi_key]['label'] ?? $t->kpi_key }}</strong></td>
                            <td>{{ $t->site?->name ?? 'Seluruh Site (Corporate)' }}</td>
                            <td>{{ $t->year }}</td>
                            <td><span class="font-bold text-[#1646A0]">{{ number_format($t->target_value, 2) }}</span> {{ $t->unit ?? ($kpiKeys[$t->kpi_key]['unit'] ?? '') }}</td>
                            <td>
                                @if($t->direction === 'lower')
                                    <span class="inline-flex rounded-full bg-blue-100 px-2 py-0.5 text-xs font-semibold text-blue-800">≤ Target (Lower)</span>
                                @else
                                    <span class="inline-flex rounded-full bg-emerald-100 px-2 py-0.5 text-xs font-semibold text-emerald-800">≥ Target (Higher)</span>
                                @endif
                            </td>
                            <td>{{ $t->updated_at->format('d M Y H:i') }}</td>
                        </tr>
                    @empty
                        <x-ui.table-empty-state :colspan="7" title="Belum ada target" message="Belum ada target KPI yang ditetapkan untuk tahun {{ $year }}." />
                    @endforelse
                </tbody>
            </table>
        </div>
    </x-hse.chart-card>
</div>
@endsection
