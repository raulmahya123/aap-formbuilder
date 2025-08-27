@extends('layouts.app')

@section('content')
<div class="max-w-4xl mx-auto p-6">
  <div class="flex items-center justify-between mb-4">
    <h1 class="text-xl font-semibold">Members — {{ $department->name }}</h1>
    <a href="{{ route('admin.departments.index') }}" class="text-sm underline">← Kembali</a>
  </div>

  <div class="mb-6 bg-white border rounded-xl p-4">
    <h2 class="font-medium mb-3">Tambah/Update Akses</h2>
    <form action="{{ route('admin.departments.members.store', $department) }}" method="post" class="grid md:grid-cols-3 gap-3">
      @csrf
      <div>
        <label class="block text-sm mb-1">User</label>
        <select name="user_id" class="border rounded w-full">
          @foreach($users as $u)
            <option value="{{ $u->id }}">{{ $u->name }} — {{ $u->email }}</option>
          @endforeach
        </select>
      </div>
      <div>
        <label class="block text-sm mb-1">Role</label>
        <select name="dept_role" class="border rounded w-full">
          <option value="member">member</option>
          <option value="dept_admin">dept_admin</option>
        </select>
      </div>
      <div class="flex items-end">
        <button class="px-4 py-2 bg-emerald-600 text-white rounded">Simpan</button>
      </div>
    </form>
  </div>

  <div class="bg-white border rounded-xl overflow-hidden">
    <table class="w-full text-sm">
      <thead class="bg-slate-50">
        <tr>
          <th class="text-left p-3">Nama</th>
          <th class="text-left p-3">Email</th>
          <th class="text-left p-3">Dept Role</th>
          <th class="text-left p-3">Aksi</th>
        </tr>
      </thead>
      <tbody>
        @forelse($members as $m)
          <tr class="border-t">
            <td class="p-3">{{ $m->name }}</td>
            <td class="p-3 text-slate-500">{{ $m->email }}</td>
            <td class="p-3">{{ $m->pivot->dept_role }}</td>
            <td class="p-3">
              <form action="{{ route('admin.departments.members.destroy', [$department, $m]) }}" method="post" onsubmit="return confirm('Hapus akses user ini?')">
                @csrf @method('DELETE')
                <button class="text-red-600 underline">Hapus Akses</button>
              </form>
            </td>
          </tr>
        @empty
          <tr><td colspan="4" class="p-3 text-slate-500">Belum ada member.</td></tr>
        @endforelse
      </tbody>
    </table>
  </div>
</div>
@endsection
