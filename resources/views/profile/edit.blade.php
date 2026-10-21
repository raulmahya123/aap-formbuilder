@extends('layouts.app')

@section('title', 'Akun Saya')

@section('content')
<div class="mx-auto max-w-5xl space-y-5">
  <div>
    <p class="text-sm font-medium text-maroon-700">Pengaturan akun</p>
    <h1 class="mt-1 text-3xl font-semibold text-coal-900">Akun Saya</h1>
    <p class="mt-1 text-sm text-coal-500">Kelola foto, identitas, email, dan keamanan akun Anda.</p>
  </div>

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
