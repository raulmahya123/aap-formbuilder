@extends('layouts.app')
@section('title', 'Site & Role Pengguna')
@section('content')
<x-ui.page>
  <x-ui.page-header>
    <div class="ui-page-header__copy"><x-ui.page-title>Site & Role Pengguna</x-ui.page-title><x-ui.page-description>Kelola penugasan site, role akun, dan peran department pengguna.</x-ui.page-description></div>
    <x-ui.page-actions><x-ui.button type="button" icon="user-round-plus" data-dialog-open="grant-site-access">Assign Site & Role</x-ui.button></x-ui.page-actions>
    <x-slot:filters>
      <x-ui.table-toolbar>
        <x-ui.table-search name="q" placeholder="Cari nama atau email pengguna…" aria-label="Cari pengguna" />
        <x-ui.table-filter name="site_id" :options="$sites->mapWithKeys(fn ($site) => [$site->id => $site->displayLabel()])" :value="$siteId" label="Site" placeholder="Semua site" />
        @if($email)<input type="hidden" name="email" value="{{ $email }}"><span class="text-sm">Email: {{ $email }}</span>@endif
        <x-slot:actions>
          <x-ui.button variant="secondary" type="submit">Terapkan</x-ui.button>
          <x-ui.link variant="secondary" :href="route('admin.site_access.index', ['site_id'=>''])">Reset</x-ui.link>
        </x-slot:actions>
      </x-ui.table-toolbar>
    </x-slot:filters>
  </x-ui.page-header>
  <x-ui.panel>
    <x-ui.section-title>Role dan cakupan site</x-ui.section-title>
    <p class="mt-2 text-sm text-zinc-600">Pilih pengguna dan site, lalu tentukan role akun atau peran department jika ingin mengubahnya. Admin Department memerlukan department; Super Admin berlaku untuk seluruh site. Mencabut site hanya menghapus penugasan lokasi, bukan role akun.</p>
  </x-ui.panel>
  <x-ui.table :paginator="$accesses">
    <thead><tr><th>Pengguna</th><th>Role akun</th><th>Site yang diberikan</th><th>Diberikan pada</th><th>Aksi</th></tr></thead>
    <tbody>
      @forelse($accesses as $access)
        <tr>
          <td><div class="font-medium">{{ $access->user?->name ?? 'Pengguna tidak tersedia' }}</div><div class="text-xs text-zinc-600">{{ $access->user?->email }}</div>@if($access->user && !$access->user->is_active)<x-ui.status-badge variant="warning">Akun nonaktif</x-ui.status-badge>@endif</td>
          <td><div>{{ $access->user?->isSuperAdmin() ? 'Super Admin · seluruh site' : ucfirst(str_replace('_', ' ', $access->user?->role ?? '—')) }}</div>@foreach($access->user?->departments ?? [] as $department)<div class="text-xs text-zinc-600">{{ $department->name }} · {{ $department->pivot->dept_role === 'dept_admin' ? 'Admin Department' : 'Member' }}</div>@endforeach</td>
          <td>{{ $access->site?->displayLabel() ?? 'Site tidak tersedia' }}</td>
          <td>{{ $access->created_at?->format('d M Y H:i') ?? '—' }}</td>
          <td class="ui-action-cell"><x-ui.table-actions>
            <form method="POST" action="{{ route('admin.site_access.destroy', $access) }}" data-confirm="Cabut akses {{ $access->site?->displayLabel() }} untuk {{ $access->user?->name }}? Role akun tidak berubah. {{ $access->user?->isSuperAdmin() ? 'Super Admin tetap memiliki akses seluruh site.' : '' }}">
              @csrf @method('DELETE')<x-ui.icon-button action="delete" label="Cabut akses site" type="submit" />
            </form>
          </x-ui.table-actions></td>
        </tr>
      @empty
        <x-ui.table-empty-state colspan="5" />
      @endforelse
    </tbody>
  </x-ui.table>
</x-ui.page>
@push('modals')
<x-ui.modal-form id="grant-site-access" title="Assign Site & Role" description="Simpan akses site dan perubahan role dalam satu proses." icon="user-round-plus" :action="route('admin.site_access.store')" submit-label="Simpan Penugasan" :data-auto-open="old('_ui_dialog') === 'grant-site-access' && $errors->any() ? 'true' : null">
  <input type="hidden" name="_ui_dialog" value="grant-site-access">
  <x-ui.form-field name="email" label="Pengguna" required>
    <x-ui.form-select id="email" name="email" required><option value="">Pilih pengguna</option>@foreach($users as $user)<option value="{{ $user->email }}" @selected(old('email') === $user->email)>{{ $user->name }} — {{ $user->email }}</option>@endforeach</x-ui.form-select>
  </x-ui.form-field>
  <x-ui.form-field name="site_id" label="Site" required>
    <x-ui.form-select id="site_id" name="site_id" required><option value="">Pilih site</option>@foreach($sites as $site)<option value="{{ $site->id }}" @selected(old('site_id', $siteId) == $site->id)>{{ $site->displayLabel() }}</option>@endforeach</x-ui.form-select>
  </x-ui.form-field>
  <x-ui.form-field name="role" label="Role akun" helper="Super Admin memiliki akses seluruh site. Pilih tanpa perubahan untuk mempertahankan role saat ini.">
    <x-ui.form-select id="role" name="role"><option value="">Tanpa perubahan</option><option value="user" @selected(old('role') === 'user')>User</option><option value="super_admin" @selected(old('role') === 'super_admin')>Super Admin — seluruh site</option></x-ui.form-select>
  </x-ui.form-field>
  <x-ui.form-field name="department_id" label="Department" helper="Isi bersama peran department jika ingin menambah atau mengubah keanggotaan.">
    <x-ui.form-select id="department_id" name="department_id"><option value="">Tanpa perubahan</option>@foreach($departments as $department)<option value="{{ $department->id }}" @selected(old('department_id') == $department->id)>{{ $department->name }}</option>@endforeach</x-ui.form-select>
  </x-ui.form-field>
  <x-ui.form-field name="dept_role" label="Peran department">
    <x-ui.form-select id="dept_role" name="dept_role"><option value="">Tanpa perubahan</option><option value="member" @selected(old('dept_role') === 'member')>Member</option><option value="dept_admin" @selected(old('dept_role') === 'dept_admin')>Admin Department</option></x-ui.form-select>
  </x-ui.form-field>
  <p class="text-xs text-zinc-600">Penugasan yang sudah ada diperbarui tanpa duplikat. Status aktif akun tidak berubah.</p>
</x-ui.modal-form>
@endpush
@endsection
