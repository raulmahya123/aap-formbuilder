{{-- resources/views/front/forms/index.blade.php --}}
@extends('layouts.app')

@section('title', 'FORM')

@php
  use Illuminate\Support\Str;

  /** @var \Illuminate\Support\Collection|\Illuminate\Contracts\Pagination\Paginator|\Illuminate\Contracts\Pagination\LengthAwarePaginator $forms */
  // Fallback supaya view tidak error kalau controller belum kirim $forms
  $forms = $forms ?? collect();

  $q = request('q');

  // Vars opsional dari controller untuk tab
  $currentDocType = $currentDocType ?? request('doc_type');
  $counts = $counts ?? null;

  // helper kelas tab aktif
  $tabClass = function(bool $active) {
    return $active
      ? 'bg-maroon-700 text-white'
      : 'bg-white text-coal-700 dark:bg-coal-900 dark:text-ivory-100 border border-coal-300 dark:border-coal-700 hover:bg-ivory-100 dark:hover:bg-coal-800/70';
  };
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
      @if(Route::has('front.forms.entries.index'))
        <a href="{{ route('front.forms.entries.index') }}"
           class="inline-flex items-center gap-2 px-3 py-2 rounded-lg border border-coal-300 dark:border-coal-700 hover:bg-ivory-100 dark:hover:bg-coal-900 text-sm">
          🗂️ Riwayat Saya
        </a>
      @endif
    @endsection
  </div>

  {{-- Filter/Search --}}
  <form method="get" class="mb-4 flex flex-col md:flex-row items-stretch md:items-center gap-3">
    <div class="flex-1">
      <input type="search" name="q" value="{{ $q }}"
             placeholder="Cari form (judul/deskripsi)…"
             class="w-full px-3 py-2 rounded-lg border border-coal-300 dark:border-coal-700 bg-white dark:bg-coal-900">
    </div>
    {{-- pertahankan doc_type saat search --}}
    @if($currentDocType)
      <input type="hidden" name="doc_type" value="{{ $currentDocType }}">
    @endif
    <button class="px-4 py-2 rounded-lg border border-coal-300 dark:border-coal-700 hover:bg-ivory-100 dark:hover:bg-coal-900">
      Cari
    </button>
  </form>

  {{-- Tabs Jenis Dokumen --}}
  @php
    $dt = $currentDocType ? strtoupper($currentDocType) : null;
  @endphp
  <div class="flex flex-wrap items-center gap-2 mb-6 text-sm">
    <a href="{{ request()->fullUrlWithQuery(['doc_type'=>null, 'page'=>null]) }}"
       class="px-3 py-1.5 rounded-xl {{ $tabClass(!$dt) }}">
      Semua
      @if($counts && isset($counts['ALL'])) <span class="opacity-80">({{ $counts['ALL'] }})</span> @endif
    </a>
    <a href="{{ request()->fullUrlWithQuery(['doc_type'=>'SOP', 'page'=>null]) }}"
       class="px-3 py-1.5 rounded-xl {{ $tabClass($dt==='SOP') }}">
      SOP
      @if($counts && isset($counts['SOP'])) <span class="opacity-80">({{ $counts['SOP'] }})</span> @endif
    </a>
    <a href="{{ request()->fullUrlWithQuery(['doc_type'=>'IK', 'page'=>null]) }}"
       class="px-3 py-1.5 rounded-xl {{ $tabClass($dt==='IK') }}">
      IK
      @if($counts && isset($counts['IK'])) <span class="opacity-80">({{ $counts['IK'] }})</span> @endif
    </a>
    <a href="{{ request()->fullUrlWithQuery(['doc_type'=>'FORM', 'page'=>null]) }}"
       class="px-3 py-1.5 rounded-xl {{ $tabClass($dt==='FORM') }}">
      FORM
      @if($counts && isset($counts['FORM'])) <span class="opacity-80">({{ $counts['FORM'] }})</span> @endif
    </a>
  </div>

  {{-- Daftar Form (cards) --}}
{{-- Daftar Form (list 3 kolom, tanpa foto) --}}
@if($forms->count() > 0)
  <div class="rounded-2xl border border-coal-200 dark:border-coal-800 overflow-hidden">

    {{-- header kolom (desktop) --}}
    <div class="hidden md:grid grid-cols-12 gap-3 px-4 py-3 text-xs font-semibold
                bg-ivory-100 dark:bg-coal-900/40 border-b border-coal-200 dark:border-coal-800">
      <div class="md:col-span-6">Info</div>
      <div class="md:col-span-4">Lokasi</div>
      <div class="md:col-span-2 text-right">Aksi</div>
    </div>

    @foreach($forms as $form)
      @php
        // Tentukan URL show
        $showUrl = '#';
        if (Route::has('front.forms.show')) {
          $showUrl = route('front.forms.show', $form);
        } elseif (Route::has('front.forms.fill')) {
          $showUrl = route('front.forms.fill', $form);
        }

        $title = $form->title ?? ($form->name ?? 'Untitled Form');
        $desc  = $form->description ?? '';
        $dept  = optional($form->department)->name;
        $site  = optional($form->site)->code ?: optional($form->site)->name;
        $isActive = (bool)($form->is_active ?? true);

        $doc = strtoupper($form->doc_type ?? 'FORM');
        $badge = match ($doc) {
          'SOP'  => 'bg-blue-100 text-blue-700',
          'IK'   => 'bg-amber-100 text-amber-700',
          default=> 'bg-slate-100 text-slate-700',
        };
      @endphp

      <div class="grid grid-cols-1 md:grid-cols-12 gap-3 px-4 py-4 border-t
                  border-coal-200 dark:border-coal-800 bg-white dark:bg-coal-950">
        {{-- 1) INFO --}}
        <div class="md:col-span-6">
          <div class="flex items-start gap-2">
            <h3 class="font-semibold leading-snug">{{ $title }}</h3>
            <span class="text-[10px] px-2 py-0.5 rounded-full {{ $badge }}">{{ $doc }}</span>
          </div>
          @if($desc)
            <p class="mt-2 text-sm text-coal-700 dark:text-coal-300">
              {{ \Illuminate\Support\Str::limit(strip_tags($desc), 160) }}
            </p>
          @endif
        </div>

        {{-- 2) LOKASI (site & department) --}}
        <div class="md:col-span-4">
          <div class="flex flex-wrap gap-2">
            @if($site)
              <span class="inline-flex items-center gap-1 px-2 py-0.5 text-xs rounded border
                           border-maroon-700 text-maroon-800 dark:text-maroon-300 dark:border-maroon-600
                           bg-maroon-50/70 dark:bg-maroon-900/20">
                📍 {{ $site }}
              </span>
            @endif
            @if($dept)
              <span class="inline-flex items-center gap-1 px-2 py-0.5 text-xs rounded border
                           border-coal-300 dark:border-coal-700">
                🏷️ {{ $dept }}
              </span>
            @endif
          </div>
        </div>

        {{-- 3) AKSI (status + tombol) --}}
        <div class="md:col-span-2 flex items-center md:justify-end gap-2">
          @if($isActive)
            <span class="text-[10px] rounded px-2 py-0.5 bg-emerald-100 text-emerald-700
                         dark:bg-emerald-900/30 dark:text-emerald-300 border border-emerald-200 dark:border-emerald-800">
              Open
            </span>
          @else
            <span class="text-[10px] rounded px-2 py-0.5 bg-rose-100 text-rose-700
                         dark:bg-rose-900/30 dark:text-rose-300 border border-rose-200 dark:border-rose-800">
              Closed
            </span>
          @endif

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
