@extends('layouts.app')

@section('content')
<div
  x-data="{ dark: (localStorage.getItem('theme') ?? (window.matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light')) === 'dark' }"
  x-init="document.documentElement.classList.toggle('dark', dark)"
  :class="dark ? 'dark' : ''"
  class="bg-ivory-100 dark:bg-coal-900 min-h-screen text-coal-800 dark:text-ivory-100"
>
  <div class="max-w-5xl mx-auto p-4 sm:p-6">
    <!-- HEADER -->
    <div class="flex items-start justify-between gap-2 mb-4 sm:mb-6">
      <h1 class="text-xl sm:text-2xl font-serif tracking-tight">
        Members — {{ $department->name }}
      </h1>
      <a href="{{ route('admin.departments.index') }}"
         class="text-sm underline text-maroon-700 hover:text-maroon-600 dark:text-maroon-300">
        ← Kembali
      </a>
    </div>

    <!-- FORM TAMBAH -->
    <div class="mb-6 rounded-xl border bg-ivory-50 dark:bg-coal-900 dark:border-coal-800 shadow-soft p-4 sm:p-5">
      <h2 class="font-medium mb-3">Tambah / Update Akses</h2>
      <form action="{{ route('admin.departments.members.store', $department) }}"
            method="post"
            class="grid gap-3 md:grid-cols-3">
        @csrf
        <div>
          <label class="block text-xs font-medium text-coal-600 dark:text-coal-300 mb-1">User</label>
          <select name="user_id"
                  class="w-full rounded-lg border bg-white dark:bg-coal-950 dark:border-coal-700 px-3 py-2 text-sm
                         focus:outline-none focus:ring-2 focus:ring-maroon-500/60 focus:border-maroon-500">
            @foreach($users as $u)
              <option value="{{ $u->id }}">{{ $u->name }} — {{ $u->email }}</option>
            @endforeach
          </select>
        </div>
        <div>
          <label class="block text-xs font-medium text-coal-600 dark:text-coal-300 mb-1">Role</label>
          <select name="dept_role"
                  class="w-full rounded-lg border bg-white dark:bg-coal-950 dark:border-coal-700 px-3 py-2 text-sm
                         focus:outline-none focus:ring-2 focus:ring-maroon-500/60 focus:border-maroon-500">
            <option value="member">member</option>
            <option value="dept_admin">dept_admin</option>
          </select>
        </div>
        <div class="flex items-end">
          <button
            class="w-full md:w-auto px-4 py-2 rounded-lg bg-maroon-700 text-ivory-50 hover:bg-maroon-600 transition text-sm">
            Simpan
          </button>
        </div>
      </form>
    </div>

    <!-- TABLE MEMBERS -->
    <div class="rounded-xl border bg-ivory-50 dark:bg-coal-900 dark:border-coal-800 shadow-soft overflow-hidden">
      <div class="overflow-x-auto nice-scroll">
        <table class="w-full text-sm min-w-[640px]">
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
                  <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs
                               bg-maroon-50 text-maroon-700 dark:bg-maroon-900/20 dark:text-maroon-300">
                    {{ $m->pivot->dept_role }}
                  </span>
                </td>
                <td class="p-3">
                  <form action="{{ route('admin.departments.members.destroy', [$department, $m]) }}"
                        method="post"
                        onsubmit="return confirm('Hapus akses user ini?')">
                    @csrf @method('DELETE')
                    <button
                      class="px-3 py-1.5 rounded-lg bg-maroon-700 text-ivory-50 hover:bg-maroon-600 transition text-xs">
                      Hapus Akses
                    </button>
                  </form>
                </td>
              </tr>
            @empty
              <tr>
                <td colspan="4" class="p-5 text-center text-coal-500 dark:text-coal-400 text-sm">
                  Belum ada member.
                </td>
              </tr>
            @endforelse
          </tbody>
        </table>
      </div>
    </div>
  </div>
</div>
@endsection
