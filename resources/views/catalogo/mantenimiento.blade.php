<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $empresaNombre ?? 'FarmaBien' }} - Catálogo en Mantenimiento</title>
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700,800&display=swap" rel="stylesheet" />
    @vite(['resources/css/app.css'])
</head>
<body class="font-sans antialiased bg-slate-50 text-slate-800 min-h-screen flex items-center justify-center p-4">
    <div class="max-w-md w-full bg-white rounded-3xl border border-slate-200 shadow-xl p-8 text-center space-y-6">
        @if($empresaLogo)
            <img src="{{ asset('storage/' . $empresaLogo) }}" alt="Logo" class="h-16 mx-auto object-contain">
        @else
            <div class="w-16 h-16 mx-auto bg-emerald-600 rounded-2xl flex items-center justify-center text-white font-black text-3xl shadow-md">
                +
            </div>
        @endif

        <div class="space-y-2">
            <span class="inline-block px-3 py-1 rounded-full text-xs font-extrabold bg-amber-100 text-amber-800 uppercase tracking-wide">
                ⚙️ Catálogo en Actualización
            </span>
            <h1 class="text-xl font-bold text-slate-900">Catálogo Temporalmente No Disponible</h1>
            <p class="text-xs text-slate-500">
                Estamos realizando labores de mantenimiento y actualización de inventario en línea. Mientras tanto, puedes comunicarte directamente con nuestro equipo de farmacéuticos.
            </p>
        </div>

        <div class="bg-slate-50 rounded-2xl p-4 border border-slate-100 text-left space-y-2.5 text-xs">
            <div class="flex items-center space-x-2 text-slate-700">
                <span>📍</span>
                <span><strong>Ubicación:</strong> {{ $empresaDireccion ?? 'Caracas, Venezuela' }}</span>
            </div>
            <div class="flex items-center space-x-2 text-slate-700">
                <span>📞</span>
                <span><strong>Teléfono:</strong> {{ $empresaTelefono ?? '(0212) 555-0199' }}</span>
            </div>
        </div>

        @if(!empty($empresaWhatsapp))
        <a href="https://wa.me/{{ preg_replace('/[^0-9]/', '', $empresaWhatsapp) }}" target="_blank"
           class="w-full inline-flex items-center justify-center space-x-2 py-3 px-4 bg-emerald-600 hover:bg-emerald-700 text-white rounded-xl text-xs font-bold transition shadow-md">
            <span>💬</span>
            <span>Contactar por WhatsApp</span>
        </a>
        @endif

        <div class="pt-2 border-t border-slate-100">
            <a href="{{ route('login') }}" class="text-[11px] font-bold text-slate-400 hover:text-slate-600 transition">
                Acceso al Sistema FarmaBien &rarr;
            </a>
        </div>
    </div>
</body>
</html>
