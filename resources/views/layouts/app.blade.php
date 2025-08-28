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
    function layout(){
      return {
        dark: localStorage.getItem('theme') === 'dark',
        sidebarOpen: false,
        init(){
          document.documentElement.classList.toggle('dark', this.dark);
          this.$watch('dark', v => {
            document.documentElement.classList.toggle('dark', v);
            localStorage.setItem('theme', v ? 'dark' : 'light');
          });
        },
        toggleSidebar(){ this.sidebarOpen = !this.sidebarOpen; },
        userInitials(name){
          if(!name) return 'U';
          return name.split(' ').map(s=>s[0]).slice(0,2).join('').toUpperCase();
        }
      }
    }
  </script>

  <style>
    :root { --font-sans: 'Poppins', ui-sans-serif, system-ui, -apple-system, Segoe UI, Roboto, 'Helvetica Neue', Arial, 'Noto Sans'; }
    body { font-family: var(--font-sans); }
    .nice-scroll::-webkit-scrollbar{width:8px;height:8px}
    .nice-scroll::-webkit-scrollbar-thumb{background:#cbd5e1;border-radius:8px}
    .nice-scroll::-webkit-scrollbar-track{background:transparent}

    /* Warna AKTIF yang pasti merah (maroon) */
    aside nav a.is-active{
      background-color:#7b1c1c !important; /* maroon utama */
      color:#fff !important;
      border-color:#7b1c1c !important;
    }
    /* Netralisir efek :active/:focus supaya item lain tidak ikut merah */
    aside nav a:is(:active,:focus):not(.is-active){
      color:inherit !important;
      background-color:inherit !important;
      border-color:transparent !important;
      box-shadow:none !important;
      outline:none !important;
    }
  </style>
</head>
<body class="bg-ivory-100 dark:bg-coal-900 text-coal-900 dark:text-ivory-100 min-h-screen">

  {{-- MOBILE TOPBAR --}}
  <div class="lg:hidden sticky top-0 z-40 bg-ivory-50/90 dark:bg-coal-900/80 backdrop-blur border-b border-coal-200/60 dark:border-coal-800">
    <div class="max-w-7xl mx-auto px-4 py-3 flex items-center gap-3">
      <button @click="toggleSidebar()" class="p-2 rounded-lg border border-coal-200 dark:border-coal-700">
        <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" viewBox="0 0 24 24" fill="currentColor"><path d="M3 6h18M3 12h18M3 18h18"/></svg>
      </button>

      <a href="{{ Route::has('admin.dashboard') ? route('admin.dashboard') : url('/') }}" class="flex items-center gap-2">
        <svg viewBox="0 0 24 24" class="w-4 h-4" fill="currentColor" aria-hidden="true">
          <path d="M12 3l7 18h-2.6l-1.7-4.5H9.4L7.7 21H5.1L12 3zm2 11L12 7l-2 7h4z"/>
        </svg>
        <span class="font-medium tracking-tight">{{ config('app.name','AAP') }}</span>
      </a>

      <div class="ml-auto flex items-center gap-2">
        @auth
          <span class="hidden sm:inline text-sm text-coal-600 dark:text-coal-300">{{ auth()->user()->name }}</span>
        @endauth
        @guest
          @if(Route::has('login'))<a href="{{ route('login') }}" class="text-sm underline">Login</a>@endif
        @endguest
      </div>
    </div>
  </div>

  {{-- GRID --}}
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
            {{ strtoupper(Str::of(auth()->user()->name)->explode(' ')->map(fn($s)=>Str::substr($s,0,1))->take(2)->implode('')) }}
          </div>
          <div class="min-w-0">
            <div class="text-sm font-semibold truncate">{{ auth()->user()->name }}</div>
            <div class="text-xs text-coal-500 dark:text-coal-400 truncate">{{ auth()->user()->email ?? '' }}</div>
          </div>
        </div>
      </div>
      @endauth

      {{-- NAV MENU --}}
      @php
        // Helper kelas nav (closure)
        $navItemClass = function(bool $active = false){
          $base = 'flex items-center gap-3 px-3 py-2 rounded-xl transition outline-none';
          $inactive = 'text-coal-800 dark:text-ivory-100 border border-transparent '.
                      'hover:bg-ivory-100 dark:hover:bg-coal-900 '.
                      'focus-visible:ring-2 focus-visible:ring-maroon-700';
          $activeCls = 'is-active border'; // warna diatur oleh CSS .is-active
          return $active ? "$base $activeCls" : "$base $inactive";
        };
      @endphp

      <nav class="p-4 space-y-6 flex-1">
        <div>
          <div class="px-3 text-xs uppercase tracking-wider text-coal-500 dark:text-coal-300">Menu</div>
          <div class="mt-2 grid gap-1">
            <a href="{{ route('admin.dashboard') }}"
               class="{{ $navItemClass(request()->routeIs('admin.dashboard')) }}">
              <span class="inline-block w-5 text-center">🏛️</span><span>Dashboard</span>
            </a>

            @if(Route::has('admin.departments.index'))
            <a href="{{ route('admin.departments.index') }}"
               class="{{ $navItemClass(request()->routeIs('admin.departments.*')) }}">
              <span class="inline-block w-5 text-center">🏷️</span><span>Departments</span>
            </a>
            @endif

            @if(Route::has('admin.forms.index'))
            <a href="{{ route('admin.forms.index') }}"
               class="{{ $navItemClass(request()->routeIs('admin.forms.*')) }}">
              <span class="inline-block w-5 text-center">🧾</span><span>Forms</span>
            </a>
            @endif

            @if(Route::has('admin.entries.index'))
            <a href="{{ route('admin.entries.index') }}"
               class="{{ $navItemClass(request()->routeIs('admin.entries.*')) }}">
              <span class="inline-block w-5 text-center">📥</span><span>Entries</span>
            </a>
            @endif

            @php($user = auth()->user())
            @if($user && method_exists($user,'isSuperAdmin') && $user->isSuperAdmin())
              @if(Route::has('admin.users.active.index'))
              <a href="{{ route('admin.users.active.index') }}"
                 class="{{ $navItemClass(request()->routeIs('admin.users.active.*')) }}">
                <span class="inline-block w-5 text-center">👥</span><span>Manage Users</span>
              </a>
              @endif
            @endif
          </div>
        </div>

        @hasSection('sidenav')
          <div>
            <div class="px-3 text-xs uppercase tracking-wider text-coal-500 dark:text-coal-300">Lainnya</div>
            <div class="mt-2">@yield('sidenav')</div>
          </div>
        @endif
      </nav>

      {{-- LOGOUT (BAWAH) --}}
      @auth
      <div class="p-4 border-t border-coal-200 dark:border-coal-800 bg-ivory-100 dark:bg-coal-900">
        <form method="post" action="{{ route('logout') }}">@csrf
          <button class="w-full text-center text-sm rounded-lg px-3 py-2 border border-maroon-700 text-maroon-700 hover:bg-maroon-50
                         dark:border-maroon-600 dark:text-maroon-300 dark:hover:bg-maroon-900/20">
            Logout
          </button>
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
                  {{ strtoupper(Str::of(auth()->user()->name)->explode(' ')->map(fn($s)=>Str::substr($s,0,1))->take(2)->implode('')) }}
                </div>
                <div class="min-w-0">
                  <div class="text-sm font-semibold truncate">{{ auth()->user()->name }}</div>
                  <div class="text-xs text-coal-500 dark:text-coal-400 truncate">{{ auth()->user()->email ?? '' }}</div>
                </div>
              </div>
            </div>
            @endauth

            {{-- MENU --}}
            <div class="p-4 grid gap-1 flex-1">
              <a href="{{ route('admin.dashboard') }}"
                 class="{{ $navItemClass(request()->routeIs('admin.dashboard')) }}">🏛️ Dashboard</a>

              @if(Route::has('admin.departments.index'))
                <a href="{{ route('admin.departments.index') }}"
                   class="{{ $navItemClass(request()->routeIs('admin.departments.*')) }}">🏷️ Departments</a>
              @endif

              @if(Route::has('admin.forms.index'))
                <a href="{{ route('admin.forms.index') }}"
                   class="{{ $navItemClass(request()->routeIs('admin.forms.*')) }}">🧾 Forms</a>
              @endif

              @if(Route::has('admin.entries.index'))
                <a href="{{ route('admin.entries.index') }}"
                   class="{{ $navItemClass(request()->routeIs('admin.entries.*')) }}">📥 Entries</a>
              @endif

              @php($user = auth()->user())
              @if($user && method_exists($user,'isSuperAdmin') && $user->isSuperAdmin())
                @if(Route::has('admin.users.active.index'))
                  <a href="{{ route('admin.users.active.index') }}"
                     class="{{ $navItemClass(request()->routeIs('admin.users.active.*')) }}">👥 Manage Users</a>
                @endif
              @endif
            </div>

            {{-- LOGOUT (BAWAH) --}}
            @auth
            <div class="p-4 border-t border-coal-200 dark:border-coal-800 bg-ivory-100 dark:bg-coal-900">
              <form method="post" action="{{ route('logout') }}">@csrf
                <button class="w-full text-center text-sm rounded-lg px-3 py-2 border border-maroon-700 text-maroon-700 hover:bg-maroon-50
                               dark:border-maroon-600 dark:text-maroon-300 dark:hover:bg-maroon-900/20">
                  Logout
                </button>
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
          <div>@hasSection('breadcrumbs') @yield('breadcrumbs') @endif</div>
          <div>@hasSection('actions') @yield('actions') @endif</div>
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
