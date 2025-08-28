@extends('layouts.app')

@section('content')
<div
  x-data="{ dark: (localStorage.getItem('theme') ?? (window.matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light')) === 'dark' }"
  x-init="document.documentElement.classList.toggle('dark', dark)"
  :class="dark ? 'dark' : ''"
  class="bg-ivory-100 dark:bg-coal-900 min-h-screen text-coal-800 dark:text-ivory-100"
>
  <div class="max-w-6xl mx-auto p-3 sm:p-4">
    <!-- HEADER -->
    <div class="flex items-start justify-between gap-2 mb-3 sm:mb-4">
      <div>
        <h1 class="text-xl sm:text-2xl font-serif tracking-tight">Departments</h1>
        <p class="text-xs sm:text-sm text-coal-500 dark:text-coal-300">Kelola daftar department dan anggotanya.</p>
      </div>
      @can('create', App\Models\Department::class)
        <a href="{{ route('admin.departments.create') }}"
           class="inline-flex items-center px-3 py-1.5 rounded-lg bg-maroon-700 text-ivory-50 hover:bg-maroon-600 text-sm transition">
          + Tambah
        </a>
      @endcan
    </div>

    <div class="rounded-xl border bg-ivory-50 dark:bg-coal-900 dark:border-coal-800 shadow-soft overflow-hidden">
      {{-- ===== MOBILE / TABLET: CARD LIST (≤ md) ===== --}}
      <div class="md:hidden divide-y dark:divide-coal-800">
        @forelse($departments as $d)
          <div class="p-3">
            <div class="flex items-start justify-between gap-2">
              <div>
                <div class="font-medium text-sm">{{ $d->name }}</div>
                <div class="text-[11px] text-coal-500 dark:text-coal-400">slug: {{ $d->slug }}</div>
              </div>
              <div class="flex flex-wrap gap-1.5 justify-end">
                @can('update', $d)
                  <a href="{{ route('admin.departments.edit',$d) }}"
                     class="px-2.5 py-1 rounded-full bg-maroon-700 text-ivory-50 hover:bg-maroon-600 text-[12px] transition">Edit</a>
                @endcan
                <a href="{{ route('admin.departments.members',$d) }}"
                   class="px-2.5 py-1 rounded-full bg-maroon-700 text-ivory-50 hover:bg-maroon-600 text-[12px] transition">Members</a>
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
            </div>
          </div>
        @empty
          <div class="p-5 text-center text-coal-500 dark:text-coal-400 text-sm">
            Belum ada department.
            @can('create', App\Models\Department::class)
              <a href="{{ route('admin.departments.create') }}" class="underline text-maroon-700 dark:text-maroon-300">Tambah sekarang</a>.
            @endcan
          </div>
        @endforelse
      </div>

      {{-- ===== DESKTOP: TABLE (≥ md) ===== --}}
      <div class="hidden md:block overflow-x-auto nice-scroll">
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
              <tr class="border-t dark:border-coal-800/70 hover:bg-ivory-100 dark:hover:bg-coal-800/50">
                <td class="p-3 font-medium text-[13px]">{{ $d->name }}</td>
                <td class="p-3 text-coal-500 dark:text-coal-400 text-[13px]">{{ $d->slug }}</td>
                <td class="p-3">
                  <div class="flex flex-wrap items-center gap-1.5">
                    @can('update', $d)
                      <a href="{{ route('admin.departments.edit',$d) }}"
                         class="px-2.5 py-1 rounded-full bg-maroon-700 text-ivory-50 hover:bg-maroon-600 text-[12px] transition"
                         title="Edit Department">Edit</a>
                    @endcan

                    <a href="{{ route('admin.departments.members',$d) }}"
                       class="px-2.5 py-1 rounded-full bg-maroon-700 text-ivory-50 hover:bg-maroon-600 text-[12px] transition"
                       title="Kelola Anggota">Members</a>

                    @can('delete', $d)
                      <form action="{{ route('admin.departments.destroy',$d) }}" method="post"
                            onsubmit="return confirm('Hapus department & relasi anggotanya?')">
                        @csrf @method('DELETE')
                        <button class="px-2.5 py-1 rounded-full bg-maroon-700 text-ivory-50 hover:bg-maroon-600 text-[12px] transition"
                                title="Hapus Department">Hapus</button>
                      </form>
                    @endcan
                  </div>
                </td>
              </tr>
            @empty
              <tr>
                <td colspan="3" class="p-6 text-center text-coal-500 dark:text-coal-400">
                  Belum ada department.
                  @can('create', App\Models\Department::class)
                    <a href="{{ route('admin.departments.create') }}" class="underline text-maroon-700 dark:text-maroon-300">Tambah sekarang</a>.
                  @endcan
                </td>
              </tr>
            @endforelse
          </tbody>
        </table>
      </div>
    </div>

    {{-- Pagination --}}
    @if($departments->hasPages())
      <div class="mt-3">
        {{ $departments->onEachSide(1)->links() }}
      </div>
    @endif
  </div>
</div>
@endsection
