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
          @can('create', [\App\Models\Form::class, $department->id])
            <a href="{{ route('admin.forms.create', ['department_id' => $department->id]) }}"
               class="px-3 py-1.5 rounded-lg bg-maroon-700 text-ivory-50 text-sm hover:bg-maroon-600 transition">
              + Tambah Form
            </a>
          @endcan
        @else
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
        @php
          $isFileType = $f->type === 'pdf';
          $typeLabel  = $isFileType ? 'File (PDF/Word/Excel)' : 'Builder';
          $ext        = null;
          if ($isFileType && $f->pdf_path) {
            $ext = strtolower(pathinfo($f->pdf_path, PATHINFO_EXTENSION));
          }
        @endphp

        <div class="p-4 rounded-xl border bg-ivory-50 dark:bg-coal-900 dark:border-coal-800 shadow-soft hover:bg-ivory-100 dark:hover:bg-coal-800/50 transition">
          <div class="flex items-start justify-between gap-3">
            <a class="flex-1" href="{{ route('front.forms.show', $f->slug) }}">
              <div class="font-medium flex items-center gap-2">
                {{ $f->title }}

                {{-- Status aktif --}}
                @if($f->is_active)
                  <span class="text-[10px] px-2 py-0.5 rounded-full bg-emerald-100 text-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-300">Aktif</span>
                @else
                  <span class="text-[10px] px-2 py-0.5 rounded-full bg-slate-100 text-slate-600 dark:bg-slate-800 dark:text-slate-300">Nonaktif</span>
                @endif
              </div>

              <div class="text-sm text-coal-500 dark:text-coal-400 mt-0.5 flex flex-wrap items-center gap-x-2 gap-y-1">
                <span>{{ $typeLabel }}</span>
                <span>— {{ $f->department->name ?? 'Tanpa Departemen' }}</span>

                {{-- Kalau tipe file & ada file, tampilkan ekstensi + link --}}
                @if($isFileType && $f->pdf_path)
                  <span>•</span>
                  <span class="uppercase">{{ $ext }}</span>
                  <span>•</span>
                  <a class="underline hover:no-underline"
                     target="_blank"
                     href="{{ Storage::disk('public')->url($f->pdf_path) }}">
                    Lihat file
                  </a>
                @endif
              </div>
            </a>

            {{-- ACTIONS --}}
            <div class="flex items-center gap-2 shrink-0">
              {{-- Builder (hanya tipe builder) --}}
              @if($f->type === 'builder')
                @can('update', $f)
                  @if(Route::has('admin.forms.builder'))
                    <a href="{{ route('admin.forms.builder', $f) }}"
                       class="text-xs px-2 py-1 rounded-lg border border-maroon-600 text-maroon-700 dark:text-maroon-300 hover:bg-maroon-50/60 dark:hover:bg-maroon-900/20 transition">
                      Builder
                    </a>
                  @endif
                @endcan
              @endif

              {{-- Edit (opsional, kalau ada route dan punya izin) --}}
              @can('update', $f)
                @if(Route::has('admin.forms.edit'))
                  <a href="{{ route('admin.forms.edit', $f) }}"
                     class="text-xs px-2 py-1 rounded-lg border border-slate-300 text-slate-700 dark:text-slate-300 hover:bg-slate-100/60 dark:hover:bg-coal-800/60 transition">
                    Edit
                  </a>
                @endif
              @endcan

              {{-- Delete --}}
              @can('delete', $f)
                @if(Route::has('admin.forms.destroy'))
                  <form method="POST"
                        action="{{ route('admin.forms.destroy', $f) }}"
                        onsubmit="return confirm('Yakin ingin menghapus form & datanya? Tindakan ini tidak bisa dibatalkan.')">
                    @csrf
                    @method('DELETE')
                    <button type="submit"
                            class="text-xs px-2 py-1 rounded-lg border border-rose-600 text-rose-700 hover:bg-rose-50
                                   dark:border-rose-500 dark:text-rose-300 dark:hover:bg-rose-900/20 transition">
                      Delete
                    </button>
                  </form>
                @endif
              @endcan
            </div>
          </div>
        </div>
      @empty
        <div class="text-coal-500 dark:text-coal-400">Belum ada form.</div>
      @endforelse
    </div>
  </div>
</div>
@endsection
