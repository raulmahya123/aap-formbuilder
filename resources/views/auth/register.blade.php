<!doctype html>
<html lang="id" class="scroll-smooth">

<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <title>Register — Andalan Group</title>

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
          }
        }
      }
    }
  </script>
  <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@600&family=Poppins:wght@400;500;600&display=swap" rel="stylesheet">
</head>

<body class="grid min-h-screen md:grid-cols-12 bg-gradient-to-t from-maroon-900 via-maroon-600 to-maroon-300 text-ivory-100">

  <!-- LEFT: IMAGE + OVERLAY -->
  <div class="relative hidden overflow-hidden shadow-2xl md:block md:col-span-7 ring-1 ring-black/10">
    <!-- Background -->
    <div class="absolute inset-0 bg-cover bg-center bg-[url('{{ asset('assets/images/foto2.png') }}')]"></div>
    <div class="absolute inset-0 shadow-[inset_-32px_0_60px_rgba(0,0,0,.35),inset_0_0_80px_rgba(0,0,0,.25)]"></div>
    <div class="absolute inset-0 bg-gradient-to-tr from-maroon-900/95 via-maroon-800/75 to-maroon-900/10"></div>

    <!-- Branding + Visi Misi -->
    <div class="relative flex items-end h-full">
      <div class="absolute top-0 -translate-x-1/2 left-1/2">
        <img src="{{ asset('assets/images/logomandala.png') }}"
          alt="Logo Mandala"
          class="h-20 drop-shadow-lg">
      </div>
      <div class="p-10 space-y-6 lg:p-14">
        <h1 class="font-serif text-4xl leading-tight lg:text-5xl">Bergabung dengan<br>Generasi Baru</h1>
        <p class="max-w-xl mt-2 text-ivory-100/80">
          Daftarkan akun Anda untuk akses aplikasi internal Andalan Group.
        </p>
        <div class="mt-6 h-[2px] w-40 bg-gradient-to-r from-gold to-transparent"></div>

        <!-- Visi -->
        <div>
          <h3 class="mb-2 text-lg font-semibold text-gold">Visi</h3>
          <p class="text-sm leading-relaxed">
            Menjadi kontraktor pertambangan terkemuka dan terpercaya di Indonesia
            dengan kredibilitas dan komitmen dalam memberikan hasil, didukung oleh
            fondasi operasi pertambangan yang solid, sistem yang terintegrasi,
            dan kerja tim yang luar biasa.
          </p>
        </div>

        <!-- Misi -->
        <div>
          <h3 class="mb-2 text-lg font-semibold text-gold">Misi</h3>
          <ul class="space-y-1 text-sm leading-relaxed list-disc list-inside">
            <li>Memaksimalkan produktivitas operasional dengan sistem pemantauan yang sangat baik.</li>
            <li>Melakukan identifikasi yang tepat dengan pemeriksaan data yang teliti sebagai peningkatan dan perkembangan berkelanjutan.</li>
            <li>Memiliki pola pikir yang kuat dan tangguh untuk memiliki ketahanan mental dan emosional untuk bangkit kembali dari kesulitan, mengatasi tantangan, dan beradaptasi dengan situasi sulit.</li>
            <li>Memberikan solusi yang saling menguntungkan bagi para pemangku kepentingan.</li>
          </ul>
        </div>
      </div>
    </div>
  </div>

  <!-- RIGHT: REGISTER CARD -->
  <div class="flex items-center min-h-screen bg-white md:col-span-5">
    <div class="w-full max-w-md p-6 mx-auto sm:p-8">
      <!-- Logo center -->
      <div class="flex justify-center mb-6">
        <img src="{{ asset('assets/images/foto.png') }}"
          class="h-36 sm:h-40 drop-shadow-lg"
          alt="Logo AAP">
          
      </div>

      <div class="overflow-hidden border shadow-2xl bg-maroon-700 rounded-2xl border-maroon-600 text-black">
        <div class="p-6 sm:p-8">
          <div class="mb-6 text-center">
            <p class="text-sm uppercase tracking-[0.2em] text-black">Registrasi</p>
            <h2 class="mt-1 text-2xl font-semibold text-black">Buat Akun Baru</h2>
            <p class="mt-2 text-sm text-gray-900">Gunakan data yang valid untuk akses internal.</p>
          </div>

          <form method="POST" action="{{ route('register') }}" class="space-y-5">
            @csrf

            <!-- Name -->
            <div>
              <label for="name" class="block mb-1 text-sm font-medium text-gray-900">Nama Lengkap</label>
              <input id="name" name="name" type="text" value="{{ old('name') }}" required autofocus autocomplete="name"
                class="w-full px-3 py-2.5 text-black placeholder-gray-600 bg-white border rounded-lg border-gray-300 focus:border-maroon-700 focus:ring-maroon-700">
              @error('name') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
            </div>

            <!-- Email -->
            <div>
              <label for="email" class="block mb-1 text-sm font-medium text-gray-900">Email</label>
              <input id="email" name="email" type="email" value="{{ old('email') }}" required autocomplete="username"
                class="w-full px-3 py-2.5 text-black placeholder-gray-600 bg-white border rounded-lg border-gray-300 focus:border-maroon-700 focus:ring-maroon-700">
              @error('email') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
            </div>

            <!-- Password -->
            <div>
              <label for="password" class="block mb-1 text-sm font-medium text-gray-900">Password</label>
              <input id="password" name="password" type="password" required autocomplete="new-password"
                class="w-full px-3 py-2.5 text-black bg-white border rounded-lg border-gray-300 focus:border-maroon-700 focus:ring-maroon-700">
              @error('password') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
            </div>

            <!-- Confirm Password -->
            <div>
              <label for="password_confirmation" class="block mb-1 text-sm font-medium text-gray-900">Konfirmasi Password</label>
              <input id="password_confirmation" name="password_confirmation" type="password" required autocomplete="new-password"
                class="w-full px-3 py-2.5 text-black bg-white border rounded-lg border-gray-300 focus:border-maroon-700 focus:ring-maroon-700">
              @error('password_confirmation') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
            </div>

            <!-- Action -->
            <div class="flex items-center justify-between pt-2">
              <a href="{{ route('login') }}" class="text-sm font-medium text-black hover:text-gray-900">Sudah punya akun?</a>
              <button type="submit"
                class="inline-flex items-center justify-center gap-2 px-6 py-2.5 font-semibold text-black transition bg-white border rounded-lg border-white hover:bg-gray-100 focus:ring-2 focus:ring-white/70">
                <svg class="w-5 h-5 text-black stroke-black" viewBox="0 0 24 24" fill="none" stroke="currentColor" aria-hidden="true">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 5v14M5 12h14" />
                </svg>
                Daftar
              </button>
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

