@extends('layouts.app')

@section('title', "Editar Precios — {$producto->nombre} — FarmaBien")

@section('content')
<div x-data="editarPreciosForm()"
     :class="formLayout === 'compact' ? 'w-full' : 'max-w-5xl mx-auto'"
     class="space-y-4 transition-all duration-200">

    {{-- Fila 1 — Breadcrumb --}}
    <nav class="flex items-center space-x-2 text-xs text-slate-500 dark:text-slate-400">
        <a href="{{ route('dashboard') }}" class="hover:text-emerald-600 dark:hover:text-emerald-400 transition">Inicio</a>
        <svg class="w-3 h-3 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
        <a href="{{ route('precios.index') }}" class="hover:text-emerald-600 dark:hover:text-emerald-400 transition">Precios de Venta</a>
        <svg class="w-3 h-3 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
        <span class="text-slate-800 dark:text-slate-200 font-semibold truncate">Editar: {{ $producto->nombre }}</span>
    </nav>

    {{-- Fila 2 — Título y Acciones --}}
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h1 class="text-xl font-bold text-slate-900 dark:text-white">Editar Precios de Venta</h1>
            <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">Producto: <span class="font-bold text-slate-800 dark:text-slate-200">{{ $producto->nombre }}</span> &bull; Actualización de tarifa base y presentaciones.</p>
        </div>

        {{-- Barra de Acciones: ← Precios de Venta, Modo Full, Guardar Cambios (Verde sólido), Toggle Diseño --}}
        <div class="flex items-center gap-2 flex-wrap shrink-0">
            {{-- Botón de Navegación --}}
            <a href="{{ route('precios.index') }}" 
               class="inline-flex items-center space-x-1.5 px-3.5 py-1.5 rounded-xl bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 hover:bg-slate-50 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-200 text-xs font-semibold shadow-2xs transition">
                <svg class="w-4 h-4 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
                <span>Precios de Venta</span>
            </a>

            {{-- Modo Full --}}
            <button type="button" 
                    @click="$dispatch('toggle-pos-fullscreen')"
                    title="Modo Pantalla Completa"
                    class="inline-flex items-center space-x-1.5 px-3.5 py-1.5 rounded-xl bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 hover:bg-slate-50 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-200 text-xs font-semibold shadow-2xs transition cursor-pointer">
                <svg class="w-4 h-4 text-emerald-600 dark:text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 8V4m0 0h4M4 4l5 5m11-5h-4m4 0v4m0-4l-5 5M4 16v4m0 0h4m-4 0l5-5m11 5l-5-5m5 5v-4m0 4h-4"/></svg>
                <span x-text="posFullscreen ? 'Salir Full' : 'Modo Full'">Modo Full</span>
            </button>

            {{-- Principal Verde Sólido: Guardar Cambios --}}
            <button type="button" 
                    @click="enviarFormulario()"
                    :disabled="precioMenorAlCosto"
                    class="inline-flex items-center space-x-1.5 px-3.5 py-1.5 bg-emerald-600 hover:bg-emerald-700 active:bg-emerald-800 disabled:opacity-50 disabled:cursor-not-allowed text-white text-xs font-semibold rounded-xl shadow-xs transition cursor-pointer">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                <span>Guardar Cambios</span>
            </button>

            {{-- Toggle Diseño: Moderna | Compacta --}}
            <div class="inline-flex items-center p-0.5 rounded-xl bg-slate-100 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 text-xs font-semibold shadow-2xs">
                <button type="button" 
                        @click="formLayout = 'modern'" 
                        :class="formLayout === 'modern' ? 'bg-white dark:bg-slate-700 text-emerald-950 dark:text-emerald-400 shadow-xs font-bold' : 'text-slate-600 hover:text-slate-900 dark:text-slate-400 dark:hover:text-slate-200'"
                        class="px-2.5 py-1 rounded-lg transition flex items-center space-x-1.5 cursor-pointer">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"/></svg>
                    <span>Moderna</span>
                </button>
                <button type="button" 
                        @click="formLayout = 'compact'" 
                        :class="formLayout === 'compact' ? 'bg-white dark:bg-slate-700 text-emerald-950 dark:text-emerald-400 shadow-xs font-bold' : 'text-slate-600 hover:text-slate-900 dark:text-slate-400 dark:hover:text-slate-200'"
                        class="px-2.5 py-1 rounded-lg transition flex items-center space-x-1.5 cursor-pointer">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                    <span>Compacta</span>
                </button>
            </div>
        </div>
    </div>

    {{-- Formulario Principal --}}
    <form method="POST" action="{{ route('precios.update', $producto) }}" id="formEditarPrecios" class="space-y-4">
        @csrf
        @method('PUT')

        {{-- Alertas en Vivo --}}
        <template x-if="precioMenorAlCosto">
            <div class="p-3.5 rounded-2xl bg-red-50 dark:bg-red-500/10 border border-red-200 dark:border-red-500/30 flex items-center space-x-3 text-red-900 dark:text-red-400">
                <svg class="w-5 h-5 shrink-0 text-red-600 dark:text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                <div class="text-xs">
                    <span class="font-bold">Alerta Crítica:</span> El precio de venta base ingresado es inferior al costo de adquisición (<span class="font-mono font-bold">C$ {{ number_format($costoReferencia, 2) }}</span>). El guardado se encuentra bloqueado para prevenir pérdidas financieras.
                </div>
            </div>
        </template>

        <template x-if="!precioMenorAlCosto && margenBase < 25 && margenBase >= 0">
            <div class="p-3.5 rounded-2xl bg-amber-50 dark:bg-amber-500/10 border border-amber-200 dark:border-amber-500/30 flex items-center space-x-3 text-amber-900 dark:text-amber-400">
                <svg class="w-5 h-5 shrink-0 text-amber-600 dark:text-amber-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                <div class="text-xs">
                    <span class="font-bold">Margen Comercial Bajo:</span> El margen resultante es del <span class="font-bold" x-text="margenBase + '%'"></span> (inferior al umbral sugerido del 25%).
                </div>
            </div>
        </template>

        {{-- Sección 1 y 2: Medicamento & Costo de Referencia (Solo Lectura) --}}
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-4">
            {{-- Sección 1: Datos del Producto --}}
            <div class="lg:col-span-8 p-4 rounded-2xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 shadow-2xs space-y-3">
                <div class="text-xs font-bold uppercase tracking-wider text-slate-700 dark:text-slate-300 flex items-center space-x-2 border-b border-slate-200/80 dark:border-slate-800 pb-2">
                    <svg class="w-4 h-4 text-emerald-600 dark:text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/></svg>
                    <span>Información del Medicamento (Solo Lectura)</span>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 text-xs">
                    <div>
                        <span class="text-slate-500 block">Nombre Comercial:</span>
                        <span class="font-bold text-slate-900 dark:text-white text-sm">{{ $producto->nombre }}</span>
                    </div>
                    <div>
                        <span class="text-slate-500 block">Principio Activo & Concentración:</span>
                        <span class="font-medium text-slate-800 dark:text-slate-200">{{ $producto->principio_activo ?? 'N/A' }} {{ $producto->concentracion ? '· ' . $producto->concentracion : '' }}</span>
                    </div>
                    <div>
                        <span class="text-slate-500 block">Categoría / Grupo:</span>
                        <span class="font-medium text-slate-800 dark:text-slate-200">{{ $producto->categoria->nombre ?? 'General' }}</span>
                    </div>
                    <div>
                        <span class="text-slate-500 block">Laboratorio Fabricante:</span>
                        <span class="font-medium text-slate-800 dark:text-slate-200">{{ $producto->laboratorio->nombre ?? 'Sin especificar' }}</span>
                    </div>
                </div>
            </div>

            {{-- Sección 2: Costo de Adquisición --}}
            <div class="lg:col-span-4 p-4 rounded-2xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 shadow-2xs space-y-3">
                <div class="text-xs font-bold uppercase tracking-wider text-slate-700 dark:text-slate-300 flex items-center space-x-2 border-b border-slate-200/80 dark:border-slate-800 pb-2">
                    <svg class="w-4 h-4 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    <span>Costo de Referencia</span>
                </div>
                <div class="space-y-1">
                    <div class="text-2xl font-bold font-mono text-slate-900 dark:text-white">
                        C$ {{ number_format($costoReferencia, 2) }}
                    </div>
                    <div class="text-[11px] text-slate-500">Costo unitario base de adquisición registrado en catálogo.</div>
                </div>
            </div>
        </div>

        {{-- Sección 3: Configuración de Precios de Venta --}}
        <div class="p-4 sm:p-5 rounded-2xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 shadow-2xs space-y-5">
            <div class="text-xs font-bold uppercase tracking-wider text-slate-700 dark:text-slate-300 flex items-center justify-between border-b border-slate-200/80 dark:border-slate-800 pb-2">
                <div class="flex items-center space-x-2">
                    <svg class="w-4 h-4 text-emerald-600 dark:text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
                    <span>Definición de Precios de Venta</span>
                </div>
            </div>

            {{-- Fila Principal: Precio Base y Margen en Vivo --}}
            <div class="grid grid-cols-1 sm:grid-cols-12 gap-4">
                {{-- Precio de Venta Base --}}
                <div class="sm:col-span-6">
                    <label for="precio_base" class="block text-xs font-bold text-slate-800 dark:text-slate-200 mb-1.5">
                        Precio de Venta Base (C$, por unidad) <span class="text-rose-500">*</span>
                    </label>
                    <div class="relative">
                        <span class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-slate-400 font-mono text-xs">C$</span>
                        <input type="number" 
                               step="0.01" 
                               min="0.01" 
                               name="precio_base" 
                               id="precio_base" 
                               x-model="precioBase" 
                               @input="recalcularPresentaciones()" 
                               required 
                               class="w-full pl-9 pr-3 py-2.5 bg-white dark:bg-slate-800 border border-slate-300 dark:border-slate-700 rounded-xl text-sm font-mono font-bold text-slate-900 dark:text-white focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500">
                    </div>
                </div>

                {{-- Margen Estimado en Vivo --}}
                <div class="sm:col-span-6 flex flex-col justify-end">
                    <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1.5">Margen Comercial Base Calculado</label>
                    <div class="px-3.5 py-2 rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800 flex items-center justify-between">
                        <span class="text-xs text-slate-500">Margen Bruto:</span>
                        <span class="text-base font-bold font-mono" 
                              :class="margenBase < 0 ? 'text-red-900 dark:text-red-400' : (margenBase < 25 ? 'text-amber-900 dark:text-amber-400' : 'text-emerald-900 dark:text-emerald-400')" 
                              x-text="margenBase + '%'"></span>
                    </div>
                </div>
            </div>

            {{-- Tabla de Precios por Presentación --}}
            @if($producto->presentaciones->isNotEmpty())
            <div class="space-y-2 pt-2 border-t border-slate-200/80 dark:border-slate-800">
                <div class="flex items-center justify-between">
                    <label class="block text-xs font-bold text-slate-800 dark:text-slate-200">
                        Precios por Presentación (Caja, Blíster, Frasco)
                    </label>
                    <button type="button" @click="recalcularPresentaciones(true)" class="text-[11px] font-semibold text-emerald-600 hover:text-emerald-700 dark:text-emerald-400">
                        Sugerir todos por factor (Base &times; Factor)
                    </button>
                </div>

                <div class="border border-slate-200 dark:border-slate-800 rounded-xl overflow-hidden">
                    <table class="w-full text-left text-xs text-slate-700 dark:text-slate-300">
                        <thead class="bg-slate-100/80 dark:bg-slate-800 text-[11px] font-bold text-slate-600 dark:text-slate-400">
                            <tr>
                                <th class="px-3.5 py-2.5">Presentación</th>
                                <th class="px-3 py-2.5 text-center">Factor</th>
                                <th class="px-3 py-2.5 text-right">Precio Sugerido</th>
                                <th class="px-3 py-2.5 text-right">Precio de Venta Final (C$)</th>
                                <th class="px-3 py-2.5 text-center">Margen</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-200 dark:divide-slate-800">
                            <template x-for="(pres, index) in presentaciones" :key="pres.id">
                                <tr>
                                    <td class="px-3.5 py-2 font-medium text-slate-900 dark:text-white">
                                        <input type="hidden" :name="'presentaciones[' + index + '][id]'" :value="pres.id">
                                        <span x-text="pres.nombre"></span>
                                        <template x-if="pres.esManual">
                                            <span class="ml-1 px-1.5 py-0.5 text-[9px] rounded bg-amber-100 dark:bg-amber-900/30 text-amber-900 dark:text-amber-300 font-semibold">Manual</span>
                                        </template>
                                    </td>
                                    <td class="px-3 py-2 text-center font-mono" x-text="pres.factor + ' u.'"></td>
                                    <td class="px-3 py-2 text-right font-mono text-slate-500" x-text="'C$ ' + (precioBase * pres.factor).toFixed(2)"></td>
                                    <td class="px-3 py-2 text-right">
                                        <input type="number" 
                                               step="0.01" 
                                               min="0.01" 
                                               :name="'presentaciones[' + index + '][precio_venta]'" 
                                               x-model="pres.precio" 
                                               @input="pres.esManual = true"
                                               required 
                                               class="w-24 px-2 py-1 bg-white dark:bg-slate-800 border border-slate-300 dark:border-slate-700 rounded-lg text-xs font-mono font-bold text-slate-900 dark:text-white text-right focus:ring-1 focus:ring-emerald-500">
                                    </td>
                                    <td class="px-3 py-2 text-center font-bold font-mono" 
                                        :class="calcularMargenPres(pres) < 0 ? 'text-red-900 dark:text-red-400' : (calcularMargenPres(pres) < 25 ? 'text-amber-900 dark:text-amber-400' : 'text-emerald-900 dark:text-emerald-400')" 
                                        x-text="calcularMargenPres(pres) + '%'"></td>
                                </tr>
                            </template>
                        </tbody>
                    </table>
                </div>
            </div>
            @endif

            {{-- Vigencia y Motivo del Cambio (Obligatorio) --}}
            <div class="grid grid-cols-1 sm:grid-cols-12 gap-4 pt-2 border-t border-slate-200/80 dark:border-slate-800">
                <div class="sm:col-span-4">
                    <label for="vigente_desde" class="block text-xs font-bold text-slate-800 dark:text-slate-200 mb-1">
                        Vigente Desde
                    </label>
                    <input type="datetime-local" 
                           name="vigente_desde" 
                           id="vigente_desde" 
                           value="{{ now()->format('Y-m-d\TH:i') }}"
                           class="w-full px-3 py-2 bg-white dark:bg-slate-800 border border-slate-300 dark:border-slate-700 rounded-xl text-xs text-slate-900 dark:text-white focus:ring-1 focus:ring-emerald-500">
                </div>

                <div class="sm:col-span-8">
                    <label for="motivo" class="block text-xs font-bold text-slate-800 dark:text-slate-200 mb-1">
                        Motivo del Cambio de Precio <span class="text-rose-500">*</span>
                    </label>
                    <input type="text" 
                           name="motivo" 
                           id="motivo" 
                           required 
                           x-model="motivoCambio"
                           placeholder="Ej: Actualización por lista de precios del laboratorio / inflación" 
                           class="w-full px-3 py-2 bg-white dark:bg-slate-800 border border-slate-300 dark:border-slate-700 rounded-xl text-xs text-slate-900 dark:text-white focus:ring-1 focus:ring-emerald-500">
                </div>
            </div>
        </div>

    </form>
</div>

@push('scripts')
<script>
function editarPreciosForm() {
    return {
        formLayout: 'modern',
        costoUnitario: {{ $costoReferencia }},
        precioBase: {{ (float)($producto->precio_venta ?? 0) }},
        motivoCambio: '',
        presentaciones: [
            @foreach($producto->presentaciones as $p)
            {
                id: {{ $p->id }},
                nombre: @js($p->nombre),
                factor: {{ max(1, (int)$p->unidades_por_presentacion) }},
                precio: {{ (float)($p->precio_venta ?? 0) }},
                esManual: false
            },
            @endforeach
        ],

        get margenBase() {
            const p = parseFloat(this.precioBase) || 0;
            const c = parseFloat(this.costoUnitario) || 0;
            if (p <= 0) return 0;
            return (((p - c) / p) * 100).toFixed(1);
        },

        get precioMenorAlCosto() {
            const p = parseFloat(this.precioBase) || 0;
            const c = parseFloat(this.costoUnitario) || 0;
            return c > 0 && p < c;
        },

        calcularMargenPres(pres) {
            const p = parseFloat(pres.precio) || 0;
            const c = (parseFloat(this.costoUnitario) || 0) * pres.factor;
            if (p <= 0) return 0;
            return (((p - c) / p) * 100).toFixed(1);
        },

        recalcularPresentaciones(forzar = false) {
            const pBase = parseFloat(this.precioBase) || 0;
            this.presentaciones.forEach(pres => {
                if (forzar || !pres.esManual) {
                    pres.precio = (pBase * pres.factor).toFixed(2);
                }
            });
        },

        enviarFormulario() {
            if (this.precioMenorAlCosto) {
                alert('No se puede guardar un precio de venta inferior al costo.');
                return;
            }
            if (this.motivoCambio.trim().length < 3) {
                alert('El motivo del cambio es obligatorio para fines de auditoría.');
                document.getElementById('motivo').focus();
                return;
            }
            document.getElementById('formEditarPrecios').submit();
        }
    };
}
</script>
@endpush
@endsection
