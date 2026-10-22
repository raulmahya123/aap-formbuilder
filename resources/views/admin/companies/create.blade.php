@extends('layouts.app')

@section('title', 'Tambah Perusahaan')

@section('content')
<x-ui.page class="w-full min-w-0 ">
  <x-ui.page-header>
  <div class="ui-page-header__copy"><x-ui.page-title>Tambah Perusahaan</x-ui.page-title>
<x-ui.page-description>Lengkapi informasi untuk menambahkan perusahaan.</x-ui.page-description></div>
  <x-ui.page-actions><x-ui.link variant="secondary" href="{{ route('admin.companies.index') }}" class="px-3 py-2 rounded-lg border">← Kembali</x-ui.link>
<x-ui.button form="page-primary-form" variant="primary" class="px-4 py-2 rounded-lg hover:brightness-105">
          Simpan
        </x-ui.button>
</x-ui.page-actions>
</x-ui.page-header>

  <x-ui.panel class="rounded-lg border bg-white">
    <form id="page-primary-form" action="{{ route('admin.companies.store') }}" method="POST" enctype="multipart/form-data" class="grid gap-5">
  @csrf
      @include('admin.companies._form')
      <div class="flex items-center gap-3">

        <x-ui.link variant="secondary" href="{{ route('admin.companies.index') }}" class="px-4 py-2 rounded-lg border">Batal</x-ui.link>
      </div>
    </form>
  </x-ui.panel>
</x-ui.page>
@endsection
