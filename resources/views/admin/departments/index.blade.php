@extends('layouts.app')
@section('title', 'Departments')
@section('content')
<x-ui.page>
<x-ui.page-header><div class="ui-page-header__copy"><x-ui.page-title>Departments</x-ui.page-title><x-ui.page-description>Kelola daftar department dan anggotanya.</x-ui.page-description></div><x-ui.page-actions>@can('create', App\Models\Department::class)<x-ui.link data-modal-form variant="primary" icon="plus" :href="route('admin.departments.create')">Tambah Department</x-ui.link>@endcan</x-ui.page-actions></x-ui.page-header>
<x-ui.table-toolbar><x-ui.table-search name="q" placeholder="Cari department…" aria-label="Cari department" /><x-ui.link variant="secondary" :href="route('admin.departments.index')">Reset</x-ui.link></x-ui.table-toolbar>
<x-ui.table><thead><tr><th>Department</th><th>Slug</th><th class="text-right">Anggota</th><th>Aksi</th></tr></thead><tbody>@forelse($departments as $d)<tr><td class="font-medium"><x-ui.truncated-text :value="$d->name" /></td><td>{{ $d->slug }}</td><td class="text-right">{{ $d->users_count }}</td><td class="ui-action-cell"><x-ui.table-actions><x-ui.icon-button data-modal-form action="edit" ability="update" :subject="$d" :href="route('admin.departments.edit', $d)" /><x-ui.icon-button action="members" ability="update" :subject="$d" :href="route('admin.departments.members', $d)" />@can('delete', $d)<form method="POST" action="{{ route('admin.departments.destroy', $d) }}" data-confirm='Hapus department "{{ $d->name }}"? Relasi anggota juga akan dihapus.'>@csrf @method('DELETE')<x-ui.icon-button action="delete" type="submit" /></form>@endcan</x-ui.table-actions></td></tr>@empty<x-ui.table-empty-state colspan="4" />@endforelse</tbody></x-ui.table>
{{ $departments->links() }}
</x-ui.page>
@endsection