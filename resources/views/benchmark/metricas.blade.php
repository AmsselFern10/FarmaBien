<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reporte de Eficiencia de Desempeño - ISO/IEC 25023</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;600;700;800&family=JetBrains+Mono:wght@500;700&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Plus Jakarta Sans', sans-serif; }
        .font-mono { font-family: 'JetBrains Mono', monospace; }
    </style>
</head>
<body class="bg-slate-950 text-slate-100 min-h-screen p-6 md:p-12">
    <div class="max-w-5xl mx-auto space-y-8">
        
        {{-- Header --}}
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 pb-6 border-b border-slate-800">
            <div>
                <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-emerald-950/80 border border-emerald-800 text-emerald-400 text-xs font-bold mb-3">
                    <span class="w-2 h-2 rounded-full bg-emerald-400 animate-pulse"></span>
                    EVALUACIÓN ISO/IEC 25010 & 25023
                </div>
                <h1 class="text-2xl md:text-3xl font-extrabold text-white">Eficiencia de Desempeño — FarmaBien v2.0</h1>
                <p class="text-slate-400 text-sm mt-1">Telemetría de utilización de recursos (CPU, RAM y Latencia) ejecutada dentro del servidor.</p>
            </div>
            <div class="flex items-center gap-2 self-start md:self-auto">
                <a href="?format=json" target="_blank" class="px-3 py-2 rounded-xl bg-slate-900 hover:bg-slate-800 border border-slate-700 text-slate-300 text-xs font-semibold transition">
                    Ver JSON Raw
                </a>
                <button onclick="window.print()" class="px-4 py-2 rounded-xl bg-emerald-600 hover:bg-emerald-500 text-white text-xs font-bold transition shadow-lg shadow-emerald-900/30">
                    Imprimir / Guardar PDF
                </button>
            </div>
        </div>

        {{-- Environment Info Cards --}}
        <div class="grid grid-cols-2 sm:grid-cols-4 gap-3">
            <div class="p-4 rounded-2xl bg-slate-900/80 border border-slate-800">
                <span class="text-xs text-slate-500 font-semibold uppercase">Servidor / SO</span>
                <p class="text-sm font-bold text-white mt-1">{{ $resultado['entorno']['servidor'] }}</p>
            </div>
            <div class="p-4 rounded-2xl bg-slate-900/80 border border-slate-800">
                <span class="text-xs text-slate-500 font-semibold uppercase">Versión PHP</span>
                <p class="text-sm font-bold text-emerald-400 font-mono mt-1">PHP {{ $resultado['entorno']['php_version'] }}</p>
            </div>
            <div class="p-4 rounded-2xl bg-slate-900/80 border border-slate-800">
                <span class="text-xs text-slate-500 font-semibold uppercase">Base de Datos</span>
                <p class="text-sm font-bold text-sky-400 font-mono mt-1">{{ strtoupper($resultado['entorno']['database_driver']) }}</p>
            </div>
            <div class="p-4 rounded-2xl bg-slate-900/80 border border-slate-800">
                <span class="text-xs text-slate-500 font-semibold uppercase">Límite Memoria RAM</span>
                <p class="text-sm font-bold text-purple-400 font-mono mt-1">{{ $resultado['entorno']['memory_limit_mb'] }} MB</p>
            </div>
        </div>

        {{-- Main ISO Metrics Grid --}}
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            
            {{-- PRu-1-G (CPU) --}}
            <div class="p-6 rounded-3xl bg-slate-900/90 border border-slate-800 space-y-4">
                <div class="flex items-center justify-between">
                    <div>
                        <span class="text-xs font-bold text-emerald-400 uppercase tracking-wider">Métrica ISO/IEC 25023</span>
                        <h2 class="text-lg font-bold text-white mt-0.5">PRu-1-G: Utilización de CPU</h2>
                    </div>
                    <span class="px-3 py-1 rounded-full bg-emerald-950 border border-emerald-700 text-emerald-300 text-xs font-extrabold">
                        {{ $resultado['metricas_iso_25023']['pru_1_g_utilizacion_cpu']['evaluacion'] }}
                    </span>
                </div>

                <div class="flex items-baseline gap-2">
                    <span class="text-4xl font-extrabold text-white font-mono">{{ $resultado['metricas_iso_25023']['pru_1_g_utilizacion_cpu']['porcentaje_carga'] }}</span>
                    <span class="text-xs text-slate-400">carga de procesador</span>
                </div>

                <div class="w-full bg-slate-800 h-2.5 rounded-full overflow-hidden">
                    <div class="bg-emerald-500 h-full rounded-full" style="width: {{ min(100, $resultado['metricas_iso_25023']['pru_1_g_utilizacion_cpu']['ratio_x'] * 100) }}%"></div>
                </div>

                <div class="p-3.5 rounded-xl bg-slate-950/70 border border-slate-800 text-xs space-y-1.5 font-mono text-slate-300">
                    <div class="flex justify-between"><span>A (Tiempo CPU activo):</span><b class="text-white">{{ $resultado['metricas_iso_25023']['pru_1_g_utilizacion_cpu']['tiempo_cpu_ms'] }} ms</b></div>
                    <div class="flex justify-between"><span>B (Tiempo ejecución total):</span><b class="text-white">{{ $resultado['metricas_iso_25023']['pru_1_g_utilizacion_cpu']['tiempo_total_ms'] }} ms</b></div>
                    <div class="flex justify-between border-t border-slate-800 pt-1.5 text-emerald-400"><span>Fórmula X = A / B:</span><b>{{ $resultado['metricas_iso_25023']['pru_1_g_utilizacion_cpu']['ratio_x'] }}</b></div>
                </div>
            </div>

            {{-- PRu-2-G (RAM) --}}
            <div class="p-6 rounded-3xl bg-slate-900/90 border border-slate-800 space-y-4">
                <div class="flex items-center justify-between">
                    <div>
                        <span class="text-xs font-bold text-purple-400 uppercase tracking-wider">Métrica ISO/IEC 25023</span>
                        <h2 class="text-lg font-bold text-white mt-0.5">PRu-2-G: Utilización de Memoria RAM</h2>
                    </div>
                    <span class="px-3 py-1 rounded-full bg-emerald-950 border border-emerald-700 text-emerald-300 text-xs font-extrabold">
                        {{ $resultado['metricas_iso_25023']['pru_2_g_utilizacion_ram']['evaluacion'] }}
                    </span>
                </div>

                <div class="flex items-baseline gap-2">
                    <span class="text-4xl font-extrabold text-white font-mono">{{ $resultado['metricas_iso_25023']['pru_2_g_utilizacion_ram']['porcentaje_uso'] }}</span>
                    <span class="text-xs text-slate-400">de {{ $resultado['entorno']['memory_limit_mb'] }} MB disponibles</span>
                </div>

                <div class="w-full bg-slate-800 h-2.5 rounded-full overflow-hidden">
                    <div class="bg-purple-500 h-full rounded-full" style="width: {{ min(100, $resultado['metricas_iso_25023']['pru_2_g_utilizacion_ram']['ratio_x'] * 100) }}%"></div>
                </div>

                <div class="p-3.5 rounded-xl bg-slate-950/70 border border-slate-800 text-xs space-y-1.5 font-mono text-slate-300">
                    <div class="flex justify-between"><span>A (RAM Pico Promedio):</span><b class="text-white">{{ $resultado['metricas_iso_25023']['pru_2_g_utilizacion_ram']['ram_pico_promedio_mb'] }} MB</b></div>
                    <div class="flex justify-between"><span>B (Memoria Asignada):</span><b class="text-white">{{ $resultado['metricas_iso_25023']['pru_2_g_utilizacion_ram']['limite_disponible_mb'] }} MB</b></div>
                    <div class="flex justify-between border-t border-slate-800 pt-1.5 text-purple-400"><span>Fórmula X = A / B:</span><b>{{ $resultado['metricas_iso_25023']['pru_2_g_utilizacion_ram']['ratio_x'] }}</b></div>
                </div>
            </div>

        </div>

        {{-- Detail Table --}}
        <div class="p-6 rounded-3xl bg-slate-900/80 border border-slate-800 space-y-4">
            <h3 class="text-base font-bold text-white">Desglose de Muestras Evaluadas ({{ count($resultado['detalle_muestras']) }} Ejecuciones)</h3>
            <div class="overflow-x-auto">
                <table class="w-full text-xs text-left">
                    <thead>
                        <tr class="text-slate-400 border-b border-slate-800">
                            <th class="py-3 px-3">#</th>
                            <th class="py-3 px-3">Operación Ejecutada</th>
                            <th class="py-3 px-3 text-right">Tiempo Total (Wall)</th>
                            <th class="py-3 px-3 text-right">Tiempo CPU Activo</th>
                            <th class="py-3 px-3 text-right">RAM Pico</th>
                            <th class="py-3 px-3 text-right">% Memoria</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-800/60 font-mono">
                        @foreach($resultado['detalle_muestras'] as $row)
                        <tr class="hover:bg-slate-800/40 transition">
                            <td class="py-2.5 px-3 text-slate-500">{{ $row['muestra'] }}</td>
                            <td class="py-2.5 px-3 font-sans text-slate-200">{{ $row['operacion'] }}</td>
                            <td class="py-2.5 px-3 text-right text-emerald-400">{{ $row['tiempo_wall_ms'] }} ms</td>
                            <td class="py-2.5 px-3 text-right text-cyan-400">{{ $row['tiempo_cpu_ms'] }} ms</td>
                            <td class="py-2.5 px-3 text-right text-purple-400">{{ $row['ram_pico_mb'] }} MB</td>
                            <td class="py-2.5 px-3 text-right text-slate-400">{{ $row['porcentaje_ram_php'] }}%</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        {{-- Footer --}}
        <div class="text-center text-xs text-slate-500 pt-4">
            Generado automáticamente por FarmaBien v2.0 • Estándar Internacional ISO/IEC 25010 & ISO/IEC 25023
        </div>

    </div>
</body>
</html>
