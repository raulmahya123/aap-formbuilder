@extends('layouts.app')
@section('title', $group->exists ? 'Edit Grup Indikator' : 'Tambah Grup Indikator')
@section('content')
<x-ui.page>
<x-ui.page-header><div class="ui-page-header__copy"><x-ui.page-title>{{ $group->exists ? 'Edit' : 'Tambah' }} Grup Indikator</x-ui.page-title><x-ui.page-description>Atur identitas dan urutan grup indikator.</x-ui.page-description></div><x-ui.page-actions><x-ui.button form="page-primary-form" variant="primary">{{ $group->exists ? 'Simpan Perubahan' : 'Simpan Data' }}</x-ui.button></x-ui.page-actions></x-ui.page-header>
<form id="page-primary-form" method="POST" action="{{ $group->exists ? route('admin.groups.update',$group) : route('admin.groups.store') }}" class="ui-card space-y-5 max-w-xl">@csrf @if($group->exists) @method('PUT') @endif
<x-ui.form-field name="name" label="Nama" required><x-ui.form-input id="name" name="name" :value="old('name',$group->name)" required /></x-ui.form-field>
<x-ui.form-field name="code" label="Kode" required><x-ui.form-input id="code" name="code" :value="old('code',$group->code)" required /></x-ui.form-field>
<x-ui.form-field name="order_index" label="Urutan"><x-ui.form-input id="order_index" name="order_index" type="number" :value="old('order_index',$group->order_index ?? 0)" /></x-ui.form-field>
<input type="hidden" name="is_active" value="0"><label class="flex items-center gap-2"><input type="checkbox" name="is_active" value="1" @checked(old('is_active',$group->is_active ?? true))>Aktif</label>
</form>
</x-ui.page>
@endsection
