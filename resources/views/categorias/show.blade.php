@extends('layouts.app')

@section('title', 'Detalle de Categoría')

@section('header')
    <div class="flex justify-between items-center">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Categoría: {{ $categoria->nombre }}
        </h2>
        <div class="flex space-x-2">
            @can('editar categorias')
            <a href="{{ route('categorias.edit', $categoria) }}" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
                Editar
            </a>
            @endcan
            <a href="{{ route('categorias.index') }}" class="bg-gray-300 hover:bg-gray-400 text-gray-800 font-bold py-2 px-4 rounded">
                Volver
            </a>
        </div>
    </div>
@endsection

@section('content')
<div class="space-y-6">
    <!-- Información de la Categoría -->
    <div class="bg-white overflow-hidden shadow-sm rounded-lg">
        <div class="px-6 py-4 border-b border-gray-200">
            <h3 class="text-lg font-semibold text-gray-900">Información General</h3>
        </div>
        <div class="p-6">
            <dl class="grid grid-cols-1 gap-x-4 gap-y-6 sm:grid-cols-2">
                <div>
                    <dt class="text-sm font-medium text-gray-500">ID</dt>
                    <dd class="mt-1 text-sm text-gray-900">{{ $categoria->id }}</dd>
                </div>
                <div>
                    <dt class="text-sm font-medium text-gray-500">Estado</dt>
                    <dd class="mt-1">
                        @if($categoria->activo)
                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">
                                Activo
                            </span>
                        @else
                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-red-100 text-red-800">
                                Inactivo
                            </span>
                        @endif
                    </dd>
                </div>
                <div>
                    <dt class="text-sm font-medium text-gray-500">Nombre</dt>
                    <dd class="mt-1 text-sm text-gray-900">{{ $categoria->nombre }}</dd>
                </div>
                <div>
                    <dt class="text-sm font-medium text-gray-500">Total de Productos</dt>
                    <dd class="mt-1 text-sm text-gray-900">{{ $categoria->productos->count() }}</dd>
                </div>
                <div class="sm:col-span-2">
                    <dt class="text-sm font-medium text-gray-500">Descripción</dt>
                    <dd class="mt-1 text-sm text-gray-900">{{ $categoria->descripcion ?? 'Sin descripción' }}</dd>
                </div>
                <div>
                    <dt class="text-sm font-medium text-gray-500">Creado</dt>
                    <dd class="mt-1 text-sm text-gray-900">{{ $categoria->created_at->format('d/m/Y H:i') }}</dd>
                </div>
                <div>
                    <dt class="text-sm font-medium text-gray-500">Última Actualización</dt>
                    <dd class="mt-1 text-sm text-gray-900">{{ $categoria->updated_at->format('d/m/Y H:i') }}</dd>
                </div>
            </dl>
        </div>
    </div>

    <!-- Productos de la Categoría -->
    <div class="bg-white overflow-hidden shadow-sm rounded-lg">
        <div class="px-6 py-4 border-b border-gray-200">
            <h3 class="text-lg font-semibold text-gray-900">Productos en esta Categoría</h3>
        </div>
        <div class="p-6">
            @if($categoria->productos->isNotEmpty())
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Nombre</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Código</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Precio</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Estado</th>
                                <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Acciones</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @foreach($categoria->productos as $producto)
                            <tr class="hover:bg-gray-50">
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                    {{ $producto->nombre }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    {{ $producto->codigo_barra ?? 'N/A' }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                    S/ {{ number_format($producto->precio_venta, 2) }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    @if($producto->activo)
                                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">
                                            Activo
                                        </span>
                                    @else
                                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-red-100 text-red-800">
                                            Inactivo
                                        </span>
                                    @endif
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-right text-sm">
                                    <a href="{{ route('productos.show', $producto) }}" class="text-blue-600 hover:text-blue-900">
                                        Ver detalles
                                    </a>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <p class="text-gray-500 text-center py-4">No hay productos en esta categoría</p>
            @endif
        </div>
    </div>
</div>
@endsection