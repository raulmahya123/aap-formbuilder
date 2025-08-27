@extends('layouts.app')
@section('content')
<div class="max-w-xl mx-auto p-6 bg-white rounded-xl">
  <h1 class="text-xl font-semibold mb-4">Tambah Department</h1>
  <form method="post" action="{{ route('admin.departments.store') }}">
    @csrf
    <label class="block mb-2">Nama</label>
    <input type="text" name="name" class="border rounded w-full mb-3" required>

    <button class="px-4 py-2 bg-emerald-600 text-white rounded">Simpan</button>
  </form>
</div>
@endsection
