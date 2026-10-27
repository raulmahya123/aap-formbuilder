@extends('layouts.app')

@section('title', 'Buat Jadwal Inspeksi')

@section('content')
<div class="ui-page space-y-6 max-w-3xl mx-auto">
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-xl font-bold text-slate-900">Buat Jadwal Inspeksi</h1>
            <p class="text-sm text-slate-500">Rencanakan inspeksi SHE-FM</p>
        </div>
        <a href="{{ route('hse.sap.inspeksi.schedule.index') }}" class="px-3 py-2 rounded-lg border border-slate-300 text-xs font-semibold text-slate-700">Kembali</a>
    </div>

    <form action="{{ route('hse.sap.inspeksi.schedule.store') }}" method="POST" class="rounded-xl border border-slate-200 bg-white shadow-sm p-6 space-y-4">
        @csrf
        <div>
            <label class="block text-xs font-semibold text-slate-700 mb-1">Template <span class="text-rose-500">*</span></label>
            <select name="template_id" required class="w-full text-sm rounded-lg border border-slate-300 p-2.5">
                @foreach($templates as $t)
                    <option value="{{ $t->id }}">{{ $t->code }} - {{ $t->name }}</option>
                @endforeach
            </select>
        </div>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
                <label class="block text-xs font-semibold text-slate-700 mb-1">Site</label>
                <select name="site_id" class="w-full text-sm rounded-lg border border-slate-300 p-2.5">
                    <option value="">-- Pilih Site --</option>
                    @foreach($options['sites'] ?? [] as $s)
                        <option value="{{ $s->id }}">{{ $s->name }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-xs font-semibold text-slate-700 mb-1">Departemen</label>
                <select name="department_id" class="w-full text-sm rounded-lg border border-slate-300 p-2.5">
                    <option value="">-- Pilih Departemen --</option>
                    @foreach($options['departments'] ?? [] as $d)
                        <option value="{{ $d->id }}">{{ $d->name }}</option>
                    @endforeach
                </select>
            </div>
        </div>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <div>
                <label class="block text-xs font-semibold text-slate-700 mb-1">Ditugaskan Kepada</label>
                <select name="assigned_to" class="w-full text-sm rounded-lg border border-slate-300 p-2.5">
                    <option value="">-- Pilih --</option>
                    @foreach($users as $u)
                        <option value="{{ $u->id }}">{{ $u->name }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-xs font-semibold text-slate-700 mb-1">Periode</label>
                <select name="period" class="w-full text-sm rounded-lg border border-slate-300 p-2.5">
                    <option value="monthly">Bulanan</option>
                    <option value="quarterly">Triwulan</option>
                    <option value="yearly">Tahunan</option>
                </select>
            </div>
            <div>
                <label class="block text-xs font-semibold text-slate-700 mb-1">Jatuh Tempo</label>
                <input type="date" name="due_date" class="w-full text-sm rounded-lg border border-slate-300 p-2.5">
            </div>
        </div>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
                <label class="block text-xs font-semibold text-slate-700 mb-1">Tahun</label>
                <input type="number" name="year" value="{{ date('Y') }}" class="w-full text-sm rounded-lg border border-slate-300 p-2.5">
            </div>
            <div>
                <label class="block text-xs font-semibold text-slate-700 mb-1">Bulan</label>
                <input type="number" name="month" min="1" max="12" value="{{ date('n') }}" class="w-full text-sm rounded-lg border border-slate-300 p-2.5">
            </div>
        </div>
        <div class="flex justify-end gap-2 pt-4 border-t border-slate-200">
            <a href="{{ route('hse.sap.inspeksi.schedule.index') }}" class="px-4 py-2 rounded-lg border border-slate-300 text-xs font-semibold text-slate-700">Batal</a>
            <button type="submit" class="px-5 py-2 rounded-lg bg-[#1646A0] text-white text-xs font-semibold">Simpan Jadwal</button>
        </div>
    </form>
</div>
@endsection
