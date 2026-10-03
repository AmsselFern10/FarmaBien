@extends('layouts.app')
@section('title', 'Toma de Inventario — ' . $conteo->nombre . ' - FarmaBien')
@section('content')
<div x-data="{
    buscar: '',
    soloDiferencias: false,
    filas: [],
    init() {
        this.filas = Array.from(document.querySelectorAll('[data-fila]'));
    },
    get filasFiltradas() {
        return this.filas.filter(f => {
            const txt = f.dataset.nombre?.toLowerCase() ?? '';
            const tieneDif = f.dataset.diferencia !== '0';
            if (this.soloDiferencias && !tieneDif) return false;
            if (this.buscar && !txt.includes(this.buscar.toLowerCase())) return false;
            return true;
        });
    }
}" class="space-y-5">

    {{-- Breadcrumb --}}
    <nav class="flex items-center space-x-2 text-xs text-slate-500 dark:text-slate-400">
        <a href="{{ route('dashboard') }}" class="hover:text-emerald-600 dark:hover:text-emerald-400 transition">Inicio</a>
        <svg class="w-3 h-3 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
        <a href="{{ route('inventario.conteos.index') }}" class="hover:text-emerald-600 dark:hover:text-emerald-400 transition">Tomas de Inventario</a>
        <svg class="w-3 h-3 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
        <span class="text-slate-800 dark:text-slate-200 font-semibold truncate max-w-xs">{{ $conteo->nombre }}</span>
    </nav>

    {{-- Header --}}
    <div class="flex flex-col sm:flex-row sm:items-start justify-between gap-4">
        <div>
            <h1 class="text-xl font-bold text-slate-900 dark:text-white flex items-center gap-2 flex-wrap">
                {{ $conteo->nombre }}
                @if($conteo->estado === 'en_proceso')
                <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[11px] font-semibold bg-blue-100 dark:bg-blue-950/60 text-blue-700 dark:text-blue-300 border border-blue-200 dark:border-blue-800">
                    <span class="w-1.5 h-1.5 rounded-full bg-blue-500 animate-pulse inline-block"></span>En Proceso
                </span>
                @elseif($conteo->estado === 'completado')
                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[11px] font-semibold bg-emerald-100 dark:bg-emerald-950/60 text-emerald-700 dark:text-emerald-300 border border-emerald-200 dark:border-emerald-800">✓ Completado</span>
                @elseif($conteo->estado === 'cancelado')
                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[11px] font-semibold bg-slate-100 dark:bg-slate-800 text-slate-500 border border-slate-200 dark:border-slate-700">Cancelado</span>
                @endif
            </h1>
            <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">
                Iniciado por <span class="font-semibold">{{ $conteo->usuario->name ?? '—' }}</span>
                el {{ $conteo->created_at->format('d/m/Y H:i') }}
                @if($conteo->aprobadoPor)
                · Aprobado por <span class="font-semibold text-emerald-600 dark:text-emerald-400">{{ $conteo->aprobadoPor->name }}</span>
                el {{ $conteo->completado_en?->format('d/m/Y H:i') }}
                @endif
            </p>
        </div>
        <a href="{{ route('inventario.conteos.index') }}"
           class="inline-flex items-center space-x-1.5 px-3.5 py-2 bg-slate-100 dark:bg-slate-800 hover:bg-slate-200 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-300 text-xs font-semibold rounded-xl transition shrink-0">
            <span>&larr;</span><span>Todas las tomas</span>
        </a>
    </div>

    {{-- Alerts --}}
    @if(session('success'))
    <div class="px-4 py-3 rounded-xl bg-emerald-50 dark:bg-emerald-950/40 border border-emerald-300 dark:border-emerald-700 text-xs text-emerald-800 dark:text-emerald-300 font-semibold flex items-center space-x-2">
        <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
        <span>{{ session('success') }}</span>
    </div>
    @endif
    @if(session('error'))
    <div class="px-4 py-3 rounded-xl bg-rose-50 dark:bg-rose-950/40 border border-rose-300 dark:border-rose-700 text-xs text-rose-800 dark:text-rose-300 font-semibold flex items-center space-x-2">
        <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
        <span>{{ session('error') }}</span>
    </div>
    @endif

    {{-- Stats --}}
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
        <div class="bg-white dark:bg-slate-900 rounded-xl p-4 border border-slate-200 dark:border-slate-800 shadow-sm">
            <p class="text-xs font-medium text-slate-500 dark:text-slate-400">Total Lotes</p>
            <p class="text-2xl font-bold text-slate-900 dark:text-white mt-0.5">{{ $conteo->total_lotes }}</p>
        </div>
        <div class="bg-white dark:bg-slate-900 rounded-xl p-4 border border-slate-200 dark:border-slate-800 shadow-sm">
            <p class="text-xs font-medium text-slate-500 dark:text-slate-400">Contados</p>
            <div class="flex items-end gap-2 mt-0.5">
                <p class="text-2xl font-bold text-emerald-600 dark:text-emerald-400">{{ $contados }}</p>
                <p class="text-xs text-slate-400 mb-1">/ {{ $conteo->total_lotes }} ({{ $progresoPct }}%)</p>
            </div>
            <div class="w-full bg-slate-200 dark:bg-slate-700 rounded-full h-1.5 mt-2">
                <div class="bg-emerald-500 h-1.5 rounded-full transition-all" style="width: {{ $progresoPct }}%"></div>
            </div>
        </div>
        <div class="bg-white dark:bg-slate-900 rounded-xl p-4 border border-slate-200 dark:border-slate-800 shadow-sm">
            <p class="text-xs font-medium text-slate-500 dark:text-slate-400">Lotes con Diferencia</p>
            <p class="text-2xl font-bold mt-0.5 {{ $conDiferencia > 0 ? 'text-rose-600 dark:text-rose-400' : 'text-slate-900 dark:text-white' }}">
                {{ $conDiferencia }}
            </p>
        </div>
        <div class="bg-white dark:bg-slate-900 rounded-xl p-4 border border-slate-200 dark:border-slate-800 shadow-sm">
            <p class="text-xs font-medium text-slate-500 dark:text-slate-400">Diferencia Neta</p>
            @php $dif = $conteo->diferencia_total_unidades; @endphp
            <p class="text-2xl font-bold font-mono mt-0.5 {{ $dif > 0 ? 'text-emerald-600 dark:text-emerald-400' : ($dif < 0 ? 'text-rose-600 dark:text-rose-400' : 'text-slate-900 dark:text-white') }}">
                {{ $dif > 0 ? '+' : '' }}{{ $dif }} <span class="text-sm font-normal">u.</span>
            </p>
        </div>
    </div>

    @if($conteo->estado === 'en_proceso')

    {{-- Formulario de conteo --}}
    <form method="POST" action="{{ route('inventario.conteos.guardar', $conteo) }}">
        @csrf

        {{-- Barra de filtros + acciones --}}
        <div class="bg-white dark:bg-slate-900 rounded-xl p-4 border border-slate-200 dark:border-slate-800 shadow-sm mb-4">
            <div class="flex flex-col md:flex-row items-center gap-3">
                <div class="relative flex-1 w-full">
                    <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                    </div>
                    <input type="text" x-model="buscar" @input="$nextTick(aplicarFiltro)"
                           placeholder="Filtrar por medicamento o lote..."
                           class="w-full pl-10 pr-4 py-2 bg-slate-50 dark:bg-slate-800/80 border border-slate-200 dark:border-slate-700 rounded-xl text-xs text-slate-900 dark:text-white placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-emerald-500 transition">
                </div>
                <label class="flex items-center gap-2 text-xs font-semibold text-slate-700 dark:text-slate-300 cursor-pointer shrink-0 select-none">
                    <input type="checkbox" x-model="soloDiferencias" @change="$nextTick(aplicarFiltro)"
                           class="w-4 h-4 rounded text-rose-500 focus:ring-rose-500 bg-slate-50 dark:bg-slate-800 border-slate-300 dark:border-slate-700">
                    Solo con diferencia
                </label>
                <div class="flex items-center gap-2 shrink-0">
                    <button type="submit"
                            class="px-4 py-2 bg-slate-900 hover:bg-slate-800 dark:bg-slate-700 dark:hover:bg-slate-600 text-white text-xs font-semibold rounded-xl transition">
                        Guardar Avance
                    </button>
                </div>
            </div>
        </div>

        {{-- Tabla de conteo --}}
        <div class="bg-white dark:bg-slate-900 rounded-xl border border-slate-200 dark:border-slate-800 shadow-sm overflow-hidden" id="tablaConteo">
            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs">
                    <thead class="bg-slate-50 dark:bg-slate-800/60 text-slate-500 dark:text-slate-400 font-semibold border-b border-slate-200 dark:border-slate-800 uppercase tracking-wider">
                        <tr>
                            <th class="px-5 py-3.5">Medicamento</th>
                            <th class="px-5 py-3.5">N° Lote</th>
                            <th class="px-5 py-3.5">Vence</th>
                            <th class="px-5 py-3.5 text-center">Stock Sistema</th>
                            <th class="px-5 py-3.5 text-center w-36">Conteo Físico</th>
                            <th class="px-5 py-3.5 text-center">Diferencia</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 dark:divide-slate-800/60 text-slate-700 dark:text-slate-300" id="tbodyConteo">
                        @foreach($detalles as $det)
                        @php
                            $dif = $det->stock_fisico !== null ? ($det->stock_fisico - $det->stock_sistema) : null;
                            $esFaltante = $dif !== null && $dif < 0;
                            $esSobrante = $dif !== null && $dif > 0;
                        @endphp
                        <tr data-fila
                            data-nombre="{{ strtolower($det->producto->nombre ?? '') }} {{ strtolower($det->lote->numero_lote ?? '') }}"
                            data-diferencia="{{ $dif ?? '0' }}"
                            class="hover:bg-slate-50/70 dark:hover:bg-slate-800/40 transition {{ $esFaltante ? 'bg-rose-50/40 dark:bg-rose-950/10' : ($esSobrante ? 'bg-emerald-50/40 dark:bg-emerald-950/10' : '') }}">
                            <td class="px-5 py-3">
                                <div class="font-semibold text-slate-900 dark:text-white">{{ $det->producto->nombre }}</div>
                                @if($det->producto->laboratorio)
                                <div class="text-slate-400 text-[10px]">{{ $det->producto->laboratorio->nombre }}</div>
                                @endif
                            </td>
                            <td class="px-5 py-3 font-mono text-slate-600 dark:text-slate-400 text-[11px]">
                                {{ $det->lote->numero_lote ?? '—' }}
                            </td>
                            <td class="px-5 py-3">
                                @if($det->lote)
                                @php $vence = $det->lote->fecha_vencimiento; @endphp
                                <span class="{{ $vence <= now()->addDays(90) ? 'text-amber-600 dark:text-amber-400 font-semibold' : 'text-slate-600 dark:text-slate-400' }}">
                                    {{ $vence->format('d/m/Y') }}
                                </span>
                                @endif
                            </td>
                            <td class="px-5 py-3 text-center font-mono font-bold text-slate-900 dark:text-white">
                                {{ $det->stock_sistema }}
                            </td>
                            <td class="px-5 py-3 text-center">
                                <input type="number"
                                       name="cantidades[{{ $det->id }}]"
                                       value="{{ $det->stock_fisico }}"
                                       min="0"
                                       placeholder="—"
                                       class="w-24 text-center px-2 py-1.5 bg-slate-50 dark:bg-slate-800 border border-slate-300 dark:border-slate-600 rounded-lg text-xs font-mono text-slate-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 transition"
                                       oninput="actualizarDiferencia(this, {{ $det->stock_sistema }})">
                            </td>
                            <td class="px-5 py-3 text-center" id="dif-{{ $det->id }}">
                                @if($dif !== null)
                                    @if($dif < 0)
                                    <span class="font-bold font-mono text-rose-600 dark:text-rose-400">{{ $dif }}</span>
                                    @elseif($dif > 0)
                                    <span class="font-bold font-mono text-emerald-600 dark:text-emerald-400">+{{ $dif }}</span>
                                    @else
                                    <span class="text-slate-400">0</span>
                                    @endif
                                @else
                                <span class="text-slate-300 dark:text-slate-600">—</span>
                                @endif
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        {{-- Footer sticky --}}
        <div class="sticky bottom-0 -mx-4 sm:-mx-6 lg:-mx-8 px-4 sm:px-6 lg:px-8 py-3.5
                    bg-white/95 dark:bg-slate-900/95 backdrop-blur-md border-t border-slate-200 dark:border-slate-800
                    shadow-lg z-20 flex items-center justify-between gap-3">
            <div class="text-xs text-slate-500 dark:text-slate-400">
                Contados: <span class="font-bold text-slate-800 dark:text-white">{{ $contados }}/{{ $conteo->total_lotes }}</span>
                @if($conteo->total_lotes > $contados)
                · <span class="text-amber-600 dark:text-amber-400 font-semibold">{{ $conteo->total_lotes - $contados }} pendientes</span>
                @endif
            </div>
            <div class="flex items-center gap-3">
                <button type="submit"
                        class="px-5 py-2.5 bg-slate-900 hover:bg-slate-800 dark:bg-slate-700 dark:hover:bg-slate-600 text-white text-sm font-semibold rounded-xl transition">
                    Guardar Avance
                </button>
                @if($contados === $conteo->total_lotes && $conteo->total_lotes > 0)
                <button type="button"
                        onclick="document.getElementById('formAprobar').submit()"
                        class="px-6 py-2.5 bg-emerald-600 hover:bg-emerald-700 active:bg-emerald-800 text-white text-sm font-bold rounded-xl shadow-sm transition inline-flex items-center gap-2">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    Aprobar y Aplicar Ajustes
                </button>
                @else
                <button type="button" disabled
                        class="px-6 py-2.5 bg-slate-300 dark:bg-slate-700 text-slate-500 dark:text-slate-400 text-sm font-bold rounded-xl cursor-not-allowed inline-flex items-center gap-2"
                        title="Completa el conteo de todos los lotes antes de aprobar">
                    Aprobar (completa primero)
                </button>
                @endif
            </div>
        </div>
    </form>

    {{-- Formas ocultas para aprobar / cancelar --}}
    <form id="formAprobar" method="POST" action="{{ route('inventario.conteos.aprobar', $conteo) }}" class="hidden">@csrf</form>

    <div class="flex justify-end">
        <form method="POST" action="{{ route('inventario.conteos.cancelar', $conteo) }}"
              onsubmit="return confirm('¿Cancelar esta toma de inventario sin aplicar ajustes?')">
            @csrf
            <button type="submit"
                    class="px-4 py-2 text-xs font-semibold text-rose-600 dark:text-rose-400 hover:bg-rose-50 dark:hover:bg-rose-950/30 rounded-xl transition">
                Cancelar Conteo
            </button>
        </form>
    </div>

    @else
    {{-- Vista de solo lectura (completado / cancelado) --}}
    <div class="bg-white dark:bg-slate-900 rounded-xl border border-slate-200 dark:border-slate-800 shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead class="bg-slate-50 dark:bg-slate-800/60 text-slate-500 dark:text-slate-400 font-semibold border-b border-slate-200 dark:border-slate-800 uppercase tracking-wider">
                    <tr>
                        <th class="px-5 py-3.5">Medicamento</th>
                        <th class="px-5 py-3.5">N° Lote</th>
                        <th class="px-5 py-3.5">Vence</th>
                        <th class="px-5 py-3.5 text-center">Sistema</th>
                        <th class="px-5 py-3.5 text-center">Físico</th>
                        <th class="px-5 py-3.5 text-center">Diferencia</th>
                        <th class="px-5 py-3.5 text-center">Ajustado</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-slate-800/60 text-slate-700 dark:text-slate-300">
                    @foreach($detalles as $det)
                    @php $dif = $det->diferencia; @endphp
                    <tr class="hover:bg-slate-50/70 dark:hover:bg-slate-800/40 transition {{ $dif < 0 ? 'bg-rose-50/30 dark:bg-rose-950/10' : ($dif > 0 ? 'bg-emerald-50/30 dark:bg-emerald-950/10' : '') }}">
                        <td class="px-5 py-3 font-semibold text-slate-900 dark:text-white">{{ $det->producto->nombre }}</td>
                        <td class="px-5 py-3 font-mono text-slate-500 dark:text-slate-400 text-[11px]">{{ $det->lote->numero_lote ?? '—' }}</td>
                        <td class="px-5 py-3 text-slate-600 dark:text-slate-400">{{ $det->lote?->fecha_vencimiento?->format('d/m/Y') }}</td>
                        <td class="px-5 py-3 text-center font-mono font-bold">{{ $det->stock_sistema }}</td>
                        <td class="px-5 py-3 text-center font-mono font-bold">{{ $det->stock_fisico ?? '—' }}</td>
                        <td class="px-5 py-3 text-center font-mono font-bold">
                            @if($dif < 0) <span class="text-rose-600 dark:text-rose-400">{{ $dif }}</span>
                            @elseif($dif > 0) <span class="text-emerald-600 dark:text-emerald-400">+{{ $dif }}</span>
                            @else <span class="text-slate-400">0</span>
                            @endif
                        </td>
                        <td class="px-5 py-3 text-center">
                            @if($det->ajustado)
                            <span class="text-emerald-600 dark:text-emerald-400 font-bold">✓</span>
                            @elseif($dif !== 0 && !$det->ajustado)
                            <span class="text-slate-400 text-[10px]">sin dif.</span>
                            @else
                            <span class="text-slate-300 dark:text-slate-600">—</span>
                            @endif
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
    @endif

</div>

<script>
function actualizarDiferencia(input, stockSistema) {
    const fila = input.closest('tr');
    const celda = fila.querySelector('[id^="dif-"]');
    if (!celda) return;
    const valor = input.value === '' ? null : parseInt(input.value, 10);
    if (valor === null) {
        celda.innerHTML = '<span class="text-slate-300 dark:text-slate-600">—</span>';
        fila.dataset.diferencia = '0';
        fila.classList.remove('bg-rose-50/40', 'bg-emerald-50/40', 'dark:bg-rose-950/10', 'dark:bg-emerald-950/10');
        return;
    }
    const dif = valor - stockSistema;
    fila.dataset.diferencia = String(dif);
    if (dif < 0) {
        celda.innerHTML = `<span class="font-bold font-mono text-rose-600 dark:text-rose-400">${dif}</span>`;
        fila.classList.add('bg-rose-50/40', 'dark:bg-rose-950/10');
        fila.classList.remove('bg-emerald-50/40', 'dark:bg-emerald-950/10');
    } else if (dif > 0) {
        celda.innerHTML = `<span class="font-bold font-mono text-emerald-600 dark:text-emerald-400">+${dif}</span>`;
        fila.classList.add('bg-emerald-50/40', 'dark:bg-emerald-950/10');
        fila.classList.remove('bg-rose-50/40', 'dark:bg-rose-950/10');
    } else {
        celda.innerHTML = '<span class="text-slate-400">0</span>';
        fila.classList.remove('bg-rose-50/40', 'bg-emerald-50/40', 'dark:bg-rose-950/10', 'dark:bg-emerald-950/10');
    }
}

// Filtro de filas en tiempo real (client-side)
function aplicarFiltro() {
    const buscar = document.querySelector('[x-model="buscar"]')?._x_model?.get() ?? '';
    const soloDif = document.querySelector('[x-model="soloDiferencias"]')?._x_model?.get() ?? false;
    document.querySelectorAll('[data-fila]').forEach(fila => {
        const nombre = fila.dataset.nombre ?? '';
        const dif    = fila.dataset.diferencia ?? '0';
        const matchBuscar = !buscar || nombre.includes(buscar.toLowerCase());
        const matchDif    = !soloDif || dif !== '0';
        fila.style.display = (matchBuscar && matchDif) ? '' : 'none';
    });
}
</script>
@endsection
