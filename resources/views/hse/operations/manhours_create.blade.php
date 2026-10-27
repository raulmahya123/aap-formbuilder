@extends('layouts.app')

@section('title', 'Input Jam Kerja (Manhours) Harian')

@section('content')
<div class="ui-page max-w-4xl mx-auto space-y-6">
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-xl font-bold text-slate-900">Input Jam Kerja (Manhours)</h1>
            <p class="text-xs text-slate-500 mt-1">Catat jam kerja aman dan tenaga kerja harian per site dan kontraktor untuk kalkulasi LTIFR & LTISR.</p>
        </div>
        <a href="{{ route('hse.dashboard.manhours.index') }}" class="inline-flex items-center gap-1.5 rounded-lg border border-slate-300 bg-white px-3.5 py-2 text-xs font-semibold text-slate-700 hover:bg-slate-50 shadow-sm transition">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
            Kembali ke Riwayat
        </a>
    </div>

    @if($errors->any())
        <div class="rounded-xl border border-rose-200 bg-rose-50 p-4 text-xs font-medium text-rose-800">
            <ul class="list-disc list-inside space-y-1">
                @foreach($errors->all() as $err)
                    <li>{{ $err }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    {{-- IMPORT VIA EXCEL / CSV --}}
    <div class="rounded-xl border border-dashed border-[#94A3B8] bg-slate-50 p-6 shadow-sm">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
                <h3 class="text-sm font-bold text-[#0F172A]">Opsi: Import Massal Excel / CSV</h3>
                <p class="mt-1 text-xs text-[#64748B]">Kolom: <code>Tanggal (YYYY-MM-DD), Jumlah Karyawan, Jam Kerja, Lost Hours</code>.</p>
            </div>
            <form action="{{ route('hse.dashboard.manhours.import') }}" method="POST" enctype="multipart/form-data" class="flex items-center gap-2">
                @csrf
                <input type="file" name="file" required accept=".csv,.xlsx,.xls" class="text-xs text-slate-500 file:mr-2 file:rounded-md file:border-0 file:bg-blue-600 file:px-3 file:py-2 file:text-xs file:font-semibold file:text-white hover:file:bg-blue-700 cursor-pointer" />
                <button type="submit" class="rounded-lg bg-[#0F172A] px-4 py-2 text-xs font-semibold text-white hover:bg-slate-800">
                    Proses Import
                </button>
            </form>
        </div>
    </div>

    {{-- MANUAL INPUT FORM --}}
    <div class="rounded-xl border border-[#E2E8F0] bg-white p-6 shadow-sm">
        <h2 class="text-sm font-bold text-slate-900 border-b border-slate-100 pb-3 mb-5">Formulir Input Manual</h2>
        <form action="{{ route('hse.dashboard.manhours.store') }}" method="POST" class="space-y-6">
            @csrf

            <div class="grid gap-4 sm:grid-cols-2">
                <div>
                    <label class="block text-xs font-semibold text-[#475569]">Tanggal <span class="text-rose-500">*</span></label>
                    <input type="date" name="date" value="{{ now()->toDateString() }}" required class="mt-1.5 w-full rounded-lg border border-[#CBD5E1] p-2.5 text-xs focus:border-[#1646A0] focus:ring-1 focus:ring-[#1646A0]" />
                </div>

                <div>
                    <label class="block text-xs font-semibold text-[#475569]">Site <span class="text-rose-500">*</span></label>
                    <select name="site_id" required class="mt-1.5 w-full rounded-lg border border-[#CBD5E1] p-2.5 text-xs focus:border-[#1646A0] focus:ring-1 focus:ring-[#1646A0]">
                        <option value="">Pilih Site</option>
                        @foreach($options['sites'] as $s)
                            <option value="{{ $s->id }}">{{ $s->name }} ({{ $s->code }})</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block text-xs font-semibold text-[#475569]">Perusahaan / Kontraktor</label>
                    <select name="company_id" class="mt-1.5 w-full rounded-lg border border-[#CBD5E1] p-2.5 text-xs focus:border-[#1646A0] focus:ring-1 focus:ring-[#1646A0]">
                        <option value="">Internal / Pilih Perusahaan</option>
                        @foreach($options['companies'] as $c)
                            <option value="{{ $c->id }}">{{ $c->name }}</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block text-xs font-semibold text-[#475569]">Department</label>
                    <select name="department_id" class="mt-1.5 w-full rounded-lg border border-[#CBD5E1] p-2.5 text-xs focus:border-[#1646A0] focus:ring-1 focus:ring-[#1646A0]">
                        <option value="">Pilih Department</option>
                        @foreach($options['departments'] as $d)
                            <option value="{{ $d->id }}">{{ $d->name }}</option>
                        @endforeach
                    </select>
                </div>
            </div>

            <div class="grid gap-4 sm:grid-cols-3 border-t border-slate-100 pt-5">
                <div>
                    <label class="block text-xs font-semibold text-[#475569]">Jumlah Karyawan (Manpower) <span class="text-rose-500">*</span></label>
                    <input type="number" name="employee_count" min="0" value="0" required class="mt-1.5 w-full rounded-lg border border-[#CBD5E1] p-2.5 text-xs focus:border-[#1646A0] focus:ring-1 focus:ring-[#1646A0]" />
                </div>

                <div>
                    <label class="block text-xs font-semibold text-[#475569]">Jam Kerja Aman (Worked Hours) <span class="text-rose-500">*</span></label>
                    <input type="number" step="0.5" name="manhours" min="0" required placeholder="Contoh: 80" class="mt-1.5 w-full rounded-lg border border-[#CBD5E1] p-2.5 text-xs focus:border-[#1646A0] focus:ring-1 focus:ring-[#1646A0]" />
                </div>

                <div>
                    <label class="block text-xs font-semibold text-[#475569]">Lost Hours (Jam Hilang)</label>
                    <input type="number" step="0.5" name="lost_hours" min="0" value="0" class="mt-1.5 w-full rounded-lg border border-[#CBD5E1] p-2.5 text-xs focus:border-[#1646A0] focus:ring-1 focus:ring-[#1646A0]" />
                </div>
            </div>

            <div class="border-t border-slate-100 pt-5">
                <label class="block text-xs font-semibold text-[#475569]">Catatan / Keterangan</label>
                <textarea name="notes" rows="2" placeholder="Catatan tambahan (opsional)..." class="mt-1.5 w-full rounded-lg border border-[#CBD5E1] p-2.5 text-xs focus:border-[#1646A0] focus:ring-1 focus:ring-[#1646A0]"></textarea>
            </div>

            <div class="flex items-center justify-end gap-3 border-t border-slate-100 pt-5">
                <a href="{{ route('hse.dashboard.manhours.index') }}" class="rounded-lg border border-slate-300 bg-white px-4 py-2.5 text-xs font-semibold text-slate-700 hover:bg-slate-50 transition">
                    Batal
                </a>
                <x-ui.button variant="primary" icon="save" type="submit">Simpan Jam Kerja</x-ui.button>
            </div>
        </form>
    </div>
</div>
@endsection
