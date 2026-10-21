@extends('layouts.app')

@section('content')
<x-ui.page
  x-data="{ dark: (localStorage.getItem('theme') ?? (window.matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light')) === 'dark' }"
  x-init="document.documentElement.classList.toggle('dark', dark)"
  ::class="dark ? 'dark' : ''"
  class="min-h-screen text-coal-800 dark:text-ivory-100"
>
  <div class="max-w-5xl mx-auto">
    <!-- HEADER -->
    <x-ui.page-header>
  <div class="ui-page-header__copy"><x-ui.page-title>Members — {{ $department->name }}</x-ui.page-title>
<x-ui.page-description>Kelola department dan keanggotaan pengguna.</x-ui.page-description></div>
  <x-ui.page-actions><a href="{{ route('admin.departments.index') }}"
         class="text-sm underline text-maroon-700 hover:text-maroon-600 dark:text-maroon-300">
        ← Kembali
      </a></x-ui.page-actions>
</x-ui.page-header>

    <!-- FORM TAMBAH -->
    <div class="mb-6 rounded-lg border bg-ivory-50 dark:bg-coal-900 dark:border-coal-800 shadow-none p-4 sm:p-5">
      <x-ui.section-title class="font-medium mb-3">Tambah / Update Akses</x-ui.section-title>
      <form action="{{ route('admin.departments.members.store', $department) }}"
            method="post"
            class="grid gap-3 md:grid-cols-3">
        @csrf
        <div>
          <label class="block text-xs font-medium text-coal-600 dark:text-coal-300 mb-1">User</label>
          <select name="user_id"
                  class="w-full rounded-lg border bg-white dark:bg-coal-950 dark:border-coal-700 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-maroon-500/60 focus:border-maroon-500">
            @foreach($users as $u)
              <option value="{{ $u->id }}">{{ $u->name }} — {{ $u->email }}</option>
            @endforeach
          </select>
        </div>
        <div>
          <label class="block text-xs font-medium text-coal-600 dark:text-coal-300 mb-1">Role</label>
          <select name="dept_role"
                  class="w-full rounded-lg border bg-white dark:bg-coal-950 dark:border-coal-700 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-maroon-500/60 focus:border-maroon-500">
            <option value="member">member</option>
            <option value="dept_admin">dept_admin</option>
          </select>
        </div>
        <div class="flex items-end">
          <x-ui.button variant="primary"
            class="w-full md:w-auto px-4 py-2 rounded-lg text-ivory-50 transition text-sm">
            Simpan
          </x-ui.button>
        </div>
      </form>
    </div>

    <!-- TABLE MEMBERS -->
    <div class="rounded-lg border bg-ivory-50 dark:bg-coal-900 dark:border-coal-800 shadow-none overflow-hidden">
      <div class="overflow-x-auto nice-scroll">
        <x-ui.table class="w-full text-sm min-w-[640px]">
          <thead class="bg-ivory-100 dark:bg-coal-800/60 text-coal-700 dark:text-coal-300">
            <tr>
              <th class="text-left p-3">Nama</th>
              <th class="text-left p-3">Email</th>
              <th class="text-left p-3">Dept Role</th>
              <th class="text-left p-3 w-40">Aksi</th>
            </tr>
          </thead>
          <tbody>
            @forelse($members as $m)
              <tr class="border-t dark:border-coal-800/70 hover:bg-ivory-100 dark:hover:bg-coal-800/50">
                <td class="p-3 font-medium">{{ $m->name }}</td>
                <td class="p-3 text-coal-500 dark:text-coal-400">{{ $m->email }}</td>
                <td class="p-3">
                  <span class="inline-flex items-center px-2 py-1 rounded-full text-xs bg-maroon-50 text-maroon-700 dark:bg-maroon-900/20 dark:text-maroon-300">
                    {{ $m->pivot->dept_role }}
                  </span>
                </td>
                <td class="ui-action-cell p-3"><x-ui.table-actions>
                  <form action="{{ route('admin.departments.members.destroy', [$department, $m]) }}"
                        method="post"
                        data-confirm="Hapus akses user ini?">
                    @csrf @method('DELETE')
                    <x-ui.icon-button action="delete" label="Hapus" type="submit" />
                  </form>
                </x-ui.table-actions></td>
              </tr>
            @empty
              <tr>
                <td colspan="4" class="p-5 text-center text-coal-500 dark:text-coal-400 text-sm">
                  Belum ada member.
                </td>
              </tr>
            @endforelse
          </tbody>
        </x-ui.table>
      </div>
    </div>
  </div>
</x-ui.page>
@endsection
