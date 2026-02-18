@extends('layouts.app')

@section('title', 'Reporte de Compras')

@section('header')
    Reporte de Compras
@endsection

@section('page-actions')
    <div>
        <h2 class="text-2xl font-bold text-slate-900 dark:text-white">Reporte de Compras 🧾</h2>
        <p class="text-sm text-slate-600 dark:text-slate-400 mt-1">Vista previa y exportación (PDF/Excel).</p>
    </div>
    <div class="flex gap-3">
        <a href="{{ route('reportes.index') }}"
           class="inline-flex items-center px-4 py-2 bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-600 text-slate-700 dark:text-slate-300 font-semibold rounded-lg hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors duration-200 shadow-sm">
            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
            Volver
        </a>
    </div>
@endsection

@push('styles')
<style>
    .table-excel th{position:sticky; top:0; z-index:10;}
</style>
@endpush

@section('content')
@php
    $moneda = 'S/';

    $prov = collect($comprasPorProveedor ?? []);
    $labelsProv = $prov->pluck('proveedor');
    $totalesProv = $prov->pluck('total');

    $cats = collect($comprasPorCategoria ?? [])->take(12);
    $labelsCat = $cats->pluck('categoria');
    $gastoCat = $cats->pluck('gasto');

    $topProd = collect($comprasPorProducto ?? [])->take(10);
    $labelsProd = $topProd->pluck('producto');
    $unidadesProd = $topProd->pluck('unidades');
@endphp

<div class="max-w-7xl mx-auto px-4 sm:px-1 lg:px-3 py-2">

    {{-- Filtros --}}
    <div class="rounded-2xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-gray-800 shadow-sm p-5 mb-4">
        <form method="GET" class="grid grid-cols-1 md:grid-cols-12 gap-3" id="formFiltrosCompras">
            <input type="hidden" name="export" id="export" value="">

            <div class="md:col-span-2">
                <label class="block text-xs font-black text-slate-700 dark:text-slate-300 uppercase tracking-tight">Inicio</label>
                <input type="date" name="fecha_inicio" value="{{ $fechaInicio }}"
                       class="mt-1 w-full px-3 py-2 bg-white dark:bg-gray-700 border border-slate-300 dark:border-slate-600 rounded-lg text-sm text-slate-900 dark:text-white focus:ring-2 focus:ring-indigo-500" />
            </div>

            <div class="md:col-span-2">
                <label class="block text-xs font-black text-slate-700 dark:text-slate-300 uppercase tracking-tight">Fin</label>
                <input type="date" name="fecha_fin" value="{{ $fechaFin }}" max="{{ date('Y-m-d') }}"
                       class="mt-1 w-full px-3 py-2 bg-white dark:bg-gray-700 border border-slate-300 dark:border-slate-600 rounded-lg text-sm text-slate-900 dark:text-white focus:ring-2 focus:ring-indigo-500" />
            </div>

            <div class="md:col-span-2">
                <label class="block text-xs font-black text-slate-700 dark:text-slate-300 uppercase tracking-tight">Proveedor</label>
                <select name="proveedor_id"
                        class="mt-1 w-full px-3 py-2 bg-white dark:bg-gray-700 border border-slate-300 dark:border-slate-600 rounded-lg text-sm text-slate-900 dark:text-white focus:ring-2 focus:ring-indigo-500">
                    <option value="">Todos</option>
                    @foreach(($proveedores ?? []) as $p)
                        <option value="{{ $p->id }}" {{ (string)request('proveedor_id') === (string)$p->id ? 'selected' : '' }}>{{ $p->nombre }}</option>
                    @endforeach
                </select>
            </div>

            <div class="md:col-span-2">
                <label class="block text-xs font-black text-slate-700 dark:text-slate-300 uppercase tracking-tight">Usuario</label>
                <select name="user_id"
                        class="mt-1 w-full px-3 py-2 bg-white dark:bg-gray-700 border border-slate-300 dark:border-slate-600 rounded-lg text-sm text-slate-900 dark:text-white focus:ring-2 focus:ring-indigo-500">
                    <option value="">Todos</option>
                    @foreach(($usuarios ?? []) as $u)
                        <option value="{{ $u->id }}" {{ (string)request('user_id') === (string)$u->id ? 'selected' : '' }}>{{ $u->name }}</option>
                    @endforeach
                </select>
            </div>

            <div class="md:col-span-2">
                <label class="block text-xs font-black text-slate-700 dark:text-slate-300 uppercase tracking-tight">Producto</label>
                <select name="producto_id"
                        class="mt-1 w-full px-3 py-2 bg-white dark:bg-gray-700 border border-slate-300 dark:border-slate-600 rounded-lg text-sm text-slate-900 dark:text-white focus:ring-2 focus:ring-indigo-500">
                    <option value="">Todos</option>
                    @foreach(($productos ?? []) as $p)
                        <option value="{{ $p->id }}" {{ (string)request('producto_id') === (string)$p->id ? 'selected' : '' }}>{{ $p->nombre }}</option>
                    @endforeach
                </select>
            </div>

            <div class="md:col-span-2">
                <label class="block text-xs font-black text-slate-700 dark:text-slate-300 uppercase tracking-tight">Categoría</label>
                <select name="categoria_id"
                        class="mt-1 w-full px-3 py-2 bg-white dark:bg-gray-700 border border-slate-300 dark:border-slate-600 rounded-lg text-sm text-slate-900 dark:text-white focus:ring-2 focus:ring-indigo-500">
                    <option value="">Todas</option>
                    @foreach(($categorias ?? []) as $cat)
                        <option value="{{ $cat->id }}" {{ (string)request('categoria_id') === (string)$cat->id ? 'selected' : '' }}>{{ $cat->nombre }}</option>
                    @endforeach
                </select>
            </div>

            <div class="md:col-span-2">
                <label class="block text-xs font-black text-slate-700 dark:text-slate-300 uppercase tracking-tight">Agrupar por</label>
                <select name="group_by"
                        class="mt-1 w-full px-3 py-2 bg-white dark:bg-gray-700 border border-slate-300 dark:border-slate-600 rounded-lg text-sm text-slate-900 dark:text-white focus:ring-2 focus:ring-indigo-500">
                    <option value="" {{ empty(request('group_by')) ? 'selected' : '' }}>Sin agrupar</option>
                    <option value="proveedor" {{ request('group_by')==='proveedor' ? 'selected' : '' }}>Proveedor</option>
                    <option value="usuario" {{ request('group_by')==='usuario' ? 'selected' : '' }}>Usuario</option>
                    <option value="producto" {{ request('group_by')==='producto' ? 'selected' : '' }}>Producto</option>
                    <option value="categoria" {{ request('group_by')==='categoria' ? 'selected' : '' }}>Categoría</option>
                </select>
            </div>

            <div class="md:col-span-2">
                <label class="block text-xs font-black text-slate-700 dark:text-slate-300 uppercase tracking-tight">Vista</label>
                <select name="modo"
                        class="mt-1 w-full px-3 py-2 bg-white dark:bg-gray-700 border border-slate-300 dark:border-slate-600 rounded-lg text-sm text-slate-900 dark:text-white focus:ring-2 focus:ring-indigo-500">
                    <option value="detalle" {{ request('modo','detalle')==='detalle' ? 'selected' : '' }}>Detalle</option>
                    <option value="resumen" {{ request('modo')==='resumen' ? 'selected' : '' }}>Resumen (Ejecutivo)</option>
                </select>
            </div>

            <div class="md:col-span-2">
                <label class="block text-xs font-black text-slate-700 dark:text-slate-300 uppercase tracking-tight">Orden fecha</label>
                <select name="order_dir"
                        class="mt-1 w-full px-3 py-2 bg-white dark:bg-gray-700 border border-slate-300 dark:border-slate-600 rounded-lg text-sm text-slate-900 dark:text-white focus:ring-2 focus:ring-indigo-500">
                    <option value="desc" {{ request('order_dir','desc')==='desc' ? 'selected' : '' }}>Más nuevo → más viejo</option>
                    <option value="asc" {{ request('order_dir')==='asc' ? 'selected' : '' }}>Más viejo → más nuevo</option>
                </select>
            </div>

            <div class="md:col-span-4 flex items-end gap-2">
                <button type="submit"
                        onclick="document.getElementById('export').value='';"
                        class="inline-flex w-full md:w-auto justify-center items-center px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white font-bold rounded-lg shadow-sm">
                    Ver / Actualizar
                </button>

                <button type="button"
                        onclick="document.getElementById('export').value='pdf'; document.getElementById('formFiltrosCompras').submit();"
                        class="inline-flex w-full md:w-auto justify-center items-center px-4 py-2 bg-white dark:bg-gray-800 border border-slate-300 dark:border-slate-600 text-slate-700 dark:text-slate-300 font-bold rounded-lg hover:bg-slate-50 dark:hover:bg-gray-700 shadow-sm">
                    PDF
                </button>

                <button type="button"
                        onclick="document.getElementById('export').value='excel'; document.getElementById('formFiltrosCompras').submit();"
                        class="inline-flex w-full md:w-auto justify-center items-center px-4 py-2 bg-white dark:bg-gray-800 border border-slate-300 dark:border-slate-600 text-slate-700 dark:text-slate-300 font-bold rounded-lg hover:bg-slate-50 dark:hover:bg-gray-700 shadow-sm">
                    Excel
                </button>

                @if(request()->hasAny(['fecha_inicio','fecha_fin','proveedor_id','user_id','producto_id','categoria_id']))
                    <a href="{{ route('reportes.compras') }}"
                       class="inline-flex w-full md:w-auto justify-center items-center px-4 py-2 bg-rose-50 dark:bg-rose-900/20 border border-rose-200 dark:border-rose-800 text-rose-700 dark:text-rose-300 font-bold rounded-lg hover:bg-rose-100 dark:hover:bg-rose-900/30 shadow-sm">
                        Limpiar
                    </a>
                @endif
            </div>
        </form>
    </div>

    {{-- KPIs --}}
    <div class="grid grid-cols-1 md:grid-cols-4 gap-3 mb-4">
        <div class="rounded-2xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-gray-800 p-5 shadow-sm">
            <p class="text-xs font-black text-slate-600 dark:text-slate-400 uppercase tracking-tight">Total compras</p>
            <p class="mt-1 text-2xl font-extrabold text-indigo-600 dark:text-indigo-300">{{ $moneda }} {{ number_format((float)($totalCompras ?? 0), 2) }}</p>
        </div>
        <div class="rounded-2xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-gray-800 p-5 shadow-sm">
            <p class="text-xs font-black text-slate-600 dark:text-slate-400 uppercase tracking-tight">Cantidad</p>
            <p class="mt-1 text-2xl font-extrabold text-emerald-600 dark:text-emerald-300">{{ (int)($cantidadCompras ?? 0) }}</p>
        </div>
        <div class="rounded-2xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-gray-800 p-5 shadow-sm">
            <p class="text-xs font-black text-slate-600 dark:text-slate-400 uppercase tracking-tight">Promedio</p>
            <p class="mt-1 text-2xl font-extrabold text-amber-600 dark:text-amber-300">{{ $moneda }} {{ number_format((float)($promedioCompra ?? 0), 2) }}</p>
        </div>
        <div class="rounded-2xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-gray-800 p-5 shadow-sm">
            <p class="text-xs font-black text-slate-600 dark:text-slate-400 uppercase tracking-tight">Unidades compradas</p>
            <p class="mt-1 text-2xl font-extrabold text-fuchsia-600 dark:text-fuchsia-300">{{ (int)($totalProductosComprados ?? 0) }}</p>
        </div>
    </div>

    {{-- Gráficos --}}
    <div class="grid grid-cols-1 xl:grid-cols-3 gap-4 mb-4">
        <div class="rounded-2xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-gray-800 p-5 shadow-sm">
            <div class="flex items-center justify-between">
                <h3 class="text-sm font-black text-slate-700 dark:text-slate-300 uppercase tracking-tight">Compras por proveedor</h3>
                <span class="text-xs text-slate-500 dark:text-slate-400">Participación</span>
            </div>
            <div class="mt-3 h-64">
                <canvas id="chartProveedor"></canvas>
            </div>
        </div>

        <div class="xl:col-span-2 rounded-2xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-gray-800 p-5 shadow-sm">
            <div class="flex items-center justify-between">
                <h3 class="text-sm font-black text-slate-700 dark:text-slate-300 uppercase tracking-tight">Gasto por categoría</h3>
                <span class="text-xs text-slate-500 dark:text-slate-400">Top 12</span>
            </div>
            <div class="mt-3 h-64">
                <canvas id="chartCategoria"></canvas>
            </div>
        </div>

        <div class="xl:col-span-3 rounded-2xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-gray-800 p-5 shadow-sm">
            <div class="flex items-center justify-between">
                <h3 class="text-sm font-black text-slate-700 dark:text-slate-300 uppercase tracking-tight">Top productos por unidades</h3>
                <span class="text-xs text-slate-500 dark:text-slate-400">Top 10</span>
            </div>
            <div class="mt-3 h-72">
                <canvas id="chartTopProductos"></canvas>
            </div>
        </div>
    </div>

    @php($g = request('group_by'))
    <div class="rounded-2xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-gray-800 shadow-sm">
        <div class="p-5 border-b border-slate-200 dark:border-slate-700">
            <h3 class="text-sm font-black text-slate-700 dark:text-slate-300 uppercase tracking-tight">
                {{ (request('modo','detalle')==='resumen' && $g) ? 'Resumen ejecutivo' : ($g ? 'Detalle agrupado + subtotales' : 'Detalle de compras') }}
            </h3>
            <p class="text-xs text-slate-500 dark:text-slate-400 mt-1">
                {{ (request('modo','detalle')==='resumen' && $g) ? 'Totaliza por grupo y omite el detalle.' : ($g ? 'Agrupa movimientos y agrega subtotal al final de cada grupo.' : 'Cada fila es una compra (ideal para exportar).') }}
            </p>
        </div>
        <div class="overflow-auto max-h-[520px]">
            <table class="min-w-full table-excel text-sm">
                <thead class="bg-slate-50 dark:bg-slate-900/40 border-b border-slate-200 dark:border-slate-700">
                    @if(request('modo','detalle')==='resumen' && $g)
                        <tr class="text-xs text-slate-600 dark:text-slate-300 uppercase tracking-tight">
                            <th class="px-4 py-3 text-left font-black">Grupo</th>
                            <th class="px-4 py-3 text-right font-black">Operaciones</th>
                            <th class="px-4 py-3 text-right font-black">Total</th>
                        </tr>
                    @elseif(in_array($g, ['producto','categoria'], true))
                        <tr class="text-xs text-slate-600 dark:text-slate-300 uppercase tracking-tight">
                            <th class="px-4 py-3 text-left font-black">Compra</th>
                            <th class="px-4 py-3 text-left font-black">Fecha</th>
                            <th class="px-4 py-3 text-left font-black">Proveedor</th>
                            <th class="px-4 py-3 text-left font-black">Usuario</th>
                            <th class="px-4 py-3 text-left font-black">Producto</th>
                            <th class="px-4 py-3 text-left font-black">Categoría</th>
                            <th class="px-4 py-3 text-right font-black">Unidades</th>
                            <th class="px-4 py-3 text-right font-black">Gasto</th>
                        </tr>
                    @else
                        <tr class="text-xs text-slate-600 dark:text-slate-300 uppercase tracking-tight">
                            <th class="px-4 py-3 text-left font-black">ID</th>
                            <th class="px-4 py-3 text-left font-black">Fecha</th>
                            <th class="px-4 py-3 text-left font-black">Proveedor</th>
                            <th class="px-4 py-3 text-left font-black">Usuario</th>
                            <th class="px-4 py-3 text-right font-black">Subtotal</th>
                            <th class="px-4 py-3 text-right font-black">Desc %</th>
                            <th class="px-4 py-3 text-right font-black">Desc $</th>
                            <th class="px-4 py-3 text-right font-black">Total</th>
                        </tr>
                    @endif
                </thead>
                <tbody class="divide-y divide-slate-200 dark:divide-slate-700">
                    @if(request('modo','detalle')==='resumen' && $g)
                        @forelse(($resumen ?? []) as $r)
                            <tr class="hover:bg-slate-50/70 dark:hover:bg-slate-900/30">
                                <td class="px-4 py-3 font-semibold text-slate-900 dark:text-white">
                                    {{ $g==='proveedor' ? ($r['proveedor'] ?? '—') : ($g==='usuario' ? ($r['usuario'] ?? '—') : ($g==='producto' ? ($r['producto'] ?? '—') : ($r['categoria'] ?? '—'))) }}
                                </td>
                                <td class="px-4 py-3 text-right text-slate-700 dark:text-slate-300">{{ (int)($r['cantidad'] ?? $r['compras'] ?? 0) }}</td>
                                <td class="px-4 py-3 text-right font-extrabold text-indigo-700 dark:text-indigo-300">{{ $moneda }} {{ number_format((float)($r['total'] ?? $r['gasto'] ?? 0), 2) }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="3" class="px-6 py-10 text-center text-slate-500 dark:text-slate-400">No hay datos para estos filtros.</td></tr>
                        @endforelse
                    @elseif(!empty($grouped) && $g)
                        @foreach($grouped as $grp)
                            <tr class="bg-slate-100 dark:bg-slate-900/40">
                                <td colspan="{{ in_array($g,['producto','categoria'],true) ? 8 : 8 }}" class="px-4 py-3 font-black text-slate-800 dark:text-slate-100">{{ $grp['label'] }}</td>
                            </tr>

                            @if(in_array($g, ['producto','categoria'], true))
                                @foreach(($grp['rows'] ?? []) as $r)
                                    <tr class="hover:bg-slate-50/70 dark:hover:bg-slate-900/30">
                                        <td class="px-4 py-3 font-semibold text-slate-900 dark:text-white">#{{ $r['compra_id'] }}</td>
                                        <td class="px-4 py-3 text-slate-700 dark:text-slate-300">{{ $r['fecha'] }}</td>
                                        <td class="px-4 py-3 text-slate-700 dark:text-slate-300">{{ $r['proveedor'] }}</td>
                                        <td class="px-4 py-3 text-slate-700 dark:text-slate-300">{{ $r['usuario'] }}</td>
                                        <td class="px-4 py-3 text-slate-700 dark:text-slate-300">{{ $r['producto'] }}</td>
                                        <td class="px-4 py-3 text-slate-700 dark:text-slate-300">{{ $r['categoria'] }}</td>
                                        <td class="px-4 py-3 text-right text-slate-700 dark:text-slate-300">{{ (int)$r['unidades'] }}</td>
                                        <td class="px-4 py-3 text-right font-extrabold text-indigo-700 dark:text-indigo-300">{{ $moneda }} {{ number_format((float)$r['gasto'],2) }}</td>
                                    </tr>
                                @endforeach
                                <tr class="bg-indigo-50/60 dark:bg-indigo-900/10">
                                    <td colspan="6" class="px-4 py-3 text-right font-black text-slate-700 dark:text-slate-200">SUBTOTAL</td>
                                    <td class="px-4 py-3 text-right font-black text-slate-700 dark:text-slate-200">{{ (int)($grp['subtotal']['unidades'] ?? 0) }}</td>
                                    <td class="px-4 py-3 text-right font-extrabold text-indigo-700 dark:text-indigo-300">{{ $moneda }} {{ number_format((float)($grp['subtotal']['gasto'] ?? 0), 2) }}</td>
                                </tr>
                            @else
                                @foreach(($grp['rows'] ?? []) as $c)
                                    <tr class="hover:bg-slate-50/70 dark:hover:bg-slate-900/30">
                                        <td class="px-4 py-3 font-semibold text-slate-900 dark:text-white">#{{ $c->id }}</td>
                                        <td class="px-4 py-3 text-slate-700 dark:text-slate-300">{{ optional($c->fecha)->format('d/m/Y H:i') }}</td>
                                        <td class="px-4 py-3 text-slate-700 dark:text-slate-300">{{ $c->proveedor?->nombre ?? '—' }}</td>
                                        <td class="px-4 py-3 text-slate-700 dark:text-slate-300">{{ $c->usuario?->name ?? '—' }}</td>
                                        <td class="px-4 py-3 text-right font-semibold text-slate-900 dark:text-white">{{ $moneda }} {{ number_format((float)($c->subtotal_bruto ?? 0), 2) }}</td>
                                        <td class="px-4 py-3 text-right text-slate-700 dark:text-slate-300">{{ number_format((float)($c->descuento_porcentaje ?? 0), 2) }}</td>
                                        <td class="px-4 py-3 text-right text-slate-700 dark:text-slate-300">{{ $moneda }} {{ number_format((float)($c->descuento_monto_total ?? 0), 2) }}</td>
                                        <td class="px-4 py-3 text-right font-extrabold text-indigo-700 dark:text-indigo-300">{{ $moneda }} {{ number_format((float)($c->total ?? 0), 2) }}</td>
                                    </tr>
                                @endforeach
                                <tr class="bg-indigo-50/60 dark:bg-indigo-900/10">
                                    <td colspan="4" class="px-4 py-3 text-right font-black text-slate-700 dark:text-slate-200">SUBTOTAL</td>
                                    <td class="px-4 py-3 text-right font-black text-slate-700 dark:text-slate-200">{{ $moneda }} {{ number_format((float)($grp['subtotal']['bruto'] ?? 0), 2) }}</td>
                                    <td class="px-4 py-3"></td>
                                    <td class="px-4 py-3 text-right font-black text-slate-700 dark:text-slate-200">{{ $moneda }} {{ number_format((float)($grp['subtotal']['descuento'] ?? 0), 2) }}</td>
                                    <td class="px-4 py-3 text-right font-extrabold text-indigo-700 dark:text-indigo-300">{{ $moneda }} {{ number_format((float)($grp['subtotal']['total'] ?? 0), 2) }}</td>
                                </tr>
                            @endif
                        @endforeach
                    @else
                        @forelse(($compras ?? []) as $c)
                            <tr class="hover:bg-slate-50/70 dark:hover:bg-slate-900/30">
                                <td class="px-4 py-3 font-semibold text-slate-900 dark:text-white">#{{ $c->id }}</td>
                                <td class="px-4 py-3 text-slate-700 dark:text-slate-300">{{ optional($c->fecha)->format('d/m/Y H:i') }}</td>
                                <td class="px-4 py-3 text-slate-700 dark:text-slate-300">{{ $c->proveedor?->nombre ?? '—' }}</td>
                                <td class="px-4 py-3 text-slate-700 dark:text-slate-300">{{ $c->usuario?->name ?? '—' }}</td>
                                <td class="px-4 py-3 text-right font-semibold text-slate-900 dark:text-white">{{ $moneda }} {{ number_format((float)($c->subtotal_bruto ?? 0), 2) }}</td>
                                <td class="px-4 py-3 text-right text-slate-700 dark:text-slate-300">{{ number_format((float)($c->descuento_porcentaje ?? 0), 2) }}</td>
                                <td class="px-4 py-3 text-right text-slate-700 dark:text-slate-300">{{ $moneda }} {{ number_format((float)($c->descuento_monto_total ?? 0), 2) }}</td>
                                <td class="px-4 py-3 text-right font-extrabold text-indigo-700 dark:text-indigo-300">{{ $moneda }} {{ number_format((float)($c->total ?? 0), 2) }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="8" class="px-6 py-10 text-center text-slate-500 dark:text-slate-400">No hay compras para estos filtros.</td></tr>
                        @endforelse
                    @endif
                </tbody>

                @if(empty($g) && !empty($compras) && count($compras) > 0)
                    <tfoot class="bg-slate-50 dark:bg-slate-900/40 border-t border-slate-200 dark:border-slate-700">
                        <tr>
                            <td colspan="7" class="px-4 py-3 text-right font-black text-slate-700 dark:text-slate-200">Total</td>
                            <td class="px-4 py-3 text-right font-extrabold text-indigo-700 dark:text-indigo-300">{{ $moneda }} {{ number_format((float)($totalCompras ?? 0), 2) }}</td>
                        </tr>
                    </tfoot>
                @endif
            </table>
        </div>
    </div>

</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', () => {
        const isDark = document.documentElement.classList.contains('dark');
        const gridColor = isDark ? 'rgba(148,163,184,0.18)' : 'rgba(100,116,139,0.15)';
        const tickColor = isDark ? 'rgba(226,232,240,0.85)' : 'rgba(15,23,42,0.75)';

        const labelsProv = @json($labelsProv->values());
        const totalesProv = @json($totalesProv->values());

        const labelsCat = @json($labelsCat->values());
        const gastoCat = @json($gastoCat->values());

        const labelsProd = @json($labelsProd->values());
        const unidadesProd = @json($unidadesProd->values());

        const commonScales = {
            x: { ticks: { color: tickColor }, grid: { color: gridColor } },
            y: { ticks: { color: tickColor }, grid: { color: gridColor } },
        };

        const elProv = document.getElementById('chartProveedor');
        if (elProv) {
            new Chart(elProv, {
                type: 'pie',
                data: {
                    labels: labelsProv,
                    datasets: [{ data: totalesProv, borderWidth: 1 }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: { position: 'bottom', labels: { color: tickColor } },
                    }
                }
            });
        }

        const elCat = document.getElementById('chartCategoria');
        if (elCat) {
            new Chart(elCat, {
                type: 'bar',
                data: {
                    labels: labelsCat,
                    datasets: [{ label: 'Gasto', data: gastoCat, borderWidth: 1 }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: { legend: { labels: { color: tickColor } } },
                    scales: commonScales,
                }
            });
        }

        const elProd = document.getElementById('chartTopProductos');
        if (elProd) {
            new Chart(elProd, {
                type: 'bar',
                data: {
                    labels: labelsProd,
                    datasets: [{ label: 'Unidades', data: unidadesProd, borderWidth: 1 }]
                },
                options: {
                    indexAxis: 'y',
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: { legend: { labels: { color: tickColor } } },
                    scales: commonScales,
                }
            });
        }
    });
</script>
@endsection
