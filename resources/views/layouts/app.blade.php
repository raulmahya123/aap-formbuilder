{{-- resources/views/app.blade.php --}}
<!doctype html>
<html
  lang="{{ str_replace('_','-', app()->getLocale()) }}"
  x-data="layout()"
  @keydown.escape.window="if (sidebarOpen) { sidebarOpen = false; $nextTick(() => document.getElementById('navigation-toggle').focus()); }"
  @keydown.tab.window="trapDrawerFocus($event)"
  @resize.window="isDesktop = window.innerWidth >= 1024; if (isDesktop) sidebarOpen = false; else sidebarCollapsed = false"
  class="scroll-smooth"
>
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <meta name="csrf-token" content="{{ csrf_token() }}">
  <meta name="theme-color" content="#713F12">
  {{-- Critical visibility rules must work before assets load or with cached CSS. --}}
  <style>dialog.ui-modal:not([open]),dialog.ui-modal [hidden]{display:none!important}dialog.ui-modal[open]{display:flex}</style>
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
          this.$watch('sidebarOpen', value => { document.body.style.overflow = value && !this.isDesktop ? 'hidden' : ''; });
          this.$watch('isDesktop', () => { document.body.style.overflow = ''; });
        },
        toggleSidebar() {
          this.sidebarOpen = !this.sidebarOpen;
          if (this.sidebarOpen) this.$nextTick(() => document.querySelector('#app-sidebar a')?.focus());
        },
        trapDrawerFocus(event) {
          if (this.isDesktop || !this.sidebarOpen) return;
          const controls = [...document.querySelectorAll('#app-sidebar a, #app-sidebar button')].filter(el => el.getClientRects().length);
          const first = controls[0], last = controls[controls.length - 1];
          if (!first) return;
          if (event.shiftKey && (document.activeElement === first || !document.getElementById('app-sidebar').contains(document.activeElement))) { event.preventDefault(); last.focus(); }
          else if (!event.shiftKey && (document.activeElement === last || !document.getElementById('app-sidebar').contains(document.activeElement))) { event.preventDefault(); first.focus(); }
        },
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

<body class="ui-shell {{ request()->routeIs('admin.qa.index') ? 'ui-shell--chat' : '' }} min-h-screen bg-white text-coal-900">
  <a class="ui-skip" href="#main-content">Lewati ke konten</a>

  {{-- ===== Grid 2 kolom: Sidebar kiri + Konten kanan ===== --}}
  <div class="ui-layout-grid max-w-[100vw] mx-auto lg:grid" :class="sidebarCollapsed ? 'lg:grid-cols-[80px_minmax(0,1fr)]' : 'lg:grid-cols-[256px_minmax(0,1fr)]'">

    {{-- Sidebar (handle drawer di partial) --}}
    @include('layouts.navigation')

    {{-- ===== Konten ===== --}}
    <div class="min-h-screen min-w-0 flex flex-col" :inert="sidebarOpen && !isDesktop">

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
        <div class="ui-topbar__inner flex min-w-0 items-center justify-between gap-3">
          <div class="flex min-w-0 items-center gap-3">
            <button id="navigation-toggle" type="button" @click="isDesktop ? toggleSidebarCollapse() : toggleSidebar()" class="ui-shell-toggle" aria-controls="app-sidebar" :aria-expanded="isDesktop ? !sidebarCollapsed : sidebarOpen" aria-label="Buka/tutup navigasi" title="Buka/tutup navigasi">
              <x-ui.icon name="menu" />
            </button>
            <span class="ui-app-name truncate text-sm font-semibold text-[#18181B]">Mandala</span>
          </div>

          <div class="flex items-center gap-2 sm:gap-3">
            @if($navbarUser && $navbarUser->isAdmin() && $navbarSites->count() > 0 && Route::has('admin.sites.switch'))
              <form method="POST" action="{{ route('admin.sites.switch') }}" class="flex min-w-0 items-center gap-2" data-site-selector data-error-bag="siteSelection">
                @csrf
                <label for="global-site" class="sr-only">Site aktif</label>
                <x-ui.form-select id="global-site" name="site_id" aria-label="Site aktif">
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
                <button type="button" @click="open=!open" @click.outside="open=false" class="ui-account-toggle inline-flex items-center gap-2 rounded-lg border border-[#D4D4D8] bg-white px-2 text-left text-[#18181B] hover:bg-[#F4F4F5]" aria-label="Buka profil akun" x-ref="accountButton" aria-controls="account-dropdown" :aria-expanded="open" @keydown.escape.stop="open=false; $el.focus()">
                  @if($navbarUser->avatar_path)
                    <img src="{{ asset('storage/'.$navbarUser->avatar_path) }}" alt="" class="h-8 w-8 rounded-lg object-cover">
                  @else
                    <span class="flex h-8 w-8 items-center justify-center rounded-lg bg-maroon-700 text-xs font-semibold text-white">{{ strtoupper(substr($navbarUser->name, 0, 2)) }}</span>
                  @endif
                  <span class="hidden max-w-[10rem] truncate text-sm font-medium sm:block">{{ $navbarUser->name }}</span>
                  <svg class="hidden h-4 w-4 text-coal-400 sm:block" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="m6 9 6 6 6-6"/></svg>
                </button>
                <div id="account-dropdown" x-show="open" @keydown.escape.stop="open=false; $refs.accountButton.focus()" x-transition x-cloak class="absolute right-0 mt-2 w-80 max-w-[calc(100vw-2rem)] rounded-lg border border-[#D4D4D8] bg-white p-4 shadow-none">
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
