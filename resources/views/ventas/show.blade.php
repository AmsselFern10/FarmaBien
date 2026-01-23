@extends('layouts.app')

@section('title', 'Detalle de Venta')

@section('header')
    <div class="flex justify-between items-center">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Venta #{{ $venta->id }}
        </h2>
        <div class="flex space-x-2">
            <a href="{{ route('ventas.imprimir', $venta) }}" target="_blank"
               class="bg-green-500 hover:bg-green-700 text-white font-bold py-2 px-4 rounded inline-flex items-center">
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"></path>
                </svg>
                Imprimir
            </a>
            <a href="{{ route('ventas.index') }}" class="bg-gray-300 hover:bg-gray-400 text-gray-800 font-bold py-2 px-4 rounded">
                Volver
            </a>
        </div>
    </div>
@endsection

@section('content')
<div class="space-y-6">
    <!-- Estado de la Venta -->
    @if($venta->estado === 'anulada')
    <div class="bg-red-50 border-l-4 border-red-400 p-4 rounded">
        <div class="flex">
            <div class="flex-shrink-0">
                <svg class="h-5 w-5 text-red-400" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"></path>
                </svg>
            </div>
            <div class="ml-3">
                <h3 class="text-sm font-medium text-red-800">Venta Anulada</h3>
                <div class="mt-2 text-sm text-red-700">
                    <p><strong>Motivo:</strong> {{ $venta->motivo_anulacion }}</p>
                    <p><strong>Anulada por:</strong> {{ $venta->anuladoPor->name }}</p>
                    <p><strong>Fecha:</strong> {{ $venta->fecha_anulacion->format('d/m/Y H:i') }}</p>
                </div>
            </div>
        </div>
    </div>
    @endif

    <!-- Información de Modificación -->
    @if($venta->esModificacion() || $venta->fueModificada())
    <div class="bg-blue-50 border-l-4 border-blue-400 p-4 rounded">
        <div class="flex">
            <div class="flex-shrink-0">
                <svg class="h-5 w-5 text-blue-400" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"></path>
                </svg>
            </div>
            <div class="ml-3">
                <h3 class="text-sm font-medium text-blue-800">Información de Modificación</h3>
                <div class="mt-2 text-sm text-blue-700">
                    @if($venta->esModificacion())
                        <p>Esta venta es una modificación de la <a href="{{ route('ventas.show', $venta->ventaOriginal) }}" class="underline">Venta #{{ $venta->venta_original_id }}</a></p>
                    @endif
                    @if($venta->fueModificada())
                        <p>Esta venta fue modificada. Ver <a href="{{ route('ventas.show', $venta->reemplazadaPor) }}" class="underline">Venta #{{ $venta->reemplazada_por }}</a></p>
                    @endif
                </div>
            </div>
        </div>
    </div>
    @endif

    <!-- Información General -->
    <div class="bg-white overflow-hidden shadow-sm rounded-lg">
        <div class="px-6 py-4 border-b border-gray-200 bg-gray-50">
            <h3 class="text-lg font-semibold text-gray-900">Información General</h3>
        </div>
        <div class="p-6">
            <dl class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
                <div>
                    <dt class="text-sm font-medium text-gray-500">Fecha</dt>
                    <dd class="mt-1 text-sm text-gray-900">{{ $venta->fecha->format('d/m/Y H:i') }}</dd>
                </div>
                <div>
                    <dt class="text-sm font-medium text-gray-500">Cliente</dt>
                    <dd class="mt-1 text-sm text-gray-900">
                        @if($venta->cliente)
                            <a href="{{ route('clientes.show', $venta->cliente) }}" class="text-blue-600 hover:text-blue-800">
                                {{ $venta->cliente->nombre }}
                            </a>
                        @else
                            Público General
                        @endif
                    </dd>
                </div>
                <div>
                    <dt class="text-sm font-medium text-gray-500">Vendedor</dt>
                    <dd class="mt-1 text-sm text-gray-900">{{ $venta->usuario->name }}</dd>
                </div>
                <div>
                    <dt class="text-sm font-medium text-gray-500">Método de Pago</dt>
                    <dd class="mt-1">
                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-blue-100 text-blue-800">
                            {{ ucfirst($venta->metodo_pago) }}
                        </span>
                    </dd>
                </div>
            </dl>
        </div>
    </div>

    <!-- Productos Vendidos -->
    <div class="bg-white overflow-hidden shadow-sm rounded-lg">
        <div class="px-6 py-4 border-b border-gray-200 bg-gray-50">
            <h3 class="text-lg font-semibold text-gray-900">Productos Vendidos</h3>
        </div>
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Producto</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Lote</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Vencimiento</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Cantidad</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">P. Unitario</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Subtotal</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @foreach($venta->detalles as $detalle)
                    <tr>
                        <td class="px-6 py-4">
                            <div class="text-sm font-medium text-gray-900">{{ $detalle->producto->nombre }}</div>
                            <div class="text-sm text-gray-500">{{ $detalle->producto->categoria->nombre }}</div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                            {{ $detalle->lote->numero_lote }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                            {{ $detalle->lote->fecha_vencimiento->format('d/m/Y') }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 text-right">
                            {{ $detalle->cantidad }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 text-right">
                            S/ {{ number_format($detalle->precio_unitario, 2) }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-semibold text-gray-900 text-right">
                            S/ {{ number_format($detalle->subtotal, 2) }}
                        </td>
                    </tr>
                    @endforeach
                </tbody>
                <tfoot class="bg-gray-50">
                    <tr>
                        <td colspan="5" class="px-6 py-4 text-right text-lg font-bold text-gray-900">
                            TOTAL:
                        </td>
                        <td class="px-6 py-4 text-right text-2xl font-bold text-blue-600">
                            S/ {{ number_format($venta->total, 2) }}
                        </td>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>

    <!-- Recetas Médicas -->
    @if($venta->recetas->isNotEmpty())
    <div class="bg-white overflow-hidden shadow-sm rounded-lg">
        <div class="px-6 py-4 border-b border-gray-200 bg-gray-50">
            <h3 class="text-lg font-semibold text-gray-900">Recetas Médicas Asociadas</h3>
        </div>
        <div class="p-6">
            @foreach($venta->recetas as $receta)
            <div class="mb-4 last:mb-0 p-4 bg-gray-50 rounded-lg">
                <dl class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div>
                        <dt class="text-sm font-medium text-gray-500">Número de Receta</dt>
                        <dd class="mt-1 text-sm text-gray-900">{{ $receta->numero_receta }}</dd>
                    </div>
                    <div>
                        <dt class="text-sm font-medium text-gray-500">Médico</dt>
                        <dd class="mt-1 text-sm text-gray-900">{{ $receta->medico }}</dd>
                    </div>
                    <div>
                        <dt class="text-sm font-medium text-gray-500">Fecha de Receta</dt>
                        <dd class="mt-1 text-sm text-gray-900">{{ $receta->fecha->format('d/m/Y') }}</dd>
                    </div>
                </dl>
            </div>
            @endforeach
        </div>
    </div>
    @endif

    <!-- Historial de Modificaciones -->
    @if($historial && $historial->count() > 1)
    <div class="bg-white overflow-hidden shadow-sm rounded-lg">
        <div class="px-6 py-4 border-b border-gray-200 bg-gray-50">
            <h3 class="text-lg font-semibold text-gray-900">Historial de Modificaciones</h3>
        </div>
        <div class="p-6">
            <div class="flow-root">
                <ul class="-mb-8">
                    @foreach($historial as $version)
                    <li>
                        <div class="relative pb-8">
                            @if(!$loop->last)
                            <span class="absolute top-4 left-4 -ml-px h-full w-0.5 bg-gray-200"></span>
                            @endif
                            <div class="relative flex space-x-3">
                                <div>
                                    <span class="h-8 w-8 rounded-full {{ $version['es_activa'] ? 'bg-green-500' : 'bg-gray-400' }} flex items-center justify-center ring-8 ring-white">
                                        <span class="text-white text-xs font-bold">V{{ $version['version'] }}</span>
                                    </span>
                                </div>
                                <div class="flex-1 min-w-0">
                                    <div class="flex justify-between items-start">
                                        <div>
                                            <a href="{{ route('ventas.show', $version['id']) }}" class="text-sm font-medium text-blue-600 hover:text-blue-800">
                                                Venta #{{ $version['id'] }}
                                            </a>
                                            @if($version['es_activa'])
                                                <span class="ml-2 px-2 py-1 text-xs rounded-full bg-green-100 text-green-800">Actual</span>
                                            @endif
                                            @if($version['es_original'])
                                                <span class="ml-2 px-2 py-1 text-xs rounded-full bg-blue-100 text-blue-800">Original</span>
                                            @endif
                                        </div>
                                        <p class="text-sm text-gray-500">{{ $version['fecha']->format('d/m/Y H:i') }}</p>
                                    </div>
                                    <p class="mt-1 text-sm text-gray-600">
                                        Total: S/ {{ number_format($version['total'], 2) }} | 
                                        Usuario: {{ $version['usuario'] }}
                                    </p>
                                    @if($version['motivo_anulacion'])
                                    <p class="mt-1 text-sm text-red-600">{{ $version['motivo_anulacion'] }}</p>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </li>
                    @endforeach
                </ul>
            </div>
        </div>
    </div>
    @endif

    <!-- Acciones -->
    @if($venta->estado === 'completada' && !$venta->fueModificada())
    <div class="flex justify-end space-x-2">
        @can('anular ventas')
        <button onclick="document.getElementById('modalAnular').classList.remove('hidden')" 
                class="bg-red-500 hover:bg-red-700 text-white font-bold py-2 px-4 rounded">
            Anular Venta
        </button>
        @endcan

        @can('anular ventas')
        <a href="{{ route('ventas.edit', $venta) }}" 
           class="bg-yellow-500 hover:bg-yellow-700 text-white font-bold py-2 px-4 rounded">
            Modificar Venta
        </a>
        @endcan
    </div>
    @endif
</div>

<!-- Modal de Anulación -->
<div id="modalAnular" class="hidden fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50">
    <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white">
        <h3 class="text-lg font-bold text-gray-900 mb-4">Anular Venta #{{ $venta->id }}</h3>
        <form action="{{ route('ventas.anular', $venta) }}" method="POST">
            @csrf
            <div class="mb-4">
                <label for="motivo" class="block text-sm font-medium text-gray-700 mb-2">
                    Motivo de anulación <span class="text-red-500">*</span>
                </label>
                <textarea id="motivo" name="motivo" rows="3" required
                          class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                          placeholder="Explique el motivo de la anulación..."></textarea>
            </div>
            <div class="flex justify-end space-x-2">
                <button type="button" onclick="document.getElementById('modalAnular').classList.add('hidden')" 
                        class="bg-gray-300 hover:bg-gray-400 text-gray-800 font-bold py-2 px-4 rounded">
                    Cancelar
                </button>
                <button type="submit" 
                        class="bg-red-500 hover:bg-red-700 text-white font-bold py-2 px-4 rounded">
                    Anular Venta
                </button>
            </div>
        </form>
    </div>
</div>
@endsection