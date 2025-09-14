@extends('layouts.app')
@section('title','Upload Kontrak')

@section('content')
<h1 class="text-2xl font-bold mb-4">Upload Kontrak Baru</h1>

@if ($errors->any())
  <div class="mb-3 p-3 rounded bg-red-50 text-red-700">
    <ul class="list-disc pl-5">
      @foreach($errors->all() as $err)
        <li>{{ $err }}</li>
      @endforeach
    </ul>
  </div>
@endif

<form method="post" action="{{ route('admin.contracts.store') }}" enctype="multipart/form-data"
      class="space-y-4 max-w-xl bg-white p-6 rounded shadow border-t-4 border-[#7A2C2F]">
  @csrf
  <div>
    <label class="block mb-1 font-medium text-[#7A2C2F]">Judul</label>
    <input type="text" name="title" value="{{ old('title') }}"
           class="w-full border rounded p-2 focus:border-[#7A2C2F] focus:ring-0" required>
  </div>

  <div>
    <label class="block mb-1 font-medium text-[#7A2C2F]">File PDF</label>
    <input type="file" name="file" accept="application/pdf"
           class="w-full border rounded p-2 focus:border-[#7A2C2F] focus:ring-0" required>
  </div>

  <div>
    <label class="block mb-1 font-medium text-[#7A2C2F]">Viewer (User Terdaftar)</label>
    <select name="emails[]" multiple size="8"
            class="w-full border rounded p-2 focus:border-[#7A2C2F] focus:ring-0">
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
    <button class="px-4 py-2 bg-[#7A2C2F] text-white rounded hover:bg-[#651E20]">
      Simpan
    </button>
  </div>
</form>
@endsection
