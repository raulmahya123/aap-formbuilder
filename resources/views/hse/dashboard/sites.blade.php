@extends('layouts.app')

@section('title', 'HSE Site Performance')

@section('content')
@php $toBars = fn (array $chart) => array_combine($chart['labels'] ?? [], $chart['data'] ?? []); @endphp
<div class="ui-page space-y-6">
    @include('hse.dashboard.partials.header', [
        'showDashboardFilters' => true,
        'title' => 'Site Performance Comparison',
        'subtitle' => 'Perbandingan insiden, near miss, skor inspeksi, open action, training, dan risiko antar site.',
        'filters' => $filters,
        'options' => $options,
        'export' => 'sites',
    ])

    <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-3">
        @forelse($data['cards'] as $card)
            <article class="rounded-lg border border-[#E2E8F0] bg-white p-4">
                <header class="flex items-start justify-between gap-2">
                    <div class="min-w-0">
                        <h2 class="truncate text-sm font-semibold text-[#0F172A]">{{ $card['name'] }}</h2>
                        <p class="text-xs text-[#64748B]">{{ $card['code'] ?: '—' }} · {{ $card['company'] ?? '—' }}</p>
                    </div>
                    @if($card['high_risk'] > 0)
                        <span class="rounded bg-[#FEE2E2] px-2 py-0.5 text-[11px] font-semibold text-[#991B1B]">{{ $card['high_risk'] }} high risk</span>
                    @endif
                </header>
                <dl class="mt-3 grid grid-cols-2 gap-2 text-xs">
                    <div class="rounded bg-[#F8FAFC] p-2"><dt class="text-[#64748B]">Insiden</dt><dd class="text-base font-bold text-[#0F172A]">{{ $card['incidents'] }}</dd></div>
                    <div class="rounded bg-[#F8FAFC] p-2"><dt class="text-[#64748B]">Near Miss</dt><dd class="text-base font-bold text-[#0F172A]">{{ $card['near_miss'] }}</dd></div>
                    <div class="rounded bg-[#F8FAFC] p-2"><dt class="text-[#64748B]">Skor Inspeksi</dt><dd class="text-base font-bold text-[#0F172A]">{{ $card['inspection_score'] === null ? 'N/A' : number_format((float) $card['inspection_score'], 1, ',', '.') }}</dd></div>
                    <div class="rounded bg-[#F8FAFC] p-2"><dt class="text-[#64748B]">Open Action</dt><dd class="text-base font-bold {{ $card['open_actions'] > 0 ? 'text-[#B91C1C]' : 'text-[#0F172A]' }}">{{ $card['open_actions'] }}</dd></div>
                    <div class="col-span-2 rounded bg-[#F8FAFC] p-2">
                        <div class="flex items-center justify-between">
                            <dt class="text-[#64748B]">Training Completion</dt>
                            <dd class="font-semibold text-[#0F172A]">{{ $card['training_completion'] === null ? 'N/A' : number_format((float) $card['training_completion'], 1, ',', '.').'%' }}</dd>
                        </div>
                        <div class="mt-1.5 h-2 overflow-hidden rounded-full bg-[#E2E8F0]">
                            <div class="h-full rounded-full bg-[#16A34A]" style="width:{{ min(100, (float) ($card['training_completion'] ?? 0)) }}%"></div>
                        </div>
                    </div>
                </dl>
            </article>
        @empty
            <div class="sm:col-span-2 xl:col-span-3">
                <x-ui.empty-state title="Belum ada site" message="Tambahkan master site terlebih dahulu." />
            </div>
        @endforelse
    </div>

    <x-hse.chart-card title="Sebaran Site" subtitle="Peta koordinat site (hover untuk detail)">
        <x-hse.site-map :points="$data['map']['points']" :missing="$data['map']['missing']" />
    </x-hse.chart-card>

    <div class="grid gap-4 xl:grid-cols-2">
        <x-hse.chart-card title="Insiden per Site" subtitle="Perbandingan periode terpilih">
            <x-ui.bar-chart :series="$toBars($data['charts']['incidents'])" label="Insiden per site" />
        </x-hse.chart-card>
        <x-hse.chart-card title="Near Miss per Site" subtitle="Perbandingan periode terpilih">
            <x-ui.bar-chart :series="$toBars($data['charts']['near_miss'])" label="Near miss per site" />
        </x-hse.chart-card>
        <x-hse.chart-card title="Skor Inspeksi per Site" subtitle="Rata-rata skor inspeksi selesai">
            <x-ui.bar-chart :series="$toBars($data['charts']['inspection'])" label="Skor inspeksi per site" />
        </x-hse.chart-card>
        <x-hse.chart-card title="Open Action per Site" subtitle="Action yang belum closed">
            <x-ui.bar-chart :series="$toBars($data['charts']['actions'])" label="Open action per site" />
        </x-hse.chart-card>
    </div>

    <x-hse.chart-card title="Perbandingan Site" subtitle="Ringkasan KPI seluruh site">
        <div class="ui-table-scroll">
            <table class="ui-table">
                <thead><tr><th>Site</th><th>Kode</th><th>Perusahaan</th><th>Insiden</th><th>Near Miss</th><th>Skor Inspeksi</th><th>Open Action</th><th>Training</th><th>High Risk</th></tr></thead>
                <tbody>
                    @forelse($data['cards'] as $card)
                        <tr>
                            <td class="font-medium">{{ $card['name'] }}</td>
                            <td>{{ $card['code'] ?: '—' }}</td>
                            <td>{{ $card['company'] ?? '—' }}</td>
                            <td>{{ $card['incidents'] }}</td>
                            <td>{{ $card['near_miss'] }}</td>
                            <td>{{ $card['inspection_score'] === null ? 'N/A' : number_format((float) $card['inspection_score'], 1, ',', '.') }}</td>
                            <td>{{ $card['open_actions'] }}</td>
                            <td>{{ $card['training_completion'] === null ? 'N/A' : number_format((float) $card['training_completion'], 1, ',', '.').'%' }}</td>
                            <td>{{ $card['high_risk'] }}</td>
                        </tr>
                    @empty
                        <x-ui.table-empty-state :colspan="9" title="Belum ada site" message="Tambahkan master site terlebih dahulu." />
                    @endforelse
                </tbody>
            </table>
        </div>
    </x-hse.chart-card>
</div>
@endsection
