@extends('layouts.app')

@section('title', 'Kelola Akses Site')

@section('breadcrumbs')
  <h1 class="text-lg font-semibold">Kelola Akses Site</h1>
@endsection

@section('actions')
  @if(Route::has('admin.sites.index'))
    <a href="{{ route('admin.sites.index') }}" class="px-3 py-2 rounded-lg border text-sm">
      📍 Kelola Sites
    </a>
  @endif
@endsection

@section('content')
@php
  // variabel yang disediakan controller:
  // $accesses (Paginator), $users (id, name, email), $sites (id, code, name)
@endphp

{{-- Filter --}}
<form method="GET" class="mb-6 grid grid-cols-1 sm:grid-cols-3 gap-3">
  <div>
    <label class="block text-sm mb-1">Filter User</label>
    <select name="user_id" class="w-full rounded-lg border px-3 py-2 bg-white dark:bg-coal-900">
      <option value="">— Semua User —</option>
      @foreach($users as $u)
        <option value="{{ $u->id }}" @selected(request('user_id') == $u->id)>{{ $u->name }} {{ $u->email ? '— '.$u->email : '' }}</option>
      @endforeach
    </select>
  </div>
  <div>
    <label class="block text-sm mb-1">Filter Site</label>
    <select name="site_id" class="w-full rounded-lg border px-3 py-2 bg-white dark:bg-coal-900">
      <option value="">— Semua Site —</option>
      @foreach($sites as $s)
        <option value="{{ $s->id }}" @selected(request('site_id') == $s->id)>{{ $s->code }} — {{ $s->name }}</option>
      @endforeach
    </select>
  </div>
  <div class="flex items-end gap-2">
    <button class="px-3 py-2 rounded-lg border">Terapkan</button>
    <a href="{{ route('admin.site_access.index') }}" class="px-3 py-2 rounded-lg border">Reset</a>
  </div>
</form>

<div class="grid lg:grid-cols-2 gap-6">

  {{-- Form Tambah Akses --}}
  <div class="rounded-2xl border p-4">
    <h2 class="font-semibold mb-3">Tambah Akses (User ↔ Site)</h2>

    <form method="POST" action="{{ route('admin.site_access.store') }}" class="grid gap-3">
      @csrf

      <div>
        <label class="block text-sm mb-1">Pilih User</label>
        <select name="user_id" class="w-full rounded-lg border px-3 py-2 bg-white dark:bg-coal-900" required>
          <option value="">— Pilih User —</option>
          @foreach($users as $u)
            <option value="{{ $u->id }}" @selected(old('user_id') == $u->id)>{{ $u->name }} {{ $u->email ? '— '.$u->email : '' }}</option>
          @endforeach
        </select>
        @error('user_id') <div class="text-sm text-rose-600 mt-1">{{ $message }}</div> @enderror
      </div>

      <div>
        <label class="block text-sm mb-1">Pilih Site</label>
        <select name="site_id" class="w-full rounded-lg border px-3 py-2 bg-white dark:bg-coal-900" required>
          <option value="">— Pilih Site —</option>
          @foreach($sites as $s)
            <option value="{{ $s->id }}" @selected(old('site_id') == $s->id)>{{ $s->code }} — {{ $s->name }}</option>
          @endforeach
        </select>
        @error('site_id') <div class="text-sm text-rose-600 mt-1">{{ $message }}</div> @enderror
      </div>

      <div class="pt-2">
        <button class="px-4 py-2 rounded-lg border border-maroon-700 text-maroon-700 hover:bg-maroon-50
                       dark:border-maroon-600 dark:text-maroon-300 dark:hover:bg-maroon-900/20">
          + Tambah Akses
        </button>
      </div>
    </form>
  </div>

  {{-- (Opsional) Form Bulk Attach: 1 user -> banyak site --}}
  @if(Route::has('admin.site_access.bulk'))
  <div class="rounded-2xl border p-4">
    <h2 class="font-semibold mb-3">Tambah Akses Massal (User → Banyak Site)</h2>

    <form method="POST" action="{{ route('admin.site_access.bulk') }}" class="grid gap-3">
      @csrf

      <div>
        <label class="block text-sm mb-1">User</label>
        <select name="user_id" class="w-full rounded-lg border px-3 py-2 bg-white dark:bg-coal-900" required>
          <option value="">— Pilih User —</option>
          @foreach($users as $u)
            <option value="{{ $u->id }}">{{ $u->name }} {{ $u->email ? '— '.$u->email : '' }}</option>
          @endforeach
        </select>
      </div>

      <div>
        <label class="block text-sm mb-1">Sites</label>
        <select name="site_ids[]" multiple size="8"
                class="w-full rounded-lg border px-3 py-2 bg-white dark:bg-coal-900 nice-scroll" required>
          @foreach($sites as $s)
            <option value="{{ $s->id }}">{{ $s->code }} — {{ $s->name }}</option>
          @endforeach
        </select>
        <p class="text-xs text-coal-500 mt-1">Tahan Ctrl / Cmd untuk pilih banyak.</p>
      </div>

      <div class="pt-2">
        <button class="px-4 py-2 rounded-lg border border-maroon-700 text-maroon-700 hover:bg-maroon-50
                       dark:border-maroon-600 dark:text-maroon-300 dark:hover:bg-maroon-900/20">
          + Tambah Akses Massal
        </button>
      </div>
    </form>
  </div>
  @endif

</div>

{{-- Daftar Akses --}}
<div class="mt-8 rounded-2xl border overflow-hidden">
  <div class="px-4 py-3 border-b">
    <h2 class="font-semibold">Daftar Akses User ↔ Site</h2>
  </div>

  <div class="overflow-x-auto">
    <table class="min-w-full text-sm">
      <thead class="bg-ivory-100 dark:bg-coal-900">
        <tr>
          <th class="text-left px-4 py-2">#</th>
          <th class="text-left px-4 py-2">User</th>
          <th class="text-left px-4 py-2">Site</th>
          <th class="text-left px-4 py-2">Dibuat</th>
          <th class="text-left px-4 py-2"></th>
        </tr>
      </thead>
      <tbody>
        @forelse($accesses as $i => $a)
          <tr class="border-t">
            <td class="px-4 py-2 align-top">{{ ($accesses->currentPage()-1)*$accesses->perPage() + $i + 1 }}</td>
            <td class="px-4 py-2 align-top">
              <div class="font-medium">{{ $a->user->name ?? '-' }}</div>
              <div class="text-xs text-coal-500">{{ $a->user->email ?? '' }}</div>
            </td>
            <td class="px-4 py-2 align-top">
              <div class="font-medium">{{ $a->site->code ?? '-' }}</div>
              <div class="text-xs text-coal-500">{{ $a->site->name ?? '' }}</div>
            </td>
            <td class="px-4 py-2 align-top text-coal-600 dark:text-coal-300">
              {{ $a->created_at?->format('Y-m-d H:i') }}
            </td>
            <td class="px-4 py-2 align-top">
              <form method="POST" action="{{ route('admin.site_access.destroy', $a->id) }}"
                    onsubmit="return confirm('Hapus akses ini?')">
                @csrf @method('DELETE')
                <button class="px-3 py-1.5 rounded-lg border hover:bg-ivory-100 dark:hover:bg-coal-900">
                  Hapus
                </button>
              </form>
            </td>
          </tr>
        @empty
          <tr>
            <td colspan="5" class="px-4 py-6 text-center text-coal-500">Belum ada data akses.</td>
          </tr>
        @endforelse
      </tbody>
    </table>
  </div>

  @if($accesses->hasPages())
    <div class="px-4 py-3 border-t">
      {{ $accesses->withQueryString()->links() }}
    </div>
  @endif
</div>
@endsection
