@extends('layouts.app')

@section('title', 'Tetapkan Target SAP Baru')

@section('content')
<div class="ui-page max-w-4xl mx-auto space-y-6">
    <x-ui.page-header>
        <div class="ui-page-header__copy">
            <h1 class="ui-page-title">Tetapkan Target SAP Baru</h1>
            <p class="ui-page-description">Konfigurasi kuota kegiatan K3 wajib bulanan untuk inspeksi, observasi tugas (PTO), dan laporan bahaya.</p>
        </div>
        <a href="{{ route('hse.dashboard.sap.targets.index') }}" class="inline-flex items-center gap-1.5 rounded-lg border border-slate-300 bg-white px-3.5 py-2 text-xs font-semibold text-slate-700 hover:bg-slate-50 shadow-sm transition">
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
        <form action="{{ route('hse.dashboard.sap.targets.store') }}" method="POST" >
            @csrf

            <div class="grid gap-6 sm:grid-cols-2">
                <div>
                    <label class="block">Site Operasional</label>
                    <select name="site_id" class="w-full ui-input">
                        <option value="">Semua Site (Corporate Level)</option>
                        @foreach($options['sites'] as $s)
                            <option value="{{ $s->id }}">{{ $s->name }} ({{ $s->code }})</option>
                        @endforeach
                    </select>
                    <span class="text-[11px] text-slate-400 mt-1 block">Biarkan kosong jika berlaku umum untuk seluruh site.</span>
                </div>

                <div>
                    <label class="block">Department</label>
                    <select name="department_id" class="w-full ui-input">
                        <option value="">Semua Department</option>
                        @foreach($options['departments'] as $d)
                            <option value="{{ $d->id }}">{{ $d->name }}</option>
                        @endforeach
                    </select>
                    <span class="text-[11px] text-slate-400 mt-1 block">Pilih departemen jika target spesifik per departemen.</span>
                </div>

                <div>
                    <label class="block">Target Karyawan Spesifik (Opsional)</label>
                    <select name="user_id" class="w-full ui-input">
                        <option value="">Semua Karyawan dalam Lingkup</option>
                        @foreach($options['users'] as $u)
                            <option value="{{ $u->id }}">{{ $u->name }} ({{ $u->email }})</option>
                        @endforeach
                    </select>
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block">Tahun <span class="text-rose-500">*</span></label>
                        <input type="number" name="year" value="{{ now()->year }}" required class="w-full ui-input" />
                    </div>

                    <div>
                        <label class="block">Bulan <span class="text-rose-500">*</span></label>
                        <select name="month" required class="w-full ui-input">
                            @for($m = 1; $m <= 12; $m++)
                                <option value="{{ $m }}" {{ $m == now()->month ? 'selected' : '' }}>
                                    {{ \Carbon\Carbon::create(2000, $m, 1)->translatedFormat('F') }}
                                </option>
                            @endfor
                        </select>
                    </div>
                </div>
            </div>

            <div class="border-t border-slate-100 pt-6">
                <h3 class="text-sm font-bold text-slate-900 mb-1">Target Kuota Kegiatan (per Bulan)</h3>
                <p class="text-xs text-slate-500 mb-4">Jumlah minimum kegiatan yang wajib diselesaikan setiap bulannya.</p>

                <div class="grid gap-4 sm:grid-cols-3">
                    <div class="rounded-xl border border-blue-100 bg-blue-50/50 p-4">
                        <label class="block">Target Inspeksi</label>
                        <div class="mt-2 flex items-center gap-2">
                            <input type="number" name="target_inspection" min="0" value="4" required class="w-full ui-input" />
                            <span class="text-xs text-slate-500">kali</span>
                        </div>
                    </div>

                    <div class="rounded-xl border border-emerald-100 bg-emerald-50/50 p-4">
                        <label class="block">Target PTO (Observasi)</label>
                        <div class="mt-2 flex items-center gap-2">
                            <input type="number" name="target_pto" min="0" value="2" required class="w-full ui-input" />
                            <span class="text-xs text-slate-500">kali</span>
                        </div>
                    </div>

                    <div class="rounded-xl border border-amber-100 bg-amber-50/50 p-4">
                        <label class="block">Target Hazard Report</label>
                        <div class="mt-2 flex items-center gap-2">
                            <input type="number" name="target_hazard" min="0" value="6" required class="w-full ui-input" />
                            <span class="text-xs text-slate-500">laporan</span>
                        </div>
                    </div>
                </div>
            </div>

            <div class="flex items-center justify-end gap-3 border-t border-slate-100 pt-6">
                <a href="{{ route('hse.dashboard.sap.targets.index') }}" class="rounded-lg border border-slate-300 bg-white px-4 py-2.5 text-xs font-semibold text-slate-700 hover:bg-slate-50 transition">
                    Batal
                </a>
                <x-ui.button variant="primary" icon="save" type="submit">Simpan Target SAP</x-ui.button>
            </div>
        </form>
    </div>
</div>
@endsection
