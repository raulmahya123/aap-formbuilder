@php
    $user = auth()->user();
    $isSuper = $user?->isSuperAdmin() ?? false;
    $navItemClass = function (bool $isActive) {
        return $isActive ? 'ui-nav-item is-active' : 'ui-nav-item';
    };
@endphp

<nav class="ui-sidebar flex flex-col justify-between py-4" :class="sidebarCollapsed ? 'px-2' : 'px-4'" aria-label="Navigasi Utama">
  <div class="flex items-center justify-center border-b border-[#D4D4D8] bg-white px-4 py-5">
    <a href="{{ route('dashboard') }}" class="flex items-center justify-center">
      <img
        src="{{ asset('assets/images/logomandala.png') }}"
        alt="Mandala Management System Andalan"
        class="h-auto transition-all"
        :class="sidebarCollapsed ? 'w-10' : 'w-36'"
      />
    </a>
  </div>
  <div class="mt-4 grid gap-4 overflow-y-auto">
    {{-- ================= ADMIN ================= --}}
    @if($isSuper || ($user && $user->isAdmin()))

    <div>
      <div class="grid gap-1">
        @if(Route::has('dashboard'))
        @if($user?->canUseFeatureRoute('dashboard'))
<a title="Dashboard" aria-label="Dashboard" href="{{ route('dashboard') }}" class="{{ $navItemClass(request()->routeIs('dashboard')) }}"><x-ui-icon name="dashboard"/><span class="ui-nav-label">Dashboard</span></a>
@endif
        @endif
      </div>
    </div>

    <div>
      <div class="ui-nav-section px-3 text-[11px] font-semibold normal-case tracking-normal text-[#52525B]">K3LH / HSE</div>
      <div class="mt-2 grid gap-1">
        @if(Route::has('admin.daily.index'))
        @if($user?->canUseFeatureRoute('admin.daily.index'))
<a title="Input Data Operasional" aria-label="Input Data Operasional" href="{{ route('admin.daily.index') }}" class="{{ $navItemClass(request()->routeIs('admin.daily.*')) }}"><x-ui-icon name="daily"/><span class="ui-nav-label">Input Data Operasional</span></a>
@endif
        @endif

        @if(Route::has('admin.qa.index'))
        @if($user?->canUseFeatureRoute('admin.qa.index'))
<a title="HIPO & Nearmiss" aria-label="HIPO & Nearmiss" href="{{ route('admin.qa.index') }}" class="{{ $navItemClass(request()->routeIs('admin.qa.*')) }}"><x-ui-icon name="qa"/><span class="ui-nav-label">HIPO & Nearmiss</span></a>
@endif
        @endif

        @if(Route::has('admin.wellness.index'))
        @if($user?->canUseFeatureRoute('admin.wellness.index'))
<a title="Health Assesment Checklist" aria-label="Health Assesment Checklist" href="{{ route('admin.wellness.index') }}" class="{{ $navItemClass(request()->routeIs('admin.wellness.*')) }}"><x-ui-icon name="report"/><span class="ui-nav-label">Health Assesment</span></a>
@endif
        @endif

        @if(Route::has('admin.reports.index'))
        @if($user?->canUseFeatureRoute('admin.reports.index'))
<a title="Dashboard KPI & Statistik" aria-label="Dashboard KPI & Statistik" href="{{ route('admin.reports.index') }}" class="{{ $navItemClass(request()->routeIs('admin.reports.index')) }}"><x-ui-icon name="chart"/><span class="ui-nav-label">Dashboard KPI & Statistik</span></a>
@endif
        @endif

        @if(Route::has('admin.reports.annual'))
        @if($user?->canUseFeatureRoute('admin.reports.annual'))
<a title="Laporan Tahunan" aria-label="Laporan Tahunan" href="{{ route('admin.reports.annual') }}" class="{{ $navItemClass(request()->routeIs('admin.reports.annual')) }}"><x-ui-icon name="report"/><span class="ui-nav-label">Laporan Tahunan</span></a>
@endif
        @endif

        @if(Route::has('admin.reports.quarterly'))
        @if($user?->canUseFeatureRoute('admin.reports.quarterly'))
<a title="Laporan Triwulan" aria-label="Laporan Triwulan" href="{{ route('admin.reports.quarterly') }}" class="{{ $navItemClass(request()->routeIs('admin.reports.quarterly')) }}"><x-ui-icon name="report"/><span class="ui-nav-label">Laporan Triwulan</span></a>
@endif
        @endif

        @if(Route::has('admin.reports.aggregate'))
        @if($user?->canUseFeatureRoute('admin.reports.aggregate'))
<a title="Laporan Agregat" aria-label="Laporan Agregat" href="{{ route('admin.reports.aggregate') }}" class="{{ $navItemClass(request()->routeIs('admin.reports.aggregate')) }}"><x-ui-icon name="report"/><span class="ui-nav-label">Laporan Agregat</span></a>
@endif
        @endif

        @if(Route::has('admin.reports.monthly'))
        @if($user?->canUseFeatureRoute('admin.reports.monthly'))
<a title="Rekap Bulanan" aria-label="Rekap Bulanan" href="{{ route('admin.reports.monthly') }}" class="{{ $navItemClass(request()->routeIs('admin.reports.monthly')) }}"><x-ui-icon name="report"/><span class="ui-nav-label">Rekap Bulanan</span></a>
@endif
        @endif

        @if(Route::has('admin.daily.summary'))
        @if($user?->canUseFeatureRoute('admin.daily.summary'))
<a title="Matriks Input" aria-label="Matriks Input" href="{{ route('admin.daily.summary') }}" class="{{ $navItemClass(request()->routeIs('admin.daily.summary')) }}"><x-ui-icon name="daily"/><span class="ui-nav-label">Matriks Input</span></a>
@endif
        @endif

        @if(Route::has('admin.ccm.index'))
        @if($user?->canUseFeatureRoute('admin.ccm.index'))
<a title="Laporan CCM" aria-label="Laporan CCM" href="{{ route('admin.ccm.index') }}" class="{{ $navItemClass(request()->routeIs('admin.ccm.*')) }}"><x-ui-icon name="report"/><span class="ui-nav-label">Laporan CCM</span></a>
@endif
        @endif
      </div>
    </div>

    <div>
      <div class="ui-nav-section px-3 text-[11px] font-semibold normal-case tracking-normal text-[#52525B]">Dokumen & Master Data</div>
      <div class="mt-2 grid gap-1">
        @if(Route::has('admin.documents.index'))
        @if($user?->canUseFeatureRoute('admin.documents.index'))
<a title="Dokumen Mandala" aria-label="Dokumen Mandala" href="{{ route('admin.documents.index') }}" class="{{ $navItemClass(request()->routeIs('admin.documents.*')) }}"><x-ui-icon name="document"/><span class="ui-nav-label">Dokumen Mandala</span></a>
@endif
        @endif

        @if(Route::has('admin.departments.index'))
        @if($user?->canUseFeatureRoute('admin.departments.index'))
<a title="Department" aria-label="Department" href="{{ route('admin.departments.index') }}" class="{{ $navItemClass(request()->routeIs('admin.departments.*')) }}"><x-ui-icon name="folder"/><span class="ui-nav-label">Department</span></a>
@endif
        @endif

        @if(Route::has('admin.sites.index'))
        @if($user?->canUseFeatureRoute('admin.sites.index'))
<a title="Master Site" aria-label="Master Site" href="{{ route('admin.sites.index') }}" class="{{ $navItemClass(request()->routeIs('admin.sites.*')) }}"><x-ui-icon name="site"/><span class="ui-nav-label">Master Site</span></a>
@endif
        @endif

        @if(Route::has('admin.companies.index'))
        @if($user?->canUseFeatureRoute('admin.companies.index'))
<a title="Master Perusahaan" aria-label="Master Perusahaan" href="{{ route('admin.companies.index') }}" class="{{ $navItemClass(request()->routeIs('admin.companies.*')) }}"><x-ui-icon name="site"/><span class="ui-nav-label">Master Perusahaan</span></a>
@endif
        @endif

        @if(Route::has('admin.groups.index'))
        @if($user?->canUseFeatureRoute('admin.groups.index'))
<a title="Kelompok Indikator" aria-label="Kelompok Indikator" href="{{ route('admin.groups.index') }}" class="{{ $navItemClass(request()->routeIs('admin.groups.*')) }}"><x-ui-icon name="group"/><span class="ui-nav-label">Kelompok Indikator</span></a>
@endif
        @endif

        @if(Route::has('admin.indicators.index'))
        @if($user?->canUseFeatureRoute('admin.indicators.index'))
<a title="Indikator" aria-label="Indikator" href="{{ route('admin.indicators.index') }}" class="{{ $navItemClass(request()->routeIs('admin.indicators.*')) }}"><x-ui-icon name="indicator"/><span class="ui-nav-label">Indikator</span></a>
@endif
        @endif

        @if(Route::has('admin.forms.index'))
        @if($user?->canUseFeatureRoute('admin.forms.index'))
<a title="Form Builder" aria-label="Form Builder" href="{{ route('admin.forms.index') }}" class="{{ $navItemClass(request()->routeIs('admin.forms.*')) }}"><x-ui-icon name="form"/><span class="ui-nav-label">Form Builder</span></a>
@endif
        @endif

        @if(Route::has('user.forms.index'))
        @if($user?->canUseFeatureRoute('user.forms.index'))
<a title="Formulir Publik" aria-label="Formulir Publik" href="{{ route('user.forms.index') }}" class="{{ $navItemClass(request()->routeIs('user.forms.*')) }}"><x-ui-icon name="form"/><span class="ui-nav-label">Formulir Publik</span></a>
@endif
        @endif

        @if(Route::has('user.daily_notes.index'))
        @if($user?->canUseFeatureRoute('user.daily_notes.index'))
<a title="Catatan Harian" aria-label="Catatan Harian" href="{{ route('user.daily_notes.index') }}" class="{{ $navItemClass(request()->routeIs('user.daily_notes.*')) }}"><x-ui-icon name="note"/><span class="ui-nav-label">Catatan Harian</span></a>
@endif
        @endif

        @if(Route::has('admin.reports.monthly'))
        @if($user?->canUseFeatureRoute('admin.reports.monthly'))
<a title="Rekap Bulanan" aria-label="Rekap Bulanan" href="{{ route('admin.reports.monthly') }}" class="{{ $navItemClass(request()->routeIs('admin.reports.monthly')) }}"><x-ui-icon name="report"/><span class="ui-nav-label">Rekap Bulanan</span></a>
@endif
        @endif
      </div>
    </div>

    @if($user?->role === 'super_admin')
    <div x-data="{openAdministration:true}">
      <button type="button" class="ui-nav-section flex cursor-pointer justify-between px-3 text-[11px] font-semibold normal-case tracking-normal text-[#52525B]" :aria-expanded="openAdministration" @click="openAdministration=!openAdministration">
        Administrasi
        <span aria-hidden="true" x-text="openAdministration ? '−' : '+'"></span>
      </button>
      <div class="mt-2 grid gap-1" x-show="sidebarCollapsed || openAdministration" x-transition>
        @if($isSuper && Route::has('admin.users.active.index'))
        @if($user?->canUseFeatureRoute('admin.users.active.index'))
<a title="Kelola Pengguna & Akses" aria-label="Kelola Pengguna & Akses" href="{{ route('admin.users.active.index') }}" class="{{ $navItemClass(request()->routeIs('admin.users.active.*') || request()->routeIs('admin.site_access.*')) }}"><x-ui-icon name="users"/><span class="ui-nav-label">Kelola Pengguna & Akses</span></a>
@endif
        @endif
      </div>
    </div>
    @endif

    {{-- ================= NON ADMIN ================= --}}
    @else

    <div x-data="{openMenu:true}">
      <button type="button" class="ui-nav-section px-3 text-xs normal-case tracking-normalr text-coal-500 flex justify-between cursor-pointer" :aria-expanded="openMenu" @click="openMenu=!openMenu">
        Menu
        <span aria-hidden="true" x-text="openMenu ? '−' : '+'"></span>
      </button>

      <div class="mt-2 grid gap-1" x-show="sidebarCollapsed || openMenu" x-transition>
        @if($user?->canUseFeatureRoute('dashboard'))
<a title="Dashboard" aria-label="Dashboard" href="{{ route('dashboard') }}" class="{{ $navItemClass(request()->routeIs('dashboard')) }}"><x-ui-icon name="dashboard"/><span class="ui-nav-label">Dashboard</span></a>
@endif

        @if(Route::has('admin.qa.index'))
        @if($user?->canUseFeatureRoute('admin.qa.index'))
<a title="HIPO & Nearmiss" aria-label="HIPO & Nearmiss" href="{{ route('admin.qa.index') }}" class="{{ $navItemClass(request()->routeIs('admin.qa.*')) }}"><x-ui-icon name="qa"/><span class="ui-nav-label">HIPO & Nearmiss</span></a>
@endif
        @endif

        @if(Route::has('admin.documents.index'))
        @if($user?->canUseFeatureRoute('admin.documents.index'))
<a title="Dokumen Mandala" aria-label="Dokumen Mandala" href="{{ route('admin.documents.index') }}" class="{{ $navItemClass(request()->routeIs('admin.documents.*')) }}"><x-ui-icon name="document"/><span class="ui-nav-label">Dokumen Mandala</span></a>
@endif
        @endif

        @if(Route::has('user.forms.index'))
        @if($user?->canUseFeatureRoute('user.forms.index'))
<a title="Formulir Publik" aria-label="Formulir Publik" href="{{ route('user.forms.index') }}" class="{{ $navItemClass(request()->routeIs('user.forms.*')) }}"><x-ui-icon name="form"/><span class="ui-nav-label">Formulir Publik</span></a>
@endif
        @endif

        @if(Route::has('user.daily_notes.index'))
        @if($user?->canUseFeatureRoute('user.daily_notes.index'))
<a title="Catatan Harian" aria-label="Catatan Harian" href="{{ route('user.daily_notes.index') }}" class="{{ $navItemClass(request()->routeIs('user.daily_notes.*')) }}"><x-ui-icon name="note"/><span class="ui-nav-label">Catatan Harian</span></a>
@endif
        @endif

        @if(Route::has('admin.reports.monthly'))
        @if($user?->canUseFeatureRoute('admin.reports.monthly'))
<a title="Rekap Bulanan" aria-label="Rekap Bulanan" href="{{ route('admin.reports.monthly') }}" class="{{ $navItemClass(request()->routeIs('admin.reports.monthly')) }}"><x-ui-icon name="report"/><span class="ui-nav-label">Rekap Bulanan</span></a>
@endif
        @endif
      </div>
    </div>
    @endif
  </div>
</nav>
