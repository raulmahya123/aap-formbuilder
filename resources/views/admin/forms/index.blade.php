{{-- resources/views/admin/forms/index.blade.php --}}
@extends('layouts.app')

@section('content')
<div
  x-data="{ dark: (localStorage.getItem('theme') ?? (window.matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light')) === 'dark' }"
  x-init="document.documentElement.classList.toggle('dark', dark)"
  :class="dark ? 'dark' : ''"
  class="bg-ivory-100 dark:bg-coal-900 min-h-screen text-coal-800 dark:text-ivory-100">
  <div class="max-w-6xl mx-auto p-4 sm:p-6">

    {{-- ====== HEADER + FILTER CHIP (GLOBAL DOC TYPE) ====== --}}
    @php
      $pp            = (int) request('per_page', 10);
      $activeDoc     = strtoupper(request('doc_type', ''));
      $activeDept    = request('department_id') ? (string) request('department_id') : null;
      $activeCompany = request('company_id') ? (string) request('company_id') : null;
      $activeSite    = request('site_id') ? (string) request('site_id') : null;

      // Builder untuk link filter atas (global) — pertahankan dept/company/site jika sudah dipilih
      $makeTop = function (?string $doc) use ($pp, $activeDept, $activeCompany, $activeSite) {
        return route('admin.forms.index', array_filter([
          'department_id' => $activeDept ?: null,
          'company_id'    => $activeCompany ?: null,
          'site_id'       => $activeSite ?: null,
          'doc_type'      => $doc ?: null,
          'per_page'      => $pp,
        ]));
      };
    @endphp

    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 mb-4 sm:mb-6">
      <h1 class="text-xl sm:text-2xl font-serif tracking-tight">Form Tersedia</h1>

      <div class="flex items-center gap-2">
        <a href="{{ $makeTop(null) }}"
           class="text-xs px-3 py-1.5 rounded-lg border border-slate-300 dark:border-coal-700 hover:bg-slate-100/70 dark:hover:bg-coal-800/60 {{ $activeDoc==='' ? 'ring-2 ring-offset-1 ring-slate-300 dark:ring-slate-500' : '' }}">
          Semua
        </a>
        <a href="{{ $makeTop('SOP') }}"
           class="text-xs px-3 py-1.5 rounded-lg bg-[color:var(--brand-maroon,#7b1d2e)] text-white hover:brightness-110 {{ $activeDoc==='SOP' ? 'ring-2 ring-offset-1 ring-maroon-300' : '' }}">
          SOP
        </a>
        <a href="{{ $makeTop('IK') }}"
           class="text-xs px-3 py-1.5 rounded-lg bg-amber-600 text-white hover:bg-amber-500 {{ $activeDoc==='IK' ? 'ring-2 ring-offset-1 ring-amber-300' : '' }}">
          IK
        </a>
        <a href="{{ $makeTop('FORM') }}"
           class="text-xs px-3 py-1.5 rounded-lg bg-slate-800 text-white hover:bg-slate-700 {{ $activeDoc==='FORM' ? 'ring-2 ring-offset-1 ring-slate-300' : '' }}">
          FORM
        </a>

        @if(Route::has('admin.forms.create'))
          @can('create', \App\Models\Form::class)
            <a href="{{ route('admin.forms.create') }}"
               class="ml-2 px-3 py-1.5 rounded-lg bg-[color:var(--brand-maroon,#7b1d2e)] text-ivory-50 text-sm hover:brightness-110 transition">
              + Tambah Form
            </a>
          @endcan
        @endif
      </div>
    </div>

    {{-- ===================================================== --}}
    {{--  GRID DEPARTEMEN — DITEMPELIN FILTER PERUSAHAAN/SITE  --}}
    {{-- ===================================================== --}}
    @isset($departments)
      @php
        $pp = (int) request('per_page', 10); // re-ensure

        // Link builder: kombinasi dept + (opsional) company + (opsional) site + (opsional) doc_type
        $makeDeptCompany = function ($deptId, ?string $companyId, ?string $doc) use ($pp, $activeSite) {
          return route('admin.forms.index', array_filter([
            'department_id' => $deptId,
            'company_id'    => $companyId ?: null,
            'site_id'       => $activeSite ?: null,
            'doc_type'      => $doc ?: null,
            'per_page'      => $pp,
          ]));
        };

        // Builder dengan site eksplisit
        $makeDeptCompanySite = function ($deptId, ?string $companyId, ?string $siteId, ?string $doc) use ($pp) {
          return route('admin.forms.index', array_filter([
            'department_id' => $deptId,
            'company_id'    => $companyId ?: null,
            'site_id'       => $siteId ?: null,
            'doc_type'      => $doc ?: null,
            'per_page'      => $pp,
          ]));
        };

        // Map sites by company untuk render chip "Site"
        /** @var \Illuminate\Support\Collection|\App\Models\Site[] $sites */
        $sitesByCompany = collect(($sites ?? []))->groupBy(function($s){ return (string)$s->company_id; })->map(function($rows){
          return $rows->map(fn($s)=>['id'=>(string)$s->id,'name'=>$s->name,'company_id'=>(string)$s->company_id])->values();
        })->toArray();

        $colorOf = fn($d) => $d->color ?? '#7b1d2e'; // maroon default
      @endphp

      <h2 class="text-sm font-semibold text-slate-600 dark:text-slate-300 mb-3">Semua Departemen</h2>

      <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-3 mb-6">
        @foreach($departments as $d)
          @php
            $isActiveDept = $activeDept === (string) $d->id;
            $hex          = $colorOf($d);
          @endphp

          <div class="p-4 rounded-2xl border bg-white dark:bg-coal-900 border-slate-200/70 dark:border-coal-700/70 shadow-sm hover:shadow-md transition">
            <div class="flex items-start justify-between">
              <div class="flex items-center gap-3">
                {{-- Icon bulat berwarna --}}
                <div class="h-10 w-10 rounded-xl flex items-center justify-center" style="background: {{ $hex }};">
                  <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-white" fill="currentColor" viewBox="0 0 20 20">
                    <path d="M3 3a1 1 0 0 1 1 1v11h12a1 1 0 1 1 0 2H3a1 1 0 0 1-1-1V4a1 1 0 0 1 1-1Zm5 4a1 1 0 0 1 1 1v6H7V8a1 1 0 0 1 1-1Zm6-3a1 1 0 0 1 1 1v9h-2V5a1 1 0 0 1 1-1ZM9 10a1 1 0 0 1 1-1h2v5h-2v-4Z"/>
                  </svg>
                </div>
                <div>
                  <div class="text-xs font-medium text-slate-400">{{ $hex }}</div>
                  <div class="text-lg font-semibold text-slate-900 dark:text-ivory-100 -mt-0.5">
                    {{ $d->name }}
                  </div>
                  <div class="text-sm text-slate-500">Pilih perusahaan, site & tipe dokumen</div>
                </div>
              </div>
            </div>

            {{-- Baris 1: Filter Perusahaan (chips scrollable) --}}
            @isset($companies)
              <div class="mt-3 overflow-x-auto">
                <div class="flex items-center gap-2 min-w-max">
                  {{-- Semua Perusahaan di dept ini --}}
                  <a href="{{ $makeDeptCompany($d->id, null, $activeDoc ?: null) }}"
                     class="text-xs px-2.5 py-1.5 rounded-lg border border-slate-300 dark:border-coal-700 hover:bg-slate-100/70 dark:hover:bg-coal-800/60
                            {{ $isActiveDept && empty($activeCompany) ? 'ring-2 ring-offset-1 ring-slate-300 dark:ring-slate-500' : '' }}">
                    Semua Perusahaan
                  </a>
                  @foreach($companies as $c)
                    @php
                      $chosen = $isActiveDept && $activeCompany === (string) $c->id;
                    @endphp
                    <a href="{{ $makeDeptCompany($d->id, (string)$c->id, $activeDoc ?: null) }}"
                       class="text-xs px-2.5 py-1.5 rounded-lg border
                              {{ $chosen
                                  ? 'border-[color:var(--brand-maroon,#7b1d2e)] text-[color:var(--brand-maroon,#7b1d2e)] bg-[color:var(--brand-maroon,#7b1d2e)]/10 ring-2 ring-offset-1 ring-maroon-300'
                                  : 'border-slate-300 dark:border-coal-700 hover:bg-slate-100/70 dark:hover:bg-coal-800/60 text-slate-700 dark:text-slate-300' }}">
                      @if(!empty($c->logo_url))
                        <img src="{{ $c->logo_url }}?h=20" class="inline-block h-4 w-4 rounded object-cover mr-1 align-middle" alt="">
                      @endif
                      <span class="align-middle">{{ $c->code ?? 'CMP' }}</span>
                    </a>
                  @endforeach
                </div>
              </div>
            @endisset

            {{-- Baris 1.5: Filter Site (muncul hanya jika company aktif) --}}
            @if($isActiveDept && $activeCompany && !empty($sitesByCompany[$activeCompany] ?? []))
              <div class="mt-2 overflow-x-auto">
                <div class="flex items-center gap-2 min-w-max">
                  <a href="{{ $makeDeptCompanySite($d->id, $activeCompany, null, $activeDoc ?: null) }}"
                     class="text-[11px] px-2.5 py-1.5 rounded-lg border border-slate-300 dark:border-coal-700 hover:bg-slate-100/70 dark:hover:bg-coal-800/60
                            {{ empty($activeSite) ? 'ring-2 ring-offset-1 ring-slate-300 dark:ring-slate-500' : '' }}">
                    Semua Site
                  </a>
                  @foreach(($sitesByCompany[$activeCompany] ?? []) as $s)
                    @php $siteActive = $activeSite === (string)$s['id']; @endphp
                    <a href="{{ $makeDeptCompanySite($d->id, $activeCompany, (string)$s['id'], $activeDoc ?: null) }}"
                       class="text-[11px] px-2.5 py-1.5 rounded-lg border
                              {{ $siteActive
                                  ? 'border-emerald-600 text-emerald-700 bg-emerald-50 ring-2 ring-offset-1 ring-emerald-200 dark:bg-emerald-900/20 dark:text-emerald-300'
                                  : 'border-slate-300 dark:border-coal-700 hover:bg-slate-100/70 dark:hover:bg-coal-800/60 text-slate-700 dark:text-slate-300' }}">
                      {{ $s['name'] }}
                    </a>
                  @endforeach
                </div>
              </div>
            @endif

            {{-- Baris 2: Chip SOP/IK/FORM yang mempertahankan pilihan perusahaan & site --}}
            <div class="mt-2 flex flex-wrap items-center gap-2">
              <a href="{{ $makeDeptCompanySite($d->id, $activeCompany ?: null, $activeSite ?: null, null) }}"
                 class="text-xs px-2.5 py-1.5 rounded-lg border border-slate-300 dark:border-coal-700 hover:bg-slate-100/70 dark:hover:bg-coal-800/60
                        {{ $isActiveDept && $activeDoc==='' ? 'ring-2 ring-offset-1 ring-slate-300 dark:ring-slate-500' : '' }}">
                Semua Dokumen
              </a>
              <a href="{{ $makeDeptCompanySite($d->id, $activeCompany ?: null, $activeSite ?: null, 'SOP') }}"
                 class="text-xs px-2.5 py-1.5 rounded-lg bg-[color:var(--brand-maroon,#7b1d2e)] text-white hover:brightness-110
                        {{ $isActiveDept && $activeDoc==='SOP' ? 'ring-2 ring-offset-1 ring-maroon-300' : '' }}">
                SOP
              </a>
              <a href="{{ $makeDeptCompanySite($d->id, $activeCompany ?: null, $activeSite ?: null, 'IK') }}"
                 class="text-xs px-2.5 py-1.5 rounded-lg bg-amber-600 text-white hover:bg-amber-500
                        {{ $isActiveDept && $activeDoc==='IK' ? 'ring-2 ring-offset-1 ring-amber-300' : '' }}">
                IK
              </a>
              <a href="{{ $makeDeptCompanySite($d->id, $activeCompany ?: null, $activeSite ?: null, 'FORM') }}"
                 class="text-xs px-2.5 py-1.5 rounded-lg bg-slate-800 text-white hover:bg-slate-700
                        {{ $isActiveDept && $activeDoc==='FORM' ? 'ring-2 ring-offset-1 ring-slate-300' : '' }}">
                FORM
              </a>
            </div>
          </div>
        @endforeach
      </div>
    @endisset

    {{-- ===== VISIBILITY GUARD: tampilkan list hanya jika ada salah satu filter ===== --}}
    @php
      $shouldShowList = $activeDept || $activeCompany || $activeSite || in_array($activeDoc, ['SOP','IK','FORM']);
    @endphp

    {{-- ===== LIST FORM ===== --}}
    @if($shouldShowList)
      <div class="space-y-3">
        @forelse($forms as $f)
          @php
            $isFileType = $f->type === 'pdf';
            $typeLabel  = $isFileType ? 'File (PDF/Word/Excel)' : 'Builder';
            $ext        = $isFileType && $f->pdf_path ? strtolower(pathinfo($f->pdf_path, PATHINFO_EXTENSION)) : null;

            $doc      = strtoupper($f->doc_type ?? 'FORM');
            $docClass = match ($doc) {
              'SOP' => 'bg-[color:var(--brand-maroon,#7b1d2e)]/10 text-[color:var(--brand-maroon,#7b1d2e)] dark:bg-maroon-900/30',
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

                  @if(isset($f->company) && $f->company)
                    <span>•</span>
                    <span class="truncate">Perusahaan: {{ $f->company->code ?? '' }} {{ $f->company->name ?? '' }}</span>
                  @endif

                  @if(isset($f->site) && $f->site)
                    <span>•</span>
                    <span class="truncate">Site: {{ $f->site->name }}</span>
                  @endif

                  <span>•</span>
                  <span class="uppercase">{{ $doc }}</span>

                  @if($isFileType && $f->pdf_path)
                    @php
                      $fileUrl = Route::has('admin.forms.file')
                                ? route('admin.forms.file', $f)
                                : (\Storage::disk('public')->exists($f->pdf_path) ? \Storage::disk('public')->url($f->pdf_path) : null);
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
                         class="text-xs px-2 py-1 rounded-lg border border-[color:var(--brand-maroon,#7b1d2e)] text-[color:var(--brand-maroon,#7b1d2e)]
                                hover:bg-[color:var(--brand-maroon,#7b1d2e)]/10 transition">
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
    @else
      {{-- Petunjuk ketika belum pilih apa-apa --}}
      <div class="mt-6">
        <div class="p-4 rounded-xl border bg-white dark:bg-coal-900 border-slate-200/70 dark:border-coal-800">
          <div class="text-sm text-slate-600 dark:text-slate-300">
            Pilih <span class="font-semibold">Departemen</span>, lalu tentukan
            <span class="font-semibold">Perusahaan</span>, <span class="font-semibold">Site</span> (opsional),
            dan <span class="font-semibold">SOP/IK/FORM</span> dari kartu departemen.
          </div>
        </div>
      </div>
    @endif

  </div>
</div>
@endsection
