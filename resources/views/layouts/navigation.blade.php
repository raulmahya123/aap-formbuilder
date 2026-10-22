{{-- resources/views/partials/navigation.blade.php --}}
@php

$user = auth()->user();
$isSuper = $user && method_exists($user,'isSuperAdmin') && $user->isSuperAdmin();
$isAdmin = $isSuper || ($user && method_exists($user,'isAdmin') && $user->isAdmin());

$homeUrl = $user?->canUseFeature('dashboard') && Route::has('admin.dashboard') ? route('admin.dashboard') : route('profile.edit');

$navItemClass = function(bool $active = false){
$base = 'flex items-center gap-3 rounded-lg border px-3 py-2 text-sm transition outline-none';
$inactive = 'border-transparent text-[#18181B] hover:border-[#D4D4D8] hover:bg-[#F4F4F5] focus-visible:ring-2 focus-visible:ring-[#1D4ED8]';
$activeCls = 'is-active border-[#713F12] bg-[#713F12] text-white';
return $active ? "$base $activeCls" : "$base $inactive";
};

$userContractsUrl = null;
if (Route::has('user.contracts.index')) $userContractsUrl = route('user.contracts.index');
elseif (Route::has('contracts.index')) $userContractsUrl = route('contracts.index');
@endphp


{{-- ===== SIDEBAR (DESKTOP) ===== --}}
<aside
  x-show="isDesktop || sidebarOpen" x-cloak
  :class="{ 'translate-x-0': sidebarOpen, 'ui-sidebar--collapsed': sidebarCollapsed && isDesktop }"
  id="app-sidebar" aria-label="Navigasi utama" :inert="!isDesktop && !sidebarOpen"
  class="ui-sidebar fixed inset-y-0 left-0 z-50 w-64 -translate-x-full transition-transform duration-200 lg:static lg:translate-x-0 lg:w-auto lg:block overflow-y-auto bg-white border-r border-[#D4D4D8] flex flex-col">

  <div class="flex min-h-0 flex-1 flex-col">
  <button type="button" class="ui-drawer-close ui-shell-toggle lg:hidden" @click="sidebarOpen=false; $nextTick(() => document.getElementById('navigation-toggle').focus())" aria-label="Tutup navigasi"><x-ui.icon name="x" /></button>

  {{-- BRAND --}}
  <div class="flex items-center justify-center border-b border-[#D4D4D8] bg-white px-4 py-5">
    <a href="{{ $homeUrl }}" class="inline-flex items-center justify-center" aria-label="Mandala">
      <img src="{{ asset('assets/images/logomandala.png') }}" alt="Mandala" class="h-12 w-auto max-w-[11rem] object-contain">
    </a>
  </div>


  {{-- MENU --}}
  <nav class="p-4 space-y-5 flex-1">

    {{-- ================= ADMIN ================= --}}
    @if($isAdmin)

    {{-- MENU COLLAPSE --}}
    <div x-data="{openMenu:true}">
      <button type="button" class="ui-nav-section flex cursor-pointer justify-between px-3 text-[11px] font-semibold normal-case tracking-normal text-[#52525B]" :aria-expanded="openMenu" @click="openMenu=!openMenu">
        Menu
        <span aria-hidden="true" x-text="openMenu ? '−' : '+'"></span>
      </button>

      <div class="mt-2 grid gap-1" x-show="sidebarCollapsed || openMenu" x-transition>
        @if($user?->canUseFeatureRoute('admin.dashboard'))
<a title="Dashboard" aria-label="Dashboard" href="{{ route('admin.dashboard') }}" class="{{ $navItemClass(request()->routeIs('admin.dashboard')) }}"><x-ui-icon name="dashboard"/><span class="ui-nav-label">Dashboard</span></a>
@endif

        @if(Route::has('admin.wellness.index'))
        @if($user?->canUseFeatureRoute('admin.wellness.index'))
<a title="Health Assesment Checklist" aria-label="Health Assesment Checklist" href="{{ route('admin.wellness.index') }}" class="{{ $navItemClass(request()->routeIs('admin.wellness.*')) }}"><x-ui-icon name="wellness"/><span class="ui-nav-label">Health Assesment Checklist</span></a>
@endif
        @endif

        @if(Route::has('admin.departments.index'))
        @if($user?->canUseFeatureRoute('admin.departments.index'))
<a title="Departments" aria-label="Departments" href="{{ route('admin.departments.index') }}" class="{{ $navItemClass(request()->routeIs('admin.departments.*')) }}"><x-ui-icon name="department"/><span class="ui-nav-label">Departments</span></a>
@endif
        @endif

        @if(Route::has('admin.forms.index'))
        @if($user?->canUseFeatureRoute('admin.forms.index'))
<a title="Mandala" aria-label="Mandala" href="{{ route('admin.forms.index') }}" class="{{ $navItemClass(request()->routeIs('admin.forms.*')) }}"><x-ui-icon name="mandala"/><span class="ui-nav-label">Mandala</span></a>
@endif
        @endif

        @if(Route::has('admin.qa.index'))
        @if($user?->canUseFeatureRoute('admin.qa.index'))
<a title="Mandala Chat" aria-label="Mandala Chat" href="{{ route('admin.qa.index') }}" class="{{ $navItemClass(request()->routeIs('admin.qa.*')) }}"><x-ui-icon name="message-circle"/><span class="ui-nav-label">Mandala Chat</span></a>
@endif
        @endif

        @if(Route::has('admin.contracts.index'))
        @if($user?->canUseFeatureRoute('admin.contracts.index'))
<a title="Contracts" aria-label="Contracts" href="{{ route('admin.contracts.index') }}" class="{{ $navItemClass(request()->routeIs('admin.contracts.*')) }}"><x-ui-icon name="contract"/><span class="ui-nav-label">Contracts</span></a>
@endif
        @endif

        @if($isSuper && Route::has('admin.users.active.index'))
        @if($user?->canUseFeatureRoute('admin.users.active.index'))
<a title="Manage Users" aria-label="Manage Users" href="{{ route('admin.users.active.index') }}" class="{{ $navItemClass(request()->routeIs('admin.users.active.*')) }}"><x-ui-icon name="users"/><span class="ui-nav-label">Manage Users</span></a>
@endif
        @endif
      </div>
    </div>


    {{-- HSE COLLAPSE --}}
    <div x-data="{openHse:true}">
      <button type="button" class="ui-nav-section flex cursor-pointer justify-between px-3 text-[11px] font-semibold normal-case tracking-normal text-[#52525B]" :aria-expanded="openHse" @click="openHse=!openHse">
        HSE / KPI
        <span aria-hidden="true" x-text="openHse ? '−' : '+'"></span>
      </button>

      <div class="mt-2 grid gap-1" x-show="sidebarCollapsed || openHse" x-transition>

        @if(Route::has('ccm-reports.index'))
        @if($user?->canUseFeatureRoute('ccm-reports.index'))
<a title="Critical Control Management" aria-label="Critical Control Management" href="{{ route('ccm-reports.index') }}"
          class="{{ $navItemClass(request()->routeIs('ccm-reports.*')) }}">
          <x-ui-icon name="shield"/><span class="ui-nav-label">Critical Control Management</span>
        </a>
@endif
        @endif



        @if(Route::has('admin.hipo.index'))
        @if($user?->canUseFeatureRoute('admin.hipo.index'))
<a title="HIPO / Nearmiss" aria-label="HIPO / Nearmiss" href="{{ route('admin.hipo.index') }}"
          class="{{ $navItemClass(request()->routeIs('admin.hipo.*')) }}">
          <x-ui-icon name="warning"/><span class="ui-nav-label">HIPO / Nearmiss</span>
        </a>
@endif
        @endif

        @if(Route::has('admin.sites.index'))
        @if($user?->canUseFeatureRoute('admin.sites.index'))
<a title="Sites" aria-label="Sites" href="{{ route('admin.sites.index') }}" class="{{ $navItemClass(request()->routeIs('admin.sites.*')) }}"><x-ui-icon name="location"/><span class="ui-nav-label">Sites</span></a>
@endif
        @endif

        @if($isSuper && Route::has('admin.companies.index'))
        @if($user?->canUseFeatureRoute('admin.companies.index'))
<a title="Companies" aria-label="Companies" href="{{ route('admin.companies.index') }}" class="{{ $navItemClass(request()->routeIs('admin.companies.*')) }}"><x-ui-icon name="company"/><span class="ui-nav-label">Companies</span></a>
@endif
        @endif

        @if(Route::has('admin.groups.index'))
        @if($user?->canUseFeatureRoute('admin.groups.index'))
<a title="Indicator Groups" aria-label="Indicator Groups" href="{{ route('admin.groups.index') }}" class="{{ $navItemClass(request()->routeIs('admin.groups.*')) }}"><x-ui-icon name="groups"/><span class="ui-nav-label">Indicator Groups</span></a>
@endif
        @endif

        @if(Route::has('admin.indicators.index'))
        @if($user?->canUseFeatureRoute('admin.indicators.index'))
<a title="Indicators" aria-label="Indicators" href="{{ route('admin.indicators.index') }}" class="{{ $navItemClass(request()->routeIs('admin.indicators.*')) }}"><x-ui-icon name="chart"/><span class="ui-nav-label">Indicators</span></a>
@endif
        @endif

        @if(Route::has('admin.daily.create'))
        @if($user?->canUseFeatureRoute('admin.daily.create'))
<a title="Input Harian" aria-label="Input Harian" href="{{ route('admin.daily.create') }}" class="{{ $navItemClass(request()->routeIs('admin.daily.*')) }}"><x-ui-icon name="input"/><span class="ui-nav-label">Input Harian</span></a>
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

        @if(Route::has('admin.site_access.index'))
        @if($user?->canUseFeatureRoute('admin.site_access.index'))
<a title="Input Access" aria-label="Input Access" href="{{ route('admin.site_access.index') }}" class="{{ $navItemClass(request()->routeIs('admin.site_access.*')) }}"><x-ui-icon name="access"/><span class="ui-nav-label">Input Access</span></a>
@endif
        @endif
      </div>
    </div>

    {{-- ================= NON ADMIN ================= --}}
    @else

    <div x-data="{openMenu:true}">
      <button type="button" class="ui-nav-section px-3 text-xs normal-case tracking-normalr text-coal-500 flex justify-between cursor-pointer" :aria-expanded="openMenu" @click="openMenu=!openMenu">
        Menu
        <span aria-hidden="true" x-text="openMenu ? '−' : '+'"></span>
      </button>

      <div class="mt-2 grid gap-1" x-show="sidebarCollapsed || openMenu" x-transition>
        @if($user?->canUseFeatureRoute('dashboard'))
<a title="Beranda" aria-label="Beranda" href="{{ route('dashboard') }}" class="{{ $navItemClass(request()->routeIs('dashboard')) }}"><x-ui-icon name="dashboard"/><span class="ui-nav-label">Beranda</span></a>
@endif

        @if(Route::has('front.forms.index'))
        @if($user?->canUseFeatureRoute('front.forms.index'))
<a title="FORM" aria-label="FORM" href="{{ route('front.forms.index') }}" class="{{ $navItemClass(request()->routeIs('front.forms.*')) }}"><x-ui-icon name="mandala"/><span class="ui-nav-label">FORM</span></a>
@endif
        @endif

        @if($userContractsUrl)
        @if($user?->canUseFeature('contracts'))
<a title="Kontrak Saya" aria-label="Kontrak Saya" href="{{ $userContractsUrl }}" class="{{ $navItemClass(request()->routeIs('user.contracts.*') || request()->routeIs('contracts.*')) }}"><x-ui-icon name="contract"/><span class="ui-nav-label">Kontrak Saya</span></a>
@endif
        @endif

        @if(Route::has('admin.qa.index'))
        @if($user?->canUseFeatureRoute('admin.qa.index'))
<a title="Mandala Chat" aria-label="Mandala Chat" href="{{ route('admin.qa.index') }}" class="{{ $navItemClass(request()->routeIs('admin.qa.*')) }}"><x-ui-icon name="message-circle"/><span class="ui-nav-label">Mandala Chat</span></a>
@endif
        @endif
      </div>
    </div>

    <div x-data="{openHse:true}">
      <button type="button" class="ui-nav-section px-3 text-xs normal-case tracking-normalr text-coal-500 flex justify-between cursor-pointer" :aria-expanded="openHse" @click="openHse=!openHse">
        HSE / KPI
        <span aria-hidden="true" x-text="openHse ? '−' : '+'"></span>
      </button>

      <div class="mt-2 grid gap-1" x-show="sidebarCollapsed || openHse" x-transition>

        @if(Route::has('ccm-reports.create'))
        @if($user?->canUseFeatureRoute('ccm-reports.create'))
<a title="Input CCM" aria-label="Input CCM" href="{{ route('ccm-reports.create') }}"
          class="{{ $navItemClass(request()->routeIs('ccm-reports.*')) }}">
          <x-ui-icon name="shield"/><span class="ui-nav-label">Input CCM</span>
        </a>
@endif
        @endif


        @if(Route::has('daily.index'))
        @if($user?->canUseFeatureRoute('daily.index'))
<a title="Input Harian" aria-label="Input Harian" href="{{ route('daily.index') }}" class="{{ $navItemClass(request()->routeIs('daily.*')) }}"><x-ui-icon name="input"/><span class="ui-nav-label">Input Harian</span></a>
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
  </nav>


  </div>

</aside>



<div x-show="sidebarOpen && !isDesktop" x-cloak @click="sidebarOpen=false" class="fixed inset-0 z-40 bg-black/40 lg:hidden" aria-hidden="true"></div>
