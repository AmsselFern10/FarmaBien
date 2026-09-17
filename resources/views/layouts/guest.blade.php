<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'FarmaBien') }} - Acceso al Sistema</title>

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700&display=swap" rel="stylesheet" />

        <!-- Scripts & Styles -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="font-sans antialiased bg-slate-900 text-slate-100 min-h-screen flex items-center justify-center p-4">
        <div class="w-full max-w-5xl bg-slate-800/90 backdrop-blur border border-slate-700/60 shadow-2xl rounded-2xl overflow-hidden grid grid-cols-1 lg:grid-cols-12 min-h-[620px]">
            {{ $slot }}
        </div>
    </body>
</html>
