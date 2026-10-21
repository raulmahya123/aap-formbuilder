<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">
        <meta name="theme-color" content="#603813">
        <link rel="icon" type="image/png" href="{{ asset('assets/images/andalanlogo-removebg-preview.png') }}">

        <title>{{ config('app.name', 'Laravel') }}</title>

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

        <!-- Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="font-sans text-[#111111] antialiased">
        <div class="min-h-screen flex flex-col sm:justify-center items-center px-4 py-8 bg-white">
            <div class="mb-6">
                <a href="/">
                    <img src="{{ asset('assets/images/logomandala.png') }}" alt="Mandala" class="mx-auto h-12 w-auto">
                </a>
            </div>

            <div class="w-full sm:max-w-md px-6 py-6 bg-white border border-[#C5C5C5] shadow-soft overflow-hidden rounded-lg">
                {{ $slot }}
            </div>
        </div>
    </body>
</html>
