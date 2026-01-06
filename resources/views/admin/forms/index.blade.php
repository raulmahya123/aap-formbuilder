{{-- resources/views/admin/forms/index.blade.php --}}
@extends('layouts.app')

@section('content')
<div class="">

  <style>
    .soft-scroll::-webkit-scrollbar {
      height: 6px
    }

    .soft-scroll::-webkit-scrollbar-thumb {
      background: #d1d5db;
      border-radius: 4px
    }
  </style>

  <div class="max-w-7xl mx-auto p-6">

    @php
    $pp = (int) request('per_page', 10);
    $activeDoc = strtoupper(request('doc_type', ''));
    $activeDept = request('department_id') ? (string) request('department_id') : null;
    $activeCompany = request('company_id') ? (string) request('company_id') : null;
    $activeSite = request('site_id') ? (string) request('site_id') : null;

    $makeUrl = function (?string $company = null, ?string $dept = null, ?string $site = null, ?string $doc = null) use ($pp) {
    return route('admin.forms.index', array_filter([
    'company_id' => $company ?: null,
    'department_id' => $dept ?: null,
    'site_id' => $site ?: null,
    'doc_type' => $doc ?: null,
    'per_page' => $pp,
    ]));
    };
    @endphp


    {{-- HEADER --}}
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between mb-6">
      <div>
        <h1 class="text-3xl font-bold tracking-tight flex items-center gap-3">
          <span class="w-10 h-10 bg-rose-600 text-white rounded-xl flex items-center justify-center shadow-md">📂</span>
          Management Form
        </h1>
        <p class="text-slate-500 dark:text-slate-400 mt-1">
          Pilih perusahaan → departemen → site → tipe dokumen
        </p>
      </div>

      <div class="flex gap-2 mt-4 sm:mt-0">
        <a href="{{ $makeUrl($activeCompany,$activeDept,$activeSite,'SOP') }}"
          class="px-4 py-2 rounded-xl bg-rose-600 text-white text-xs hover:brightness-110 shadow">
          SOP
        </a>
        <a href="{{ $makeUrl($activeCompany,$activeDept,$activeSite,'IK') }}"
          class="px-4 py-2 rounded-xl bg-amber-500 text-white text-xs hover:brightness-110 shadow">
          IK
        </a>
        <a href="{{ $makeUrl($activeCompany,$activeDept,$activeSite,'FORM') }}"
          class="px-4 py-2 rounded-xl bg-slate-900 text-white text-xs hover:brightness-125 shadow">
          FORM
        </a>

        @can('create', \App\Models\Form::class)
        <a href="{{ route('admin.forms.create') }}"
          class="px-4 py-2 rounded-xl bg-emerald-600 text-white text-xs hover:brightness-110 shadow">
          + Tambah Form
        </a>
        @endcan
      </div>
    </div>




    {{-- STEP 1 - COMPANY --}}
    @if(isset($companies))
    <div class="mb-6">
      <div class="flex items-center gap-2 mb-3">
        <span class="w-8 h-8 bg-rose-600 text-white rounded-xl flex items-center justify-center text-sm shadow">1</span>
        <h3 class="font-semibold text-lg">Pilih Perusahaan (PT)</h3>
      </div>

      <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-3">
        <a href="{{ $makeUrl(null,null,null,$activeDoc ?: null) }}"
          class="p-4 rounded-2xl border bg-white dark:bg-coal-900 dark:border-coal-700 shadow-sm hover:shadow-lg transition
       {{ empty($activeCompany) ? 'ring-2 ring-rose-400' : '' }}">
          <div class="flex gap-3 items-center">
            <div class="w-10 h-10 bg-rose-600 text-white rounded-xl flex items-center justify-center shadow">🌐</div>
            <div>
              <div class="font-semibold">Semua Perusahaan</div>
              <div class="text-xs text-slate-500">Tampilkan global</div>
            </div>
          </div>
        </a>

        @foreach($companies as $c)
        @php $chosen = $activeCompany === (string)$c->id; @endphp
        <a href="{{ $makeUrl((string)$c->id,null,null,$activeDoc ?: null) }}"
          class="p-4 rounded-2xl border bg-white dark:bg-coal-900 dark:border-coal-700 shadow-sm hover:shadow-lg transition
       {{ $chosen ? 'border-rose-600 ring-2 ring-rose-400 bg-rose-50 dark:bg-rose-900/20' : '' }}">
          <div class="flex gap-3 items-center">
            <div class="w-10 h-10 bg-rose-600 text-white rounded-xl flex items-center justify-center shadow">🏢</div>
            <div>
              <div class="font-semibold">{{ $c->code ?? 'PT' }}</div>
              <div class="text-xs text-slate-500">{{ $c->name }}</div>
            </div>
          </div>
        </a>
        @endforeach
      </div>
    </div>
    @endif




    {{-- STEP 2 - DEPARTMENT --}}
    @if($activeCompany && isset($departments))
    <div class="mb-6">
      <div class="flex items-center gap-2 mb-3">
        <span class="w-8 h-8 bg-emerald-600 text-white rounded-xl flex items-center justify-center text-sm shadow">2</span>
        <h3 class="font-semibold text-lg">Pilih Departemen</h3>
      </div>

      <div class="flex gap-3 overflow-x-auto soft-scroll pb-3 snap-x">

        {{-- ALL --}}
        <a href="{{ $makeUrl($activeCompany,null,$activeSite,$activeDoc ?: null) }}"
          class="shrink-0 snap-start inline-flex items-center gap-2 
          px-8 min-h-[64px] min-w-[200px]
          rounded-[20px] border-2 bg-white dark:bg-coal-900 dark:border-coal-700
          shadow hover:shadow-md transition whitespace-nowrap
         {{ empty($activeDept) ? 'border-emerald-500 border-2 bg-emerald-50 dark:bg-emerald-900/20' : 'border-2 border-slate-200 dark:border-coal-700' }}
">
          🧭 Semua Departemen
        </a>


        @foreach($departments as $d)
        @php $isActiveDept = $activeDept === (string)$d->id; @endphp
        <a href="{{ $makeUrl($activeCompany,(string)$d->id,$activeSite,$activeDoc ?: null) }}"
          class="shrink-0 snap-start inline-flex items-center gap-2
          px-8 min-h-[64px] min-w-[200px]
          rounded-[20px] bg-white dark:bg-coal-900 shadow hover:shadow-md transition whitespace-nowrap
          {{ $isActiveDept 
              ? 'border-emerald-500 border-2 bg-emerald-50 dark:bg-emerald-900/20' 
              : 'border-2 border-slate-200 dark:border-coal-700' }}">
          🏷️ {{ $d->name }}
        </a>

        @endforeach



      </div>
    </div>
    @endif




    {{-- STEP 3 - SITE --}}
    @if($activeCompany && $activeDept && isset($sites))
    @php
    $sitesByCompany = collect($sites)->where('company_id',$activeCompany);
    @endphp

    @if($sitesByCompany->count())
    <div class="mb-6">
      <div class="flex items-center gap-2 mb-3">
        <span class="w-8 h-8 bg-sky-600 text-white rounded-xl flex items-center justify-center text-sm shadow">3</span>
        <h3 class="font-semibold text-lg">Pilih Site</h3>
      </div>

      <div class="flex gap-3 overflow-x-auto soft-scroll pb-3 snap-x">

        {{-- ALL --}}
        <a href="{{ $makeUrl($activeCompany,$activeDept,null,$activeDoc ?: null) }}"
          class="shrink-0 snap-start inline-flex items-center gap-2
          px-8 min-h-[64px] min-w-[200px]
          rounded-[20px] bg-white dark:bg-coal-900 shadow hover:shadow-md whitespace-nowrap
          {{ empty($activeSite) 
              ? 'border-2 border-sky-500 bg-sky-50 dark:bg-sky-900/20' 
              : 'border-2 border-slate-200 dark:border-coal-700' }}">
          🌍 Semua Site
        </a>


        {{-- LIST --}}
        @foreach($sitesByCompany as $s)
        @php $siteActive = $activeSite === (string)$s->id; @endphp
        <a href="{{ $makeUrl($activeCompany,$activeDept,(string)$s->id,$activeDoc ?: null) }}"
          class="shrink-0 snap-start inline-flex items-center gap-2
          px-8 min-h-[64px] min-w-[200px]
          rounded-[20px] bg-white dark:bg-coal-900 shadow hover:shadow-md transition whitespace-nowrap
          {{ $siteActive 
              ? 'border-2 border-sky-500 bg-sky-50 dark:bg-sky-900/20' 
              : 'border-2 border-slate-200 dark:border-coal-700' }}">
          📌 {{ $s->name }}
        </a>

        @endforeach

      </div>
    </div>
    @endif
    @endif




    {{-- LIST --}}
    @php
    $shouldShowList = $activeCompany || $activeDept || $activeSite || in_array($activeDoc,['SOP','IK','FORM']);
    @endphp

    @if($shouldShowList)

    <div class="space-y-3">
      @forelse($forms as $f)
      @php
      $isFile = $f->type === 'pdf';
      $doc = strtoupper($f->doc_type ?? 'FORM');
      $badge = $doc === 'SOP' ? 'bg-rose-600'
      : ($doc === 'IK' ? 'bg-amber-500'
      : 'bg-slate-800');
      @endphp

      <div class="p-4 rounded-2xl border bg-white dark:bg-coal-900 dark:border-coal-700 shadow-sm hover:shadow-xl transition">
        <div class="flex justify-between items-start gap-3">
          <div>
            <div class="flex items-center gap-2">
              <span class="px-2 py-1 text-[10px] text-white rounded-lg {{ $badge }}">{{ $doc }}</span>
              <span class="font-semibold">{{ $f->title }}</span>
              @if($f->is_active)
              <span class="text-[10px] px-2 py-1 rounded-full bg-emerald-600/20 text-emerald-700 dark:text-emerald-300">
                Aktif
              </span>
              @endif
            </div>

            <div class="text-sm text-slate-500 dark:text-slate-400 mt-1 flex gap-2 flex-wrap">
              {{ $isFile ? '📎 File' : '🧩 Builder' }}
              @if($f->company) • {{ $f->company->code }} @endif
              @if($f->department) • {{ $f->department->name }} @endif
              @if($f->site) • {{ $f->site->name }} @endif
            </div>
          </div>

          <div class="flex gap-2">
            <a href="{{ route('front.forms.show',$f->slug ?? $f) }}"
              class="text-xs px-3 py-1 rounded-xl bg-slate-900 text-white hover:brightness-125 shadow">
              Lihat
            </a>
            <a href="{{ route('admin.forms.edit',$f) }}"
              class="text-xs px-3 py-1 rounded-xl border shadow">
              Edit
            </a>
          </div>
        </div>
      </div>

      @empty
      <div class="text-slate-500 dark:text-slate-400">Tidak ada form.</div>
      @endforelse
    </div>

    @if($forms->hasPages())
    <div class="mt-6">
      {{ $forms->appends(request()->except('page'))->links() }}
    </div>
    @endif

    @else
    <div class="mt-8 p-5 rounded-2xl border bg-white dark:bg-coal-900 dark:border-coal-700 shadow">
      Pilih <b>PT</b> → <b>Departemen</b> → <b>Site</b> → <b>SOP / IK / FORM</b>.
    </div>
    @endif



  </div>
</div>
@endsection