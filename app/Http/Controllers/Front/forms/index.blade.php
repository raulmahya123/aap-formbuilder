@extends('layouts.app')

@section('title', 'Daftar Formulir')

@section('content')
<div 
    x-data="{ dark: localStorage.getItem('theme')==='dark' }" 
    x-init="document.documentElement.classList.toggle('dark',dark)"
    class="bg-ivory-100 dark:bg-coal-900 min-h-screen text-coal-800 dark:text-ivory-100"
>
    <div class="max-w-6xl mx-auto px-4 sm:px-6 py-8">
        <h1 class="text-2xl md:text-3xl font-serif mb-4">Daftar Formulir</h1>

        {{-- Filter Jenis Dokumen --}}
        @php
          $dt = $currentDocType ?? request('doc_type'); // prefer variabel dari controller
          $allCount  = $counts['ALL']  ?? null;
          $sopCount  = $counts['SOP']  ?? null;
          $ikCount   = $counts['IK']   ?? null;
          $formCount = $counts['FORM'] ?? null;
          // helper kelas tab aktif
          $tab = fn($active) => $active
              ? 'bg-maroon-700 text-white'
              : 'bg-white text-coal-700 dark:bg-coal-800 dark:text-ivory-100 border border-coal-200 dark:border-coal-700 hover:bg-ivory-50 dark:hover:bg-coal-800/70';
        @endphp

        <div class="flex flex-wrap items-center gap-2 mb-6 text-sm">
          <a href="{{ request()->fullUrlWithQuery(['doc_type'=>null, 'page'=>null]) }}"
             class="px-3 py-1.5 rounded-xl {{ $tab(!$dt) }}">
             Semua @isset($allCount)<span class="opacity-80">({{ $allCount }})</span>@endisset
          </a>
          <a href="{{ request()->fullUrlWithQuery(['doc_type'=>'SOP', 'page'=>null]) }}"
             class="px-3 py-1.5 rounded-xl {{ $tab(($dt ?? '')==='SOP') }}">
             SOP @isset($sopCount)<span class="opacity-80">({{ $sopCount }})</span>@endisset
          </a>
          <a href="{{ request()->fullUrlWithQuery(['doc_type'=>'IK', 'page'=>null]) }}"
             class="px-3 py-1.5 rounded-xl {{ $tab(($dt ?? '')==='IK') }}">
             IK @isset($ikCount)<span class="opacity-80">({{ $ikCount }})</span>@endisset
          </a>
          <a href="{{ request()->fullUrlWithQuery(['doc_type'=>'FORM', 'page'=>null]) }}"
             class="px-3 py-1.5 rounded-xl {{ $tab(($dt ?? '')==='FORM') }}">
             FORM @isset($formCount)<span class="opacity-80">({{ $formCount }})</span>@endisset
          </a>
        </div>

        @if(session('success'))
            <div class="mb-4 p-3 rounded-lg bg-green-100 text-green-800">
                {{ session('success') }}
            </div>
        @endif

        @if($forms->count())
            <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
                @foreach($forms as $form)
                    @php
                      $doc = strtoupper($form->doc_type ?? 'FORM');
                      $badge = match ($doc) {
                        'SOP'  => 'bg-blue-100 text-blue-700',
                        'IK'   => 'bg-amber-100 text-amber-700',
                        default=> 'bg-slate-100 text-slate-700',
                      };
                    @endphp
                    <div class="p-5 rounded-2xl border border-coal-200 dark:border-coal-700 bg-white dark:bg-coal-800 shadow-soft flex flex-col justify-between">
                        <div>
                            <div class="flex items-center gap-2 mb-2">
                              <h2 class="text-lg font-semibold">{{ $form->title }}</h2>
                              <span class="text-[10px] px-2 py-0.5 rounded-full {{ $badge }}">{{ $doc }}</span>
                              @if($form->is_active)
                                <span class="text-[10px] px-2 py-0.5 rounded-full bg-emerald-100 text-emerald-700">Aktif</span>
                              @else
                                <span class="text-[10px] px-2 py-0.5 rounded-full bg-slate-100 text-slate-600">Nonaktif</span>
                              @endif
                            </div>
                            <p class="text-sm text-coal-600 dark:text-coal-300 line-clamp-3">
                                {{ $form->description ?? 'Tanpa deskripsi' }}
                            </p>
                            <div class="mt-2 text-xs text-coal-500 dark:text-coal-300 flex flex-wrap gap-2">
                              <span>{{ $form->type === 'pdf' ? 'File (PDF/Word/Excel)' : 'Builder' }}</span>
                              @if(!empty($form->department?->name))
                                <span>• {{ $form->department->name }}</span>
                              @endif
                            </div>
                        </div>
                        <div class="mt-4">
                            <a href="{{ route('front.forms.show', $form) }}"
                               class="inline-block px-4 py-2 rounded-xl bg-maroon-700 text-white hover:bg-maroon-800 dark:bg-maroon-600 dark:hover:bg-maroon-700 transition">
                                Isi Formulir
                            </a>
                        </div>
                    </div>
                @endforeach
            </div>

            {{-- Pagination --}}
            <div class="mt-6">
                {{ $forms->links() }}
            </div>
        @else
            <p class="text-coal-500 dark:text-coal-300">Belum ada formulir tersedia.</p>
        @endif
    </div>
</div>
@endsection
