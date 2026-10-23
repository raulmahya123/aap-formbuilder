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

    {{-- INPUT TARGET FORM --}}
    <div class="rounded-xl border border-[#E2E8F0] bg-white p-6 shadow-sm">
        <h2 class="text-base font-semibold text-[#0F172A]">Tetapkan Target KPI Baru</h2>
        <p class="mt-1 text-xs text-[#64748B]">Target akan otomatis terhubung ke kartu KPI dan grafik tren performa aktual vs target.</p>

        <form action="{{ route('hse.dashboard.targets.store') }}" method="POST" class="mt-4 grid gap-4 sm:grid-cols-2 lg:grid-cols-5">
            @csrf
            <div>
                <label class="block text-xs font-semibold text-[#475569]">Indikator KPI</label>
                <select name="kpi_key" required class="mt-1 w-full rounded-lg border border-[#CBD5E1] p-2 text-sm focus:border-[#1646A0] focus:ring-1 focus:ring-[#1646A0]">
                    <option value="">Pilih KPI</option>
                    @foreach($kpiKeys as $k => $conf)
                        <option value="{{ $k }}">{{ $conf['label'] }} ({{ $conf['unit'] }})</option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="block text-xs font-semibold text-[#475569]">Site (Opsional)</label>
                <select name="site_id" class="mt-1 w-full rounded-lg border border-[#CBD5E1] p-2 text-sm focus:border-[#1646A0] focus:ring-1 focus:ring-[#1646A0]">
                    <option value="">Semua Site (Corporate Target)</option>
                    @foreach($options['sites'] as $s)
                        <option value="{{ $s->id }}">{{ $s->name }} ({{ $s->code }})</option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="block text-xs font-semibold text-[#475569]">Tahun</label>
                <input type="number" name="year" value="{{ $year }}" required class="mt-1 w-full rounded-lg border border-[#CBD5E1] p-2 text-sm focus:border-[#1646A0] focus:ring-1 focus:ring-[#1646A0]" />
            </div>

            <div>
                <label class="block text-xs font-semibold text-[#475569]">Nilai Target</label>
                <input type="number" step="any" name="target_value" required placeholder="0" class="mt-1 w-full rounded-lg border border-[#CBD5E1] p-2 text-sm focus:border-[#1646A0] focus:ring-1 focus:ring-[#1646A0]" />
            </div>

            <div>
                <label class="block text-xs font-semibold text-[#475569]">Arah Sukses</label>
                <select name="direction" required class="mt-1 w-full rounded-lg border border-[#CBD5E1] p-2 text-sm focus:border-[#1646A0] focus:ring-1 focus:ring-[#1646A0]">
                    <option value="lower">Lebih rendah lebih baik (cth: Incident/LTI = 0)</option>
                    <option value="higher">Lebih tinggi lebih baik (cth: Manhours, Score)</option>
                </select>
            </div>

            <div class="sm:col-span-2 lg:col-span-5 flex justify-end">
                <button type="submit" class="rounded-lg bg-[#1646A0] px-5 py-2.5 text-sm font-semibold text-white shadow hover:bg-[#123880] transition">
                    Simpan Target KPI
                </button>
            </div>
        </form>
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
