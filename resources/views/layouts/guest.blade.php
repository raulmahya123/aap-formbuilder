<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">
        <meta name="theme-color" content="#713F12">
        <link rel="icon" type="image/png" href="{{ asset('assets/images/andalanlogo-removebg-preview.png') }}">

        <title>{{ config('app.name', 'Laravel') }}</title>

        <link rel="preload" href="{{ Vite::asset('resources/fonts/InterVariable.woff2') }}" as="font" type="font/woff2" crossorigin>

        <!-- Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="ui-auth font-sans text-[#18181B] antialiased">
        <div class="min-h-screen flex flex-col sm:justify-center items-center px-4 py-8">
            <div class="mb-6">
                <a href="/">
                    <img src="{{ asset('assets/images/logomandala.png') }}" alt="Mandala" class="mx-auto h-12 w-auto">
                </a>
            </div>

            <div class="ui-auth-card w-full sm:max-w-md px-6 py-6 bg-white border border-[#D4D4D8] shadow-none overflow-hidden rounded-lg">
                {{ $slot }}
            </div>
        </div>
      <x-ui.toast-provider />
</body>
</html>
