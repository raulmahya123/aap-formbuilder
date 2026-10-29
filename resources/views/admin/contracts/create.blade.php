@extends('layouts.app')
@section('title','Upload Kontrak')

@section('content')
<x-ui.page-header>
  <div class="ui-page-header__copy"><x-ui.page-title>Upload Kontrak Baru</x-ui.page-title>
<x-ui.page-description>Lengkapi informasi untuk menambahkan kontrak.</x-ui.page-description></div>
  <x-ui.page-actions>
<x-ui.button form="page-primary-form" variant="primary" >
      Simpan
    </x-ui.button>
</x-ui.page-actions>
</x-ui.page-header>

@if ($errors->any())
  <div class="mb-3 p-3 rounded-lg bg-red-50 text-red-700">
    <ul class="list-disc pl-5">
      @foreach($errors->all() as $err)
        <li>{{ $err }}</li>
      @endforeach
    </ul>
  </div>
@endif

<form id="page-primary-form" method="post" action="{{ route('admin.contracts.store') }}" enctype="multipart/form-data"
      class="w-full min-w-0 ui-card">
  @csrf
  <div>
    <label class="block">Judul</label>
    <input type="text" name="title" value="{{ old('title') }}"
           class="w-full ui-input" required>
  </div>

  <div>
    <label class="block">File PDF</label>
    <input type="file" name="file" accept="application/pdf"
           class="w-full" required>
  </div>

  <div>
    <label class="block">Viewer (User Terdaftar)</label>
    <select name="emails[]" multiple size="8"
            class="w-full ui-input">
      @foreach($users as $u)
        <option value="{{ $u->email }}"
          @if(collect(old('emails'))->contains($u->email)) selected @endif>
          {{ $u->name }} — {{ $u->email }}
        </option>
      @endforeach
    </select>
    <p class="text-xs text-gray-500 mt-1">
      Pilih beberapa user dengan menahan <kbd>Ctrl</kbd> / <kbd>Cmd</kbd>.
    </p>
  </div>

  <div class="pt-2">

  </div>
</form>
@endsection
