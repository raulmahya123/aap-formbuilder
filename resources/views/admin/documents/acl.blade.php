@extends('layouts.app')

@section('content')
<div class="max-w-4xl mx-auto p-6 space-y-6">
  <h1 class="text-xl font-semibold">Kelola Akses: {{ $document->title }}</h1>

  {{-- Tambah akses --}}
  <form method="POST" action="{{ route('admin.documents.acl.store',$document) }}" class="flex gap-3 items-end">
    @csrf
    <div>
      <label class="text-sm">User</label>
      <select name="user_id" class="border rounded px-2 py-1">
        <option value="">-- pilih --</option>
        @foreach($users as $u)
          <option value="{{ $u->id }}">{{ $u->name }}</option>
        @endforeach
      </select>
    </div>
    <div>
      <label class="text-sm">Departemen</label>
      <select name="department_id" class="border rounded px-2 py-1">
        <option value="">-- pilih --</option>
        @foreach($departments as $d)
          <option value="{{ $d->id }}">{{ $d->name }}</option>
        @endforeach
      </select>
    </div>
    <div>
      <label class="text-sm">Permission</label>
      <select name="perm" class="border rounded px-2 py-1" required>
        <option value="view">View</option>
        <option value="edit">Edit</option>
        <option value="delete">Delete</option>
        <option value="share">Share</option>
        <option value="export">Export</option>
      </select>
    </div>
    <button class="px-3 py-2 bg-[#7A2C2F] text-white rounded">Tambah</button>
  </form>

  {{-- Daftar akses --}}
  <div class="bg-white border rounded-xl">
    <table class="w-full text-sm">
      <thead class="bg-[#1D1C1A] text-white">
        <tr>
          <th class="p-2 text-left">User</th>
          <th class="p-2 text-left">Departemen</th>
          <th class="p-2 text-left">Permission</th>
          <th class="p-2"></th>
        </tr>
      </thead>
      <tbody>
        @foreach($acls as $acl)
        <tr class="border-t">
          <td class="p-2">{{ $acl->user?->name ?? '-' }}</td>
          <td class="p-2">{{ $acl->department?->name ?? '-' }}</td>
          <td class="p-2">{{ ucfirst($acl->perm) }}</td>
          <td class="p-2">
            <form method="POST" action="{{ route('admin.documents.acl.destroy',[$document,$acl]) }}">
              @csrf @method('DELETE')
              <button class="text-red-600 hover:underline">Hapus</button>
            </form>
          </td>
        </tr>
        @endforeach
      </tbody>
    </table>
  </div>
</div>
@endsection
