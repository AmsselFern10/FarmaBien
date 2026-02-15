@extends('layouts.app')

@section('title', 'Editar Venta')

@section('header')
    Editar Venta
@endsection

@section('page-actions')
    <div>
        <h2 class="text-2xl font-bold text-slate-900 dark:text-white">
            Editar Venta #{{ $venta->id }} ✏️
        </h2>
        <p class="text-sm text-slate-600 dark:text-slate-400 mt-1">
            Modifica la venta (se anula la original y se crea una nueva)
        </p>
    </div>
    <div class="flex gap-3">
        <button type="button"
                onclick="window.dispatchEvent(new CustomEvent('pos-revertir-venta'))"
                class="inline-flex items-center px-4 py-2 bg-rose-50 dark:bg-rose-900/20 border border-rose-200 dark:border-rose-800 text-rose-700 dark:text-rose-300 font-semibold rounded-lg hover:bg-rose-100 dark:hover:bg-rose-900/30 transition-colors duration-200 shadow-sm">
            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
            </svg>
            Revertir cambios
        </button>

        <a href="{{ route('ventas.show', $venta) }}"
           class="inline-flex items-center px-4 py-2 bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-600 text-slate-700 dark:text-slate-300 font-semibold rounded-lg hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors duration-200 shadow-sm">
            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                      d="M15 19l-7-7 7-7"/>
            </svg>
            Volver
        </a>

    </div>
@endsection

@push('styles')
<style>
    [x-cloak] { display: none !important; }
    .col-resize-handle{touch-action:none;}
    th.th-resizable{overflow:visible; cursor: move;}
    #tablaProductos td{vertical-align:top;}
</style>
@endpush

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-1 lg:px-3 py-0 pb-2" x-data="ventaPOS()" x-init="init()" x-cloak>

    {{-- Errores server-side (si vuelves a validar por POST tradicional) --}}
    @if ($errors->any())
        <div class="mb-4 rounded-xl border border-red-200 bg-red-50 p-4 text-red-700">
            <p class="font-bold mb-2">Hay errores en el formulario:</p>
            <ul class="list-disc ml-5 text-sm space-y-1">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    @php
        // Catálogo POS: map igual que compras, pero con precio_venta + lotes + presentaciones
        $productosData = collect($productos ?? [])->map(function($p){
            $img = $p->imagen ?? null;
            $imgUrl = null;
            if ($img) {
                $imgUrl = str_starts_with($img, 'http') ? $img : asset('storage/'.$img);
            }

            // Lotes disponibles FEFO (ya vienen filtrados por controller)
            $lotes = ($p->lotes ?? collect())->map(function($l){
                $fv = $l->fecha_vencimiento ? \Illuminate\Support\Carbon::parse($l->fecha_vencimiento) : null;
                return [
                    'id' => $l->id,
                    'numero_lote' => $l->numero_lote,
                    'fecha_vencimiento' => $fv?->format('Y-m-d'),
                    'fecha_vencimiento_fmt' => $fv?->format('d/m/Y'),
                    'stock_actual' => (int)($l->stock_actual ?? 0),
                ];
            })->values();

            // Presentaciones (Unidad + presentaciones activas)
            $presentaciones = collect([
                [
                    'id' => null,
                    'nombre' => 'Unidad',
                    'descripcion' => 'Venta por unidad',
                    'unidades_por_presentacion' => 1,
                    'precio_sugerido' => (float)($p->precio_venta ?? 0),
                ]
            ])->concat(
                collect($p->presentacionesActivas ?? [])->map(function($pr){
                    return [
                        'id' => $pr->id,
                        'nombre' => $pr->nombre,
                        'descripcion' => $pr->descripcion,
                        'unidades_por_presentacion' => (int)$pr->unidades_por_presentacion,
                        'precio_sugerido' => (float)($pr->precio_sugerido ?? 0),
                    ];
                })
            )->values();

            $stockTotal = $lotes->sum('stock_actual');

            return [
                'id' => $p->id,
                'nombre' => $p->nombre,
                'descripcion' => $p->descripcion ?? null,
                'codigo_barra' => $p->codigo_barra ?? null,
                'imagen_url' => $imgUrl,
                'precio_venta' => (float)($p->precio_venta ?? 0),
                'requiere_receta' => (bool)($p->requiere_receta ?? false),
                'stock_total' => (int)$stockTotal,
                'lotes' => $lotes,
                'presentaciones' => $presentaciones,
            ];
        })->values();
    @endphp

    
    @php
        $clientesData = collect($clientes ?? [])->map(function($c){
            return [
                'id' => $c->id,
                'nombre' => $c->nombre,
            ];
        })->values();
    
$ventaData = [
    'id' => $venta->id,
    'cliente_id' => $venta->cliente_id,
    'cliente_nombre' => $venta->cliente?->nombre,
    'fecha' => $venta->fecha?->format('Y-m-d H:i:s'),
    'fecha_input' => $venta->fecha?->format('Y-m-d\TH:i'),
    'metodo_pago' => $venta->metodo_pago,
    'banco' => $venta->banco ?? null,
    'monto_recibido' => $venta->monto_recibido ?? null,
    'cambio' => $venta->cambio ?? null,
    'referencia_pago' => $venta->referencia_pago ?? null,
    'descuento_porcentaje' => $venta->descuento_porcentaje ?? 0,
    'observaciones' => $venta->observaciones ?? null,
    'detalles' => ($venta->detalles ?? collect())->map(function($d) {
        
        // Extraemos el valor real usando el operador nullsafe (?->)
        $fv = $d->lote?->fecha_vencimiento; 
        
        // Si fv existe, lo convertimos a Carbon, de lo contrario es null
        $fvCarbon = $fv ? \Illuminate\Support\Carbon::parse($fv) : null;

        return [
            'producto_id' => $d->producto_id,
            'presentacion_id' => $d->presentacion_id,
            'lote_id' => $d->lote_id,

            'cantidad' => $d->cantidad,
            'cantidad_presentaciones' => $d->cantidad_presentaciones,
            'unidades_por_presentacion' => $d->unidades_por_presentacion,
            'tipo_presentacion' => $d->tipo_presentacion,

            'precio_unitario' => $d->precio_unitario,
            'descuento_porcentaje' => $d->descuento_porcentaje,

            // Acceso limpio a los datos del lote
            'lote_numero' => $d->lote?->numero_lote, 
            'lote_venc' => $fvCarbon?->format('Y-m-d'),
            'lote_venc_fmt' => $fvCarbon?->format('d/m/Y'),
        ];
    })->values(),
];

@endphp

    <script>
        window.__VENTAS_CATALOGO = @json($productosData);
        window.__VENTAS_CLIENTES = @json($clientesData);
        window.__VENTA_EDIT = @json($ventaData);
    </script>


    {{-- =========================
         PRODUCTOS (full width)
       ========================= --}}
    <div class="bg-white dark:bg-gray-800 rounded-xl shadow-lg border border-gray-200 dark:border-gray-700 overflow-hidden flex flex-col flex-1">
        <div class="px-4 py-3 border-b border-gray-200 dark:border-gray-700 bg-gradient-to-r from-green-50 to-white dark:from-gray-800 dark:to-gray-800/50">
            <div class="flex items-center justify-between gap-3 flex-wrap">
                <div class="flex items-center space-x-3">
                    <div class="p-2 bg-green-100 dark:bg-green-900/30 rounded-lg">
                        <svg class="w-6 h-6 text-green-600 dark:text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 7h18M3 11h18M7 15h10M7 19h10"/>
                        </svg>
                    </div>
                    <div>
                        <h3 class="text-lg font-semibold text-slate-900 dark:text-white">Productos</h3>
                        <p class="text-sm text-slate-500 dark:text-slate-400">Escanea, busca o selecciona desde catálogo.</p>
                    </div>
                </div>

                <div class="flex items-center gap-2">

                    <div class="inline-flex rounded-lg overflow-hidden border border-gray-300 dark:border-gray-600 shadow-sm">
                        <button type="button" @click="setVista('tabla')"
                                :class="vista === 'tabla' ? 'px-4 py-2 bg-green-600 text-white font-semibold' : 'px-4 py-2 bg-white dark:bg-gray-700 text-slate-700 dark:text-slate-200 font-semibold'">
                            Tabla
                        </button>
                        <button type="button" @click="setVista('card')"
                                :class="vista === 'card' ? 'px-4 py-2 bg-green-600 text-white font-semibold' : 'px-4 py-2 bg-white dark:bg-gray-700 text-slate-700 dark:text-slate-200 font-semibold'">
                            Cards
                        </button>
                        <button type="button" @click="setVista('ticket')"
                                :class="vista === 'ticket' ? 'px-4 py-2 bg-green-600 text-white font-semibold' : 'px-4 py-2 bg-white dark:bg-gray-700 text-slate-700 dark:text-slate-200 font-semibold'">
                            Ticket
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <div class="p-4 space-y-4">

            {{-- Barra de escaneo (igual a compras) --}}
            <div class="rounded-xl border border-gray-200 dark:border-gray-700 bg-slate-50 dark:bg-gray-800/50 p-3">
                <div class="grid grid-cols-1 lg:grid-cols-12 gap-3 items-end">
                    <div class="lg:col-span-9">
                        <label for="barcodeInput" class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-1">
                            Escanear / escribir código de barras
                        </label>
                        <input id="barcodeInput" x-ref="barcode" type="text" inputmode="numeric" autocomplete="off"
                               class="w-full px-3 py-2.5 bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-lg text-base text-slate-900 dark:text-white focus:ring-2 focus:ring-amber-500 focus:border-amber-500"
                               placeholder="Clic aquí y escanea…"
                               x-model="barcode"
                               @input.debounce.150ms="updateBarcodePreview()"
                               @keydown.enter.prevent="agregarPorBarcode()">
                    </div>

                    
                    <div class="lg:col-span-3">
                        <div class="grid grid-cols-2 gap-2">
                            <button type="button" @click="agregarPorBarcode()"
                                    class="inline-flex items-center justify-center w-full px-4 py-2.5 bg-gradient-to-r from-amber-500 to-amber-600 hover:from-amber-600 hover:to-amber-700 text-white font-semibold rounded-lg shadow-sm transition-all duration-200 hover:shadow-md disabled:opacity-50 disabled:cursor-not-allowed">
                                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                                </svg>
                                Agregar
                            </button>

                            <button type="button" @click="openCatalogo()"
                                    class="inline-flex items-center justify-center w-full px-4 py-2.5 bg-green-600 hover:bg-green-700 text-white font-semibold rounded-lg shadow-sm transition-colors duration-200">
                                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 7h16M4 12h16M4 17h16"/>
                                </svg>
                                Catálogo
                            </button>
                        </div>
                    </div>

                </div>

                <p class="mt-2 text-xs text-slate-500 dark:text-slate-400">ENTER también agrega si tu escáner lo envía.</p>

                {{-- Preview (igual a compras) --}}
                <div x-show="barcodePreview.show" x-transition class="mt-3">
                    <div class="flex items-center gap-3 p-3 rounded-xl border border-slate-200 dark:border-gray-700 bg-white dark:bg-gray-800">
                        <div class="w-12 h-12 rounded-lg overflow-hidden bg-white dark:bg-gray-700 border border-slate-200 dark:border-gray-600 flex items-center justify-center">
                            <template x-if="barcodePreview.imagen_url">
                                <img :src="barcodePreview.imagen_url" alt="Producto" class="w-full h-full object-cover">
                            </template>
                            <template x-if="!barcodePreview.imagen_url">
                                <svg class="w-6 h-6 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 7h18M3 11h18M7 15h10M7 19h10"/>
                                </svg>
                            </template>
                        </div>

                        <div class="min-w-0 flex-1">
                            <p class="font-semibold text-slate-900 dark:text-white truncate" x-text="barcodePreview.nombre || '—'"></p>
                            <p class="text-xs text-slate-600 dark:text-slate-400 truncate" x-text="barcodePreview.extra || '—'"></p>
                        </div>

                        <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold"
                              :class="barcodePreview.badgeClass"
                              x-text="barcodePreview.badge">
                        </span>
                    </div>
                </div>
            </div>

            {{-- VISTA TABLA (full width) --}}
            <div x-show="vista === 'tabla'" x-transition x-cloak>
                <div class="rounded-xl border border-gray-200 dark:border-gray-700 overflow-hidden">
                    <div class="max-h-[72vh] lg:max-h-[calc(100vh-280px)] overflow-auto">
                        <table id="tablaProductos" class="min-w-[1500px] w-full text-sm table-fixed">
                            <thead class="bg-gray-50 dark:bg-gray-900/40">
                            <tr id="theadRowProductos" class="text-left text-slate-700 dark:text-slate-300">
                                <th class="px-2 py-3 w-10 text-center"></th>

                            
                      <th data-col="1" data-minw="160" class="px-3 py-3 relative select-none th-resizable cursor-move min-w-[160px] w-[180px]">
                                    Producto
                                    <div class="col-resize-handle absolute top-0 right-0 h-full w-3 cursor-col-resize select-none z-20"></div>
                                </th>

                                <th data-col="2" data-minw="150" class="px-3 py-3 text-center relative select-none th-resizable cursor-move min-w-[150px] w-[160px]">
                                    Presentación
                                    <div class="col-resize-handle absolute top-0 right-0 h-full w-3 cursor-col-resize select-none z-20"></div>
                                </th>

                                <th data-col="3" data-minw="120" class="px-3 py-3 text-center relative select-none th-resizable cursor-move min-w-[120px] w-[120px]">
                                    Cantidad
                                    <div class="col-resize-handle absolute top-0 right-0 h-full w-3 cursor-col-resize select-none z-20"></div>
                                </th>

                                <th data-col="4" data-minw="110" class="px-3 py-3 text-center relative select-none th-resizable cursor-move min-w-[110px] w-[150px]">
                                    Precio
                                    <div class="col-resize-handle absolute top-0 right-0 h-full w-3 cursor-col-resize select-none z-20"></div>
                                </th>

                                <th data-col="5" data-minw="120" class="px-3 py-3 text-center relative select-none th-resizable cursor-move min-w-[120px] w-[120px]">
                                    Desc. %
                                    <div class="col-resize-handle absolute top-0 right-0 h-full w-3 cursor-col-resize select-none z-20"></div>
                                </th>

                               <th data-col="6" data-minw="130" class="px-3 py-3 !text-center relative select-none th-resizable cursor-move min-w-[130px] w-[130px]">
    <div class="flex items-center justify-center w-full">
        Subtotal
    </div>
    <div class="col-resize-handle absolute top-0 right-0 h-full w-3 cursor-col-resize select-none z-20"></div>
</th>

                                <th data-col="7" data-minw="110" class="px-3 py-3 relative select-none th-resizable cursor-move min-w-[110px] w-[170px]">
                                    Lote (FEFO)
                                    <div class="col-resize-handle absolute top-0 right-0 h-full w-3 cursor-col-resize select-none z-20"></div>
                                </th>

                                <th class="px-3 py-3 w-16 text-center"></th>
                            </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-200 dark:divide-gray-700 bg-white dark:bg-gray-800">
                                <template x-for="(it, idx) in items" :key="it.key">
                                    <tr class="align-top">
                                        <td class="px-2 py-3 text-center text-slate-400 dark:text-slate-500 select-none">⋮⋮</td>
                                        <td data-col="1" class="px-3 py-3">
                                            <div class="flex items-start gap-3">
                                                <div class="w-10 h-10 rounded-lg overflow-hidden bg-slate-100 dark:bg-gray-700 border border-slate-200 dark:border-gray-600 flex items-center justify-center shrink-0">
                                                    <template x-if="it.imagen_url">
                                                        <img :src="it.imagen_url" class="w-full h-full object-cover" alt="">
                                                    </template>
                                                    <template x-if="!it.imagen_url">
                                                        <svg class="w-5 h-5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 7h18M3 11h18M7 15h10M7 19h10"/>
                                                        </svg>
                                                    </template>
                                                </div>
                                                <div class="min-w-0">
                                                    <p class="font-semibold text-slate-900 dark:text-white leading-5 whitespace-normal break-words" x-text="it.nombre"></p>
                                                    <p class="text-xs text-slate-500 dark:text-slate-400 whitespace-normal break-words" x-text="it.codigo_barra ? ('CB: ' + it.codigo_barra) : '—'"></p>
                                                </div>
                                            </div>
                                        </td>

                                        <td data-col="2" class="px-3 py-3">
                                            <label class="sr-only">Presentación</label>
                                            <select class="w-full px-3 py-2 bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-lg text-base text-slate-900 dark:text-white placeholder-slate-400 dark:placeholder-slate-400"
                                                    x-model="it.presentacion_id"
                                                    @change="onChangePresentacion(it)">
                                                <template x-for="pres in it.presentaciones" :key="pres.id ?? 'u'">
                                                    <option :value="pres.id" x-text="pres.select_label ?? pres.nombre_completo ?? pres.nombre"></option>
                                                </template>
                                            </select>

                                            <p class="mt-1 text-xs text-slate-500 dark:text-slate-400" x-show="it.unidades_por_presentacion > 1">
                                                Equivale a <span class="font-semibold" x-text="money(it.precio_unitario)"></span> / unidad
                                            </p>
                                        </td>


                                        <td data-col="3" class="px-3 py-3 text-center">
                                            <label class="sr-only">Cantidad</label>
                                            <input type="number" min="1" step="1"
                                                   class="w-24 mx-auto px-3 py-2 bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-lg text-base text-center text-slate-900 dark:text-white placeholder-slate-400 dark:placeholder-slate-400"
                                                   x-model.number="it.cantidad_presentaciones"
                                                   @input.debounce.150ms="recalcItem(it)">
                                        </td>


                                        <td data-col="4" class="px-3 py-3 text-right">
                                            
                                            <input type="number" min="0" step="0.01"
                                                   class="w-36 ml-auto px-3 py-2 text-center bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-lg text-base  text-slate-900 dark:text-white placeholder-slate-400 dark:placeholder-slate-400"
                                                   x-model.number="it.precio_display"
                                                   @input.debounce.150ms="onChangePrecio(it)">
                                            
                                              <label class="block text-xs text-center font-semibold text-slate-600 dark:text-slate-300 mb-1"
                                                   x-text="priceLabel(it)"></label>

                                            
                                        </td>


                                        <td data-col="5" class="px-3 py-3 text-center">
                                            <input type="number" min="0" step="0.01"
                                                   class="w-24 mx-auto px-3 py-2 bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-lg text-base text-center text-slate-900 dark:text-white placeholder-slate-400 dark:placeholder-slate-400"
                                                   x-model.number="it.descuento_porcentaje"
                                                   @input.debounce.150ms="onChangeDescuento(it)">
                                        </td>


               <td data-col="6" class="px-3 py-3">
    <div class="flex flex-col items-center justify-center">
        <div class="font-semibold text-slate-900 dark:text-white" 
             x-text="money(it.subtotal)">
        </div>

        <div class="text-xs font-medium text-red-500 dark:text-red-400 mt-1" 
             x-show="it.descuento_monto > 0">
            -<span x-text="money(it.descuento_monto)"></span>
        </div>
    </div>
</td>


                                        <td data-col="7" class="px-3 py-3">
                                            <label class="sr-only">Lote</label>
                                            <select class="w-full px-3 py-2 bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-lg text-base text-slate-900 dark:text-white placeholder-slate-400 dark:placeholder-slate-400"
                                                    x-model="it.lote_id"
                                                    @change="onChangeLote(it)">
                                                <template x-for="l in it.lotes" :key="l.id">
                                                    <option :value="l.id" x-text="`${l.numero_lote} • V: ${l.fecha_vencimiento_fmt} • Stock: ${l.stock_actual}`"></option>
                                                </template>
                                            </select>
                                        </td>



                                        <td class="px-3 py-3 text-center">
                                            <button type="button" @click="removeItem(it.key)"
                                                    class="p-2 rounded-lg hover:bg-red-50 dark:hover:bg-red-900/20 text-red-600">
                                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                                </svg>
                                            </button>
                                        </td>
                                    </tr>
                                </template>
                            </tbody>
                        </table>
                    </div>
                </div>

                <div class="mt-3 text-sm text-slate-600 dark:text-slate-400" x-show="items.length === 0">
                    Aún no has agregado productos.
                </div> 
            </div>


            {{-- VISTA CARDS (similar a compras) --}}
            <div x-show="vista === 'card'" x-transition x-cloak>
                <div class="max-h-[72vh] lg:max-h-[calc(100vh-280px)] overflow-auto pr-1">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <template x-for="it in items" :key="it.key">
                            <div class="rounded-2xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 shadow-sm hover:shadow-md transition-shadow overflow-hidden">
                                <div class="p-4 border-b border-gray-200 dark:border-gray-700 flex items-start justify-between gap-3">
                                    <div class="flex items-start gap-3 min-w-0">
                                        <div class="w-12 h-12 rounded-xl overflow-hidden bg-slate-100 dark:bg-gray-700 border border-slate-200 dark:border-gray-600 flex items-center justify-center shrink-0">
                                            <template x-if="it.imagen_url">
                                                <img :src="it.imagen_url" class="w-full h-full object-cover" alt="">
                                            </template>
                                            <template x-if="!it.imagen_url">
                                                <svg class="w-6 h-6 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 7h18M3 11h18M7 15h10M7 19h10"/>
                                                </svg>
                                            </template>
                                        </div>
                                        <div class="min-w-0">
                                            <p class="font-bold text-slate-900 dark:text-white leading-5 truncate" x-text="it.nombre"></p>
                                            <p class="text-xs text-slate-500 dark:text-slate-400 whitespace-normal break-words" x-text="it.codigo_barra ? ('CB: ' + it.codigo_barra) : '—'"></p>
                                        </div>
                                    </div>

                                    <button type="button" @click="removeItem(it.key)"
                                            class="p-2 rounded-xl hover:bg-red-50 dark:hover:bg-red-900/20 text-red-600">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                        </svg>
                                    </button>
                                </div>

                                <div class="p-4 grid grid-cols-1 sm:grid-cols-2 gap-4">
                                    <div>
                                        <label class="block text-xs font-bold text-slate-600 dark:text-slate-300 mb-1">Presentación</label>
                                        <select class="w-full px-3 py-2 bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-lg text-base text-slate-900 dark:text-white"
                                                x-model="it.presentacion_id"
                                                @change="onChangePresentacion(it)">
                                            <template x-for="(pres, idx) in it.presentaciones" :key="(pres.id ?? 'u') + '-' + idx">
                                                <option :value="pres.id === null ? '' : String(pres.id)" x-text="pres.select_label ?? pres.nombre_completo ?? pres.nombre"></option>
                                            </template>
                                        </select>
                                        <p class="mt-1 text-xs text-slate-500 dark:text-slate-400" x-show="it.unidades_por_presentacion > 1">
                                            Equivale a <span class="font-semibold" x-text="money(it.precio_unitario)"></span> / unidad
                                        </p>
                                    </div>

                                    <div>
                                        <label class="block text-xs font-bold text-slate-600 dark:text-slate-300 mb-1">Lote (FEFO)</label>
                                        <select class="w-full px-3 py-2 bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-lg text-base text-slate-900 dark:text-white"
                                                x-model="it.lote_id"
                                                @change="onChangeLote(it)">
                                            <template x-for="(l, lidx) in it.lotes" :key="String(l.id) + '-' + lidx">
                                                <option :value="String(l.id)" x-text="`${l.numero_lote} • V: ${l.fecha_vencimiento_fmt} • Stock: ${l.stock_actual}`"></option>
                                            </template>
                                        </select>
                                    </div>

                                    <div>
                                        <label class="block text-xs font-bold text-slate-600 dark:text-slate-300 mb-1">Cantidad</label>
                                        <input type="number" min="1" step="1"
                                               class="w-full px-3 py-2 bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-lg text-base text-slate-900 dark:text-white text-center"
                                               x-model.number="it.cantidad_presentaciones"
                                               @input.debounce.150ms="recalcItem(it)">
                                    </div>

                                    <div>
                                        <label class="block text-xs font-bold text-slate-600 dark:text-slate-300 mb-1" x-text="priceLabel(it)"></label>
                                        <input type="number" min="0" step="0.01"
                                               class="w-full px-3 py-2 bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-lg text-base text-slate-900 dark:text-white text-right"
                                               x-model.number="it.precio_display"
                                               @input.debounce.150ms="onChangePrecio(it)">
                                    </div>

                                    <div>
                                        <label class="block text-xs font-bold text-slate-600 dark:text-slate-300 mb-1">Desc. %</label>
                                        <input type="number" min="0" step="0.01"
                                               class="w-full px-3 py-2 bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-lg text-base text-slate-900 dark:text-white text-center"
                                               x-model.number="it.descuento_porcentaje"
                                               @input.debounce.150ms="onChangeDescuento(it)">
                                    </div>

                                    <div class="flex items-end justify-between gap-3">
                                        <div class="text-xs text-slate-500 dark:text-slate-400">
                                            Subtotal
                                            <div class="text-[11px]" x-show="it.descuento_monto > 0">(-<span x-text="money(it.descuento_monto)"></span>)</div>
                                        </div>
                                        <div class="text-lg font-black text-slate-900 dark:text-white" x-text="money(it.subtotal)"></div>
                                    </div>
                                </div>
                            </div>
                        </template>
                    </div>

                    <div class="mt-3 text-sm text-slate-600 dark:text-slate-400" x-show="items.length === 0">
                        Aún no has agregado productos.
                    </div>
                </div>
            </div>

            {{-- VISTA TICKET (full width) --}}
            <div x-show="vista === 'ticket'" x-transition x-cloak>
                <div class="rounded-xl border border-gray-200 dark:border-gray-700 overflow-hidden">
                    <div class="p-4 bg-white dark:bg-gray-800">
                        <div class="flex items-center justify-between">
                            <div>
                                <h4 class="font-bold text-slate-900 dark:text-white">Vista Ticket</h4>
                            </div>
                            <div class="text-xs text-slate-500 dark:text-slate-400" x-text="fecha ? ('Fecha: ' + prettyFecha(fecha)) : ''"></div>
                        </div>

                        <div class="mt-4 border-t border-dashed border-gray-300 dark:border-gray-600 pt-3 space-y-3">
                            <template x-for="it in items" :key="it.key">
                                <div class="flex items-start justify-between gap-3">
                                    <div class="min-w-0">
                                        <p class="font-semibold text-slate-900 dark:text-white" x-text="it.nombre"></p>
                                        <p class="text-xs text-slate-500 dark:text-slate-400">
                                            <span x-text="it.tipo_presentacion"></span>
                                            <span class="mx-1">•</span>
                                            <span x-text="'Cant: ' + it.cantidad_presentaciones"></span>
                                            <span class="mx-1">•</span>
                                            <span x-text="'Lote: ' + (it.lote?.numero_lote || '—')"></span>
                                        </p>
                                    </div>
                                    <div class="text-right shrink-0">
                                        <div class="text-xs text-slate-500 dark:text-slate-400" x-text="money(it.precio_display)"></div>
                                        <div class="font-bold text-slate-900 dark:text-white" x-text="money(it.subtotal)"></div>
                                    </div>
                                </div>
                            </template>

                            <div class="pt-3 border-t border-dashed border-gray-300 dark:border-gray-600 space-y-1 text-sm">
                                <div class="flex justify-between">
                                    <span class="text-slate-600 dark:text-slate-300">Subtotal bruto</span>
                                    <span class="font-semibold text-slate-900 dark:text-white" x-text="money(subtotal_bruto)"></span>
                                </div>
                                <div class="flex justify-between" x-show="descuento_productos > 0">
                                    <span class="text-slate-600 dark:text-slate-300">Desc. productos</span>
                                    <span class="font-semibold text-slate-900 dark:text-white">-<span x-text="money(descuento_productos)"></span></span>
                                </div>
                                <div class="flex justify-between" x-show="descuento_global_monto > 0">
                                    <span class="text-slate-600 dark:text-slate-300">Desc. global</span>
                                    <span class="font-semibold text-slate-900 dark:text-white">-<span x-text="money(descuento_global_monto)"></span></span>
                                </div>
                                <div class="flex justify-between pt-2 border-t border-gray-200 dark:border-gray-700">
                                    <span class="font-bold text-slate-900 dark:text-white">Total</span>
                                    <span class="font-bold text-slate-900 dark:text-white" x-text="money(total_neto)"></span>
                                </div>
                            </div>

                            <div class="text-sm text-slate-600 dark:text-slate-400" x-show="items.length === 0">
                                Aún no has agregado productos.
                            </div>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </div>

       {{-- =========================
         DATOS + CAJA + RESUMEN (compacto, estilo compras)
       ========================= --}}
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-4 items-stretch mt-4">
    
    {{-- COLUMNA IZQUIERDA: Datos + Resumen --}}
<div class="lg:col-span-8 flex flex-col gap-4">
    <div class="bg-white dark:bg-gray-800 rounded-xl shadow-lg border border-gray-200 dark:border-gray-700 overflow-hidden">
        <div class="px-4 py-3 border-b border-gray-200 dark:border-gray-700 bg-gradient-to-r from-blue-50 to-white dark:from-gray-800 dark:to-gray-800/50">
            <div class="flex items-center space-x-3">
                <div class="p-2 bg-blue-100 dark:bg-blue-900/30 rounded-lg">
                    <svg class="w-6 h-6 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 7h18M3 11h18M7 15h10M7 19h10"/>
                    </svg>
                </div>
                <div>
                    <h3 class="text-lg font-semibold text-slate-900 dark:text-white">Datos de la Venta 🧾</h3>
                    <p class="text-sm text-slate-500 dark:text-slate-400">Información general del comprobante</p>
                </div>
            </div>
        </div>

        {{-- Cuerpo optimizado --}}
        <div class="px-5 py-5">
            <div class="grid grid-cols-1 md:grid-cols-12 gap-x-5 gap-y-4">
                
                {{-- Columna Cliente --}}
                <div class="md:col-span-6">
                    <div class="flex items-center justify-between mb-0.5">
                        <label class="block text-base font-bold text-slate-700 dark:text-slate-300 italic">Cliente</label>
                        <button type="button" @click="openNuevoCliente()" class="text-xs font-black text-blue-600 hover:text-blue-700 dark:text-blue-400 uppercase tracking-tighter">
                            + Nuevo
                        </button>
                    </div>
                    <div class="relative">
                        <select id="cliente_id_select" name="cliente_id" x-model="cliente_id" class="w-full pl-3 pr-10 py-1.5 bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-lg text-base font-semibold text-slate-900 dark:text-white focus:ring-2 focus:ring-blue-500 appearance-none">
                            <option value="">— Cliente de mostrador —</option>
                            <template x-for="c in clientes" :key="c.id">
                                <option :value="String(c.id)" x-text="c.nombre"></option>
                            </template>
                        </select>
                        <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-3 text-slate-500">
                            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M19 9l-7 7-7-7" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
                        </div>
                    </div>
                </div>

                {{-- Columna Fecha --}}
                <div class="md:col-span-3">
                    <label class="block text-base font-bold text-slate-700 dark:text-slate-300 mb-0.5 italic">Fecha</label>
                    <input type="datetime-local" name="fecha" x-model="fecha" class="w-full px-2 py-1.5 bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-lg text-base font-semibold text-slate-900 dark:text-white focus:ring-2 focus:ring-blue-500">
                </div>

                {{-- Columna Cajero --}}
                <div class="md:col-span-3">
                    <label class="block text-base font-bold text-slate-700 dark:text-slate-300 mb-0.5 italic">Cajero</label>
                    <div class="relative">
                        <input type="text" value="{{ auth()->user()->name ?? 'Cajero' }}" readonly 
                            class="w-full pl-8 pr-2 py-1.5 bg-slate-50 dark:bg-gray-700/60 border border-gray-200 dark:border-gray-600 rounded-lg text-base font-semibold text-slate-600 dark:text-slate-300 truncate shadow-sm">
                        <div class="absolute inset-y-0 left-0 pl-2.5 flex items-center pointer-events-none text-slate-400">
                            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
                        </div>
                    </div>
                </div>

                {{-- FILA EXPANDIDA: Notas y Motivo --}}
                <div class="md:col-span-6">
                    <label class="block text-base font-bold text-slate-700 dark:text-slate-300 mb-1 italic">Notas</label>
                    <textarea name="observaciones" rows="3" x-model="observaciones" 
                        placeholder="📝 Notas opcionales sobre la venta..." 
                        class="w-full px-3 py-2 bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-lg text-base text-slate-900 dark:text-white focus:ring-2 focus:ring-blue-500 placeholder-slate-400 shadow-sm"></textarea>
                </div>

                <div class="md:col-span-6">
                    <div class="bg-rose-50 dark:bg-rose-900/10 p-3 rounded-xl border border-rose-100 dark:border-rose-900/30 h-full">
                        <label class="block text-sm font-bold text-rose-700 dark:text-rose-400 mb-1">
                            Motivo de modificación <span class="text-rose-500">*</span>
                        </label>
                        <textarea x-model="motivo" rows="2"
                            :class="motivoError ? 'border-rose-500 focus:ring-rose-500' : 'border-rose-200 dark:border-rose-800'"
                            class="w-full px-4 py-2 bg-white dark:bg-gray-800 border rounded-lg text-base text-slate-900 dark:text-white focus:ring-2 focus:ring-rose-500"
                            placeholder="Ej: error de cantidad, precio incorrecto..."></textarea>
                        <p class="mt-1 text-[10px] leading-tight text-rose-600/80 dark:text-rose-400/80 uppercase font-bold italic">
                            Obligatorio para el historial de auditoría.
                        </p>
                    </div>
                </div>
                
                {{-- Alerta de Error Cliente --}}
                <div class="md:col-span-12" x-show="clienteRequiredError">
                    <p class="text-xs font-bold text-red-600 dark:text-red-400 flex items-center gap-1">
                        ⚠️ Selecciona cliente para <span class="underline">Pagar Luego</span>.
                    </p>
                </div>
            </div>
        </div>
    </div>


       {{-- Bloque 2: Resumen (Compacto Vertical con Títulos Originales) --}}
<div class="bg-white dark:bg-gray-800 rounded-xl shadow-lg border border-gray-200 dark:border-gray-700 overflow-hidden">
    {{-- Encabezado con tamaño ORIGINAL --}}
    <div class="px-4 py-3 border-b border-gray-200 dark:border-gray-700 bg-gradient-to-r from-blue-50 to-white dark:from-gray-800 dark:to-gray-800/50">
        <div class="flex items-center gap-3">
            <div class="p-2 bg-blue-100 dark:bg-blue-900/30 rounded-lg">
                <svg class="w-6 h-6 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 14l2-2 4 4m0 0l2-2m-2 2V8m0 8H7a2 2 0 01-2-2V7a2 2 0 012-2h5"/>
                </svg>
            </div>
            <div>
                <h3 class="text-lg font-semibold text-slate-900 dark:text-white">Resumen 💰</h3>
            </div>
        </div>
    </div>

    <div class="px-4 py-3 space-y-3">
        {{-- Fila: Subtotal --}}
        <div class="flex items-center justify-between">
            <span class="text-sm font-semibold text-slate-600 dark:text-slate-400">Subtotal bruto</span>
            <span class="text-base font-bold text-slate-900 dark:text-white">C$ <span x-text="money(subtotal_bruto)"></span></span>
        </div>

        {{-- Fila: Descuento Productos --}}
        <div class="flex items-center justify-between border-t border-gray-100 dark:border-gray-700 pt-2">
            <span class="text-sm font-semibold text-slate-600 dark:text-slate-400">Descuento por producto</span>
            <span class="text-base font-bold text-red-600 dark:text-red-400">C$ <span x-text="money(descuento_productos)"></span></span>
        </div>

        {{-- Fila: Descuento Global (Input y Monto alineados horizontalmente) --}}
        <div class="flex items-center justify-between gap-4 py-2 border-y border-gray-100 dark:border-gray-700">
            <div class="flex items-center gap-2">
                <label for="descuento_global" class="text-sm font-semibold text-slate-600 dark:text-slate-400">Descuento global</label>
                <div class="flex items-center">
                    <input type="number" step="0.01" min="0" max="100" name="descuento" id="descuento_global" 
                           x-model.number="descuento_global_pct" @input="recalcTotales()" 
                           class="w-20 px-3 py-1.5 bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-lg text-base font-bold text-slate-900 dark:text-white focus:ring-2 focus:ring-emerald-500">
                    <span class="ml-1 text-sm font-bold text-slate-500">%</span>
                </div>
            </div>
            <div class="text-right">
                <span class="text-base font-bold text-red-600 dark:text-red-400">C$ <span x-text="money(descuento_global_monto)"></span></span>
            </div>
        </div>

        {{-- Fila: Total Neto con tamaño ORIGINAL --}}
        <div class="pt-2 flex items-center justify-between">
            <span class="text-sm font-bold text-slate-700 dark:text-slate-300 uppercase">Total Neto</span>
            <div class="text-2xl font-black text-emerald-600 dark:text-emerald-400">
                C$ <span x-text="money(total_neto)"></span>
            </div>
        </div>
    </div>
     </div>
</div>

            {{-- Caja / Pago (Diseño Renovado Basado en Referencia) --}}
{{-- Caja / Pago (Diseño Renovado - Sin Campo Motivo) --}}
<div class="lg:col-span-4  ">
    <div class="bg-white dark:bg-gray-800 rounded-xl shadow-lg border border-gray-200 dark:border-gray-700 overflow-hidden h-full flex flex-col sticky top-4">
        
        {{-- Encabezado Estilo Referencia --}}
        <div class="px-4 py-3 border-b border-gray-200 dark:border-gray-700 bg-gradient-to-r from-emerald-50 to-white dark:from-gray-800 dark:to-gray-800/50">
            <div class="flex items-center gap-3">
                <div class="p-2 bg-emerald-100 dark:bg-emerald-900/30 rounded-lg">
                    <svg class="w-6 h-6 text-emerald-600 dark:text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-2m4-6h-4m4 0a2 2 0 01-2 2h-2m4-2a2 2 0 00-2-2h-2m0 4h2a2 2 0 002-2"/>
                    </svg>
                </div>
                <div>
                    <h3 class="text-lg font-semibold text-slate-900 dark:text-white">Caja / Pago 💳</h3>
                    <p class="text-sm text-slate-500 dark:text-slate-400">Finalizar edición</p>
                </div>
            </div>
        </div>

        <div class="p-5 space-y-10 flex-1 flex flex-col">
            
            {{-- Display de Total (Estilo Referencia) --}}
            <div class="px-3 py-4 bg-emerald-600 rounded-xl text-white shadow-md">
                <label class="block text-xs font-bold uppercase opacity-90 tracking-wider">Total Actualizado</label>
                <div class="text-xl font-black">
                    C$ <span x-text="money(total_neto)"></span>
                </div>
            </div>

            {{-- Método de Pago --}}
            <div>
                <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-1">Método de pago</label>
                <select x-ref="metodoPago" name="metodo_pago" x-model="metodo_pago" @change="onChangeMetodoPago()" 
                        class="w-full px-4 py-3 bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-lg text-base text-slate-900 dark:text-white focus:ring-2 focus:ring-emerald-500">
                    <option value="efectivo">💵 Efectivo</option>
                    <option value="transferencia">🏦 Transferencia</option>
                    <option value="credito">💳 Crédito</option>
                    <option value="debito">💳 Débito</option>
                    <option value="pagar_luego">⏳ Pagar Luego</option>
                    <option value="otros">🧾 Otros</option>
                </select>
            </div>

            {{-- Sección de Efectivo Estilo Referencia --}}
            <div x-show="metodo_pago === 'efectivo'" x-transition class="space-y-3 relative">
                <div class="flex items-stretch gap-3">
                    <div class="flex-1">
                        <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">Recibido</label>
                        <input type="number" step="0.01" x-model.number="monto_recibido" @input="recalcTotales()" 
                               class="w-full h-[60px] px-4 py-4 bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-lg text-xl font-bold text-slate-900 dark:text-white focus:ring-2 focus:ring-emerald-500">
                    </div>
                    <div class="flex-1 flex flex-col">
                        <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1 text-center">Vuelto</label>
                        <div class="flex-1 flex flex-col justify-center items-center bg-blue-50 dark:bg-blue-900/20 px-2 h-[60px] rounded-lg border border-blue-200 dark:border-blue-800">
                            <div class="text-xl font-black text-blue-700 dark:text-blue-300">
                                C$ <span x-text="money(cambio)"></span>
                            </div>
                        </div>
                    </div>
                </div>
                <div x-show="efectivoInsuficiente" x-transition class="absolute left-0 -bottom-5 flex items-center gap-1 text-red-600 dark:text-red-400">
                    <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
                    </svg>
                    <span class="text-[11px] font-bold uppercase">Monto insuficiente</span>
                </div>
            </div>

            {{-- Bancos / Referencia --}}
            <div x-show="needsBanco || metodo_pago !== 'efectivo'" x-transition class="space-y-3">
                <div x-show="needsBanco" class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-1">Banco</label>
                        <select x-model="banco" :class="bancoRequiredError ? 'border-red-400 focus:ring-red-500' : ''"
                                class="w-full px-3 py-3 bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-lg text-sm text-slate-900 dark:text-white">
                            <option value="">Seleccionar…</option>
                            <option value="BAC">BAC</option>
                            <option value="LAFISE">LAFISE</option>
                            <option value="FICOHSA">FICOHSA</option>
                            <option value="BANPRO">BANPRO</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-1">Referencia</label>
                        <input type="text" x-model="referencia" :class="referenciaRequiredError ? 'border-red-400 focus:ring-red-500' : ''"
                               placeholder="No. voucher" class="w-full px-3 py-3 bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-lg text-sm text-slate-900 dark:text-white">
                    </div>
                </div>
                <div x-show="!needsBanco && metodo_pago !== 'efectivo'">
                    <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-1">Referencia (opcional)</label>
                    <input type="text" x-model="referencia" placeholder="Referencia / autorización" 
                           class="w-full px-4 py-3 bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-lg text-sm text-slate-900 dark:text-white">
                </div>
            </div>

            {{-- Footer y Botones --}}
            <div class="pt-4 border-t border-gray-200 dark:border-gray-700 space-y-3">
                
                <label class="inline-flex items-center gap-2 text-sm font-semibold text-slate-700 dark:text-slate-300 cursor-pointer">
                    <input type="checkbox" x-model.boolean="mantener_en_pantalla" class="w-4 h-4 rounded border-gray-300 dark:border-gray-600 text-emerald-600 focus:ring-emerald-500">
                    Mantenerme aquí
                </label>

                <div class="flex flex-col gap-2.5">
                    {{-- GRUPO DE BOTÓN DIVIDIDO --}}
                    <div class="relative flex w-full" x-data="{ open: false }">
                        <button type="button" @click="submitVenta()" :disabled="submitDisabled" 
                                class="flex-1 py-3 bg-emerald-600 hover:bg-emerald-700 text-white rounded-l-xl font-bold text-base shadow-lg active:scale-[0.98] transition-transform disabled:opacity-50 uppercase border-r border-emerald-500">
                            <span x-text="accion === 'facturar' ? 'Guardar e Imprimir' : 'GUARDAR CAMBIOS'"></span>
                        </button>

                        <button type="button" @click="open = !open" @click.away="open = false"
                                class="px-3 bg-emerald-600 hover:bg-emerald-700 text-white rounded-r-xl shadow-lg active:scale-[0.98] transition-transform">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                            </svg>
                        </button>

                        <div x-show="open" x-transition 
                             class="absolute bottom-full mb-2 right-0 w-full bg-white dark:bg-gray-700 border border-gray-200 dark:border-gray-600 rounded-lg shadow-xl z-50 overflow-hidden">
                            <button type="button" @click="accion = 'guardar'; open = false" 
                                    class="w-full px-4 py-3 text-left text-sm font-semibold hover:bg-emerald-50 dark:hover:bg-emerald-900/30 text-slate-700 dark:text-slate-200 border-b border-gray-100 dark:border-gray-600">
                                Guardar Cambios (Solo sistema)
                            </button>
                            <button type="button" @click="accion = 'facturar'; open = false" 
                                    class="w-full px-4 py-3 text-left text-sm font-semibold hover:bg-emerald-50 dark:hover:bg-emerald-900/30 text-slate-700 dark:text-slate-200">
                                Guardar e Imprimir Factura
                            </button>
                        </div>
                    </div>

                    <a href="{{ route('ventas.show', $venta) }}" 
                       class="px-4 py-2.5 text-center bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-xl font-semibold text-slate-700 dark:text-slate-300 hover:bg-gray-50 dark:hover:bg-gray-600 transition-colors uppercase text-sm tracking-wide">
                        Cancelar Edición
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>
{{-- =========================
         MODAL CATÁLOGO (igual a compras)
       ========================= --}}
    <div x-show="catalogoOpen" x-transition x-cloak class="fixed inset-0 bg-black/50 z-[60] flex items-center justify-center p-4">
        <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-2xl max-w-6xl w-full overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700 flex items-start justify-between">
                <div>
                    <h3 class="text-xl font-bold text-slate-900 dark:text-white">Catálogo de productos</h3>
                    <p class="text-sm text-slate-500 dark:text-slate-400 mt-1">Selecciona uno o varios productos y agrégalos al detalle.</p>
                </div>
                <button type="button" @click="closeCatalogo()" class="p-2 hover:bg-gray-100 dark:hover:bg-gray-700 rounded-lg">
                    <svg class="w-6 h-6 text-slate-600 dark:text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>

            <div class="p-6">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 items-end">
                    <div>
                        <div class="relative">
                            <span class="absolute inset-y-0 left-3 flex items-center text-slate-400">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-4.35-4.35m0 0A7.5 7.5 0 1010.5 18.5a7.5 7.5 0 006.15-3.85z"/>
                                </svg>
                            </span>
                            <input type="text" x-model="catalogoBuscar" @input.debounce.150ms="filtrarCatalogo()"
                                   class="w-full pl-11 pr-4 py-2.5 bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-xl text-base text-slate-900 dark:text-white focus:ring-2 focus:ring-blue-500"
                                   placeholder="Escribe para buscar (nombre / descripción / código de barras)..."
                                   autocomplete="off">
                        </div>
                        <p class="text-xs text-slate-500 dark:text-slate-400 mt-1">
                            Tip: usa palabras clave (ej. "paracetamol", "jarabe", "7750...").
                        </p>
                        <div x-show="catalogoProductos.length === 0" class="mt-3 rounded-xl border border-amber-200 bg-amber-50 p-3 text-amber-800 dark:border-amber-800/40 dark:bg-amber-900/20 dark:text-amber-200">
                            <div class="font-bold">No se cargaron productos en el catálogo.</div>
                            <div class="text-xs mt-1">
                                Revisa que <span class="font-semibold">VentaController@create</span> esté enviando <span class="font-semibold">$productos</span> a la vista <span class="font-semibold">ventas.create</span> (como en compras).
                            </div>
                        </div>
                    </div>

                    <div class="md:text-right">
                        <div class="text-sm font-semibold text-slate-700 dark:text-slate-300">Seleccionados</div>
                        <div class="mt-1 inline-flex items-center gap-2 px-4 py-2 rounded-xl bg-gray-100 dark:bg-gray-700 text-slate-900 dark:text-white font-bold">
                            <span x-text="catalogoSeleccionados.size"></span>
                            <span class="text-sm font-semibold text-slate-600 dark:text-slate-300">producto(s)</span>
                        </div>
                    </div>
                </div>

                <div class="mt-4 grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-3 max-h-[68vh] overflow-auto pr-1">
                    <template x-for="p in catalogoFiltrados" :key="p.id">
                        <div class="rounded-xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 hover:shadow-md transition-shadow p-3">
                            <div class="flex items-start gap-3">
                                <input type="checkbox" class="mt-1 rounded border-gray-300 dark:border-gray-600"
                                       :checked="catalogoSeleccionados.has(p.id)"
                                       @change="toggleSeleccion(p.id)">
                                <div class="w-14 h-14 rounded-xl overflow-hidden bg-slate-100 dark:bg-gray-700 border border-slate-200 dark:border-gray-600 flex items-center justify-center shrink-0">
                                    <template x-if="p.imagen_url">
                                        <img :src="p.imagen_url" class="w-full h-full object-cover" alt="">
                                    </template>
                                    <template x-if="!p.imagen_url">
                                        <svg class="w-6 h-6 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 7h18M3 11h18M7 15h10M7 19h10"/>
                                        </svg>
                                    </template>
                                </div>

                                <div class="min-w-0 flex-1">
                                    <p class="font-semibold text-slate-900 dark:text-white leading-5 whitespace-normal break-words" x-text="p.nombre"></p>
                                    <p class="text-xs text-slate-500 dark:text-slate-400 whitespace-normal break-words" x-text="p.descripcion || '—'"></p>

                                    <div class="mt-2 flex flex-wrap gap-2 text-xs">
                                        <span class="inline-flex items-center px-2 py-1 rounded-full bg-slate-100 dark:bg-gray-700 text-slate-700 dark:text-slate-200"
                                              x-text="p.codigo_barra ? ('CB: ' + p.codigo_barra) : 'Sin código'"></span>
                                        <span class="inline-flex items-center px-2 py-1 rounded-full bg-emerald-100 dark:bg-emerald-900/30 text-emerald-700 dark:text-emerald-300"
                                              x-text="'Stock: ' + (p.stock_total ?? 0)"></span>
                                    </div>

                                    <div class="mt-2 flex items-center justify-between">
                                        <div class="text-xs text-slate-500 dark:text-slate-400">Precio</div>
                                        <div class="font-bold text-slate-900 dark:text-white" x-text="money(p.precio_venta || 0)"></div>
                                    </div>

                                    <div class="mt-3 flex items-center justify-end">
                                        <button type="button" @click="agregarProductoDesdeCatalogo(p)"
                                                class="inline-flex items-center px-3 py-1.5 rounded-lg bg-blue-600 hover:bg-blue-700 text-white text-sm font-semibold">
                                            Agregar
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </template>

                    <div class="text-sm text-slate-600 dark:text-slate-400" x-show="catalogoFiltrados.length === 0">
                        No hay resultados. Prueba otra búsqueda.
                    </div>
                </div>
            </div>

            <div class="px-6 py-4 border-t border-gray-200 dark:border-gray-700 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
                <button type="button" @click="limpiarSeleccion()"
                        class="inline-flex items-center justify-center px-4 py-2 bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-lg font-semibold text-slate-700 dark:text-slate-200 hover:bg-gray-50 dark:hover:bg-gray-600">
                    Limpiar selección
                </button>

                <div class="flex items-center justify-end gap-2">
                    <button type="button" @click="closeCatalogo()"
                            class="px-4 py-2 bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-lg font-semibold text-slate-700 dark:text-slate-200">
                        Cancelar
                    </button>
                    <button type="button" @click="agregarSeleccionados()"
                            class="inline-flex items-center justify-center px-5 py-2 bg-green-600 hover:bg-green-700 text-white rounded-lg font-semibold shadow-sm disabled:opacity-50 disabled:cursor-not-allowed"
                            :disabled="catalogoSeleccionados.size === 0">
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                        </svg>
                        Agregar seleccionados
                    </button>
                </div>
            </div>
        </div>
    </div>

    {{-- MODAL NUEVO CLIENTE --}}
    <div x-show="nuevoClienteOpen" x-transition x-cloak class="fixed inset-0 bg-black/50 z-[70] flex items-center justify-center p-4">
        <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-2xl max-w-xl w-full overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700 flex items-start justify-between">
                <div>
                    <h3 class="text-xl font-bold text-slate-900 dark:text-white">Nuevo Cliente</h3>
                    <p class="text-sm text-slate-500 dark:text-slate-400 mt-1">Registra el cliente y selecciónalo automáticamente.</p>
                </div>
                <button type="button" @click="closeNuevoCliente()" class="p-2 hover:bg-gray-100 dark:hover:bg-gray-700 rounded-lg">
                    <svg class="w-6 h-6 text-slate-600 dark:text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>

            <div class="p-6 space-y-4">
                <div x-show="clienteError" class="rounded-lg border border-red-200 bg-red-50 p-3 text-sm text-red-700" x-text="clienteError"></div>

                <div>
                    <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-1">Nombre</label>
                    <input type="text" x-model="nuevoCliente.nombre"
                           class="w-full px-3 py-2 bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-lg text-base text-slate-900 dark:text-white placeholder-slate-400 dark:placeholder-slate-400">
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-1">Tipo de documento</label>
                        <select x-model="nuevoCliente.tipo_documento"
                                class="w-full px-3 py-2 bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-lg text-base text-slate-900 dark:text-white">
                            <option value="Cedula">Cédula</option>
                            <option value="DNI">DNI</option>
                            <option value="Pasaporte">Pasaporte</option>
                            <option value="RUC">RUC</option>
                            <option value="Otro">Otro</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-1">Documento</label>
                        <input type="text" x-model="nuevoCliente.documento"
                               class="w-full px-3 py-2 bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-lg text-base text-slate-900 dark:text-white placeholder-slate-400 dark:placeholder-slate-400">
                    </div>
                    <div>
                        <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-1">Teléfono</label>
                        <input type="text" x-model="nuevoCliente.telefono"
                               class="w-full px-3 py-2 bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-lg text-base text-slate-900 dark:text-white placeholder-slate-400 dark:placeholder-slate-400">
                    </div>
                </div>

                <div>
                    <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-1">Dirección (opcional)</label>
                    <input type="text" x-model="nuevoCliente.direccion"
                           class="w-full px-3 py-2 bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-lg text-base text-slate-900 dark:text-white placeholder-slate-400 dark:placeholder-slate-400">
                </div>
            </div>

            <div class="px-6 py-4 border-t border-gray-200 dark:border-gray-700 flex items-center justify-end gap-2">
                <button type="button" @click="closeNuevoCliente()"
                        class="px-4 py-2 bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-lg font-semibold text-slate-700 dark:text-slate-200">
                    Cancelar
                </button>
                <button type="button" @click="guardarNuevoCliente()"
                        class="px-4 py-2 bg-green-600 hover:bg-green-700 text-white rounded-lg font-semibold">
                    Guardar
                </button>
            </div>
        </div>
    </div>

</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.2/Sortable.min.js"></script>
<script>
/** ================================
 *  Column resize + reorder (como compras create)
 *  ================================= */
function initResizableColumns() {
    const table = document.getElementById('tablaProductos');
    if (!table) return;
    const ths = table.querySelectorAll('th.th-resizable');
    ths.forEach(th => {
        const handle = th.querySelector('.col-resize-handle');
        if (!handle || handle.__bound) return;
        handle.__bound = true;

        let startX = 0;
        let startW = 0;

        const onDown = (e) => {
            e.preventDefault();
            e.stopPropagation();
            startX = (e.touches ? e.touches[0].clientX : e.clientX);
            startW = th.getBoundingClientRect().width;

            document.addEventListener('mousemove', onMove);
            document.addEventListener('mouseup', onUp);
            document.addEventListener('touchmove', onMove, { passive: false });
            document.addEventListener('touchend', onUp);
        };

        const onMove = (e) => {
            e.preventDefault();
            const x = (e.touches ? e.touches[0].clientX : e.clientX);
            const dx = x - startX;
            const minW = parseInt(th.dataset.minw || '90', 10);
            const newW = Math.max(minW, startW + dx);
            th.style.width = newW + 'px';

            const col = th.dataset.col;
            if (col) {
                table.querySelectorAll(`tbody td[data-col="${col}"]`).forEach(td => td.style.width = newW + 'px');
            }
        };

        const onUp = () => {
            document.removeEventListener('mousemove', onMove);
            document.removeEventListener('mouseup', onUp);
            document.removeEventListener('touchmove', onMove);
            document.removeEventListener('touchend', onUp);
        };

        handle.addEventListener('mousedown', onDown);
        handle.addEventListener('touchstart', onDown, { passive: false });
    });
}

function initReorderableColumns() {
    const table = document.getElementById('tablaProductos');
    const headerRow = document.getElementById('theadRowProductos');
    if (!table || !headerRow) return;
    if (typeof Sortable === 'undefined') return;

    const applyColumnOrder = () => {
        const order = Array.from(headerRow.querySelectorAll('th[data-col]')).map(th => th.dataset.col);

        table.querySelectorAll('tbody tr').forEach(tr => {
            const fixed = Array.from(tr.children).filter(td => !td.dataset.col); // ej: [drag, acciones]
            const byCol = {};
            tr.querySelectorAll('td[data-col]').forEach(td => { byCol[td.dataset.col] = td; });

            tr.innerHTML = '';
            if (fixed[0]) tr.appendChild(fixed[0]);
            order.forEach(col => { if (byCol[col]) tr.appendChild(byCol[col]); });
            if (fixed.length > 1) tr.appendChild(fixed[fixed.length - 1]);
        });
    };

    // Si ya existe, solo aplica el orden actual (útil al agregar filas nuevas)
    if (headerRow.__sortableCols) {
        applyColumnOrder();
        return;
    }

    headerRow.__sortableCols = new Sortable(headerRow, {
        animation: 150,
        draggable: 'th[data-col]',
        filter: '.col-resize-handle',
        preventOnFilter: false,
        onEnd: () => applyColumnOrder()
    });

    applyColumnOrder();
}
</script>
<script>

function ventaPOS() {
    return {
        STORAGE_KEY: (window.__VENTA_EDIT && window.__VENTA_EDIT.id) ? `ventas_pos_edit_${window.__VENTA_EDIT.id}` : 'ventas_pos_draft_v2_fullwidth',

        isEdit: !!(window.__VENTA_EDIT && window.__VENTA_EDIT.id),
        ventaEdit: (window.__VENTA_EDIT || null),
        motivo: '',
        motivoError: false,


        // Estado principal
        vista: localStorage.getItem('ventas_pos_vista') || 'tabla',
        barcode: '',
        items: [],

        // Datos venta
        fecha: '',
        cliente_id: '',
        observaciones: '',

        // Pago
        metodo_pago: 'efectivo',
        banco: '',
        referencia: '',
        monto_recibido: null,
        cambio: 0,

        // Descuentos
        descuento_global_pct: 0,

        // UI
        accion: 'guardar',
        mantener_en_pantalla: false,

        // Validaciones UI
        efectivoInsuficiente: false,
        bancoRequiredError: false,
        referenciaRequiredError: false,
        clienteRequiredError: false,

        // Barcode preview
        barcodePreview: {
            show: false,
            imagen_url: null,
            nombre: '',
            extra: '',
            badge: 'Esperando…',
            badgeClass: 'bg-slate-200 text-slate-700 dark:bg-gray-700 dark:text-slate-200'
        },

        // Clientes (para que el modal agregue y aparezca al instante)
        clientes: [],

        // Catálogo
        catalogoOpen: false,
        catalogoProductos: [],
        catalogoFiltrados: [],
        catalogoBuscar: '',
        catalogoSeleccionados: new Set(),

        // Nuevo cliente
        nuevoClienteOpen: false,
        nuevoCliente: { nombre: '', tipo_documento: 'Cedula', documento: '', telefono: '', direccion: '' },
        clienteError: '',

        /* ======= Computados ======= */
        get subtotal_bruto() {
            return this.items.reduce((a, it) => a + Number(it.subtotal_bruto || 0), 0);
        },
        get descuento_productos() {
            return this.items.reduce((a, it) => a + Number(it.descuento_monto || 0), 0);
        },
        get descuento_global_monto() {
            const base = Math.max(0, this.subtotal_bruto - this.descuento_productos);
            const pct = Math.max(0, Number(this.descuento_global_pct || 0));
            return base * (pct / 100);
        },
        get total_neto() {
            return Math.max(0, (this.subtotal_bruto - this.descuento_productos - this.descuento_global_monto));
        },
        get needsBanco() {
            return ['transferencia', 'credito', 'debito'].includes(this.metodo_pago);
        },
        get needsReferencia() {
            return ['transferencia', 'credito', 'debito', 'otros'].includes(this.metodo_pago);
        },
        get submitDisabled() {
            if (this.items.length === 0) return true;
            if (this.metodo_pago === 'efectivo' && this.efectivoInsuficiente) return true;
            if (this.needsBanco && !this.banco) return true;
            if (this.needsReferencia && !String(this.referencia || '').trim()) return true;
            if (this.metodo_pago === 'pagar_luego' && !this.cliente_id) return true;
            return false;
        },

        /* ======= Init ======= */
        init() {
            this.catalogoProductos = (window.__VENTAS_CATALOGO || []);
            this.catalogoFiltrados = this.catalogoProductos;

            this.clientes = (window.__VENTAS_CLIENTES || []);

            if (this.isEdit && this.ventaEdit && this.ventaEdit.cliente_id) {
        const cid = String(this.ventaEdit.cliente_id);
        const exists = this.clientes.some(c => String(c.id) === cid);
             if (!exists) {
                 const nombre = this.ventaEdit.cliente_nombre
                 ? `${this.ventaEdit.cliente_nombre} (inactivo)`
                     : `Cliente #${cid} (inactivo)`;
                        this.clientes.unshift({ id: Number(cid), nombre });
              }
            }
            // Precarga (editar): aplicar datos de venta original ANTES de restaurar draft
            if (this.isEdit && this.ventaEdit) {
                this.applyVentaServer(this.ventaEdit);
            }

            // Persistencia (draft): si existe, sobreescribe lo precargado
            this.restoreFromStorage();
// FIX: si hay draft en localStorage que dejó cliente vacío, NO lo dejamos pisar
// el cliente real de la venta original.
if (this.isEdit && this.ventaEdit) {
    const originalCliente = this.ventaEdit.cliente_id ? String(this.ventaEdit.cliente_id) : '';
    if (!this.cliente_id && originalCliente) {
        this.cliente_id = originalCliente;
    }
}


            // Asegurar método de pago inicial (evita bug visual de select)
            if (!this.metodo_pago) this.metodo_pago = 'efectivo';
            this.onChangeMetodoPago(true);

            // Botón global "Revertir cambios" desde header
            window.addEventListener('pos-revertir-venta', () => {
                if (this.isEdit && this.ventaEdit) {
                    try { localStorage.removeItem(this.STORAGE_KEY); } catch(e) {}
                    this.applyVentaServer(this.ventaEdit, true);
                    return;
                }
                this.resetVenta(true);
            });


            if (!this.fecha) {
                const now = new Date();
                this.fecha = this.toDatetimeLocal(now);
            }

            // Watchers (autosave)
            this.$watch('items', () => this.saveToStorage(), { deep: true });
            this.$watch('cliente_id', () => this.saveToStorage());
            this.$watch('observaciones', () => this.saveToStorage());
            this.$watch('metodo_pago', () => this.saveToStorage());
            this.$watch('banco', () => this.saveToStorage());
            this.$watch('referencia', () => this.saveToStorage());
            this.$watch('monto_recibido', () => this.saveToStorage());
            this.$watch('descuento_global_pct', () => this.saveToStorage());
            this.$watch('accion', () => this.saveToStorage());
            this.$watch('mantener_en_pantalla', () => this.saveToStorage());
            this.$watch('fecha', () => this.saveToStorage());
            this.$watch('vista', () => { localStorage.setItem('ventas_pos_vista', this.vista); this.saveToStorage(); });

            // Hotkeys
            window.addEventListener('keydown', (e) => this.handleHotkeys(e));

            this.$nextTick(() => { this.$refs.barcode?.focus(); if (this.vista === 'tabla') { initResizableColumns(); initReorderableColumns(); } });
        },

        /* ======= Hotkeys ======= */
        handleHotkeys(e) {
            const tag = (e.target && e.target.tagName) ? e.target.tagName.toLowerCase() : '';
            const typing = ['input', 'textarea', 'select'].includes(tag);

            if (e.key === 'F6') { e.preventDefault(); this.$nextTick(() => this.$refs.barcode?.focus()); return; }
            if (e.key === 'F7') { e.preventDefault(); this.openCatalogo(); return; }
            if (e.key === 'F8') { e.preventDefault(); this.$nextTick(() => this.$refs.metodoPago?.focus()); return; }
            if (e.key === 'F9') { e.preventDefault(); this.openNuevoCliente(); return; }
            if (e.key === 'F10') { e.preventDefault(); if (!this.submitDisabled) this.submitVenta(); return; }

            if ((e.ctrlKey || e.metaKey) && e.key === 'Enter') {
                e.preventDefault();
                if (!this.submitDisabled) this.submitVenta();
                return;
            }

            if (e.key === 'Escape') {
                if (this.nuevoClienteOpen) { this.closeNuevoCliente(); return; }
                if (this.catalogoOpen) { this.closeCatalogo(); return; }
                if (!typing) { this.barcode = ''; this.updateBarcodePreview(true); this.$nextTick(() => { this.$refs.barcode?.focus(); if (this.vista === 'tabla') { initResizableColumns(); initReorderableColumns(); } }); }
            }
        },

        /* ======= UI helpers ======= */
        money(v) {
            const n = Number(v || 0);
            return n.toLocaleString(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 2 });
        },
        toDatetimeLocal(d) {
            const pad = (n) => String(n).padStart(2, '0');
            return `${d.getFullYear()}-${pad(d.getMonth()+1)}-${pad(d.getDate())}T${pad(d.getHours())}:${pad(d.getMinutes())}`;
        },
        prettyFecha(value) {
            try { return new Date(value).toLocaleString(); } catch { return value; }
        },
        priceLabel(it) {
            if (!it.presentacion_id) return 'Precio (por unidad)';
            return `Precio (por ${it.tipo_presentacion || 'presentación'})`;
        },

        /* ======= Catálogo ======= */
        openCatalogo() {
            this.catalogoOpen = true;
            this.catalogoBuscar = '';
            this.catalogoFiltrados = this.catalogoProductos;
            this.$nextTick(() => {});
        },
        closeCatalogo() {
            this.catalogoOpen = false;
        },
        filtrarCatalogo() {
            const q = String(this.catalogoBuscar || '').trim().toLowerCase();
            if (!q) {
                this.catalogoFiltrados = this.catalogoProductos;
                return;
            }
            this.catalogoFiltrados = this.catalogoProductos.filter(p => {
                const nombre = (p.nombre || '').toLowerCase();
                const desc = (p.descripcion || '').toLowerCase();
                const cb = (p.codigo_barra || '').toLowerCase();
                return nombre.includes(q) || desc.includes(q) || cb.includes(q);
            });
        },
        toggleSeleccion(id) {
            if (this.catalogoSeleccionados.has(id)) this.catalogoSeleccionados.delete(id);
            else this.catalogoSeleccionados.add(id);
        },
        limpiarSeleccion() {
            this.catalogoSeleccionados = new Set();
        },
        agregarProductoDesdeCatalogo(p) {
            this.addProducto(p);
            this.$nextTick(() => { this.$refs.barcode?.focus(); if (this.vista === 'tabla') { initResizableColumns(); initReorderableColumns(); } });
        },
        agregarSeleccionados() {
            const ids = Array.from(this.catalogoSeleccionados);
            ids.forEach(id => {
                const p = this.catalogoProductos.find(x => x.id === id);
                if (p) this.addProducto(p);
            });
            this.limpiarSeleccion();
            this.closeCatalogo();
            this.$nextTick(() => { this.$refs.barcode?.focus(); if (this.vista === 'tabla') { initResizableColumns(); initReorderableColumns(); } });
        },

        /* ======= Barcode ======= */
        updateBarcodePreview(forceHide = false) {
            const code = String(this.barcode || '').trim();
            if (forceHide || !code) {
                this.barcodePreview.show = false;
                this.barcodePreview.badge = 'Esperando…';
                this.barcodePreview.badgeClass = 'bg-slate-200 text-slate-700 dark:bg-gray-700 dark:text-slate-200';
                return;
            }

            const p = this.catalogoProductos.find(x => String(x.codigo_barra || '').trim() === code);
            this.barcodePreview.show = true;

            if (!p) {
                this.barcodePreview.imagen_url = null;
                this.barcodePreview.nombre = 'No encontrado';
                this.barcodePreview.extra = 'Revisa el código o usa Catálogo.';
                this.barcodePreview.badge = 'Sin match';
                this.barcodePreview.badgeClass = 'bg-red-100 text-red-700 dark:bg-red-900/30 dark:text-red-300';
                return;
            }

            this.barcodePreview.imagen_url = p.imagen_url || null;
            this.barcodePreview.nombre = p.nombre || 'Producto';
            this.barcodePreview.extra = `Stock: ${p.stock_total ?? 0} • Precio: ${this.money(p.precio_venta || 0)}`;
            if ((p.stock_total ?? 0) <= 0) {
                this.barcodePreview.badge = 'Sin stock';
                this.barcodePreview.badgeClass = 'bg-amber-100 text-amber-700 dark:bg-amber-900/30 dark:text-amber-300';
            } else {
                this.barcodePreview.badge = 'Listo';
                this.barcodePreview.badgeClass = 'bg-emerald-100 text-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-300';
            }
        },
        agregarPorBarcode() {
            const code = String(this.barcode || '').trim();
            if (!code) return;

            const p = this.catalogoProductos.find(x => String(x.codigo_barra || '').trim() === code);
            if (!p) {
                this.updateBarcodePreview();
                return;
            }
            if ((p.stock_total ?? 0) <= 0) {
                this.updateBarcodePreview();
                return;
            }

            this.addProducto(p);
            this.barcode = '';
            this.updateBarcodePreview(true);
            this.$nextTick(() => { this.$refs.barcode?.focus(); if (this.vista === 'tabla') { initResizableColumns(); initReorderableColumns(); } });
        },

        /* ======= Línea / Item ======= */
        addProducto(p) {
            // Agrega una nueva línea al detalle (permite repetir producto)
            const firstLote = (p.lotes && p.lotes.length) ? p.lotes[0] : null;
            const presList = Array.isArray(p.presentaciones) ? p.presentaciones : [{
                id: null, nombre: 'Unidad', descripcion: 'Unidad base', unidades_por_presentacion: 1, precio_sugerido: null, nombre_completo: 'Unidad', select_label: 'Unidad'
            }];
            const presDefault = presList[0]; // Unidad
            const presId = presDefault.id;
            // Permitir agregar el mismo producto varias veces (no auto-fusionar filas)

            const unidades = Number(presDefault.unidades_por_presentacion || 1);
            let precioDisplay = Number(p.precio_venta || 0);
            if (presId) {
                const sug = Number(presDefault.precio_sugerido || 0);
                precioDisplay = sug > 0 ? sug : (Number(p.precio_venta || 0) * unidades);
            }
            const precioUnit = unidades > 0 ? (precioDisplay / unidades) : precioDisplay;

            const item = {
                key: `${Date.now()}-${Math.random().toString(16).slice(2)}`,
                producto_id: p.id,
                nombre: p.nombre,
                codigo_barra: p.codigo_barra || '',
                imagen_url: p.imagen_url || null,

                lotes: (p.lotes || []),
                lote_id: firstLote ? firstLote.id : null,
                lote: firstLote,

                presentaciones: presList,
                presentacion_id: presId,
                tipo_presentacion: presDefault.nombre || 'Unidad',
                unidades_por_presentacion: unidades,

                cantidad_presentaciones: 1,
                cantidad_unidades_base: unidades,

                precio_display: precioDisplay,
                precio_unitario: precioUnit,

                descuento_porcentaje: 0,
                descuento_monto: 0,

                subtotal_bruto: precioUnit * unidades,
                subtotal: precioUnit * unidades,
            };

            this.items.push(item);
            this.recalcItem(item);
        },
        removeItem(key) {
            this.items = this.items.filter(it => it.key !== key);
        },
        onChangePresentacion(it) {
            const pres = it.presentaciones.find(p => String(p.id ?? '') === String(it.presentacion_id ?? ''));
            const unidades = Number(pres?.unidades_por_presentacion || 1);
            it.tipo_presentacion = pres?.nombre || 'Unidad';
            it.unidades_por_presentacion = unidades;

            // Ajustar precio_display al sugerido (si existe) para evitar confusión
            if (it.presentacion_id) {
                const sug = Number(pres?.precio_sugerido || 0);
                it.precio_display = sug > 0 ? sug : (Number(it.precio_unitario || 0) * unidades);
            } else {
                it.precio_display = Number(it.precio_unitario || 0); // unidad
            }
            this.onChangePrecio(it);
        },
        onChangeLote(it) {
            const l = it.lotes.find(x => String(x.id) === String(it.lote_id));
            it.lote = l || null;
            this.recalcItem(it);
        },
        onChangePrecio(it) {
            const unidades = Number(it.unidades_por_presentacion || 1);
            const display = Math.max(0, Number(it.precio_display || 0));
            it.precio_unitario = unidades > 0 ? (display / unidades) : display;
            this.recalcItem(it);
        },
        onChangeDescuento(it) {
            const pct = Math.max(0, Number(it.descuento_porcentaje || 0));
            it.descuento_porcentaje = pct;
            this.recalcItem(it);
        },
        recalcItem(it) {
            const cantPres = Math.max(1, parseInt(it.cantidad_presentaciones || 1, 10));
            it.cantidad_presentaciones = cantPres;

            const unidades = Math.max(1, parseInt(it.unidades_por_presentacion || 1, 10));
            it.cantidad_unidades_base = cantPres * unidades;

            // Ajuste por stock del lote (en edición: permitir qty original si mantiene el mismo lote)
            if (it.lote) {
                const stock = Number(it.lote.stock_actual ?? 0);
                const addBack = (this.isEdit && it.orig_lote_id && it.lote_id && String(it.orig_lote_id) === String(it.lote_id))
                    ? Number(it.orig_unidades_base || 0)
                    : 0;
                const maxBase = Math.max(0, (isNaN(stock) ? 0 : stock) + (isNaN(addBack) ? 0 : addBack));

                if (it.cantidad_unidades_base > maxBase) {
                    it.cantidad_unidades_base = maxBase;
                    it.cantidad_presentaciones = Math.max(1, Math.floor(maxBase / unidades) || 1);
                }
            }

            const bruto = (Number(it.precio_unitario || 0) * Number(it.cantidad_unidades_base || 0));
            it.subtotal_bruto = bruto;

            const descMonto = bruto * (Math.max(0, Number(it.descuento_porcentaje || 0)) / 100);
            it.descuento_monto = descMonto;

            it.subtotal = Math.max(0, bruto - descMonto);

            this.recalcTotales();
            this.saveToStorage();
        },
        recalcTotales() {
            // Solo triggers getters
            this.recalcCambio();
            this.saveToStorage();
        },

        /* ======= Pago ======= */
        onChangeMetodoPago(isInit = false) {
            this.bancoRequiredError = false;
            this.referenciaRequiredError = false;
            this.clienteRequiredError = false;

            // Reset banco/referencia al cambiar (en init no se borra lo restaurado)
            if (!isInit) {
                this.banco = '';
                this.referencia = '';
            }

            if (this.metodo_pago !== 'efectivo') {
                this.monto_recibido = null;
                this.cambio = 0;
                this.efectivoInsuficiente = false;
            } else {
                this.recalcCambio();
            }

            if (this.metodo_pago === 'pagar_luego') {
                this.clienteRequiredError = !this.cliente_id;
            }

            this.saveToStorage();
        },
        setRecibidoExacto() {
            this.monto_recibido = Number(this.total_neto || 0);
            this.recalcCambio();
        },
        clearRecibido() {
            this.monto_recibido = null;
            this.cambio = 0;
            this.efectivoInsuficiente = false;
        },
        sumDenominacion(d) {
            const cur = Number(this.monto_recibido || 0);
            this.monto_recibido = cur + Number(d);
            this.recalcCambio();
        },
        recalcCambio() {
            if (this.metodo_pago !== 'efectivo') {
                this.cambio = 0;
                this.efectivoInsuficiente = false;
                return;
            }
            const recibido = Number(this.monto_recibido || 0);
            const total = Number(this.total_neto || 0);
            const diff = recibido - total;

            this.efectivoInsuficiente = diff < 0;
            this.cambio = diff > 0 ? diff : 0;
        },

        /* ======= Persistencia ======= */
        saveToStorage() {
            try {
                const payload = {
                    fecha: this.fecha,
                motivo: this.motivo,
                    cliente_id: this.cliente_id,
                    observaciones: this.observaciones,
                    metodo_pago: this.metodo_pago,
                    banco: this.banco,
                    referencia: this.referencia,
                    monto_recibido: this.monto_recibido,
                    descuento_global_pct: this.descuento_global_pct,
                    accion: this.accion,
                    mantener_en_pantalla: this.mantener_en_pantalla,
                    vista: this.vista,
                    items: this.items.map(it => ({
                        key: it.key,
                        producto_id: it.producto_id,
                        nombre: it.nombre,
                        codigo_barra: it.codigo_barra,
                        imagen_url: it.imagen_url,

                        lotes: it.lotes || [],
                        lote_id: it.lote_id,
                        lote: it.lote || null,

                        presentaciones: it.presentaciones || [],
                        presentacion_id: it.presentacion_id,
                        tipo_presentacion: it.tipo_presentacion,
                        unidades_por_presentacion: it.unidades_por_presentacion,

                        cantidad_presentaciones: it.cantidad_presentaciones,
                        cantidad_unidades_base: it.cantidad_unidades_base,

                        precio_display: it.precio_display,
                        precio_unitario: it.precio_unitario,

                        descuento_porcentaje: it.descuento_porcentaje,
                        descuento_monto: it.descuento_monto,

                        subtotal_bruto: it.subtotal_bruto,
                        subtotal: it.subtotal,
                    }))
                };
                localStorage.setItem(this.STORAGE_KEY, JSON.stringify(payload));
            } catch (e) {}
        },
        restoreFromStorage() {
            try {
                const raw = localStorage.getItem(this.STORAGE_KEY);
                if (!raw) return;
                const data = JSON.parse(raw);

                this.fecha = data.fecha || '';
                this.cliente_id = data.cliente_id || '';
                this.observaciones = data.observaciones || '';

                this.metodo_pago = data.metodo_pago || 'efectivo';
                this.banco = data.banco || '';
                this.referencia = data.referencia || '';
                this.monto_recibido = data.monto_recibido ?? null;

                this.descuento_global_pct = data.descuento_global_pct ?? 0;
                this.accion = data.accion || 'guardar';
                this.mantener_en_pantalla = (data.mantener_en_pantalla === true || data.mantener_en_pantalla === 1);

                this.vista = data.vista || this.vista;

                if (Array.isArray(data.items)) {
                    this.items = data.items.map(it => this.hydrateItem(it));
                    this.items.forEach(it => this.recalcItem(it));
                }

                // Recalc pago
                this.recalcCambio();
            } catch (e) {}
        },
        hydrateItem(it) {
            const key = it.key || `${Date.now()}-${Math.random().toString(16).slice(2)}`;
            return {
                key,
                producto_id: it.producto_id,
                nombre: it.nombre || 'Producto',
                codigo_barra: it.codigo_barra || '',
                imagen_url: it.imagen_url || null,

                lotes: Array.isArray(it.lotes) ? it.lotes : [],
                lote_id: it.lote_id,
                lote: it.lote || null,

                presentaciones: Array.isArray(it.presentaciones) ? it.presentaciones : [],
                presentacion_id: it.presentacion_id || null,
                tipo_presentacion: it.tipo_presentacion || 'Unidad',
                unidades_por_presentacion: Number(it.unidades_por_presentacion || 1),

                cantidad_presentaciones: Number(it.cantidad_presentaciones || 1),
                cantidad_unidades_base: Number(it.cantidad_unidades_base || 1),

                precio_display: Number(it.precio_display || 0),
                precio_unitario: Number(it.precio_unitario || 0),

                descuento_porcentaje: Number(it.descuento_porcentaje || 0),
                descuento_monto: Number(it.descuento_monto || 0),

                subtotal_bruto: Number(it.subtotal_bruto || 0),
                subtotal: Number(it.subtotal || 0),
            };
        },

        /* ======= Acciones ======= */

        applyVentaServer(v, forceUIRefresh = false) {
            const vv = v || {};

            // Resetea el estado a partir de la venta original (server)
            this.items = [];
            this.barcode = '';
            this.updateBarcodePreview(true);

            this.observaciones = vv.observaciones || '';
            this.descuento_global_pct = Number(vv.descuento_porcentaje || 0);

            this.metodo_pago = (vv.metodo_pago || 'efectivo');
            this.banco = vv.banco || '';
            this.referencia = vv.referencia_pago || '';
            this.monto_recibido = (vv.monto_recibido !== undefined ? vv.monto_recibido : null);
            this.cambio = Number(vv.cambio || 0);

            this.cliente_id = vv.cliente_id ? String(vv.cliente_id) : '';

            // Fecha (datetime-local)
            this.fecha = vv.fecha_input || this.fecha || '';

            // Motivo (se requiere para guardar cambios)
            this.motivo = '';
            this.motivoError = false;

            // Detalles precargados
            const detalles = Array.isArray(vv.detalles) ? vv.detalles : [];

            detalles.forEach(d => {
                const pid = Number(d.producto_id);

                // Buscar en catálogo para reutilizar presentaciones/lotes/imágenes
                const pcat = this.catalogoProductos.find(x => Number(x.id) === pid) || null;

                const p = pcat || {
                    id: pid,
                    nombre: `Producto #${pid}`,
                    codigo_barra: '',
                    imagen_url: null,
                    precio_venta: Number(d.precio_unitario || 0),
                    presentaciones: [],
                    lotes: [],
                };

                // Lotes: asegurar que el lote de la venta exista en el selector aunque hoy esté sin stock
                const lotes = (p.lotes || []).slice();
                const loteId = d.lote_id ? Number(d.lote_id) : null;

                if (loteId && !lotes.some(l => Number(l.id) === loteId)) {
                    lotes.push({
                        id: loteId,
                        numero_lote: d.lote_numero || `Lote #${loteId}`,
                        fecha_vencimiento: d.lote_venc || null,
                        fecha_vencimiento_fmt: d.lote_venc_fmt || null,
                        stock_total: 0,
                        stock_actual: 0,
                    });
                }

                const loteSel = loteId ? (lotes.find(l => Number(l.id) === loteId) || null) : null;

                // Presentaciones: usar catálogo (ya incluye Unidad) o fallback; asegurar presentación de la venta si no existe
                let presList = Array.isArray(p.presentaciones) && p.presentaciones.length
                    ? (p.presentaciones.map(pr => ({ ...pr })))
                    : [{ id: null, nombre: 'Unidad', unidades_por_presentacion: 1, precio_sugerido: Number(p.precio_venta || 0) }];

                // Asegurar que 'Unidad' esté al inicio
                presList = presList.sort((a, b) => (a.id === null ? -1 : 1) - (b.id === null ? -1 : 1));


                const presId = d.presentacion_id ? Number(d.presentacion_id) : null;

                if (presId && !presList.some(pr => Number(pr.id) === presId)) {
                    presList.push({
                        id: presId,
                        nombre: d.tipo_presentacion || 'Presentación',
                        unidades_por_presentacion: Number(d.unidades_por_presentacion || 1),
                        precio_sugerido: null,
                    });
                }

                const presSel = presId ? (presList.find(pr => String(pr.id) === String(presId)) || presList[0]) : presList[0];
                const unidades = Number((presSel && presSel.unidades_por_presentacion) ? presSel.unidades_por_presentacion : (d.unidades_por_presentacion || 1));

                const precioUnit = Number(d.precio_unitario || 0);
                const precioDisplay = presId ? (precioUnit * unidades) : precioUnit;

                const baseQty = Number((d.cantidad_unidades_base ?? d.cantidad) || 1);
                const qty = Number(presId ? (d.cantidad_presentaciones || 1) : baseQty);

                const item = {
                    key: `${Date.now()}-${Math.random().toString(16).slice(2)}`,
                    producto_id: pid,
                    nombre: p.nombre,
                    codigo_barra: p.codigo_barra || '',
                    imagen_url: p.imagen_url || null,

                    lotes,
                    lote_id: loteId ? String(loteId) : '',
                    lote: loteSel,

                    // Para edición: stock efectivo = stock_actual + qty original (si mantiene el mismo lote)
                    orig_lote_id: loteId,
                    orig_unidades_base: baseQty,

                    presentaciones: presList,
                    presentacion_id: presId ? String(presId) : '',
                    tipo_presentacion: (presSel && presSel.nombre) ? presSel.nombre : 'Unidad',
                    unidades_por_presentacion: unidades,

                    cantidad_presentaciones: qty,
                    cantidad_unidades_base: qty * unidades,

                    precio_display: precioDisplay,
                    precio_unitario: precioUnit,

                    descuento_porcentaje: Number(d.descuento_porcentaje || 0),
                    descuento_monto: 0,

                    subtotal_bruto: 0,
                    subtotal: 0,
                };

                this.items.push(item);
                this.recalcItem(item);
            });

            // Asegurar flags de UI del método de pago
            if (!this.metodo_pago) this.metodo_pago = 'efectivo';
            this.onChangeMetodoPago(true);

            if (forceUIRefresh) {
                this.$nextTick(() => {
                    if (this.vista === 'tabla') { initResizableColumns(); initReorderableColumns(); }
                    this.$refs.barcode?.focus();
                });
            }
        },

        resetVenta(clearStorage = false) {
            this.items = [];
            this.barcode = '';
            this.updateBarcodePreview(true);

            this.observaciones = '';
            this.descuento_global_pct = 0;

            this.motivo = '';
            this.motivoError = false;

            this.metodo_pago = 'efectivo';
            this.onChangeMetodoPago(true);
            this.banco = '';
            this.referencia = '';
            this.monto_recibido = null;
            this.cambio = 0;

            this.cliente_id = '';
            this.fecha = this.toDatetimeLocal(new Date());

            this.efectivoInsuficiente = false;
            this.bancoRequiredError = false;
            this.referenciaRequiredError = false;
            this.clienteRequiredError = false;

            if (clearStorage) {
                try { localStorage.removeItem(this.STORAGE_KEY); } catch(e) {}
            } else {
                this.saveToStorage();
            }

            this.$nextTick(() => { this.$refs.barcode?.focus(); if (this.vista === 'tabla') { initResizableColumns(); initReorderableColumns(); } });
        },
        cancelar() {
            if (!confirm('¿Cancelar la venta y salir?')) return;
            try { localStorage.removeItem(this.STORAGE_KEY); } catch(e) {}
            window.location.href = `{{ route('ventas.show', $venta) }}`;
        },

        async submitVenta() {
            // Validaciones UI obligatorias
            this.bancoRequiredError = this.needsBanco && !this.banco;
            this.referenciaRequiredError = this.needsReferencia && !String(this.referencia || '').trim();
            this.clienteRequiredError = this.metodo_pago === 'pagar_luego' && !this.cliente_id;

            if (this.submitDisabled) return;

            // ✅ En edición: motivo obligatorio (mín. 10 caracteres)
            if (this.isEdit) {
                const m = String(this.motivo || '').trim();
                this.motivoError = (m.length < 10);
                if (this.motivoError) {
                    alert('Debes indicar un motivo de modificación (mínimo 10 caracteres).');
                    return;
                }
            }

            const payload = {
                fecha: this.fecha,
                motivo: this.motivo,
                cliente_id: this.cliente_id || null,
                observaciones: this.observaciones || null,
                metodo_pago: this.metodo_pago,
                banco: this.needsBanco ? this.banco : null,
                referencia_pago: this.needsReferencia ? this.referencia : null,
                monto_recibido: this.metodo_pago === 'efectivo' ? Number(this.monto_recibido || 0) : null,
                cambio: this.metodo_pago === 'efectivo' ? Number(this.cambio || 0) : null,
                descuento: Number(this.descuento_global_pct || 0),
                productos: this.items.map(it => {
                    const presentacionId = it.presentacion_id ? Number(it.presentacion_id) : null;

                    const obj = {
                        producto_id: Number(it.producto_id),
                        lote_id: it.lote_id ? Number(it.lote_id) : null,
                        presentacion_id: presentacionId,
                        precio_unitario: Number(it.precio_unitario || 0),
                        descuento: Number(it.descuento_porcentaje || 0),
                    };

                    // ✅ Reglas del backend:
                    // - Si hay presentación => cantidad_presentaciones
                    // - Si NO hay presentación (unidad) => cantidad
                    const qty = Number(it.cantidad_presentaciones || 1);

                    if (presentacionId) {
                        obj.cantidad_presentaciones = qty;
                    } else {
                        obj.cantidad = qty;
                    }

                    return obj;
                }),
            };

            const res = await fetch(`{{ route('ventas.update', $venta) }}`, {
                method: 'PUT',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': `{{ csrf_token() }}`,
                },
                body: JSON.stringify(payload),
            });

            if (!res.ok) {
                let msg = 'Error al modificar la venta.';
                try {
                    const data = await res.json();
                    msg = data.message || msg;
                } catch (e) {}
                alert(msg);
                return;
            }

            const data = await res.json();

            // Controller devuelve { success, message, redirect } (y a veces id)
            const redirect = data.redirect || data.redirect_url || data.url || null;
            const id = data.id || data.venta_id || data.venta?.id || (redirect ? String(redirect).split('/').filter(Boolean).slice(-1)[0] : null);

            // Limpiar storage
            try { localStorage.removeItem(this.STORAGE_KEY); } catch(e) {}

            const keepHere = (this.mantener_en_pantalla === true);

            if (keepHere) {
                // En edición, "mantenerme aquí" significa volver al POS (nueva venta)
                window.location.href = `{{ route('ventas.create') }}`;
                return;
            }

            // Preferir redirect del backend (ventas.show)
            if (redirect) {
                const base = String(redirect).replace(/\/+$/, '');
                if (this.accion === 'facturar') {
                    window.location.href = base + '/imprimir';
                } else {
                    window.location.href = base;
                }
                return;
            }

            if (!id) {
                // Si backend no devuelve id/redirect, recargar
                window.location.reload();
                return;
            }

            if (this.accion === 'facturar') {
                window.location.href = `{{ url('/ventas') }}/${id}/imprimir`;
            } else {
                window.location.href = `{{ url('/ventas') }}/${id}`;
            }
        },

        setVista(tipo) {
            this.vista = tipo;
            this.$nextTick(() => {
                if (this.vista === 'tabla') initResizableColumns();
                initReorderableColumns();
            });
        },

        /* ======= Cliente ======= */
        openNuevoCliente() {
            this.nuevoClienteOpen = true;
            this.clienteError = '';
            this.$nextTick(() => {});
        },
        closeNuevoCliente() {
            this.nuevoClienteOpen = false;
        },
        async guardarNuevoCliente() {
            this.clienteError = '';
            const payload = { ...this.nuevoCliente };

            const res = await fetch(`{{ route('clientes.store') }}`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': `{{ csrf_token() }}`,
                },
                body: JSON.stringify(payload),
            });

            if (!res.ok) {
                let msg = 'Error al crear el cliente.';
                try {
                    const data = await res.json();
                    msg = data.message || msg;
                } catch (e) {}
                this.clienteError = msg;
                return;
            }

            const data = await res.json();
            const id = data.id || data.cliente?.id;

            if (id) {
                // Agregar al combo inmediatamente (sin recargar)
                const nombre = data.nombre || data.cliente?.nombre || payload.nombre || 'Cliente';
                const existe = this.clientes.some(c => String(c.id) === String(id));
                if (!existe) {
                    this.clientes.push({ id, nombre });
                    // Ordenar alfabéticamente como en compras
                    this.clientes.sort((a,b) => String(a.nombre).localeCompare(String(b.nombre)));
                }
                this.cliente_id = String(id);
                this.saveToStorage();
            }

            this.nuevoCliente = { nombre: '', tipo_documento: 'Cedula', documento: '', telefono: '', direccion: '' };
            this.closeNuevoCliente();
        }
    }
}
</script>
@endpush