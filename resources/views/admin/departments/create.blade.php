@extends('layouts.app')
@section('content')
<x-ui.page class="max-w-xl mx-auto">
  <x-ui.page-header>
  <div class="ui-page-header__copy"><x-ui.page-title>Tambah Department</x-ui.page-title>
<x-ui.page-description>Lengkapi informasi untuk menambahkan department dan anggota.</x-ui.page-description></div>
  <x-ui.page-actions>
<x-ui.button form="page-primary-form" variant="primary" class="px-4 py-2 rounded-lg">Simpan</x-ui.button>
</x-ui.page-actions>
</x-ui.page-header>
  <form id="page-primary-form" method="post" action="{{ route('admin.departments.store') }}">
  @csrf
    <label class="block mb-2">Nama</label>
    <input type="text" name="name" class="border rounded-lg w-full mb-3" required>


  </form>
</x-ui.page>
@endsection
