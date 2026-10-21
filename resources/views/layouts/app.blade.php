{{-- resources/views/app.blade.php --}}
<!doctype html>
<html
  lang="{{ str_replace('_','-', app()->getLocale()) }}"
  x-data="layout()"
  @keydown.escape.window="sidebarOpen = false"
  @resize.window="isDesktop = window.innerWidth >= 1024; if (isDesktop) sidebarOpen = false; else sidebarCollapsed = false"
  class="scroll-smooth"
>
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <meta name="csrf-token" content="{{ csrf_token() }}">
  <meta name="theme-color" content="#713F12">
  <link rel="icon" type="image/png" href="{{ asset('assets/images/andalanlogo-removebg-preview.png') }}">
  <link rel="apple-touch-icon" href="{{ asset('assets/images/andalanlogo-removebg-preview.png') }}">
  <title>@yield('title', config('app.name', 'AAP FormBuilder'))</title>

  {{-- Vite assets --}}
  <link rel="preload" href="{{ Vite::asset('resources/fonts/InterVariable.woff2') }}" as="font" type="font/woff2" crossorigin>
  @vite(['resources/css/app.css','resources/js/app.js'])
  @stack('styles')

  <script>
    function layout() {
      return {
        // HANYA untuk drawer mobile
        sidebarOpen: false,
        isDesktop: window.innerWidth >= 1024,
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


</head>

<body class="ui-shell min-h-screen bg-white text-coal-900">
  <a class="ui-skip" href="#main-content">Lewati ke konten</a>

  {{-- ===== Mobile hamburger (tanpa topbar) ===== --}}
  <div class="lg:hidden fixed top-3 left-3 z-[60]">
    <button
      type="button"
      @click="toggleSidebar()"
      aria-label="Open menu"
      :aria-expanded="sidebarOpen"
      class="inline-flex h-10 w-10 items-center justify-center rounded-lg border border-[#D4D4D8] bg-white text-[#18181B] hover:bg-[#F4F4F5] transition"
    >
      <svg class="h-6 w-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" aria-hidden="true">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
              d="M4 6h16M4 12h16M4 18h16"/>
      </svg>
    </button>
  </div>

  {{-- ===== Grid 2 kolom: Sidebar kiri + Konten kanan ===== --}}
  <div class="max-w-[100vw] mx-auto lg:grid" :class="sidebarCollapsed ? 'lg:grid-cols-[80px_minmax(0,1fr)]' : 'lg:grid-cols-[256px_minmax(0,1fr)]'">

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
      <header class="ui-topbar sticky top-0 z-30 border-b border-[#D4D4D8] bg-white px-4 py-3 lg:px-8">
        <div class="flex min-h-10 items-center justify-between gap-4">
          <div class="flex min-w-0 items-center gap-3">
            <button type="button" @click="toggleSidebarCollapse()" class="hidden h-9 w-9 shrink-0 items-center justify-center rounded-lg border border-coal-200 bg-white text-coal-700 hover:bg-ivory-100 lg:inline-flex" title="Buka/tutup sidebar" aria-label="Buka/tutup sidebar">
              <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 12h16M4 18h16"/></svg>
            </button>
            <div class="hidden text-sm font-semibold text-[#713F12] sm:block">AAP Form Builder</div>
            <div class="h-5 w-px bg-[#D4D4D8]"></div>
            <div class="ui-breadcrumb min-w-0 truncate text-[#18181B]">
              {{ trim($__env->yieldContent('title')) ?: 'Dashboard' }}
            </div>
          </div>

          <div class="flex items-center gap-2 sm:gap-3">
            @if($navbarUser && $navbarUser->isAdmin() && $navbarSites->count() > 0 && Route::has('admin.sites.switch'))
              <form method="POST" action="{{ route('admin.sites.switch') }}" class="flex min-w-0 items-center gap-2" data-site-selector data-error-bag="siteSelection">
                @csrf
                <label for="global-site" class="sr-only">Site aktif</label>
                <x-ui.form-select id="global-site" name="site_id" class="max-w-[min(36vw,15rem)]" aria-label="Site aktif">
                  <option value="">Semua site</option>
                  @foreach($navbarSites as $navbarSite)
                    <option value="{{ $navbarSite->id }}" @selected($navbarActiveSite?->id === $navbarSite->id)>{{ $navbarSite->code }} — {{ $navbarSite->name }}</option>
                  @endforeach
                </x-ui.form-select>
                <noscript><x-ui.button type="submit" variant="secondary">Terapkan</x-ui.button></noscript>
              </form>
            @endif

            @hasSection('actions')
            @endif

            @if($navbarUser)
              <div x-data="{open:false}" class="relative">
                <button type="button" @click="open=!open" @click.outside="open=false" class="inline-flex items-center gap-2 rounded-lg border border-[#D4D4D8] bg-white px-2 py-2 text-left text-[#18181B] hover:bg-[#F4F4F5]" aria-label="Buka profil akun">
                  @if($navbarUser->avatar_path)
                    <img src="{{ asset('storage/'.$navbarUser->avatar_path) }}" alt="" class="h-8 w-8 rounded-lg object-cover">
                  @else
                    <span class="flex h-8 w-8 items-center justify-center rounded-lg bg-maroon-700 text-xs font-semibold text-white">{{ strtoupper(substr($navbarUser->name, 0, 2)) }}</span>
                  @endif
                  <span class="hidden max-w-[10rem] truncate text-sm font-medium sm:block">{{ $navbarUser->name }}</span>
                  <svg class="hidden h-4 w-4 text-coal-400 sm:block" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="m6 9 6 6 6-6"/></svg>
                </button>
                <div x-show="open" x-transition x-cloak class="absolute right-0 mt-2 w-80 rounded-lg border border-[#D4D4D8] bg-white p-4 shadow-none">
                  <div class="flex items-center gap-3 border-b border-coal-100 pb-4">
                    @if($navbarUser->avatar_path)
                      <img src="{{ asset('storage/'.$navbarUser->avatar_path) }}" alt="Foto {{ $navbarUser->name }}" class="h-14 w-14 rounded-lg object-cover">
                    @else
                      <span class="flex h-14 w-14 items-center justify-center rounded-lg bg-maroon-700 text-lg font-semibold text-white">{{ strtoupper(substr($navbarUser->name, 0, 2)) }}</span>
                    @endif
                    <div class="min-w-0"><div class="truncate font-semibold text-coal-900">{{ $navbarUser->name }}</div><div class="truncate text-xs text-coal-500">{{ $navbarUser->email }}</div></div>
                  </div>
                  <div class="mt-4 grid gap-2">
                    @if(Route::has('profile.edit'))
                      <a href="{{ route('profile.edit') }}" class="inline-flex items-center justify-center gap-2 rounded-lg bg-blue-700 px-3 py-3 text-sm font-semibold text-white hover:bg-blue-800">
                        <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><circle cx="12" cy="8" r="3.5"/><path stroke-linecap="round" d="M5 20c.8-3.2 3.2-5 7-5s6.2 1.8 7 5"/></svg>
                        Lihat akun
                      </a>
                    @endif
                    <form method="POST" action="{{ route('logout') }}">@csrf<button class="w-full rounded-lg border border-rose-200 px-3 py-2 text-sm font-medium text-rose-700 hover:bg-rose-50">Logout</button></form>
                  </div>
                </div>
              </div>
            @endif
          </div>
        </div>
      </header>

      {{-- Main content --}}
      <main id="main-content" tabindex="-1" class="px-4 lg:px-8 py-6 grow">
        @yield('content')
      </main>

    </div>
  </div>

  <x-ui.toast-provider />

  <x-ui.action-dialogs />
  <x-ui.remote-modal />
  @stack('modals')
  @stack('scripts')

</body>
</html>
