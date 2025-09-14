{{-- resources/views/admin/sites/index.blade.php --}}
@extends('layouts.app')
@section('title','Sites')

@section('content')
<div class="flex items-center justify-between mb-6">
  <h1 class="text-2xl font-bold text-maroon-700">Sites</h1>
  <a href="{{ route('admin.sites.create') }}"
     class="px-4 py-2 rounded-xl bg-maroon-600 hover:bg-maroon-700 text-white shadow">
     + Tambah
  </a>
</div>

<form class="mb-4 flex gap-2">
  <input class="border rounded-lg px-3 py-2 flex-1 focus:ring-2 focus:ring-maroon-400 focus:border-maroon-400"
         type="text" name="q" value="{{ $q }}" placeholder="Cari name/code...">
  <button class="px-4 py-2 rounded-lg bg-maroon-600 hover:bg-maroon-700 text-white">Cari</button>
</form>

@if(session('success'))
  <div class="mb-4 p-3 rounded-lg bg-green-50 text-green-800 border border-green-200">
    {{ session('success') }}
  </div>
@endif

<div class="overflow-hidden rounded-xl border border-gray-200 shadow-sm">
  <table class="w-full text-left">
    <thead class="bg-maroon-700 text-white">
      <tr>
        <th class="p-3 border-b">#</th>
        <th class="p-3 border-b">Code</th>
        <th class="p-3 border-b">Name</th>
        <th class="p-3 border-b">Description</th>
        <th class="p-3 border-b w-40">Aksi</th>
      </tr>
    </thead>
    <tbody class="divide-y divide-gray-100">
      @forelse($sites as $i => $s)
        <tr class="hover:bg-gray-50 transition">
          <td class="p-3">{{ $sites->firstItem() + $i }}</td>
          <td class="p-3 font-mono text-sm text-gray-700">{{ $s->code }}</td>
          <td class="p-3 font-medium">{{ $s->name }}</td>
          <td class="p-3 text-gray-600">{{ Str::limit($s->description, 80) }}</td>
          <td class="p-3 flex gap-2">
            {{-- Tombol Edit --}}
            <a href="{{ route('admin.sites.edit',$s) }}"
               class="px-3 py-1 rounded-lg bg-maroon-500 text-white hover:bg-maroon-600 shadow-sm">
               Edit
            </a>
            {{-- Tombol Hapus --}}
            <form action="{{ route('admin.sites.destroy',$s) }}" method="POST"
                  onsubmit="return confirm('Hapus site {{ $s->code }} ?')">
              @csrf @method('DELETE')
              <button class="px-3 py-1 rounded-lg bg-maroon-700 text-white hover:bg-maroon-800 shadow-sm">
                Hapus
              </button>
            </form>
          </td>
        </tr>
      @empty
        <tr>
          <td colspan="5" class="p-6 text-center text-gray-500">Belum ada data.</td>
        </tr>
      @endforelse
    </tbody>
  </table>
</div>

<div class="mt-4">{{ $sites->links() }}</div>
@endsection
