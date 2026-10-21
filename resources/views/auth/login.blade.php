<x-guest-layout>
  <x-ui.page-header>
  <div class="ui-page-header__copy"><x-ui.page-title>Selamat datang kembali</x-ui.page-title>
    <x-ui.page-description>Masuk untuk melanjutkan aktivitas operasional Anda.</x-ui.page-description></div>
  <x-ui.page-actions></x-ui.page-actions>
</x-ui.page-header>

  <form method="POST" action="{{ route('login') }}" class="space-y-5">
            @csrf
            <!-- Email -->
            <div>
              <label for="email" class="block mb-1 text-sm font-medium text-gray-900">Email</label>
              <input id="email" name="email" type="email" required autofocus autocomplete="username"
                value="{{ old('email') }}"
                class="w-full px-3 py-3 text-black placeholder-gray-600 bg-white border rounded-lg border-gray-300 focus:border-maroon-700 focus:ring-maroon-700">
              @error('email') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
            </div>

            <!-- Password -->
            <div>
              <label for="password" class="block mb-1 text-sm font-medium text-gray-900">Password</label>
              <input id="password" name="password" type="password" required autocomplete="current-password"
                class="w-full px-3 py-3 text-black bg-white border rounded-lg border-gray-300 focus:border-maroon-700 focus:ring-maroon-700">
              @error('password') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
            </div>

            <!-- Remember -->
            <label class="inline-flex items-center gap-2 text-black">
              <input type="checkbox" name="remember"
                class="bg-white rounded-lg border-gray-300 text-maroon-700 focus:ring-maroon-700">
              <span class="text-sm">Ingat saya</span>
            </label>

            <!-- Button -->
            <x-ui.button variant="primary" type="submit"
              class="inline-flex items-center justify-center w-full gap-2 py-3 font-semibold transition border rounded-lg focus:ring-2 focus:ring-white/70">
              <svg class="w-5 h-5 text-black stroke-black" viewBox="0 0 24 24" fill="none" stroke="currentColor" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 3h4a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2h-4M10 17l5-5-5-5M15 12H3" />
              </svg>
              Masuk
            </x-ui.button>

            <!-- Register link -->
            <div class="mt-3 text-center">
              <a href="{{ route('register') }}" class="text-sm font-medium text-black hover:text-gray-900">Belum punya akun? Daftar</a>
            </div>
          </form>
  <div class="mt-6 border-t pt-4 text-xs text-coal-400">© {{ date('Y') }} Andalan Group · Mandala</div>
</x-guest-layout>
