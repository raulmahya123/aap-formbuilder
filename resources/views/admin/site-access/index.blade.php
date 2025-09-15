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
  // Controller menyediakan:
  // $accesses (Paginator)
  // $sites (id, code, name)
  // $emails (Collection email untuk dropdown)
  $siteId = $siteId ?? request('site_id');
  $email  = $email  ?? request('email');
@endphp

{{-- Flash message (opsional) --}}
@if(session('ok') || session('info') || session('error'))
  <div class="mb-4 rounded-lg border p-3
              @if(session('ok')) border-emerald-300 bg-emerald-50 dark:bg-emerald-900/20 @endif
              @if(session('info')) border-amber-300 bg-amber-50 dark:bg-amber-900/20 @endif
              @if(session('error')) border-rose-300 bg-rose-50 dark:bg-rose-900/20 @endif">
    <div class="text-sm">
      {{ session('ok') ?? session('info') ?? session('error') }}
    </div>
  </div>
@endif

{{-- Filter --}}
<form method="GET" class="mb-6 grid grid-cols-1 sm:grid-cols-3 gap-3">
  <div>
    <label class="block text-sm mb-1">Filter Site</label>
    <select name="site_id" class="w-full rounded-lg border px-3 py-2 bg-white dark:bg-coal-900">
      <option value="">— Semua Site —</option>
      @foreach($sites as $s)
        <option value="{{ $s->id }}" @selected($siteId == $s->id)>{{ $s->code }} — {{ $s->name }}</option>
      @endforeach
    </select>
  </div>

  <div>
    <label class="block text-sm mb-1">Filter Email</label>
    <select
      name="email"
      class="w-full rounded-lg border px-3 py-2 bg-white dark:bg-coal-900"
    >
      <option value="">— Semua Email —</option>
      @foreach($emails as $e)
        <option value="{{ $e }}" @selected($email === $e)>{{ $e }}</option>
      @endforeach
    </select>
  </div>

  <div class="flex items-end gap-2">
    <button class="px-3 py-2 rounded-lg border">Terapkan</button>
    <a href="{{ route('admin.site_access.index') }}" class="px-3 py-2 rounded-lg border">Reset</a>
  </div>
</form>

<div class="grid lg:grid-cols-2 gap-6">

  {{-- Form Tambah Akses (per-site & per-email) --}}
  <div class="rounded-2xl border p-4">
    <h2 class="font-semibold mb-3">Tambah Akses (Email ↔ Site)</h2>

    <form method="POST" action="{{ route('admin.site_access.store') }}" class="grid gap-3">
      @csrf

      <div>
        <label class="block text-sm mb-1">Site</label>
        <select name="site_id" class="w-full rounded-lg border px-3 py-2 bg-white dark:bg-coal-900" required>
          <option value="">— Pilih Site —</option>
          @foreach($sites as $s)
            <option value="{{ $s->id }}" @selected(old('site_id', $siteId) == $s->id)>{{ $s->code }} — {{ $s->name }}</option>
          @endforeach
        </select>
        @error('site_id') <div class="text-sm text-rose-600 mt-1">{{ $message }}</div> @enderror
      </div>

      <div>
        <label class="block text-sm mb-1">Email User</label>
        <select
          name="email"
          class="w-full rounded-lg border px-3 py-2 bg-white dark:bg-coal-900"
          required
        >
          <option value="">— Pilih Email —</option>
          @foreach($emails as $e)
            <option value="{{ $e }}" @selected(old('email', $email) === $e)>{{ $e }}</option>
          @endforeach
        </select>
        @error('email') <div class="text-sm text-rose-600 mt-1">{{ $message }}</div> @enderror
      </div>

      <div class="pt-2">
        <button class="px-4 py-2 rounded-lg border border-maroon-700 text-maroon-700 hover:bg-maroon-50
                       dark:border-maroon-600 dark:text-maroon-300 dark:hover:bg-coal-900/20">
          + Tambah Akses
        </button>
      </div>
    </form>
  </div>

  {{-- Info Panduan (opsional) --}}
  <div class="rounded-2xl border p-4">
    <h2 class="font-semibold mb-3">Panduan</h2>
    <ul class="list-disc pl-5 space-y-1 text-sm text-coal-700 dark:text-coal-200">
      <li>Pilih <strong>Site</strong> dan <strong>Email</strong> user, lalu klik <em>Tambah Akses</em>.</li>
      <li>Gunakan filter di atas untuk menyaring daftar berdasarkan Site/Email.</li>
      <li>Untuk mencabut akses, klik tombol <em>Hapus</em> pada baris yang diinginkan.</li>
    </ul>
  </div>

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
