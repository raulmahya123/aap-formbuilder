@extends('layouts.app')

@section('title', 'Akun Saya')

@section('content')
<div class="mx-auto max-w-5xl space-y-5">
  <div>
    <p class="text-sm font-medium text-maroon-700">Pengaturan akun</p>
    <h1 class="mt-1 text-3xl font-semibold text-coal-900">Akun Saya</h1>
    <p class="mt-1 text-sm text-coal-500">Kelola foto, identitas, email, dan keamanan akun Anda.</p>
  </div>

  @php($profileSites = $user->isSuperAdmin() ? collect() : $user->sites()->orderBy('code')->get(['sites.id', 'sites.code', 'sites.name']))
  <section class="overflow-hidden rounded-2xl border border-coal-200 bg-white shadow-sm">
    <div class="bg-gradient-to-r from-maroon-700 to-maroon-500 px-6 py-7 text-white">
      <div class="flex flex-wrap items-center gap-5">
        @if($user->avatar_path)
          <img src="{{ asset('storage/'.$user->avatar_path) }}" alt="Foto {{ $user->name }}" class="h-24 w-24 rounded-3xl object-cover ring-4 ring-white/30">
        @else
          <div class="flex h-24 w-24 items-center justify-center rounded-3xl bg-white/20 text-3xl font-semibold ring-4 ring-white/30">{{ strtoupper(substr($user->name, 0, 2)) }}</div>
        @endif
        <div class="min-w-0">
          <h2 class="truncate text-2xl font-semibold">{{ $user->name }}</h2>
          <p class="mt-1 truncate text-sm text-white/80">{{ $user->email }}</p>
          <span class="mt-3 inline-flex rounded-full bg-white/15 px-3 py-1 text-xs font-medium">{{ $user->isSuperAdmin() ? 'Head Office / Super Admin' : ucfirst(str_replace('_', ' ', $user->role ?? 'User')) }}</span>
        </div>
      </div>
    </div>
    <div class="grid gap-4 px-6 py-5 sm:grid-cols-2 lg:grid-cols-4">
      <div><p class="text-xs font-medium uppercase tracking-wide text-coal-400">NIK</p><p class="mt-1 font-semibold text-coal-800">{{ $user->nik ?: 'Belum diisi' }}</p></div>
      <div><p class="text-xs font-medium uppercase tracking-wide text-coal-400">Tanggal lahir</p><p class="mt-1 font-semibold text-coal-800">{{ $user->birth_date?->format('d M Y') ?: 'Belum diisi' }}</p></div>
      <div><p class="text-xs font-medium uppercase tracking-wide text-coal-400">Telepon</p><p class="mt-1 font-semibold text-coal-800">{{ $user->phone ?: 'Belum diisi' }}</p></div>
      <div><p class="text-xs font-medium uppercase tracking-wide text-coal-400">Site</p><p class="mt-1 font-semibold text-coal-800">{{ $user->isSuperAdmin() ? 'Semua site' : ($profileSites->pluck('code')->join(', ') ?: 'Belum ditugaskan') }}</p></div>
    </div>
  </section>

  <div class="grid gap-5 lg:grid-cols-[minmax(0,1fr)_minmax(0,1fr)]">
    <section class="rounded-2xl border border-coal-200 bg-white p-5 shadow-sm lg:col-span-2">
      @include('profile.partials.update-profile-information-form')
    </section>
    <section class="rounded-2xl border border-coal-200 bg-white p-5 shadow-sm">
      @include('profile.partials.update-password-form')
    </section>
    <section class="rounded-2xl border border-rose-200 bg-white p-5 shadow-sm">
      @include('profile.partials.delete-user-form')
    </section>
  </div>
</div>
@endsection
