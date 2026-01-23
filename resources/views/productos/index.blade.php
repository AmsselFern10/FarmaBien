@extends('layouts.app')

@section('title', 'Productos')

@section('header')
    <div class="flex justify-between items-center">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Productos
        </h2>
        @can('crear productos')
        <a href="{{ route('productos.create') }}" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded inline-flex items-center">
            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
            </svg>
            Nuevo Producto
        </a>
        @endcan
    </div>
@endsection

@section('content')
<div class="bg-white overflow-hidden shadow-sm rounded-lg">
    <!-- Filtros -->
    <div class="p-6 border-b border-gray-200 bg-gray-50">
        <form method="GET" action="{{ route('productos.index') }}" class="grid grid-cols-1 md:grid-cols-4 gap-4">
            <!-- Búsqueda -->
            <div class="md:col-span-2">
                <input type="text" name="buscar" placeholder="Buscar por nombre o código..."
                       value="{{ request('buscar') }}"
                       class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
            </div>

            <!-- Categoría -->
            <div>
                <select name="categoria_id" class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                    <option value="">Todas las categorías</option>
                    @foreach($categorias as $categoria)
                        <option value="{{ $categoria->id }}" {{ request('categoria_id') == $categoria->id ? 'selected' : '' }}>
                            {{ $categoria->nombre }}
                        </option>
                    @endforeach
                </select>
            </div>

            <!-- Botones -->
            <div class="flex space-x-2">
                <button type="submit" class="flex-1 bg-gray-500 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded">
                    Filtrar
                </button>
                @if(request()->hasAny(['buscar', 'categoria_id']))
                <a href="{{ route('productos.index') }}" class="bg-gray-300 hover:bg-gray-400 text-gray-800 font-bold py-2 px-4 rounded">
                    Limpiar
                </a>
                @endif
            </div>
        </form>
    </div>

    <!-- Grid de Productos -->
    <div class="p-6">
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
            @forelse($productos as $producto)
            <div class="bg-white border border-gray-200 rounded-lg overflow-hidden hover:shadow-lg transition-shadow">
                <!-- Imagen -->
                <div class="h-48 bg-gray-100 flex items-center justify-center overflow-hidden">
                    @if($producto->imagen)
                        <img src="{{ asset('storage/' . $producto->imagen) }}" 
                             alt="{{ $producto->nombre }}"
                             class="w-full h-full object-cover">
                    @else
                        <svg class="w-20 h-20 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path>
                        </svg>
                    @endif
                </div>

                <!-- Contenido -->
                <div class="p-4">
                    <div class="flex justify-between items-start mb-2">
                        <h3 class="font-semibold text-gray-900 text-sm line-clamp-2 flex-1">
                            {{ $producto->nombre }}
                        </h3>
                        @if($producto->activo)
                            <span class="ml-2 px-2 py-1 text-xs rounded-full bg-green-100 text-green-800">Activo</span>
                        @else
                            <span class="ml-2 px-2 py-1 text-xs rounded-full bg-red-100 text-red-800">Inactivo</span>
                        @endif
                    </div>

                    <p class="text-xs text-gray-500 mb-2">{{ $producto->categoria->nombre }}</p>

                    @if($producto->codigo_barra)
                    <p class="text-xs text-gray-400 mb-2">Cód: {{ $producto->codigo_barra }}</p>
                    @endif

                    <div class="flex justify-between items-center mb-3">
                        <span class="text-2xl font-bold text-blue-600">S/ {{ number_format($producto->precio_venta, 2) }}</span>
                        @if($producto->requiere_receta)
                            <span class="px-2 py-1 text-xs rounded bg-orange-100 text-orange-800">Receta</span>
                        @endif
                    </div>

                    <div class="text-xs text-gray-600 mb-3">
                        <p>Stock: <strong>{{ $producto->stock_total }}</strong> unidades</p>
                        <p>Mínimo: {{ $producto->stock_minimo }}</p>
                    </div>

                    <!-- Acciones -->
                    <div class="flex space-x-2">
                        @can('ver productos')
                        <a href="{{ route('productos.show', $producto) }}" 
                           class="flex-1 bg-blue-500 hover:bg-blue-700 text-white text-xs font-bold py-2 px-3 rounded text-center">
                            Ver
                        </a>
                        @endcan

                        @can('editar productos')
                        <a href="{{ route('productos.edit', $producto) }}" 
                           class="flex-1 bg-gray-500 hover:bg-gray-700 text-white text-xs font-bold py-2 px-3 rounded text-center">
                            Editar
                        </a>
                        @endcan
                    </div>
                </div>
            </div>
            @empty
            <div class="col-span-full text-center py-12 text-gray-500">
                <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path>
                </svg>
                <p class="mt-2">No se encontraron productos</p>
            </div>
            @endforelse
        </div>

        <!-- Paginación -->
        <div class="mt-6">
            {{ $productos->links() }}
        </div>
    </div>
</div>
@endsection