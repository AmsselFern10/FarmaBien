<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>403 - Acceso No Autorizado | {{ config('app.name', 'FarmaBien') }}</title>
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700,800&display=swap" rel="stylesheet" />
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="font-sans antialiased bg-slate-950 text-slate-100 min-h-screen flex items-center justify-center p-4">
    <div class="max-w-md w-full text-center bg-slate-900/90 border border-slate-800 rounded-3xl p-8 shadow-2xl space-y-6">
        <div class="w-16 h-16 mx-auto bg-amber-500/10 border border-amber-500/20 rounded-2xl flex items-center justify-center text-amber-500 text-3xl font-black shadow-inner">
            🛡️
        </div>
        <div class="space-y-2">
            <h1 class="text-3xl font-black text-white tracking-tight">403</h1>
            <h2 class="text-base font-bold text-amber-400">Acceso No Autorizado / Restringido</h2>
            <p class="text-xs text-slate-400 leading-relaxed">
                {{ $exception->getMessage() ?: 'No posees los permisos necesarios asignados a tu rol para acceder a este recurso del sistema farmacéutico.' }}
            </p>
        </div>
        <div class="pt-2 flex flex-col sm:flex-row gap-2.5 justify-center">
            <a href="{{ route('dashboard') }}" class="px-5 py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white rounded-xl text-xs font-bold shadow-lg shadow-emerald-950 transition">
                Ir al Dashboard
            </a>
            <button onclick="history.back()" class="px-5 py-2.5 bg-slate-800 hover:bg-slate-700 text-slate-300 rounded-xl text-xs font-bold border border-slate-700 transition">
                Regresar
            </button>
        </div>
        <div class="border-t border-slate-800/80 pt-4 text-[10px] text-slate-400">
            Control de Acceso Basado en Roles (RBAC) &bull; {{ config('app.name', 'FarmaBien') }}
        </div>
    </div>
</body>
</html>
