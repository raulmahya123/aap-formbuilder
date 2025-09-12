{{-- resources/views/admin/sites/index.blade.php --}}
@extends('layouts.app')
@section('title','Sites')

@section('content')
<div class="flex items-center justify-between mb-4">
  <h1 class="text-2xl font-bold">Sites</h1>
  <a href="{{ route('admin.sites.create') }}" class="px-3 py-2 rounded bg-indigo-600 text-white">Tambah</a>
</div>

<form class="mb-3">
  <input class="border rounded px-3 py-2" type="text" name="q" value="{{ $q }}" placeholder="Cari name/code...">
  <button class="px-3 py-2 border rounded">Cari</button>
</form>

@if(session('success'))
  <div class="mb-3 p-3 bg-green-50 text-green-800 rounded">{{ session('success') }}</div>
@endif

<table class="w-full text-left border">
  <thead class="bg-gray-50">
    <tr>
      <th class="p-2 border">#</th>
      <th class="p-2 border">Code</th>
      <th class="p-2 border">Name</th>
      <th class="p-2 border">Description</th>
      <th class="p-2 border w-40">Aksi</th>
    </tr>
  </thead>
  <tbody>
    @forelse($sites as $i => $s)
      <tr>
        <td class="p-2 border">{{ $sites->firstItem() + $i }}</td>
        <td class="p-2 border font-mono">{{ $s->code }}</td>
        <td class="p-2 border">{{ $s->name }}</td>
        <td class="p-2 border">{{ Str::limit($s->description, 80) }}</td>
        <td class="p-2 border">
          <a href="{{ route('admin.sites.edit',$s) }}" class="px-2 py-1 text-blue-700">Edit</a>
          <form action="{{ route('admin.sites.destroy',$s) }}" method="POST" class="inline"
                onsubmit="return confirm('Hapus site {{ $s->code }} ?')">
            @csrf @method('DELETE')
            <button class="px-2 py-1 text-red-700">Hapus</button>
          </form>
        </td>
      </tr>
    @empty
      <tr><td colspan="5" class="p-4 text-center text-gray-500">Belum ada data.</td></tr>
    @endforelse
  </tbody>
</table>

<div class="mt-3">{{ $sites->links() }}</div>
@endsection
