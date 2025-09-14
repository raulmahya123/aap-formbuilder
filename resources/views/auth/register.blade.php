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
          }
        }
      }
    }
  </script>
  <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@600&family=Poppins:wght@400;500;600&display=swap" rel="stylesheet">
</head>

<body class="min-h-screen grid md:grid-cols-12 bg-gradient-to-t from-maroon-900 via-maroon-600 to-maroon-300 text-ivory-100">

  <!-- LEFT: IMAGE + OVERLAY -->
  <div class="hidden md:block md:col-span-7 relative">
    <!-- Background -->
    <div class="absolute inset-0 bg-cover bg-center bg-[url('{{ asset('assets/images/foto2.png') }}')]"></div>
    <div class="absolute inset-0 bg-gradient-to-tr from-maroon-900/95 via-maroon-800/75 to-maroon-900/10"></div>

    <!-- Branding + Visi Misi -->
    <div class="relative h-full flex items-end">
      <div class="absolute top-0 left-1/2 -translate-x-1/2">
        <img src="{{ asset('assets/images/logomandala.png') }}"
          alt="Logo Mandala"
          class="h-20 drop-shadow-lg">
      </div>
      <div class="p-10 lg:p-14 space-y-6">
        <h1 class="font-serif text-4xl lg:text-5xl leading-tight">Bergabung dengan<br>Generasi Baru</h1>
        <p class="mt-2 max-w-xl text-ivory-100/80">
          Daftarkan akun Anda untuk akses aplikasi internal Andalan Group.
        </p>
        <div class="mt-6 h-[2px] w-40 bg-gradient-to-r from-gold to-transparent"></div>

        <!-- Visi -->
        <div>
          <h3 class="font-semibold text-lg text-gold mb-2">Visi</h3>
          <p class="text-sm leading-relaxed">
            Menjadi kontraktor pertambangan terkemuka dan terpercaya di Indonesia
            dengan kredibilitas dan komitmen dalam memberikan hasil, didukung oleh
            fondasi operasi pertambangan yang solid, sistem yang terintegrasi,
            dan kerja tim yang luar biasa.
          </p>
        </div>

        <!-- Misi -->
        <div>
          <h3 class="font-semibold text-lg text-gold mb-2">Misi</h3>
          <ul class="list-disc list-inside space-y-1 text-sm leading-relaxed">
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
  <div class="md:col-span-5 min-h-screen flex items-center">
    <div class="w-full max-w-md mx-auto p-6 sm:p-8">
      <!-- Logo center -->
      <div class="flex justify-center mb-6">
        <img src="{{ asset('assets/images/foto.png') }}"
          class="h-28 drop-shadow-lg"
          alt="Logo AAP">
          <img src="{{ asset('assets/images/logo-abn.png') }}"
          class="h-40 drop-shadow-lg"
          alt="Logo AAP">
      </div>

      <div class="bg-maroon-800/60 backdrop-blur-md rounded-2xl border border-gold/40 shadow-2xl">
        <div class="p-6 sm:p-8">
          <div class="mb-6 text-center">
            <p class="text-sm uppercase tracking-[0.2em] text-ivory-100/70">Registrasi</p>
            <h2 class="mt-1 text-2xl font-semibold">Buat Akun Baru</h2>
          </div>

          <form method="POST" action="{{ route('register') }}" class="space-y-5">
            @csrf

            <!-- Name -->
            <div>
              <label for="name" class="block text-sm mb-1">Nama Lengkap</label>
              <input id="name" name="name" type="text" value="{{ old('name') }}" required autofocus autocomplete="name"
                class="w-full rounded-lg bg-maroon-900/60 border border-maroon-700 text-ivory-100 placeholder-ivory-100/40 focus:border-gold focus:ring-gold">
              @error('name') <p class="text-red-500 text-sm mt-1">{{ $message }}</p> @enderror
            </div>

            <!-- Email -->
            <div>
              <label for="email" class="block text-sm mb-1">Email</label>
              <input id="email" name="email" type="email" value="{{ old('email') }}" required autocomplete="username"
                class="w-full rounded-lg bg-maroon-900/60 border border-maroon-700 text-ivory-100 placeholder-ivory-100/40 focus:border-gold focus:ring-gold">
              @error('email') <p class="text-red-500 text-sm mt-1">{{ $message }}</p> @enderror
            </div>

            <!-- Password -->
            <div>
              <label for="password" class="block text-sm mb-1">Password</label>
              <input id="password" name="password" type="password" required autocomplete="new-password"
                class="w-full rounded-lg bg-maroon-900/60 border border-maroon-700 text-ivory-100 focus:border-gold focus:ring-gold">
              @error('password') <p class="text-red-500 text-sm mt-1">{{ $message }}</p> @enderror
            </div>

            <!-- Confirm Password -->
            <div>
              <label for="password_confirmation" class="block text-sm mb-1">Konfirmasi Password</label>
              <input id="password_confirmation" name="password_confirmation" type="password" required autocomplete="new-password"
                class="w-full rounded-lg bg-maroon-900/60 border border-maroon-700 text-ivory-100 focus:border-gold focus:ring-gold">
              @error('password_confirmation') <p class="text-red-500 text-sm mt-1">{{ $message }}</p> @enderror
            </div>

            <!-- Action -->
            <div class="flex items-center justify-between pt-2">
              <a href="{{ route('login') }}" class="text-xs underline hover:text-gold">Sudah punya akun?</a>
              <button type="submit"
                class="px-6 py-2 rounded-lg bg-maroon-700 border border-gold/60 font-semibold hover:bg-maroon-900 hover:border-gold focus:ring-gold">
                Daftar
              </button>
            </div>
          </form>

          <div class="mt-6 text-center text-xs text-ivory-100/50">
            © {{ date('Y') }} Andalan Group
          </div>
        </div>
      </div>
    </div>
  </div>

</body>

</html>