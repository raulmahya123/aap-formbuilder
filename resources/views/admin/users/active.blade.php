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
      <div class="mb-4 sm:mb-6">
        <h1 class="text-2xl font-serif tracking-tight">Kelola Status User</h1>
        <p class="mt-1 text-[13px] sm:text-sm text-coal-600 dark:text-coal-300">Aktif/nonaktifkan akun pengguna.</p>
      </div>

      <!-- TABLE -->
      <div class="rounded-xl border bg-ivory-50 dark:bg-coal-900 dark:border-coal-800 shadow-soft overflow-x-auto">
        <table class="w-full text-sm min-w-[720px]">
          <thead class="bg-ivory-100 dark:bg-coal-800/60 text-coal-700 dark:text-coal-300">
            <tr>
              <th class="px-3 py-2 text-left">Nama</th>
              <th class="px-3 py-2 text-left">Email</th>
              <th class="px-3 py-2 text-left">Status</th>
              <th class="px-3 py-2 text-left w-44">Aksi</th>
            </tr>
          </thead>
          <tbody>
            @foreach ($users as $user)
              <tr class="border-t dark:border-coal-800/70 hover:bg-ivory-100 dark:hover:bg-coal-800/50">
                <td class="px-3 py-2">{{ $user->name }}</td>
                <td class="px-3 py-2">
                  <span class="text-coal-700 dark:text-ivory-100">{{ $user->email }}</span>
                </td>
                <td class="px-3 py-2">
                  @if ($user->is_active)
                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-emerald-100 text-emerald-800">
                      Aktif
                    </span>
                  @else
                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-rose-100 text-rose-800">
                      Nonaktif
                    </span>
                  @endif
                </td>
                <td class="px-3 py-2">
                  <form action="{{ route('admin.users.active.toggle', $user) }}" method="POST" class="inline">
                    @csrf
                    @method('PATCH')
                    <button type="submit"
                            class="px-3 py-1.5 rounded-full text-[12px]
                                  {{ $user->is_active
                                        ? 'border border-maroon-600 text-maroon-700 hover:bg-maroon-50/60 dark:text-maroon-300 dark:hover:bg-maroon-900/20'
                                        : 'bg-maroon-700 text-ivory-50 hover:bg-maroon-600' }}
                                  transition">
                      {{ $user->is_active ? 'Nonaktifkan' : 'Aktifkan' }}
                    </button>
                  </form>
                </td>
              </tr>
            @endforeach
          </tbody>
        </table>
      </div>

      <!-- PAGINATION -->
      <div class="mt-4">
        {{ $users->links() }}
      </div>
    </div>
  </div>
  @endsection
