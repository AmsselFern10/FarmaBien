@extends('layouts.app')

@section('title', 'Ventas')

@section('header')
    <div class="flex justify-between items-center">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Historial de Ventas
        </h2>
        @can('realizar ventas')
        <a href="{{ route('ventas.create') }}" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded inline-flex items-center">
            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
            </svg>
            Nueva Venta
        </a>
        @endcan
    </div>
@endsection

@section('content')
<div class="bg-white overflow-hidden shadow-sm rounded-lg">
    <!-- Filtros -->
    <div class="p-6 border-b border-gray-200 bg-gray-50">
        <form method="GET" action="{{ route('ventas.index') }}" class="grid grid-cols-1 md:grid-cols-4 gap-4">
            <!-- Estado -->
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Estado</label>
                <select name="estado" class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                    <option value="">Todos</option>
                    <option value="completada" {{ request('estado') === 'completada' ? 'selected' : '' }}>Completadas</option>
                    <option value="anulada" {{ request('estado') === 'anulada' ? 'selected' : '' }}>Anuladas</option>
                </select>
            </div>

            <!-- Fecha Inicio -->
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Fecha Inicio</label>
                <input type="date" name="fecha_inicio" value="{{ request('fecha_inicio') }}"
                       class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
            </div>

            <!-- Fecha Fin -->
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Fecha Fin</label>
                <input type="date" name="fecha_fin" value="{{ request('fecha_fin') }}"
                       class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
            </div>

            <!-- Botones -->
            <div class="flex items-end space-x-2">
                <button type="submit" class="flex-1 bg-gray-500 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded">
                    Filtrar
                </button>
                @if(request()->hasAny(['estado', 'fecha_inicio', 'fecha_fin']))
                <a href="{{ route('ventas.index') }}" class="bg-gray-300 hover:bg-gray-400 text-gray-800 font-bold py-2 px-4 rounded">
                    Limpiar
                </a>
                @endif
            </div>
        </form>
    </div>

    <!-- Tabla -->
    <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">ID</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Fecha</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Cliente</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Vendedor</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Total</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Método Pago</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Estado</th>
                    <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Acciones</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                @forelse($ventas as $venta)
                <tr class="hover:bg-gray-50 {{ $venta->estado === 'anulada' ? 'opacity-60' : '' }}">
                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                        #{{ $venta->id }}
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                        {{ $venta->fecha->format('d/m/Y H:i') }}
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                        {{ $venta->cliente ? $venta->cliente->nombre : 'Público General' }}
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                        {{ $venta->usuario->name }}
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm font-semibold {{ $venta->estado === 'anulada' ? 'text-red-600 line-through' : 'text-gray-900' }}">
                        S/ {{ number_format($venta->total, 2) }}
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-blue-100 text-blue-800">
                            {{ ucfirst($venta->metodo_pago) }}
                        </span>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        @if($venta->estado === 'completada')
                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">
                                Completada
                            </span>
                        @else
                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-red-100 text-red-800">
                                Anulada
                            </span>
                        @endif
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                        <div class="flex justify-end space-x-2">
                            <a href="{{ route('ventas.show', $venta) }}" class="text-blue-600 hover:text-blue-900" title="Ver detalle">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                                </svg>
                            </a>

                            @if($venta->estado === 'completada')
                                @can('anular ventas')
                                <button onclick="anularVenta({{ $venta->id }})" class="text-red-600 hover:text-red-900" title="Anular">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                                    </svg>
                                </button>
                                @endcan
                            @endif
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="8" class="px-6 py-4 text-center text-gray-500">
                        No se encontraron ventas
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <!-- Paginación -->
    <div class="px-6 py-4 border-t border-gray-200">
        {{ $ventas->links() }}
    </div>
</div>

<!-- Modal de Anulación -->
<div id="modalAnular" class="hidden fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50">
    <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white">
        <h3 class="text-lg font-bold text-gray-900 mb-4">Anular Venta</h3>
        <form id="formAnular" method="POST">
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
                <button type="button" onclick="cerrarModal()" 
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

@push('scripts')
<script>
function anularVenta(ventaId) {
    const modal = document.getElementById('modalAnular');
    const form = document.getElementById('formAnular');
    form.action = `/ventas/${ventaId}/anular`;
    modal.classList.remove('hidden');
}

function cerrarModal() {
    const modal = document.getElementById('modalAnular');
    modal.classList.add('hidden');
    document.getElementById('motivo').value = '';
}

// Cerrar modal al hacer clic fuera
document.getElementById('modalAnular')?.addEventListener('click', function(e) {
    if (e.target === this) {
        cerrarModal();
    }
});
</script>
@endpush