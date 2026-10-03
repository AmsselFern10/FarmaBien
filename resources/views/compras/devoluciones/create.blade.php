@extends('layouts.app')
@section('title', 'Nueva Devolución a Proveedor - FarmaBien')
@section('content')
<div x-data="{
    items: [],
    lotesCatalogo: @js($lotes->map(fn($l) => [
        'id'          => $l->id,
        'lote'        => $l->numero_lote,
        'producto'    => $l->producto->nombre ?? '',
        'proveedor_id'=> $l->proveedor_id,
        'stock'       => $l->stock_actual,
        'precio'      => $l->precio_compra ?? 0,
        'controlado'  => $l->producto->esControlado() ?? false,
        'vence'       => $l->fecha_vencimiento?->format('d/m/Y'),
    ])),
    proveedorId: '{{ old('proveedor_id', $compra?->proveedor_id ?? '') }}',
    agregarItem() {
        this.items.push({ lote_id: '', cantidad: 1, precio_unitario: 0, motivo_detalle: '', _stock: 0, _producto: '', _lote: '', _controlado: false });
    },
    removerItem(idx) {
        this.items.splice(idx, 1);
    },
    onLoteChange(item, loteId) {
        const lote = this.lotesCatalogo.find(l => l.id == loteId);
        if (lote) {
            item._stock      = lote.stock;
            item._producto   = lote.producto;
            item._lote       = lote.lote;
            item._controlado = lote.controlado;
            item.precio_unitario = lote.precio;
            if (!this.proveedorId) this.proveedorId = lote.proveedor_id;
        }
    },
    get total() {
        return this.items.reduce((s, i) => s + (parseFloat(i.precio_unitario||0) * parseInt(i.cantidad||0)), 0);
    }
}" class="max-w-5xl mx-auto space-y-6">

    {{-- Breadcrumb --}}
    <nav class="flex items-center space-x-2 text-xs text-slate-500 dark:text-slate-400">
        <a href="{{ route('compras.devoluciones.index') }}" class="hover:text-rose-600 dark:hover:text-rose-400 transition">Devoluciones a Proveedor</a>
        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
        <span class="text-slate-800 dark:text-slate-200 font-semibold">Nueva Devolución</span>
    </nav>

    {{-- Header --}}
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h1 class="text-xl font-bold text-slate-900 dark:text-white">Registrar Devolución a Proveedor</h1>
            <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">
                El stock se descuenta inmediatamente. Los productos controlados MINSA quedan automáticamente en el libro de controlados como egreso.
            </p>
        </div>
        <a href="{{ route('compras.devoluciones.index') }}"
           class="inline-flex items-center space-x-1.5 px-3.5 py-2 bg-slate-100 dark:bg-slate-800 hover:bg-slate-200 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-300 text-xs font-semibold rounded-xl transition shrink-0">
            <span>&larr;</span><span>Volver</span>
        </a>
    </div>

    @if(session('error') || $errors->any())
    <div class="px-4 py-3 rounded-xl bg-rose-50 dark:bg-rose-950/40 border border-rose-300 dark:border-rose-700 text-xs text-rose-800 dark:text-rose-300 font-semibold space-y-1">
        @if(session('error'))<p>{{ session('error') }}</p>@endif
        @foreach($errors->all() as $e)<p>• {{ $e }}</p>@endforeach
    </div>
    @endif

    {{-- Info controlados --}}
    <div class="flex items-start gap-3 px-4 py-3.5 bg-amber-50 dark:bg-amber-950/30 border border-amber-200 dark:border-amber-800 rounded-xl text-xs text-amber-800 dark:text-amber-300">
        <svg class="w-4 h-4 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
        <span>Los lotes marcados con <span class="font-bold text-rose-600 dark:text-rose-400">CONTROLADO</span> generarán automáticamente un registro de egreso en el libro de medicamentos controlados MINSA al guardar.</span>
    </div>

    <form method="POST" action="{{ route('compras.devoluciones.store') }}" class="space-y-6">
        @csrf

        {{-- Cabecera --}}
        <div class="bg-white dark:bg-slate-900 rounded-2xl p-6 border border-slate-200 dark:border-slate-800 shadow-sm space-y-5">
            <div class="border-b border-slate-100 dark:border-slate-800 pb-3">
                <h3 class="text-sm font-bold text-slate-900 dark:text-white flex items-center space-x-2">
                    <span class="w-2 h-2 rounded-full bg-rose-500"></span><span>Datos Generales</span>
                </h3>
            </div>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                <div>
                    <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-1.5">
                        Proveedor <span class="text-rose-500">*</span>
                    </label>
                    <select name="proveedor_id" x-model="proveedorId" required
                            class="w-full px-4 py-2.5 bg-slate-50 dark:bg-slate-800/80 border border-slate-200 dark:border-slate-700 rounded-xl text-sm text-slate-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-rose-500/30 focus:border-rose-500 transition @error('proveedor_id') border-rose-500 @enderror">
                        <option value="">-- Seleccionar proveedor --</option>
                        @foreach($proveedores as $prov)
                        <option value="{{ $prov->id }}" {{ old('proveedor_id') == $prov->id ? 'selected' : '' }}>{{ $prov->nombre }}</option>
                        @endforeach
                    </select>
                    @error('proveedor_id') <p class="text-rose-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>
                @if($compra)
                <div>
                    <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-1.5">OC / Compra de Origen</label>
                    <div class="px-4 py-2.5 bg-slate-100 dark:bg-slate-800 rounded-xl text-sm text-slate-700 dark:text-slate-300">
                        #{{ $compra->id }} — {{ $compra->proveedor->nombre ?? '—' }} ({{ $compra->created_at->format('d/m/Y') }})
                    </div>
                    <input type="hidden" name="compra_id" value="{{ $compra->id }}">
                </div>
                @endif
                <div class="{{ $compra ? 'md:col-span-2' : 'md:col-span-2' }}">
                    <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-1.5">
                        Motivo General <span class="text-rose-500">*</span>
                    </label>
                    <textarea name="motivo" rows="2" required minlength="5"
                              placeholder="Ej. Productos vencidos en el almacén, Error en pedido, Daños en transporte..."
                              class="w-full px-4 py-2.5 bg-slate-50 dark:bg-slate-800/80 border border-slate-200 dark:border-slate-700 rounded-xl text-sm text-slate-900 dark:text-white placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-rose-500/30 focus:border-rose-500 transition @error('motivo') border-rose-500 @enderror">{{ old('motivo') }}</textarea>
                    @error('motivo') <p class="text-rose-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>
            </div>
        </div>

        {{-- Lotes a devolver --}}
        <div class="bg-white dark:bg-slate-900 rounded-2xl p-6 border border-slate-200 dark:border-slate-800 shadow-sm space-y-4">
            <div class="border-b border-slate-100 dark:border-slate-800 pb-3 flex items-center justify-between">
                <h3 class="text-sm font-bold text-slate-900 dark:text-white flex items-center space-x-2">
                    <span class="w-2 h-2 rounded-full bg-indigo-500"></span><span>Lotes a Devolver</span>
                </h3>
                <button type="button" @click="agregarItem()"
                        class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-semibold rounded-lg transition">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                    Agregar Lote
                </button>
            </div>

            {{-- Filas de items --}}
            <template x-for="(item, idx) in items" :key="idx">
                <div class="grid grid-cols-12 gap-3 items-start p-4 bg-slate-50 dark:bg-slate-800/60 rounded-xl border border-slate-200 dark:border-slate-700">
                    {{-- Lote --}}
                    <div class="col-span-12 md:col-span-4">
                        <label class="block text-[11px] font-semibold text-slate-500 dark:text-slate-400 mb-1">Lote *</label>
                        <select x-model="item.lote_id" @change="onLoteChange(item, item.lote_id)"
                                :name="'items[' + idx + '][lote_id]'" required
                                class="w-full px-3 py-2 bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-lg text-xs text-slate-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-rose-500 transition">
                            <option value="">-- Seleccionar lote --</option>
                            <template x-for="l in lotesCatalogo" :key="l.id">
                                <option :value="l.id" x-text="`${l.producto} · Lote ${l.lote} (Stock: ${l.stock}) ${l.vence ? '· Vence '+l.vence : ''}`"></option>
                            </template>
                        </select>
                        <div class="flex items-center gap-2 mt-1">
                            <span x-show="item._controlado"
                                  class="px-1.5 py-0.5 rounded text-[9px] font-black bg-rose-100 dark:bg-rose-950/60 text-rose-700 dark:text-rose-300">
                                CONTROLADO — se registrará en libro MINSA
                            </span>
                            <span x-show="item._stock > 0" x-text="`Stock disponible: ${item._stock}`"
                                  class="text-[10px] text-slate-400"></span>
                        </div>
                    </div>
                    {{-- Cantidad --}}
                    <div class="col-span-4 md:col-span-2">
                        <label class="block text-[11px] font-semibold text-slate-500 dark:text-slate-400 mb-1">Cantidad *</label>
                        <input type="number" x-model="item.cantidad" :name="'items[' + idx + '][cantidad]'"
                               :max="item._stock" min="1" required
                               class="w-full px-3 py-2 bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-lg text-xs text-slate-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-rose-500 transition">
                    </div>
                    {{-- Precio unitario --}}
                    <div class="col-span-4 md:col-span-2">
                        <label class="block text-[11px] font-semibold text-slate-500 dark:text-slate-400 mb-1">C\$ Unitario</label>
                        <input type="number" x-model="item.precio_unitario" :name="'items[' + idx + '][precio_unitario]'"
                               step="0.01" min="0"
                               class="w-full px-3 py-2 bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-lg text-xs text-slate-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-rose-500 transition">
                    </div>
                    {{-- Subtotal --}}
                    <div class="col-span-4 md:col-span-2 flex flex-col justify-end">
                        <label class="block text-[11px] font-semibold text-slate-500 dark:text-slate-400 mb-1">Subtotal</label>
                        <div class="px-3 py-2 bg-slate-200 dark:bg-slate-700 rounded-lg text-xs font-mono font-bold text-slate-800 dark:text-slate-200">
                            C\$ <span x-text="(parseFloat(item.precio_unitario||0) * parseInt(item.cantidad||0)).toFixed(2)"></span>
                        </div>
                    </div>
                    {{-- Motivo ítem --}}
                    <div class="col-span-10 md:col-span-3">
                        <label class="block text-[11px] font-semibold text-slate-500 dark:text-slate-400 mb-1">Motivo por Ítem</label>
                        <input type="text" x-model="item.motivo_detalle" :name="'items[' + idx + '][motivo_detalle]'"
                               placeholder="Vencido, dañado, error..."
                               class="w-full px-3 py-2 bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-lg text-xs text-slate-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-rose-500 transition">
                    </div>
                    {{-- Eliminar --}}
                    <div class="col-span-2 md:col-span-1 flex items-end justify-end pb-0.5">
                        <button type="button" @click="removerItem(idx)"
                                class="w-8 h-8 inline-flex items-center justify-center text-slate-400 hover:text-rose-500 hover:bg-rose-50 dark:hover:bg-rose-950/30 rounded-lg transition">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                        </button>
                    </div>
                </div>
            </template>

            {{-- Empty state --}}
            <div x-show="items.length === 0" class="text-center py-8 text-slate-400 text-xs">
                No has agregado lotes aún. Haz clic en "Agregar Lote" para comenzar.
            </div>

            {{-- Total --}}
            <div x-show="items.length > 0" class="flex justify-end">
                <div class="px-4 py-2.5 bg-rose-50 dark:bg-rose-950/40 rounded-xl border border-rose-200 dark:border-rose-800">
                    <span class="text-xs font-semibold text-rose-700 dark:text-rose-300">Total a devolver: </span>
                    <span class="font-mono font-bold text-rose-700 dark:text-rose-300">C\$ <span x-text="total.toFixed(2)"></span></span>
                </div>
            </div>
        </div>

        {{-- Footer sticky --}}
        <div class="sticky bottom-0 -mx-4 sm:-mx-6 lg:-mx-8 px-4 sm:px-6 lg:px-8 py-3.5
                    bg-white/95 dark:bg-slate-900/95 backdrop-blur-md border-t border-slate-200 dark:border-slate-800
                    shadow-lg z-20 flex items-center justify-between gap-3">
            <a href="{{ route('compras.devoluciones.index') }}"
               class="px-5 py-2.5 bg-slate-100 dark:bg-slate-800 hover:bg-slate-200 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-300 text-sm font-medium rounded-xl transition">
                Cancelar
            </a>
            <button type="submit" :disabled="items.length === 0"
                    class="px-6 py-2.5 bg-rose-600 hover:bg-rose-700 active:bg-rose-800 disabled:opacity-50 disabled:cursor-not-allowed text-white text-sm font-semibold rounded-xl shadow-sm transition inline-flex items-center gap-2">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h10a8 8 0 018 8v2M3 10l6 6m-6-6l6-6"/></svg>
                Confirmar Devolución
            </button>
        </div>
    </form>
</div>
@endsection
