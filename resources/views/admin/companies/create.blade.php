@extends('layouts.app')

@section('title', 'Tambah Perusahaan')

@section('content')
<x-ui.page class="w-full min-w-0 ">
  <x-ui.page-header>
  <div class="ui-page-header__copy"><x-ui.page-title>Tambah Perusahaan</x-ui.page-title>
<x-ui.page-description>Lengkapi informasi untuk menambahkan perusahaan.</x-ui.page-description></div>
  <x-ui.page-actions><x-ui.back-link href="{{ route('admin.companies.index') }}">Kembali</x-ui.back-link>
<x-ui.button form="page-primary-form" variant="primary" >
          Simpan
        </x-ui.button>
</x-ui.page-actions>
</x-ui.page-header>

  <x-ui.panel >
    <form id="page-primary-form" action="{{ route('admin.companies.store') }}" method="POST" enctype="multipart/form-data" class="grid">
  @csrf
      @include('admin.companies._form')
      <div class="flex items-center gap-3">

        <x-ui.link variant="secondary" href="{{ route('admin.companies.index') }}" >Batal</x-ui.link>
      </div>
    </form>
  </x-ui.panel>
</x-ui.page>
@endsection
