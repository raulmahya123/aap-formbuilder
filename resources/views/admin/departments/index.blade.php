@extends('layouts.app')

@section('content')
<div class="max-w-5xl mx-auto p-6">
  <div class="flex items-center justify-between mb-4">
    <h1 class="text-xl font-semibold">Departments</h1>
    @can('create', App\Models\Department::class)
      <a class="px-3 py-2 rounded bg-emerald-600 text-white" href="{{ route('admin.departments.create') }}">Tambah</a>
    @endcan
  </div>

  <div class="bg-white rounded-xl border">
    <table class="w-full text-sm">
      <thead class="bg-slate-50">
        <tr>
          <th class="text-left p-3">Nama</th>
          <th class="text-left p-3">Slug</th>
          <th class="text-left p-3">Aksi</th>
        </tr>
      </thead>
      <tbody>
        @foreach($departments as $d)
          <tr class="border-t">
            <td class="p-3">{{ $d->name }}</td>
            <td class="p-3 text-slate-500">{{ $d->slug }}</td>
            <td class="p-3 space-x-2">
              @can('update', $d)
                <a href="{{ route('admin.departments.edit',$d) }}" class="underline">Edit</a>
              @endcan
              <a href="{{ route('admin.departments.members',$d) }}" class="underline">Members</a>
              @can('delete', $d)
              <form action="{{ route('admin.departments.destroy',$d) }}" method="post" class="inline" onsubmit="return confirm('Hapus department?')">
                @csrf @method('DELETE')
                <button class="text-red-600 underline">Hapus</button>
              </form>
              @endcan
            </td>
          </tr>
        @endforeach
      </tbody>
    </table>
  </div>

  <div class="mt-4">{{ $departments->links() }}</div>
</div>
@endsection
