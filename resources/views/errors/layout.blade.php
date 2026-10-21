<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1">
    <title>@yield('code') · Mandala</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="ui-auth"><main class="min-h-screen flex items-center justify-center p-6">@yield('content')</main></body>
</html>
