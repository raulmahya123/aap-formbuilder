<x-guest-layout>
  <div class="sr-only">
    <x-ui.page-header>
      <x-ui.page-title>Selamat datang kembali</x-ui.page-title>
      <x-ui.page-description>Masuk ke akun Anda untuk melanjutkan aktivitas operasional.</x-ui.page-description>
    </x-ui.page-header>
  </div>

  <div 
    x-data="{
      isSubmitting: false,
      showPassword: false,
      email: '{{ old('email') }}',
      password: '',
      errors: {
        email: '{{ $errors->first('email') }}',
        password: '{{ $errors->first('password') }}',
      },
      handleSubmit(e) {
        if (!this.email || !this.password) return;
        this.isSubmitting = true;
      }
    }"
    class="w-full"
  >
    <!-- Mobile header logo -->
    <div class="lg:hidden mb-6 flex flex-col items-center text-center">
      <img src="{{ asset('assets/images/logomandala.png') }}" alt="MANDALA" class="h-10 w-auto mb-2" />
      <span class="text-[10px] font-bold text-[#64748B] tracking-wider uppercase">Andalan Management System</span>
    </div>

    <!-- Heading -->
    <div class="mb-8">
      <h1 class="text-2xl sm:text-[28px] font-bold text-[#111827] tracking-tight">Selamat datang kembali</h1>
      <p class="text-xs sm:text-sm text-[#64748B] mt-1.5 leading-relaxed">
        Masuk ke akun Anda untuk mengakses operasional dan sistem terpadu Mandala.
      </p>
    </div>

    <!-- General Error Bag / Status -->
    @if (session('status'))
      <div class="mb-5 p-3.5 bg-[#DCFCE7] border border-[#BBF7D0] text-[#166534] rounded-xl text-xs font-semibold flex items-center gap-2">
        <x-ui-icon name="check" class="w-4 h-4 text-[#166534] shrink-0" />
        <span>{{ session('status') }}</span>
      </div>
    @endif

    <form method="POST" action="{{ route('login') }}" @submit="handleSubmit($event)" class="space-y-5">
      @csrf

      <!-- Email Field -->
      <div>
        <label for="email" class="block mb-1.5 text-xs font-semibold text-[#111827]">
          Alamat Email Perusahaan <span class="text-[#DC2626]">*</span>
        </label>
        <div class="relative">
          <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-[#94A3B8]">
            <x-ui-icon name="mail" class="w-4 h-4" />
          </div>
          <input 
            id="email" 
            name="email" 
            type="email" 
            required 
            autofocus 
            autocomplete="username"
            x-model="email"
            placeholder="nama@perusahaan.com"
            class="w-full h-12 pl-10 pr-4 text-xs bg-white border border-[#D1D5DB] rounded-[10px] text-[#111827] placeholder-[#94A3B8] focus:border-[#1455D9] focus:ring-1 focus:ring-[#1455D9] transition"
            :class="errors.email ? 'border-[#DC2626] focus:border-[#DC2626] focus:ring-[#DC2626]' : ''"
          />
        </div>
        <template x-if="errors.email">
          <p class="mt-1.5 text-[11px] text-[#DC2626] flex items-center gap-1 font-medium" role="alert">
            <x-ui-icon name="warning" class="w-3.5 h-3.5 shrink-0" />
            <span x-text="errors.email"></span>
          </p>
        </template>
      </div>

      <!-- Password Field -->
      <div>
        <div class="flex items-center justify-between mb-1.5">
          <label for="password" class="text-xs font-semibold text-[#111827]">
            Kata Sandi <span class="text-[#DC2626]">*</span>
          </label>
          @if (Route::has('password.request'))
            <a href="{{ route('password.request') }}" class="text-xs font-medium text-[#1455D9] hover:underline">
              Lupa password?
            </a>
          @endif
        </div>
        <div class="relative">
          <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-[#94A3B8]">
            <x-ui-icon name="lock" class="w-4 h-4" />
          </div>
          <input 
            id="password" 
            name="password" 
            :type="showPassword ? 'text' : 'password'" 
            required 
            autocomplete="current-password"
            x-model="password"
            placeholder="Masukkan kata sandi Anda"
            class="w-full h-12 pl-10 pr-10 text-xs bg-white border border-[#D1D5DB] rounded-[10px] text-[#111827] placeholder-[#94A3B8] focus:border-[#1455D9] focus:ring-1 focus:ring-[#1455D9] transition"
            :class="errors.password ? 'border-[#DC2626] focus:border-[#DC2626] focus:ring-[#DC2626]' : ''"
          />
          <button 
            type="button" 
            @click="showPassword = !showPassword" 
            class="absolute inset-y-0 right-0 pr-3.5 flex items-center text-[#94A3B8] hover:text-[#475569] transition"
            title="Tampilkan / Sembunyikan Password"
          >
            <x-ui-icon name="eye" class="w-4 h-4" x-show="!showPassword" />
            <x-ui-icon name="eye-off" class="w-4 h-4" x-show="showPassword" />
          </button>
        </div>
        <template x-if="errors.password">
          <p class="mt-1.5 text-[11px] text-[#DC2626] flex items-center gap-1 font-medium" role="alert">
            <x-ui-icon name="warning" class="w-3.5 h-3.5 shrink-0" />
            <span x-text="errors.password"></span>
          </p>
        </template>
      </div>

      <!-- Remember Me -->
      <div class="flex items-center">
        <label class="inline-flex items-center gap-2 cursor-pointer select-none">
          <input 
            type="checkbox" 
            name="remember"
            class="w-4 h-4 rounded border-[#D1D5DB] text-[#1455D9] focus:ring-[#1455D9]"
          />
          <span class="text-xs text-[#475569]">Ingat saya di perangkat ini</span>
        </label>
      </div>

      <!-- Submit Button -->
      <div>
        <button 
          type="submit"
          :disabled="isSubmitting"
          class="w-full h-12 rounded-[10px] bg-[#1455D9] hover:bg-[#0046CC] disabled:opacity-75 disabled:pointer-events-none text-white text-xs sm:text-sm font-semibold transition flex items-center justify-center gap-2 shadow-xs cursor-pointer"
        >
          <template x-if="!isSubmitting">
            <span class="inline-flex items-center gap-2">
              <x-ui-icon name="log-in" class="w-4 h-4 text-white" />
              <span>Masuk</span>
            </span>
          </template>
          <template x-if="isSubmitting">
            <span class="inline-flex items-center gap-2">
              <svg class="animate-spin h-4 w-4 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
              </svg>
              <span>Memverifikasi akun...</span>
            </span>
          </template>
        </button>
      </div>

      <!-- Register Link if enabled -->
      @if (Route::has('register'))
        <div class="text-center pt-2">
          <span class="text-xs text-[#64748B]">Belum punya akun? </span>
          <a href="{{ route('register') }}" class="text-xs font-semibold text-[#1455D9] hover:underline">
            Daftar Sekarang
          </a>
        </div>
      @endif
    </form>

    <!-- Footer Branding -->
    <div class="mt-10 pt-6 border-t border-[#E2E8F0] flex flex-col items-center justify-center gap-2 text-center text-xs text-[#94A3B8]">
      <div class="flex items-center gap-2">
        <span>Powered by</span>
        <img src="{{ asset('assets/images/andalanlogo-removebg-preview.png') }}" alt="Andalan Group" class="h-4 w-auto object-contain" />
        <span class="font-bold text-[#111827]">Andalan Group</span>
      </div>
    </div>
  </div>
</x-guest-layout>
