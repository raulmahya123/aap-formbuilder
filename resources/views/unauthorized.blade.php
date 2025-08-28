{{-- resources/views/unauthorized.blade.php --}}
@extends('layouts.app')

@section('title', '403 — Unauthorized')

@section('content')
<div
  x-data="{ dark:(localStorage.getItem('theme') ?? (window.matchMedia('(prefers-color-scheme: dark)').matches?'dark':'light'))==='dark' }"
  x-init="document.documentElement.classList.toggle('dark',dark)"
  :class="dark?'dark':''"
  class="bg-ivory-100 dark:bg-coal-900 min-h-screen flex items-center justify-center text-coal-800 dark:text-ivory-100"
>
  <div class="text-center max-w-lg px-6 py-12 rounded-2xl border bg-ivory-50 dark:bg-coal-900 dark:border-coal-800 shadow-soft">
    <h1 class="text-7xl font-bold text-maroon-700 dark:text-maroon-300">403</h1>
    <h2 class="mt-3 text-2xl font-serif tracking-tight">Akses Ditolak</h2>

    <p class="mt-3 text-sm text-coal-600 dark:text-coal-300">
      {{ $message ?? 'Anda tidak memiliki izin untuk mengakses halaman ini.' }}
    </p>

    <div class="mt-6 flex flex-col sm:flex-row items-center justify-center gap-3">
      <a href="{{ url()->previous() }}"
         class="px-5 py-2.5 rounded-lg bg-maroon-700 text-ivory-50 hover:bg-maroon-600 transition text-sm w-full sm:w-auto">
        ← Kembali
      </a>
      <a href="{{ route('dashboard') }}"
         class="px-5 py-2.5 rounded-lg border border-maroon-600 text-maroon-700 hover:bg-maroon-50/60
                dark:text-maroon-300 dark:border-maroon-900/40 dark:hover:bg-maroon-900/20 transition text-sm w-full sm:w-auto">
        Ke Dashboard
      </a>
    </div>
  </div>
</div>
@endsection
