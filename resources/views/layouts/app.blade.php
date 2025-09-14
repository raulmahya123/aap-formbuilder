<!doctype html>
<html lang="{{ str_replace('_','-',app()->getLocale()) }}"
  x-data="layout()"
  x-init="init()"
  :class="dark ? 'dark scroll-smooth' : 'scroll-smooth'">

<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <meta name="csrf-token" content="{{ csrf_token() }}">
  <title>@yield('title', config('app.name','AAP FormBuilder'))</title>

  {{-- Fonts: Poppins --}}
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600&display=swap" rel="stylesheet">

  @vite(['resources/css/app.css','resources/js/app.js'])
  @stack('styles')

  <script>
    function layout() {
      return {
        dark: localStorage.getItem('theme') === 'dark',
        sidebarOpen: false,
        init() {
          document.documentElement.classList.toggle('dark', this.dark);
          this.$watch('dark', v => {
            document.documentElement.classList.toggle('dark', v);
            localStorage.setItem('theme', v ? 'dark' : 'light');
          });
        },
        toggleSidebar() { this.sidebarOpen = !this.sidebarOpen; },
        userInitials(name) {
          if (!name) return 'U';
          return name.split(' ').map(s => s[0]).slice(0, 2).join('').toUpperCase();
        }
      }
    }
  </script>

  <style>
    :root { --font-sans: 'Poppins', ui-sans-serif, system-ui, -apple-system, Segoe UI, Roboto, 'Helvetica Neue', Arial, 'Noto Sans'; }
    body { font-family: var(--font-sans); }

    .nice-scroll::-webkit-scrollbar { width: 8px; height: 8px }
    .nice-scroll::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 8px }
    .nice-scroll::-webkit-scrollbar-track { background: transparent }

    /* Aktif maroon */
    aside nav a.is-active {
      background-color: #7b1c1c !important;
      color: #fff !important;
      border-color: #7b1c1c !important;
    }
    aside nav a:is(:active, :focus):not(.is-active) {
      color: inherit !important;
      background-color: inherit !important;
      border-color: transparent !important;
      box-shadow: none !important;
      outline: none !important;
    }
  </style>
</head>

<body class="bg-ivory-100 dark:bg-coal-900 text-coal-900 dark:text-ivory-100 min-h-screen">

  {{-- ===== MOBILE TOPBAR ===== --}}
  <div class="lg:hidden sticky top-0 z-40 bg-ivory-50/90 dark:bg-coal-900/80 backdrop-blur border-b border-coal-200/60 dark:border-coal-800">
    <div class="max-w-7xl mx-auto px-4 py-3 flex items-center gap-3">
      <button @click="toggleSidebar()" class="p-2 rounded-lg border border-coal-200 dark:border-coal-700">
        <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" viewBox="0 0 24 24" fill="currentColor"><path d="M3 6h18M3 12h18M3 18h18"/></svg>
      </button>

      {{-- ===== ROLE HELPERS ===== --}}
      @php
        use App\Models\Site;

        $user    = auth()->user();
        $isSuper = $user && method_exists($user,'isSuperAdmin') && $user->isSuperAdmin();
        $isAdmin = $isSuper || ($user && method_exists($user,'isAdmin') && $user->isAdmin());
        $isBasic = $user && !$isAdmin;

        $homeUrl = Route::has('admin.dashboard') ? route('admin.dashboard') : url('/');

        $navItemClass = function(bool $active = false){
          $base = 'flex items-center gap-3 px-3 py-2 rounded-xl transition outline-none';
          $inactive = 'text-coal-800 dark:text-ivory-100 border border-transparent hover:bg-ivory-100 dark:hover:bg-coal-900 focus-visible:ring-2 focus-visible:ring-maroon-700';
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

        // === Kontrak Saya URL (fallback)
        $userContractsUrl = null;
        if (Route::has('user.contracts.index')) {
          $userContractsUrl = route('user.contracts.index');
        } elseif (Route::has('contracts.index')) {
          $userContractsUrl = route('contracts.index');
        }
      @endphp

      <nav class="p-4 space-y-6 flex-1">
        {{-- ========================= MENU ========================= --}}
        <div>
          <div class="px-3 text-xs uppercase tracking-wider text-coal-500 dark:text-coal-300">Menu</div>

          {{-- ADMIN --}}
          @if($isAdmin)
            <div class="mt-2 grid gap-1">
              <a href="{{ route('admin.dashboard') }}" class="{{ $navItemClass(request()->routeIs('admin.dashboard')) }}">🏛️ Dashboard</a>

              @if(Route::has('admin.departments.index'))
                <a href="{{ route('admin.departments.index') }}" class="{{ $navItemClass(request()->routeIs('admin.departments.*')) }}">🏷️ Departments</a>
              @endif

              @if(Route::has('admin.forms.index'))
                <a href="{{ route('admin.forms.index') }}" class="{{ $navItemClass(request()->routeIs('admin.forms.*')) }}">🧾 Forms</a>
              @endif

              @if(Route::has('admin.documents.index'))
                <a href="{{ route('admin.documents.index') }}" class="{{ $navItemClass(request()->routeIs('admin.documents.*')) }}">📄 Documents</a>
              @endif

              @if(($isSuper) || ($user && $user->department_id))
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
            </div>

          {{-- USER BIASA --}}
          @else
            <div class="mt-2 grid gap-1">
              <a href="{{ route('admin.dashboard') }}" class="{{ $navItemClass(request()->routeIs('admin.dashboard')) }}">🏛️ Dashboard</a>

              @if(Route::has('front.forms.index'))
                <a href="{{ route('front.forms.index') }}" class="{{ $navItemClass(request()->routeIs('front.forms.*')) }}">🧾 FORM</a>
              @endif

              @if($userContractsUrl)
                <a href="{{ $userContractsUrl }}"
                   class="{{ $navItemClass(request()->routeIs('user.contracts.*') || request()->routeIs('contracts.*')) }}">
                  📑 Kontrak Saya
                </a>
              @endif

              @if(Route::has('admin.qa.index'))
                <a href="{{ route('admin.qa.index') }}" class="{{ $navItemClass(request()->routeIs('admin.qa.*')) }}">💬 Tanya Jawab</a>
              @endif
            </div>
          @endif
        </div>

        {{-- ========================= HSE / KPI ========================= --}}
        <div>
          <div class="px-3 text-xs uppercase tracking-wider text-coal-500 dark:text-coal-300">HSE / KPI</div>
          <div class="mt-2 grid gap-1">

            {{-- Master Data: ADMIN only --}}
            @if($isAdmin)
              @if(Route::has('admin.sites.index'))
                <a href="{{ route('admin.sites.index') }}" class="{{ $navItemClass(request()->routeIs('admin.sites.*')) }}">📍 Sites</a>
              @endif
              @if(Route::has('admin.groups.index'))
                <a href="{{ route('admin.groups.index') }}" class="{{ $navItemClass(request()->routeIs('admin.groups.*')) }}">🗂️ Indicator Groups</a>
              @endif
              @if(Route::has('admin.indicators.index'))
                <a href="{{ route('admin.indicators.index') }}" class="{{ $navItemClass(request()->routeIs('admin.indicators.*')) }}">📊 Indicators</a>
              @endif
            @endif

            {{-- Operasional Input Harian --}}
            @if($isAdmin && Route::has('admin.daily.create'))
              <a href="{{ route('admin.daily.create') }}" class="{{ $navItemClass(request()->routeIs('admin.daily.*')) }}">✍️ Input Harian</a>
            @elseif(Route::has('daily.index'))
              <a href="{{ route('daily.index') }}" class="{{ $navItemClass(request()->routeIs('daily.*')) }}">✍️ Input Harian</a>
            @endif

            {{-- Rekap Bulanan: semua user --}}
            @if(Route::has('admin.reports.monthly'))
              <a href="{{ route('admin.reports.monthly') }}" class="{{ $navItemClass(request()->routeIs('admin.reports.monthly')) }}">📈 Rekap Bulanan</a>
            @endif

            {{-- Super Admin --}}
            @if($isSuper && Route::has('admin.site_access.index'))
              <a href="{{ route('admin.site_access.index') }}" class="{{ $navItemClass(request()->routeIs('admin.site_access.*')) }}">🛂 Input Access</a>
            @endif
          </div>
        </div>
      </nav>

      <a href="{{ $homeUrl }}" class="flex items-center gap-2">
        <svg viewBox="0 0 24 24" class="w-4 h-4" fill="currentColor"><path d="M12 3l7 18h-2.6l-1.7-4.5H9.4L7.7 21H5.1L12 3zm2 11L12 7l-2 7h4z"/></svg>
        <span class="font-medium tracking-tight">{{ config('app.name','AAP') }}</span>
      </a>

      <div class="ml-auto flex items-center gap-2">
        @if($sites->count() > 0)
          <span class="inline-flex items-center gap-1 px-2 py-1 rounded-md border text-xs
                       border-maroon-700 text-maroon-800 dark:text-maroon-300 dark:border-maroon-600 bg-maroon-50/70
                       dark:bg-maroon-900/20">
            📍 {{ $activeSite?->code ?? 'ALL' }}
          </span>
        @endif

        {{-- theme toggle --}}
        <button @click="dark=!dark" class="p-2 rounded-lg border border-coal-200 dark:border-coal-700" title="Toggle theme">
          <span x-show="!dark">🌙</span><span x-show="dark" x-cloak>☀️</span>
        </button>

        @auth
          <span class="hidden sm:inline text-sm text-coal-600 dark:text-coal-300">{{ auth()->user()->name }}</span>
        @endauth
        @guest
          @if(Route::has('login'))<a href="{{ route('login') }}" class="text-sm underline">Login</a>@endif
        @endguest
      </div>
    </div>
  </div>

  {{-- ===== GRID ===== --}}
  <div class="max-w-[100vw] mx-auto lg:grid lg:grid-cols-[260px_1fr] lg:gap-0">

    {{-- ========== SIDEBAR (DESKTOP) ========== --}}
    <aside class="nice-scroll fixed inset-y-0 left-0 z-50 w-72 -translate-x-full transition-transform duration-200
                   lg:static lg:translate-x-0 lg:w-auto lg:block overflow-y-auto
                   bg-ivory-50 dark:bg-coal-950 border-r border-coal-200 dark:border-coal-800
                   flex flex-col">

      {{-- USER (ATAS) --}}
      @auth
      <div class="px-4 py-4 border-b border-coal-200 dark:border-coal-800 bg-ivory-50/70 dark:bg-coal-950/70">
        <div class="flex items-center gap-3">
          <div class="w-10 h-10 rounded-full bg-maroon-700 text-ivory-50 flex items-center justify-center text-sm font-semibold">
            {{ strtoupper(\Illuminate\Support\Str::of(auth()->user()->name)->explode(' ')->map(fn($s)=>\Illuminate\Support\Str::substr($s,0,1))->take(2)->implode('')) }}
          </div>
          <div class="min-w-0">
            <div class="text-sm font-semibold truncate">{{ auth()->user()->name }}</div>
            <div class="text-xs text-coal-500 dark:text-coal-400 truncate">{{ auth()->user()->email ?? '' }}</div>
          </div>
          <button @click="dark=!dark" class="ml-auto p-2 rounded-lg border border-coal-200 dark:border-coal-700" title="Toggle theme">
            <span x-show="!dark">🌙</span><span x-show="dark" x-cloak>☀️</span>
          </button>
        </div>
      </div>
      @endauth

      {{-- NAV MENU --}}
      @php
        $navItemClass = function(bool $active = false){
          $base = 'flex items-center gap-3 px-3 py-2 rounded-xl transition outline-none';
          $inactive = 'text-coal-800 dark:text-ivory-100 border border-transparent hover:bg-ivory-100 dark:hover:bg-coal-900 focus-visible:ring-2 focus-visible:ring-maroon-700';
          $activeCls = 'is-active border';
          return $active ? "$base $activeCls" : "$base $inactive";
        };
        $user    = auth()->user();
        $isSuper = $user && method_exists($user,'isSuperAdmin') && $user->isSuperAdmin();
        $isAdmin = $isSuper || ($user && method_exists($user,'isAdmin') && $user->isAdmin());
        $isBasic = $user && !$isAdmin;

        // Kontrak Saya URL (fallback) untuk sidebar desktop
        $userContractsUrl = null;
        if (Route::has('user.contracts.index')) {
          $userContractsUrl = route('user.contracts.index');
        } elseif (Route::has('contracts.index')) {
          $userContractsUrl = route('contracts.index');
        }
      @endphp

      <nav class="p-4 space-y-6 flex-1">
        {{-- ===== ADMIN FULL MENU ===== --}}
        @if($isAdmin)
          <div>
            <div class="px-3 text-xs uppercase tracking-wider text-coal-500 dark:text-coal-300">Menu</div>
            <div class="mt-2 grid gap-1">
              <a href="{{ route('admin.dashboard') }}" class="{{ $navItemClass(request()->routeIs('admin.dashboard')) }}">🏛️ Dashboard</a>

              @if(Route::has('admin.departments.index'))
                <a href="{{ route('admin.departments.index') }}" class="{{ $navItemClass(request()->routeIs('admin.departments.*')) }}">🏷️ Departments</a>
              @endif

              @if(Route::has('admin.forms.index'))
                <a href="{{ route('admin.forms.index') }}" class="{{ $navItemClass(request()->routeIs('admin.forms.*')) }}">🧾 Forms</a>
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
            </div>
          </div>

          {{-- HSE / KPI --}}
          <div>
            <div class="px-3 text-xs uppercase tracking-wider text-coal-500 dark:text-coal-300">HSE / KPI</div>
            <div class="mt-2 grid gap-1">
              @if(Route::has('admin.sites.index'))
                <a href="{{ route('admin.sites.index') }}" class="{{ $navItemClass(request()->routeIs('admin.sites.*')) }}">📍 Sites</a>
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
              @if(Route::has('admin.reports.monthly'))
                <a href="{{ route('admin.reports.monthly') }}" class="{{ $navItemClass(request()->routeIs('admin.reports.monthly')) }}">📈 Rekap Bulanan</a>
              @endif

              @if(Route::has('admin.site_access.index'))
                <a href="{{ route('admin.site_access.index') }}" class="{{ $navItemClass(request()->routeIs('admin.site_access.*')) }}">🛂 Input Access</a>
              @endif
            </div>
          </div>

        {{-- ===== NON-ADMIN (USER BIASA) ===== --}}
        @else
          <div>
            <div class="px-3 text-xs uppercase tracking-wider text-coal-500 dark:text-coal-300">Menu</div>
            <div class="mt-2 grid gap-1">
              <a href="{{ route('admin.dashboard') }}" class="{{ $navItemClass(request()->routeIs('admin.dashboard')) }}">🏛️ Dashboard</a>

              @if(Route::has('front.forms.index'))
                <a href="{{ route('front.forms.index') }}" class="{{ $navItemClass(request()->routeIs('front.forms.*')) }}">🧾 FORM</a>
              @endif

              @if($userContractsUrl)
                <a href="{{ $userContractsUrl }}"
                   class="{{ $navItemClass(request()->routeIs('user.contracts.*') || request()->routeIs('contracts.*')) }}">
                  📑 Kontrak Saya
                </a>
              @endif

              @if(Route::has('admin.qa.index'))
                <a href="{{ route('admin.qa.index') }}" class="{{ $navItemClass(request()->routeIs('admin.qa.*')) }}">💬 Tanya Jawab</a>
              @endif
            </div>
          </div>

          <div class="mt-6">
            <div class="px-3 text-xs uppercase tracking-wider text-coal-500 dark:text-coal-300">HSE / KPI</div>
            <div class="mt-2 grid gap-1">
              @if(Route::has('daily.index'))
                <a href="{{ route('daily.index') }}" class="{{ $navItemClass(request()->routeIs('daily.*')) }}">✍️ Input Harian</a>
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
      <div class="px-4 py-3 border-t border-coal-200 dark:border-coal-800">
        <div class="text-xs mb-2 text-coal-600 dark:text-coal-300 uppercase tracking-wider">Active Site</div>

        <div class="mb-2">
          <span class="inline-flex items-center gap-2 px-2 py-1 rounded-lg border
                          border-maroon-700 text-maroon-800 dark:text-maroon-300 dark:border-maroon-600 bg-maroon-50/70
                          dark:bg-maroon-900/20">
            📍 {{ $activeSite?->code ?? 'ALL' }}
            @if($activeSite && $activeSite->name)
              <span class="text-xs text-coal-500 dark:text-coal-400">— {{ $activeSite->name }}</span>
            @endif
          </span>
        </div>

        @if(Route::has('admin.sites.switch'))
        <form method="POST" action="{{ route('admin.sites.switch') }}" class="grid gap-2">
          @csrf
          <select name="site_id" class="border rounded-lg px-3 py-2 bg-white dark:bg-coal-900">
            <option value="">ALL SITES</option>
            @foreach($sites as $s)
              <option value="{{ $s->id }}" @selected($activeSiteId==$s->id)>{{ $s->code }} — {{ $s->name }}</option>
            @endforeach
          </select>
          <button class="px-3 py-2 rounded-lg border border-coal-300 dark:border-coal-700 hover:bg-ivory-100 dark:hover:bg-coal-900">
            Ganti Site
          </button>
        </form>
        @endif
      </div>
      @endif

      {{-- LOGOUT (BAWAH) --}}
      @auth
      <div class="p-4 border-t border-coal-200 dark:border-coal-800 bg-ivory-100 dark:bg-coal-900">
        <form method="post" action="{{ route('logout') }}">@csrf
          <button class="w-full text-center text-sm rounded-lg px-3 py-2 border border-maroon-700 text-maroon-700 hover:bg-maroon-50
                         dark:border-maroon-600 dark:text-maroon-300 dark:hover:bg-maroon-900/20">Logout</button>
        </form>
      </div>
      @endauth
    </aside>

    {{-- ========== MOBILE DRAWER ========== --}}
    <div class="lg:hidden" x-cloak>
      <div x-show="sidebarOpen" @click="sidebarOpen=false" class="fixed inset-0 bg-black/40 backdrop-blur-sm z-40"></div>
      <div x-show="sidebarOpen" class="fixed inset-y-0 left-0 w-72 z-50">
        <div class="h-full shadow-2xl" @click.outside="sidebarOpen=false">
          <aside class="h-full bg-ivory-50 dark:bg-coal-950 border-r border-coal-200 dark:border-coal-800 p-0 flex flex-col">

            {{-- USER (ATAS) --}}
            @auth
            <div class="px-4 py-4 border-b border-coal-200 dark:border-coal-800">
              <div class="flex items-center gap-3">
                <div class="w-9 h-9 rounded-full bg-maroon-700 text-ivory-50 flex items-center justify-center text-xs font-semibold">
                  {{ strtoupper(\Illuminate\Support\Str::of(auth()->user()->name)->explode(' ')->map(fn($s)=>\Illuminate\Support\Str::substr($s,0,1))->take(2)->implode('')) }}
                </div>
                <div class="min-w-0">
                  <div class="text-sm font-semibold truncate">{{ auth()->user()->name }}</div>
                  <div class="text-xs text-coal-500 dark:text-coal-400 truncate">{{ auth()->user()->email ?? '' }}</div>
                </div>
                <button @click="dark=!dark" class="ml-auto p-2 rounded-lg border border-coal-200 dark:border-coal-700" title="Toggle theme">
                  <span x-show="!dark">🌙</span><span x-show="dark" x-cloak>☀️</span>
                </button>
              </div>
            </div>
            @endauth

            {{-- MENU (ADMIN / NON-ADMIN) --}}
            @php
              // Kontrak Saya URL (fallback) untuk mobile drawer
              $userContractsUrl = null;
              if (Route::has('user.contracts.index')) {
                $userContractsUrl = route('user.contracts.index');
              } elseif (Route::has('contracts.index')) {
                $userContractsUrl = route('contracts.index');
              }
            @endphp

            @if($isAdmin)
              <div class="p-4 grid gap-1 flex-1">
                <a href="{{ route('admin.dashboard') }}" class="{{ $navItemClass(request()->routeIs('admin.dashboard')) }}">🏛️ Dashboard</a>

                @if(Route::has('admin.departments.index'))
                  <a href="{{ route('admin.departments.index') }}" class="{{ $navItemClass(request()->routeIs('admin.departments.*')) }}">🏷️ Departments</a>
                @endif
                @if(Route::has('admin.forms.index'))
                  <a href="{{ route('admin.forms.index') }}" class="{{ $navItemClass(request()->routeIs('admin.forms.*')) }}">🧾 Forms</a>
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
              </div>
            @else
              <div class="p-4 grid gap-1 flex-1">
                <a href="{{ route('admin.dashboard') }}" class="{{ $navItemClass(request()->routeIs('admin.dashboard')) }}">🏛️ Dashboard</a>

                @if(Route::has('front.forms.index'))
                  <a href="{{ route('front.forms.index') }}" class="{{ $navItemClass(request()->routeIs('front.forms.*')) }}">🧾 FORM</a>
                @endif

                @if($userContractsUrl)
                  <a href="{{ $userContractsUrl }}"
                     class="{{ $navItemClass(request()->routeIs('user.contracts.*') || request()->routeIs('contracts.*')) }}">
                    📑 Kontrak Saya
                  </a>
                @endif

                @if(Route::has('admin.qa.index'))
                  <a href="{{ route('admin.qa.index') }}" class="{{ $navItemClass(request()->routeIs('admin.qa.*')) }}">💬 Tanya Jawab</a>
                @endif
              </div>
            @endif

            {{-- HSE / KPI (mobile) --}}
            <div class="p-4 pt-0">
              <div class="px-1 text-xs uppercase tracking-wider text-coal-500 dark:text-coal-300">HSE / KPI</div>
              <div class="mt-2 grid gap-1">
                @if($isAdmin)
                  @if(Route::has('admin.sites.index'))
                    <a href="{{ route('admin.sites.index') }}" class="{{ $navItemClass(request()->routeIs('admin.sites.*')) }}">📍 Sites</a>
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
                  @if(Route::has('admin.reports.monthly'))
                    <a href="{{ route('admin.reports.monthly') }}" class="{{ $navItemClass(request()->routeIs('admin.reports.monthly')) }}">📈 Rekap Bulanan</a>
                  @endif
                @endif
              </div>
            </div>

            {{-- ACTIVE SITE (mobile) --}}
            @if($sites->count() > 0)
            <div class="p-4 border-t border-coal-200 dark:border-coal-800">
              <div class="text-xs mb-2 text-coal-600 dark:text-coal-300 uppercase tracking-wider">Active Site</div>

              <div class="mb-2">
                <span class="inline-flex items-center gap-2 px-2 py-1 rounded-lg border
                                border-maroon-700 text-maroon-800 dark:text-maroon-300 dark:border-maroon-600 bg-maroon-50/70
                                dark:bg-maroon-900/20">
                  📍 {{ $activeSite?->code ?? 'ALL' }}
                  @if($activeSite && $activeSite->name)
                    <span class="text-xs text-coal-500 dark:text-coal-400">— {{ $activeSite->name }}</span>
                  @endif
                </span>
              </div>

              @if(Route::has('admin.sites.switch'))
              <form method="POST" action="{{ route('admin.sites.switch') }}" class="grid gap-2">
                @csrf
                <select name="site_id" class="border rounded-lg px-3 py-2 bg-white dark:bg-coal-900">
                  <option value="">ALL SITES</option>
                  @foreach($sites as $s)
                    <option value="{{ $s->id }}" @selected($activeSiteId==$s->id)>{{ $s->code }} — {{ $s->name }}</option>
                  @endforeach
                </select>
                <button class="px-3 py-2 rounded-lg border border-coal-300 dark:border-coal-700 hover:bg-ivory-100 dark:hover:bg-coal-900">
                  Ganti Site
                </button>
              </form>
              @endif
            </div>
            @endif

            {{-- LOGOUT (BAWAH) --}}
            @auth
            <div class="p-4 border-t border-coal-200 dark:border-coal-800 bg-ivory-100 dark:bg-coal-900">
              <form method="post" action="{{ route('logout') }}">@csrf
                <button class="w-full text-center text-sm rounded-lg px-3 py-2 border border-maroon-700 text-maroon-700 hover:bg-maroon-50
                               dark:border-maroon-600 dark:text-maroon-300 dark:hover:bg-maroon-900/20">Logout</button>
              </form>
            </div>
            @endauth
          </aside>
        </div>
      </div>
    </div>

    {{-- ========== CONTENT ========== --}}
    <div class="min-h-screen lg:ml-0">
      <div class="px-4 lg:px-8 pt-6">
        <div class="flex items-center justify-between gap-4">
          <div>
            @hasSection('breadcrumbs')
              @yield('breadcrumbs')
            @endif
          </div>

          {{-- Right header actions + compact site switcher --}}
          <div class="flex items-center gap-3">
            @if($sites->count() > 0)
            <div class="hidden md:flex items-center gap-2">
              <span class="inline-flex items-center gap-1 px-2 py-1 rounded-md border text-xs
                             border-maroon-700 text-maroon-800 dark:text-maroon-300 dark:border-maroon-600 bg-maroon-50/70
                             dark:bg-maroon-900/20">
                📍 {{ $activeSite?->code ?? 'ALL' }}
              </span>
              @if(Route::has('admin.sites.switch'))
              <form method="POST" action="{{ route('admin.sites.switch') }}" class="flex items-center gap-2">
                @csrf
                <select name="site_id" class="border rounded px-2 py-1 bg-white dark:bg-coal-900 text-sm">
                  <option value="">ALL</option>
                  @foreach($sites as $s)
                    <option value="{{ $s->id }}" @selected($activeSiteId==$s->id)>{{ $s->code }}</option>
                  @endforeach
                </select>
                <button class="px-2 py-1 rounded border border-coal-300 dark:border-coal-700 text-sm">
                  Set
                </button>
              </form>
              @endif
            </div>
            @endif

            @hasSection('actions')
              @yield('actions')
            @endif
          </div>
        </div>
      </div>

      <main class="px-4 lg:px-8 py-6">
        @yield('content')
      </main>

      @if(session('ok') || session('success'))
      <div class="px-4 lg:px-8">
        <div class="p-3 rounded bg-emerald-50 text-emerald-800 border border-emerald-200 dark:bg-emerald-900/20 dark:text-emerald-200 dark:border-emerald-900">
          {{ session('ok') ?? session('success') }}
        </div>
      </div>
      @endif

      @if(session('error'))
      <div class="px-4 lg:px-8 mt-4">
        <div class="p-3 rounded bg-rose-50 text-rose-800 border border-rose-200 dark:bg-rose-900/20 dark:text-rose-200 dark:border-rose-900">
          {{ session('error') }}
        </div>
      </div>
      @endif
    </div>
  </div>

  @stack('modals')
  @stack('scripts')

  <script defer src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js"></script>
</body>
</html>
