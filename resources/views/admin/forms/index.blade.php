{{-- resources/views/admin/forms/index.blade.php --}}
@extends('layouts.app')

@section('content')
<div
  x-data="{ dark: (localStorage.getItem('theme') ?? (window.matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light')) === 'dark' }"
  x-init="document.documentElement.classList.toggle('dark', dark)"
  :class="dark ? 'dark' : ''"
  class="bg-ivory-100 dark:bg-coal-900 min-h-screen text-coal-800 dark:text-ivory-100">
  <div class="max-w-6xl mx-auto p-4 sm:p-6">

    {{-- ====== HEADER ====== --}}
    <div class="flex items-center justify-between mb-4 sm:mb-6">
      <h1 class="text-xl sm:text-2xl font-serif tracking-tight">Form Tersedia</h1>

      @if(Route::has('admin.forms.create'))
        @can('create', \App\Models\Form::class)
          <a href="{{ route('admin.forms.create') }}"
             class="px-3 py-1.5 rounded-lg bg-maroon-700 text-ivory-50 text-sm hover:bg-maroon-600 transition">
            + Tambah Form
          </a>
        @endcan
      @endif
    </div>

    {{-- ===================================================== --}}
    {{--  GRID DEPARTEMEN (KARTU) + TOMBOL SOP/IK/FORM        --}}
    {{--  Tanpa kartu "ALL", semua dept selalu muncul          --}}
    {{-- ===================================================== --}}
    @isset($departments)
      @php
        $activeDept = request('department_id') ? (string) request('department_id') : null;
        $activeDoc  = strtoupper(request('doc_type', ''));
        $pp         = (int) request('per_page', 10);

        $makeDept = function ($deptId, ?string $doc) use ($pp) {
          return route('admin.forms.index', array_filter([
            'department_id' => $deptId,
            'doc_type'      => $doc ?: null,
            'per_page'      => $pp,
          ]));
        };

        // warna kartu: ambil dari $department->color jika ada, default mirip contoh
        $colorOf = function($d) {
          return $d->color ?? '#e61caf';
        };
      @endphp

      <h2 class="text-sm font-semibold text-slate-600 dark:text-slate-300 mb-3">Semua Departemen</h2>

      <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-3 mb-6">
        @foreach($departments as $d)
          @php
            $isActive = $activeDept === (string) $d->id;
            $hex      = $colorOf($d);
          @endphp

          <div class="p-4 rounded-2xl border bg-white dark:bg-coal-900 border-slate-200/70 dark:border-coal-700/70 shadow-sm hover:shadow-md transition">
            <div class="flex items-start justify-between">
              <div class="flex items-center gap-3">
                {{-- Icon bulat berwarna --}}
                <div class="h-10 w-10 rounded-xl flex items-center justify-center"
                     style="background: {{ $hex }};">
                  <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-white" fill="currentColor" viewBox="0 0 20 20">
                    <path d="M3 3a1 1 0 0 1 1 1v11h12a1 1 0 1 1 0 2H3a1 1 0 0 1-1-1V4a1 1 0 0 1 1-1Zm5 4a1 1 0 0 1 1 1v6H7V8a1 1 0 0 1 1-1Zm6-3a1 1 0 0 1 1 1v9h-2V5a1 1 0 0 1 1-1ZM9 10a1 1 0 0 1 1-1h2v5h-2v-4Z"/>
                  </svg>
                </div>
                <div>
                  <div class="text-xs font-medium text-slate-400">{{ $hex }}</div>
                  <div class="text-lg font-semibold text-slate-900 dark:text-ivory-100 -mt-0.5">
                    {{ $d->name }}
                  </div>
                  <div class="text-sm text-slate-500">Klik tombol untuk membuka daftar</div>
                </div>
              </div>
            </div>

            {{-- Tombol selalu tampil, walau belum ada datanya --}}
            <div class="mt-3 flex flex-wrap items-center gap-2">
              <a href="{{ $makeDept($d->id, null) }}"
                 class="text-xs px-2.5 py-1.5 rounded-lg border border-slate-300 dark:border-coal-700 hover:bg-slate-100/70 dark:hover:bg-coal-800/60
                        {{ $isActive && $activeDoc==='' ? 'ring-2 ring-offset-1 ring-slate-300 dark:ring-slate-500' : '' }}">
                Semua Dokumen
              </a>
              <a href="{{ $makeDept($d->id, 'SOP') }}"
                 class="text-xs px-2.5 py-1.5 rounded-lg bg-blue-700 text-white hover:bg-blue-600
                        {{ $isActive && $activeDoc==='SOP' ? 'ring-2 ring-offset-1 ring-blue-300' : '' }}">
                SOP
              </a>
              <a href="{{ $makeDept($d->id, 'IK') }}"
                 class="text-xs px-2.5 py-1.5 rounded-lg bg-amber-600 text-white hover:bg-amber-500
                        {{ $isActive && $activeDoc==='IK' ? 'ring-2 ring-offset-1 ring-amber-300' : '' }}">
                IK
              </a>
              <a href="{{ $makeDept($d->id, 'FORM') }}"
                 class="text-xs px-2.5 py-1.5 rounded-lg bg-slate-800 text-white hover:bg-slate-700
                        {{ $isActive && $activeDoc==='FORM' ? 'ring-2 ring-offset-1 ring-slate-300' : '' }}">
                FORM
              </a>
            </div>
          </div>
        @endforeach
      </div>
    @endisset

    {{-- ===== LIST FORM ===== --}}
    <div class="space-y-3">
      @forelse($forms as $f)
        @php
          $isFileType = $f->type === 'pdf';
          $typeLabel  = $isFileType ? 'File (PDF/Word/Excel)' : 'Builder';
          $ext        = $isFileType && $f->pdf_path ? strtolower(pathinfo($f->pdf_path, PATHINFO_EXTENSION)) : null;

          $doc      = strtoupper($f->doc_type ?? 'FORM');
          $docClass = match ($doc) {
            'SOP' => 'bg-blue-100 text-blue-700 dark:bg-blue-900/30 dark:text-blue-300',
            'IK'  => 'bg-amber-100 text-amber-700 dark:bg-amber-900/30 dark:text-amber-300',
            default => 'bg-slate-100 text-slate-700 dark:bg-slate-800 dark:text-slate-300',
          };

          $frontUrl = Route::has('front.forms.show') ? route('front.forms.show', $f->slug ?: $f) : '#';

          $no = method_exists($forms, 'firstItem') && $forms->firstItem()
                ? $forms->firstItem() + $loop->index
                : $loop->iteration;
        @endphp

        <div class="p-4 rounded-xl border bg-white dark:bg-coal-900 border-slate-200/70 dark:border-coal-800 shadow-sm hover:shadow-md transition">
          <div class="flex items-start justify-between gap-3">
            <a class="flex-1" href="{{ $frontUrl }}">
              <div class="font-medium flex flex-wrap items-center gap-2">
                <span class="inline-flex items-center justify-center w-6 h-6 text-xs font-semibold rounded-full
                             bg-slate-200 text-slate-800 dark:bg-coal-800 dark:text-ivory-200">
                  {{ $no }}
                </span>
                {{ $f->title }}
                <span class="text-[10px] px-2 py-0.5 rounded-full {{ $docClass }}">{{ $doc }}</span>
                @if($f->is_active)
                  <span class="text-[10px] px-2 py-0.5 rounded-full bg-emerald-100 text-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-300">Aktif</span>
                @else
                  <span class="text-[10px] px-2 py-0.5 rounded-full bg-slate-100 text-slate-600 dark:bg-slate-800 dark:text-slate-300">Nonaktif</span>
                @endif
              </div>

              <div class="text-sm text-slate-500 dark:text-coal-400 mt-0.5 flex flex-wrap items-center gap-x-2 gap-y-1">
                <span>{{ $typeLabel }}</span>
                <span>— {{ $f->department->name ?? 'Tanpa Departemen' }}</span>
                <span>•</span>
                <span class="uppercase">{{ $doc }}</span>

                @if($isFileType && $f->pdf_path)
                  @php
                    $fileUrl = Route::has('admin.forms.file')
                              ? route('admin.forms.file', $f)
                              : (Storage::disk('public')->exists($f->pdf_path) ? Storage::disk('public')->url($f->pdf_path) : null);
                  @endphp
                  <span>•</span>
                  <span class="uppercase">{{ $ext }}</span>
                  @if($fileUrl)
                    <span>•</span>
                    <a class="underline hover:no-underline" target="_blank" href="{{ $fileUrl }}">Lihat file</a>
                  @else
                    <span>•</span>
                    <span class="text-rose-600 dark:text-rose-300">File tidak ditemukan</span>
                  @endif

                  @if(Route::has('admin.forms.download'))
                    <span>•</span>
                    <a class="underline hover:no-underline" href="{{ route('admin.forms.download', $f) }}">Unduh</a>
                  @endif
                @endif
              </div>
            </a>

            {{-- ACTIONS --}}
            <div class="flex items-center gap-2 shrink-0">
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

              @can('update', $f)
                @if(Route::has('admin.forms.edit'))
                  <a href="{{ route('admin.forms.edit', $f) }}"
                     class="text-xs px-2 py-1 rounded-lg border border-slate-300 text-slate-700 dark:text-slate-300 hover:bg-slate-100/60 dark:hover:bg-coal-800/60 transition">
                    Edit
                  </a>
                @endif
              @endcan

              @can('delete', $f)
                @if(Route::has('admin.forms.destroy'))
                  <form method="POST" action="{{ route('admin.forms.destroy', $f) }}"
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
        <div class="text-slate-500 dark:text-coal-400">Belum ada form.</div>
      @endforelse
    </div>

    @if(method_exists($forms, 'links') && $forms->hasPages())
      <div class="mt-6">
        {{ $forms->appends(request()->except('page'))->links() }}
      </div>
    @endif
  </div>
</div>
@endsection
