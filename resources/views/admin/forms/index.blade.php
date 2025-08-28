@extends('layouts.app')

@section('content')
<div
  x-data="{ dark: (localStorage.getItem('theme') ?? (window.matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light')) === 'dark' }"
  x-init="document.documentElement.classList.toggle('dark', dark)"
  :class="dark ? 'dark' : ''"
  class="bg-ivory-100 dark:bg-coal-900 min-h-screen text-coal-800 dark:text-ivory-100"
>
  <div class="max-w-5xl mx-auto p-4 sm:p-6">
    <!-- HEADER -->
    <div class="flex items-center justify-between mb-4 sm:mb-6">
      <h1 class="text-xl sm:text-2xl font-serif tracking-tight">Form Tersedia</h1>

      {{-- Tombol tambah form --}}
      @if(Route::has('admin.forms.create'))
        @isset($department)
          {{-- Policy create(User $user, int $departmentId) --}}
          @can('create', [\App\Models\Form::class, $department->id])
            <a href="{{ route('admin.forms.create', ['department_id' => $department->id]) }}"
               class="px-3 py-1.5 rounded-lg bg-maroon-700 text-ivory-50 text-sm hover:bg-maroon-600 transition">
              + Tambah Form
            </a>
          @endcan
        @else
          {{-- Fallback: policy create(User $user) --}}
          @can('create', \App\Models\Form::class)
            <a href="{{ route('admin.forms.create') }}"
               class="px-3 py-1.5 rounded-lg bg-maroon-700 text-ivory-50 text-sm hover:bg-maroon-600 transition">
              + Tambah Form
            </a>
          @endcan
        @endisset
      @endif
    </div>

    <!-- LIST FORM -->
    <div class="space-y-3">
      @forelse($forms as $f)
        <div class="p-4 rounded-xl border bg-ivory-50 dark:bg-coal-900 dark:border-coal-800 shadow-soft hover:bg-ivory-100 dark:hover:bg-coal-800/50 transition">
          <div class="flex items-start justify-between gap-3">
            <a class="flex-1" href="{{ route('front.forms.show', $f->slug) }}">
              <div class="font-medium">{{ $f->title }}</div>
              <div class="text-sm text-coal-500 dark:text-coal-400">
                {{ strtoupper($f->type) }} — {{ $f->department->name ?? 'Tanpa Departemen' }}
              </div>
            </a>

            {{-- Tombol Builder hanya untuk form tipe builder --}}
            @if($f->type === 'builder')
              @can('update', $f)
                <a href="{{ route('admin.forms.builder', $f) }}"
                   class="text-xs px-2 py-1 rounded-lg border border-maroon-600 text-maroon-700 dark:text-maroon-300 hover:bg-maroon-50/60 dark:hover:bg-maroon-900/20 transition">
                  Builder
                </a>
              @endcan
            @endif
          </div>
        </div>
      @empty
        <div class="text-coal-500 dark:text-coal-400">Belum ada form.</div>
      @endforelse
    </div>
  </div>
</div>
@endsection
