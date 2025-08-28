{{-- resources/views/unauthorized.blade.php --}}
@extends('layouts.app')

@section('title', '403 — Unauthorized')

@section('content')
<div
  x-data="unauth403()"
  x-init="init()"
  :class="dark ? 'dark' : ''"
  class="relative min-h-screen overflow-hidden bg-gradient-to-b from-ivory-100 to-white dark:from-coal-950 dark:to-coal-900 text-coal-800 dark:text-ivory-100"
  @keydown.window.escape="goBack()"
>
  {{-- BACKGROUND PARTICLES --}}
  <canvas x-ref="bg" class="pointer-events-none absolute inset-0 opacity-50"></canvas>

  {{-- TOP BAR --}}
  <div class="absolute top-0 inset-x-0 flex items-center justify-between px-5 py-3 text-xs">
    <div class="flex items-center gap-2">
      <span class="inline-flex items-center gap-1 px-2 py-1 rounded-full bg-maroon-700/10 text-maroon-800 dark:text-maroon-200 dark:bg-maroon-900/30">
        {{-- Shield Icon --}}
        <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor">
          <path stroke-width="1.5" d="M12 3l7 3v6c0 5-3.5 8-7 9-3.5-1-7-4-7-9V6l7-3z"/>
          <path stroke-width="1.5" d="M9.5 12l2 2 3-4"/>
        </svg>
        Access Guard
      </span>
      <span class="hidden sm:inline text-coal-500 dark:text-coal-300">Kode:
        <kbd class="px-1.5 py-0.5 rounded bg-black/5 dark:bg-white/10">403</kbd>
        • Tekan <kbd class="px-1 py-0.5 rounded bg-black/5 dark:bg-white/10">Esc</kbd> untuk kembali
      </span>
    </div>

    <button @click="toggleTheme"
            class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl border border-coal-200/60 dark:border-coal-700/60 hover:scale-[1.02] active:scale-95 transition">
      <svg x-show="!dark" class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor">
        <circle cx="12" cy="12" r="4" stroke-width="1.5"/>
        <path stroke-width="1.5" d="M12 2v2m0 16v2m10-10h-2M4 12H2m15.5-7.5L19 6m-14 12l1.5 1.5m0-15L5 6m14 12l-1.5 1.5"/>
      </svg>
      <svg x-show="dark" class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor">
        <path stroke-width="1.5" d="M21 12.79A9 9 0 1 1 11.21 3 7 7 0 0 0 21 12.79z"/>
      </svg>
    </button>
  </div>

  {{-- CONTENT CARD --}}
  <div class="relative z-10 max-w-3xl mx-auto px-6 pt-28 pb-16 sm:pt-36">
    <div
      @mousemove="tilt($event)"
      @mouseleave="resetTilt()"
      :style="{'transform': `rotateX(${rx}deg) rotateY(${ry}deg)`, 'box-shadow': shadow}"
      class="group rounded-2xl border border-coal-200/70 dark:border-coal-700/60 bg-ivory-50/80 dark:bg-coal-900/70 backdrop-blur p-8 sm:p-12 transition-transform duration-200 will-change-transform"
    >
      {{-- BIG SECTION --}}
      <div class="flex flex-col items-center justify-center gap-4">
        <div class="flex items-center justify-center gap-4">
          <span class="text-7xl sm:text-8xl font-black tracking-tight bg-clip-text text-transparent
                       bg-gradient-to-br from-maroon-700 via-maroon-600 to-maroon-400
                       dark:from-maroon-300 dark:via-maroon-200 dark:to-ivory-100 select-none">
            403
          </span>
          {{-- Lock Icon --}}
          <div class="p-2 rounded-xl bg-maroon-700/10 dark:bg-maroon-900/30">
            <svg class="w-12 h-12 text-maroon-700 dark:text-maroon-300" viewBox="0 0 24 24" fill="none" stroke="currentColor">
              <rect x="3" y="11" width="18" height="10" rx="2" stroke-width="1.5"/>
              <path stroke-width="1.5" d="M7 11V8a5 5 0 0 1 10 0v3"/>
            </svg>
          </div>
        </div>

        <h2 class="mt-2 text-3xl sm:text-4xl font-serif tracking-tight text-center">Akses Ditolak</h2>
        <p class="mt-2 text-base text-center text-coal-600 dark:text-coal-300">
          {{ $message ?? 'Anda tidak memiliki izin untuk mengakses halaman ini.' }}
        </p>

        {{-- ICON INFO DI BAWAH TEKS --}}
        <div class="mt-3 flex items-center justify-center gap-2 text-sm text-coal-500 dark:text-coal-300">
          <svg class="w-5 h-5 text-maroon-600 dark:text-maroon-300" viewBox="0 0 24 24" fill="none" stroke="currentColor">
            <circle cx="12" cy="12" r="10" stroke-width="1.5"/>
            <line x1="12" y1="8" x2="12" y2="13" stroke-width="1.5"/>
            <circle cx="12" cy="16" r="0.5" fill="currentColor"/>
          </svg>
          <span>Hubungi admin departemen atau Super Admin untuk meminta akses.</span>
        </div>
      </div>

      {{-- ACTIONS --}}
      <div class="mt-8 flex flex-col sm:flex-row items-center justify-center gap-3">
        <a @click.prevent="goBack()"
           href="{{ url()->previous() }}"
           class="w-full sm:w-auto inline-flex items-center justify-center gap-2 px-5 py-2.5 rounded-xl
                  bg-maroon-700 text-ivory-50 hover:bg-maroon-600 active:scale-95 transition">
          <svg class="w-4 h-4 -ml-0.5" viewBox="0 0 24 24" fill="none" stroke="currentColor">
            <path stroke-width="1.5" d="M7 12h10M7 12l4-4M7 12l4 4"/>
          </svg>
          Kembali
        </a>

        <a href="{{ route('dashboard') }}"
           class="w-full sm:w-auto inline-flex items-center justify-center gap-2 px-5 py-2.5 rounded-xl
                  border border-maroon-600/60 text-maroon-700 hover:bg-maroon-50/70
                  dark:text-maroon-300 dark:hover:bg-maroon-900/20 active:scale-95 transition">
          {{-- Home Icon --}}
          <svg class="w-4 h-4 -ml-0.5" viewBox="0 0 24 24" fill="none" stroke="currentColor">
            <path stroke-width="1.5" d="M3 11l9-7 9 7M5 10v10h14V10"/>
          </svg>
          Ke Dashboard
        </a>

        @auth
          <form method="POST" action="{{ route('logout') }}" class="w-full sm:w-auto">
            @csrf
            <button type="submit"
                    class="w-full sm:w-auto inline-flex items-center justify-center gap-2 px-5 py-2.5 rounded-xl
                           border border-coal-300/70 dark:border-coal-700/70 hover:bg-black/5 dark:hover:bg-white/5
                           active:scale-95 transition">
              {{-- Logout Icon --}}
              <svg class="w-4 h-4 -ml-0.5" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                <path stroke-width="1.5" d="M10 12h9m0 0l-3-3m3 3l-3 3"/>
                <path stroke-width="1.5" d="M15 7V6a3 3 0 0 0-3-3H6a3 3 0 0 0-3 3v12a3 3 0 0 0 3 3h6a3 3 0 0 0 3-3v-1"/>
              </svg>
              Logout
            </button>
          </form>
        @endauth
      </div>

      {{-- TIP ROW (tetap ada, bisa dihapus kalau tidak perlu) --}}
      <div class="mt-6 text-[11px] text-center text-coal-500 dark:text-coal-400">
        Butuh akses? Hubungi admin departemen atau Super Admin.
      </div>
    </div>
  </div>
</div>

{{-- Alpine Component --}}
<script>
function unauth403(){
  return {
    dark: localStorage.getItem('theme')
          ? localStorage.getItem('theme') === 'dark'
          : window.matchMedia('(prefers-color-scheme: dark)').matches,

    rx: 0, ry: 0, shadow: '0 20px 60px rgba(0,0,0,0.10)',

    init(){
      document.documentElement.classList.toggle('dark', this.dark);
      this.drawParticles();
      window.addEventListener('resize', () => this.drawParticles(true));
    },

    toggleTheme(){
      this.dark = !this.dark;
      localStorage.setItem('theme', this.dark ? 'dark' : 'light');
      document.documentElement.classList.toggle('dark', this.dark);
      this.drawParticles(true);
    },

    goBack(){
      // fallback kalau history kosong
      if (document.referrer === '') {
        window.location.href = @json(route('dashboard'));
      } else {
        window.history.back();
      }
    },

    tilt(e){
      const card = e.currentTarget.getBoundingClientRect();
      const cx = e.clientX - card.left;
      const cy = e.clientY - card.top;
      const px = (cx / card.width) - 0.5;
      const py = (cy / card.height) - 0.5;
      this.ry = px * 6;  // rotateY
      this.rx = -py * 6; // rotateX
      this.shadow = `${-px*10}px ${py*20}px 60px rgba(0,0,0,0.20)`;
    },

    resetTilt(){
      this.rx = 0; this.ry = 0;
      this.shadow = '0 20px 60px rgba(0,0,0,0.10)';
    },

    drawParticles(force=false){
      const c = this.$refs.bg;
      const ctx = c.getContext('2d');
      const DPR = window.devicePixelRatio || 1;
      const w = c.clientWidth, h = c.clientHeight;
      if(force || c.width !== w*DPR || c.height !== h*DPR){
        c.width = w*DPR; c.height = h*DPR; ctx.scale(DPR, DPR);
      }
      // simple star/diamond field with parallax-ish jitter
      const N = Math.floor((w*h)/18000);
      ctx.clearRect(0,0,w,h);
      const color = this.dark ? 'rgba(212,175,55,0.25)' : 'rgba(91,30,35,0.20)'; // gold / maroon
      for(let i=0;i<N;i++){
        const x = Math.random()*w, y = Math.random()*h, s = Math.random()*2+0.5;
        ctx.save();
        ctx.translate(x,y);
        ctx.rotate(Math.random()*Math.PI);
        ctx.fillStyle = color;
        ctx.beginPath();
        ctx.moveTo(0,-s);
        ctx.lineTo(s,0);
        ctx.lineTo(0,s);
        ctx.lineTo(-s,0);
        ctx.closePath();
        ctx.fill();
        ctx.restore();
      }
    }
  }
}
</script>
@endsection
