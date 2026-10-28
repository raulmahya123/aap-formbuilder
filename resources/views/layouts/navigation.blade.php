@php
    $user = auth()->user();
    $isSuper = $user?->isSuperAdmin() ?? false;
    $navItemClass = function (bool $isActive) {
        return $isActive ? 'ui-nav-item is-active' : 'ui-nav-item';
    };
    $subItemClass = function (bool $isActive) {
        return $isActive
            ? 'flex items-center gap-2 rounded-lg px-2.5 py-1.5 text-xs font-semibold bg-[#713F12] text-white transition'
            : 'flex items-center gap-2 rounded-lg px-2.5 py-1.5 text-xs font-medium text-slate-600 hover:text-slate-900 hover:bg-slate-100 transition';
    };

    // Auto open dropdown if current URL matches child routes
    $isShePerfActive = request()->is('*/safety*') || request()->is('*/performance/*') || request()->is('*/manhours*') || request()->is('*/targets*');
    $isSapActive = request()->is('*/sap*');
    $isRiskActive = request()->is('*/risk*') || request()->is('*/hipo*') || request()->is('*/ccm-reports*');
    $isPicaActive = request()->is('*/pica*');
    $isDocActive = request()->is('*/documents*');
    $isOpsActive = request()->is('*/overview*') || request()->is('*/incidents*') || request()->is('*/inspections*') || request()->is('*/training*') || request()->is('*/environment*') || request()->is('*/sites*') || request()->is('*/reports*') || request()->is('*/notifications*');
    $isMasterActive = request()->routeIs('admin.departments.*') || request()->routeIs('admin.sites.*') || request()->routeIs('admin.companies.*') || request()->routeIs('admin.groups.*') || request()->routeIs('admin.indicators.*') || request()->routeIs('admin.forms.*') || request()->routeIs('user.forms.*');
    $isK3lhActive = request()->routeIs('admin.daily.*') || request()->routeIs('admin.hipo.*') || request()->routeIs('admin.wellness.*') || request()->routeIs('admin.reports.*') || request()->routeIs('admin.ccm.*');
@endphp

<aside id="app-sidebar" aria-label="Navigasi utama" x-show="isDesktop || sidebarOpen" x-cloak
  :class="{ 'translate-x-0': sidebarOpen, 'ui-sidebar--collapsed': sidebarCollapsed && isDesktop }"
  :inert="!isDesktop && !sidebarOpen"
  class="ui-sidebar fixed inset-y-0 left-0 z-50 w-64 -translate-x-full bg-white border-r border-[#D4D4D8] transition-transform duration-200 lg:static lg:translate-x-0 lg:w-auto lg:block overflow-y-auto">
  <button type="button" class="ui-drawer-close ui-shell-toggle lg:hidden" @click="sidebarOpen=false; $nextTick(() => document.getElementById('navigation-toggle').focus())" aria-label="Tutup navigasi"><x-ui.icon name="x" /></button>
  <nav aria-label="Menu aplikasi">
  <div class="ui-sidebar-brand flex items-center justify-center border-b border-[#D4D4D8] bg-white px-4 py-5">
    <a href="{{ route('dashboard') }}" class="flex items-center justify-center" aria-label="Mandala — Dashboard">
      <img
        src="{{ asset('assets/images/logomandala.png') }}"
        alt="Mandala Management System Andalan"
        class="h-auto w-36 transition-all"
        x-show="!sidebarCollapsed"
        x-cloak
      />
      <span class="ui-sidebar-brand-mark" x-show="sidebarCollapsed" x-cloak aria-hidden="true">M</span>
    </a>
  </div>
  <div class="mt-4 grid gap-3">
    {{-- ================= ADMIN ================= --}}
    @if($isSuper || ($user && $user->isAdmin()))

    {{-- 1. BERANDA / DASHBOARD --}}
    <div>
      <div class="grid gap-1">
        @if(Route::has('dashboard'))
        @if($user?->canUseFeatureRoute('dashboard'))
          <a title="Dashboard" aria-label="Dashboard" href="{{ route('dashboard') }}" class="{{ $navItemClass(request()->routeIs('dashboard')) }}">
            <x-ui-icon name="dashboard"/>
            <span class="ui-nav-label font-semibold">Dashboard Utama</span>
          </a>
        @endif
        @endif
        @if(Route::has('admin.qa.index') && $user?->canUseFeatureRoute('admin.qa.index'))
          <a title="Mandala Chat" href="{{ route('admin.qa.index') }}" class="{{ $navItemClass(request()->routeIs('admin.qa.*')) }}">
            <x-ui-icon name="chat"/>
            <span class="ui-nav-label font-semibold">Mandala Chat</span>
          </a>
        @endif
      </div>
    </div>

    {{-- 2. OPERASIONAL K3LH (DROPDOWN) --}}
    <div x-data="{ openK3lh: {{ $isK3lhActive ? 'true' : 'false' }} }" class="rounded-xl border border-transparent transition">
      <button type="button" @click="if (sidebarCollapsed) { toggleSidebarCollapse(); openK3lh = true; } else { openK3lh = !openK3lh; }" title="Operasional K3LH" aria-label="Operasional K3LH" class="ui-nav-item w-full justify-between cursor-pointer font-semibold text-slate-800 hover:bg-slate-100" :class="{ 'bg-slate-50 border-slate-200': openK3lh }">
        <div class="flex items-center gap-3 min-w-0">
          <x-ui-icon name="shield"/>
          <span class="ui-nav-label">Operasional K3LH</span>
        </div>
        <svg class="h-4 w-4 text-slate-400 transition-transform duration-200" :class="openK3lh ? 'rotate-180' : ''" fill="none" viewBox="0 0 24 24" stroke="currentColor">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
        </svg>
      </button>

      <div x-show="openK3lh && !sidebarCollapsed" x-collapse x-cloak class="mt-1 pl-7 pr-2 grid gap-1 border-l-2 border-slate-200 ml-5 py-1">
        @if(Route::has('admin.daily.index'))
        @if($user?->canUseFeatureRoute('admin.daily.index'))
          <a title="Input Data Operasional" href="{{ route('admin.daily.index') }}" class="{{ $subItemClass(request()->routeIs('admin.daily.*')) }}">Input Data Operasional</a>
        @endif
        @endif

        @if(Route::has('admin.hipo.index'))
        @if($user?->canUseFeatureRoute('admin.hipo.index'))
          <a title="HIPO & Nearmiss" href="{{ route('admin.hipo.index') }}" class="{{ $subItemClass(request()->routeIs('admin.hipo.*')) }}">HIPO & Nearmiss</a>
        @endif
        @endif

        @if(Route::has('admin.wellness.index'))
        @if($user?->canUseFeatureRoute('admin.wellness.index'))
          <a title="Health Assesment" href="{{ route('admin.wellness.index') }}" class="{{ $subItemClass(request()->routeIs('admin.wellness.*')) }}">Health Assesment</a>
        @endif
        @endif

        @if(Route::has('admin.reports.index'))
        @if($user?->canUseFeatureRoute('admin.reports.index'))
          <a title="Dashboard KPI & Statistik" href="{{ route('admin.reports.index') }}" class="{{ $subItemClass(request()->routeIs('admin.reports.index')) }}">Dashboard KPI & Statistik</a>
        @endif
        @endif

        @if(Route::has('admin.reports.annual'))
        @if($user?->canUseFeatureRoute('admin.reports.annual'))
          <a title="Laporan Tahunan" href="{{ route('admin.reports.annual') }}" class="{{ $subItemClass(request()->routeIs('admin.reports.annual')) }}">Laporan Tahunan</a>
        @endif
        @endif

        @if(Route::has('admin.reports.quarterly'))
        @if($user?->canUseFeatureRoute('admin.reports.quarterly'))
          <a title="Laporan Triwulan" href="{{ route('admin.reports.quarterly') }}" class="{{ $subItemClass(request()->routeIs('admin.reports.quarterly')) }}">Laporan Triwulan</a>
        @endif
        @endif

        @if(Route::has('admin.reports.aggregate'))
        @if($user?->canUseFeatureRoute('admin.reports.aggregate'))
          <a title="Laporan Agregat" href="{{ route('admin.reports.aggregate') }}" class="{{ $subItemClass(request()->routeIs('admin.reports.aggregate')) }}">Laporan Agregat</a>
        @endif
        @endif

        @if(Route::has('admin.daily.summary'))
        @if($user?->canUseFeatureRoute('admin.daily.summary'))
          <a title="Matriks Input" href="{{ route('admin.daily.summary') }}" class="{{ $subItemClass(request()->routeIs('admin.daily.summary')) }}">Matriks Input</a>
        @endif
        @endif

        @if(Route::has('admin.ccm.index'))
        @if($user?->canUseFeatureRoute('admin.ccm.index'))
          <a title="Laporan CCM" href="{{ route('admin.ccm.index') }}" class="{{ $subItemClass(request()->routeIs('admin.ccm.*')) }}">Laporan CCM</a>
        @endif
        @endif
      </div>
    </div>

    @if($user?->canUseFeature('hse'))
      <div class="ui-nav-section px-3 pt-2 text-xs font-medium text-[#52525B]">SHE Management</div>
      <x-ui.navigation-tree :items="\App\Support\SheNavigation::visible(\App\Support\SheNavigation::groups(), $user)" />
    @endif

    {{-- SECTION DIVIDER: MASTER DATA --}}
    <div class="ui-nav-section px-3 pt-2 text-[10px] font-bold uppercase tracking-wider text-slate-400">
      Master Data & Formulir
    </div>

    {{-- 9. DOKUMEN & MASTER DATA (DROPDOWN) --}}
    <div x-data="{ openMaster: {{ $isMasterActive ? 'true' : 'false' }} }" class="rounded-xl border border-transparent transition">
      <button type="button" @click="if (sidebarCollapsed) { toggleSidebarCollapse(); openMaster = true; } else { openMaster = !openMaster; }" title="Master Data & Forms" aria-label="Master Data & Forms" class="ui-nav-item w-full justify-between cursor-pointer font-semibold text-slate-800 hover:bg-slate-100" :class="{ 'bg-slate-50 border-slate-200': openMaster }">
        <div class="flex items-center gap-3 min-w-0">
          <x-ui-icon name="mandala"/>
          <span class="ui-nav-label">Master Data & Forms</span>
        </div>
        <svg class="h-4 w-4 text-slate-400 transition-transform duration-200" :class="openMaster ? 'rotate-180' : ''" fill="none" viewBox="0 0 24 24" stroke="currentColor">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
        </svg>
      </button>

      <div x-show="openMaster && !sidebarCollapsed" x-collapse x-cloak class="mt-1 pl-7 pr-2 grid gap-1 border-l-2 border-slate-200 ml-5 py-1">
        @if(Route::has('admin.forms.index'))
        @if($user?->canUseFeatureRoute('admin.forms.index'))
          <a href="{{ route('admin.forms.index') }}" class="{{ $subItemClass(request()->routeIs('admin.forms.*')) }}">Dokumen Mandala</a>
        @endif
        @endif

        @if(Route::has('admin.departments.index'))
        @if($user?->canUseFeatureRoute('admin.departments.index'))
          <a href="{{ route('admin.departments.index') }}" class="{{ $subItemClass(request()->routeIs('admin.departments.*')) }}">Department</a>
        @endif
        @endif

        @if(Route::has('admin.sites.index'))
        @if($user?->canUseFeatureRoute('admin.sites.index'))
          <a href="{{ route('admin.sites.index') }}" class="{{ $subItemClass(request()->routeIs('admin.sites.*')) }}">Master Site</a>
        @endif
        @endif

        @if(Route::has('admin.companies.index'))
        @if($user?->canUseFeatureRoute('admin.companies.index'))
          <a href="{{ route('admin.companies.index') }}" class="{{ $subItemClass(request()->routeIs('admin.companies.*')) }}">Master Perusahaan</a>
        @endif
        @endif

        @if(Route::has('admin.groups.index'))
        @if($user?->canUseFeatureRoute('admin.groups.index'))
          <a href="{{ route('admin.groups.index') }}" class="{{ $subItemClass(request()->routeIs('admin.groups.*')) }}">Kelompok Indikator</a>
        @endif
        @endif

        @if(Route::has('admin.indicators.index'))
        @if($user?->canUseFeatureRoute('admin.indicators.index'))
          <a href="{{ route('admin.indicators.index') }}" class="{{ $subItemClass(request()->routeIs('admin.indicators.*')) }}">Indikator</a>
        @endif
        @endif

        @if(Route::has('user.forms.index'))
        @if($user?->canUseFeatureRoute('user.forms.index'))
          <a href="{{ route('user.forms.index') }}" class="{{ $subItemClass(request()->routeIs('user.forms.*')) }}">Formulir Publik</a>
        @endif
        @endif

        @if(Route::has('user.daily_notes.index'))
        @if($user?->canUseFeatureRoute('user.daily_notes.index'))
          <a href="{{ route('user.daily_notes.index') }}" class="{{ $subItemClass(request()->routeIs('user.daily_notes.*')) }}">Catatan Harian</a>
        @endif
        @endif
      </div>
    </div>

    {{-- 10. ADMINISTRASI SISTEM --}}
    @if($user?->role === 'super_admin')
    <div x-data="{ openAdmin: false }" class="rounded-xl border border-transparent transition">
      <button type="button" @click="if (sidebarCollapsed) { toggleSidebarCollapse(); openAdmin = true; } else { openAdmin = !openAdmin; }" title="Administrasi" aria-label="Administrasi" class="ui-nav-item w-full justify-between cursor-pointer font-semibold text-slate-800 hover:bg-slate-100">
        <div class="flex items-center gap-3 min-w-0">
          <x-ui-icon name="lock"/>
          <span class="ui-nav-label">Administrasi</span>
        </div>
        <svg class="h-4 w-4 text-slate-400 transition-transform duration-200" :class="openAdmin ? 'rotate-180' : ''" fill="none" viewBox="0 0 24 24" stroke="currentColor">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
        </svg>
      </button>

      <div x-show="openAdmin && !sidebarCollapsed" x-collapse x-cloak class="mt-1 pl-7 pr-2 grid gap-1 border-l-2 border-slate-200 ml-5 py-1">
        @if($isSuper && Route::has('admin.users.active.index'))
        @if($user?->canUseFeatureRoute('admin.users.active.index'))
          <a href="{{ route('admin.users.active.index') }}" class="{{ $subItemClass(request()->routeIs('admin.users.active.*') || request()->routeIs('admin.site_access.*')) }}">Kelola Pengguna & Akses</a>
        @endif
        @endif
      </div>
    </div>
    @endif

    {{-- ================= NON ADMIN ================= --}}
    @else
    <div x-data="{ openMenu: true }">
      <div class="px-3 text-[10px] font-bold uppercase tracking-wider text-slate-400 mb-2">Menu Utama</div>
      <div class="grid gap-1">
        @if($user?->canUseFeatureRoute('dashboard'))
          <a href="{{ route('dashboard') }}" class="{{ $navItemClass(request()->routeIs('dashboard')) }}"><x-ui-icon name="dashboard"/><span class="ui-nav-label">Dashboard</span></a>
        @endif

        @if(Route::has('admin.qa.index') && $user?->canUseFeatureRoute('admin.qa.index'))
          <a href="{{ route('admin.qa.index') }}" class="{{ $navItemClass(request()->routeIs('admin.qa.*')) }}"><x-ui-icon name="chat"/><span class="ui-nav-label">Mandala Chat</span></a>
        @endif


        @if(Route::has('user.forms.index'))
        @if($user?->canUseFeatureRoute('user.forms.index'))
          <a href="{{ route('user.forms.index') }}" class="{{ $navItemClass(request()->routeIs('user.forms.*')) }}"><x-ui-icon name="mandala"/><span class="ui-nav-label">Formulir Publik</span></a>
        @endif
        @endif

        @if(Route::has('user.daily_notes.index'))
        @if($user?->canUseFeatureRoute('user.daily_notes.index'))
          <a href="{{ route('user.daily_notes.index') }}" class="{{ $navItemClass(request()->routeIs('user.daily_notes.*')) }}"><x-ui-icon name="note"/><span class="ui-nav-label">Catatan Harian</span></a>
        @endif
        @endif
      </div>
    </div>
    @endif
  </div>
</nav>
</aside>
