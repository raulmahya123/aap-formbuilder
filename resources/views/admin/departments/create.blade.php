@extends('layouts.app')
@section('title','Tambah Department')
@section('content')
<x-ui.page><x-ui.page-header><div class="ui-page-header__copy"><x-ui.page-title>Tambah Department</x-ui.page-title><x-ui.page-description>Atur nama department untuk pengelompokan anggota.</x-ui.page-description></div><x-ui.page-actions><x-ui.button form="page-primary-form" variant="primary">Simpan Data</x-ui.button></x-ui.page-actions></x-ui.page-header><form id="page-primary-form" method="POST" action="{{ route('admin.departments.store') }}" class="ui-card max-w-xl space-y-5">@csrf <x-ui.form-field name="name" label="Nama Department" required><x-ui.form-input id="name" name="name" :value="old('name')" maxlength="150" required autocomplete="off" /></x-ui.form-field></form></x-ui.page>
@endsection
