<!doctype html>
<html lang="{{ str_replace('_','-',app()->getLocale()) }}"
      x-data="{ dark: localStorage.getItem('theme') === 'dark' }"
      x-init="document.documentElement.classList.toggle('dark', dark); $watch('dark', v => { document.documentElement.classList.toggle('dark', v); localStorage.setItem('theme', v ? 'dark' : 'light') })"
      class="scroll-smooth">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <meta name="csrf-token" content="{{ csrf_token() }}">
  <title>@yield('title', config('app.name','AAP FormBuilder'))</title>

  @vite(['resources/css/app.css','resources/js/app.js'])
  @stack('styles')
</head>
<body class="bg-slate-100 dark:bg-slate-950 min-h-screen text-slate-900 dark:text-slate-100">
  {{-- Topbar --}}
  <nav class="bg-white/90 dark:bg-slate-900/90 border-b border-slate-200 dark:border-slate-800 backdrop-blur">
    <div class="max-w-6xl mx-auto px-4 py-3 flex gap-4 items-center">
      @if(Route::has('admin.dashboard'))
        <a href="{{ route('admin.dashboard') }}" class="font-semibold">AAP</a>
      @else
        <a href="{{ url('/') }}" class="font-semibold">AAP</a>
      @endif

      @auth
        @if(Route::has('admin.departments.index'))
          <a href="{{ route('admin.departments.index') }}" class="text-sm hover:underline">Departments</a>
        @endif
        @if(Route::has('admin.forms.index'))
          <a href="{{ route('admin.forms.index') }}" class="text-sm hover:underline">Forms</a>
        @endif

        {{-- Slot nav tambahan dari halaman --}}
        @hasSection('topnav')
          <div class="ml-2">@yield('topnav')</div>
        @endif

        <div class="ml-auto flex items-center gap-3">
          {{-- Toggle Dark Mode (opsional) --}}
          <button type="button" class="text-xs px-2 py-1 rounded border"
                  @click="dark = !dark"
                  x-text="dark ? 'Light' : 'Dark'"></button>

          <span class="text-sm text-slate-600 dark:text-slate-300">{{ auth()->user()->name }}</span>
          @if(Route::has('logout'))
            <form method="post" action="{{ route('logout') }}">
              @csrf
              <button class="text-sm underline">Logout</button>
            </form>
          @endif
        </div>
      @endauth

      @guest
        <div class="ml-auto">
          @if(Route::has('login'))
            <a href="{{ route('login') }}" class="text-sm underline">Login</a>
          @endif
        </div>
      @endguest
    </div>
  </nav>

  {{-- Flash message sukses --}}
  @if(session('ok') || session('success'))
    <div class="max-w-3xl mx-auto mt-4 px-4">
      <div class="p-3 rounded bg-emerald-50 text-emerald-800 border border-emerald-200 dark:bg-emerald-900/20 dark:text-emerald-200 dark:border-emerald-900">
        {{ session('ok') ?? session('success') }}
      </div>
    </div>
  @endif

  {{-- Flash message error global --}}
  @if(session('error'))
    <div class="max-w-3xl mx-auto mt-4 px-4">
      <div class="p-3 rounded bg-rose-50 text-rose-800 border border-rose-200 dark:bg-rose-900/20 dark:text-rose-200 dark:border-rose-900">
        {{ session('error') }}
      </div>
    </div>
  @endif

  {{-- Breadcrumbs + Actions (opsional, dari halaman) --}}
  <div class="max-w-6xl mx-auto px-4 pt-6">
    <div class="flex items-center justify-between gap-4">
      <div>
        @hasSection('breadcrumbs')
          @yield('breadcrumbs')
        @endif
      </div>
      <div>
        @hasSection('actions')
          @yield('actions')
        @endif
      </div>
    </div>
  </div>

  <main class="py-6">
    @yield('content')
  </main>

  {{-- Modal stack & script tambahan --}}
  @stack('modals')
  @stack('scripts')

  {{-- Alpine (kalau belum dibundle lewat Vite) --}}
  <script defer src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js"></script>
</body>
</html>
