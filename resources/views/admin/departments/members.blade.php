@extends('layouts.app')
@section('title', 'Anggota Department')
@section('content')
<x-ui.page>
<x-ui.page-header><div class="ui-page-header__copy"><x-ui.page-title>Anggota — {{ $department->name }}</x-ui.page-title><x-ui.page-description>Kelola anggota dan peran di department ini.</x-ui.page-description></div><x-ui.page-actions><x-ui.link variant="secondary" :href="route('admin.departments.index')">Kembali</x-ui.link><x-ui.button type="button" variant="primary" icon="plus" data-dialog-open="department-member-form">Tambah / Update Anggota</x-ui.button></x-ui.page-actions>
<x-slot:filters>
<x-ui.table-toolbar><x-ui.table-search name="q" placeholder="Cari anggota…" aria-label="Cari anggota" />
<x-slot:actions>
<x-ui.link variant="secondary" :href="route('admin.departments.members',$department)">Reset</x-ui.link>
</x-slot:actions>
</x-ui.table-toolbar>
</x-slot:filters>
</x-ui.page-header>
<x-ui.table :paginator="$members"><thead><tr><th>Nama</th><th>Email</th><th>Peran department</th><th>Aksi</th></tr></thead><tbody>@forelse($members as $m)<tr><td>{{ $m->name }}</td><td>{{ $m->email }}</td><td><x-ui.status-badge>{{ $m->pivot->dept_role }}</x-ui.status-badge></td><td class="ui-action-cell"><x-ui.table-actions><form method="POST" action="{{ route('admin.departments.members.destroy',[$department,$m]) }}" data-confirm='Hapus akses "{{ $m->name }}" dari department ini?'>@csrf @method('DELETE')<x-ui.icon-button action="delete" label="Hapus akses anggota" type="submit" /></form></x-ui.table-actions></td></tr>@empty<x-ui.table-empty-state colspan="4" />@endforelse</tbody></x-ui.table>
<x-ui.modal-form id="department-member-form" title="Tambah / Update Anggota" icon="users" :action="route('admin.departments.members.store',$department)" :data-auto-open="old('_ui_dialog') === 'department-member-form' ? '' : null">
<input type="hidden" name="_ui_dialog" value="department-member-form"><div class="space-y-5"><x-ui.form-field name="user_id" label="Pengguna" required><x-ui.form-select id="user_id" name="user_id" required>@foreach($users as $u)<option value="{{ $u->id }}" @selected(old('user_id') == $u->id)>{{ $u->name }} — {{ $u->email }}</option>@endforeach</x-ui.form-select></x-ui.form-field><x-ui.form-field name="dept_role" label="Peran department" required><x-ui.form-select id="dept_role" name="dept_role" required><option value="member" @selected(old('dept_role') === 'member')>Member</option><option value="dept_admin" @selected(old('dept_role') === 'dept_admin')>Department Admin</option></x-ui.form-select></x-ui.form-field></div>
</x-ui.modal-form>
</x-ui.page>
@endsection