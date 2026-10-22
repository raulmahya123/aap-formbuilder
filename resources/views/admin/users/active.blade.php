@extends('layouts.app')
@section('title', 'Manage Users & Akses')
@section('content')
<x-ui.page>
  <div class="ui-module-stack w-full">
    <!-- HEADER -->
    <x-ui.page-header>
      <div class="ui-page-header__copy">
        <x-ui.page-title>Kelola Pengguna & Akses</x-ui.page-title>
        <x-ui.page-description>Kelola status aktif akun, reset password, serta penugasan role, site, dan hak akses fitur.</x-ui.page-description>
      </div>
      <x-ui.page-actions>
        <x-ui.button type="button" icon="user-round-plus" data-dialog-open="grant-site-access">Assign Site, Role & Fitur</x-ui.button>
      </x-ui.page-actions>
    </x-ui.page-header>

    <!-- TAB NAVIGATION: Menyatukan Flow Manage Users & Site Access -->
    <div class="border-b border-[#E2E8F0] dark:border-coal-800 mb-5">
      <div class="flex items-center gap-6">
        <a 
          href="{{ route('admin.users.active.index') }}" 
          class="pb-3 text-sm font-semibold transition relative border-b-2 text-[#0057FF] border-[#0057FF] flex items-center gap-2"
        >
          <x-ui.lucide name="users" class="w-4 h-4" />
          <span>Status Akun & Pengguna</span>
        </a>

        <a 
          href="{{ route('admin.site_access.index') }}" 
          class="pb-3 text-sm font-medium transition relative border-b-2 border-transparent text-[#64748B] hover:text-[#0F172A] hover:border-slate-300 flex items-center gap-2"
        >
          <x-ui.lucide name="shield-check" class="w-4 h-4" />
          <span>Site, Role & Hak Akses Fitur</span>
        </a>
      </div>
    </div>

    <!-- TOOLBAR FILTERS -->
    <x-ui.table-toolbar>
      <x-ui.table-search name="q" placeholder="Cari nama atau email…" aria-label="Cari pengguna" />
      <x-ui.table-filter name="role" :options="$roles" label="Role" placeholder="Semua role" />
      <x-ui.table-filter name="site_id" :options="$sites->pluck('name','id')" label="Site" placeholder="Semua site" />
      <x-ui.table-filter name="status" :options="['active'=>'Aktif','inactive'=>'Nonaktif']" label="Status" placeholder="Semua status" />
      <x-slot:actions>
        <x-ui.link variant="secondary" :href="route('admin.users.active.index')">Reset</x-ui.link>
      </x-slot:actions>
    </x-ui.table-toolbar>

    @if (isset($errors) && $errors->any())
      <div class="mb-4 rounded-lg border border-rose-200 bg-rose-50 px-4 py-3 text-sm text-rose-700">
        {{ $errors->first() }}
      </div>
    @endif

    <!-- TABLE -->
    <div class="rounded-lg border bg-white dark:bg-coal-900 border-[#E2E8F0] dark:border-coal-800 shadow-none overflow-x-auto">
      <x-ui.table :paginator="$users" class="w-full text-sm min-w-[860px]">
        <thead class="bg-slate-50 dark:bg-coal-800/60 text-slate-700 dark:text-coal-300">
          <tr>
            <th class="px-4 py-3 text-left">Nama</th>
            <th class="px-4 py-3 text-left">Email</th>
            <th class="px-4 py-3 text-left">Role / Site</th>
            <th class="px-4 py-3 text-left">Department</th>
            <th class="px-4 py-3 text-left">Status</th>
            <th class="px-4 py-3 text-left w-36">Aksi & Akses</th>
          </tr>
        </thead>
        <tbody class="divide-y divide-[#E2E8F0] dark:divide-coal-800">
          @forelse ($users as $user)
            <tr class="hover:bg-slate-50/80 dark:hover:bg-coal-800/50 transition-colors">
              <td class="px-4 py-3 font-medium text-slate-900 dark:text-white">{{ $user->name }}</td>
              <td class="px-4 py-3 text-slate-600 dark:text-slate-300">
                <x-ui.truncated-text :value="$user->email" />
              </td>
              <td class="px-4 py-3">
                <div class="font-medium text-slate-800">{{ $user->roleLabel() }}</div>
                <div class="text-xs text-slate-500 mt-0.5">
                  {{ $user->isSuperAdmin() ? 'Seluruh site (HO)' : ($user->sites->pluck('code')->join(', ') ?: 'Belum ada site') }}
                </div>
              </td>
              <td class="px-4 py-3 text-slate-600 dark:text-slate-300">
                @if($user->departments->isNotEmpty())
                  <div class="flex flex-wrap gap-1">
                    @foreach($user->departments as $dept)
                      <span class="inline-flex items-center px-2 py-0.5 rounded text-xs bg-slate-100 text-slate-700">
                        {{ $dept->name }}
                      </span>
                    @endforeach
                  </div>
                @else
                  <span class="text-slate-400 text-xs">—</span>
                @endif
              </td>
              <td class="px-4 py-3">
                @if ($user->is_active)
                  <x-ui.badge variant="success" class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium">
                    Aktif
                  </x-ui.badge>
                @else
                  <x-ui.badge variant="danger" class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium">
                    Nonaktif
                  </x-ui.badge>
                @endif
              </td>
              <td class="ui-action-cell px-4 py-3">
                <x-ui.table-actions>
                  <!-- Quick assign role & site for this specific user -->
                  <x-ui.icon-button 
                    action="assign" 
                    label="Atur Site, Role & Fitur {{ $user->name }}" 
                    data-dialog-open="grant-site-access"
                    data-prefill-user="{{ $user->email }}"
                    onclick="
                      const sel = document.querySelector('#grant-site-access [data-feature-user-select]');
                      if (sel) {
                        sel.value = '{{ $user->email }}';
                        sel.dispatchEvent(new Event('change', { bubbles: true }));
                      }
                    "
                  />

                  @if($user->id !== auth()->id() && !$user->isSuperAdmin())
                    <form action="{{ route('admin.users.active.toggle', $user) }}" method="POST" class="inline">
                      @csrf
                      @method('PATCH')
                      <x-ui.icon-button :action="$user->is_active ? 'deactivate' : 'activate'" :label="$user->is_active ? 'Nonaktifkan akun' : 'Aktifkan akun'" type="submit" />
                    </form>
                  @endif

                  <x-ui.icon-button action="reset-password" label="Reset password {{ $user->name }}" data-reset-url="{{ route('admin.users.active.password', $user) }}" data-reset-user="{{ $user->name }}" />
                </x-ui.table-actions>
              </td>
            </tr>
          @empty
            <x-ui.table-empty-state colspan="6" />
          @endforelse
        </tbody>
      </x-ui.table>
    </div>
  </div>
</x-ui.page>

@push('modals')
<x-ui.modal-form id="grant-site-access" title="Assign Site, Role & Fitur" description="Pilih pengguna, role akun, satu atau beberapa site, department, dan fitur." icon="user-round-plus" :action="route('admin.site_access.store')" submit-label="Simpan Penugasan" :data-auto-open="old('_ui_dialog') === 'grant-site-access' && $errors->any() ? 'true' : null">
  <input type="hidden" name="_ui_dialog" value="grant-site-access">
  <x-ui.form-field name="email" label="Pengguna" required>
    <x-ui.form-select id="email" name="email" required data-feature-user-select>
      <option value="">Pilih pengguna</option>
      @foreach($allUsers as $u)
        <option data-sites="{{ json_encode($u->sites->pluck('id')) }}" data-role="{{ $u->role }}" data-features="{{ json_encode($u->feature_access) }}" value="{{ $u->email }}" @selected(old('email') === $u->email)>{{ $u->name }} — {{ $u->email }}</option>
      @endforeach
    </x-ui.form-select>
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
      @foreach($sites as $site)
        <label class="flex items-center gap-2 text-sm">
          <input type="checkbox" name="site_ids[]" value="{{ $site->id }}" @checked(in_array($site->id, old('site_ids', [])))>
          {{ $site->displayLabel() }}
        </label>
      @endforeach
    </div>
    @error('site_ids')<p class="mt-2 text-sm text-red-700">{{ $message }}</p>@enderror
  </fieldset>

  <x-ui.form-field name="department_id" label="Department" helper="Keanggotaan kerja dan akses dokumen; tidak mengubah pengguna menjadi admin. Department lain yang sudah dimiliki tetap dipertahankan.">
    <x-ui.form-select id="department_id" name="department_id">
      <option value="">Tanpa perubahan department</option>
      @foreach($departments as $department)
        <option value="{{ $department->id }}" @selected(old('department_id') == $department->id)>{{ $department->name }}</option>
      @endforeach
    </x-ui.form-select>
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
        <label class="flex items-center gap-2 text-sm">
          <input type="checkbox" name="feature_access[]" value="{{ $key }}" @checked(in_array($key, old('feature_access', [])))>
          {{ $label }}
        </label>
      @endforeach
    </fieldset>
    <p class="mt-2 text-xs text-zinc-600">Centang modul yang boleh dibuka pengguna. Jika tidak ada fitur dicentang pada mode terbatas, pengguna hanya dapat membuka akun dan keluar. Admin HO dan Super Admin memiliki seluruh fitur.</p>
  </div>
  <p class="text-xs text-zinc-600">Penugasan yang sudah ada diperbarui tanpa duplikat. Status aktif akun tidak berubah.</p>
</x-ui.modal-form>
@endpush
@endsection
