{{-- resources/views/admin/forms/index.blade.php --}}
@extends('layouts.app')

@section('content')
<x-ui.page class="">

  <style>
    .soft-scroll::-webkit-scrollbar {
      height: 6px
    }

    .soft-scroll::-webkit-scrollbar-thumb {
      background: #d1d5db;
      border-radius: 4px
    }
  </style>

  <div class="max-w-7xl mx-auto min-w-0">

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
    <x-ui.page-header>
  <div class="ui-page-header__copy"><div>
        <x-ui.page-title>Management Form</x-ui.page-title>
        <x-ui.page-description>
          Pilih perusahaan → departemen → site → tipe dokumen
        </x-ui.page-description>
      </div></div>
  <x-ui.page-actions><div class="flex flex-wrap gap-2 mt-4 sm:mt-0">
        <x-ui.link variant="primary" href="{{ $makeUrl($activeCompany,$activeDept,$activeSite,'SOP') }}"
          class="px-4 py-2 rounded-lg text-xs text-center hover:brightness-110 shadow-none">
          SOP
        </x-ui.link>
        <x-ui.link variant="secondary" href="{{ $makeUrl($activeCompany,$activeDept,$activeSite,'IK') }}"
          class="px-4 py-2 rounded-lg text-xs text-center hover:brightness-110 shadow-none">
          IK
        </x-ui.link>
        <x-ui.link variant="secondary" href="{{ $makeUrl($activeCompany,$activeDept,$activeSite,'FORM') }}"
          class="px-4 py-2 rounded-lg text-xs text-center hover:brightness-125 shadow-none">
          FORM
        </x-ui.link>

        @can('create', \App\Models\Form::class)
        <x-ui.link icon="plus" variant="primary" href="{{ route('admin.forms.create') }}"
          class="px-4 py-2 rounded-lg text-xs text-center hover:brightness-110 shadow-none">Tambah Form
        </x-ui.link>
        @endcan
      </div></x-ui.page-actions>
</x-ui.page-header>




    {{-- STEP 1 - COMPANY --}}
    @if(isset($companies))
    <div class="mb-6">
      <div class="flex items-center gap-2 mb-3">
        <span class="w-8 h-8 bg-maroon-700 text-white rounded-lg flex items-center justify-center text-sm shadow-none">1</span>
        <x-ui.card-title class="font-semibold text-lg">Pilih Perusahaan (PT)</x-ui.card-title>
      </div>

      <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-3">
        <a href="{{ $makeUrl(null,null,null,$activeDoc ?: null) }}"
          class="p-4 rounded-2xl border bg-white dark:bg-coal-900 dark:border-coal-700 shadow-sm hover:shadow-lg transition
       {{ empty($activeCompany) ? 'ring-2 ring-maroon-400' : '' }}">
          <div class="flex gap-3 items-center">
            <div class="w-10 h-10 bg-maroon-700 text-white rounded-lg flex items-center justify-center shadow-none">🌐</div>
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
       {{ $chosen ? 'border-maroon-600 ring-2 ring-maroon-400 bg-maroon-50 dark:bg-maroon-900/20' : '' }}">
          <div class="flex gap-3 items-center">
            <div class="w-10 h-10 bg-maroon-700 text-white rounded-lg flex items-center justify-center shadow-none">🏢</div>
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
        <span class="w-8 h-8 bg-emerald-600 text-white rounded-lg flex items-center justify-center text-sm shadow-none">2</span>
        <x-ui.card-title class="font-semibold text-lg">Pilih Departemen</x-ui.card-title>
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
        <span class="w-8 h-8 bg-sky-600 text-white rounded-lg flex items-center justify-center text-sm shadow-none">3</span>
        <x-ui.card-title class="font-semibold text-lg">Pilih Site</x-ui.card-title>
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
      $badge = $doc === 'SOP' ? 'bg-maroon-700'
      : ($doc === 'IK' ? 'bg-amber-500'
      : 'bg-slate-800');
      @endphp

      <x-ui.panel class="rounded-lg border bg-white dark:bg-coal-900 dark:border-coal-700 shadow-none hover:shadow-xl transition">
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

          <div class="flex flex-nowrap items-center gap-2">
            <x-ui.icon-button action="view" label="Lihat form" href="{{ route('front.forms.show',$f->slug ?? $f) }}" />
            <x-ui.icon-button action="edit" label="Edit form" href="{{ route('admin.forms.edit',$f) }}" />
          </div>
        </div>
      </x-ui.panel>

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
    <x-ui.panel class="mt-8 rounded-lg border bg-white dark:bg-coal-900 dark:border-coal-700 shadow-none">
      Pilih <b>PT</b> → <b>Departemen</b> → <b>Site</b> → <b>SOP / IK / FORM</b>.
    </x-ui.panel>
    @endif



  </div>
</x-ui.page>
@endsection
