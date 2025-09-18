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
        init() {},
        toggleSidebar() { this.sidebarOpen = !this.sidebarOpen; },
        userInitials(name) {
          if (!name) return 'U';
          return name.trim().split(/\s+/).map(s => s[0]).slice(0,2).join('').toUpperCase();
        }
      }
    }
  </script>

  <style>
    :root { --font-sans: 'Poppins', ui-sans-serif, system-ui, -apple-system, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, 'Noto Sans'; }
    body  { font-family: var(--font-sans); }

    /* Scrollbar halus */
    .nice-scroll::-webkit-scrollbar { width: 8px; height: 8px; }
    .nice-scroll::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 8px; }
    .nice-scroll::-webkit-scrollbar-track { background: transparent; }

    /* Active state menu (maroon) */
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
  <div class="max-w-[100vw] mx-auto lg:grid lg:grid-cols-[260px_1fr]">

    {{-- Sidebar (handle drawer di partial) --}}
    @include('layouts.navigation')

    {{-- ===== Konten ===== --}}
    <div class="min-h-screen flex flex-col">

      {{-- Header (breadcrumbs + actions) --}}
      <header class="px-4 lg:px-8 pt-6">
        <div class="flex items-center justify-between gap-4">
          <div>
            @hasSection('breadcrumbs')
              @yield('breadcrumbs')
            @endif
          </div>

          <div class="flex items-center gap-3">
            @hasSection('actions')
              @yield('actions')
            @endif
          </div>
        </div>
      </header>

      {{-- Main content --}}
      <main class="px-4 lg:px-8 py-6 grow">
        @yield('content')
      </main>

      {{-- Flash success --}}
      @if(session('ok') || session('success'))
        <div class="px-4 lg:px-8 pb-4" role="status" aria-live="polite">
          <div class="rounded border p-3
                      bg-emerald-50 text-emerald-800 border-emerald-200">
            {{ session('ok') ?? session('success') }}
          </div>
        </div>
      @endif

      {{-- Flash error --}}
      @if(session('error'))
        <div class="px-4 lg:px-8 pb-4" role="alert" aria-live="assertive">
          <div class="rounded border p-3
                      bg-rose-50 text-rose-800 border-rose-200">
            {{ session('error') }}
          </div>
        </div>
      @endif

    </div>
  </div>

  @stack('modals')
  @stack('scripts')

  {{-- Alpine (defer) --}}
  <script defer src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js"></script>
</body>
</html>
