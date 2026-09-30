@extends('layouts.app')

@section('title', "Estado de Cuenta Compra #{$compra->numero_comprobante} - FarmaBien")

@section('content')
<div class="max-w-5xl mx-auto space-y-5" x-data="{ modalAbono: false, metodoPago: 'transferencia', montoAbono: {{ $compra->saldo_pendiente }} }">
    
    <!-- Breadcrumbs -->
    <nav class="flex items-center space-x-2 text-xs text-slate-500 dark:text-slate-400">
        <a href="{{ route('dashboard') }}" class="hover:text-emerald-600 dark:hover:text-emerald-400 transition">Inicio</a>
        <svg class="w-3 h-3 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
        <a href="{{ route('compras.index') }}" class="hover:text-emerald-600 dark:hover:text-emerald-400 transition">Compras</a>
        <svg class="w-3 h-3 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
        <a href="{{ route('cuentas-por-pagar.index') }}" class="hover:text-emerald-600 dark:hover:text-emerald-400 transition">Cuentas por Pagar</a>
        <svg class="w-3 h-3 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
        <span class="text-slate-800 dark:text-slate-200 font-semibold">{{ $compra->numero_comprobante ?? 'Compra #' . $compra->id }}</span>
    </nav>

    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <div class="flex items-center gap-2">
                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-bold {{ $compra->saldo_pendiente <= 0 ? 'bg-emerald-100 text-emerald-800 dark:bg-emerald-900/40 dark:text-emerald-300' : 'bg-rose-100 text-rose-800 dark:bg-rose-900/40 dark:text-rose-300' }}">
                    {{ $compra->saldo_pendiente <= 0 ? 'PAGADA TOTALMENTE' : 'SALDO PENDIENTE' }}
                </span>
                <span class="text-xs text-slate-500 font-medium">Crédito a {{ $compra->dias_credito }} días</span>
            </div>
            <h1 class="text-2xl font-black tracking-tight text-slate-900 dark:text-white mt-1">
                Factura {{ $compra->numero_comprobante ?? 'Compra #' . $compra->id }}
            </h1>
            <p class="text-xs text-slate-500">Proveedor: <span class="font-bold text-slate-700 dark:text-slate-300">{{ $compra->proveedor->nombre ?? $compra->proveedor->nombre_empresa }}</span></p>
        </div>
        <div class="flex items-center gap-2">
            @if($compra->saldo_pendiente > 0)
            <button type="button" @click="modalAbono = true" 
                    class="inline-flex items-center px-4 py-2 rounded-xl text-xs font-bold text-white bg-emerald-600 hover:bg-emerald-700 shadow-sm transition">
                <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                Registrar Abono
            </button>
            @endif
            <a href="{{ route('cuentas-por-pagar.index') }}" 
               class="inline-flex items-center px-3.5 py-2 rounded-xl text-xs font-semibold text-slate-700 dark:text-slate-200 bg-white dark:bg-slate-800 border border-slate-300 dark:border-slate-700 hover:bg-slate-50 shadow-xs transition">
                Volver
            </a>
        </div>
    </div>

    <!-- Summary Cards -->
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
        <div class="p-4 rounded-xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 shadow-xs">
            <span class="text-xs font-medium text-slate-500">Total Factura</span>
            <p class="text-xl font-black text-slate-900 dark:text-white mt-1">{{ formato_moneda($compra->total) }}</p>
            <p class="text-[11px] text-slate-400 mt-0.5">Emisión: {{ $compra->fecha->format('d/m/Y') }}</p>
        </div>

        <div class="p-4 rounded-xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 shadow-xs">
            <span class="text-xs font-medium text-emerald-600 dark:text-emerald-400">Total Abonado</span>
            <p class="text-xl font-black text-emerald-600 dark:text-emerald-400 mt-1">{{ formato_moneda($compra->total - $compra->saldo_pendiente) }}</p>
            <p class="text-[11px] text-slate-400 mt-0.5">{{ $compra->pagos->count() }} pago(s) realizados</p>
        </div>

        <div class="p-4 rounded-xl bg-white dark:bg-slate-900 border border-rose-200 dark:border-rose-900/40 shadow-xs">
            <span class="text-xs font-medium text-rose-600 dark:text-rose-400">Saldo Restante</span>
            <p class="text-xl font-black text-rose-600 dark:text-rose-400 mt-1">{{ formato_moneda($compra->saldo_pendiente) }}</p>
            <p class="text-[11px] text-slate-400 mt-0.5">Pendiente por liquidar</p>
        </div>

        <div class="p-4 rounded-xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 shadow-xs">
            <span class="text-xs font-medium text-slate-500">Fecha Vencimiento</span>
            <p class="text-base font-bold text-slate-900 dark:text-white mt-1">{{ $compra->fecha_vencimiento_pago ? $compra->fecha_vencimiento_pago->format('d/m/Y') : 'N/A' }}</p>
            @if($compra->saldo_pendiente > 0 && $compra->fecha_vencimiento_pago)
                @php $dias = (int) now()->startOfDay()->diffInDays($compra->fecha_vencimiento_pago->startOfDay(), false); @endphp
                <p class="text-[11px] font-semibold {{ $dias < 0 ? 'text-rose-500' : 'text-slate-400' }} mt-0.5">
                    {{ $dias < 0 ? 'Vencida hace ' . abs($dias) . ' días' : 'Faltan ' . $dias . ' días' }}
                </p>
            @endif
        </div>
    </div>

    <!-- Payments Ledger Table -->
    <div class="rounded-xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 shadow-xs overflow-hidden">
        <div class="px-5 py-4 border-b border-slate-200 dark:border-slate-800 bg-slate-50/50 dark:bg-slate-800/40 flex items-center justify-between">
            <h3 class="text-sm font-bold text-slate-900 dark:text-white">Historial de Abonos y Pagos Realizados</h3>
            <span class="text-xs text-slate-500">Registros de egreso</span>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead class="bg-slate-50 dark:bg-slate-800/60 text-slate-500 dark:text-slate-400 border-b border-slate-200 dark:border-slate-800 text-[11px] font-semibold uppercase tracking-wider">
                    <tr>
                        <th class="px-4 py-3">N° Recibo / Comprobante</th>
                        <th class="px-4 py-3">Fecha de Pago</th>
                        <th class="px-4 py-3">Método / Banco</th>
                        <th class="px-4 py-3">Referencia</th>
                        <th class="px-4 py-3 text-right">Monto Abonado</th>
                        <th class="px-4 py-3">Registrado Por</th>
                        <th class="px-4 py-3">Observaciones</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-200 dark:divide-slate-800 text-slate-700 dark:text-slate-300">
                    @forelse($compra->pagos as $pago)
                    <tr class="hover:bg-slate-50/80 dark:hover:bg-slate-800/50 transition">
                        <td class="px-4 py-3 font-bold text-slate-900 dark:text-white">{{ $pago->numero_pago }}</td>
                        <td class="px-4 py-3">{{ $pago->fecha_pago->format('d/m/Y H:i') }}</td>
                        <td class="px-4 py-3 capitalize">
                            <span class="font-semibold">{{ $pago->metodo_pago }}</span>
                            @if($pago->banco)
                            <div class="text-[11px] text-slate-500">{{ $pago->banco }}</div>
                            @endif
                        </td>
                        <td class="px-4 py-3 font-mono text-[11px]">{{ $pago->numero_referencia ?? '-' }}</td>
                        <td class="px-4 py-3 text-right font-black text-emerald-600 dark:text-emerald-400">{{ formato_moneda($pago->monto) }}</td>
                        <td class="px-4 py-3">{{ $pago->usuario->name ?? 'Sistema' }}</td>
                        <td class="px-4 py-3 text-slate-500 text-[11px]">{{ $pago->observaciones ?? '-' }}</td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="px-4 py-6 text-center text-slate-500">
                            Aún no se han registrado abonos a esta factura.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- Products in this purchase -->
    <div class="rounded-xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 shadow-xs overflow-hidden">
        <div class="px-5 py-4 border-b border-slate-200 dark:border-slate-800 bg-slate-50/50 dark:bg-slate-800/40">
            <h3 class="text-sm font-bold text-slate-900 dark:text-white">Productos Adquiridos en esta Compra</h3>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead class="bg-slate-50 dark:bg-slate-800/60 text-slate-500 dark:text-slate-400 border-b border-slate-200 dark:border-slate-800 text-[11px] font-semibold uppercase tracking-wider">
                    <tr>
                        <th class="px-4 py-3">Producto</th>
                        <th class="px-4 py-3">Lote</th>
                        <th class="px-4 py-3 text-center">Cantidad</th>
                        <th class="px-4 py-3 text-right">Precio Compra</th>
                        <th class="px-4 py-3 text-right">Subtotal</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-200 dark:divide-slate-800 text-slate-700 dark:text-slate-300">
                    @foreach($compra->detalles as $det)
                    <tr class="hover:bg-slate-50/80 dark:hover:bg-slate-800/50 transition">
                        <td class="px-4 py-3 font-bold text-slate-900 dark:text-white">{{ $det->producto->nombre }}</td>
                        <td class="px-4 py-3 font-mono">{{ $det->lote->numero_lote ?? 'N/A' }}</td>
                        <td class="px-4 py-3 text-center font-semibold">{{ $det->cantidad_presentaciones }} ({{ $det->tipo_presentacion }})</td>
                        <td class="px-4 py-3 text-right">{{ formato_moneda($det->precio_unitario) }}</td>
                        <td class="px-4 py-3 text-right font-black">{{ formato_moneda($det->subtotal) }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    <!-- Modal Registrar Abono -->
    <div x-show="modalAbono" 
         x-cloak
         class="fixed inset-0 z-50 overflow-y-auto p-4 sm:p-6 flex items-center justify-center bg-slate-900/60 backdrop-blur-xs transition-opacity"
         @keydown.escape.window="modalAbono = false">
        
        <div class="bg-white dark:bg-slate-900 rounded-2xl max-w-lg w-full p-6 border border-slate-200 dark:border-slate-800 shadow-2xl space-y-4 max-h-[90vh] overflow-y-auto custom-scrollbar my-auto"
             @click.outside="modalAbono = false">
            
            <div class="flex items-center justify-between pb-3 border-b border-slate-200 dark:border-slate-800">
                <div class="flex items-center space-x-2.5">
                    <div class="w-9 h-9 rounded-xl bg-emerald-100 dark:bg-emerald-950/60 text-emerald-600 dark:text-emerald-400 flex items-center justify-center shrink-0">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
                    </div>
                    <div>
                        <h3 class="text-sm font-bold text-slate-900 dark:text-white">Registrar Abono a Proveedor</h3>
                        <p class="text-xs text-slate-500 dark:text-slate-400 truncate max-w-[280px]">{{ $compra->proveedor->nombre ?? $compra->proveedor->nombre_empresa }} ({{ $compra->numero_comprobante }})</p>
                    </div>
                </div>
                <button type="button" @click="modalAbono = false" class="text-slate-400 hover:text-slate-600 dark:hover:text-slate-200 text-base font-bold p-1 rounded-lg hover:bg-slate-100 dark:hover:bg-slate-800 transition">✕</button>
            </div>

            <form action="{{ route('cuentas-por-pagar.abonos.store', $compra) }}" method="POST" class="space-y-4">
                @csrf
                <div>
                    <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">Monto a Abonar (C$) <span class="text-rose-500">*</span></label>
                    <input type="number" 
                           name="monto" 
                           x-model="montoAbono" 
                           step="0.01" 
                           min="0.01" 
                           max="{{ $compra->saldo_pendiente }}" 
                           required 
                           class="w-full px-3.5 py-2 text-sm font-bold rounded-xl border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-900 dark:text-white focus:ring-2 focus:ring-emerald-500">
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">Forma de Pago <span class="text-rose-500">*</span></label>
                        <select name="metodo_pago" x-model="metodoPago" required 
                                class="w-full px-3 py-2 text-xs font-semibold rounded-xl border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-900 dark:text-white focus:ring-2 focus:ring-emerald-500">
                            <option value="transferencia">Transferencia Bancaria</option>
                            <option value="efectivo">Efectivo</option>
                            <option value="cheque">Cheque</option>
                            <option value="otro">Otro</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">Fecha de Pago</label>
                        <input type="date" name="fecha_pago" value="{{ now()->toDateString() }}" 
                               class="w-full px-3 py-2 text-xs font-semibold rounded-xl border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-900 dark:text-white focus:ring-2 focus:ring-emerald-500">
                    </div>
                </div>

                <div x-show="metodoPago === 'transferencia' || metodoPago === 'cheque'" class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">Banco</label>
                        <select name="banco" class="w-full px-3 py-2 text-xs font-semibold rounded-xl border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-900 dark:text-white focus:ring-2 focus:ring-emerald-500">
                            <option value="">Seleccione Banco...</option>
                            <option value="BAC Credomatic">BAC Credomatic</option>
                            <option value="Banco LAFISE Bancentro">Banco LAFISE Bancentro</option>
                            <option value="Banpro Grupo Promerica">Banpro Grupo Promerica</option>
                            <option value="Banco Ficohsa">Banco Ficohsa</option>
                            <option value="Banco Avanz">Banco Avanz</option>
                            <option value="Banco BDF">Banco BDF</option>
                            <option value="Otro">Otro</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">N° Referencia / Cheque</label>
                        <input type="text" name="numero_referencia" placeholder="Ej: TR-00192" 
                               class="w-full px-3 py-2 text-xs rounded-xl border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-900 dark:text-white focus:ring-2 focus:ring-emerald-500">
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">Observaciones</label>
                    <textarea name="observaciones" rows="2" placeholder="Nota opcional sobre el abono..." 
                              class="w-full px-3.5 py-2 text-xs rounded-xl border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-900 dark:text-white focus:ring-2 focus:ring-emerald-500"></textarea>
                </div>

                <div x-show="metodoPago === 'efectivo'" class="pt-1">
                    <label class="inline-flex items-center text-xs text-slate-700 dark:text-slate-300 font-medium cursor-pointer">
                        <input type="checkbox" name="registrar_en_caja" value="1" checked class="rounded border-slate-300 text-emerald-600 focus:ring-emerald-500 mr-2">
                        Registrar egreso automáticamente en la caja activa del turno
                    </label>
                </div>

                <div class="flex items-center justify-end gap-2 pt-3 border-t border-slate-200 dark:border-slate-800">
                    <button type="button" @click="modalAbono = false" 
                            class="px-4 py-2 rounded-xl text-xs font-semibold text-slate-600 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800 transition">
                        Cancelar
                    </button>
                    <button type="submit" 
                            class="px-5 py-2 rounded-xl text-xs font-bold text-white bg-emerald-600 hover:bg-emerald-700 shadow-xs transition">
                        Guardar Abono
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
