@extends('layouts.app')

@section('content')
<x-ui.page
  x-data="{ dark: (localStorage.getItem('theme') ?? (window.matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light')) === 'dark' }"
  x-init="document.documentElement.classList.toggle('dark', dark)"
  ::class="dark ? 'dark' : ''"
  class="min-h-screen text-coal-800 dark:text-ivory-100"
>
  <div class="max-w-6xl mx-auto">
    <!-- HEADER -->
    <x-ui.page-header>
  <div class="ui-page-header__copy"><div>
        <x-ui.page-title>Departments</x-ui.page-title>
        <x-ui.page-description>Kelola daftar department dan anggotanya.</x-ui.page-description>
      </div></div>
  <x-ui.page-actions>@can('create', App\Models\Department::class)
        <x-ui.link icon="plus" variant="primary" href="{{ route('admin.departments.create') }}"
           class="inline-flex items-center px-3 py-2 rounded-lg text-ivory-50 text-sm transition">Tambah Department
        </x-ui.link>
      @endcan</x-ui.page-actions>
</x-ui.page-header>

    <div class="rounded-lg border bg-ivory-50 dark:bg-coal-900 dark:border-coal-800 shadow-none overflow-hidden">
      {{-- ===== MOBILE / TABLET: CARD LIST (≤ md) ===== --}}
      <div class="md:hidden divide-y dark:divide-coal-800">
        @forelse($departments as $d)
          <div class="p-3">
            <div class="flex items-start justify-between gap-2">
              <div>
                <div class="font-medium text-sm">{{ $d->name }}</div>
                <div class="text-[11px] text-coal-500 dark:text-coal-400">slug: {{ $d->slug }}</div>
              </div>
              <div class="flex flex-nowrap items-center gap-2 justify-end">
                <x-ui.icon-button ability="update" :subject="$d" action="edit" label="Edit Department" href="{{ route('admin.departments.edit',$d) }}" />
                <x-ui.icon-button ability="update" :subject="$d" action="members" label="Kelola Anggota" href="{{ route('admin.departments.members',$d) }}" />
                @can('delete', $d)
                  <form action="{{ route('admin.departments.destroy',$d) }}" method="post"
                        data-confirm="Hapus department & relasi anggotanya?">
                    @csrf @method('DELETE')
                    <x-ui.icon-button action="delete" label="Hapus Department" type="submit" />
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
        <x-ui.table class="w-full text-sm min-w-[640px]">
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
                <td class="ui-action-cell p-3"><x-ui.table-actions>
                  <div class="flex flex-nowrap items-center gap-2">
                    @can('update', $d)
                      <x-ui.icon-button action="edit" label="Edit Department" href="{{ route('admin.departments.edit',$d) }}" />
                    @endcan

                    <x-ui.icon-button action="members" label="Kelola Anggota" ability="update" :subject="$d" href="{{ route('admin.departments.members',$d) }}" />

                    @can('delete', $d)
                      <form action="{{ route('admin.departments.destroy',$d) }}" method="post"
                            data-confirm="Hapus department & relasi anggotanya?">
                        @csrf @method('DELETE')
                        <x-ui.icon-button action="delete" label="Hapus Department" type="submit" />
                      </form>
                    @endcan
                  </div>
                </x-ui.table-actions></td>
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
        </x-ui.table>
      </div>
    </div>

    {{-- Pagination --}}
    @if($departments->hasPages())
      <div class="mt-3">
        {{ $departments->onEachSide(1)->links() }}
      </div>
    @endif
  </div>
</x-ui.page>
@endsection
