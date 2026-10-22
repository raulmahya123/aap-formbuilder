@extends('layouts.app')
@section('content')
<x-ui.page x-data="aclUI()" x-init="init()" class="w-full min-w-0 ">
  {{-- HEADER --}}
  <x-ui.page-header>
  <div class="ui-page-header__copy"><div>
      <x-ui.page-title>Kelola Akses Dokumen</x-ui.page-title>
      <x-ui.page-description>Tambah/hapus izin untuk user atau departemen ke banyak dokumen sekaligus.</x-ui.page-description>
    </div></div>
  <x-ui.page-actions><x-ui.link variant="secondary" href="{{ route('admin.documents.index') }}"
       class="px-3 py-2 rounded-lg border">← Kembali</x-ui.link></x-ui.page-actions>
</x-ui.page-header>
  {{-- FLASH / ERRORS --}}
  @if ($errors->any())
    <div class="rounded-lg border border-red-200 bg-red-50 p-3 text-red-800">
      <ul class="list-disc pl-5 space-y-1">
        @foreach ($errors->all() as $error)
          <li>{{ $error }}</li>
        @endforeach
      </ul>
    </div>
  @endif
  {{-- CARD: FORM TAMBAH AKSES --}}
  <x-ui.panel class="rounded-lg border bg-white space-y-5">
    <div class="flex items-start gap-4 flex-col lg:flex-row">
      {{-- DOKUMEN PICKER --}}
      <div class="flex-1 min-w-[320px]">
        <label class="text-sm font-medium">Dokumen</label>
        <div class="mt-2 rounded-lg border">
          <div class="p-3 flex items-center gap-3 border-b">
            <input x-model="query" type="text" placeholder="Cari judul dokumen…"
                   class="w-full rounded-lg border px-3 py-2 focus:outline-none focus:ring">
            <x-ui.button variant="secondary" type="button" @click="selectAllFiltered()"
                    class="text-sm px-3 py-2 rounded-lg border">
              Pilih Semua (terfilter)
            </x-ui.button>
          </div>
          <div class="max-h-64 overflow-auto">
            <template x-for="doc in filteredDocs" :key="doc.id">
              <label class="flex items-center gap-3 px-3 py-2 border-b last:border-b-0 cursor-pointer hover:bg-gray-50">
                <input type="checkbox" :value="doc.id" x-model="selectedIds"
                       class="rounded-lg border-gray-300">
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
      <x-ui.button variant="primary" type="submit"
              ::disabled="selectedIds.length===0 || (!userId && !departmentId)"
              class="px-4 py-2 rounded-lg disabled:opacity-50"
              ::class="selectedIds.length>0 && (userId || departmentId) ? 'bg-[#713F12] hover:opacity-90' : 'bg-[#713F12]'">
        Tambah Akses
      </x-ui.button>
      <x-ui.button variant="secondary" type="button" @click="clearSelection()" class="px-3 py-2 rounded-lg border">
        Bersihkan pilihan
      </x-ui.button>
    </div>
  </form>
</div>
    </div>
  </x-ui.panel>
  {{-- CARD: DAFTAR AKSES --}}
  <x-ui.panel :padded="false" class="rounded-lg border bg-white overflow-hidden">
    <div class="p-4 flex items-center justify-between">
      <div>
        <x-ui.section-title class="font-semibold">Daftar Akses</x-ui.section-title>
        <p class="text-xs opacity-70">Menampilkan kombinasi ACL yang berlaku.</p>
      </div>
      <div class="flex items-center gap-2">
        <input x-model="tableQuery" type="text" placeholder="Cari user/dept/dokumen…"
               class="border rounded-lg px-3 py-2 text-sm">
      </div>
    </div>
    <div class="overflow-x-auto">
      <x-ui.table class="w-full text-sm">
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
              'view'   => 'ui-status ui-status--success',
              'edit'   => 'ui-status ui-status--info',
              'delete' => 'ui-status ui-status--danger',
              'share'  => 'ui-status ui-status--warning',
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
            <td class="ui-action-cell p-3 align-top"><x-ui.table-actions>
              <form x-ref="del{{ $acl->id }}" method="POST" action="{{ route('admin.documents.acl.destroy',[$acl->document,$acl]) }}" data-confirm="Hapus akses ini?">
                @csrf @method('DELETE')
                <x-ui.icon-button action="delete" label="Hapus akses" ability="share" :subject="$acl->document" type="submit" />
              </form>
            </x-ui.table-actions></td>
          </tr>
          @empty
<x-ui.table-empty-state colspan="5"></x-ui.table-empty-state>
@endforelse
        </tbody>
      </x-ui.table>
    </div>
  </x-ui.panel>
</x-ui.page>
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