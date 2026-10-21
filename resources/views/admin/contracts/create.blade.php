@extends('layouts.app')
@section('title','Upload Kontrak')

@section('content')
<x-ui.page-header>
  <div class="ui-page-header__copy"><x-ui.page-title>Upload Kontrak Baru</x-ui.page-title>
<x-ui.page-description>Lengkapi informasi untuk menambahkan kontrak.</x-ui.page-description></div>
  <x-ui.page-actions>
<x-ui.button form="page-primary-form" variant="primary" class="px-4 py-2 rounded-lg hover:brightness-95">
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
      class="ui-card space-y-4 max-w-xl bg-white p-6 rounded shadow border-t-4 border-[#713F12]">
  @csrf
  <div>
    <label class="block mb-1 font-medium text-[#713F12]">Judul</label>
    <input type="text" name="title" value="{{ old('title') }}"
           class="w-full border rounded-lg p-2 focus:border-[#713F12] focus:ring-0" required>
  </div>

  <div>
    <label class="block mb-1 font-medium text-[#713F12]">File PDF</label>
    <input type="file" name="file" accept="application/pdf"
           class="w-full border rounded-lg p-2 focus:border-[#713F12] focus:ring-0" required>
  </div>

  <div>
    <label class="block mb-1 font-medium text-[#713F12]">Viewer (User Terdaftar)</label>
    <select name="emails[]" multiple size="8"
            class="w-full border rounded-lg p-2 focus:border-[#713F12] focus:ring-0">
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
