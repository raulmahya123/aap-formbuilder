{{-- resources/views/admin/sites/form.blade.php --}}
@extends('layouts.app')
@section('title', $site->exists ? 'Edit Site' : 'Tambah Site')

@section('content')
<h1 class="text-2xl font-bold mb-6 text-maroon-700">
  {{ $site->exists ? 'Edit' : 'Tambah' }} Site
</h1>

@if ($errors->any())
  <div class="mb-5 p-4 rounded-lg bg-red-50 border border-red-200 text-red-800">
    <ul class="list-disc ml-5 space-y-1 text-sm">
      @foreach ($errors->all() as $e)
        <li>{{ $e }}</li>
      @endforeach
    </ul>
  </div>
@endif

<form method="POST"
      action="{{ $site->exists ? route('admin.sites.update',$site) : route('admin.sites.store') }}"
      class="space-y-5 max-w-lg bg-white rounded-xl p-6 shadow-sm border border-gray-200">
  @csrf
  @if($site->exists) @method('PUT') @endif

  <div>
    <label class="block mb-1 font-semibold text-gray-700">Code <span class="text-sm text-gray-500">(mis: HO, BGG, SBS, DBK)</span></label>
    <input name="code" value="{{ old('code',$site->code) }}"
           class="border rounded-lg px-3 py-2 w-full focus:ring-2 focus:ring-maroon-400 focus:outline-none"
           required>
  </div>

  <div>
    <label class="block mb-1 font-semibold text-gray-700">Name</label>
    <input name="name" value="{{ old('name',$site->name) }}"
           class="border rounded-lg px-3 py-2 w-full focus:ring-2 focus:ring-maroon-400 focus:outline-none"
           required>
  </div>

  <div>
    <label class="block mb-1 font-semibold text-gray-700">Description</label>
    <textarea name="description"
              class="border rounded-lg px-3 py-2 w-full focus:ring-2 focus:ring-maroon-400 focus:outline-none"
              rows="3">{{ old('description',$site->description) }}</textarea>
  </div>

  <div class="flex gap-3">
    <button class="px-5 py-2.5 rounded-lg bg-maroon-600 hover:bg-maroon-700 text-white shadow">
      Simpan
    </button>
    <a href="{{ route('admin.sites.index') }}"
       class="px-5 py-2.5 rounded-lg border border-gray-300 hover:bg-gray-50 text-gray-700">
      Batal
    </a>
  </div>
</form>
@endsection
