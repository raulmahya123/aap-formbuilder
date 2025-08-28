@extends('layouts.app')

@section('content')
<div
  x-data="{ dark: (localStorage.getItem('theme') ?? (window.matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light')) === 'dark' }"
  x-init="document.documentElement.classList.toggle('dark', dark)"
  :class="dark ? 'dark' : ''"
  class="bg-ivory-100 dark:bg-coal-900 min-h-screen text-coal-800 dark:text-ivory-100"
>
  <div class="max-w-7xl mx-auto p-4 sm:p-6">
    <!-- Header -->
    <div class="mb-4 sm:mb-6">
      <h1 class="text-2xl md:text-3xl font-serif tracking-tight">Edit Department</h1>
      <p class="text-sm text-coal-500 dark:text-coal-300">Perbarui informasi nama department.</p>
    </div>

    <!-- Card -->
    <div class="max-w-xl rounded-2xl border bg-ivory-50 dark:bg-coal-900 dark:border-coal-800 shadow-soft">
      <form method="post" action="{{ route('admin.departments.update', $department) }}" class="p-4 sm:p-6">
        @csrf
        @method('PUT')

        <!-- Nama -->
        <label class="block text-xs font-medium text-coal-600 dark:text-coal-300 mb-1">Nama</label>
        <input
          type="text"
          name="name"
          value="{{ old('name', $department->name) }}"
          required
          class="mt-1 w-full rounded-lg border bg-white px-3 py-2 text-coal-800 placeholder:text-coal-400
                 focus:outline-none focus:ring-2 focus:ring-maroon-500/60 focus:border-maroon-500
                 dark:bg-coal-950 dark:text-ivory-100 dark:border-coal-700"
          placeholder="Contoh: Human Capital"
        />
        @error('name')
          <p class="mt-1 text-xs text-maroon-600 dark:text-maroon-300">{{ $message }}</p>
        @enderror

        <!-- Actions -->
        <div class="mt-5 flex flex-col sm:flex-row gap-2">
          <button
            class="w-full sm:w-auto px-4 py-2 rounded-lg bg-maroon-700 text-ivory-50 hover:bg-maroon-600 transition"
          >
            Simpan Perubahan
          </button>

          <a
            href="{{ route('admin.departments.index') }}"
            class="w-full sm:w-auto px-4 py-2 rounded-lg border text-center
                   hover:bg-ivory-50 dark:border-coal-700 dark:hover:bg-coal-800 transition"
          >
            Batal
          </a>
        </div>
      </form>
    </div>

    <!-- Optional: link balik kecil -->
    <div class="mt-3">
      <a href="{{ route('admin.departments.index') }}"
         class="text-sm text-maroon-700 hover:underline dark:text-maroon-300">
        ← Kembali ke daftar department
      </a>
    </div>
  </div>
</div>
@endsection
