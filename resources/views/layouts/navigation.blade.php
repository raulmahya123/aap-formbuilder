{{-- resources/views/partials/navigation.blade.php --}}
@php
use App\Models\Site;
use Illuminate\Support\Str;

$user = auth()->user();
$isSuper = $user && method_exists($user,'isSuperAdmin') && $user->isSuperAdmin();
$isAdmin = $isSuper || ($user && method_exists($user,'isAdmin') && $user->isAdmin());

$homeUrl = Route::has('admin.dashboard') ? route('admin.dashboard') : url('/');

$navItemClass = function(bool $active = false){
$base = 'flex items-center gap-3 px-3 py-2 rounded-xl transition outline-none';
$inactive = 'text-coal-800 border border-transparent hover:bg-ivory-100 focus-visible:ring-2 focus-visible:ring-maroon-700';
$activeCls = 'is-active border';
return $active ? "$base $activeCls" : "$base $inactive";
};

$sites = collect();
if ($user) {
if ($isSuper) {
$sites = Site::select('id','code','name')->orderBy('code')->get();
} elseif (method_exists($user, 'sites')) {
$sites = $user->sites()->select('sites.id','sites.code','sites.name')->orderBy('sites.code')->get();
}
}

$activeSiteId = session('active_site_id');
$activeSite = $activeSiteId ? $sites->firstWhere('id', $activeSiteId) : null;

$userContractsUrl = null;
if (Route::has('user.contracts.index')) $userContractsUrl = route('user.contracts.index');
elseif (Route::has('contracts.index')) $userContractsUrl = route('contracts.index');
@endphp


{{-- ===== SIDEBAR (DESKTOP) ===== --}}
<aside
  :class="{ 'translate-x-0': sidebarOpen }"
  class="nice-scroll fixed inset-y-0 left-0 z-50 w-72 -translate-x-full transition-transform duration-200
               lg:static lg:translate-x-0 lg:w-auto lg:block overflow-y-auto
               bg-ivory-50 border-r border-coal-200
               flex flex-col">

  {{-- Icon rail ketika sidebar diciutkan --}}
  <div x-show="sidebarCollapsed" x-cloak class="hidden h-full overflow-y-auto lg:flex flex-col items-center gap-2 p-3">
    <button type="button" @click="toggleSidebarCollapse()" class="inline-flex h-10 w-10 items-center justify-center rounded-xl bg-maroon-700 text-white shadow-sm hover:bg-maroon-800" title="Buka sidebar" aria-label="Buka sidebar">
      <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 12h16M4 18h16"/></svg>
    </button>
    <a href="{{ route('admin.dashboard') }}" class="inline-flex h-10 w-10 items-center justify-center rounded-xl hover:bg-ivory-100" title="Dashboard" aria-label="Dashboard"><x-ui-icon name="dashboard"/></a>
    @if(Route::has('admin.wellness.index'))<a href="{{ route('admin.wellness.index') }}" class="inline-flex h-10 w-10 items-center justify-center rounded-xl hover:bg-ivory-100" title="Wellness" aria-label="Wellness"><x-ui-icon name="wellness"/></a>@endif
    @if(Route::has('admin.forms.index'))<a href="{{ route('admin.forms.index') }}" class="inline-flex h-10 w-10 items-center justify-center rounded-xl hover:bg-ivory-100" title="Mandala" aria-label="Mandala"><x-ui-icon name="mandala"/></a>@endif
    @if(Route::has('admin.hipo.index'))<a href="{{ route('admin.hipo.index') }}" class="inline-flex h-10 w-10 items-center justify-center rounded-xl hover:bg-ivory-100" title="HIPO / Nearmiss" aria-label="HIPO / Nearmiss"><x-ui-icon name="warning"/></a>@endif
    @if(Route::has('ccm-reports.index'))<a href="{{ route('ccm-reports.index') }}" class="inline-flex h-10 w-10 items-center justify-center rounded-xl hover:bg-ivory-100" title="Critical Control" aria-label="Critical Control"><x-ui-icon name="shield"/></a>@endif
    @if(Route::has('admin.sites.index'))<a href="{{ route('admin.sites.index') }}" class="inline-flex h-10 w-10 items-center justify-center rounded-xl hover:bg-ivory-100" title="Sites" aria-label="Sites"><x-ui-icon name="location"/></a>@endif
    @if(Route::has('admin.daily.create'))<a href="{{ route('admin.daily.create') }}" class="inline-flex h-10 w-10 items-center justify-center rounded-xl hover:bg-ivory-100" title="Input Harian" aria-label="Input Harian"><x-ui-icon name="input"/></a>@endif
    @if(Route::has('user.daily_notes.index'))<a href="{{ route('user.daily_notes.index') }}" class="inline-flex h-10 w-10 items-center justify-center rounded-xl hover:bg-ivory-100" title="Catatan Harian" aria-label="Catatan Harian"><x-ui-icon name="note"/></a>@endif
    @if(Route::has('admin.reports.monthly'))<a href="{{ route('admin.reports.monthly') }}" class="inline-flex h-10 w-10 items-center justify-center rounded-xl hover:bg-ivory-100" title="Rekap Bulanan" aria-label="Rekap Bulanan"><x-ui-icon name="report"/></a>@endif
    @if($isSuper && Route::has('admin.site_access.index'))<a href="{{ route('admin.site_access.index') }}" class="inline-flex h-10 w-10 items-center justify-center rounded-xl hover:bg-ivory-100" title="Site access" aria-label="Site access"><x-ui-icon name="access"/></a>@endif
  </div>

  <div x-show="!sidebarCollapsed" x-cloak class="flex min-h-0 flex-1 flex-col">

  {{-- USER --}}
  @auth
  <div class="px-4 py-4 border-b border-coal-200 bg-ivory-50/70">
    <div class="flex items-center gap-3">
      <div class="w-10 h-10 rounded-full bg-maroon-700 text-ivory-50 flex items-center justify-center text-sm font-semibold">
        {{ strtoupper(Str::of(auth()->user()->name)->explode(' ')->map(fn($s)=>Str::substr($s,0,1))->take(2)->implode('')) }}
      </div>
      <div class="min-w-0">
        <div class="text-sm font-semibold truncate">{{ auth()->user()->name }}</div>
        <div class="text-xs text-coal-500 truncate">{{ auth()->user()->email ?? '' }}</div>
      </div>
    </div>
    <button type="button" @click="toggleSidebarCollapse()" class="mt-4 hidden w-full items-center justify-center gap-2 rounded-xl border border-coal-200 px-3 py-2 text-xs text-coal-600 hover:bg-white lg:flex" title="Ciutkan sidebar">
      <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M11 5l-7 7 7 7M4 12h16"/></svg>
      Ciutkan menu
    </button>
  </div>
  @endauth


  {{-- MENU --}}
  <nav class="p-4 space-y-6 flex-1">

    {{-- ================= ADMIN ================= --}}
    @if($isAdmin)

    {{-- MENU COLLAPSE --}}
    <div x-data="{openMenu:true}">
      <div class="px-3 text-xs uppercase tracking-wider text-coal-500 flex justify-between cursor-pointer"
        @click="openMenu=!openMenu">
        Menu
        <span x-text="openMenu ? '−' : '+'"></span>
      </div>

      <div class="mt-2 grid gap-1" x-show="openMenu" x-transition>
        <a href="{{ route('admin.dashboard') }}" class="{{ $navItemClass(request()->routeIs('admin.dashboard')) }}"><x-ui-icon name="dashboard"/> Dashboard</a>

        @if(Route::has('admin.wellness.index'))
        <a href="{{ route('admin.wellness.index') }}" class="{{ $navItemClass(request()->routeIs('admin.wellness.*')) }}"><x-ui-icon name="wellness"/> Wellness</a>
        @endif

        @if(Route::has('admin.departments.index'))
        <a href="{{ route('admin.departments.index') }}" class="{{ $navItemClass(request()->routeIs('admin.departments.*')) }}"><x-ui-icon name="department"/> Departments</a>
        @endif

        @if(Route::has('admin.forms.index'))
        <a href="{{ route('admin.forms.index') }}" class="{{ $navItemClass(request()->routeIs('admin.forms.*')) }}"><x-ui-icon name="mandala"/> Mandala</a>
        @endif

        @if(Route::has('admin.qa.index'))
        <a href="{{ route('admin.qa.index') }}" class="{{ $navItemClass(request()->routeIs('admin.qa.*')) }}"><x-ui-icon name="chat"/> Tanya Jawab</a>
        @endif

        @if(Route::has('admin.contracts.index'))
        <a href="{{ route('admin.contracts.index') }}" class="{{ $navItemClass(request()->routeIs('admin.contracts.*')) }}"><x-ui-icon name="contract"/> Contracts</a>
        @endif

        @if($isSuper && Route::has('admin.users.active.index'))
        <a href="{{ route('admin.users.active.index') }}" class="{{ $navItemClass(request()->routeIs('admin.users.active.*')) }}"><x-ui-icon name="users"/> Manage Users</a>
        @endif
      </div>
    </div>


    {{-- HSE COLLAPSE --}}
    <div x-data="{openHse:true}">
      <div class="px-3 text-xs uppercase tracking-wider text-coal-500 flex justify-between cursor-pointer"
        @click="openHse=!openHse">
        HSE / KPI
        <span x-text="openHse ? '−' : '+'"></span>
      </div>

      <div class="mt-2 grid gap-1" x-show="openHse" x-transition>

        @if(Route::has('ccm-reports.index'))
        <a href="{{ route('ccm-reports.index') }}"
          class="{{ $navItemClass(request()->routeIs('admin.ccm-reports.*')) }}">
          <x-ui-icon name="shield"/> Critical Control Management
        </a>
        @endif



        @if(Route::has('admin.hipo.index'))
        <a href="{{ route('admin.hipo.index') }}"
          class="{{ $navItemClass(request()->routeIs('admin.hipo.*')) }}">
          <x-ui-icon name="warning"/> HIPO / Nearmiss
        </a>
        @endif

        @if(Route::has('admin.sites.index'))
        <a href="{{ route('admin.sites.index') }}" class="{{ $navItemClass(request()->routeIs('admin.sites.*')) }}"><x-ui-icon name="location"/> Sites</a>
        @endif

        @if($isSuper && Route::has('admin.companies.index'))
        <a href="{{ route('admin.companies.index') }}" class="{{ $navItemClass(request()->routeIs('admin.companies.*')) }}"><x-ui-icon name="company"/> Companies</a>
        @endif

        @if(Route::has('admin.groups.index'))
        <a href="{{ route('admin.groups.index') }}" class="{{ $navItemClass(request()->routeIs('admin.groups.*')) }}"><x-ui-icon name="groups"/> Indicator Groups</a>
        @endif

        @if(Route::has('admin.indicators.index'))
        <a href="{{ route('admin.indicators.index') }}" class="{{ $navItemClass(request()->routeIs('admin.indicators.*')) }}"><x-ui-icon name="chart"/> Indicators</a>
        @endif

        @if(Route::has('admin.daily.create'))
        <a href="{{ route('admin.daily.create') }}" class="{{ $navItemClass(request()->routeIs('admin.daily.*')) }}"><x-ui-icon name="input"/> Input Harian</a>
        @endif

        @if(Route::has('user.daily_notes.index'))
        <a href="{{ route('user.daily_notes.index') }}" class="{{ $navItemClass(request()->routeIs('user.daily_notes.*')) }}"><x-ui-icon name="note"/> Catatan Harian</a>
        @endif

        @if(Route::has('admin.reports.monthly'))
        <a href="{{ route('admin.reports.monthly') }}" class="{{ $navItemClass(request()->routeIs('admin.reports.monthly')) }}"><x-ui-icon name="report"/> Rekap Bulanan</a>
        @endif

        @if(Route::has('admin.site_access.index'))
        <a href="{{ route('admin.site_access.index') }}" class="{{ $navItemClass(request()->routeIs('admin.site_access.*')) }}"><x-ui-icon name="access"/> Input Access</a>
        @endif
      </div>
    </div>

    {{-- ================= NON ADMIN ================= --}}
    @else

    <div x-data="{openMenu:true}">
      <div class="px-3 text-xs uppercase tracking-wider text-coal-500 flex justify-between cursor-pointer"
        @click="openMenu=!openMenu">
        Menu
        <span x-text="openMenu ? '−' : '+'"></span>
      </div>

      <div class="mt-2 grid gap-1" x-show="openMenu" x-transition>
        <a href="{{ route('dashboard') }}" class="{{ $navItemClass(request()->routeIs('dashboard')) }}"><x-ui-icon name="dashboard"/> Beranda</a>

        @if(Route::has('front.forms.index'))
        <a href="{{ route('front.forms.index') }}" class="{{ $navItemClass(request()->routeIs('front.forms.*')) }}"><x-ui-icon name="mandala"/> FORM</a>
        @endif

        @if($userContractsUrl)
        <a href="{{ $userContractsUrl }}" class="{{ $navItemClass(request()->routeIs('user.contracts.*') || request()->routeIs('contracts.*')) }}"><x-ui-icon name="contract"/> Kontrak Saya</a>
        @endif

        @if(Route::has('admin.qa.index'))
        <a href="{{ route('admin.qa.index') }}" class="{{ $navItemClass(request()->routeIs('admin.qa.*')) }}"><x-ui-icon name="chat"/> Tanya Jawab</a>
        @endif
      </div>
    </div>

    <div x-data="{openHse:true}">
      <div class="px-3 text-xs uppercase tracking-wider text-coal-500 flex justify-between cursor-pointer"
        @click="openHse=!openHse">
        HSE / KPI
        <span x-text="openHse ? '−' : '+'"></span>
      </div>

      <div class="mt-2 grid gap-1" x-show="openHse" x-transition>

        @if(Route::has('ccm-reports.create'))
        <a href="{{ route('ccm-reports.create') }}"
          class="{{ $navItemClass(request()->routeIs('.ccm-rebeports.create')) }}">
          <x-ui-icon name="shield"/> Input CCM
        </a>
        @endif


        @if(Route::has('daily.index'))
        <a href="{{ route('daily.index') }}" class="{{ $navItemClass(request()->routeIs('daily.*')) }}"><x-ui-icon name="input"/> Input Harian</a>
        @endif

        @if(Route::has('user.daily_notes.index'))
        <a href="{{ route('user.daily_notes.index') }}" class="{{ $navItemClass(request()->routeIs('user.daily_notes.*')) }}"><x-ui-icon name="note"/> Catatan Harian</a>
        @endif

        @if(Route::has('admin.reports.monthly'))
        <a href="{{ route('admin.reports.monthly') }}" class="{{ $navItemClass(request()->routeIs('admin.reports.monthly')) }}"><x-ui-icon name="report"/> Rekap Bulanan</a>
        @endif
      </div>
    </div>
    @endif
  </nav>


  </div>

</aside>



{{-- ================= MOBILE DRAWER ================= --}}
<div class="lg:hidden" x-cloak>
  <div x-show="sidebarOpen" @click="sidebarOpen=false" class="fixed inset-0 bg-black/40 backdrop-blur-sm z-40"></div>

  <div x-show="sidebarOpen" class="fixed inset-y-0 left-0 w-72 z-50">
    <div class="h-full shadow-2xl" @click.outside="sidebarOpen=false">

      <aside class="h-full bg-ivory-50 border-r border-coal-200 p-0 flex flex-col">

        @auth
        <div class="px-4 py-4 border-b border-coal-200">
          <div class="flex items-center gap-3">
            <div class="w-9 h-9 rounded-full bg-maroon-700 text-ivory-50 flex items-center justify-center text-xs font-semibold">
              {{ strtoupper(Str::of(auth()->user()->name)->explode(' ')->map(fn($s)=>Str::substr($s,0,1))->take(2)->implode('')) }}
            </div>
            <div class="min-w-0">
              <div class="text-sm font-semibold truncate">{{ auth()->user()->name }}</div>
              <div class="text-xs text-coal-500 truncate">{{ auth()->user()->email ?? '' }}</div>
            </div>
          </div>
        </div>
        @endauth


        {{-- MOBILE MENU --}}
        <div class="p-4 grid gap-1 flex-1">

          @if($isAdmin)

          <div x-data="{openMenu:true}">
            <div class="px-1 text-xs uppercase tracking-wider text-coal-500 flex justify-between cursor-pointer"
              @click="openMenu=!openMenu">
              Menu
              <span x-text="openMenu ? '−' : '+'"></span>
            </div>

            <div class="mt-2 grid gap-1" x-show="openMenu" x-transition>
              <a href="{{ route('admin.dashboard') }}" class="{{ $navItemClass(request()->routeIs('admin.dashboard')) }}"><x-ui-icon name="dashboard"/> Dashboard</a>

              @if(Route::has('admin.wellness.index'))
              <a href="{{ route('admin.wellness.index') }}" class="{{ $navItemClass(request()->routeIs('admin.wellness.*')) }}"><x-ui-icon name="wellness"/> Wellness</a>
              @endif

              @if(Route::has('admin.departments.index'))
              <a href="{{ route('admin.departments.index') }}" class="{{ $navItemClass(request()->routeIs('admin.departments.*')) }}"><x-ui-icon name="department"/> Departments</a>
              @endif

              @if(Route::has('admin.forms.index'))
              <a href="{{ route('admin.forms.index') }}" class="{{ $navItemClass(request()->routeIs('admin.forms.*')) }}"><x-ui-icon name="mandala"/> Mandala</a>
              @endif

              @if(Route::has('admin.qa.index'))
              <a href="{{ route('admin.qa.index') }}" class="{{ $navItemClass(request()->routeIs('admin.qa.*')) }}"><x-ui-icon name="chat"/> Tanya Jawab</a>
              @endif

              @if(Route::has('admin.contracts.index'))
              <a href="{{ route('admin.contracts.index') }}" class="{{ $navItemClass(request()->routeIs('admin.contracts.*')) }}"><x-ui-icon name="contract"/> Contracts</a>
              @endif

              @if($isSuper && Route::has('admin.users.active.index'))
              <a href="{{ route('admin.users.active.index') }}" class="{{ $navItemClass(request()->routeIs('admin.users.active.*')) }}"><x-ui-icon name="users"/> Manage Users</a>
              @endif
            </div>
          </div>


          <div x-data="{openHse:true}" class="mt-4">
            <div class="px-1 text-xs uppercase tracking-wider text-coal-500 flex justify-between cursor-pointer"
              @click="openHse=!openHse">
              HSE / KPI
              <span x-text="openHse ? '−' : '+'"></span>
            </div>

            <div class="mt-2 grid gap-1" x-show="openHse" x-transition>

              @if(Route::has('admin.sites.index'))
              <a href="{{ route('admin.sites.index') }}" class="{{ $navItemClass(request()->routeIs('admin.sites.*')) }}"><x-ui-icon name="location"/> Sites</a>
              @endif

              @if($isSuper && Route::has('admin.companies.index'))
              <a href="{{ route('admin.companies.index') }}" class="{{ $navItemClass(request()->routeIs('admin.companies.*')) }}"><x-ui-icon name="company"/> Companies</a>
              @endif

              @if(Route::has('admin.groups.index'))
              <a href="{{ route('admin.groups.index') }}" class="{{ $navItemClass(request()->routeIs('admin.groups.*')) }}"><x-ui-icon name="groups"/> Indicator Groups</a>
              @endif

              @if(Route::has('admin.indicators.index'))
              <a href="{{ route('admin.indicators.index') }}" class="{{ $navItemClass(request()->routeIs('admin.indicators.*')) }}"><x-ui-icon name="chart"/> Indicators</a>
              @endif

              @if(Route::has('admin.daily.create'))
              <a href="{{ route('admin.daily.create') }}" class="{{ $navItemClass(request()->routeIs('admin.daily.*')) }}"><x-ui-icon name="input"/> Input Harian</a>
              @endif

              @if(Route::has('user.daily_notes.index'))
              <a href="{{ route('user.daily_notes.index') }}" class="{{ $navItemClass(request()->routeIs('user.daily_notes.*')) }}"><x-ui-icon name="note"/> Catatan Harian</a>
              @endif

              @if(Route::has('admin.reports.monthly'))
              <a href="{{ route('admin.reports.monthly') }}" class="{{ $navItemClass(request()->routeIs('admin.reports.monthly')) }}"><x-ui-icon name="report"/> Rekap Bulanan</a>
              @endif

              @if($isSuper && Route::has('admin.site_access.index'))
              <a href="{{ route('admin.site_access.index') }}" class="{{ $navItemClass(request()->routeIs('admin.site_access.*')) }}"><x-ui-icon name="access"/> Input Access</a>
              @endif
            </div>
          </div>

          {{-- NON ADMIN MOBILE --}}
          @else

          <div x-data="{openMenu:true}">
            <div class="px-1 text-xs uppercase tracking-wider text-coal-500 flex justify-between cursor-pointer"
              @click="openMenu=!openMenu">
              Menu
              <span x-text="openMenu ? '−' : '+'"></span>
            </div>

            <div class="mt-2 grid gap-1" x-show="openMenu" x-transition>
              <a href="{{ route('dashboard') }}" class="{{ $navItemClass(request()->routeIs('dashboard')) }}"><x-ui-icon name="dashboard"/> Beranda</a>

              @if(Route::has('front.forms.index'))
              <a href="{{ route('front.forms.index') }}" class="{{ $navItemClass(request()->routeIs('front.forms.*')) }}"><x-ui-icon name="mandala"/> FORM</a>
              @endif

              @if($userContractsUrl)
              <a href="{{ $userContractsUrl }}" class="{{ $navItemClass(request()->routeIs('user.contracts.*') || request()->routeIs('contracts.*')) }}"><x-ui-icon name="contract"/> Kontrak Saya</a>
              @endif

              @if(Route::has('admin.qa.index'))
              <a href="{{ route('admin.qa.index') }}" class="{{ $navItemClass(request()->routeIs('admin.qa.*')) }}"><x-ui-icon name="chat"/> Tanya Jawab</a>
              @endif
            </div>
          </div>


          <div x-data="{openHse:true}" class="mt-4">
            <div class="px-1 text-xs uppercase tracking-wider text-coal-500 flex justify-between cursor-pointer"
              @click="openHse=!openHse">
              HSE / KPI
              <span x-text="openHse ? '−' : '+'"></span>
            </div>

            <div class="mt-2 grid gap-1" x-show="openHse" x-transition>
              @if(Route::has('admin.hipo.index'))
              <a href="{{ route('admin.hipo.index') }}"
                class="{{ $navItemClass(request()->routeIs('admin.hipo.*')) }}">
                <x-ui-icon name="warning"/> HIPO / Nearmiss
              </a>
              @endif

              @if(Route::has('daily.index'))
              <a href="{{ route('daily.index') }}" class="{{ $navItemClass(request()->routeIs('daily.*')) }}"><x-ui-icon name="input"/> Input Harian</a>
              @endif

              @if(Route::has('user.daily_notes.index'))
              <a href="{{ route('user.daily_notes.index') }}" class="{{ $navItemClass(request()->routeIs('user.daily_notes.*')) }}"><x-ui-icon name="note"/> Catatan Harian</a>
              @endif

              @if(Route::has('admin.reports.monthly'))
              <a href="{{ route('admin.reports.monthly') }}" class="{{ $navItemClass(request()->routeIs('admin.reports.monthly')) }}"><x-ui-icon name="report"/> Rekap Bulanan</a>
              @endif
            </div>
          </div>

          @endif
        </div>


      </aside>

    </div>
  </div>
</div>
