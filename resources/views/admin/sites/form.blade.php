{{-- resources/views/admin/sites/form.blade.php --}}

@extends('layouts.app')
@section('title', $site->exists ? 'Edit Site' : 'Tambah Site')

@section('content')
<h1 class="text-2xl font-bold mb-4">{{ $site->exists ? 'Edit' : 'Tambah' }} Site</h1>

@if ($errors->any())
<div class="mb-3 p-3 bg-red-50 text-red-800 rounded">
  <ul class="list-disc ml-5">
    @foreach ($errors->all() as $e) <li>{{ $e }}</li> @endforeach
  </ul>
</div>
@endif

<form method="POST" action="{{ $site->exists ? route('admin.sites.update',$site) : route('admin.sites.store') }}" class="space-y-4">
  @csrf
  @if($site->exists) @method('PUT') @endif

  <div>
    <label class="block mb-1 font-semibold">Code (mis: HO, BGG, SBS, DBK)</label>
    <input name="code" value="{{ old('code',$site->code) }}" class="border rounded px-3 py-2 w-full" required>
  </div>

  <div>
    <label class="block mb-1 font-semibold">Name</label>
    <input name="name" value="{{ old('name',$site->name) }}" class="border rounded px-3 py-2 w-full" required>
  </div>

  <div>
    <label class="block mb-1 font-semibold">Description</label>
    <textarea name="description" class="border rounded px-3 py-2 w-full" rows="3">{{ old('description',$site->description) }}</textarea>
  </div>

  <div class="flex gap-2">
    <button class="px-4 py-2 rounded bg-indigo-600 text-white">Simpan</button>
    <a href="{{ route('admin.sites.index') }}" class="px-4 py-2 rounded border">Batal</a>
  </div>
</form>
@endsection
