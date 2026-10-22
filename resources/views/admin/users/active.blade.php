  @extends('layouts.app')
  @section('title', 'Manage Users')
  @section('content')
  <x-ui.page>
    <div class="ui-module-stack w-full">
      <!-- HEADER -->
      <x-ui.page-header>
  <div class="ui-page-header__copy"><x-ui.page-title>Kelola Status User</x-ui.page-title>
        <x-ui.page-description>Aktif/nonaktifkan akun pengguna dan reset password saat dibutuhkan.</x-ui.page-description></div>
  <x-ui.page-actions></x-ui.page-actions>

<x-slot:filters>
<x-ui.table-toolbar><x-ui.table-search name="q" placeholder="Cari nama atau email…" aria-label="Cari pengguna" /><x-ui.table-filter name="role" :options="$roles" label="Role" placeholder="Semua role" /><x-ui.table-filter name="site_id" :options="$sites->pluck('name','id')" label="Site" placeholder="Semua site" /><x-ui.table-filter name="status" :options="['active'=>'Aktif','inactive'=>'Nonaktif']" label="Status" placeholder="Semua status" />
<x-slot:actions>
<x-ui.link variant="secondary" :href="url()->current()">Reset</x-ui.link>
</x-slot:actions>
</x-ui.table-toolbar>
</x-slot:filters>
</x-ui.page-header>
      @if (isset($errors) && $errors->any())
        <div class="mb-4 rounded-lg border border-rose-200 bg-rose-50 px-4 py-3 text-sm text-rose-700">
          {{ $errors->first() }}
        </div>
      @endif
      <!-- TABLE -->
      <div class="rounded-lg border bg-ivory-50 dark:bg-coal-900 dark:border-coal-800 shadow-none overflow-x-auto">
        <x-ui.table :paginator="$users" class="w-full text-sm min-w-[860px]">
          <thead class="bg-ivory-100 dark:bg-coal-800/60 text-coal-700 dark:text-coal-300">
            <tr>
              <th class="px-3 py-2 text-left">Nama</th>
              <th class="px-3 py-2 text-left">Email</th>
              <th class="px-3 py-2 text-left">Role / Site</th><th class="px-3 py-2 text-left">Status</th>
              <th class="px-3 py-2 text-left w-24">Aksi</th>
            </tr>
          </thead>
          <tbody>
            @forelse ($users as $user)
              <tr class="border-t dark:border-coal-800/70 hover:bg-ivory-100 dark:hover:bg-coal-800/50">
                <td class="px-3 py-2">{{ $user->name }}</td>
                <td class="px-3 py-2">
                  <span class="text-coal-700 dark:text-ivory-100"><x-ui.truncated-text :value="$user->email" /></span>
                </td>
                <td>{{ $user->role }}<div class="text-xs text-zinc-600">{{ $user->sites->pluck('code')->join(', ') ?: '—' }}</div></td><td class="px-3 py-2">
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
                <td class="ui-action-cell px-3 py-2">
                  <x-ui.table-actions>
                    @if($user->id !== auth()->id() && !$user->isSuperAdmin())<form action="{{ route('admin.users.active.toggle', $user) }}" method="POST" class="inline">
                      @csrf
                      @method('PATCH')
                      <x-ui.icon-button :action="$user->is_active ? 'deactivate' : 'activate'" :label="$user->is_active ? 'Nonaktifkan' : 'Aktifkan'" type="submit" />
                    </form>@endif
                    <x-ui.icon-button action="reset-password" label="Reset password {{ $user->name }}" data-reset-url="{{ route('admin.users.active.password', $user) }}" data-reset-user="{{ $user->name }}" />
                  </x-ui.table-actions>
                </td>
              </tr>
            @empty<x-ui.table-empty-state colspan="5" />@endforelse
          </tbody>
        </x-ui.table>
      </div>
      <!-- PAGINATION -->

    </div>
  </x-ui.page>
  @endsection