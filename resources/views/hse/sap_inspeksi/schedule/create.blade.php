@extends('layouts.app')

@section('title', 'Buat Jadwal Inspeksi')

@section('content')
<div class="ui-page space-y-6 max-w-3xl mx-auto">
    <x-ui.page-header>
        <div class="ui-page-header__copy">
            <h1 class="ui-page-title">Buat Jadwal Inspeksi</h1>
            <p class="ui-page-description">Rencanakan inspeksi SHE-FM</p>
        </div>
        <a href="{{ route('hse.dashboard.sap.inspeksi.schedule.index') }}" class="px-3 py-2 rounded-lg border border-slate-300 text-xs font-semibold text-slate-700">Kembali</a>
    </x-ui.page-header>

    <form action="{{ route('hse.dashboard.sap.inspeksi.schedule.store') }}" method="POST" >
        @csrf
        <div>
            <label class="block">Template <span class="text-rose-500">*</span></label>
            <select name="template_id" required class="w-full ui-input">
                @foreach($templates as $t)
                    <option value="{{ $t->id }}">{{ $t->code }} - {{ $t->name }}</option>
                @endforeach
            </select>
        </div>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
                <label class="block">Site</label>
                <select name="site_id" class="w-full ui-input">
                    <option value="">-- Pilih Site --</option>
                    @foreach($options['sites'] ?? [] as $s)
                        <option value="{{ $s->id }}">{{ $s->name }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block">Departemen</label>
                <select name="department_id" class="w-full ui-input">
                    <option value="">-- Pilih Departemen --</option>
                    @foreach($options['departments'] ?? [] as $d)
                        <option value="{{ $d->id }}">{{ $d->name }}</option>
                    @endforeach
                </select>
            </div>
        </div>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <div>
                <label class="block">Ditugaskan Kepada</label>
                <select name="assigned_to" class="w-full ui-input">
                    <option value="">-- Pilih --</option>
                    @foreach($users as $u)
                        <option value="{{ $u->id }}">{{ $u->name }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block">Periode</label>
                <select name="period" class="w-full ui-input">
                    <option value="monthly">Bulanan</option>
                    <option value="quarterly">Triwulan</option>
                    <option value="yearly">Tahunan</option>
                </select>
            </div>
            <div>
                <label class="block">Jatuh Tempo</label>
                <input type="date" name="due_date" class="w-full ui-input">
            </div>
        </div>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
                <label class="block">Tahun</label>
                <input type="number" name="year" value="{{ date('Y') }}" class="w-full ui-input">
            </div>
            <div>
                <label class="block">Bulan</label>
                <input type="number" name="month" min="1" max="12" value="{{ date('n') }}" class="w-full ui-input">
            </div>
        </div>
        <div class="flex justify-end gap-2 pt-4 border-t border-slate-200">
            <a href="{{ route('hse.dashboard.sap.inspeksi.schedule.index') }}" class="px-4 py-2 rounded-lg border border-slate-300 text-xs font-semibold text-slate-700">Batal</a>
            <x-ui.button variant="primary" icon="save" type="submit">Simpan Jadwal</x-ui.button>
        </div>
    </form>
</div>
@endsection
