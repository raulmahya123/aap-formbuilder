{{-- resources/views/front/forms/index.blade.php --}}
@extends('layouts.app')

@section('title', 'FORM')

@php
  use Illuminate\Support\Str;

  /** @var \Illuminate\Support\Collection|\Illuminate\Contracts\Pagination\Paginator|\Illuminate\Contracts\Pagination\LengthAwarePaginator $forms */
  // Fallback supaya view tidak error kalau controller belum kirim $forms
  $forms = $forms ?? collect();

  $q = request('q');
@endphp

@section('breadcrumbs')
  <nav class="text-sm text-coal-600 dark:text-coal-300">
    <a href="{{ url('/') }}" class="hover:underline">Home</a>
    <span class="mx-1">/</span>
    <span class="font-medium">FORM</span>
  </nav>
@endsection

@section('content')
  {{-- Header --}}
  <div class="flex items-center justify-between mb-4">
    <h1 class="text-2xl font-extrabold tracking-tight">FORM</h1>

    {{-- Aksi kanan (opsional) --}}
    @section('actions')
      {{-- Kosongkan/isi sesuai kebutuhan, misal tombol "Riwayat Saya" jika route tersedia --}}
      @if(Route::has('front.forms.entries.index'))
        <a href="{{ route('front.forms.entries.index') }}"
           class="inline-flex items-center gap-2 px-3 py-2 rounded-lg border border-coal-300 dark:border-coal-700 hover:bg-ivory-100 dark:hover:bg-coal-900 text-sm">
          🗂️ Riwayat Saya
        </a>
      @endif
    @endsection
  </div>

  {{-- Filter/Search --}}
  <form method="get" class="mb-6 flex flex-col md:flex-row items-stretch md:items-center gap-3">
    <div class="flex-1">
      <input type="search" name="q" value="{{ $q }}"
             placeholder="Cari form (judul/deskripsi)…"
             class="w-full px-3 py-2 rounded-lg border border-coal-300 dark:border-coal-700 bg-white dark:bg-coal-900">
    </div>

    {{-- Contoh dropdown status (opsional), tinggal aktifkan kalau pakai param 'status' --}}
    {{-- <select name="status" class="px-3 py-2 rounded-lg border border-coal-300 dark:border-coal-700 bg-white dark:bg-coal-900">
      <option value="">Semua Status</option>
      <option value="open" @selected(request('status')==='open')>Open</option>
      <option value="closed" @selected(request('status')==='closed')>Closed</option>
    </select> --}}

    <button class="px-4 py-2 rounded-lg border border-coal-300 dark:border-coal-700 hover:bg-ivory-100 dark:hover:bg-coal-900">
      Cari
    </button>
  </form>

  {{-- Daftar Form (cards) --}}
  @if($forms->count() > 0)
    <div class="grid sm:grid-cols-2 lg:grid-cols-3 gap-4">
      @foreach($forms as $form)
        @php
          // Siapkan URL isi/detail form yang aman walau route belum ada
          $showUrl = '#';
          if (Route::has('front.forms.show')) {
            $showUrl = route('front.forms.show', $form->id);
          } elseif (Route::has('front.forms.fill')) {
            $showUrl = route('front.forms.fill', $form->id);
          }

          // Field umum yang mungkin ada di model: title, description, department, site, is_active
          $title = $form->title ?? ($form->name ?? 'Untitled Form');
          $desc  = $form->description ?? '';
          $dept  = optional($form->department)->name;
          $site  = optional($form->site)->code ?: optional($form->site)->name;
          $isActive = (bool)($form->is_active ?? true);
        @endphp

        <div class="rounded-2xl border border-coal-200 dark:border-coal-800 bg-ivory-50 dark:bg-coal-950 p-4 flex flex-col">
          <div class="flex items-start justify-between gap-3">
            <h3 class="font-semibold leading-snug">{{ $title }}</h3>
            @if($isActive)
              <span class="text-xs rounded px-2 py-0.5 bg-emerald-100 text-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-300 border border-emerald-200 dark:border-emerald-800">Open</span>
            @else
              <span class="text-xs rounded px-2 py-0.5 bg-rose-100 text-rose-700 dark:bg-rose-900/30 dark:text-rose-300 border border-rose-200 dark:border-rose-800">Closed</span>
            @endif
          </div>

          @if($dept || $site)
            <div class="mt-2 flex flex-wrap gap-2">
              @if($site)
                <span class="inline-flex items-center gap-1 px-2 py-0.5 text-xs rounded border
                             border-maroon-700 text-maroon-800 dark:text-maroon-300 dark:border-maroon-600 bg-maroon-50/70 dark:bg-maroon-900/20">
                  📍 {{ $site }}
                </span>
              @endif
              @if($dept)
                <span class="inline-flex items-center gap-1 px-2 py-0.5 text-xs rounded border border-coal-300 dark:border-coal-700">
                  🏷️ {{ $dept }}
                </span>
              @endif
            </div>
          @endif

          @if($desc)
            <p class="mt-3 text-sm text-coal-700 dark:text-coal-300">{{ Str::limit(strip_tags($desc), 160) }}</p>
          @endif

          <div class="mt-4 flex items-center gap-2">
            <a href="{{ $showUrl }}"
               class="inline-flex items-center gap-2 px-3 py-2 rounded-lg border border-maroon-700 text-maroon-700 hover:bg-maroon-50
                      dark:border-maroon-600 dark:text-maroon-300 dark:hover:bg-maroon-900/20">
              LIHAT
            </a>
          </div>
        </div>
      @endforeach
    </div>

    {{-- Pagination (jika tersedia) --}}
    @if(method_exists($forms, 'links'))
      <div class="mt-6">
        {{ $forms->withQueryString()->links() }}
      </div>
    @endif

  @else
    {{-- Empty state --}}
    <div class="rounded-2xl border border-dashed border-coal-300 dark:border-coal-700 p-8 text-center">
      <div class="mx-auto w-12 h-12 mb-3 rounded-full border border-coal-300 dark:border-coal-700 flex items-center justify-center">
        🧾
      </div>
      <h3 class="font-semibold mb-1">Belum ada FORM</h3>
      <p class="text-sm text-coal-600 dark:text-coal-400">Silakan kembali lagi nanti, atau gunakan kotak pencarian di atas.</p>
    </div>
  @endif
@endsection
