<!doctype html>
<html lang="id" class="scroll-smooth">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <title>Login — PT Andalan Artha Primanusa</title>

  <!-- Tailwind -->
  <script src="https://cdn.tailwindcss.com"></script>
  <script>
    tailwind.config = {
      theme: {
        extend: {
          colors: {
            ivory: {100: '#F7F5F0'},
            maroon: { 900:'#3B0D11', 800:'#5C1E23', 700:'#7A2C2F' },
            gold: '#D4AF37',
          },
          fontFamily: {
            serif: ['"Playfair Display"', 'serif'],
            sans: ['Poppins','sans-serif']
          }
        }
      }
    }
  </script>
  <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@600&family=Poppins:wght@400;500;600&display=swap" rel="stylesheet">
</head>
<body class="min-h-screen grid md:grid-cols-12 bg-maroon-900 text-ivory-100">

  <!-- LEFT BACKGROUND -->
  <div class="hidden md:block md:col-span-7 relative">
    <!-- Foto tambang -->
    <div class="absolute inset-0 bg-cover bg-center" style="background-image:url('/images/mining-bg.jpg')"></div>
    <!-- Overlay gradasi maroon -->
    <div class="absolute inset-0 bg-gradient-to-tr from-maroon-900/95 via-maroon-800/70 to-transparent"></div>

    <!-- Content branding -->
    <div class="relative h-full flex items-end">
      <div class="p-10 lg:p-14">
        <h1 class="font-serif text-4xl lg:text-5xl leading-tight">Kontraktor Tambang<br>Generasi Baru</h1>
        <p class="mt-4 max-w-xl text-ivory-100/80">
          Spesialis nikel, overburden, hauling, mine plan, compliance & ESG.<br>
          Kami menambang dengan presisi & integritas.
        </p>
        <div class="mt-8 h-[2px] w-40 bg-gradient-to-r from-gold to-transparent"></div>
      </div>
    </div>
  </div>

  <!-- RIGHT LOGIN -->
  <div class="md:col-span-5 min-h-screen flex items-center">
    <div class="w-full max-w-md mx-auto p-6 sm:p-8">

      <!-- Logo Center -->
      <div class="flex justify-center mb-6">
        <img src="{{ asset('assets/images/foto.png') }}" class="h-28 drop-shadow-lg" alt="Logo AAP">
      </div>

      <div class="bg-maroon-800/60 backdrop-blur-md rounded-2xl border border-gold/40 shadow-2xl">
        <div class="p-6 sm:p-8">
          <div class="mb-6 text-center">
            <p class="text-sm uppercase tracking-[0.2em] text-ivory-100/70">Selamat Datang</p>
            <h2 class="mt-1 text-2xl font-semibold">Masuk ke Akun Anda</h2>
          </div>

          @if (session('status'))
            <div class="mb-4 text-sm text-green-500">{{ session('status') }}</div>
          @endif

          <form method="POST" action="{{ route('login') }}" class="space-y-5">
            @csrf
            <!-- Email -->
            <div>
              <label for="email" class="block text-sm mb-1">Email</label>
              <input id="email" name="email" type="email" required autofocus autocomplete="username"
                     value="{{ old('email') }}"
                     class="w-full rounded-lg bg-maroon-900/60 border border-maroon-700 text-ivory-100 placeholder-ivory-100/40 focus:border-gold focus:ring-gold">
              @error('email') <p class="text-red-500 text-sm mt-1">{{ $message }}</p> @enderror
            </div>

            <!-- Password -->
            <div>
              <label for="password" class="block text-sm mb-1">Password</label>
              <input id="password" name="password" type="password" required autocomplete="current-password"
                     class="w-full rounded-lg bg-maroon-900/60 border border-maroon-700 text-ivory-100 focus:border-gold focus:ring-gold">
              @error('password') <p class="text-red-500 text-sm mt-1">{{ $message }}</p> @enderror
            </div>

            <!-- Remember -->
            <label class="inline-flex items-center gap-2">
              <input type="checkbox" name="remember"
                     class="rounded border-maroon-700 bg-transparent text-gold focus:ring-gold">
              <span class="text-sm">Ingat saya</span>
            </label>

            <!-- Button -->
            <button type="submit"
                    class="w-full py-3 rounded-lg bg-maroon-700 border border-gold/60 font-semibold hover:bg-maroon-900 hover:border-gold focus:ring-gold">
              Masuk
            </button>

            <!-- Register link (pengganti lupa password) -->
            <div class="text-center mt-3">
              <a href="{{ route('register') }}" class="text-xs underline hover:text-gold">Belum punya akun? Daftar</a>
            </div>
          </form>

          <div class="mt-6 text-center text-xs text-ivory-100/50">
            © {{ date('Y') }} PT Andalan Artha Primanusa
          </div>
        </div>
      </div>
    </div>
  </div>

</body>
</html>
