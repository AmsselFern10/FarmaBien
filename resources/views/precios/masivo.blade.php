@extends('layouts.app')

@section('title', 'Actualización Masiva de Precios — FarmaBien')

@section('content')
<div x-data="actualizacionMasivaPrecios()" class="space-y-4">

    {{-- Fila 1 — Breadcrumb --}}
    <nav class="flex items-center space-x-2 text-xs text-slate-500 dark:text-slate-400">
        <a href="{{ route('dashboard') }}" class="hover:text-emerald-600 dark:hover:text-emerald-400 transition">Inicio</a>
        <svg class="w-3 h-3 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
        <a href="{{ route('precios.index') }}" class="hover:text-emerald-600 dark:hover:text-emerald-400 transition">Precios de Venta</a>
        <svg class="w-3 h-3 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
        <span class="text-slate-800 dark:text-slate-200 font-semibold">Actualización Masiva</span>
    </nav>

    {{-- Fila 2 — Título y Acciones --}}
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 pb-1">
        <div>
            <h1 class="text-xl sm:text-2xl font-bold text-slate-900 dark:text-white">Actualización Masiva de Precios</h1>
            <p class="text-xs text-slate-700 dark:text-slate-400 mt-0.5">Aplica variaciones porcentuales o fijas a grupos de medicamentos con vista previa instantánea y registro auditable.</p>
        </div>

        {{-- Barra de Acciones: ← Precios de Venta, Modo Full, Principal Aplicar Cambios --}}
        <div class="flex items-center gap-2 flex-wrap shrink-0">
            {{-- Botón de Navegación --}}
            <a href="{{ route('precios.index') }}"
               class="inline-flex items-center space-x-1.5 px-3.5 py-1.5 rounded-xl bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 hover:bg-slate-50 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-200 text-xs font-semibold shadow-xs transition">
                <svg class="w-4 h-4 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
                <span>Precios de Venta</span>
            </a>

            {{-- Modo Full --}}
            <button type="button" 
                    @click="$dispatch('toggle-pos-fullscreen')"
                    title="Modo Pantalla Completa"
                    class="inline-flex items-center space-x-1.5 px-3.5 py-1.5 rounded-xl bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 hover:bg-slate-50 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-200 text-xs font-semibold shadow-xs transition cursor-pointer">
                <svg class="w-4 h-4 text-emerald-900 dark:text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 8V4m0 0h4M4 4l5 5m11-5h-4m4 0v4m0-4l-5 5M4 16v4m0 0h4m-4 0l5-5m11 5l-5-5m5 5v-4m0 4h-4"/></svg>
                <span>Modo Full</span>
            </button>

            {{-- Principal Verde Sólido: Aplicar Cambios --}}
            <button type="button" 
                    @click="abrirModalConfirmacion()"
                    :disabled="totalAfectados === 0 || cargandoPreview"
                    class="inline-flex items-center space-x-1.5 px-3.5 py-1.5 bg-emerald-600 hover:bg-emerald-700 active:bg-emerald-800 disabled:opacity-50 disabled:cursor-not-allowed text-white text-xs font-semibold rounded-xl shadow-xs transition cursor-pointer">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                <span>Aplicar Cambios</span>
            </button>
        </div>
    </div>

    {{-- Formulario de 3 Bloques en una Sola Página --}}
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-4">

        {{-- Bloque 1: Alcance --}}
        <div class="lg:col-span-6 p-4 rounded-xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 shadow-xs space-y-3">
            <div class="text-xs font-bold uppercase tracking-wider text-slate-700 dark:text-slate-300 flex items-center space-x-2 border-b border-slate-200/80 dark:border-slate-800 pb-2">
                <span class="w-5 h-5 rounded-full bg-emerald-100 dark:bg-emerald-900/40 text-emerald-950 dark:text-emerald-300 flex items-center justify-center text-[11px] font-bold">1</span>
                <span>Alcance de la Actualización</span>
            </div>

            <div>
                <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">Criterio de Selección</label>
                <div class="grid grid-cols-2 sm:grid-cols-3 gap-1.5 text-xs">
                    <label class="p-2 rounded-xl border cursor-pointer transition flex items-center space-x-2" :class="tipoAlcance === 'todo' ? 'border-emerald-500 bg-emerald-50/50 dark:bg-emerald-500/10 text-emerald-950 dark:text-emerald-300 font-bold' : 'border-slate-200 dark:border-slate-700 text-slate-700 dark:text-slate-300'">
                        <input type="radio" name="alcance_radio" value="todo" x-model="tipoAlcance" @change="onAlcanceChange()" class="hidden">
                        <span>Todo el Catálogo</span>
                    </label>
                    <label class="p-2 rounded-xl border cursor-pointer transition flex items-center space-x-2" :class="tipoAlcance === 'categoria' ? 'border-emerald-500 bg-emerald-50/50 dark:bg-emerald-500/10 text-emerald-950 dark:text-emerald-300 font-bold' : 'border-slate-200 dark:border-slate-700 text-slate-700 dark:text-slate-300'">
                        <input type="radio" name="alcance_radio" value="categoria" x-model="tipoAlcance" @change="onAlcanceChange()" class="hidden">
                        <span>Por Categoría</span>
                    </label>
                    <label class="p-2 rounded-xl border cursor-pointer transition flex items-center space-x-2" :class="tipoAlcance === 'laboratorio' ? 'border-emerald-500 bg-emerald-50/50 dark:bg-emerald-500/10 text-emerald-950 dark:text-emerald-300 font-bold' : 'border-slate-200 dark:border-slate-700 text-slate-700 dark:text-slate-300'">
                        <input type="radio" name="alcance_radio" value="laboratorio" x-model="tipoAlcance" @change="onAlcanceChange()" class="hidden">
                        <span>Por Laboratorio</span>
                    </label>
                    <label class="p-2 rounded-xl border cursor-pointer transition flex items-center space-x-2" :class="tipoAlcance === 'proveedor' ? 'border-emerald-500 bg-emerald-50/50 dark:bg-emerald-500/10 text-emerald-950 dark:text-emerald-300 font-bold' : 'border-slate-200 dark:border-slate-700 text-slate-700 dark:text-slate-300'">
                        <input type="radio" name="alcance_radio" value="proveedor" x-model="tipoAlcance" @change="onAlcanceChange()" class="hidden">
                        <span>Por Proveedor</span>
                    </label>
                    <label class="p-2 rounded-xl border cursor-pointer transition flex items-center space-x-2" :class="tipoAlcance === 'productos' ? 'border-emerald-500 bg-emerald-50/50 dark:bg-emerald-500/10 text-emerald-950 dark:text-emerald-300 font-bold' : 'border-slate-200 dark:border-slate-700 text-slate-700 dark:text-slate-300'">
                        <input type="radio" name="alcance_radio" value="productos" x-model="tipoAlcance" @change="onAlcanceChange()" class="hidden">
                        <span>Productos Específicos</span>
                    </label>
                </div>
            </div>

            {{-- Buscador AJAX y Chips cuando no es 'todo' --}}
            <template x-if="tipoAlcance !== 'todo'">
                <div class="space-y-2 pt-2 border-t border-slate-200/80 dark:border-slate-800 relative" @click.outside="busquedaOpen = false">
                    <label class="block text-xs font-bold text-slate-700 dark:text-slate-300" x-text="'Buscar ' + labelAlcance()"></label>
                    <div class="relative">
                        <span class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-slate-400">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                        </span>
                        <input type="text" 
                               x-model="queryBusqueda" 
                               @input.debounce.300ms="ejecutarBusquedaAjax()" 
                               @focus="busquedaOpen = true"
                               :placeholder="'Escribe al menos 2 letras para buscar ' + labelAlcance().toLowerCase() + '...'" 
                               class="w-full pl-9 pr-3 py-2 bg-slate-50 dark:bg-slate-800 border border-slate-300 dark:border-slate-700 rounded-xl text-xs text-slate-900 dark:text-white focus:ring-1 focus:ring-emerald-500">
                    </div>

                    {{-- Menú de Resultados AJAX --}}
                    <div x-show="busquedaOpen && resultadosBusqueda.length > 0" x-cloak class="absolute z-50 w-full bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl shadow-lg max-h-48 overflow-y-auto">
                        <template x-for="item in resultadosBusqueda" :key="item.id">
                            <button type="button" @click="agregarChip(item)" class="w-full text-left px-3 py-2 text-xs hover:bg-emerald-50 dark:hover:bg-slate-700 flex items-center justify-between text-slate-800 dark:text-slate-200">
                                <span x-text="item.text"></span>
                                <span class="text-[10px] text-emerald-900 font-bold">+ Seleccionar</span>
                            </button>
                        </template>
                    </div>

                    {{-- Chips Removibles --}}
                    <div class="flex flex-wrap gap-1.5 pt-1">
                        <template x-for="(chip, idx) in chipsSeleccionados" :key="chip.id">
                            <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-semibold bg-emerald-50 dark:bg-emerald-500/10 text-emerald-950 dark:text-emerald-300 border border-emerald-200 dark:border-emerald-500/30">
                                <span x-text="chip.text"></span>
                                <button type="button" @click="removerChip(idx)" class="ml-1.5 text-emerald-900 hover:text-emerald-950 dark:text-emerald-400">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                                </button>
                            </span>
                        </template>
                    </div>
                </div>
            </template>
        </div>

        {{-- Bloque 2: Regla de Ajuste --}}
        <div class="lg:col-span-6 p-4 rounded-xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 shadow-xs space-y-3">
            <div class="text-xs font-bold uppercase tracking-wider text-slate-700 dark:text-slate-300 flex items-center space-x-2 border-b border-slate-200/80 dark:border-slate-800 pb-2">
                <span class="w-5 h-5 rounded-full bg-emerald-100 dark:bg-emerald-900/40 text-emerald-950 dark:text-emerald-300 flex items-center justify-center text-[11px] font-bold">2</span>
                <span>Regla de Precios</span>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                <div>
                    <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">Acción</label>
                    <select x-model="tipoAjuste" @change="actualizarPreview()" class="w-full px-2.5 py-2 bg-slate-50 dark:bg-slate-800 border border-slate-300 dark:border-slate-700 rounded-xl text-xs text-slate-900 dark:text-white focus:ring-1 focus:ring-emerald-500">
                        <option value="porcentaje_aumento">Aumentar por Porcentaje (%)</option>
                        <option value="porcentaje_disminucion">Disminuir por Porcentaje (%)</option>
                        <option value="monto_aumento">Aumentar Monto Fijo (C$)</option>
                        <option value="monto_disminucion">Disminuir Monto Fijo (C$)</option>
                        <option value="fijo">Fijar Precio Exacto (C$)</option>
                    </select>
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">
                        Valor del Ajuste <span x-text="tipoAjuste.includes('porcentaje') ? '(%)' : '(C$)'"></span>
                    </label>
                    <input type="number" 
                           step="0.01" 
                           min="0.01" 
                           x-model="valorAjuste" 
                           @input.debounce.300ms="actualizarPreview()"
                           class="w-full px-3 py-2 bg-slate-50 dark:bg-slate-800 border border-slate-300 dark:border-slate-700 rounded-xl text-xs font-mono font-bold text-slate-900 dark:text-white focus:ring-1 focus:ring-emerald-500">
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">Redondeo</label>
                    <select x-model="tipoRedondeo" @change="actualizarPreview()" class="w-full px-2.5 py-2 bg-slate-50 dark:bg-slate-800 border border-slate-300 dark:border-slate-700 rounded-xl text-xs text-slate-900 dark:text-white focus:ring-1 focus:ring-emerald-500">
                        <option value="sin">Sin redondeo (2 decimales exactos)</option>
                        <option value="0.05">Al múltiplo de 0.05 más cercano</option>
                        <option value="unidad">A la unidad entera más cercana</option>
                    </select>
                </div>

                <div class="flex items-end pb-1">
                    <label class="inline-flex items-center space-x-2 cursor-pointer select-none text-xs text-slate-800 dark:text-slate-200">
                        <input type="checkbox" x-model="aplicarPresentaciones" @change="actualizarPreview()" class="rounded border-slate-300 text-emerald-900 focus:ring-emerald-500 w-4 h-4">
                        <span class="font-medium">Aplicar también a presentaciones (x Factor)</span>
                    </label>
                </div>
            </div>
        </div>

    </div>

    {{-- Bloque 3: Vista Previa en Vivo --}}
    <div class="p-4 rounded-xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 shadow-xs space-y-3">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2 border-b border-slate-200/80 dark:border-slate-800 pb-2">
            <div class="text-xs font-bold uppercase tracking-wider text-slate-700 dark:text-slate-300 flex items-center space-x-2">
                <span class="w-5 h-5 rounded-full bg-emerald-100 dark:bg-emerald-900/40 text-emerald-950 dark:text-emerald-300 flex items-center justify-center text-[11px] font-bold">3</span>
                <span>Vista Previa de Medicamentos Afectados</span>
            </div>

            <div class="flex items-center space-x-3 text-xs">
                <span class="text-slate-500">Afectados: <strong class="text-slate-900 dark:text-white" x-text="totalAfectados"></strong></span>
                <template x-if="totalMargenNegativo > 0">
                    <span class="px-2 py-0.5 rounded-full text-[11px] font-bold bg-red-50 text-red-950 border border-red-200" x-text="totalMargenNegativo + ' con margen negativo'"></span>
                </template>
            </div>
        </div>

        {{-- Loader --}}
        <div x-show="cargandoPreview" class="py-12 text-center text-slate-500">
            <div class="inline-block animate-spin rounded-full h-8 w-8 border-b-2 border-emerald-600 mb-2"></div>
            <p class="text-xs">Calculando simulación de precios...</p>
        </div>

        {{-- Tabla de Simulación --}}
        <div x-show="!cargandoPreview" class="overflow-x-auto max-h-96 overflow-y-auto">
            <table class="w-full text-left text-xs text-slate-700 dark:text-slate-300">
                <thead class="bg-slate-100/80 dark:bg-slate-800/80 sticky top-0 text-[11px] uppercase tracking-wider text-slate-700 dark:text-slate-400 font-bold border-b border-slate-200 dark:border-slate-800">
                    <tr>
                        <th class="px-4 py-2.5">Medicamento</th>
                        <th class="px-3 py-2.5">Categoría & Lab</th>
                        <th class="px-3 py-2.5 text-right">Costo Compra</th>
                        <th class="px-3 py-2.5 text-right">Precio Actual</th>
                        <th class="px-3 py-2.5 text-right">Precio Nuevo</th>
                        <th class="px-3 py-2.5 text-right">Diferencia</th>
                        <th class="px-3 py-2.5 text-center">Margen Nuevo</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-200/70 dark:divide-slate-800/70">
                    <template x-for="p in productosPreview" :key="p.id">
                        <tr :class="p.es_negativo ? 'bg-red-50/50 dark:bg-red-900/10' : ''">
                            <td class="px-4 py-2 font-medium text-slate-900 dark:text-white">
                                <span x-text="p.nombre"></span>
                                <div class="text-[10px] font-mono text-slate-400" x-text="p.codigo_barra"></div>
                            </td>
                            <td class="px-3 py-2 text-slate-700 dark:text-slate-400">
                                <span x-text="p.categoria"></span> · <span x-text="p.laboratorio"></span>
                            </td>
                            <td class="px-3 py-2 text-right font-mono" x-text="'C$ ' + Number(p.costo).toFixed(2)"></td>
                            <td class="px-3 py-2 text-right font-mono" x-text="'C$ ' + Number(p.precio_actual).toFixed(2)"></td>
                            <td class="px-3 py-2 text-right font-mono font-bold text-slate-900 dark:text-white" x-text="'C$ ' + Number(p.precio_nuevo).toFixed(2)"></td>
                            <td class="px-3 py-2 text-right font-mono font-semibold" 
                                :class="p.diferencia > 0 ? 'text-emerald-950 dark:text-emerald-400' : (p.diferencia < 0 ? 'text-red-950 dark:text-red-400' : 'text-slate-500')" 
                                x-text="(p.diferencia > 0 ? '+' : '') + 'C$ ' + Number(p.diferencia).toFixed(2)"></td>
                            <td class="px-3 py-2 text-center">
                                <span class="inline-flex px-2 py-0.5 rounded-full text-[11px] font-bold" 
                                      :class="p.es_negativo ? 'bg-red-50 text-red-950 border border-red-200' : (p.margen_nuevo < 25 ? 'bg-amber-50 text-amber-950 border border-amber-200' : 'bg-emerald-50 text-emerald-950 border border-emerald-200')" 
                                      x-text="p.margen_nuevo + '%'"></span>
                            </td>
                        </tr>
                    </template>
                    <template x-if="productosPreview.length === 0">
                        <tr>
                            <td colspan="7" class="px-4 py-8 text-center text-slate-500">
                                No hay medicamentos seleccionados para la vista previa.
                            </td>
                        </tr>
                    </template>
                </tbody>
            </table>
        </div>
    </div>

    {{-- Modal Centrado de Confirmación Obligatorio --}}
    <div x-show="modalConfirmOpen" 
         x-cloak 
         class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/50 backdrop-blur-xs"
         @keydown.escape.window="modalConfirmOpen = false">
        <div class="w-full max-w-lg bg-white dark:bg-slate-900 rounded-xl border border-slate-200 dark:border-slate-800 shadow-2xl overflow-hidden animate-scaleIn"
             @click.outside="modalConfirmOpen = false">
            <form method="POST" action="{{ route('precios.masivo.aplicar') }}" id="formAplicarMasivo">
                @csrf
                <input type="hidden" name="tipo_alcance" :value="tipoAlcance">
                <input type="hidden" name="alcance_ids" :value="chipsSeleccionados.map(c => c.id).join(',')">
                <input type="hidden" name="tipo_ajuste" :value="tipoAjuste">
                <input type="hidden" name="valor_ajuste" :value="valorAjuste">
                <input type="hidden" name="redondeo" :value="tipoRedondeo">
                <input type="hidden" name="aplicar_presentaciones" :value="aplicarPresentaciones ? 1 : 0">

                <div class="p-5 space-y-4">
                    <div class="flex items-center justify-between pb-3 border-b border-slate-200 dark:border-slate-800">
                        <div class="flex items-center space-x-2">
                            <div class="p-2 rounded-xl bg-emerald-50 dark:bg-emerald-500/10 text-emerald-950 dark:text-emerald-300">
                                <svg class="w-5 h-5 text-emerald-900" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
                            </div>
                            <div>
                                <h3 class="text-sm font-bold text-slate-900 dark:text-white">Confirmar Actualización Masiva</h3>
                                <p class="text-xs text-slate-500">Se registrará en el historial de auditoría</p>
                            </div>
                        </div>
                        <button type="button" @click="modalConfirmOpen = false" class="text-slate-400 hover:text-slate-600">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                        </button>
                    </div>

                    <div class="p-3.5 rounded-xl bg-slate-50 dark:bg-slate-800 space-y-2 text-xs">
                        <div class="flex justify-between">
                            <span class="text-slate-500">Medicamentos Afectados:</span>
                            <span class="font-bold text-slate-900 dark:text-white" x-text="totalAfectados + ' productos'"></span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-slate-500">Regla Aplicada:</span>
                            <span class="font-medium text-slate-800 dark:text-slate-200" x-text="tipoAjuste + ' (' + valorAjuste + ')'"></span>
                        </div>
                        <template x-if="totalMargenNegativo > 0">
                            <div class="p-2 rounded-lg bg-red-50 text-red-950 text-[11px] font-medium border border-red-200">
                                Advertencia: <span class="font-bold" x-text="totalMargenNegativo"></span> producto(s) quedarán con precio inferior a su costo.
                            </div>
                        </template>
                    </div>

                    <div>
                        <label for="motivo_masivo" class="block text-xs font-bold text-slate-800 dark:text-slate-200 mb-1">
                            Motivo o Justificación del Cambio Masivo <span class="text-red-900">*</span>
                        </label>
                        <input type="text" 
                               name="motivo" 
                               id="motivo_masivo" 
                               required 
                               x-model="motivoMasivo"
                               placeholder="Ej: Ajuste general por inflación nacional o cambio arancelario..." 
                               class="w-full px-3 py-2 bg-white dark:bg-slate-800 border border-slate-300 dark:border-slate-700 rounded-xl text-xs text-slate-900 dark:text-white focus:ring-1 focus:ring-emerald-500">
                    </div>

                    <div class="flex items-center justify-end space-x-2 pt-3 border-t border-slate-200 dark:border-slate-800">
                        <button type="button" @click="modalConfirmOpen = false" class="px-3.5 py-1.5 rounded-xl bg-slate-100 dark:bg-slate-800 hover:bg-slate-200 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-300 text-xs font-semibold transition cursor-pointer">
                            Cancelar
                        </button>
                        <button type="submit" 
                                :disabled="motivoMasivo.trim().length < 3"
                                class="inline-flex items-center space-x-1.5 px-3.5 py-1.5 rounded-xl bg-emerald-600 hover:bg-emerald-700 active:bg-emerald-800 disabled:opacity-50 text-white text-xs font-semibold shadow-xs transition cursor-pointer">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                            <span>Confirmar y Aplicar</span>
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>

</div>

@push('scripts')
<script>
function actualizacionMasivaPrecios() {
    return {
        tipoAlcance: 'todo',
        queryBusqueda: '',
        busquedaOpen: false,
        resultadosBusqueda: [],
        chipsSeleccionados: [],

        tipoAjuste: 'porcentaje_aumento',
        valorAjuste: 5.0,
        tipoRedondeo: 'sin',
        aplicarPresentaciones: true,

        cargandoPreview: false,
        productosPreview: [],
        totalAfectados: 0,
        totalMargenNegativo: 0,

        modalConfirmOpen: false,
        motivoMasivo: '',

        init() {
            this.actualizarPreview();
        },

        labelAlcance() {
            switch(this.tipoAlcance) {
                case 'categoria': return 'Categorías';
                case 'laboratorio': return 'Laboratorios';
                case 'proveedor': return 'Proveedores';
                case 'productos': return 'Medicamentos';
                default: return 'Elementos';
            }
        },

        onAlcanceChange() {
            this.chipsSeleccionados = [];
            this.resultadosBusqueda = [];
            this.queryBusqueda = '';
            this.actualizarPreview();
        },

        ejecutarBusquedaAjax() {
            if (this.queryBusqueda.length < 2) {
                this.resultadosBusqueda = [];
                return;
            }
            fetch(`{{ route('precios.buscar-ajax') }}?tipo=${this.tipoAlcance}&q=${encodeURIComponent(this.queryBusqueda)}`)
                .then(r => r.json())
                .then(data => {
                    this.resultadosBusqueda = data.results || [];
                    this.busquedaOpen = true;
                });
        },

        agregarChip(item) {
            if (!this.chipsSeleccionados.some(c => c.id === item.id)) {
                this.chipsSeleccionados.push(item);
            }
            this.busquedaOpen = false;
            this.queryBusqueda = '';
            this.actualizarPreview();
        },

        removerChip(idx) {
            this.chipsSeleccionados.splice(idx, 1);
            this.actualizarPreview();
        },

        actualizarPreview() {
            this.cargandoPreview = true;
            fetch(`{{ route('precios.masivo.preview') }}`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Accept': 'application/json'
                },
                body: JSON.stringify({
                    tipo_alcance: this.tipoAlcance,
                    alcance_ids: this.chipsSeleccionados.map(c => c.id),
                    tipo_ajuste: this.tipoAjuste,
                    valor_ajuste: this.valorAjuste,
                    redondeo: this.tipoRedondeo
                })
            })
            .then(r => r.json())
            .then(res => {
                this.cargandoPreview = false;
                if (res.success) {
                    this.productosPreview = res.productos || [];
                    this.totalAfectados = res.total_afectados || 0;
                    this.totalMargenNegativo = res.total_margen_negativo || 0;
                }
            })
            .catch(err => {
                this.cargandoPreview = false;
            });
        },

        abrirModalConfirmacion() {
            if (this.totalAfectados === 0) {
                alert('No hay medicamentos afectados.');
                return;
            }
            this.modalConfirmOpen = true;
        }
    };
}
</script>
@endpush
@endsection
