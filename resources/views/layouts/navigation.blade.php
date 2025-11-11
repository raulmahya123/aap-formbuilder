{{-- resources/views/partials/navigation.blade.php --}}
@php
  use App\Models\Site;

  $user    = auth()->user();
  $isSuper = $user && method_exists($user,'isSuperAdmin') && $user->isSuperAdmin();
  $isAdmin = $isSuper || ($user && method_exists($user,'isAdmin') && $user->isAdmin());

  $homeUrl = Route::has('admin.dashboard') ? route('admin.dashboard') : url('/');

  $navItemClass = function(bool $active = false){
    $base = 'flex items-center gap-3 px-3 py-2 rounded-xl transition outline-none';
    $inactive = 'text-coal-800 border border-transparent hover:bg-ivory-100 focus-visible:ring-2 focus-visible:ring-maroon-700';
    $activeCls = 'is-active border';
    return $active ? "$base $activeCls" : "$base $inactive";
  };

  // daftar site utk badge & switcher
  $sites = collect();
  if ($user) {
    if ($isAdmin) {
      $sites = Site::select('id','code','name')->orderBy('code')->get();
    } elseif (method_exists($user, 'sites')) {
      $sites = $user->sites()->select('sites.id','sites.code','sites.name')->orderBy('sites.code')->get();
    }
  }
  $activeSiteId = session('active_site_id');
  $activeSite   = $activeSiteId ? $sites->firstWhere('id', $activeSiteId) : null;

  // Kontrak Saya URL (fallback)
  $userContractsUrl = null;
  if (Route::has('user.contracts.index')) {
    $userContractsUrl = route('user.contracts.index');
  } elseif (Route::has('contracts.index')) {
    $userContractsUrl = route('contracts.index');
  }
@endphp

{{-- ===== SIDEBAR (DESKTOP) ===== --}}
<aside class="nice-scroll fixed inset-y-0 left-0 z-50 w-72 -translate-x-full transition-transform duration-200
               lg:static lg:translate-x-0 lg:w-auto lg:block overflow-y-auto
               bg-ivory-50 border-r border-coal-200
               flex flex-col">

  {{-- USER --}}
  @auth
  <div class="px-4 py-4 border-b border-coal-200 bg-ivory-50/70">
    <div class="flex items-center gap-3">
      <div class="w-10 h-10 rounded-full bg-maroon-700 text-ivory-50 flex items-center justify-center text-sm font-semibold">
        {{ strtoupper(\Illuminate\Support\Str::of(auth()->user()->name)->explode(' ')->map(fn($s)=>\Illuminate\Support\Str::substr($s,0,1))->take(2)->implode('')) }}
      </div>
      <div class="min-w-0">
        <div class="text-sm font-semibold truncate">{{ auth()->user()->name }}</div>
        <div class="text-xs text-coal-500 truncate">{{ auth()->user()->email ?? '' }}</div>
      </div>
    </div>
  </div>
  @endauth

  {{-- MENU --}}
  <nav class="p-4 space-y-6 flex-1">
    @if($isAdmin)
      <div>
        <div class="px-3 text-xs uppercase tracking-wider text-coal-500">Menu</div>
        <div class="mt-2 grid gap-1">
          <a href="{{ route('admin.dashboard') }}" class="{{ $navItemClass(request()->routeIs('admin.dashboard')) }}">🏛️ Dashboard</a>

          @if(Route::has('admin.departments.index'))
            <a href="{{ route('admin.departments.index') }}" class="{{ $navItemClass(request()->routeIs('admin.departments.*')) }}">🏷️ Departments</a>
          @endif

          @if(Route::has('admin.forms.index'))
            <a href="{{ route('admin.forms.index') }}" class="{{ $navItemClass(request()->routeIs('admin.forms.*')) }}">🧾 Mandala</a>
          @endif

          @if(Route::has('admin.documents.index'))
            <a href="{{ route('admin.documents.index') }}" class="{{ $navItemClass(request()->routeIs('admin.documents.*')) }}">📄 Documents</a>
          @endif

          @if(($user && method_exists($user,'isSuperAdmin') && $user->isSuperAdmin()) || ($user && $user->department_id))
            @if(Route::has('admin.document_templates.index'))
              <a href="{{ route('admin.document_templates.index') }}" class="{{ $navItemClass(request()->routeIs('admin.document_templates.*')) }}">🧩 Doc Templates</a>
            @endif
          @endif

          @if(Route::has('admin.entries.index'))
            <a href="{{ route('admin.entries.index') }}" class="{{ $navItemClass(request()->routeIs('admin.entries.*')) }}">📥 Entries</a>
          @endif

          @if(Route::has('admin.qa.index'))
            <a href="{{ route('admin.qa.index') }}" class="{{ $navItemClass(request()->routeIs('admin.qa.*')) }}">💬 Tanya Jawab</a>
          @endif

          @if(Route::has('admin.contracts.index'))
            <a href="{{ route('admin.contracts.index') }}" class="{{ $navItemClass(request()->routeIs('admin.contracts.*')) }}">📑 Contracts</a>
          @endif

          @if($isSuper && Route::has('admin.users.active.index'))
            <a href="{{ route('admin.users.active.index') }}" class="{{ $navItemClass(request()->routeIs('admin.users.active.*')) }}">👥 Manage Users</a>
          @endif
        </div>
      </div>

      <div>
        <div class="px-3 text-xs uppercase tracking-wider text-coal-500">HSE / KPI</div>
        <div class="mt-2 grid gap-1">
          @if(Route::has('admin.sites.index'))
            <a href="{{ route('admin.sites.index') }}" class="{{ $navItemClass(request()->routeIs('admin.sites.*')) }}">📍 Sites</a>
          @endif

          {{-- 🆕 Companies (di bawah Sites) --}}
          @if($isSuper && Route::has('admin.companies.index'))
            <a href="{{ route('admin.companies.index') }}" class="{{ $navItemClass(request()->routeIs('admin.companies.*')) }}">🏢 Companies</a>
          @endif

          @if(Route::has('admin.groups.index'))
            <a href="{{ route('admin.groups.index') }}" class="{{ $navItemClass(request()->routeIs('admin.groups.*')) }}">🗂️ Indicator Groups</a>
          @endif
          @if(Route::has('admin.indicators.index'))
            <a href="{{ route('admin.indicators.index') }}" class="{{ $navItemClass(request()->routeIs('admin.indicators.*')) }}">📊 Indicators</a>
          @endif

          @if(Route::has('admin.daily.create'))
            <a href="{{ route('admin.daily.create') }}" class="{{ $navItemClass(request()->routeIs('admin.daily.*')) }}">✍️ Input Harian</a>
          @endif

          @if(Route::has('user.daily_notes.index'))
            <a href="{{ route('user.daily_notes.index') }}" class="{{ $navItemClass(request()->routeIs('user.daily_notes.*')) }}">📝 Catatan Harian</a>
          @endif

          @if(Route::has('admin.reports.monthly'))
            <a href="{{ route('admin.reports.monthly') }}" class="{{ $navItemClass(request()->routeIs('admin.reports.monthly')) }}">📈 Rekap Bulanan</a>
          @endif

          @if(Route::has('admin.site_access.index'))
            <a href="{{ route('admin.site_access.index') }}" class="{{ $navItemClass(request()->routeIs('admin.site_access.*')) }}">🛂 Input Access</a>
          @endif
        </div>
      </div>
    @else
      <div>
        <div class="px-3 text-xs uppercase tracking-wider text-coal-500">Menu</div>
        <div class="mt-2 grid gap-1">
          <a href="{{ route('admin.dashboard') }}" class="{{ $navItemClass(request()->routeIs('admin.dashboard')) }}">🏛️ Dashboard</a>

          @if(Route::has('front.forms.index'))
            <a href="{{ route('front.forms.index') }}" class="{{ $navItemClass(request()->routeIs('front.forms.*')) }}">🧾 FORM</a>
          @endif

          @if($userContractsUrl)
            <a href="{{ $userContractsUrl }}" class="{{ $navItemClass(request()->routeIs('user.contracts.*') || request()->routeIs('contracts.*')) }}">📑 Kontrak Saya</a>
          @endif

          @if(Route::has('admin.qa.index'))
            <a href="{{ route('admin.qa.index') }}" class="{{ $navItemClass(request()->routeIs('admin.qa.*')) }}">💬 Tanya Jawab</a>
          @endif
        </div>
      </div>

      <div class="mt-6">
        <div class="px-3 text-xs uppercase tracking-wider text-coal-500">HSE / KPI</div>
        <div class="mt-2 grid gap-1">
          @if(Route::has('daily.index'))
            <a href="{{ route('daily.index') }}" class="{{ $navItemClass(request()->routeIs('daily.*')) }}">✍️ Input Harian</a>
          @endif

          @if(Route::has('user.daily_notes.index'))
            <a href="{{ route('user.daily_notes.index') }}" class="{{ $navItemClass(request()->routeIs('user.daily_notes.*')) }}">📝 Catatan Harian</a>
          @endif

          @if(Route::has('admin.reports.monthly'))
            <a href="{{ route('admin.reports.monthly') }}" class="{{ $navItemClass(request()->routeIs('admin.reports.monthly')) }}">📈 Rekap Bulanan</a>
          @endif
        </div>
      </div>
    @endif
  </nav>

  {{-- ACTIVE SITE (desktop) --}}
  @if($sites->count() > 0)
  <div class="px-4 py-3 border-t border-coal-200">
    <div class="text-xs mb-2 text-coal-600 uppercase tracking-wider">Active Site</div>

    <div class="mb-2">
      <span class="inline-flex items-center gap-2 px-2 py-1 rounded-lg border
                      border-maroon-700 text-maroon-800 bg-maroon-50/70">
        📍 {{ $activeSite?->code ?? 'ALL' }}
        @if($activeSite && $activeSite->name)
          <span class="text-xs text-coal-500">— {{ $activeSite->name }}</span>
        @endif
      </span>
    </div>

    @if(Route::has('admin.sites.switch'))
    <form method="POST" action="{{ route('admin.sites.switch') }}" class="grid gap-2">
      @csrf
      <select name="site_id" class="border rounded-lg px-3 py-2 bg-white">
        <option value="">ALL SITES</option>
        @foreach($sites as $s)
          <option value="{{ $s->id }}" @selected($activeSiteId==$s->id)>{{ $s->code }} — {{ $s->name }}</option>
        @endforeach
      </select>
      <button class="px-3 py-2 rounded-lg border border-coal-300 hover:bg-ivory-100">
        Ganti Site
      </button>
    </form>
    @endif
  </div>
  @endif

  {{-- LOGOUT (BAWAH) --}}
  @auth
  <div class="p-4 border-t border-coal-200 bg-ivory-100">
    <form method="post" action="{{ route('logout') }}">@csrf
      <button class="w-full text-center text-sm rounded-lg px-3 py-2 border border-maroon-700 text-maroon-700 hover:bg-maroon-50">
        Logout
      </button>
    </form>
  </div>
  @endauth
</aside>

{{-- ===== MOBILE DRAWER ===== --}}
<div class="lg:hidden" x-cloak>
  <div x-show="sidebarOpen" @click="sidebarOpen=false" class="fixed inset-0 bg-black/40 backdrop-blur-sm z-40"></div>
  <div x-show="sidebarOpen" class="fixed inset-y-0 left-0 w-72 z-50">
    <div class="h-full shadow-2xl" @click.outside="sidebarOpen=false">
      <aside class="h-full bg-ivory-50 border-r border-coal-200 p-0 flex flex-col">

        @auth
        <div class="px-4 py-4 border-b border-coal-200">
          <div class="flex items-center gap-3">
            <div class="w-9 h-9 rounded-full bg-maroon-700 text-ivory-50 flex items-center justify-center text-xs font-semibold">
              {{ strtoupper(\Illuminate\Support\Str::of(auth()->user()->name)->explode(' ')->map(fn($s)=>\Illuminate\Support\Str::substr($s,0,1))->take(2)->implode('')) }}
            </div>
            <div class="min-w-0">
              <div class="text-sm font-semibold truncate">{{ auth()->user()->name }}</div>
              <div class="text-xs text-coal-500 truncate">{{ auth()->user()->email ?? '' }}</div>
            </div>
          </div>
        </div>
        @endauth

        @php
          // fallback URL kontrak utk mobile drawer juga
          $userContractsUrl = $userContractsUrl ?? null;
        @endphp

        <div class="p-4 grid gap-1 flex-1">
          @if($isAdmin)
            <a href="{{ route('admin.dashboard') }}" class="{{ $navItemClass(request()->routeIs('admin.dashboard')) }}">🏛️ Dashboard</a>

            @if(Route::has('admin.departments.index'))
              <a href="{{ route('admin.departments.index') }}" class="{{ $navItemClass(request()->routeIs('admin.departments.*')) }}">🏷️ Departments</a>
            @endif
            @if(Route::has('admin.forms.index'))
              <a href="{{ route('admin.forms.index') }}" class="{{ $navItemClass(request()->routeIs('admin.forms.*')) }}">🧾 Mandala</a>
            @endif
            @if(Route::has('admin.entries.index'))
              <a href="{{ route('admin.entries.index') }}" class="{{ $navItemClass(request()->routeIs('admin.entries.*')) }}">📥 Entries</a>
            @endif
            @if (Route::has('admin.qa.index'))
              <a href="{{ route('admin.qa.index') }}" class="{{ $navItemClass(request()->routeIs('admin.qa.*')) }}">💬 Tanya Jawab</a>
            @endif

            @if(Route::has('admin.contracts.index'))
              <a href="{{ route('admin.contracts.index') }}" class="{{ $navItemClass(request()->routeIs('admin.contracts.*')) }}">📑 Contracts</a>
            @endif

            @if($isSuper && Route::has('admin.users.active.index'))
              <a href="{{ route('admin.users.active.index') }}" class="{{ $navItemClass(request()->routeIs('admin.users.active.*')) }}">👥 Manage Users</a>
            @endif
          @else
            <a href="{{ route('admin.dashboard') }}" class="{{ $navItemClass(request()->routeIs('admin.dashboard')) }}">🏛️ Dashboard</a>

            @if(Route::has('front.forms.index'))
              <a href="{{ route('front.forms.index') }}" class="{{ $navItemClass(request()->routeIs('front.forms.*')) }}">🧾 FORM</a>
            @endif

            @if($userContractsUrl)
              <a href="{{ $userContractsUrl }}" class="{{ $navItemClass(request()->routeIs('user.contracts.*') || request()->routeIs('contracts.*')) }}">📑 Kontrak Saya</a>
            @endif

            @if(Route::has('admin.qa.index'))
              <a href="{{ route('admin.qa.index') }}" class="{{ $navItemClass(request()->routeIs('admin.qa.*')) }}">💬 Tanya Jawab</a>
            @endif
          @endif
        </div>

        <div class="p-4 pt-0">
          <div class="px-1 text-xs uppercase tracking-wider text-coal-500">HSE / KPI</div>
          <div class="mt-2 grid gap-1">
            @if($isAdmin)
              @if(Route::has('admin.sites.index'))
                <a href="{{ route('admin.sites.index') }}" class="{{ $navItemClass(request()->routeIs('admin.sites.*')) }}">📍 Sites</a>
              @endif

              {{-- 🆕 Companies (di bawah Sites - mobile) --}}
              @if($isSuper && Route::has('admin.companies.index'))
                <a href="{{ route('admin.companies.index') }}" class="{{ $navItemClass(request()->routeIs('admin.companies.*')) }}">🏢 Companies</a>
              @endif

              @if(Route::has('admin.groups.index'))
                <a href="{{ route('admin.groups.index') }}" class="{{ $navItemClass(request()->routeIs('admin.groups.*')) }}">🗂️ Indicator Groups</a>
              @endif
              @if(Route::has('admin.indicators.index'))
                <a href="{{ route('admin.indicators.index') }}" class="{{ $navItemClass(request()->routeIs('admin.indicators.*')) }}">📊 Indicators</a>
              @endif
              @if(Route::has('admin.daily.create'))
                <a href="{{ route('admin.daily.create') }}" class="{{ $navItemClass(request()->routeIs('admin.daily.*')) }}">✍️ Input Harian</a>
              @endif
              @if(Route::has('user.daily_notes.index'))
                <a href="{{ route('user.daily_notes.index') }}" class="{{ $navItemClass(request()->routeIs('user.daily_notes.*')) }}">📝 Catatan Harian</a>
              @endif
              @if(Route::has('admin.reports.monthly'))
                <a href="{{ route('admin.reports.monthly') }}" class="{{ $navItemClass(request()->routeIs('admin.reports.monthly')) }}">📈 Rekap Bulanan</a>
              @endif
              @if($isSuper && Route::has('admin.site_access.index'))
                <a href="{{ route('admin.site_access.index') }}" class="{{ $navItemClass(request()->routeIs('admin.site_access.*')) }}">🛂 Input Access</a>
              @endif
            @else
              @if(Route::has('daily.index'))
                <a href="{{ route('daily.index') }}" class="{{ $navItemClass(request()->routeIs('daily.*')) }}">✍️ Input Harian</a>
              @endif
              @if(Route::has('user.daily_notes.index'))
                <a href="{{ route('user.daily_notes.index') }}" class="{{ $navItemClass(request()->routeIs('user.daily_notes.*')) }}">📝 Catatan Harian</a>
              @endif
              @if(Route::has('admin.reports.monthly'))
                <a href="{{ route('admin.reports.monthly') }}" class="{{ $navItemClass(request()->routeIs('admin.reports.monthly')) }}">📈 Rekap Bulanan</a>
              @endif
            @endif
          </div>
        </div>

        @if($sites->count() > 0)
        <div class="p-4 border-t border-coal-200">
          <div class="text-xs mb-2 text-coal-600 uppercase tracking-wider">Active Site</div>

          <div class="mb-2">
            <span class="inline-flex items-center gap-2 px-2 py-1 rounded-lg border
                            border-maroon-700 text-maroon-800 bg-maroon-50/70">
              📍 {{ $activeSite?->code ?? 'ALL' }}
              @if($activeSite && $activeSite->name)
                <span class="text-xs text-coal-500">— {{ $activeSite->name }}</span>
              @endif
            </span>
          </div>

          @if(Route::has('admin.sites.switch'))
          <form method="POST" action="{{ route('admin.sites.switch') }}" class="grid gap-2">
            @csrf
            <select name="site_id" class="border rounded-lg px-3 py-2 bg-white">
              <option value="">ALL SITES</option>
              @foreach($sites as $s)
                <option value="{{ $s->id }}" @selected($activeSiteId==$s->id)>{{ $s->code }} — {{ $s->name }}</option>
              @endforeach
            </select>
            <button class="px-3 py-2 rounded-lg border border-coal-300 hover:bg-ivory-100">
              Ganti Site
            </button>
          </form>
          @endif
        </div>
        @endif

        @auth
        <div class="p-4 border-t border-coal-200 bg-ivory-100">
          <form method="post" action="{{ route('logout') }}">@csrf
            <button class="w-full text-center text-sm rounded-lg px-3 py-2 border border-maroon-700 text-maroon-700 hover:bg-maroon-50">
              Logout
            </button>
          </form>
        </div>
        @endauth
      </aside>
    </div>
  </div>
</div>
