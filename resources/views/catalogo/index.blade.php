<!DOCTYPE html>
<html lang="es" class="h-full">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=5">
    <title>{{ configuracion('empresa_nombre', 'FarmaBien') }} - Catálogo de Medicamentos</title>
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700,800&display=swap" rel="stylesheet" />
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        /* Desactivar cualquier borde interno predeterminado en el input de búsqueda */
        .search-input-clean {
            border: none !important;
            outline: none !important;
            box-shadow: none !important;
            -webkit-appearance: none !important;
            -moz-appearance: none !important;
            appearance: none !important;
        }
        .search-input-clean:focus {
            border: none !important;
            outline: none !important;
            box-shadow: none !important;
            ring: 0 !important;
        }
    </style>
</head>
<body class="font-sans antialiased bg-slate-100 text-slate-800 min-h-screen flex flex-col selection:bg-emerald-500 selection:text-white">

    @php
        $rawWhatsapp = configuracion('empresa_whatsapp') ?: configuracion('empresa_telefono', '');
        $cleanWhatsapp = preg_replace('/[^0-9]/', '', $rawWhatsapp);
        $empresaNombre = configuracion('empresa_nombre', 'FarmaBien');
        $empresaRazonSocial = configuracion('empresa_razon_social', 'Farmacia & Droguería FarmaBien');
        $empresaRuc = configuracion('empresa_ruc');
        $empresaTelefono = configuracion('empresa_telefono');
        $empresaDireccion = configuracion('empresa_direccion');
        $empresaEmail = configuracion('empresa_email');
        $empresaSlogan = configuracion('empresa_slogan', 'Tu salud y bienestar en las mejores manos');
        $empresaLogo = configuracion('empresa_logo');
    @endphp

    {{-- 1. Barra Superior Informativa (Fondo Oscuro Sólido con Texto Blanco de Máxima Legibilidad) --}}
    <div style="background-color: #0f172a; color: #ffffff;" class="text-xs py-2 px-4 border-b border-slate-800">
        <div class="max-w-7xl mx-auto flex flex-col sm:flex-row items-center justify-between gap-2 text-center sm:text-left">
            <div class="flex flex-wrap items-center justify-center sm:justify-start gap-x-5 gap-y-1 text-slate-200 text-[11px] sm:text-xs">
                @if($empresaDireccion)
                <span class="inline-flex items-center gap-1.5 text-slate-200">
                    <svg class="w-3.5 h-3.5 text-emerald-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                    <span class="font-medium text-white">{{ $empresaDireccion }}</span>
                </span>
                @endif
                @if($empresaTelefono)
                <span class="inline-flex items-center gap-1.5 text-slate-200">
                    <svg class="w-3.5 h-3.5 text-emerald-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/></svg>
                    <span class="font-medium text-white">Tel: {{ $empresaTelefono }}</span>
                </span>
                @endif
            </div>
            <div class="text-[11px] font-semibold text-emerald-300 flex items-center justify-center gap-1.5">
                <span class="w-2 h-2 rounded-full bg-emerald-400 animate-pulse"></span>
                <span>Inventario sincronizado en vivo</span>
            </div>
        </div>
    </div>

    {{-- 2. Header Institucional Limpio --}}
    <header class="bg-white border-b border-slate-200 sticky top-0 z-30 shadow-xs">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-3 flex items-center justify-between gap-3">
            {{-- Logo y Razón Social --}}
            <div class="flex items-center space-x-3 min-w-0">
                @if($empresaLogo)
                    <img src="{{ asset('storage/' . $empresaLogo) }}" alt="{{ $empresaNombre }}" class="h-9 sm:h-11 w-auto object-contain shrink-0">
                @else
                    <div class="w-9 h-9 sm:w-11 sm:h-11 bg-emerald-600 rounded-xl flex items-center justify-center text-white font-black text-xl sm:text-2xl shadow-xs shrink-0">
                        +
                    </div>
                @endif
                <div class="min-w-0">
                    <div class="flex items-center gap-2 flex-wrap">
                        <span class="font-extrabold text-base sm:text-xl text-slate-900 tracking-tight truncate">
                            {{ $empresaNombre }}
                        </span>
                        @if($empresaRuc)
                            <span class="hidden sm:inline-block px-2 py-0.5 rounded-md bg-slate-100 text-slate-600 font-mono text-[10px] font-bold border border-slate-200">
                                RUC: {{ $empresaRuc }}
                            </span>
                        @endif
                    </div>
                    <p class="text-[11px] sm:text-xs text-slate-500 font-medium truncate">
                        {{ $empresaRazonSocial }}
                    </p>
                </div>
            </div>

            {{-- Botón Contacto WhatsApp --}}
            @if(!empty($cleanWhatsapp))
            <a href="https://wa.me/{{ $cleanWhatsapp }}?text={{ urlencode('Hola, deseo consultar sobre un medicamento o cotización.') }}" 
               target="_blank"
               class="inline-flex items-center space-x-1.5 sm:space-x-2 px-3 sm:px-4 py-2 bg-emerald-600 hover:bg-emerald-700 active:bg-emerald-800 text-white rounded-xl text-xs font-bold transition shadow-xs shrink-0">
                <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/></svg>
                <span class="hidden md:inline">Atención por WhatsApp</span>
                <span class="md:hidden">WhatsApp</span>
            </a>
            @endif
        </div>
    </header>

    {{-- 3. Banner Principal (Verde Esmeralda Médico Sólido con Buscador Flotante Sin Bordes Rotos) --}}
    <section style="background-color: #064e3b;" class="text-white py-8 sm:py-10 px-4 sm:px-6 lg:px-8 border-b border-emerald-950">
        <div class="max-w-4xl mx-auto text-center space-y-4 sm:space-y-5">
            
            {{-- Badge Superior --}}
            <div>
                <span style="background-color: rgba(2, 44, 34, 0.85); border: 1px solid rgba(52, 211, 153, 0.4); color: #a7f3d0;" 
                      class="inline-flex items-center gap-1.5 px-3.5 py-1 rounded-full text-[11px] font-bold uppercase tracking-wider shadow-2xs">
                    <span class="w-2 h-2 rounded-full bg-emerald-400 animate-pulse"></span>
                    <span>Catálogo de Consulta para Clientes</span>
                </span>
            </div>

            {{-- Título y Subtítulo con Contraste Puro --}}
            <div class="space-y-1.5">
                <h1 class="text-2xl sm:text-3xl md:text-4xl font-black text-white tracking-tight leading-tight">
                    Consulta la disponibilidad de tus medicamentos
                </h1>
                
                <p style="color: #ecfdf5;" class="text-xs sm:text-sm md:text-base max-w-2xl mx-auto font-normal">
                    {{ $empresaSlogan }}
                </p>
            </div>

            {{-- Buscador Unificado (Barra Flotante Blanca sin Bordes Internos) --}}
            <div class="max-w-2xl mx-auto w-full pt-1">
                <form method="GET" action="{{ route('catalogo.publico') }}" class="w-full">
                    <div style="background-color: #ffffff; box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.25);" 
                         class="flex items-center rounded-2xl p-1.5 sm:p-2 border border-emerald-500/30 transition-all">
                        
                        {{-- Icono de Búsqueda --}}
                        <div class="pl-3 pr-1 text-emerald-600 shrink-0 flex items-center justify-center">
                            <svg class="w-5 h-5 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                            </svg>
                        </div>
                        
                        {{-- Campo de Entrada Limpio --}}
                        <input type="text" 
                               name="buscar" 
                               value="{{ $buscar }}" 
                               placeholder="¿Qué medicamento buscas? (ej: Paracetamol, Ibuprofeno)..." 
                               autocomplete="off"
                               class="search-input-clean w-full py-2.5 sm:py-3 px-2 text-xs sm:text-sm md:text-base text-slate-900 placeholder-slate-400 bg-transparent">
                        
                        {{-- Botón Limpiar (si hay término buscado) --}}
                        @if(!empty($buscar))
                            <a href="{{ route('catalogo.publico') }}" 
                               class="px-2.5 py-1 text-slate-400 hover:text-rose-600 text-xs font-bold transition shrink-0 mr-1" 
                               title="Limpiar búsqueda">
                                ✕ Limpiar
                            </a>
                        @endif

                        {{-- Botón Buscar --}}
                        <button type="submit" 
                                class="bg-emerald-600 hover:bg-emerald-700 active:bg-emerald-800 text-white font-bold px-5 sm:px-7 py-2.5 sm:py-3 rounded-xl text-xs sm:text-sm transition flex items-center justify-center gap-1.5 shrink-0 shadow-xs">
                            <span>Buscar</span>
                        </button>
                    </div>
                </form>

                {{-- Feedback de Búsqueda Activa --}}
                @if(!empty($buscar))
                <div style="color: #ecfdf5;" class="mt-2.5 flex items-center justify-center gap-2 text-xs">
                    <span>Resultados para: <strong class="text-white font-bold">"{{ $buscar }}"</strong></span>
                    <a href="{{ route('catalogo.publico') }}" 
                       style="color: #ffffff;"
                       class="font-bold underline hover:text-emerald-200 ml-1">
                        Ver todo el catálogo
                    </a>
                </div>
                @endif
            </div>

            {{-- Call To Action Secundario de WhatsApp --}}
            @if(!empty($cleanWhatsapp))
            <div class="pt-1 flex justify-center">
                <a href="https://wa.me/{{ $cleanWhatsapp }}?text={{ urlencode('Hola, tengo una receta médica y deseo consultar disponibilidad y precios.') }}" 
                   target="_blank"
                   style="background-color: rgba(2, 44, 34, 0.85); border: 1px solid rgba(52, 211, 153, 0.4); color: #ffffff;"
                   class="inline-flex items-center gap-2 px-4 py-2 rounded-xl hover:bg-emerald-950 text-xs sm:text-sm font-semibold transition shadow-xs group">
                    <svg class="w-4 h-4 text-emerald-400 shrink-0 group-hover:scale-110 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                    <span>¿Tienes una receta médica? <strong class="underline decoration-emerald-400 text-white">Envíanos una foto por WhatsApp</strong></span>
                    <span class="text-emerald-400 font-bold group-hover:translate-x-0.5 transition-transform">&rarr;</span>
                </a>
            </div>
            @endif
        </div>
    </section>

    {{-- 4. Barra de Beneficios y Confianza (Tarjetas Limpias y Ordenadas) --}}
    <section class="bg-white border-b border-slate-200 py-3.5 px-4 sm:px-6">
        <div class="max-w-7xl mx-auto grid grid-cols-1 sm:grid-cols-3 gap-3 sm:gap-4 text-left">
            <div class="flex items-center gap-3 p-2.5 rounded-xl bg-slate-50 border border-slate-200/80 shadow-2xs">
                <div class="w-9 h-9 rounded-lg bg-emerald-50 text-emerald-700 flex items-center justify-center shrink-0 border border-emerald-100">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                </div>
                <div>
                    <h4 class="text-xs font-bold text-slate-900">Medicamentos Garantizados</h4>
                    <p class="text-[11px] text-slate-500">Productos originales de laboratorios certificados</p>
                </div>
            </div>

            <div class="flex items-center gap-3 p-2.5 rounded-xl bg-slate-50 border border-slate-200/80 shadow-2xs">
                <div class="w-9 h-9 rounded-lg bg-emerald-50 text-emerald-700 flex items-center justify-center shrink-0 border border-emerald-100">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                </div>
                <div>
                    <h4 class="text-xs font-bold text-slate-900">Stock en Tiempo Real</h4>
                    <p class="text-[11px] text-slate-500">Consulta inventario real antes de visitarnos</p>
                </div>
            </div>

            <div class="flex items-center gap-3 p-2.5 rounded-xl bg-slate-50 border border-slate-200/80 shadow-2xs">
                <div class="w-9 h-9 rounded-lg bg-emerald-50 text-emerald-700 flex items-center justify-center shrink-0 border border-emerald-100">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/></svg>
                </div>
                <div>
                    <h4 class="text-xs font-bold text-slate-900">Atención Personalizada</h4>
                    <p class="text-[11px] text-slate-500">Asesoría directa vía WhatsApp</p>
                </div>
            </div>
        </div>
    </section>

    {{-- 5. Catálogo de Productos (Cards Responsivas para Móvil, Tablet y Desktop) --}}
    <main class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6 sm:py-8 flex-1 w-full space-y-5">

        {{-- Conteo de Resultados --}}
        <div class="flex items-center justify-between text-xs text-slate-500 border-b border-slate-200 pb-3">
            <span>Resultados: <strong>{{ $productos->total() }}</strong> medicamentos encontrados</span>
            <span class="text-slate-400">Página {{ $productos->currentPage() }} de {{ $productos->lastPage() }}</span>
        </div>

        {{-- Grid de Tarjetas --}}
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-4 sm:gap-5">
            @forelse($productos as $producto)
                @php
                    $stockDisponible = $producto->lotes->sum('stock_actual');
                    $tieneStock = $stockDisponible > 0;
                    
                    // Texto descriptivo para la consulta por WhatsApp
                    $textoDetalle = trim("{$producto->nombre}" . ($producto->principio_activo ? " ({$producto->principio_activo}" . ($producto->concentracion ? " {$producto->concentracion}" : "") . ")" : ""));
                    if ($producto->forma_farmaceutica) {
                        $textoDetalle .= " - " . $producto->forma_farmaceutica;
                    }
                    $msgWs = urlencode("Hola {$empresaNombre}, estoy consultando su catálogo web. ¿Tienen disponible el producto: *{$textoDetalle}*?");
                @endphp

                <div class="bg-white rounded-2xl border border-slate-200 shadow-2xs hover:shadow-md transition-all duration-200 flex flex-col justify-between overflow-hidden group">
                    
                    {{-- Imagen / Placeholder --}}
                    <div class="w-full h-40 sm:h-44 bg-slate-50 border-b border-slate-100 flex items-center justify-center p-3 relative overflow-hidden">
                        @if(!empty($producto->imagen) && \Illuminate\Support\Facades\Storage::disk('public')->exists($producto->imagen))
                            <img src="{{ asset('storage/' . $producto->imagen) }}" 
                                 alt="{{ $producto->nombre }}" 
                                 loading="lazy"
                                 decoding="async"
                                 class="w-full h-full object-contain group-hover:scale-105 transition-transform duration-200">
                        @else
                            <div class="flex flex-col items-center justify-center text-slate-300 group-hover:text-emerald-500 transition-colors">
                                <svg class="w-12 h-12 sm:w-14 sm:h-14" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.2" d="M19.428 15.428a2 2 0 00-1.022-.547l-2.387-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 10.172V5L8 4z"/></svg>
                                <span class="text-[10px] text-slate-400 mt-1 font-medium">{{ $producto->categoria->nombre ?? 'Medicamento' }}</span>
                            </div>
                        @endif

                        {{-- Badges Flotantes --}}
                        <div class="absolute top-2.5 left-2.5 right-2.5 flex items-center justify-between gap-1.5">
                            <div class="flex items-center gap-1 min-w-0">
                                @if($producto->categoria)
                                    <span class="px-2 py-0.5 rounded-lg text-[10px] font-bold bg-white/95 text-slate-700 shadow-2xs border border-slate-200/80 truncate max-w-[110px]">
                                        {{ $producto->categoria->nombre }}
                                    </span>
                                @endif
                                @if($producto->tiene_oferta)
                                    <span class="px-2 py-0.5 rounded-lg text-[10px] font-bold bg-rose-600 text-white shadow-2xs flex items-center gap-0.5">
                                        <span>🔥</span>
                                        <span>{{ $producto->badge_oferta }}</span>
                                    </span>
                                @endif
                            </div>

                            @if($mostrarStock)
                                @if($tieneStock)
                                    <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-bold bg-emerald-500 text-white shadow-2xs">
                                        <span class="w-1.5 h-1.5 rounded-full bg-white animate-pulse"></span>
                                        <span>Disponible</span>
                                    </span>
                                @else
                                    <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-bold bg-amber-500 text-white shadow-2xs">
                                        <span>Consultar</span>
                                    </span>
                                @endif
                            @endif
                        </div>
                    </div>

                    {{-- Datos del Medicamento --}}
                    <div class="p-3.5 sm:p-4 flex-1 flex flex-col justify-between space-y-3">
                        <div class="space-y-2">
                            {{-- Nombre Comercial --}}
                            <h3 class="font-bold text-sm sm:text-base text-slate-900 group-hover:text-emerald-700 transition-colors line-clamp-2 leading-snug">
                                {{ $producto->nombre }}
                            </h3>

                            {{-- Principio Activo --}}
                            @if($producto->principio_activo)
                                <p class="text-xs font-semibold text-emerald-800 line-clamp-1">
                                    {{ $producto->principio_activo }}
                                </p>
                            @endif

                            {{-- Ficha Técnica en Pills --}}
                            <div class="flex flex-wrap gap-1 pt-0.5 text-[11px]">
                                @if($producto->concentracion)
                                    <span class="px-2 py-0.5 rounded-md bg-slate-100 text-slate-700 font-medium border border-slate-200">
                                        {{ $producto->concentracion }}
                                    </span>
                                @endif

                                @if($producto->forma_farmaceutica)
                                    <span class="px-2 py-0.5 rounded-md bg-slate-100 text-slate-700 font-medium border border-slate-200">
                                        {{ $producto->forma_farmaceutica }}
                                    </span>
                                @endif

                                @if($producto->laboratorio)
                                    <span class="px-2 py-0.5 rounded-md bg-slate-100 text-slate-600 border border-slate-200 truncate max-w-full">
                                        Lab: {{ $producto->laboratorio->nombre }}
                                    </span>
                                @endif
                            </div>

                            @if($producto->requiere_receta)
                                <div class="pt-0.5">
                                    <span class="inline-flex items-center gap-1 text-[10px] font-bold text-amber-700 bg-amber-50 px-2 py-0.5 rounded border border-amber-200">
                                        <svg class="w-3 h-3 text-amber-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                                        <span>Requiere receta médica</span>
                                    </span>
                                </div>
                            @endif
                        </div>

                        {{-- Precio y Botón WhatsApp --}}
                        <div class="pt-3 border-t border-slate-100 space-y-2.5">
                            @if($mostrarPrecios)
                                <div class="flex items-baseline justify-between">
                                    <span class="text-[11px] font-semibold text-slate-500 uppercase tracking-wide">Precio:</span>
                                    @if($producto->tiene_oferta)
                                        <div class="flex items-baseline gap-1.5">
                                            <span class="text-xs line-through text-slate-400 font-semibold">${{ number_format($producto->precio_venta, 2) }}</span>
                                            <span class="text-base sm:text-lg font-black text-emerald-700">
                                                ${{ number_format($producto->precio_oferta, 2) }}
                                            </span>
                                            <span class="px-1.5 py-0.2 rounded text-[10px] bg-rose-100 text-rose-700 font-bold">
                                                {{ $producto->badge_oferta }}
                                            </span>
                                        </div>
                                    @else
                                        <span class="text-base sm:text-lg font-black text-emerald-700">
                                            ${{ number_format($producto->precio_venta, 2) }}
                                        </span>
                                    @endif
                                </div>
                            @endif

                            @if(!empty($cleanWhatsapp))
                                <a href="https://wa.me/{{ $cleanWhatsapp }}?text={{ $msgWs }}" 
                                   target="_blank"
                                   class="w-full inline-flex items-center justify-center space-x-2 py-2.5 px-3 bg-emerald-600 hover:bg-emerald-700 active:bg-emerald-800 text-white rounded-xl text-xs font-bold transition shadow-xs">
                                    <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/></svg>
                                    <span>Consultar por WhatsApp</span>
                                </a>
                            @endif
                        </div>
                    </div>
                </div>
            @empty
                <div class="col-span-full text-center py-14 bg-white rounded-2xl border border-slate-200 p-6 sm:p-8 space-y-3">
                    <div class="w-12 h-12 bg-slate-100 rounded-full flex items-center justify-center mx-auto text-slate-400">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                    </div>
                    <h3 class="text-sm sm:text-base font-bold text-slate-900">No encontramos medicamentos con esa búsqueda</h3>
                    <p class="text-xs text-slate-500 max-w-md mx-auto">
                        Verifica el nombre comercial o principio activo ingresado, o consúltanos directamente por WhatsApp para verificar existencias en bodega.
                    </p>
                    @if(!empty($cleanWhatsapp))
                    <div class="pt-2">
                        <a href="https://wa.me/{{ $cleanWhatsapp }}?text={{ urlencode('Hola, busco un medicamento que no encontré en el catálogo web: ' . $buscar) }}" 
                           target="_blank"
                           class="inline-flex items-center space-x-2 px-4 py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white rounded-xl text-xs font-bold transition shadow-xs">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/></svg>
                            <span>Consultar a Farmacéutico por WhatsApp</span>
                        </a>
                    </div>
                    @endif
                </div>
            @endforelse
        </div>

        {{-- Paginación --}}
        <div class="mt-8">
            {{ $productos->links() }}
        </div>
    </main>

    {{-- 6. Footer Informativo --}}
    <footer class="bg-white border-t border-slate-200 py-8 mt-10 text-slate-600 text-xs">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">
            <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-6">
                <div>
                    <h4 class="font-bold text-slate-900 text-sm mb-2">{{ $empresaNombre }}</h4>
                    <p class="text-slate-500 text-xs leading-relaxed">
                        {{ $empresaRazonSocial }}<br>
                        @if($empresaRuc)<strong>RUC/NIT:</strong> {{ $empresaRuc }}<br>@endif
                        {{ $empresaSlogan }}
                    </p>
                </div>
                <div>
                    <h4 class="font-bold text-slate-900 text-sm mb-2">Ubicación y Teléfono</h4>
                    <p class="text-slate-500 text-xs leading-relaxed">
                        @if($empresaDireccion)📍 {{ $empresaDireccion }}<br>@endif
                        @if($empresaTelefono)📞 {{ $empresaTelefono }}<br>@endif
                        @if($empresaEmail)✉️ {{ $empresaEmail }}<br>@endif
                    </p>
                </div>
                <div class="sm:col-span-2 md:col-span-1">
                    <h4 class="font-bold text-slate-900 text-sm mb-2">Canal de Pedidos y Recetas</h4>
                    <p class="text-slate-500 text-xs leading-relaxed mb-3">
                        ¿Dudas con tu tratamiento o deseas cotizar tu fórmula médica?
                    </p>
                    @if(!empty($cleanWhatsapp))
                    <a href="https://wa.me/{{ $cleanWhatsapp }}" 
                       target="_blank"
                       class="inline-flex items-center space-x-1.5 px-3 py-1.5 bg-emerald-50 border border-emerald-300 text-emerald-800 rounded-lg text-xs font-bold hover:bg-emerald-100 transition">
                        <svg class="w-3.5 h-3.5 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/></svg>
                        <span>WhatsApp Directo</span>
                    </a>
                    @endif
                </div>
            </div>

            <div class="pt-4 border-t border-slate-100 text-center text-slate-400 text-[11px]">
                <p>&copy; {{ date('Y') }} {{ $empresaNombre }} — {{ $empresaRazonSocial }}. Todos los derechos reservados.</p>
            </div>
        </div>
    </footer>
</body>
</html>
