@extends('layouts.app')

@section('title', 'Registrar Devolución de Venta - FarmaBien')

@section('content')
<div class="max-w-5xl mx-auto space-y-5" x-data="devolucionForm()">
    
    <!-- Breadcrumbs -->
    <nav class="flex items-center space-x-2 text-xs text-slate-500 dark:text-slate-400">
        <a href="{{ route('dashboard') }}" class="hover:text-emerald-600 dark:hover:text-emerald-400 transition">Inicio</a>
        <svg class="w-3 h-3 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
        <a href="{{ route('ventas.index') }}" class="hover:text-emerald-600 dark:hover:text-emerald-400 transition">Ventas</a>
        <svg class="w-3 h-3 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
        <a href="{{ route('devoluciones.index') }}" class="hover:text-emerald-600 dark:hover:text-emerald-400 transition">Devoluciones</a>
        <svg class="w-3 h-3 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
        <span class="text-slate-800 dark:text-slate-200 font-semibold">Nueva Devolución</span>
    </nav>

    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-xl font-bold text-slate-900 dark:text-white flex items-center gap-2">
                <span>Registrar Devolución de Venta</span>
            </h1>
            <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">
                Procese devoluciones parciales o totales reintegrando stock al lote correspondiente o registrando descarte a merma.
            </p>
        </div>
        
        <div class="flex items-center gap-2.5 shrink-0 flex-wrap">
            <button type="button" 
                    @click="$dispatch('toggle-pos-fullscreen')"
                    title="Modo Pantalla Completa / Ocultar Barras"
                    class="inline-flex items-center space-x-1.5 px-3 py-2 rounded-xl bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 hover:bg-slate-100 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-200 text-xs font-semibold shadow-2xs transition cursor-pointer">
                <svg class="w-4 h-4 text-emerald-600 dark:text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 8V4m0 0h4M4 4l5 5m11-5h-4m4 0v4m0-4l-5 5M4 16v4m0 0h4m-4 0l5-5m11 5l-5-5m5 5v-4m0 4h-4"/></svg>
                <span x-text="posFullscreen ? 'Salir Full' : 'Modo Full'">Modo Full</span>
            </button>

            <a href="{{ route('devoluciones.index') }}" 
               class="inline-flex items-center px-3.5 py-2 rounded-xl text-xs font-semibold text-slate-700 dark:text-slate-200 bg-white dark:bg-slate-800 border border-slate-300 dark:border-slate-700 hover:bg-slate-50 dark:hover:bg-slate-700/60 shadow-xs transition">
                <svg class="w-4 h-4 mr-1.5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                </svg>
                Volver a Devoluciones
            </a>
        </div>
    </div>

    @if(!$venta)
    <!-- Step 1: Search & Pick Sale Interface (Visual, No raw ID needed) -->
    <div class="space-y-4">
        <!-- Search Card -->
        <div class="p-5 rounded-xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 shadow-xs space-y-3">
            <div>
                <h2 class="text-sm font-bold text-slate-900 dark:text-white flex items-center gap-2">
                    <svg class="w-4 h-4 text-emerald-600 dark:text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                    <span>Paso 1: Buscar Comprobante de Venta</span>
                </h2>
                <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">
                    Escriba el número de ticket/factura, el nombre del cliente o seleccione directamente de la lista de ventas recientes:
                </p>
            </div>

            <form method="GET" action="{{ route('devoluciones.create') }}" class="flex flex-col sm:flex-row gap-2.5">
                <div class="relative flex-1">
                    <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                    </div>
                    <input type="text" 
                           name="buscar_venta" 
                           value="{{ request('buscar_venta') }}" 
                           placeholder="Buscar por N° Comprobante (Ej: F-0012, Venta #85) o Nombre de Cliente..."
                           class="w-full pl-10 pr-4 py-2.5 text-xs rounded-xl border-slate-300 dark:border-slate-700 bg-slate-50 dark:bg-slate-800 text-slate-900 dark:text-white focus:border-emerald-500 focus:ring-emerald-500">
                </div>
                <button type="submit" class="px-5 py-2.5 rounded-xl text-xs font-bold text-white bg-slate-800 hover:bg-slate-700 dark:bg-slate-700 dark:hover:bg-slate-600 transition shrink-0">
                    Buscar Venta
                </button>
                @if(request()->filled('buscar_venta'))
                <a href="{{ route('devoluciones.create') }}" class="px-3.5 py-2.5 rounded-xl text-xs font-semibold text-slate-600 dark:text-slate-300 bg-slate-100 dark:bg-slate-800 hover:bg-slate-200 transition text-center shrink-0">
                    Ver Todas
                </a>
                @endif
            </form>
        </div>

        <!-- Recent Sales Table for 1-Click Pick -->
        <div class="rounded-xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 shadow-xs overflow-hidden">
            <div class="px-5 py-3.5 border-b border-slate-200 dark:border-slate-800 bg-slate-50/50 dark:bg-slate-800/40 flex items-center justify-between">
                <div>
                    <h3 class="text-xs font-bold text-slate-900 dark:text-white uppercase tracking-wider">
                        {{ request()->filled('buscar_venta') ? 'Resultados de la Búsqueda' : 'Ventas Recientes Disponibles para Devolución' }}
                    </h3>
                    <p class="text-[11px] text-slate-500">Haga clic en "Seleccionar" en la venta correspondiente para cargar sus productos</p>
                </div>
                <span class="text-xs font-semibold text-emerald-600 dark:text-emerald-400 bg-emerald-50 dark:bg-emerald-950/60 px-2.5 py-0.5 rounded-full border border-emerald-200 dark:border-emerald-800">
                    {{ $ventasRecientes->count() }} ventas
                </span>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs">
                    <thead class="bg-slate-50 dark:bg-slate-800/60 text-slate-500 dark:text-slate-400 border-b border-slate-200 dark:border-slate-800 text-[11px] font-semibold uppercase tracking-wider">
                        <tr>
                            <th class="px-4 py-3">Comprobante / Ticket</th>
                            <th class="px-4 py-3">Cliente</th>
                            <th class="px-4 py-3">Fecha y Hora</th>
                            <th class="px-4 py-3 text-center">Fármacos</th>
                            <th class="px-4 py-3 text-right">Total Venta</th>
                            <th class="px-4 py-3 text-center">Acción</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-200 dark:divide-slate-800 text-slate-700 dark:text-slate-300">
                        @forelse($ventasRecientes as $v)
                        <tr class="hover:bg-slate-50/80 dark:hover:bg-slate-800/50 transition">
                            <td class="px-4 py-3">
                                <span class="font-bold text-slate-900 dark:text-white">{{ $v->numero_comprobante ?? 'Venta #' . $v->id }}</span>
                                <div class="text-[11px] text-slate-400">Atendido por: {{ $v->usuario->name ?? 'Cajero' }}</div>
                            </td>
                            <td class="px-4 py-3">
                                <span class="font-bold text-slate-800 dark:text-slate-200">{{ $v->cliente->nombre ?? 'Cliente General' }}</span>
                                @if($v->cliente?->telefono)
                                <div class="text-[11px] text-slate-400">Tel: {{ $v->cliente->telefono }}</div>
                                @endif
                            </td>
                            <td class="px-4 py-3">
                                <span>{{ $v->fecha->format('d/m/Y') }}</span>
                                <div class="text-[11px] text-slate-400">{{ $v->fecha->format('H:i A') }}</div>
                            </td>
                            <td class="px-4 py-3 text-center font-semibold">
                                <span class="px-2 py-0.5 rounded-full bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-300 text-[11px]">
                                    {{ $v->detalles->count() }} producto(s)
                                </span>
                            </td>
                            <td class="px-4 py-3 text-right font-black text-slate-900 dark:text-white font-mono">
                                {{ formato_moneda($v->total) }}
                            </td>
                            <td class="px-4 py-3 text-center">
                                <a href="{{ route('devoluciones.create', ['venta_id' => $v->id]) }}" 
                                   class="inline-flex items-center px-3 py-1.5 rounded-xl text-xs font-bold text-white bg-emerald-600 hover:bg-emerald-700 shadow-2xs transition">
                                    <span>Seleccionar</span>
                                    <svg class="w-3.5 h-3.5 ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                                </a>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="6" class="px-4 py-8 text-center text-slate-500 dark:text-slate-400">
                                No se encontraron ventas elegibles con el término de búsqueda ingresado.
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    @else
    <!-- Step 2: Form with Preloaded Sale (Clean, Intuitive and Accessible) -->
    <form method="POST" action="{{ route('devoluciones.store') }}" class="space-y-5" @submit="validarEnvio($event)">
        @csrf
        <input type="hidden" name="venta_id" value="{{ $venta->id }}">

        <!-- Venta Info Card with Change Button -->
        <div class="p-5 rounded-xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 shadow-xs">
            <div class="flex items-center justify-between pb-3 border-b border-slate-100 dark:border-slate-800 mb-3">
                <div class="flex items-center gap-2">
                    <span class="w-2.5 h-2.5 rounded-full bg-emerald-500"></span>
                    <h3 class="text-xs font-bold text-slate-700 dark:text-slate-300 uppercase tracking-wider">Venta Seleccionada</h3>
                </div>
                <a href="{{ route('devoluciones.create') }}" class="text-xs font-semibold text-slate-500 hover:text-emerald-600 dark:hover:text-emerald-400 underline">
                    Cambiar Venta
                </a>
            </div>

            <div class="grid grid-cols-2 sm:grid-cols-4 gap-4">
                <div>
                    <span class="text-xs font-semibold text-slate-500">Comprobante</span>
                    <h3 class="text-base font-black text-slate-900 dark:text-white mt-0.5">{{ $venta->numero_comprobante ?? 'Venta #' . $venta->id }}</h3>
                </div>
                <div>
                    <span class="text-xs font-semibold text-slate-500">Cliente</span>
                    <p class="text-sm font-bold text-slate-800 dark:text-slate-200 mt-0.5">{{ $venta->cliente->nombre ?? 'Cliente General' }}</p>
                </div>
                <div>
                    <span class="text-xs font-semibold text-slate-500">Fecha de Emisión</span>
                    <p class="text-sm font-medium text-slate-800 dark:text-slate-200 mt-0.5">{{ $venta->fecha->format('d/m/Y H:i') }}</p>
                </div>
                <div>
                    <span class="text-xs font-semibold text-slate-500">Total Original</span>
                    <p class="text-base font-black text-emerald-600 dark:text-emerald-400 mt-0.5 font-mono">{{ formato_moneda($venta->total) }}</p>
                </div>
            </div>
        </div>

        <!-- Items Table -->
        <div class="rounded-xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 shadow-xs overflow-hidden">
            <div class="px-5 py-4 border-b border-slate-200 dark:border-slate-800 bg-slate-50/50 dark:bg-slate-800/40">
                <h3 class="text-sm font-bold text-slate-900 dark:text-white">Paso 2: Indique la cantidad de medicamentos a devolver</h3>
                <p class="text-xs text-slate-500">Ajuste las unidades a reembolsar y decida si el medicamento reingresa al stock del lote o se envía a merma</p>
            </div>
            
            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs">
                    <thead class="bg-slate-50 dark:bg-slate-800/60 text-slate-500 dark:text-slate-400 border-b border-slate-200 dark:border-slate-800 text-[11px] font-semibold uppercase tracking-wider">
                        <tr>
                            <th class="px-4 py-3">Producto / Lote</th>
                            <th class="px-4 py-3 text-center">Vendida</th>
                            <th class="px-4 py-3 text-center">Ya Devuelta</th>
                            <th class="px-4 py-3 text-center">Disponible</th>
                            <th class="px-4 py-3 text-center">Cant. a Devolver</th>
                            <th class="px-4 py-3 text-right">Precio Unit.</th>
                            <th class="px-4 py-3 text-right">Subtotal</th>
                            <th class="px-4 py-3 text-center">Destino en Inventario</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-200 dark:divide-slate-800 text-slate-700 dark:text-slate-300">
                        @foreach($detallesDisponibles as $index => $item)
                        @php 
                            $det = $item['detalle'];
                        @endphp
                        <tr class="hover:bg-slate-50/80 dark:hover:bg-slate-800/50 transition">
                            <td class="px-4 py-3">
                                <input type="hidden" name="items[{{ $index }}][detalle_venta_id]" value="{{ $det->id }}">
                                <span class="font-bold text-slate-900 dark:text-white">{{ $det->producto->nombre }}</span>
                                <div class="text-[11px] text-slate-500">
                                    Lote: <span class="font-mono font-semibold text-slate-700 dark:text-slate-300">{{ $det->lote->numero_lote ?? 'N/A' }}</span> | Vence: {{ $det->lote->fecha_vencimiento ? $det->lote->fecha_vencimiento->format('d/m/Y') : 'N/A' }}
                                </div>
                            </td>
                            <td class="px-4 py-3 text-center font-semibold">{{ $item['cantidad_original'] }}</td>
                            <td class="px-4 py-3 text-center text-slate-500">{{ $item['cantidad_devuelta_previa'] }}</td>
                            <td class="px-4 py-3 text-center font-bold text-emerald-600 dark:text-emerald-400">{{ $item['cantidad_disponible'] }}</td>
                            <td class="px-4 py-3 text-center">
                                @if($item['cantidad_disponible'] > 0)
                                <div class="inline-flex items-center border border-slate-300 dark:border-slate-700 rounded-lg overflow-hidden bg-white dark:bg-slate-800">
                                    <button type="button" 
                                            @click="ajustarCantidad({{ $index }}, -1)"
                                            class="px-2 py-1 bg-slate-100 dark:bg-slate-700 text-slate-600 dark:text-slate-300 hover:bg-slate-200 text-xs font-bold">-</button>
                                    <input type="number" 
                                           id="input-cant-{{ $index }}"
                                           name="items[{{ $index }}][cantidad]" 
                                           min="0" 
                                           max="{{ $item['cantidad_disponible'] }}" 
                                           value="0"
                                           @input="calcularTotales()"
                                           data-precio="{{ $det->precio_unitario }}"
                                           data-max="{{ $item['cantidad_disponible'] }}"
                                           class="item-cantidad w-14 text-center text-xs font-bold border-0 bg-transparent text-slate-900 dark:text-white focus:ring-0 p-1">
                                    <button type="button" 
                                            @click="ajustarCantidad({{ $index }}, 1)"
                                            class="px-2 py-1 bg-slate-100 dark:bg-slate-700 text-slate-600 dark:text-slate-300 hover:bg-slate-200 text-xs font-bold">+</button>
                                </div>
                                @else
                                <span class="text-[11px] font-bold text-slate-400">Totalmente Devuelto</span>
                                <input type="hidden" name="items[{{ $index }}][cantidad]" value="0">
                                @endif
                            </td>
                            <td class="px-4 py-3 text-right font-mono font-medium">
                                {{ formato_moneda($det->precio_unitario) }}
                            </td>
                            <td class="px-4 py-3 text-right font-mono font-bold text-slate-900 dark:text-white" id="subtotal-{{ $index }}">
                                C$ 0.00
                            </td>
                            <td class="px-4 py-3 text-center">
                                <div class="flex flex-col items-center gap-1">
                                    <select name="items[{{ $index }}][estado_producto]" 
                                            @change="cambiarEstadoProducto({{ $index }}, $event)"
                                            class="text-[11px] py-1 rounded-lg border-slate-300 dark:border-slate-700 bg-slate-50 dark:bg-slate-800 text-slate-900 dark:text-white">
                                        <option value="buen_estado">Buen Estado</option>
                                        <option value="danado">Dañado / Defectuoso</option>
                                        <option value="vencido">Vencido</option>
                                    </select>
                                    <label class="inline-flex items-center text-[10px] text-slate-500 cursor-pointer">
                                        <input type="checkbox" 
                                               name="items[{{ $index }}][reingresa_a_stock]" 
                                               value="1" 
                                               checked
                                               id="reingresa-{{ $index }}"
                                               class="rounded border-slate-300 text-emerald-600 focus:ring-emerald-500 mr-1">
                                        Reingresar al Lote
                                    </label>
                                </div>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Devolución Metadata & Reembolso -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
            <!-- Motivo & Observaciones -->
            <div class="p-5 rounded-xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 shadow-xs space-y-4">
                <h3 class="text-sm font-bold text-slate-900 dark:text-white">Motivo y Justificación</h3>
                <div>
                    <label class="block text-xs font-semibold text-slate-600 dark:text-slate-300 mb-1">Motivo de Devolución *</label>
                    <select name="motivo" required class="w-full text-xs rounded-xl border-slate-300 dark:border-slate-700 bg-slate-50 dark:bg-slate-800 text-slate-900 dark:text-white focus:border-emerald-500 focus:ring-emerald-500">
                        <option value="cliente_desiste">Cliente desistió de la compra</option>
                        <option value="error_despacho">Error en el despacho / Presentación incorrecta</option>
                        <option value="producto_defectuoso">Producto en mal estado / Defectuoso</option>
                        <option value="vencido">Producto próximo a vencer / vencido</option>
                        <option value="receta_modificada">Prescripción médica modificada</option>
                        <option value="otro">Otro motivo</option>
                    </select>
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-600 dark:text-slate-300 mb-1">Observaciones Opcionales</label>
                    <textarea name="observaciones" rows="3" placeholder="Detalle cualquier información relevante sobre la devolución..."
                              class="w-full text-xs rounded-xl border-slate-300 dark:border-slate-700 bg-slate-50 dark:bg-slate-800 text-slate-900 dark:text-white focus:border-emerald-500 focus:ring-emerald-500"></textarea>
                </div>
            </div>

            <!-- Reembolso & Total -->
            <div class="p-5 rounded-xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 shadow-xs space-y-4">
                <h3 class="text-sm font-bold text-slate-900 dark:text-white">Forma de Reembolso</h3>
                
                <div>
                    <label class="block text-xs font-semibold text-slate-600 dark:text-slate-300 mb-1">Método de Reembolso *</label>
                    <select name="metodo_reembolso" x-model="metodoReembolso" required 
                            class="w-full text-xs rounded-xl border-slate-300 dark:border-slate-700 bg-slate-50 dark:bg-slate-800 text-slate-900 dark:text-white focus:border-emerald-500 focus:ring-emerald-500">
                        <option value="efectivo">Efectivo (Egreso automático de Caja Activa)</option>
                        <option value="transferencia">Transferencia Bancaria</option>
                        <option value="tarjeta">Reversión de Tarjeta</option>
                        <option value="sin_reembolso">Sin Reembolso (Cambio por otro producto)</option>
                    </select>
                </div>

                <!-- Banco selector if transfer or card -->
                <div x-show="metodoReembolso === 'transferencia' || metodoReembolso === 'tarjeta'" style="display: none;" class="space-y-3">
                    <div>
                        <label class="block text-xs font-semibold text-slate-600 dark:text-slate-300 mb-1">Banco / Entidad Financiera</label>
                        <select name="banco" class="w-full text-xs rounded-xl border-slate-300 dark:border-slate-700 bg-slate-50 dark:bg-slate-800 text-slate-900 dark:text-white focus:border-emerald-500 focus:ring-emerald-500">
                            <option value="">Seleccione Banco...</option>
                            <option value="BAC Credomatic">BAC Credomatic</option>
                            <option value="Banco LAFISE Bancentro">Banco LAFISE Bancentro</option>
                            <option value="Banpro Grupo Promerica">Banpro Grupo Promerica</option>
                            <option value="Banco Ficohsa">Banco Ficohsa</option>
                            <option value="Banco Avanz">Banco Avanz</option>
                            <option value="Banco BDF">Banco BDF</option>
                            <option value="Otro">Otro Banco / Billetera Digital</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-slate-600 dark:text-slate-300 mb-1">Número de Transacción / Referencia</label>
                        <input type="text" name="numero_transaccion" placeholder="Ej: TR-8948123"
                               class="w-full text-xs rounded-xl border-slate-300 dark:border-slate-700 bg-slate-50 dark:bg-slate-800 text-slate-900 dark:text-white focus:border-emerald-500 focus:ring-emerald-500">
                    </div>
                </div>

                <!-- Total Amount Summary Card -->
                <div class="p-4 rounded-xl bg-slate-50 dark:bg-slate-800/80 border border-slate-200 dark:border-slate-700 flex items-center justify-between">
                    <div>
                        <span class="text-xs font-semibold text-slate-500">Monto Total a Reembolsar:</span>
                        <p class="text-[11px] text-slate-400">Calculado en base a productos devueltos</p>
                    </div>
                    <div class="text-right">
                        <span class="text-xl font-black text-rose-600 dark:text-rose-400 font-mono" x-text="formatoMoneda(totalReembolso)">C$ 0.00</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Submit Button -->
        <div class="flex items-center justify-end gap-3 pt-2">
            <a href="{{ route('devoluciones.create') }}" class="px-4 py-2 rounded-xl text-xs font-semibold text-slate-600 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800 transition">
                Cancelar
            </a>
            <button type="submit" 
                    :disabled="totalReembolso <= 0"
                    :class="totalReembolso <= 0 ? 'opacity-50 cursor-not-allowed bg-slate-400' : 'bg-rose-600 hover:bg-rose-700 shadow-sm shadow-rose-600/30'"
                    class="px-5 py-2.5 rounded-xl text-xs font-bold text-white transition">
                Confirmar y Procesar Devolución
            </button>
        </div>
    </form>
    @endif
</div>

<script>
function devolucionForm() {
    return {
        metodoReembolso: 'efectivo',
        totalReembolso: 0,

        ajustarCantidad(index, delta) {
            const input = document.getElementById('input-cant-' + index);
            if (!input) return;
            let val = parseInt(input.value) || 0;
            const max = parseInt(input.dataset.max) || 0;
            val = Math.max(0, Math.min(max, val + delta));
            input.value = val;
            this.calcularTotales();
        },

        calcularTotales() {
            let total = 0;
            const inputs = document.querySelectorAll('.item-cantidad');
            
            inputs.forEach((input, index) => {
                const cant = parseInt(input.value) || 0;
                const max = parseInt(input.dataset.max) || 0;
                const precio = parseFloat(input.dataset.precio) || 0;

                if (cant > max) {
                    input.value = max;
                }

                const subtotal = (parseInt(input.value) || 0) * precio;
                total += subtotal;

                const subtotalElem = document.getElementById('subtotal-' + index);
                if (subtotalElem) {
                    subtotalElem.innerText = 'C$ ' + subtotal.toFixed(2);
                }
            });

            this.totalReembolso = total;
        },

        cambiarEstadoProducto(index, event) {
            const val = event.target.value;
            const check = document.getElementById('reingresa-' + index);
            if (check) {
                if (val === 'danado' || val === 'vencido') {
                    check.checked = false;
                } else {
                    check.checked = true;
                }
            }
        },

        formatoMoneda(val) {
            return 'C$ ' + Number(val).toLocaleString('es-NI', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
        },

        validarEnvio(e) {
            if (this.totalReembolso <= 0) {
                e.preventDefault();
                alert('Debe indicar al menos una cantidad mayor a 0 para devolver.');
                return false;
            }
            return true;
        }
    };
}
</script>
@endsection
