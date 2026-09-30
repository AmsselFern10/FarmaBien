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
                Procese devoluciones parciales o totales reintegrando stock al lote o registrando descarte.
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
    <!-- Step 1: Search & Select Sale -->
    <div class="p-6 rounded-xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 shadow-xs space-y-4">
        <h2 class="text-sm font-bold text-slate-900 dark:text-white">Paso 1: Buscar Comprobante de Venta</h2>
        <form method="GET" action="{{ route('devoluciones.create') }}" class="flex flex-col sm:flex-row gap-3">
            <div class="flex-1">
                <input type="number" name="venta_id" placeholder="Ingrese el ID o número de venta (Ej: 105)" required
                       class="w-full text-xs rounded-xl border-slate-300 dark:border-slate-700 bg-slate-50 dark:bg-slate-800 text-slate-900 dark:text-white focus:border-emerald-500 focus:ring-emerald-500">
            </div>
            <button type="submit" class="px-5 py-2 rounded-xl text-xs font-bold text-white bg-emerald-600 hover:bg-emerald-700 transition">
                Cargar Venta
            </button>
        </form>
        <p class="text-xs text-slate-500">También puede abrir la venta directamente desde el <a href="{{ route('ventas.index') }}" class="text-emerald-600 dark:text-emerald-400 underline">Historial de Ventas</a> y hacer clic en "Procesar Devolución".</p>
    </div>
    @else
    <!-- Step 2: Form with Preloaded Sale -->
    <form method="POST" action="{{ route('devoluciones.store') }}" class="space-y-5" @submit="validarEnvio($event)">
        @csrf
        <input type="hidden" name="venta_id" value="{{ $venta->id }}">

        <!-- Venta Info Card -->
        <div class="p-5 rounded-xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 shadow-xs">
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
                    <span class="text-xs font-semibold text-slate-500">Fecha de Venta</span>
                    <p class="text-sm font-medium text-slate-800 dark:text-slate-200 mt-0.5">{{ $venta->fecha->format('d/m/Y H:i') }}</p>
                </div>
                <div>
                    <span class="text-xs font-semibold text-slate-500">Total Original</span>
                    <p class="text-base font-black text-emerald-600 dark:text-emerald-400 mt-0.5">{{ formato_moneda($venta->total) }}</p>
                </div>
            </div>
        </div>

        <!-- Items Table -->
        <div class="rounded-xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 shadow-xs overflow-hidden">
            <div class="px-5 py-4 border-b border-slate-200 dark:border-slate-800 bg-slate-50/50 dark:bg-slate-800/40">
                <h3 class="text-sm font-bold text-slate-900 dark:text-white">Seleccione los productos a devolver</h3>
                <p class="text-xs text-slate-500">Indique la cantidad exacta y defina si reingresa al lote de stock o se reporta como merma</p>
            </div>
            
            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs">
                    <thead class="bg-slate-50 dark:bg-slate-800/60 text-slate-500 dark:text-slate-400 border-b border-slate-200 dark:border-slate-800 text-[11px] font-semibold uppercase tracking-wider">
                        <tr>
                            <th class="px-4 py-3">Producto / Lote</th>
                            <th class="px-4 py-3 text-center">Cant. Vendida</th>
                            <th class="px-4 py-3 text-center">Ya Devuelta</th>
                            <th class="px-4 py-3 text-center">Disponible</th>
                            <th class="px-4 py-3 text-center">Cant. a Devolver</th>
                            <th class="px-4 py-3 text-right">Precio Unit.</th>
                            <th class="px-4 py-3 text-right">Subtotal Reembolso</th>
                            <th class="px-4 py-3 text-center">Destino Físico</th>
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
                                    Lote: <span class="font-mono">{{ $det->lote->numero_lote ?? 'N/A' }}</span> | Vence: {{ $det->lote->fecha_vencimiento ? $det->lote->fecha_vencimiento->format('d/m/Y') : 'N/A' }}
                                </div>
                            </td>
                            <td class="px-4 py-3 text-center font-semibold">{{ $item['cantidad_original'] }}</td>
                            <td class="px-4 py-3 text-center text-slate-500">{{ $item['cantidad_devuelta_previa'] }}</td>
                            <td class="px-4 py-3 text-center font-bold text-emerald-600 dark:text-emerald-400">{{ $item['cantidad_disponible'] }}</td>
                            <td class="px-4 py-3 text-center">
                                @if($item['cantidad_disponible'] > 0)
                                <input type="number" 
                                       name="items[{{ $index }}][cantidad]" 
                                       min="0" 
                                       max="{{ $item['cantidad_disponible'] }}" 
                                       value="0"
                                       @input="calcularTotales()"
                                       data-precio="{{ $det->precio_unitario }}"
                                       data-max="{{ $item['cantidad_disponible'] }}"
                                       class="item-cantidad w-20 text-center text-xs font-bold rounded-lg border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-900 dark:text-white focus:ring-emerald-500">
                                @else
                                <span class="text-[11px] font-bold text-slate-400">Agotado</span>
                                <input type="hidden" name="items[{{ $index }}][cantidad]" value="0">
                                @endif
                            </td>
                            <td class="px-4 py-3 text-right font-medium">
                                {{ formato_moneda($det->precio_unitario) }}
                            </td>
                            <td class="px-4 py-3 text-right font-bold text-slate-900 dark:text-white" id="subtotal-{{ $index }}">
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
                                        Reingresar a Stock
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
                <h3 class="text-sm font-bold text-slate-900 dark:text-white">Datos de la Devolución</h3>
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
                    <label class="block text-xs font-semibold text-slate-600 dark:text-slate-300 mb-1">Observaciones / Justificación</label>
                    <textarea name="observaciones" rows="3" placeholder="Detalle cualquier información relevante sobre el estado del medicamento..."
                              class="w-full text-xs rounded-xl border-slate-300 dark:border-slate-700 bg-slate-50 dark:bg-slate-800 text-slate-900 dark:text-white focus:border-emerald-500 focus:ring-emerald-500"></textarea>
                </div>
            </div>

            <!-- Reembolso & Total -->
            <div class="p-5 rounded-xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 shadow-xs space-y-4">
                <h3 class="text-sm font-bold text-slate-900 dark:text-white">Método de Reembolso</h3>
                
                <div>
                    <label class="block text-xs font-semibold text-slate-600 dark:text-slate-300 mb-1">Forma de Reembolso *</label>
                    <select name="metodo_reembolso" x-model="metodoReembolso" required 
                            class="w-full text-xs rounded-xl border-slate-300 dark:border-slate-700 bg-slate-50 dark:bg-slate-800 text-slate-900 dark:text-white focus:border-emerald-500 focus:ring-emerald-500">
                        <option value="efectivo">Efectivo (Egreso de Caja)</option>
                        <option value="transferencia">Transferencia Bancaria</option>
                        <option value="tarjeta">Reversión de Tarjeta</option>
                        <option value="sin_reembolso">Sin Reembolso (Cambio directo de producto)</option>
                    </select>
                </div>

                <!-- Banco selector if transfer or card -->
                <div x-show="metodoReembolso === 'transferencia' || metodoReembolso === 'tarjeta'" style="display: none;" class="space-y-3">
                    <div>
                        <label class="block text-xs font-semibold text-slate-600 dark:text-slate-300 mb-1">Banco / Entidad Financiera</label>
                        <select name="banco" class="w-full text-xs rounded-xl border-slate-300 dark:border-slate-700 bg-slate-50 dark:bg-slate-800 text-slate-900 dark:text-white focus:border-emerald-500 focus:ring-emerald-500">
                            <option value="">Seleccione un banco...</option>
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
                        <span class="text-xl font-black text-rose-600 dark:text-rose-400" x-text="formatoMoneda(totalReembolso)">C$ 0.00</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Submit Button -->
        <div class="flex items-center justify-end gap-3 pt-2">
            <a href="{{ route('devoluciones.index') }}" class="px-4 py-2 rounded-xl text-xs font-semibold text-slate-600 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800 transition">
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
