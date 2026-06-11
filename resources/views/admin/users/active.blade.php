  @extends('layouts.app')

  @section('content')
  <div
    x-data="{
      dark: (localStorage.getItem('theme') ?? (window.matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light')) === 'dark',
      resetUser: null,
      openReset(user) { this.resetUser = user },
      closeReset() { this.resetUser = null }
    }"
    x-init="document.documentElement.classList.toggle('dark', dark)"
    :class="dark ? 'dark' : ''"
    class="bg-ivory-100 dark:bg-coal-900 min-h-screen text-coal-800 dark:text-ivory-100"
  >
    <div class="max-w-5xl mx-auto p-4 sm:p-6">
      <!-- HEADER -->
      <div class="mb-4 sm:mb-6">
        <h1 class="text-2xl font-serif tracking-tight">Kelola Status User</h1>
        <p class="mt-1 text-[13px] sm:text-sm text-coal-600 dark:text-coal-300">Aktif/nonaktifkan akun pengguna dan reset password saat dibutuhkan.</p>
      </div>

      @if (isset($errors) && $errors->any())
        <div class="mb-4 rounded-lg border border-rose-200 bg-rose-50 px-4 py-3 text-sm text-rose-700">
          {{ $errors->first() }}
        </div>
      @endif

      <!-- TABLE -->
      <div class="rounded-xl border bg-ivory-50 dark:bg-coal-900 dark:border-coal-800 shadow-soft overflow-x-auto">
        <table class="w-full text-sm min-w-[860px]">
          <thead class="bg-ivory-100 dark:bg-coal-800/60 text-coal-700 dark:text-coal-300">
            <tr>
              <th class="px-3 py-2 text-left">Nama</th>
              <th class="px-3 py-2 text-left">Email</th>
              <th class="px-3 py-2 text-left">Status</th>
              <th class="px-3 py-2 text-left w-72">Aksi</th>
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
                  <div class="flex flex-wrap gap-2">
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

                    <button type="button"
                            @click="openReset({
                              name: @js($user->name),
                              email: @js($user->email),
                              action: @js(route('admin.users.active.password', $user))
                            })"
                            class="px-3 py-1.5 rounded-full border border-slate-300 text-[12px] font-medium text-coal-700 hover:bg-white dark:text-ivory-100 dark:border-coal-700 dark:hover:bg-coal-800 transition">
                      Reset Password
                    </button>
                  </div>
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

    <div x-cloak x-show="resetUser" class="fixed inset-0 z-[80] flex items-center justify-center bg-coal-900/45 px-4">
      <div class="w-full max-w-md rounded-xl bg-white p-5 shadow-xl dark:bg-coal-900 dark:border dark:border-coal-700" @click.outside="closeReset()">
        <div class="flex items-start justify-between gap-4">
          <div>
            <h2 class="text-lg font-semibold text-coal-900 dark:text-ivory-50">Reset Password</h2>
            <p class="mt-1 text-sm text-coal-500 dark:text-coal-300">
              <span x-text="resetUser?.name"></span>
              <span class="block text-xs" x-text="resetUser?.email"></span>
            </p>
          </div>
          <button type="button" @click="closeReset()" class="rounded-full px-2 py-1 text-coal-500 hover:bg-coal-100 dark:hover:bg-coal-800">x</button>
        </div>

        <form :action="resetUser?.action" method="POST" class="mt-5 space-y-4">
          @csrf
          @method('PATCH')

          <div>
            <label for="password" class="mb-1 block text-sm font-medium">Password baru</label>
            <input id="password" name="password" type="password" required autocomplete="new-password"
                   class="w-full rounded-lg border border-coal-200 px-3 py-2 text-coal-900 focus:border-maroon-500 focus:ring-maroon-500">
          </div>

          <div>
            <label for="password_confirmation" class="mb-1 block text-sm font-medium">Konfirmasi password</label>
            <input id="password_confirmation" name="password_confirmation" type="password" required autocomplete="new-password"
                   class="w-full rounded-lg border border-coal-200 px-3 py-2 text-coal-900 focus:border-maroon-500 focus:ring-maroon-500">
          </div>

          <div class="flex justify-end gap-2 pt-2">
            <button type="button" @click="closeReset()" class="rounded-lg border border-coal-200 px-4 py-2 text-sm font-semibold text-coal-700 hover:bg-coal-50">Batal</button>
            <button type="submit" class="rounded-lg bg-maroon-700 px-4 py-2 text-sm font-semibold text-white hover:bg-maroon-800">Simpan Password</button>
          </div>
        </form>
      </div>
    </div>
  </div>
  @endsection
