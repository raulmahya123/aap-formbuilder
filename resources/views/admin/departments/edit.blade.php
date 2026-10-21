@extends('layouts.app')

@section('content')
<x-ui.page
  x-data="{ dark: (localStorage.getItem('theme') ?? (window.matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light')) === 'dark' }"
  x-init="document.documentElement.classList.toggle('dark', dark)"
  ::class="dark ? 'dark' : ''"
  class="min-h-screen text-coal-800 dark:text-ivory-100"
>
  <div class="max-w-7xl mx-auto">
    <!-- Header -->
    <x-ui.page-header>
  <div class="ui-page-header__copy"><x-ui.page-title>Edit Department</x-ui.page-title>
      <x-ui.page-description>Perbarui informasi nama department.</x-ui.page-description></div>
  <x-ui.page-actions>
<x-ui.button form="page-primary-form" variant="primary"
            class="w-full sm:w-auto px-4 py-2 rounded-lg text-ivory-50 transition"
          >
            Simpan Perubahan
          </x-ui.button>
</x-ui.page-actions>
</x-ui.page-header>

    <!-- Card -->
    <div class="max-w-xl rounded-lg border bg-ivory-50 dark:bg-coal-900 dark:border-coal-800 shadow-none">
      <form id="page-primary-form" method="post" action="{{ route('admin.departments.update', $department) }}" class="p-4 sm:p-6">
  @csrf
        @method('PUT')

        <!-- Nama -->
        <label class="block text-xs font-medium text-coal-600 dark:text-coal-300 mb-1">Nama</label>
        <input
          type="text"
          name="name"
          value="{{ old('name', $department->name) }}"
          required
          class="mt-1 w-full rounded-lg border bg-white px-3 py-2 text-coal-800 placeholder:text-coal-400 focus:outline-none focus:ring-2 focus:ring-maroon-500/60 focus:border-maroon-500 dark:bg-coal-950 dark:text-ivory-100 dark:border-coal-700"
          placeholder="Contoh: Human Capital"
        />
        @error('name')
          <p class="mt-1 text-xs text-maroon-600 dark:text-maroon-300">{{ $message }}</p>
        @enderror

        <!-- Actions -->
        <div class="mt-5 flex flex-col sm:flex-row gap-2">


          <x-ui.link variant="secondary"
            href="{{ route('admin.departments.index') }}"
            class="w-full sm:w-auto px-4 py-2 rounded-lg border text-center dark:border-coal-700 dark:hover:bg-coal-800 transition"
          >
            Batal
          </x-ui.link>
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
</x-ui.page>
@endsection
