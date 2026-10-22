@extends('layouts.app')
@section('title', 'Site, Role & Fitur Pengguna')
@section('content')
<x-ui.page>
  <x-ui.page-header>
    <div class="ui-page-header__copy"><x-ui.page-title>Site, Role & Fitur Pengguna</x-ui.page-title><x-ui.page-description>Kelola penugasan site, role akun, dan peran department pengguna.</x-ui.page-description></div>
    <x-ui.page-actions><x-ui.button type="button" icon="user-round-plus" data-dialog-open="grant-site-access">Assign Site, Role & Fitur</x-ui.button></x-ui.page-actions>
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
    <p class="mt-2 text-sm text-zinc-600">Pilih pengguna dan site, lalu tentukan role akun atau peran department jika ingin mengubahnya. Admin HO memiliki seluruh site. Admin Site dan User hanya mengakses site yang dipilih. Department mengelompokkan pekerjaan dan membatasi dokumen Mandala. Mencabut site hanya menghapus penugasan lokasi, bukan role akun.</p>
  </x-ui.panel>
  <x-ui.table :paginator="$accesses">
    <thead><tr><th>Pengguna</th><th>Role akun</th><th>Site yang diberikan</th><th>Akses fitur</th><th>Diberikan pada</th><th>Aksi</th></tr></thead>
    <tbody>
      @forelse($accesses as $access)
        <tr>
          <td><div class="font-medium">{{ $access->user?->name ?? 'Pengguna tidak tersedia' }}</div><div class="text-xs text-zinc-600">{{ $access->user?->email }}</div>@if($access->user && !$access->user->is_active)<x-ui.status-badge variant="warning">Akun nonaktif</x-ui.status-badge>@endif</td>
          <td><div>{{ ($access->user?->roleLabel() ?? '—').($access->user?->isSuperAdmin() ? ' · seluruh site' : '') }}</div>@foreach($access->user?->departments ?? [] as $department)<div class="text-xs text-zinc-600">{{ $department->name }} · {{ $department->pivot->dept_role === 'dept_admin' ? 'Admin Department' : 'Member' }}</div>@endforeach</td>
          <td>{{ $access->site?->displayLabel() ?? 'Site tidak tersedia' }}</td>
          <td>@if($access->user?->isSuperAdmin()) Seluruh fitur @elseif($access->user?->feature_access === null) Mengikuti izin role @else {{ collect($access->user->feature_access)->map(fn ($key) => $features[$key] ?? $key)->join(', ') ?: 'Tidak ada fitur' }} @endif</td>
          <td>{{ $access->created_at?->format('d M Y H:i') ?? '—' }}</td>
          <td class="ui-action-cell"><x-ui.table-actions>
            <form method="POST" action="{{ route('admin.site_access.destroy', $access) }}" data-confirm="Cabut akses {{ $access->site?->displayLabel() }} untuk {{ $access->user?->name }}? Role akun tidak berubah. {{ $access->user?->isSuperAdmin() ? 'Super Admin tetap memiliki akses seluruh site.' : '' }}">
              @csrf @method('DELETE')<x-ui.icon-button action="delete" label="Cabut akses site" type="submit" />
            </form>
          </x-ui.table-actions></td>
        </tr>
      @empty
        <x-ui.table-empty-state colspan="6" />
      @endforelse
    </tbody>
  </x-ui.table>
</x-ui.page>
@push('modals')
<x-ui.modal-form id="grant-site-access" title="Assign Site, Role & Fitur" description="Pilih pengguna, role, satu atau beberapa site, department, dan fitur." icon="user-round-plus" :action="route('admin.site_access.store')" submit-label="Simpan Penugasan" :data-auto-open="old('_ui_dialog') === 'grant-site-access' && $errors->any() ? 'true' : null">
  <input type="hidden" name="_ui_dialog" value="grant-site-access">
  <x-ui.form-field name="email" label="Pengguna" required>
    <x-ui.form-select id="email" name="email" required data-feature-user-select><option value="">Pilih pengguna</option>@foreach($users as $user)<option data-sites="{{ json_encode($user->sites->pluck('id')) }}" data-role="{{ $user->role }}" data-features="{{ json_encode($user->feature_access) }}" value="{{ $user->email }}" @selected(old('email') === $user->email)>{{ $user->name }} — {{ $user->email }}</option>@endforeach</x-ui.form-select>
  </x-ui.form-field>
  <x-ui.form-field name="role" label="Role akun" required helper="Admin HO: seluruh site. Admin Site: kelola operasional site terpilih. User: akses biasa sesuai fitur.">
    <x-ui.form-select id="role" name="role" required data-assignment-role>
      <option value="">Pilih role</option>
      <option value="admin_ho" @selected(old('role') === 'admin_ho')>Admin HO — seluruh site</option>
      <option value="admin_site" @selected(old('role') === 'admin_site')>Admin Site</option>
      <option value="user" @selected(old('role') === 'user')>User</option>
      <option value="super_admin" @selected(old('role') === 'super_admin')>Super Admin — kompatibilitas akun lama</option>
    </x-ui.form-select>
  </x-ui.form-field>
  <input type="hidden" name="site_selection" value="replace">
  <fieldset class="ui-assignment-sites mt-4" data-assignment-sites>
    <legend class="mb-2 text-sm font-medium">Site yang diizinkan</legend>
    <p class="mb-3 text-xs text-zinc-600">Pilih satu atau beberapa site. Daftar ini mengganti penugasan site pengguna; Admin HO tidak memerlukan penugasan site.</p>
    <div class="grid gap-3 sm:grid-cols-2">
      @foreach($sites as $site)<label class="flex items-center gap-2 text-sm"><input type="checkbox" name="site_ids[]" value="{{ $site->id }}" @checked(in_array($site->id, old('site_ids', [])))>{{ $site->displayLabel() }}</label>@endforeach
    </div>
    @error('site_ids')<p class="mt-2 text-sm text-red-700">{{ $message }}</p>@enderror
  </fieldset>
  <x-ui.form-field name="department_id" label="Department" helper="Keanggotaan kerja dan akses dokumen; tidak mengubah pengguna menjadi admin. Department lain yang sudah dimiliki tetap dipertahankan.">
    <x-ui.form-select id="department_id" name="department_id"><option value="">Tanpa perubahan department</option>@foreach($departments as $department)<option value="{{ $department->id }}" @selected(old('department_id') == $department->id)>{{ $department->name }}</option>@endforeach</x-ui.form-select>
  </x-ui.form-field>
  <div data-feature-settings x-data="{ mode: @js(old('feature_mode', 'keep')) }">
    <x-ui.form-field name="feature_mode" label="Akses fitur" helper="Izin fitur tidak menambah hak di luar role, department, dan site pengguna.">
      <x-ui.form-select id="feature_mode" name="feature_mode" x-model="mode">
        <option value="keep">Tanpa perubahan</option>
        <option value="custom">Hanya fitur yang dipilih</option>
        <option value="default">Kembali mengikuti izin role</option>
      </x-ui.form-select>
    </x-ui.form-field>
    <p class="mt-3 text-xs text-zinc-600" x-show="mode !== 'custom'">Untuk mengatur checkbox di bawah, pilih “Hanya fitur yang dipilih”.</p>
    <fieldset :disabled="mode !== 'custom'" class="mt-3 grid gap-3 sm:grid-cols-2 disabled:opacity-60">
      <legend class="mb-3 text-sm font-medium">Fitur yang diizinkan</legend>
      @foreach($features as $key => $label)
        <label class="flex items-center gap-2 text-sm"><input type="checkbox" name="feature_access[]" value="{{ $key }}" @checked(in_array($key, old('feature_access', [])))>{{ $label }}</label>
      @endforeach
    </fieldset>
    <p class="mt-2 text-xs text-zinc-600">Centang modul yang boleh dibuka pengguna. Jika tidak ada fitur dicentang pada mode terbatas, pengguna hanya dapat membuka akun dan keluar. Admin HO dan Super Admin memiliki seluruh fitur.</p>
  </div>
  <p class="text-xs text-zinc-600">Penugasan yang sudah ada diperbarui tanpa duplikat. Status aktif akun tidak berubah.</p>
</x-ui.modal-form>
@endpush
@endsection
