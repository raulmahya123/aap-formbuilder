{{-- resources/views/app.blade.php --}}
<!doctype html>
<html
  lang="{{ str_replace('_','-', app()->getLocale()) }}"
  x-data="layout()"
  x-init="init()"
  class="scroll-smooth"
>
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <meta name="csrf-token" content="{{ csrf_token() }}">
  <link rel="icon" type="image/png" href="{{ asset('assets/images/andalanlogo-removebg-preview.png') }}">
  <link rel="apple-touch-icon" href="{{ asset('assets/images/andalanlogo-removebg-preview.png') }}">
  <title>@yield('title', config('app.name', 'AAP FormBuilder'))</title>

  {{-- Fonts: Poppins --}}
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600&display=swap" rel="stylesheet">

  {{-- Vite assets --}}
  @vite(['resources/css/app.css','resources/js/app.js'])
  @stack('styles')

  <script>
    function layout() {
      return {
        // HANYA untuk drawer mobile
        sidebarOpen: false,
        sidebarCollapsed: localStorage.getItem('aap-sidebar-collapsed') === '1',
        init() {
          if (window.innerWidth < 1024) this.sidebarCollapsed = false;
        },
        toggleSidebar() { this.sidebarOpen = !this.sidebarOpen; },
        toggleSidebarCollapse() {
          this.sidebarCollapsed = !this.sidebarCollapsed;
          localStorage.setItem('aap-sidebar-collapsed', this.sidebarCollapsed ? '1' : '0');
        },
        userInitials(name) {
          if (!name) return 'U';
          return name.trim().split(/\s+/).map(s => s[0]).slice(0,2).join('').toUpperCase();
        }
      }
    }
  </script>

  <style>
    :root {
      --font-sans: 'Poppins', ui-sans-serif, system-ui, -apple-system, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, 'Noto Sans';
      --brand-maroon: #bb9974;
    }
    body  { font-family: var(--font-sans); }

    /* Scrollbar halus */
    .nice-scroll::-webkit-scrollbar { width: 8px; height: 8px; }
    .nice-scroll::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 8px; }
    .nice-scroll::-webkit-scrollbar-track { background: transparent; }

    /* Active state menu */
    aside nav a.is-active {
      background-color: #bb9974 !important;
      color: #fff !important;
      border-color: #bb9974 !important;
    }
    aside nav a:is(:active, :focus):not(.is-active) {
      color: inherit !important;
      background-color: inherit !important;
      border-color: transparent !important;
      box-shadow: none !important;
      outline: none !important;
    }

    .table-header {
      background: #f4eee7;
      color: #51453d;
      font-size: .72rem;
      font-weight: 700;
      letter-spacing: .06em;
      text-transform: uppercase;
      white-space: nowrap;
    }
    main table thead th {
      background: #f4eee7;
      color: #51453d;
      font-size: .72rem;
      font-weight: 700;
      letter-spacing: .06em;
      text-transform: uppercase;
      white-space: nowrap;
    }
    .action-icon {
      display: inline-flex;
      align-items: center;
      justify-content: center;
      width: 2rem;
      height: 2rem;
      border-radius: .65rem;
      border: 1px solid #e7ddd4;
      color: #66564b;
      background: #fff;
      transition: .15s ease;
    }
    .action-icon:hover { background: #f4eee7; color: #4f3d31; transform: translateY(-1px); }
    .action-icon.danger { color: #be123c; border-color: #fecdd3; }
    .action-icon.danger:hover { background: #fff1f2; }
    .pagination { display: flex; flex-wrap: wrap; gap: .35rem; align-items: center; }
    .pagination > * { display: inline-flex; align-items: center; justify-content: center; min-width: 2rem; height: 2rem; padding: 0 .55rem; border: 1px solid #e7ddd4; border-radius: .55rem; background: #fff; font-size: .8rem; }
    .pagination .active { background: #bb9974; color: #fff; border-color: #bb9974; }
    .pagination .disabled { color: #b8aaa0; background: #faf8f6; }
  </style>
</head>

<body class="min-h-screen bg-ivory-100 text-coal-900">

  {{-- ===== Mobile hamburger (tanpa topbar) ===== --}}
  <div class="lg:hidden fixed top-3 left-3 z-[60]">
    <button
      type="button"
      @click="toggleSidebar()"
      aria-label="Open menu"
      :aria-expanded="sidebarOpen"
      class="inline-flex h-10 w-10 items-center justify-center rounded-xl border
             border-coal-200 bg-white/80 backdrop-blur shadow-sm text-coal-700
             hover:bg-ivory-100 transition"
    >
      <svg class="h-6 w-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" aria-hidden="true">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
              d="M4 6h16M4 12h16M4 18h16"/>
      </svg>
    </button>
  </div>

  {{-- ===== Grid 2 kolom: Sidebar kiri + Konten kanan ===== --}}
  <div class="max-w-[100vw] mx-auto lg:grid" :class="sidebarCollapsed ? 'lg:grid-cols-[76px_1fr]' : 'lg:grid-cols-[260px_1fr]'">

    {{-- Sidebar (handle drawer di partial) --}}
    @include('layouts.navigation')

    {{-- ===== Konten ===== --}}
    <div class="min-h-screen min-w-0 flex flex-col">

      @php
        $navbarUser = auth()->user();
        $navbarSites = collect();
        if ($navbarUser) {
            $navbarSites = $navbarUser->isSuperAdmin()
                ? \App\Models\Site::select('id','code','name')->orderBy('code')->get()
                : $navbarUser->sites()->select('sites.id','sites.code','sites.name')->orderBy('sites.code')->get();
        }
        $navbarActiveSite = session('active_site_id') ? $navbarSites->firstWhere('id', session('active_site_id')) : null;
      @endphp

      {{-- Navbar --}}
      <header class="sticky top-0 z-30 border-b border-coal-200 bg-ivory-50/95 px-4 py-3 backdrop-blur lg:px-8">
        <div class="flex min-h-10 items-center justify-between gap-4">
          <div class="flex min-w-0 items-center gap-3">
            <button type="button" @click="toggleSidebarCollapse()" class="hidden h-9 w-9 shrink-0 items-center justify-center rounded-lg border border-coal-200 bg-white text-coal-700 hover:bg-ivory-100 lg:inline-flex" title="Buka/tutup sidebar" aria-label="Buka/tutup sidebar">
              <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 12h16M4 18h16"/></svg>
            </button>
            <div class="hidden text-sm font-semibold text-maroon-800 sm:block">AAP Form Builder</div>
            <div class="h-5 w-px bg-coal-200"></div>
            <div class="min-w-0 truncate text-sm text-coal-600">
            @hasSection('breadcrumbs')
              @yield('breadcrumbs')
            @else
              {{ trim($__env->yieldContent('title')) ?: 'Dashboard' }}
            @endif
            </div>
          </div>

          <div class="flex items-center gap-2 sm:gap-3">
            @if($navbarUser && $navbarUser->isAdmin() && $navbarSites->count() > 0 && Route::has('admin.sites.switch'))
              <div x-data="{open:false}" class="relative hidden md:block">
                <button type="button" @click="open=!open" @click.outside="open=false" class="inline-flex max-w-[15rem] items-center gap-2 rounded-xl border border-coal-200 bg-white px-3 py-2 text-left text-sm hover:bg-ivory-100" aria-label="Pilih site">
                  <svg class="h-4 w-4 shrink-0 text-maroon-700" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M12 21s7-5.2 7-11a7 7 0 1 0-14 0c0 5.8 7 11 7 11Z"/><circle cx="12" cy="10" r="2.3"/></svg>
                  <span class="truncate font-medium">{{ $navbarActiveSite?->code ?? 'ALL SITES' }}</span>
                  <svg class="h-4 w-4 text-coal-400" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="m6 9 6 6 6-6"/></svg>
                </button>
                <div x-show="open" x-transition x-cloak class="absolute right-0 mt-2 w-72 rounded-2xl border border-coal-200 bg-white p-2 shadow-xl">
                  <div class="px-3 py-2 text-xs font-semibold uppercase tracking-wider text-coal-400">Active Site</div>
                  <form method="POST" action="{{ route('admin.sites.switch') }}" class="grid gap-1">
                    @csrf
                    <button name="site_id" value="" class="flex items-center justify-between rounded-xl px-3 py-2 text-left text-sm hover:bg-ivory-100"><span>ALL SITES</span>@if(!$navbarActiveSite)<span class="text-maroon-700">✓</span>@endif</button>
                    @foreach($navbarSites as $navbarSite)
                      <button name="site_id" value="{{ $navbarSite->id }}" class="flex items-center justify-between rounded-xl px-3 py-2 text-left text-sm hover:bg-ivory-100"><span>{{ $navbarSite->code }} — {{ $navbarSite->name }}</span>@if($navbarActiveSite?->id === $navbarSite->id)<span class="text-maroon-700">✓</span>@endif</button>
                    @endforeach
                  </form>
                </div>
              </div>
            @endif

            @hasSection('actions')
              @yield('actions')
            @endif

            @if($navbarUser)
              <div x-data="{open:false}" class="relative">
                <button type="button" @click="open=!open" @click.outside="open=false" class="inline-flex items-center gap-2 rounded-xl border border-coal-200 bg-white px-2 py-1.5 text-left hover:bg-ivory-100" aria-label="Buka profil akun">
                  @if($navbarUser->avatar_path)
                    <img src="{{ asset('storage/'.$navbarUser->avatar_path) }}" alt="" class="h-8 w-8 rounded-lg object-cover">
                  @else
                    <span class="flex h-8 w-8 items-center justify-center rounded-lg bg-maroon-700 text-xs font-semibold text-white">{{ strtoupper(substr($navbarUser->name, 0, 2)) }}</span>
                  @endif
                  <span class="hidden max-w-[10rem] truncate text-sm font-medium sm:block">{{ $navbarUser->name }}</span>
                  <svg class="hidden h-4 w-4 text-coal-400 sm:block" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="m6 9 6 6 6-6"/></svg>
                </button>
                <div x-show="open" x-transition x-cloak class="absolute right-0 mt-2 w-80 rounded-2xl border border-coal-200 bg-white p-4 shadow-xl">
                  <div class="flex items-center gap-3 border-b border-coal-100 pb-4">
                    @if($navbarUser->avatar_path)
                      <img src="{{ asset('storage/'.$navbarUser->avatar_path) }}" alt="Foto {{ $navbarUser->name }}" class="h-14 w-14 rounded-2xl object-cover">
                    @else
                      <span class="flex h-14 w-14 items-center justify-center rounded-2xl bg-maroon-700 text-lg font-semibold text-white">{{ strtoupper(substr($navbarUser->name, 0, 2)) }}</span>
                    @endif
                    <div class="min-w-0"><div class="truncate font-semibold text-coal-900">{{ $navbarUser->name }}</div><div class="truncate text-xs text-coal-500">{{ $navbarUser->email }}</div></div>
                  </div>
                  <div class="mt-4 grid gap-2">
                    @if(Route::has('profile.edit'))
                      <a href="{{ route('profile.edit') }}" class="inline-flex items-center justify-center gap-2 rounded-xl bg-maroon-700 px-3 py-2.5 text-sm font-semibold text-white hover:bg-maroon-800">
                        <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><circle cx="12" cy="8" r="3.5"/><path stroke-linecap="round" d="M5 20c.8-3.2 3.2-5 7-5s6.2 1.8 7 5"/></svg>
                        Lihat akun
                      </a>
                    @endif
                    <form method="POST" action="{{ route('logout') }}">@csrf<button class="w-full rounded-xl border border-rose-200 px-3 py-2 text-sm font-medium text-rose-700 hover:bg-rose-50">Logout</button></form>
                  </div>
                </div>
              </div>
            @endif
          </div>
        </div>
      </header>

      {{-- Main content --}}
      <main class="px-4 lg:px-8 py-6 grow">
        @yield('content')
      </main>

    </div>
  </div>

  @if(session('ok') || session('success') || session('error') || $errors->any())
    <div x-data="{show:true}" x-show="show" x-transition class="fixed right-4 top-4 z-[100] w-[min(92vw,26rem)] rounded-2xl border bg-white p-4 shadow-xl" role="status" aria-live="polite">
      <div class="flex items-start gap-3">
        <div class="mt-0.5 flex h-8 w-8 shrink-0 items-center justify-center rounded-full {{ session('error') || $errors->any() ? 'bg-rose-100 text-rose-700' : 'bg-emerald-100 text-emerald-700' }}">
          @if(session('error') || $errors->any()) ! @else ✓ @endif
        </div>
        <div class="min-w-0 flex-1 text-sm {{ session('error') || $errors->any() ? 'text-rose-800' : 'text-emerald-800' }}">
          @if($errors->any())
            <div class="font-semibold">Periksa input</div>
            <div class="mt-1">{{ $errors->first() }}</div>
          @else
            {{ session('error') ?? session('ok') ?? session('success') }}
          @endif
        </div>
        <button type="button" @click="show=false" class="text-lg leading-none text-coal-400 hover:text-coal-700" aria-label="Tutup">&times;</button>
      </div>
    </div>
  @endif

  @stack('modals')
  @stack('scripts')

  {{-- Alpine (defer) --}}
  <script defer src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js"></script>
  <script>
    document.addEventListener('submit', function (event) {
      const form = event.target;
      const message = form?.dataset?.confirm;
      if (message && !window.confirm(message)) event.preventDefault();
    });
  </script>
</body>
</html>
