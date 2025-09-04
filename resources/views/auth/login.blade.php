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
            ivory: {
              100: '#F7F5F0'
            },
            maroon: {
              900: '#551219', // merah paling gelap
              800: '#7b1e2b',
              700: '#991a25',
              600: '#a32638', // Alabama Crimson (utama)
              500: '#ba202e',
              400: '#d6737b',
              300: '#e7a8ad',
              200: '#f2cfd2',
              100: '#fae9ea',
              50: '#fdf4f5',
            },
            gold: '#D4AF37',
          },
          fontFamily: {
            serif: ['"Playfair Display"', 'serif'],
            sans: ['Poppins', 'sans-serif']
          },
          dropShadow: {
            gold: '0 0 12px rgba(212,175,55,.35)'
          }
        }
      }
    }
  </script>
  <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@600&family=Poppins:wght@400;500;600&display=swap" rel="stylesheet">
</head>

<body class="min-h-screen grid md:grid-cols-12 bg-gradient-to-t from-maroon-900 via-maroon-600 to-maroon-300 text-ivory-100 font-sans">

  <!-- LEFT BRANDING + EFFORT -->
  <div class="hidden md:block md:col-span-7 relative overflow-hidden">
    <!-- Foto tambang -->
    <div class="absolute inset-0 bg-cover bg-center" style="background-image:url('assets/images/foto1.png')"></div>

    <!-- Overlay gradasi maroon -->
    <div class="absolute inset-0 bg-gradient-to-tr from-maroon-900/95 via-maroon-800/75 to-maroon-900/10"></div>

    <!-- Content branding -->
    <div class="relative h-full flex flex-col justify-between">
      <div class="absolute top-0 left-1/2 -translate-x-1/2">
        <img src="{{ asset('assets/images/logomandala.png') }}"
          alt="Logo Mandala"
          class="h-20 drop-shadow-lg">
      </div>

      <!-- Headline & sub -->
      <div class="p-10 lg:p-14 text-center mt-20">
        <h1 class="font-serif text-4xl sm:text-5xl lg:text-6xl leading-tight drop-shadow">
          Kontraktor Tambang<br>Generasi Baru
        </h1>
        <p class="mt-6 max-w-2xl mx-auto text-base text-ivory-100/85">
          Spesialis nikel, overburden, hauling, mine plan, compliance & ESG.<br>
          Kami menambang dengan presisi & integritas.
        </p>
        <div class="mt-8 mx-auto h-[3px] w-52 bg-gradient-to-r from-gold to-transparent drop-shadow-gold"></div>
      </div>


      <!-- CORE VALUE: EFFORT (unik & keren) -->
      <div class="p-10 lg:p-14">
        <div class="mb-6">
          <p class="text-xs uppercase tracking-[0.25em] text-ivory-100/60">Core Value</p>
          <h3 class="text-2xl font-semibold text-gold drop-shadow-gold">
            EFFORT
          </h3>
        </div>

        <!-- Dekor garis emas tipis -->
        <div class="relative">
          <div class="pointer-events-none absolute -top-3 left-0 right-0 h-[1px] bg-gradient-to-r from-transparent via-gold/60 to-transparent"></div>

          <!-- Grid lingkaran E F F O R T -->
          <div class="grid grid-cols-3 gap-6 sm:grid-cols-6">

            <!-- Item template:
              - Lingkaran huruf (huruf besar)
              - Judul & deskripsi singkat
            -->
            <!-- E -->
            <div class="group text-center">
              <div class="mx-auto w-16 h-16 rounded-full border-2 border-gold bg-maroon-800/70
                          flex items-center justify-center transition
                          group-hover:scale-110 group-hover:drop-shadow-gold">
                <span class="text-gold font-bold text-xl">E</span>
              </div>
              <h4 class="mt-3 text-sm font-semibold text-gold">Excellence</h4>
              <p class="mt-1 text-[11px] leading-snug text-ivory-100/75">
                Standar kualitas tinggi & keselamatan kerja.
              </p>
            </div>

            <!-- F -->
            <div class="group text-center">
              <div class="mx-auto w-16 h-16 rounded-full border-2 border-gold bg-maroon-800/70
                          flex items-center justify-center transition
                          group-hover:scale-110 group-hover:drop-shadow-gold">
                <span class="text-gold font-bold text-xl">F</span>
              </div>
              <h4 class="mt-3 text-sm font-semibold text-gold">Focus</h4>
              <p class="mt-1 text-[11px] leading-snug text-ivory-100/75">
                Proyek sesuai target waktu, biaya, & keberlanjutan.
              </p>
            </div>

            <!-- F (Fortitude) -->
            <div class="group text-center">
              <div class="mx-auto w-16 h-16 rounded-full border-2 border-gold bg-maroon-800/70
                          flex items-center justify-center transition
                          group-hover:scale-110 group-hover:drop-shadow-gold">
                <span class="text-gold font-bold text-xl">F</span>
              </div>
              <h4 class="mt-3 text-sm font-semibold text-gold">Fortitude</h4>
              <p class="mt-1 text-[11px] leading-snug text-ivory-100/75">
                Berani & tangguh menghadapi tantangan.
              </p>
            </div>

            <!-- O -->
            <div class="group text-center">
              <div class="mx-auto w-16 h-16 rounded-full border-2 border-gold bg-maroon-800/70
                          flex items-center justify-center transition
                          group-hover:scale-110 group-hover:drop-shadow-gold">
                <span class="text-gold font-bold text-xl">O</span>
              </div>
              <h4 class="mt-3 text-sm font-semibold text-gold">Optimism</h4>
              <p class="mt-1 text-[11px] leading-snug text-ivory-100/75">
                Semangat positif, solusi inovatif, adaptif.
              </p>
            </div>

            <!-- R -->
            <div class="group text-center">
              <div class="mx-auto w-16 h-16 rounded-full border-2 border-gold bg-maroon-800/70
                          flex items-center justify-center transition
                          group-hover:scale-110 group-hover:drop-shadow-gold">
                <span class="text-gold font-bold text-xl">R</span>
              </div>
              <h4 class="mt-3 text-sm font-semibold text-gold">Responsive</h4>
              <p class="mt-1 text-[11px] leading-snug text-ivory-100/75">
                Tanggap risiko & kebutuhan klien.
              </p>
            </div>

            <!-- T -->
            <div class="group text-center">
              <div class="mx-auto w-16 h-16 rounded-full border-2 border-gold bg-maroon-800/70
                          flex items-center justify-center transition
                          group-hover:scale-110 group-hover:drop-shadow-gold">
                <span class="text-gold font-bold text-xl">T</span>
              </div>
              <h4 class="mt-3 text-sm font-semibold text-gold">Tenacity</h4>
              <p class="mt-1 text-[11px] leading-snug text-ivory-100/75">
                Gigih capai target & keberlanjutan.
              </p>
            </div>
          </div>

          <!-- Garis dekor bawah -->
          <div class="pointer-events-none mt-8 h-[1px] bg-gradient-to-r from-transparent via-gold/60 to-transparent"></div>
        </div>
      </div>
    </div>
  </div>

  <!-- RIGHT LOGIN -->
  <div class="md:col-span-5 min-h-screen flex items-center">
    <div class="w-full max-w-md mx-auto p-6 sm:p-8">

      <!-- Logo Center -->
      <div class="flex justify-center mb-6">
        <img src="{{ asset('assets/images/foto.png') }}" class="h-28 drop-shadow-lg" alt="Logo AAP">
        <img src="{{ asset('assets/images/logo-abn.png') }}" class="h-40 drop-shadow-lg" alt="Logo AAP">
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

            <!-- Register link -->
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