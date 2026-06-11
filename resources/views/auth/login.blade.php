<!doctype html>
<html lang="id" class="scroll-smooth">

<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <title>Login — Andalan Group</title>

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
              900: '#bb9974',
              800: '#bb9974',
              700: '#bb9974',
              600: '#bb9974',
              500: '#bb9974',
              400: '#bb9974',
              300: '#bb9974',
              200: '#bb9974',
              100: '#bb9974',
              50: '#bb9974',
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
  <style>
    @media (min-width: 768px) and (max-height: 780px) {
      .login-brand-copy {
        margin-top: 4rem;
        padding: 2rem;
      }

      .login-brand-title {
        font-size: clamp(2.25rem, 4vw, 3rem);
      }

      .login-brand-desc {
        margin-top: .75rem;
        font-size: .875rem;
      }

      .login-brand-rule {
        margin-top: 1rem;
      }

      .login-values {
        padding: 1.25rem 2rem 1.5rem;
      }

      .login-value-grid {
        gap: .75rem;
      }

      .login-value-icon {
        width: 3rem;
        height: 3rem;
      }

      .login-value-copy {
        display: none;
      }
    }
  </style>
</head>

<body class="grid min-h-screen font-sans md:grid-cols-12 bg-gradient-to-t from-maroon-900 via-maroon-600 to-maroon-300 text-ivory-100">

  <!-- LEFT BRANDING + EFFORT -->
  <div class="relative hidden overflow-hidden md:block md:col-span-7 login-brand-panel">
    <!-- Foto tambang -->
    <div class="absolute inset-0 bg-center bg-cover" style="background-image:url('assets/images/foto1.png')"></div>

    <!-- Overlay gradasi maroon -->

    <!-- Content branding -->
    <div class="relative flex flex-col justify-between h-full">
      <div class="absolute top-0 -translate-x-1/2 left-1/2">
        <img src="{{ asset('assets/images/logomandala.png') }}"
          alt="Logo Mandala"
          class="h-20 drop-shadow-lg">
      </div>

      <!-- Headline & sub -->
      <div class="p-10 mt-20 text-center lg:p-14 login-brand-copy">
        <h1 class="font-serif text-4xl leading-tight sm:text-5xl lg:text-6xl drop-shadow login-brand-title">
          Kontraktor Tambang<br>Generasi Baru
        </h1>
        <p class="max-w-2xl mx-auto mt-6 text-base text-ivory-100/85 login-brand-desc">
          Spesialis nikel, overburden, hauling, mine plan, compliance & ESG.<br>
          Kami menambang dengan presisi & integritas.
        </p>
        <div class="mt-8 mx-auto h-[3px] w-52 bg-gradient-to-r from-gold to-transparent drop-shadow-gold login-brand-rule"></div>
      </div>


      <!-- CORE VALUE: EFFORT (unik & keren) -->
      <div class="p-10 lg:p-14 login-values">
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
          <div class="grid grid-cols-3 gap-6 sm:grid-cols-6 login-value-grid">

            <!-- Item template:
              - Lingkaran huruf (huruf besar)
              - Judul & deskripsi singkat
            -->
            <!-- E -->
            <div class="text-center group">
              <div class="flex items-center justify-center w-16 h-16 mx-auto transition border-2 rounded-full border-gold bg-maroon-800/70 group-hover:scale-110 group-hover:drop-shadow-gold login-value-icon">
                <span class="text-xl font-bold text-gold">E</span>
              </div>
              <h4 class="mt-3 text-sm font-semibold text-gold">Excellence</h4>
              <p class="mt-1 text-[11px] leading-snug text-ivory-100/75 login-value-copy">
                Standar kualitas tinggi & keselamatan kerja.
              </p>
            </div>

            <!-- F -->
            <div class="text-center group">
              <div class="flex items-center justify-center w-16 h-16 mx-auto transition border-2 rounded-full border-gold bg-maroon-800/70 group-hover:scale-110 group-hover:drop-shadow-gold login-value-icon">
                <span class="text-xl font-bold text-gold">F</span>
              </div>
              <h4 class="mt-3 text-sm font-semibold text-gold">Focus</h4>
              <p class="mt-1 text-[11px] leading-snug text-ivory-100/75 login-value-copy">
                Proyek sesuai target waktu, biaya, & keberlanjutan.
              </p>
            </div>

            <!-- F (Fortitude) -->
            <div class="text-center group">
              <div class="flex items-center justify-center w-16 h-16 mx-auto transition border-2 rounded-full border-gold bg-maroon-800/70 group-hover:scale-110 group-hover:drop-shadow-gold login-value-icon">
                <span class="text-xl font-bold text-gold">F</span>
              </div>
              <h4 class="mt-3 text-sm font-semibold text-gold">Fortitude</h4>
              <p class="mt-1 text-[11px] leading-snug text-ivory-100/75 login-value-copy">
                Berani & tangguh menghadapi tantangan.
              </p>
            </div>

            <!-- O -->
            <div class="text-center group">
              <div class="flex items-center justify-center w-16 h-16 mx-auto transition border-2 rounded-full border-gold bg-maroon-800/70 group-hover:scale-110 group-hover:drop-shadow-gold login-value-icon">
                <span class="text-xl font-bold text-gold">O</span>
              </div>
              <h4 class="mt-3 text-sm font-semibold text-gold">Optimism</h4>
              <p class="mt-1 text-[11px] leading-snug text-ivory-100/75 login-value-copy">
                Semangat positif, solusi inovatif, adaptif.
              </p>
            </div>

            <!-- R -->
            <div class="text-center group">
              <div class="flex items-center justify-center w-16 h-16 mx-auto transition border-2 rounded-full border-gold bg-maroon-800/70 group-hover:scale-110 group-hover:drop-shadow-gold login-value-icon">
                <span class="text-xl font-bold text-gold">R</span>
              </div>
              <h4 class="mt-3 text-sm font-semibold text-gold">Responsive</h4>
              <p class="mt-1 text-[11px] leading-snug text-ivory-100/75 login-value-copy">
                Tanggap risiko & kebutuhan klien.
              </p>
            </div>

            <!-- T -->
            <div class="text-center group">
              <div class="flex items-center justify-center w-16 h-16 mx-auto transition border-2 rounded-full border-gold bg-maroon-800/70 group-hover:scale-110 group-hover:drop-shadow-gold login-value-icon">
                <span class="text-xl font-bold text-gold">T</span>
              </div>
              <h4 class="mt-3 text-sm font-semibold text-gold">Tenacity</h4>
              <p class="mt-1 text-[11px] leading-snug text-ivory-100/75 login-value-copy">
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
  <div class="flex items-center min-h-screen bg-white md:col-span-5">
    <div class="w-full max-w-md p-6 mx-auto sm:p-8">

      <!-- Logo Center -->
      <div class="flex justify-center mb-6">
        <img src="{{ asset('assets/images/foto.png') }}" class="h-36 sm:h-40 drop-shadow-lg" alt="Logo AAP">
      </div>

      <div class="overflow-hidden border shadow-2xl bg-maroon-700 rounded-2xl border-maroon-600 text-black">
        <div class="p-6 sm:p-8">
          <div class="mb-6 text-center">
            <p class="text-sm uppercase tracking-[0.2em] text-black">Selamat Datang</p>
            <h2 class="mt-1 text-2xl font-semibold text-black">Masuk ke Akun Anda</h2>
            <p class="mt-2 text-sm text-gray-900">Akses dashboard operasional Andalan Group.</p>
          </div>

          @if (session('status'))
          <div class="px-3 py-2 mb-4 text-sm border rounded-lg text-emerald-700 bg-emerald-50 border-emerald-200">{{ session('status') }}</div>
          @endif

          <form method="POST" action="{{ route('login') }}" class="space-y-5">
            @csrf
            <!-- Email -->
            <div>
              <label for="email" class="block mb-1 text-sm font-medium text-gray-900">Email</label>
              <input id="email" name="email" type="email" required autofocus autocomplete="username"
                value="{{ old('email') }}"
                class="w-full px-3 py-2.5 text-black placeholder-gray-600 bg-white border rounded-lg border-gray-300 focus:border-maroon-700 focus:ring-maroon-700">
              @error('email') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
            </div>

            <!-- Password -->
            <div>
              <label for="password" class="block mb-1 text-sm font-medium text-gray-900">Password</label>
              <input id="password" name="password" type="password" required autocomplete="current-password"
                class="w-full px-3 py-2.5 text-black bg-white border rounded-lg border-gray-300 focus:border-maroon-700 focus:ring-maroon-700">
              @error('password') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
            </div>

            <!-- Remember -->
            <label class="inline-flex items-center gap-2 text-black">
              <input type="checkbox" name="remember"
                class="bg-white rounded border-gray-300 text-maroon-700 focus:ring-maroon-700">
              <span class="text-sm">Ingat saya</span>
            </label>

            <!-- Button -->
            <button type="submit"
              class="inline-flex items-center justify-center w-full gap-2 py-3 font-semibold text-black transition bg-white border rounded-lg border-white hover:bg-gray-100 focus:ring-2 focus:ring-white/70">
              <svg class="w-5 h-5 text-black stroke-black" viewBox="0 0 24 24" fill="none" stroke="currentColor" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 3h4a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2h-4M10 17l5-5-5-5M15 12H3" />
              </svg>
              Masuk
            </button>

            <!-- Register link -->
            <div class="mt-3 text-center">
              <a href="{{ route('register') }}" class="text-sm font-medium text-black hover:text-gray-900">Belum punya akun? Daftar</a>
            </div>
          </form>

          <div class="pt-5 mt-6 text-xs text-center border-t text-gray-900 border-black/10">
            © {{ date('Y') }} Andalan Group
          </div>
        </div>
      </div>
    </div>
  </div>

</body>

</html>

