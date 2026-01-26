@extends('layouts.app')

@section('title', 'Gestión de Lotes')

@section('header')
    <div class="flex justify-between items-center">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Gestión de Lotes
        </h2>
        <a href="{{ route('inventario.index') }}" class="bg-gray-500 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded inline-flex items-center">
            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
            </svg>
            Volver
        </a>
    </div>
@endsection

@section('content')
<div class="bg-white overflow-hidden shadow-sm rounded-lg">
    
    <!-- Filtros -->
    <div class="p-6 border-b border-gray-200 bg-gray-50">
        <form method="GET" action="{{ route('inventario.lotes') }}" class="grid grid-cols-1 md:grid-cols-4 gap-4">
            
            <!-- Producto -->
            <div>
                <label class="block text-xs font-medium text-gray-700 mb-1">Producto</label>
                <select name="producto_id" class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 text-sm">
                    <option value="">Todos los productos</option>
                    @foreach($productos as $producto)
                        <option value="{{ $producto->id }}" {{ request('producto_id') == $producto->id ? 'selected' : '' }}>
                            {{ $producto->nombre }}
                        </option>
                    @endforeach
                </select>
            </div>

            <!-- Estado -->
            <div>
                <label class="block text-xs font-medium text-gray-700 mb-1">Estado</label>
                <select name="estado" class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 text-sm">
                    <option value="">Todos</option>
                    <option value="disponible" {{ request('estado') == 'disponible' ? 'selected' : '' }}>Disponible</option>
                    <option value="proximo_vencer" {{ request('estado') == 'proximo_vencer' ? 'selected' : '' }}>Próximo a Vencer</option>
                    <option value="vencido" {{ request('estado') == 'vencido' ? 'selected' : '' }}>Vencido</option>
                </select>
            </div>

            <!-- Activo -->
            <div>
                <label class="block text-xs font-medium text-gray-700 mb-1">Activo</label>
                <select name="activo" class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 text-sm">
                    <option value="">Todos</option>
                    <option value="1" {{ request('activo') === '1' ? 'selected' : '' }}>Sí</option>
                    <option value="0" {{ request('activo') === '0' ? 'selected' : '' }}>No</option>
                </select>
            </div>

            <!-- Botones -->
            <div class="flex items-end space-x-2">
                <button type="submit" class="flex-1 bg-gray-500 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded text-sm">
                    Filtrar
                </button>
                @if(request()->hasAny(['producto_id', 'estado', 'activo']))
                <a href="{{ route('inventario.lotes') }}" class="bg-gray-300 hover:bg-gray-400 text-gray-800 font-bold py-2 px-4 rounded text-sm">
                    Limpiar
                </a>
                @endif
            </div>
        </form>
    </div>

    <!-- Tabla de Lotes -->
    <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Producto</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">N° Lote</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Proveedor</th>
                    <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase">Stock</th>
                    <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase">Vencimiento</th>
                    <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase">Estado</th>
                    <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase">Acciones</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                @forelse($lotes as $lote)
                <tr class="hover:bg-gray-50 {{ !$lote->activo ? 'opacity-60' : '' }}">
                    
                    <!-- Producto -->
                    <td class="px-6 py-4">
                        <div class="text-sm font-medium text-gray-900">{{ $lote->producto->nombre }}</div>
                        <div class="text-xs text-gray-500">{{ $lote->producto->categoria->nombre }}</div>
                    </td>

                    <!-- N° Lote -->
                    <td class="px-6 py-4 whitespace-nowrap">
                        <span class="text-sm font-mono font-semibold text-gray-900">{{ $lote->numero_lote }}</span>
                    </td>

                    <!-- Proveedor -->
                    <td class="px-6 py-4">
                        <div class="text-sm text-gray-900">{{ $lote->proveedor->nombre }}</div>
                        @if($lote->compra)
                        <div class="text-xs text-gray-500">Compra #{{ $lote->compra_id }}</div>
                        @endif
                    </td>

                    <!-- Stock -->
                    <td class="px-6 py-4 whitespace-nowrap text-center">
                        <div class="text-sm">
                            <span class="font-bold {{ $lote->cantidad_actual == 0 ? 'text-red-600' : 'text-gray-900' }}">
                                {{ $lote->cantidad_actual }}
                            </span>
                            <span class="text-gray-500">/ {{ $lote->cantidad_inicial }}</span>
                        </div>
                        <div class="w-full bg-gray-200 rounded-full h-1.5 mt-1">
                            <div class="bg-blue-600 h-1.5 rounded-full" 
                                 style="width: {{ ($lote->cantidad_actual / $lote->cantidad_inicial) * 100 }}%"></div>
                        </div>
                    </td>

                    <!-- Vencimiento -->
                    <td class="px-6 py-4 whitespace-nowrap text-center">
                        <div class="text-sm text-gray-900">{{ $lote->fecha_vencimiento->format('d/m/Y') }}</div>
                        <div class="text-xs {{ $lote->estaVencido() ? 'text-red-600' : ($lote->proximoVencer(30) ? 'text-orange-600' : 'text-gray-500') }}">
                            {{ $lote->fecha_vencimiento->diffForHumans() }}
                        </div>
                    </td>

                    <!-- Estado -->
                    <td class="px-6 py-4 whitespace-nowrap text-center">
                        @if($lote->estado == 'disponible')
                            <span class="px-2 py-1 text-xs rounded-full bg-green-100 text-green-800 font-semibold">Disponible</span>
                        @elseif($lote->estado == 'agotado')
                            <span class="px-2 py-1 text-xs rounded-full bg-gray-100 text-gray-800 font-semibold">Agotado</span>
                        @elseif($lote->estado == 'vencido')
                            <span class="px-2 py-1 text-xs rounded-full bg-red-100 text-red-800 font-semibold">Vencido</span>
                        @endif
                    </td>

                    <!-- Acciones -->
                    <td class="px-6 py-4 whitespace-nowrap text-center">
                        <div class="flex justify-center space-x-2">
                            <a href="{{ route('inventario.show-lote', $lote) }}" 
                               class="text-blue-600 hover:text-blue-900" 
                               title="Ver detalles">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                                </svg>
                            </a>
                            <a href="{{ route('inventario.kardex-lote', $lote) }}" 
                               class="text-purple-600 hover:text-purple-900"
                               title="Ver kardex">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                                </svg>
                            </a>
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="7" class="px-6 py-12 text-center text-gray-500">
                        No se encontraron lotes
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <!-- Paginación -->
    <div class="px-6 py-4 border-t border-gray-200">
        {{ $lotes->links() }}
    </div>
</div>

<!-- Estadísticas -->
<div class="mt-6 grid grid-cols-1 md:grid-cols-4 gap-6">
    <div class="bg-white overflow-hidden shadow-sm rounded-lg p-6">
        <div class="text-sm font-medium text-gray-500">Total Lotes</div>
        <div class="text-2xl font-semibold text-gray-900">{{ $lotes->total() }}</div>
    </div>

    <div class="bg-white overflow-hidden shadow-sm rounded-lg p-6">
        <div class="text-sm font-medium text-gray-500">Disponibles</div>
        <div class="text-2xl font-semibold text-green-600">
            {{ $lotes->where('estado', 'disponible')->count() }}
        </div>
    </div>

    <div class="bg-white overflow-hidden shadow-sm rounded-lg p-6">
        <div class="text-sm font-medium text-gray-500">Agotados</div>
        <div class="text-2xl font-semibold text-gray-600">
            {{ $lotes->where('estado', 'agotado')->count() }}
        </div>
    </div>

    <div class="bg-white overflow-hidden shadow-sm rounded-lg p-6">
        <div class="text-sm font-medium text-gray-500">Vencidos</div>
        <div class="text-2xl font-semibold text-red-600">
            {{ $lotes->where('estado', 'vencido')->count() }}
        </div>
    </div>
</div>
@endsection