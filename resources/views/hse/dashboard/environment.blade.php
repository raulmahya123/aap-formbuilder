@extends('layouts.app')

@section('title', 'HSE Environmental')

@section('content')
@php $toBars = fn (array $chart) => array_combine($chart['labels'] ?? [], $chart['data'] ?? []); @endphp
<div class="ui-page space-y-6">
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        @include('hse.dashboard.partials.header', [
            'title' => 'Environmental Performance',
            'subtitle' => 'Waste, hazardous waste, recycling rate, energy, water, fuel, insiden lingkungan, dan target lingkungan.',
            'filters' => $filters,
            'options' => $options,
            'export' => 'environment',
        ])
    </div>

    <div class="flex justify-end -mt-4">
        <button type="button" onclick="document.getElementById('modal-input-environment').showModal()" class="inline-flex items-center gap-2 px-4 py-2 rounded-lg bg-[#1646A0] text-white text-xs font-semibold shadow-sm hover:bg-[#0B2345] transition-colors">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
            Catat Pemantauan Lingkungan & Limbah
        </button>
    </div>

    @if(session('ok'))
        <div class="rounded-lg bg-emerald-50 border border-emerald-200 p-3 text-xs text-emerald-800 font-medium">
            {{ session('ok') }}
        </div>
    @endif

    <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-3">
        @foreach($data['kpis'] as $kpi)
            <x-hse.kpi-card :kpi="$kpi" />
        @endforeach
    </div>

    <div class="grid gap-4 xl:grid-cols-2">
        <x-hse.chart-card title="Tren Waste Generated" subtitle="Total waste per bulan">
            <x-hse.line-chart :chart="$data['charts']['waste_trend']" />
        </x-hse.chart-card>
        <x-hse.chart-card title="Komposisi Waste" subtitle="Hazardous, non-hazardous, dan recycled">
            <x-hse.donut-chart :chart="$data['charts']['waste_type']" title="Komposisi waste" />
        </x-hse.chart-card>
    </div>

    <div class="grid gap-4 xl:grid-cols-2">
        <x-hse.chart-card title="Recycling Rate" subtitle="Persentase waste yang didaur ulang per bulan">
            <x-hse.line-chart :chart="$data['charts']['recycling']" />
        </x-hse.chart-card>
        <x-hse.chart-card title="Target Lingkungan" subtitle="Actual vs target per metrik">
            <x-hse.line-chart :chart="$data['charts']['targets']" />
        </x-hse.chart-card>
    </div>

    <div class="grid gap-4 xl:grid-cols-3">
        <x-hse.chart-card title="Energy Consumption" subtitle="Tren bulanan">
            <x-hse.line-chart :chart="$data['charts']['energy']" />
        </x-hse.chart-card>
        <x-hse.chart-card title="Water Consumption" subtitle="Tren bulanan">
            <x-hse.line-chart :chart="$data['charts']['water']" />
        </x-hse.chart-card>
        <x-hse.chart-card title="Fuel Consumption" subtitle="Tren bulanan">
            <x-hse.line-chart :chart="$data['charts']['fuel']" />
        </x-hse.chart-card>
    </div>

    <div class="grid gap-4 xl:grid-cols-2">
        <x-hse.chart-card title="Insiden Lingkungan per Site" subtitle="Top 10 site">
            <x-ui.bar-chart :series="$toBars($data['charts']['incidents_by_site'])" label="Insiden lingkungan per site" />
        </x-hse.chart-card>
        <x-hse.chart-card title="Total Metrik Lingkungan" subtitle="Akumulasi periode terpilih">
            @if(!empty($data['totals']))
                <x-ui.bar-chart :series="collect($data['totals'])->mapWithKeys(fn ($row, $metric) => [$metric => $row['value'] ?? 0])->all()" label="Total metrik lingkungan" />
            @else
                <x-ui.empty-state title="Belum ada data lingkungan" message="Data akan tampil setelah environmental record tersedia." />
            @endif
        </x-hse.chart-card>
    </div>
</div>

<!-- Modal Catat Pemantauan Lingkungan -->
<dialog id="modal-input-environment" class="rounded-xl border border-slate-200 p-0 shadow-2xl max-w-xl w-full backdrop:bg-slate-900/50">
    <div class="p-6">
        <div class="flex items-center justify-between border-b border-slate-200 pb-3 mb-4">
            <h3 class="text-base font-bold text-[#0F172A]">Catat Rekam Lingkungan & Pengelolaan Limbah</h3>
            <button type="button" onclick="document.getElementById('modal-input-environment').close()" class="text-slate-400 hover:text-slate-600">&times;</button>
        </div>

        <form action="{{ route('hse.dashboard.environment.record.store') }}" method="POST" class="space-y-4">
            @csrf
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Site Operasional <span class="text-rose-500">*</span></label>
                    <select name="site_id" required class="w-full text-xs rounded-lg border border-slate-300 p-2.5">
                        <option value="">-- Pilih Site --</option>
                        @foreach($options['sites'] ?? [] as $st)
                            <option value="{{ $st->id }}" {{ ($filters['site_id'] ?? null) == $st->id ? 'selected' : '' }}>{{ $st->name }} ({{ $st->code }})</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Departemen Terkait</label>
                    <select name="department_id" class="w-full text-xs rounded-lg border border-slate-300 p-2.5">
                        <option value="">-- Pilih Departemen --</option>
                        @foreach($options['departments'] ?? [] as $dp)
                            <option value="{{ $dp->id }}" {{ ($filters['department_id'] ?? null) == $dp->id ? 'selected' : '' }}>{{ $dp->name }}</option>
                        @endforeach
                    </select>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Tanggal Pencatatan <span class="text-rose-500">*</span></label>
                    <input type="date" name="recorded_date" required value="{{ now()->toDateString() }}" class="w-full text-xs rounded-lg border border-slate-300 p-2.5">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Parameter / Metrik Lingkungan <span class="text-rose-500">*</span></label>
                    <select name="metric" required class="w-full text-xs rounded-lg border border-slate-300 p-2.5">
                        <option value="Waste Generated">Waste Generated (Total Limbah)</option>
                        <option value="Hazardous Waste">Hazardous Waste (Limbah B3)</option>
                        <option value="Non-Hazardous Waste">Non-Hazardous Waste (Limbah Non-B3)</option>
                        <option value="Recycled Waste">Recycled Waste (Limbah Didaur Ulang)</option>
                        <option value="Energy Consumption">Energy Consumption (Listrik)</option>
                        <option value="Water Consumption">Water Consumption (Air)</option>
                        <option value="Fuel Consumption">Fuel Consumption (BBM Solar)</option>
                    </select>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Nilai / Jumlah Volume <span class="text-rose-500">*</span></label>
                    <input type="number" name="value" step="0.01" min="0" required placeholder="Contoh: 12.5" class="w-full text-xs rounded-lg border border-slate-300 p-2.5">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Satuan Pengukuran <span class="text-rose-500">*</span></label>
                    <select name="unit" required class="w-full text-xs rounded-lg border border-slate-300 p-2.5">
                        <option value="Ton">Ton</option>
                        <option value="Kg">Kg</option>
                        <option value="kWh">kWh</option>
                        <option value="m³">m³ (Meter Kubik)</option>
                        <option value="Liter">Liter</option>
                    </select>
                </div>
            </div>

            <div>
                <label class="block text-xs font-semibold text-slate-700 mb-1">Catatan Tambahan / Keterangan TPS</label>
                <textarea name="notes" rows="2" placeholder="Sumber limbah, nomor manifest B3, atau detail pencatatan..." class="w-full text-xs rounded-lg border border-slate-300 p-2.5"></textarea>
            </div>

            <div class="flex justify-end gap-2 pt-3 border-t border-slate-200">
                <button type="button" onclick="document.getElementById('modal-input-environment').close()" class="px-4 py-2 rounded-lg border border-slate-300 text-xs font-semibold text-slate-700 hover:bg-slate-50">Batal</button>
                <button type="submit" class="px-5 py-2 rounded-lg bg-[#1646A0] text-white text-xs font-semibold hover:bg-[#0B2345]">Simpan Rekam Lingkungan</button>
            </div>
        </form>
    </div>
</dialog>
@endsection
