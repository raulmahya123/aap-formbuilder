{{-- resources/views/front/forms/index.blade.php --}}
@extends('layouts.app')

@section('title', 'FORM')

@php
  use Illuminate\Support\Str;

  // =========================
  // DEFAULT VALUE & HELPERS
  // =========================
  $forms          = $forms ?? collect();
  $counts         = $counts ?? null;
  $currentDocType = $currentDocType ?? request('doc_type');
  $q              = request('q');
  $departments    = $departments ?? collect();

  // Kalau belum dikirim dari controller, coba ambil dari DB (aman jika table belum ada)
  if ($departments->isEmpty()) {
      try {
          $departments = \App\Models\Department::orderBy('name')->get(['id','name','color']);
      } catch (\Throwable $e) {
          $departments = collect();
      }
  }

  $dt = $currentDocType ? strtoupper($currentDocType) : null;

  $tabClass = function(bool $active) {
    return $active
      ? 'bg-maroon-700 text-white'
      : 'bg-white text-coal-700 dark:bg-coal-900 dark:text-ivory-100 border border-coal-300 dark:border-coal-700 hover:bg-ivory-100 dark:hover:bg-coal-800/70';
  };

  // keep query when clicking tabs/tiles
  $preserveQ = fn(array $params) => array_filter(array_merge(
      ['q' => request('q')],
      $params
  ), fn($v) => !is_null($v) && $v !== '');

  // ==========================================
  // TILES: DB atau FALLBACK (semua clickable)
  // ==========================================
  $fallbackTiles = collect([
      ['name'=>'HRGA','color'=>'#e61caf'],
      ['name'=>'Operasional','color'=>'#7c3aed'],
      ['name'=>'Keuangan','color'=>'#0ea5e9'],
      ['name'=>'IT','color'=>'#059669'],
      ['name'=>'Legal','color'=>'#f59e0b'],
      ['name'=>'Marketing','color'=>'#ef4444'],
      ['name'=>'Sales','color'=>'#8b5cf6'],
      ['name'=>'Procurement','color'=>'#14b8a6'],
      ['name'=>'HSE','color'=>'#16a34a'],
      ['name'=>'GA','color'=>'#334155'],
  ]);

  // Bentuk tiles: id | name | color | fake
  $tiles = $departments->count()
      ? $departments->map(function($d) {
          return (object)[
              'id'    => $d->id,
              'name'  => $d->name,
              'color' => $d->color ?: '#e61caf',
              'fake'  => false,
          ];
        })
      : $fallbackTiles->map(fn($d) => (object)[
          'id'    => null,                // fallback tidak punya id
          'name'  => $d['name'],
          'color' => $d['color'],
          'fake'  => true,
        ]);
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

    @section('actions')
      @if(Route::has('front.forms.entries.index'))
        <a href="{{ route('front.forms.entries.index') }}"
           class="inline-flex items-center gap-2 px-3 py-2 rounded-lg border border-coal-300 dark:border-coal-700 hover:bg-ivory-100 dark:hover:bg-coal-900 text-sm">
          🗂️ Riwayat Saya
        </a>
      @endif
    @endsection
  </div>

  {{-- Search --}}
  <form method="get" class="mb-4 flex flex-col md:flex-row items-stretch md:items-center gap-3">
    <div class="flex-1">
      <input type="search" name="q" value="{{ $q }}"
             placeholder="Cari form (judul/deskripsi)…"
             class="w-full px-3 py-2 rounded-lg border border-coal-300 dark:border-coal-700 bg-white dark:bg-coal-900">
    </div>
    @if($currentDocType)
      <input type="hidden" name="doc_type" value="{{ $currentDocType }}">
    @endif
    @if(request('department_id'))
      <input type="hidden" name="department_id" value="{{ request('department_id') }}">
    @endif
    <button class="px-4 py-2 rounded-lg border border-coal-300 dark:border-coal-700 hover:bg-ivory-100 dark:hover:bg-coal-900">
      Cari
    </button>
  </form>

  {{-- Tabs --}}
  <div class="flex flex-wrap items-center gap-2 mb-6 text-sm">
    <a href="{{ request()->fullUrlWithQuery($preserveQ(['doc_type'=>null, 'page'=>null])) }}"
       class="px-3 py-1.5 rounded-xl {{ $tabClass(!$dt) }}">
      Semua @if($counts && isset($counts['ALL'])) <span class="opacity-80">({{ $counts['ALL'] }})</span> @endif
    </a>
    <a href="{{ request()->fullUrlWithQuery($preserveQ(['doc_type'=>'SOP', 'page'=>null])) }}"
       class="px-3 py-1.5 rounded-xl {{ $tabClass($dt==='SOP') }}">
      SOP @if($counts && isset($counts['SOP'])) <span class="opacity-80">({{ $counts['SOP'] }})</span> @endif
    </a>
    <a href="{{ request()->fullUrlWithQuery($preserveQ(['doc_type'=>'IK', 'page'=>null])) }}"
       class="px-3 py-1.5 rounded-xl {{ $tabClass($dt==='IK') }}">
      IK @if($counts && isset($counts['IK'])) <span class="opacity-80">({{ $counts['IK'] }})</span> @endif
    </a>
    <a href="{{ request()->fullUrlWithQuery($preserveQ(['doc_type'=>'FORM', 'page'=>null])) }}"
       class="px-3 py-1.5 rounded-xl {{ $tabClass($dt==='FORM') }}">
      FORM @if($counts && isset($counts['FORM'])) <span class="opacity-80">({{ $counts['FORM'] }})</span> @endif
    </a>
  </div>

  {{-- ===== GRID KOTAK DEPARTEMEN (selalu tampil & selalu bisa diklik) ===== --}}
  <h2 class="text-sm font-semibold text-slate-600 dark:text-slate-300 mb-3">Semua Departemen</h2>
  @php $activeDept = request('department_id') ? (string) request('department_id') : null; @endphp

  <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 xl:grid-cols-6 gap-3 mb-6">
    @foreach($tiles as $d)
      @php
        $isActive = $d->id && $activeDept === (string) $d->id;
        $hex      = $d->color ?: '#e61caf';
      @endphp

      <div class="relative group rounded-2xl border bg-white dark:bg-coal-900 border-slate-200/70 dark:border-coal-700/70 shadow-sm hover:shadow-md transition overflow-hidden {{ $isActive ? 'ring-2 ring-slate-300 dark:ring-slate-600' : '' }}">
        <div class="h-40 p-3 flex flex-col">
          <div class="flex items-center gap-2">
            <div class="h-9 w-9 rounded-xl flex items-center justify-center" style="background: {{ $hex }};">
              <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-white" fill="currentColor" viewBox="0 0 20 20">
                <path d="M3 3a1 1 0 0 1 1 1v11h12a1 1 0 1 1 0 2H3a1 1 0 0 1-1-1V4a1 1 0 0 1 1-1Zm5 4a1 1 0 0 1 1 1v6H7V8a1 1 0 0 1 1-1Zm6-3a1 1 0 0 1 1 1v9h-2V5a1 1 0 0 1 1-1ZM9 10a1 1 0 0 1 1-1h2v5h-2v-4Z"/>
              </svg>
            </div>
            <div class="flex-1 min-w-0">
              <div class="text-[11px] text-slate-400 leading-none">{{ $hex }}</div>
              <div class="font-semibold truncate">{{ $d->name }}</div>
            </div>
          </div>

          <div class="flex-1"></div>

          <div class="mt-2 grid grid-cols-2 gap-1.5">
            {{-- Semua --}}
            <a href="{{ route('front.forms.index', $preserveQ(['department_id'=>$d->id, 'doc_type'=>null, 'page'=>null])) }}"
               class="text-[11px] px-2 py-1 rounded-lg border border-slate-300 dark:border-coal-700 text-slate-700 dark:text-slate-300 text-center truncate
                      {{ $isActive && !$dt ? 'ring-2 ring-offset-1 ring-slate-300 dark:ring-slate-500' : '' }}">
              Semua
            </a>
            {{-- SOP --}}
            <a href="{{ route('front.forms.index', $preserveQ(['department_id'=>$d->id, 'doc_type'=>'SOP', 'page'=>null])) }}"
               class="text-[11px] px-2 py-1 rounded-lg bg-blue-700 text-white text-center truncate
                      {{ $isActive && $dt==='SOP' ? 'ring-2 ring-offset-1 ring-blue-300' : '' }}">
              SOP
            </a>
            {{-- IK --}}
            <a href="{{ route('front.forms.index', $preserveQ(['department_id'=>$d->id, 'doc_type'=>'IK', 'page'=>null])) }}"
               class="text-[11px] px-2 py-1 rounded-lg bg-amber-600 text-white text-center truncate
                      {{ $isActive && $dt==='IK' ? 'ring-2 ring-offset-1 ring-amber-300' : '' }}">
              IK
            </a>
            {{-- FORM --}}
            <a href="{{ route('front.forms.index', $preserveQ(['department_id'=>$d->id, 'doc_type'=>'FORM', 'page'=>null])) }}"
               class="text-[11px] px-2 py-1 rounded-lg bg-slate-800 text-white text-center truncate
                      {{ $isActive && $dt==='FORM' ? 'ring-2 ring-offset-1 ring-slate-300' : '' }}">
              FORM
            </a>
          </div>
        </div>
      </div>
    @endforeach
  </div>

  {{-- LIST FORM --}}
  @if($forms->count() > 0)
    <div class="rounded-2xl border border-coal-200 dark:border-coal-800 overflow-hidden">
      <div class="hidden md:grid grid-cols-12 gap-3 px-4 py-3 text-xs font-semibold
                  bg-ivory-100 dark:bg-coal-900/40 border-b border-coal-200 dark:border-coal-800">
        <div class="md:col-span-6">Info</div>
        <div class="md:col-span-4">Lokasi</div>
        <div class="md:col-span-2 text-right">Aksi</div>
      </div>

      @foreach($forms as $form)
        @php
          $showUrl = Route::has('front.forms.show')
                    ? route('front.forms.show', $form)
                    : (Route::has('front.forms.fill') ? route('front.forms.fill', $form) : '#');

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

    @if(method_exists($forms, 'links'))
      <div class="mt-6">
        {{ $forms->withQueryString()->links() }}
      </div>
    @endif
  @else
    <div class="rounded-2xl border border-dashed border-coal-300 dark:border-coal-700 p-8 text-center">
      <div class="mx-auto w-12 h-12 mb-3 rounded-full border border-coal-300 dark:border-coal-700 flex items-center justify-center">
        🧾
      </div>
      <h3 class="font-semibold mb-1">Belum ada FORM</h3>
      <p class="text-sm text-coal-600 dark:text-coal-400">Silakan kembali lagi nanti, atau gunakan kotak pencarian di atas.</p>
    </div>
  @endif
@endsection
