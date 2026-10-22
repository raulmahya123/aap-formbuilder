@extends('layouts.app')

@section('title', 'Edit Perusahaan')

@section('content')
<x-ui.page class="w-full min-w-0 ">
  <x-ui.page-header>
  <div class="ui-page-header__copy"><x-ui.page-title>Edit Perusahaan</x-ui.page-title>
<x-ui.page-description>Perbarui informasi perusahaan.</x-ui.page-description></div>
  <x-ui.page-actions><x-ui.link variant="secondary" href="{{ route('admin.companies.index') }}" class="px-3 py-2 rounded-lg border">← Kembali</x-ui.link></x-ui.page-actions>
</x-ui.page-header>

  <x-ui.panel class="rounded-lg border bg-white">
    <form action="{{ route('admin.companies.update', $company) }}" method="POST" enctype="multipart/form-data" class="grid gap-5">
      @csrf
      @method('PUT')
      @include('admin.companies._form', ['company' => $company])
      <div class="flex items-center gap-3">
        <x-ui.button variant="secondary" class="px-4 py-2 rounded-lg hover:brightness-105">
          Update
        </x-ui.button>
        <x-ui.link variant="secondary" href="{{ route('admin.companies.index') }}" class="px-4 py-2 rounded-lg border">Batal</x-ui.link>
      </div>
    </form>
  </x-ui.panel>
</x-ui.page>
@endsection
