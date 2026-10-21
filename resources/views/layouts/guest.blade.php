<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="theme-color" content="#1455D9">
    <link rel="icon" type="image/png" href="{{ asset('assets/images/andalanlogo-removebg-preview.png') }}">

    <title>{{ config('app.name', 'Mandala') }} — Management System Andalan</title>

    <link rel="preload" href="{{ Vite::asset('resources/fonts/InterVariable.woff2') }}" as="font" type="font/woff2" crossorigin>

    <!-- Scripts & Styles -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <style>
      /* Ken Burns Subtle Slideshow Animation */
      @keyframes kenburns {
        0% { transform: scale(1.0); }
        50% { transform: scale(1.04); }
        100% { transform: scale(1.0); }
      }
      .animate-kenburns {
        animation: kenburns 25s ease-in-out infinite alternate;
      }
      .fade-enter {
        transition: opacity 1s ease-in-out;
      }
    </style>
</head>
<body class="h-full bg-white font-sans text-[#111827] antialiased">
    <div class="min-h-screen flex flex-col lg:flex-row overflow-hidden">
        
        <!-- ======================================================== -->
        <!-- KOLOM KIRI (55%): Operational Mining Visual Showcase     -->
        <!-- ======================================================== -->
        <div 
          x-data="{
            slides: [
              '{{ asset('assets/images/foto1.png') }}',
              '{{ asset('assets/images/foto2.png') }}'
            ],
            currentSlide: 0,
            init() {
              setInterval(() => {
                this.currentSlide = (this.currentSlide + 1) % this.slides.length;
              }, 7000);
            }
          }"
          class="hidden lg:flex lg:w-[55%] relative overflow-hidden bg-[#0F172A] flex-col justify-between p-12 select-none"
        >
          <!-- Background Slides with Ken Burns and Crossfade -->
          <template x-for="(slide, index) in slides" :key="index">
            <div 
              class="absolute inset-0 bg-cover bg-center transition-opacity duration-1000 ease-in-out animate-kenburns"
              :style="`background-image: url('${slide}');`"
              :class="currentSlide === index ? 'opacity-100' : 'opacity-0'"
            ></div>
          </template>

          <!-- Gradient Overlay: Rich black from bottom, dark scrim from top -->
          <div class="absolute inset-0 bg-gradient-to-t from-black/90 via-black/40 to-black/30 pointer-events-none"></div>

          <!-- Top: Andalan Group Logo -->
          <div class="relative z-10 flex items-center gap-3">
            <img src="{{ asset('assets/images/andalanlogo-removebg-preview.png') }}" alt="Andalan Group" class="h-10 w-auto object-contain drop-shadow" />
            <div class="border-l border-white/20 pl-3">
              <span class="block text-white font-bold text-sm tracking-wider uppercase">Andalan Group</span>
              <span class="block text-white/60 text-[10px] tracking-widest uppercase">Mining & Heavy Equipment</span>
            </div>
          </div>

          <!-- Bottom: Headline & Subheadline -->
          <div class="relative z-10 max-w-xl text-white space-y-4">
            <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-white/10 backdrop-blur-md border border-white/15 text-[11px] font-semibold tracking-wider uppercase text-white/90">
              <span class="w-1.5 h-1.5 rounded-full bg-[#1455D9] ring-2 ring-white/50"></span>
              <span>MANDALA · ANDALAN GROUP</span>
            </div>

            <h2 class="text-3xl sm:text-4xl font-extrabold tracking-tight leading-tight drop-shadow-sm">
              Connecting People, Operations &amp; Performance.
            </h2>

            <p class="text-sm sm:text-base text-white/80 leading-relaxed font-normal max-w-lg">
              One integrated platform to manage your daily operations, safety controls, contracts, and internal collaboration.
            </p>

            <!-- Slideshow Dot Indicators -->
            <div class="flex items-center gap-2 pt-2">
              <template x-for="(slide, index) in slides" :key="'dot-' + index">
                <button 
                  type="button" 
                  @click="currentSlide = index"
                  class="h-1.5 rounded-full transition-all duration-300"
                  :class="currentSlide === index ? 'w-8 bg-[#1455D9]' : 'w-2 bg-white/40 hover:bg-white/70'"
                  :aria-label="'Slide ' + (index + 1)"
                ></button>
              </template>
            </div>
          </div>
        </div>

        <!-- ======================================================== -->
        <!-- KOLOM KANAN (45%): Login Form Container                 -->
        <!-- ======================================================== -->
        <div class="flex-1 lg:w-[45%] flex flex-col justify-between p-6 sm:p-10 lg:p-16 bg-white overflow-y-auto chat-scroll">
          
          <!-- Top Desktop Branding Bar -->
          <div class="hidden lg:flex items-center justify-between">
            <img src="{{ asset('assets/images/logomandala.png') }}" alt="MANDALA" class="h-9 w-auto object-contain" />
            <span class="text-[11px] font-semibold text-[#64748B] uppercase tracking-wider">Enterprise Portal</span>
          </div>

          <!-- Centered Form Wrapper (Max 420px) -->
          <div class="w-full max-w-[420px] mx-auto my-auto py-8">
            {{ $slot }}
          </div>

          <!-- Bottom placeholder for balanced vertical spacing -->
          <div class="hidden lg:block"></div>
        </div>

    </div>

    <x-ui.toast-provider />
</body>
</html>
