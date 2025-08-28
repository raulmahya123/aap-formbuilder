@extends('layouts.app')

@section('content')
<div
  x-data="{ dark: (localStorage.getItem('theme') ?? (window.matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light')) === 'dark' }"
  x-init="document.documentElement.classList.toggle('dark', dark)"
  :class="dark ? 'dark' : ''"
  class="bg-ivory-100 dark:bg-coal-900 min-h-screen text-coal-800 dark:text-ivory-100"
>
  <div class="max-w-7xl mx-auto p-4 sm:p-6">
    <!-- HEADER -->
    <div class="flex flex-col sm:flex-row sm:items-start sm:justify-between gap-3 mb-4 sm:mb-6">
      <!-- kiri: judul + deskripsi -->
      <div>
        <h1 class="text-2xl md:text-3xl font-serif tracking-tight">Departments</h1>
        <p class="mt-1 text-[13px] sm:text-sm text-coal-600 dark:text-coal-300">
          Kelola daftar department dan anggotanya.
        </p>
      </div>

      <!-- kanan: tombol -->
      <div class="flex items-center gap-2">
        <form method="get" action="{{ route('admin.departments.index') }}" class="hidden sm:block">
          <input name="q" value="{{ $q ?? '' }}" placeholder="Cari…"
                 class="px-3 py-2 rounded-lg border bg-white dark:bg-coal-950 dark:border-coal-700 text-sm
                        focus:outline-none focus:ring-2 focus:ring-maroon-500/60 focus:border-maroon-500" />
        </form>
        @can('create', App\Models\Department::class)
          <a
            href="{{ route('admin.departments.create') }}"
            class="inline-flex items-center justify-center px-4 py-2 rounded-lg bg-maroon-700 text-ivory-50 hover:bg-maroon-600 transition text-sm"
          >
            + Tambah
          </a>
        @endcan
      </div>
    </div>

    <!-- CARD WRAPPER -->
    <div class="rounded-2xl border bg-ivory-50 dark:bg-coal-900 dark:border-coal-800 shadow-soft overflow-hidden">

      <!-- Mobile Cards (≤ sm) -->
      <div class="sm:hidden divide-y dark:divide-coal-800">
        @forelse($departments as $d)
          <div class="p-4">
            <div class="mb-1 font-medium">{{ $d->name }}</div>
            <div class="mb-3 text-xs text-coal-500 dark:text-coal-400">Slug: {{ $d->slug }}</div>
            <div class="flex flex-wrap gap-2">
              @can('update', $d)
                <a href="{{ route('admin.departments.edit',$d) }}"
                   class="px-3 py-1.5 rounded-lg bg-maroon-700 text-ivory-50 hover:bg-maroon-600 text-xs transition">
                  Edit
                </a>
              @endcan
              <a href="{{ route('admin.departments.members',$d) }}"
                 class="px-3 py-1.5 rounded-lg bg-maroon-700 text-ivory-50 hover:bg-maroon-600 text-xs transition">
                Members
              </a>
              @can('delete', $d)
                <form action="{{ route('admin.departments.destroy',$d) }}" method="post"
                      onsubmit="return confirm('Hapus department & relasi anggotanya?')">
                  @csrf @method('DELETE')
                  <button class="px-3 py-1.5 rounded-lg bg-maroon-700 text-ivory-50 hover:bg-maroon-600 text-xs transition">
                    Hapus
                  </button>
                </form>
              @endcan
            </div>
          </div>
        @empty
          <div class="p-6 text-center text-coal-500 dark:text-coal-400 text-sm">Belum ada department.</div>
        @endforelse
      </div>

      <!-- Desktop Table (≥ sm) -->
      <div class="hidden sm:block overflow-x-auto nice-scroll">
        <table class="w-full text-sm min-w-[640px]">
          <thead class="bg-ivory-100 dark:bg-coal-800/60 text-coal-700 dark:text-coal-300">
            <tr>
              <th class="text-left p-3">Nama</th>
              <th class="text-left p-3">Slug</th>
              <th class="text-left p-3 w-64">Aksi</th>
            </tr>
          </thead>
          <tbody>
            @forelse($departments as $d)
              <tr class="border-t dark:border-coal-800/80 hover:bg-ivory-100 dark:hover:bg-coal-800/50">
                <td class="p-3 font-medium text-[13px]">{{ $d->name }}</td>
                <td class="p-3 text-coal-500 dark:text-coal-400 text-[13px]">{{ $d->slug }}</td>
                <td class="p-3">
                  <div class="flex flex-wrap items-center gap-1.5">
                    @can('update', $d)
                      <a href="{{ route('admin.departments.edit',$d) }}"
                         class="px-2.5 py-1 rounded-full bg-maroon-700 text-ivory-50 hover:bg-maroon-600 text-[12px] transition">
                        Edit
                      </a>
                    @endcan
                    <a href="{{ route('admin.departments.members',$d) }}"
                       class="px-2.5 py-1 rounded-full bg-maroon-700 text-ivory-50 hover:bg-maroon-600 text-[12px] transition">
                      Members
                    </a>
                    @can('delete', $d)
                      <form action="{{ route('admin.departments.destroy',$d) }}" method="post"
                            onsubmit="return confirm('Hapus department & relasi anggotanya?')">
                        @csrf @method('DELETE')
                        <button class="px-2.5 py-1 rounded-full bg-maroon-700 text-ivory-50 hover:bg-maroon-600 text-[12px] transition">
                          Hapus
                        </button>
                      </form>
                    @endcan
                  </div>
                </td>
              </tr>
            @empty
              <tr>
                <td colspan="3" class="p-6 text-center text-coal-500 dark:text-coal-400">Belum ada department.</td>
              </tr>
            @endforelse
          </tbody>
        </table>
      </div>
    </div>

    <!-- TANPA pagination -->
  </div>
</div>
@endsection
