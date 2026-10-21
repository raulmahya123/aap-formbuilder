@extends('layouts.app')
@section('title','Edit Department')
@section('content')
<x-ui.page><x-ui.page-header><div class="ui-page-header__copy"><x-ui.page-title>Edit Department</x-ui.page-title><x-ui.page-description>Atur nama department untuk pengelompokan anggota.</x-ui.page-description></div><x-ui.page-actions><x-ui.button form="page-primary-form" variant="primary">Simpan Perubahan</x-ui.button></x-ui.page-actions></x-ui.page-header><form id="page-primary-form" method="POST" action="{{ route('admin.departments.update',$department) }}" class="ui-card max-w-xl space-y-5">@csrf @method('PUT')<x-ui.form-field name="name" label="Nama Department" required><x-ui.form-input id="name" name="name" :value="old('name', $department->name)" maxlength="150" required autocomplete="off" /></x-ui.form-field></form></x-ui.page>
@endsection
