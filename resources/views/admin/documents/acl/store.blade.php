@extends('layouts.app')

@section('title', 'Kelola Akses Dokumen')

@section('content')
<div class="max-w-5xl mx-auto p-6 space-y-6">
  <div class="flex items-center justify-between">
    <h1 class="text-xl font-semibold">Kelola Akses Dokumen</h1>
    <a href="{{ url()->previous() }}" class="text-sm underline">Kembali</a>
  </div>

  {{-- Flash message --}}
  @if(session('success'))
    <div class="p-3 rounded bg-green-100 text-green-800">{{ session('success') }}</div>
  @endif

  {{-- Error --}}
  @if ($errors->any())
    <div class="p-3 rounded bg-red-100 text-red-700">
      <ul class="list-disc list-inside text-sm">
        @foreach ($errors->all() as $e)
          <li>{{ $e }}</li>
        @endforeach
      </ul>
    </div>
  @endif

  {{-- Form Tambah Akses --}}
  <form method="POST" action="{{ route('admin.documents.acl.store') }}" class="bg-white border rounded-xl p-4 space-y-4">
    @csrf
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
      <div>
        <label class="text-sm font-medium">Dokumen</label>
        <select name="document_id" class="mt-1 w-full border rounded px-2 py-1.5" required>
          <option value="">-- pilih dokumen --</option>
          @foreach($documents as $doc)
            <option value="{{ $doc->id }}" @selected(old('document_id')==$doc->id)>{{ $doc->title }}</option>
          @endforeach
        </select>
      </div>

      <div>
        <label class="text-sm font-medium">User</label>
        <select name="user_id" class="mt-1 w-full border rounded px-2 py-1.5">
          <option value="">-- pilih user --</option>
          @foreach($users as $u)
            <option value="{{ $u->id }}" @selected(old('user_id')==$u->id)>{{ $u->name }}</option>
          @endforeach
        </select>
        <p class="text-xs text-gray-500 mt-1">Pilih salah satu: User <em>atau</em> Departemen</p>
      </div>

      <div>
        <label class="text-sm font-medium">Departemen</label>
        <select name="department_id" class="mt-1 w-full border rounded px-2 py-1.5">
          <option value="">-- pilih departemen --</option>
          @foreach($departments as $d)
            <option value="{{ $d->id }}" @selected(old('department_id')==$d->id)>{{ $d->name }}</option>
          @endforeach
        </select>
      </div>

      <div>
        <label class="text-sm font-medium">Permission</label>
        <select name="perm" class="mt-1 w-full border rounded px-2 py-1.5" required>
          @foreach(['view','edit','delete','share','export'] as $p)
            <option value="{{ $p }}" @selected(old('perm')==$p)>{{ ucfirst($p) }}</option>
          @endforeach
        </select>
      </div>
    </div>

    <div class="pt-2">
      <button class="px-4 py-2 bg-[#7A2C2F] text-white rounded">Tambah</button>
    </div>
  </form>

  {{-- Daftar Akses --}}
  <div class="bg-white border rounded-xl overflow-x-auto">
    <table class="w-full text-sm">
      <thead class="bg-[#1D1C1A] text-white">
        <tr>
          <th class="p-2 text-left">Dokumen</th>
          <th class="p-2 text-left">User</th>
          <th class="p-2 text-left">Departemen</th>
          <th class="p-2 text-left">Permission</th>
          <th class="p-2 w-24"></th>
        </tr>
      </thead>
      <tbody>
        @forelse($acls as $acl)
          <tr class="border-t">
            <td class="p-2">{{ $acl->document?->title ?? '-' }}</td>
            <td class="p-2">{{ $acl->user?->name ?? '-' }}</td>
            <td class="p-2">{{ $acl->department?->name ?? '-' }}</td>
            <td class="p-2">{{ ucfirst($acl->perm) }}</td>
            <td class="p-2">
              <form method="POST" action="{{ route('admin.documents.acl.destroy', $acl) }}"
                    onsubmit="return confirm('Hapus akses ini?')">
                @csrf @method('DELETE')
                <button class="text-red-600 hover:underline">Hapus</button>
              </form>
            </td>
          </tr>
        @empty
          <tr>
            <td colspan="5" class="p-4 text-center text-gray-500">Belum ada akses.</td>
          </tr>
        @endforelse
      </tbody>
    </table>
  </div>
</div>
@endsection
