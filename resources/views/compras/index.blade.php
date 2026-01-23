
@extends('layouts.app')

@section('title', 'Compras')

@section('header')
    <div class="flex justify-between items-center">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Gestión de Compras
        </h2>
        @can('registrar compras')
        <a href="{{ route('compras.create') }}" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded inline-flex items-center">
            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
            </svg>
            Nueva Compra
        </a>
        @endcan
    </div>
@endsection

@section('content')
<div class="bg-white overflow-hidden shadow-sm rounded-lg">
    
    <!-- Filtros Avanzados -->
    <div class="p-6 border-b border-gray-200 bg-gray-50">
        <form method="GET" action="{{ route('compras.index') }}" class="space-y-4">
            
            <div class="grid grid-cols-1 md:grid-cols-5 gap-4">
                
                <!-- Estado -->
                <div>
                    <label class="block text-xs font-medium text-gray-700 mb-1">Estado</label>
                    <select name="estado" class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 text-sm">
                        <option value="">Todos los estados</option>
                        <option value="completada" {{ request('estado') == 'completada' ? 'selected' : '' }}>Completada</option>
                        <option value="anulada" {{ request('estado') == 'anulada' ? 'selected' : '' }}>Anulada</option>
                        <option value="modificada" {{ request('estado') == 'modificada' ? 'selected' : '' }}>Modificada</option>
                    </select>
                </div>

                <!-- Proveedor -->
                <div>
                    <label class="block text-xs font-medium text-gray-700 mb-1">Proveedor</label>
                    <select name="proveedor_id" class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 text-sm">
                        <option value="">Todos los proveedores</option>
                        @foreach($proveedores as $proveedor)
                            <option value="{{ $proveedor->id }}" {{ request('proveedor_id') == $proveedor->id ? 'selected' : '' }}>
                                {{ $proveedor->nombre }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <!-- Fecha Inicio -->
                <div>
                    <label class="block text-xs font-medium text-gray-700 mb-1">Fecha Inicio</label>
                    <input type="date" 
                           name="fecha_inicio"
                           value="{{ request('fecha_inicio') }}"
                           class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 text-sm">
                </div>

                <!-- Fecha Fin -->
                <div>
                    <label class="block text-xs font-medium text-gray-700 mb-1">Fecha Fin</label>
                    <input type="date" 
                           name="fecha_fin"
                           value="{{ request('fecha_fin') }}"
                           class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 text-sm">
                </div>

                <!-- Botones -->
                <div class="flex items-end space-x-2">
                    <button type="submit" class="flex-1 bg-gray-500 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded text-sm">
                        Filtrar
                    </button>
                    @if(request()->hasAny(['estado', 'proveedor_id', 'fecha_inicio', 'fecha_fin']))
                    <a href="{{ route('compras.index') }}" 
                       class="bg-gray-300 hover:bg-gray-400 text-gray-800 font-bold py-2 px-4 rounded text-sm">
                        Limpiar
                    </a>
                    @endif
                </div>
            </div>

        </form>
    </div>

    <!-- Tabla de Compras -->
    <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                        ID / Fecha
                    </th>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                        Proveedor
                    </th>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                        Tipo Comprobante
                    </th>
                    <th scope="col" class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                        Subtotal
                    </th>
                    <th scope="col" class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                        IGV (18%)
                    </th>
                    <th scope="col" class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                        Total
                    </th>
                    <th scope="col" class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">
                        Estado
                    </th>
                    <th scope="col" class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">
                        Acciones
                    </th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                @forelse($compras as $compra)
                <tr class="hover:bg-gray-50 {{ $compra->estado == 'anulada' ? 'opacity-60' : '' }}">
                    
                    <!-- ID / Fecha -->
                    <td class="px-6 py-4 whitespace-nowrap">
                        <div class="text-sm font-medium text-gray-900">
                            #{{ $compra->id }}
                        </div>
                        <div class="text-xs text-gray-500">
                            {{ $compra->fecha->format('d/m/Y') }}
                        </div>
                        @if($compra->compra_original_id)
                        <div class="text-xs text-purple-600 mt-1">
                            <svg class="w-3 h-3 inline" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16V4m0 0L3 8m4-4l4 4m6 0v12m0 0l4-4m-4 4l-4-4"></path>
                            </svg>
                            Modifica #{{ $compra->compra_original_id }}
                        </div>
                        @endif
                    </td>

                    <!-- Proveedor -->
                    <td class="px-6 py-4">
                        <div class="text-sm font-medium text-gray-900">
                            {{ $compra->proveedor->nombre }}
                        </div>
                        <div class="text-xs text-gray-500">
                            RUC: {{ $compra->proveedor->ruc }}
                        </div>
                    </td>

                    <!-- Tipo Comprobante -->
                    <td class="px-6 py-4 whitespace-nowrap">
                        <div class="text-sm text-gray-900">
                            {{ strtoupper($compra->tipo_comprobante) }}
                        </div>
                        <div class="text-xs text-gray-500">
                            {{ $compra->numero_comprobante }}
                        </div>
                    </td>

                    <!-- Subtotal -->
                    <td class="px-6 py-4 whitespace-nowrap text-right text-sm text-gray-900">
                        S/ {{ number_format($compra->subtotal, 2) }}
                    </td>

                    <!-- IGV -->
                    <td class="px-6 py-4 whitespace-nowrap text-right text-sm text-gray-900">
                        S/ {{ number_format($compra->igv, 2) }}
                    </td>

                    <!-- Total -->
                    <td class="px-6 py-4 whitespace-nowrap text-right">
                        <div class="text-sm font-bold text-gray-900">
                            S/ {{ number_format($compra->total, 2) }}
                        </div>
                    </td>

                    <!-- Estado -->
                    <td class="px-6 py-4 whitespace-nowrap text-center">
                        @if($compra->estado == 'completada')
                            <span class="px-2 py-1 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">
                                Completada
                            </span>
                        @elseif($compra->estado == 'anulada')
                            <span class="px-2 py-1 inline-flex text-xs leading-5 font-semibold rounded-full bg-red-100 text-red-800">
                                Anulada
                            </span>
                        @elseif($compra->estado == 'modificada')
                            <span class="px-2 py-1 inline-flex text-xs leading-5 font-semibold rounded-full bg-purple-100 text-purple-800">
                                Modificada
                            </span>
                        @endif
                    </td>

                    <!-- Acciones -->
                    <td class="px-6 py-4 whitespace-nowrap text-center text-sm font-medium">
                        <div class="flex justify-center space-x-2">
                            @can('ver compras')
                            <a href="{{ route('compras.show', $compra) }}" 
                               class="text-blue-600 hover:text-blue-900" 
                               title="Ver detalles">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                                </svg>
                            </a>
                            @endcan

                            @can('anular compras')
                                @if($compra->puedeModificarse())
                                <a href="{{ route('compras.edit', $compra) }}" 
                                   class="text-yellow-600 hover:text-yellow-900"
                                   title="Modificar">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                                    </svg>
                                </a>

                                <button onclick="modalAnular({{ $compra->id }})"
                                        class="text-red-600 hover:text-red-900"
                                        title="Anular">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                                    </svg>
                                </button>
                                @endif
                            @endcan
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="8" class="px-6 py-12 text-center">
                        <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                        </svg>
                        <p class="mt-2 text-gray-500">No se encontraron compras</p>
                        @can('registrar compras')
                        <a href="{{ route('compras.create') }}" class="mt-4 inline-block text-blue-600 hover:text-blue-800">
                            Registrar primera compra
                        </a>
                        @endcan
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <!-- Paginación -->
    <div class="px-6 py-4 border-t border-gray-200">
        {{ $compras->links() }}
    </div>
</div>

<!-- Estadísticas -->
<div class="mt-6 grid grid-cols-1 md:grid-cols-4 gap-6">
    <div class="bg-white overflow-hidden shadow-sm rounded-lg p-6">
        <div class="flex items-center">
            <div class="flex-shrink-0 bg-blue-100 rounded-md p-3">
                <svg class="h-6 w-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                </svg>
            </div>
            <div class="ml-5 w-0 flex-1">
                <dl>
                    <dt class="text-sm font-medium text-gray-500 truncate">Total Compras</dt>
                    <dd class="text-lg font-semibold text-gray-900">{{ $compras->total() }}</dd>
                </dl>
            </div>
        </div>
    </div>

    <div class="bg-white overflow-hidden shadow-sm rounded-lg p-6">
        <div class="flex items-center">
            <div class="flex-shrink-0 bg-green-100 rounded-md p-3">
                <svg class="h-6 w-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
            </div>
            <div class="ml-5 w-0 flex-1">
                <dl>
                    <dt class="text-sm font-medium text-gray-500 truncate">Completadas</dt>
                    <dd class="text-lg font-semibold text-gray-900">
                        {{ $compras->where('estado', 'completada')->count() }}
                    </dd>
                </dl>
            </div>
        </div>
    </div>

    <div class="bg-white overflow-hidden shadow-sm rounded-lg p-6">
        <div class="flex items-center">
            <div class="flex-shrink-0 bg-red-100 rounded-md p-3">
                <svg class="h-6 w-6 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                </svg>
            </div>
            <div class="ml-5 w-0 flex-1">
                <dl>
                    <dt class="text-sm font-medium text-gray-500 truncate">Anuladas</dt>
                    <dd class="text-lg font-semibold text-gray-900">
                        {{ $compras->where('estado', 'anulada')->count() }}
                    </dd>
                </dl>
            </div>
        </div>
    </div>

    <div class="bg-white overflow-hidden shadow-sm rounded-lg p-6">
        <div class="flex items-center">
            <div class="flex-shrink-0 bg-purple-100 rounded-md p-3">
                <svg class="h-6 w-6 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
            </div>
            <div class="ml-5 w-0 flex-1">
                <dl>
                    <dt class="text-sm font-medium text-gray-500 truncate">Monto Total</dt>
                    <dd class="text-lg font-semibold text-gray-900">
                        S/ {{ number_format($compras->where('estado', 'completada')->sum('total'), 2) }}
                    </dd>
                </dl>
            </div>
        </div>
    </div>
</div>

<!-- Modal Anular Compra -->
<div id="modalAnular" class="hidden fixed z-50 inset-0 overflow-y-auto">
    <div class="flex items-center justify-center min-h-screen px-4">
        <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity"></div>
        
        <div class="relative bg-white rounded-lg max-w-lg w-full p-6">
            <h3 class="text-lg font-medium text-gray-900 mb-4">Anular Compra</h3>
            
            <form id="formAnular" method="POST">
                @csrf
                @method('POST')
                
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

                <div class="flex justify-end space-x-3">
                    <button type="button" 
                            onclick="cerrarModalAnular()"
                            class="bg-gray-300 hover:bg-gray-400 text-gray-800 font-bold py-2 px-4 rounded">
                        Cancelar
                    </button>
                    <button type="submit" 
                            class="bg-red-500 hover:bg-red-700 text-white font-bold py-2 px-4 rounded">
                        Anular Compra
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

@push('scripts')
<script>
function modalAnular(compraId) {
    const modal = document.getElementById('modalAnular');
    const form = document.getElementById('formAnular');
    form.action = `/compras/${compraId}/anular`;
    modal.classList.remove('hidden');
}

function cerrarModalAnular() {
    const modal = document.getElementById('modalAnular');
    modal.classList.add('hidden');
    document.getElementById('formAnular').reset();
}
</script>
@endpush
@endsection