<x-guest-layout>
  <x-ui.page-header>
  <div class="ui-page-header__copy"><x-ui.page-title>Buat akun Mandala</x-ui.page-title>
    <x-ui.page-description>Lengkapi data Anda untuk mengakses sistem operasional.</x-ui.page-description></div>
  <x-ui.page-actions></x-ui.page-actions>
</x-ui.page-header>
  @if(session('status'))
    <div class="toast toast-success mb-4" role="status">{{ session('status') }}</div>
  @endif
  <form method="POST" action="{{ route('register') }}" class="space-y-5">
            @csrf

            <!-- Name -->
            <div>
              <label for="name" class="block mb-1 text-sm font-medium text-gray-900">Nama Lengkap</label>
              <input id="name" name="name" type="text" value="{{ old('name') }}" required autofocus autocomplete="name"
                class="w-full px-3 py-3 text-black placeholder-gray-600 bg-white border rounded-lg border-gray-300 focus:border-maroon-700 focus:ring-maroon-700">
              @error('name') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
            </div>

            <!-- Email -->
            <div>
              <label for="email" class="block mb-1 text-sm font-medium text-gray-900">Email</label>
              <input id="email" name="email" type="email" value="{{ old('email') }}" required autocomplete="username"
                class="w-full px-3 py-3 text-black placeholder-gray-600 bg-white border rounded-lg border-gray-300 focus:border-maroon-700 focus:ring-maroon-700">
              @error('email') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
            </div>

            <!-- Password -->
            <div>
              <label for="password" class="block mb-1 text-sm font-medium text-gray-900">Password</label>
              <input id="password" name="password" type="password" required autocomplete="new-password"
                class="w-full px-3 py-3 text-black bg-white border rounded-lg border-gray-300 focus:border-maroon-700 focus:ring-maroon-700">
              @error('password') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
            </div>

            <!-- Confirm Password -->
            <div>
              <label for="password_confirmation" class="block mb-1 text-sm font-medium text-gray-900">Konfirmasi Password</label>
              <input id="password_confirmation" name="password_confirmation" type="password" required autocomplete="new-password"
                class="w-full px-3 py-3 text-black bg-white border rounded-lg border-gray-300 focus:border-maroon-700 focus:ring-maroon-700">
              @error('password_confirmation') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
            </div>

            <!-- Action -->
            <div class="flex items-center justify-between pt-2">
              <a href="{{ route('login') }}" class="text-sm font-medium text-black hover:text-gray-900">Sudah punya akun?</a>
              <x-ui.button variant="primary" type="submit"
                class="inline-flex items-center justify-center gap-2 px-6 py-3 font-semibold transition border rounded-lg focus:ring-2 focus:ring-white/70">
                <svg class="w-5 h-5 text-black stroke-black" viewBox="0 0 24 24" fill="none" stroke="currentColor" aria-hidden="true">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 5v14M5 12h14" />
                </svg>
                Daftar
              </x-ui.button>
            </div>
          </form>
  <div class="mt-6 border-t pt-4 text-xs text-coal-400">© {{ date('Y') }} Andalan Group · Mandala</div>
</x-guest-layout>
