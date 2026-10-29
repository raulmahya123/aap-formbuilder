@extends('layouts.app')

@section('title', 'Tetapkan Target KPI Baru')

@section('content')
<div class="ui-page max-w-4xl mx-auto space-y-6">
    <x-ui.page-header>
        <div class="ui-page-header__copy">
            <h1 class="ui-page-title">Tetapkan Target KPI Baru</h1>
            <p class="ui-page-description">Konfigurasi target tahunan &amp; per-site untuk indikator keselamatan dan kinerja operasional.</p>
        </div>
        <a href="{{ route('hse.dashboard.targets.index') }}" class="inline-flex items-center gap-1.5 rounded-lg border border-slate-300 bg-white px-3.5 py-2 text-xs font-semibold text-slate-700 hover:bg-slate-50 shadow-sm transition">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
            Kembali ke Daftar
        </a>
    </x-ui.page-header>

    @if($errors->any())
        <div class="rounded-xl border border-rose-200 bg-rose-50 p-4 text-xs font-medium text-rose-800">
            <ul class="list-disc list-inside space-y-1">
                @foreach($errors->all() as $err)
                    <li>{{ $err }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="rounded-xl border border-[#E2E8F0] bg-white p-6 shadow-sm">
        <form action="{{ route('hse.dashboard.targets.store') }}" method="POST" >
            @csrf

            <div class="grid gap-6 sm:grid-cols-2">
                <div>
                    <label class="block">Indikator KPI <span class="text-rose-500">*</span></label>
                    <select name="kpi_key" required class="w-full ui-input">
                        <option value="">Pilih KPI</option>
                        @foreach($kpiKeys as $k => $conf)
                            <option value="{{ $k }}">{{ $conf['label'] }} ({{ $conf['unit'] }})</option>
                        @endforeach
                    </select>
                    <span class="text-[11px] text-slate-400 mt-1 block">Pilih parameter metrik keselamatan yang akan ditetapkan targetnya.</span>
                </div>

                <div>
                    <label class="block">Site Operasional</label>
                    <select name="site_id" class="w-full ui-input">
                        <option value="">Semua Site (Corporate Target)</option>
                        @foreach($options['sites'] as $s)
                            <option value="{{ $s->id }}">{{ $s->name }} ({{ $s->code }})</option>
                        @endforeach
                    </select>
                    <span class="text-[11px] text-slate-400 mt-1 block">Biarkan kosong jika target berlaku untuk skala korporat seluruh site.</span>
                </div>

                <div>
                    <label class="block">Tahun Target <span class="text-rose-500">*</span></label>
                    <input type="number" name="year" value="{{ $year }}" required class="w-full ui-input" />
                </div>

                <div>
                    <label class="block">Nilai Target <span class="text-rose-500">*</span></label>
                    <input type="number" step="any" name="target_value" required placeholder="0" class="w-full ui-input" />
                    <span class="text-[11px] text-slate-400 mt-1 block">Angka kuantitatif yang dijadikan patokan.</span>
                </div>

                <div class="sm:col-span-2">
                    <label class="block">Arah Sukses Target <span class="text-rose-500">*</span></label>
                    <select name="direction" required class="w-full ui-input">
                        <option value="lower">Lebih rendah atau sama dengan target lebih baik (≤ Target, contoh: Incident, LTI = 0)</option>
                        <option value="higher">Lebih tinggi atau sama dengan target lebih baik (≥ Target, contoh: Manhours, Score, Kepatuhan)</option>
                    </select>
                </div>
            </div>

            <div class="flex items-center justify-end gap-3 border-t border-slate-100 pt-5">
                <a href="{{ route('hse.dashboard.targets.index') }}" class="rounded-lg border border-slate-300 bg-white px-4 py-2 text-xs font-semibold text-slate-700 hover:bg-slate-50 transition">
                    Batal
                </a>
                <button type="submit" class="inline-flex items-center gap-2 rounded-lg bg-[#1646A0] px-5 py-2 text-xs font-semibold text-white hover:bg-[#123880] shadow transition">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                    Simpan Target KPI
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
