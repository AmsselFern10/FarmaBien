@extends('layouts.app')

@section('title', 'Detalle de Compra')

@section('header')
    <div class="flex justify-between items-center">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Compra #{{ $compra->id }}
        </h2>
        <div class="flex space-x-2">
            @can('anular compras')
                @if($compra->puedeModificarse())
                <a href="{{ route('compras.edit', $compra) }}" 
                   class="bg-yellow-500 hover:bg-yellow-700 text-white font-bold py-2 px-4 rounded inline-flex items-center">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                    </svg>
                    Modificar
                </a>
                <button onclick="modalAnular({{ $compra->id }})"
                        class="bg-red-500 hover:bg-red-700 text-white font-bold py-2 px-4 rounded inline-flex items-center">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                    Anular
                </button>
                @endif
            @endcan
            <a href="{{ route('compras.index') }}" 
               class="bg-gray-500 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded inline-flex items-center">
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                </svg>
                Volver
            </a>
        </div>
    </div>
@endsection

@section('content')

<!-- Alertas de Estado -->
@if($compra->estado == 'anulada')
<div class="bg-red-50 border-l-4 border-red-400 p-4 mb-6">
    <div class="flex">
        <svg class="h-5 w-5 text-red-400" fill="currentColor" viewBox="0 0 20 20">
            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
        </svg>
        <div class="ml-3">
            <p class="text-sm text-red-700">
                <strong>Compra Anulada</strong> - {{ $compra->fecha_anulacion->format('d/m/Y H:i') }}
            </p>
            <p class="text-sm text-red-600 mt-1">
                Motivo: {{ $compra->motivo_anulacion }}
            </p>
            <p class="text-xs text-red-500 mt-1">
                Anulado por: {{ $compra->anuladoPor->name }}
            </p>
        </div>
    </div>
</div>
@endif

@if($compra->estado == 'modificada')
<div class="bg-purple-50 border-l-4 border-purple-400 p-4 mb-6">
    <div class="flex">
        <svg class="h-5 w-5 text-purple-400" fill="currentColor" viewBox="0 0 20 20">
            <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
        </svg>
        <div class="ml-3">
            <p class="text-sm text-purple-700">
                <strong>Compra Modificada</strong> - Esta compra fue reemplazada por 
                <a href="{{ route('compras.show', $compra->reemplazadaPor) }}" class="underline font-semibold">
                    Compra #{{ $compra->reemplazadaPor->id }}
                </a>
            </p>
        </div>
    </div>
</div>
@endif

@if($compra->compra_original_id)
<div class="bg-blue-50 border-l-4 border-blue-400 p-4 mb-6">
    <div class="flex">
        <svg class="h-5 w-5 text-blue-400" fill="currentColor" viewBox="0 0 20 20">
            <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"/>
        </svg>
        <div class="ml-3">
            <p class="text-sm text-blue-700">
                Esta compra es una modificación de 
                <a href="{{ route('compras.show', $compra->compraOriginal) }}" class="underline font-semibold">
                    Compra #{{ $compra->compra_original_id }}
                </a>
            </p>
            <p class="text-xs text-blue-600 mt-1">
                Motivo: {{ $compra->motivo_modificacion }}
            </p>
        </div>
    </div>
</div>
@endif

<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
    
    <!-- Columna Principal -->
    <div class="lg:col-span-2 space-y-6">
        
        <!-- Información General -->
        <div class="bg-white overflow-hidden shadow-sm rounded-lg">
            <div class="p-6">
                <h3 class="text-lg font-semibold text-gray-800 mb-4">Información de la Compra</h3>
                
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <p class="text-sm text-gray-600">Fecha de Compra</p>
                        <p class="font-semibold text-gray-900">{{ $compra->fecha->format('d/m/Y') }}</p>
                    </div>

                    <div>
                        <p class="text-sm text-gray-600">Estado</p>
                        <p>
                            @if($compra->estado == 'completada')
                                <span class="px-3 py-1 text-sm rounded-full bg-green-100 text-green-800 font-semibold">Completada</span>
                            @elseif($compra->estado == 'anulada')
                                <span class="px-3 py-1 text-sm rounded-full bg-red-100 text-red-800 font-semibold">Anulada</span>
                            @elseif($compra->estado == 'modificada')
                                <span class="px-3 py-1 text-sm rounded-full bg-purple-100 text-purple-800 font-semibold">Modificada</span>
                            @endif
                        </p>
                    </div>

                    <div>
                        <p class="text-sm text-gray-600">Tipo de Comprobante</p>
                        <p class="font-semibold text-gray-900">{{ strtoupper($compra->tipo_comprobante) }}</p>
                    </div>

                    <div>
                        <p class="text-sm text-gray-600">Número de Comprobante</p>
                        <p class="font-semibold text-gray-900">{{ $compra->numero_comprobante }}</p>
                    </div>

                    <div class="col-span-2">
                        <p class="text-sm text-gray-600">Proveedor</p>
                        <p class="font-semibold text-gray-900">{{ $compra->proveedor->nombre }}</p>
                        <p class="text-xs text-gray-500">RUC: {{ $compra->proveedor->ruc }}</p>
                    </div>

                    @if($compra->observaciones)
                    <div class="col-span-2">
                        <p class="text-sm text-gray-600">Observaciones</p>
                        <p class="text-gray-900">{{ $compra->observaciones }}</p>
                    </div>
                    @endif

                    <div>
                        <p class="text-sm text-gray-600">Registrado por</p>
                        <p class="font-semibold text-gray-900">{{ $compra->usuario->name }}</p>
                    </div>

                    <div>
                        <p class="text-sm text-gray-600">Fecha de Registro</p>
                        <p class="text-gray-900">{{ $compra->created_at->format('d/m/Y H:i') }}</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Detalle de Productos -->
        <div class="bg-white overflow-hidden shadow-sm rounded-lg">
            <div class="p-6">
                <h3 class="text-lg font-semibold text-gray-800 mb-4">Detalle de Productos</h3>
                
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Producto</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Lote</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Vencimiento</th>
                                <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase">Cantidad</th>
                                <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase">P. Unit.</th>
                                <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase">Subtotal</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @foreach($compra->detalles as $detalle)
                            <tr>
                                <td class="px-4 py-3">
                                    <div class="text-sm font-medium text-gray-900">{{ $detalle->producto->nombre }}</div>
                                    <div class="text-xs text-gray-500">{{ $detalle->producto->categoria->nombre }}</div>
                                </td>
                                <td class="px-4 py-3">
                                    <span class="text-sm font-mono text-gray-900">{{ $detalle->numero_lote }}</span>
                                </td>
                                <td class="px-4 py-3">
                                    <span class="text-sm text-gray-900">{{ $detalle->fecha_vencimiento->format('d/m/Y') }}</span>
                                </td>
                                <td class="px-4 py-3 text-right">
                                    <span class="text-sm font-semibold text-gray-900">{{ $detalle->cantidad }}</span>
                                </td>
                                <td class="px-4 py-3 text-right">
                                    <span class="text-sm text-gray-900">S/ {{ number_format($detalle->precio_unitario, 2) }}</span>
                                </td>
                                <td class="px-4 py-3 text-right">
                                    <span class="text-sm font-semibold text-gray-900">S/ {{ number_format($detalle->subtotal, 2) }}</span>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Lotes Generados -->
        @if($compra->lotes->count() > 0)
        <div class="bg-white overflow-hidden shadow-sm rounded-lg">
            <div class="p-6">
                <h3 class="text-lg font-semibold text-gray-800 mb-4">Lotes Generados</h3>
                
                <div class="space-y-3">
                    @foreach($compra->lotes as $lote)
                    <div class="border rounded-lg p-4 {{ $lote->estado == 'agotado' ? 'bg-gray-50' : '' }}">
                        <div class="flex justify-between items-start">
                            <div>
                                <p class="font-semibold text-gray-900">{{ $lote->producto->nombre }}</p>
                                <p class="text-sm text-gray-600">Lote: {{ $lote->numero_lote }}</p>
                                <p class="text-xs text-gray-500">Vencimiento: {{ $lote->fecha_vencimiento->format('d/m/Y') }}</p>
                            </div>
                            <div class="text-right">
                                <p class="text-sm text-gray-600">Stock</p>
                                <p class="text-lg font-bold text-gray-900">{{ $lote->cantidad_actual }} / {{ $lote->cantidad_inicial }}</p>
                                @if($lote->estado == 'disponible')
                                    <span class="text-xs px-2 py-1 rounded-full bg-green-100 text-green-800">Disponible</span>
                                @elseif($lote->estado == 'agotado')
                                    <span class="text-xs px-2 py-1 rounded-full bg-gray-100 text-gray-800">Agotado</span>
                                @elseif($lote->estado == 'vencido')
                                    <span class="text-xs px-2 py-1 rounded-full bg-red-100 text-red-800">Vencido</span>
                                @endif
                            </div>
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>
        </div>
        @endif

        <!-- Historial de Modificaciones -->
        @if($historial && count($historial) > 0)
        <div class="bg-white overflow-hidden shadow-sm rounded-lg">
            <div class="p-6">
                <h3 class="text-lg font-semibold text-gray-800 mb-4">Historial de Modificaciones</h3>
                
                <div class="space-y-4">
                    @foreach($historial as $registro)
                    <div class="border-l-4 border-blue-400 pl-4 py-2">
                        <div class="flex justify-between items-start">
                            <div>
                                <p class="font-semibold text-gray-900">
                                    Compra #{{ $registro->id }}
                                    @if($registro->id == $compra->id)
                                        <span class="text-xs bg-blue-100 text-blue-800 px-2 py-1 rounded">Actual</span>
                                    @endif
                                </p>
                                <p class="text-sm text-gray-600">{{ $registro->fecha->format('d/m/Y') }}</p>
                                @if($registro->motivo_modificacion)
                                    <p class="text-xs text-gray-500 mt-1">Motivo: {{ $registro->motivo_modificacion }}</p>
                                @endif
                            </div>
                            <div class="text-right">
                                <p class="font-bold text-gray-900">S/ {{ number_format($registro->total, 2) }}</p>
                                @if($registro->estado == 'modificada')
                                    <span class="text-xs px-2 py-1 rounded-full bg-purple-100 text-purple-800">Modificada</span>
                                @elseif($registro->estado == 'completada')
                                    <span class="text-xs px-2 py-1 rounded-full bg-green-100 text-green-800">Vigente</span>
                                @endif
                            </div>
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>
        </div>
        @endif

    </div>

    <!-- Columna Lateral -->
    <div class="lg:col-span-1 space-y-6">
        
        <!-- Resumen Financiero -->
        <div class="bg-white overflow-hidden shadow-sm rounded-lg">
            <div class="p-6">
                <h3 class="text-lg font-semibold text-gray-800 mb-4">Resumen Financiero</h3>
                
                <div class="space-y-3">
                    <div class="flex justify-between items-center pb-2 border-b">
                        <span class="text-sm text-gray-600">Subtotal:</span>
                        <span class="font-semibold text-gray-900">S/ {{ number_format($compra->subtotal, 2) }}</span>
                    </div>

                    <div class="flex justify-between items-center pb-2 border-b">
                        <span class="text-sm text-gray-600">IGV (18%):</span>
                        <span class="font-semibold text-gray-900">S/ {{ number_format($compra->igv, 2) }}</span>
                    </div>

                    <div class="flex justify-between items-center pt-2">
                        <span class="text-lg font-semibold text-gray-900">Total:</span>
                        <span class="text-2xl font-bold text-blue-600">S/ {{ number_format($compra->total, 2) }}</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Estadísticas -->
        <div class="bg-white overflow-hidden shadow-sm rounded-lg">
            <div class="p-6">
                <h3 class="text-lg font-semibold text-gray-800 mb-4">Estadísticas</h3>
                
                <div class="space-y-4">
                    <div>
                        <div class="flex justify-between items-center mb-1">
                            <span class="text-sm text-gray-600">Productos</span>
                            <span class="font-bold text-gray-900">{{ $compra->detalles->count() }}</span>
                        </div>
                        <div class="w-full bg-gray-200 rounded-full h-2">
                            <div class="bg-blue-600 h-2 rounded-full" style="width: 100%"></div>
                        </div>
                    </div>

                    <div>
                        <div class="flex justify-between items-center mb-1">
                            <span class="text-sm text-gray-600">Unidades Totales</span>
                            <span class="font-bold text-gray-900">{{ $compra->detalles->sum('cantidad') }}</span>
                        </div>
                        <div class="w-full bg-gray-200 rounded-full h-2">
                            <div class="bg-green-600 h-2 rounded-full" style="width: 100%"></div>
                        </div>
                    </div>

                    <div>
                        <div class="flex justify-between items-center mb-1">
                            <span class="text-sm text-gray-600">Lotes Generados</span>
                            <span class="font-bold text-gray-900">{{ $compra->lotes->count() }}</span>
                        </div>
                        <div class="w-full bg-gray-200 rounded-full h-2">
                            <div class="bg-purple-600 h-2 rounded-full" style="width: 100%"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Acciones Rápidas -->
        <div class="bg-white overflow-hidden shadow-sm rounded-lg">
            <div class="p-6">
                <h3 class="text-lg font-semibold text-gray-800 mb-4">Acciones</h3>
                
                <div class="space-y-2">
                    <button onclick="window.print()" 
                            class="w-full bg-gray-100 hover:bg-gray-200 text-gray-800 font-semibold py-2 px-4 rounded inline-flex items-center justify-center">
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"></path>
                        </svg>
                        Imprimir
                    </button>

                    <a href="{{ route('compras.index') }}" 
                       class="w-full bg-blue-100 hover:bg-blue-200 text-blue-800 font-semibold py-2 px-4 rounded inline-flex items-center justify-center">
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 10h16M4 14h16M4 18h16"></path>
                        </svg>
                        Ver Todas las Compras
                    </a>
                </div>
            </div>
        </div>

    </div>
</div>

<!-- Modal Anular -->
<div id="modalAnular" class="hidden fixed z-50 inset-0 overflow-y-auto">
    <div class="flex items-center justify-center min-h-screen px-4">
        <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity"></div>
        
        <div class="relative bg-white rounded-lg max-w-lg w-full p-6">
            <h3 class="text-lg font-medium text-gray-900 mb-4">Anular Compra #{{ $compra->id }}</h3>
            
            <form id="formAnular" action="{{ route('compras.anular', $compra) }}" method="POST">
                @csrf
                
                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        Motivo de Anulación <span class="text-red-500">*</span>
                    </label>
                    <textarea name="motivo" 
                              rows="3" 
                              required
                              class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                              placeholder="Ingrese el motivo de la anulación..."></textarea>
                </div>

                <div class="bg-yellow-50 border-l-4 border-yellow-400 p-4 mb-4">
                    <div class="flex">
                        <svg class="h-5 w-5 text-yellow-400" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                        </svg>
                        <div class="ml-3">
                            <p class="text-sm text-yellow-700">
                                Esta acción anulará la compra y revertirá el stock de los productos.
                            </p>
                        </div>
                    </div>
                </div>

                <div class="flex justify-end space-x-3">
                    <button type="button" 
                            onclick="cerrarModalAnular()"
                            class="bg-gray-300 hover:bg-gray-400 text-gray-800 font-bold py-2 px-4 rounded">
                        Cancelar
                    </button>
                    <button type="submit" 
                            class="bg-red-500 hover:bg-red-700 text-white font-bold py-2 px-4 rounded">
                        Confirmar Anulación
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

@push('scripts')
<script>
function modalAnular(compraId) {
    document.getElementById('modalAnular').classList.remove('hidden');
}

function cerrarModalAnular() {
    document.getElementById('modalAnular').classList.add('hidden');
}
</script>
@endpush
@endsection