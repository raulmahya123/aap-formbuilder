@extends('layouts.app')

@section('title', 'Buat Inspeksi Baru')

@section('content')
<div class="ui-page space-y-6 max-w-4xl mx-auto">
    <x-ui.page-header>
        <div class="ui-page-header__copy">
            <h1 class="ui-page-title">Buat Inspeksi SHE/HSE Baru</h1>
            <p class="ui-page-description">Pilih template SHE-FM sesuai jenis inspeksi</p>
        </div>
        <a href="{{ route('hse.dashboard.sap.inspeksi.index') }}" class="px-3 py-2 rounded-lg border border-slate-300 text-xs font-semibold text-slate-700">Kembali</a>
    </x-ui.page-header>

    <form action="{{ route('hse.dashboard.sap.inspeksi.store') }}" method="POST" >
        @csrf
        <div>
            <label class="block">Template Inspeksi (SHE-FM) <span class="text-rose-500">*</span></label>
            <select name="template_id" required class="w-full ui-input">
                <option value="">-- Pilih Template --</option>
                @foreach($templates as $t)
                    <option value="{{ $t->id }}" @selected((string)old('template_id', request('template_id')) === (string)$t->id)>{{ $t->code }} - {{ $t->name }} ({{ $t->scoring_type }})</option>
                @endforeach
            </select>
        </div>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
                <label class="block">Site</label>
                <select name="site_id" class="w-full ui-input">
                    <option value="">-- Pilih Site --</option>
                    @foreach($sites as $s)
                        <option value="{{ $s->id }}">{{ $s->name }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block">Departemen</label>
                <select name="department_id" class="w-full ui-input">
                    <option value="">-- Pilih Departemen --</option>
                    @foreach($departments as $d)
                        <option value="{{ $d->id }}">{{ $d->name }}</option>
                    @endforeach
                </select>
            </div>
        </div>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
                <label class="block">Inspektor</label>
                <select name="inspector_id" class="w-full ui-input">
                    <option value="{{ auth()->id() }}">{{ auth()->user()->name }} (Default)</option>
                    @foreach($users as $u)
                        <option value="{{ $u->id }}">{{ $u->name }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block">Lokasi Inspeksi</label>
                <input type="text" name="location" class="w-full ui-input" placeholder="Area/lokasi yang diinspeksi">
            </div>
        </div>
        <div>
            <label class="block">Catatan</label>
            <textarea name="notes" rows="3" class="w-full ui-input"></textarea>
        </div>
        <div class="flex justify-end gap-2 pt-4 border-t border-slate-200">
            <a href="{{ route('hse.dashboard.sap.inspeksi.index') }}" class="px-4 py-2 rounded-lg border border-slate-300 text-xs font-semibold text-slate-700">Batal</a>
            <x-ui.button variant="primary" icon="plus" type="submit">Buat & Lanjut Isi Checklist</x-ui.button>
        </div>
    </form>
</div>
@endsection
