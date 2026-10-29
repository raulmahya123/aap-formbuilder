@extends('layouts.app')

@section('title', 'Edit Perusahaan')

@section('content')
<x-ui.page class="w-full min-w-0 ">
  <x-ui.page-header>
  <div class="ui-page-header__copy"><x-ui.page-title>Edit Perusahaan</x-ui.page-title>
<x-ui.page-description>Perbarui informasi perusahaan.</x-ui.page-description></div>
  <x-ui.page-actions><x-ui.back-link href="{{ route('admin.companies.index') }}">Kembali</x-ui.back-link></x-ui.page-actions>
</x-ui.page-header>

  <x-ui.panel >
    <form action="{{ route('admin.companies.update', $company) }}" method="POST" enctype="multipart/form-data" class="grid">
      @csrf
      @method('PUT')
      @include('admin.companies._form', ['company' => $company])
      <div class="flex items-center gap-3">
        <x-ui.button variant="secondary" >
          Update
        </x-ui.button>
        <x-ui.link variant="secondary" href="{{ route('admin.companies.index') }}" >Batal</x-ui.link>
      </div>
    </form>
  </x-ui.panel>
</x-ui.page>
@endsection
