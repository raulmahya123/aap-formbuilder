@extends('layouts.app')

@section('content')
<div x-data="aclUI()" x-init="init()" class="max-w-7xl mx-auto p-6 space-y-6">

  {{-- HEADER --}}
  <div class="flex items-center justify-between gap-4">
    <div>
      <h1 class="text-2xl font-extrabold tracking-wide">Kelola Akses Dokumen</h1>
      <p class="text-sm opacity-70">Tambah/hapus izin untuk user atau departemen ke banyak dokumen sekaligus.</p>
    </div>
    <a href="{{ route('admin.documents.index') }}"
       class="px-3 py-2 rounded-xl border hover:bg-gray-50">← Kembali</a>
  </div>

  {{-- FLASH / ERRORS --}}
  @if(session('success'))
    <div class="rounded-xl border border-green-200 bg-green-50 p-3 text-green-800">
      {{ session('success') }}
    </div>
  @endif
  @if ($errors->any())
    <div class="rounded-2xl border border-red-200 bg-red-50 p-3 text-red-800">
      <ul class="list-disc pl-5 space-y-0.5">
        @foreach ($errors->all() as $error)
          <li>{{ $error }}</li>
        @endforeach
      </ul>
    </div>
  @endif

  {{-- CARD: FORM TAMBAH AKSES --}}
  <div class="rounded-2xl border bg-white p-5 space-y-5">
    <div class="flex items-start gap-4 flex-col lg:flex-row">

      {{-- DOKUMEN PICKER --}}
      <div class="flex-1 min-w-[320px]">
        <label class="text-sm font-medium">Dokumen</label>
        <div class="mt-2 rounded-xl border">
          <div class="p-3 flex items-center gap-3 border-b">
            <input x-model="query" type="text" placeholder="Cari judul dokumen…"
                   class="w-full rounded-lg border px-3 py-2 focus:outline-none focus:ring">
            <button type="button" @click="selectAllFiltered()"
                    class="text-sm px-3 py-2 rounded-lg border hover:bg-gray-50">
              Pilih Semua (terfilter)
            </button>
          </div>
          <div class="max-h-64 overflow-auto">
            <template x-for="doc in filteredDocs" :key="doc.id">
              <label class="flex items-center gap-3 px-3 py-2 border-b last:border-b-0 cursor-pointer hover:bg-gray-50">
                <input type="checkbox" :value="doc.id" x-model="selectedIds"
                       class="rounded border-gray-300">
                <span class="text-sm" x-text="doc.title"></span>
              </label>
            </template>
            <div x-show="filteredDocs.length===0" class="p-4 text-sm text-center opacity-60">
              Tidak ada dokumen yang cocok.
            </div>
          </div>
          <div class="p-3 text-xs text-gray-600 border-t">
            <span x-text="selectedIds.length"></span> dipilih dari {{ count($documents) }} dokumen
          </div>
        </div>

        {{-- Hidden inputs sinkron ke form --}}
        <template x-for="id in selectedIds" :key="'h-'+id">
          <input type="hidden" name="document_ids[]" :value="id" form="aclForm">
        </template>
      </div>

      {{-- TARGET & PERMISSION --}}
<div class="w-full lg:w-96">
  <form id="aclForm" method="POST" action="{{ route('admin.documents.acl.store') }}" class="space-y-4">
    @csrf

    {{-- USER (opsional) --}}
    <div>
      <label class="text-sm font-medium">User (opsional)</label>
      <select x-model="userId" name="user_id" class="w-full mt-1 border rounded-lg px-3 py-2">
        <option value="">-- pilih --</option>
        @foreach($users as $u)
          <option value="{{ $u->id }}">{{ $u->name }}</option>
        @endforeach
      </select>
    </div>

    {{-- DEPARTEMEN (opsional) --}}
    <div>
      <label class="text-sm font-medium">Departemen (opsional)</label>
      <select x-model="departmentId" name="department_id" class="w-full mt-1 border rounded-lg px-3 py-2">
        <option value="">-- pilih --</option>
        @foreach($departments as $d)
          <option value="{{ $d->id }}">{{ $d->name }}</option>
        @endforeach
      </select>
    </div>

    <div>
      <label class="text-sm font-medium">Permission</label>
      <select name="perm" class="w-full mt-1 border rounded-lg px-3 py-2" required>
        <option value="view">View</option>
        <option value="edit">Edit</option>
        <option value="delete">Delete</option>
        <option value="share">Share</option>
        <option value="export">Export</option>
      </select>
    </div>

    <div class="pt-1 text-xs opacity-70">
      Boleh isi salah satu atau **keduanya** (User dan Departemen).
    </div>

    <div class="flex items-center gap-3 pt-2">
      <button type="submit"
              :disabled="selectedIds.length===0 || (!userId && !departmentId)"
              class="px-4 py-2 rounded-xl text-white disabled:opacity-50"
              :class="selectedIds.length>0 && (userId || departmentId)
                      ? 'bg-[#7A2C2F] hover:opacity-90'
                      : 'bg-[#7A2C2F]'">
        Tambah Akses
      </button>
      <button type="button" @click="clearSelection()" class="px-3 py-2 rounded-xl border hover:bg-gray-50">
        Bersihkan pilihan
      </button>
    </div>
  </form>
</div>

    </div>
  </div>

  {{-- CARD: DAFTAR AKSES --}}
  <div class="rounded-2xl border bg-white overflow-hidden">
    <div class="p-4 flex items-center justify-between">
      <div>
        <h2 class="font-semibold">Daftar Akses</h2>
        <p class="text-xs opacity-70">Menampilkan kombinasi ACL yang berlaku.</p>
      </div>
      <div class="flex items-center gap-2">
        <input x-model="tableQuery" type="text" placeholder="Cari user/dept/dokumen…"
               class="border rounded-lg px-3 py-2 text-sm">
      </div>
    </div>

    <div class="overflow-x-auto">
      <table class="w-full text-sm">
        <thead class="bg-[#1D1C1A] text-white">
          <tr>
            <th class="p-3 text-left">Dokumen</th>
            <th class="p-3 text-left">User</th>
            <th class="p-3 text-left">Departemen</th>
            <th class="p-3 text-left">Permission</th>
            <th class="p-3 text-left w-24">Aksi</th>
          </tr>
        </thead>
        <tbody class="divide-y">
          @php
            $permsColors = [
              'view'   => 'bg-emerald-100 text-emerald-800',
              'edit'   => 'bg-blue-100 text-blue-800',
              'delete' => 'bg-rose-100 text-rose-800',
              'share'  => 'bg-amber-100 text-amber-800',
              'export' => 'bg-purple-100 text-purple-800',
            ];
          @endphp

          @forelse($acls as $acl)
          <tr x-show="matchRow(@js($acl->document->title ?? '-'), @js($acl->user->name ?? '-'), @js($acl->department->name ?? '-'))">
            <td class="p-3 align-top">{{ $acl->document?->title ?? '-' }}</td>
            <td class="p-3 align-top">
              @if($acl->user)
                <div class="font-medium">{{ $acl->user->name }}</div>
                <div class="text-xs opacity-60">User</div>
              @else
                <span class="opacity-40">-</span>
              @endif
            </td>
            <td class="p-3 align-top">
              @if($acl->department)
                <div class="font-medium">{{ $acl->department->name }}</div>
                <div class="text-xs opacity-60">Departemen</div>
              @else
                <span class="opacity-40">-</span>
              @endif
            </td>
            <td class="p-3 align-top">
              @php $cls = $permsColors[$acl->perm] ?? 'bg-gray-100 text-gray-800'; @endphp
              <span class="inline-block px-2 py-1 rounded-full text-xs {{ $cls }}">
                {{ ucfirst($acl->perm) }}
              </span>
            </td>
            <td class="p-3 align-top">
              <form x-ref="del{{ $acl->id }}" method="POST" action="{{ route('admin.documents.acl.destroy',[$acl->document,$acl]) }}">
                @csrf @method('DELETE')
                <button type="button"
                        @click="confirmDelete($refs['del{{ $acl->id }}'])"
                        class="text-rose-600 hover:underline">
                  Hapus
                </button>
              </form>
            </td>
          </tr>
          @empty
          <tr>
            <td colspan="5" class="p-6 text-center text-sm opacity-60">
              Belum ada ACL.
            </td>
          </tr>
          @endforelse
        </tbody>
      </table>
    </div>
  </div>
</div>

{{-- Alpine Component --}}
<script>
function aclUI() {
  return {
    // STATE
    query: '',
    tableQuery: '',
    docs: @json($documents->map(fn($d)=>['id'=>$d->id,'title'=>$d->title])),
    selectedIds: [],
    userId: '',
    departmentId: '',

    init() {},

    get filteredDocs() {
      const q = this.query.trim().toLowerCase();
      if (!q) return this.docs;
      return this.docs.filter(d => d.title.toLowerCase().includes(q));
    },
    selectAllFiltered() {
      const ids = this.filteredDocs.map(d => d.id);
      const set = new Set(this.selectedIds);
      ids.forEach(id => set.add(id));
      this.selectedIds = Array.from(set);
    },
    clearSelection() { this.selectedIds = []; },
    matchRow(doc, user, dept) {
      const q = this.tableQuery.trim().toLowerCase();
      if (!q) return true;
      return (doc||'').toLowerCase().includes(q)
          || (user||'').toLowerCase().includes(q)
          || (dept||'').toLowerCase().includes(q);
    },
    confirmDelete(formEl) {
      if (confirm('Hapus akses ini?')) formEl.submit();
    },
  }
}
</script>

@endsection
