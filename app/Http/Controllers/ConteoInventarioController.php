<?php

namespace App\Http\Controllers;

use App\Models\ConteoInventario;
use App\Models\DetalleConteo;
use App\Models\Lote;
use App\Models\Producto;
use App\Models\Laboratorio;
use App\Models\Categoria;
use App\Models\MovimientoInventario;
use App\Models\AuditLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Exception;

class ConteoInventarioController extends Controller
{
    public function __construct()
    {
        $this->middleware('permission:ajustar inventario');
    }

    /**
     * Lista de tomas / sesiones de conteo físico
     */
    public function index()
    {
        $conteos = ConteoInventario::with(['usuario', 'aprobadoPor'])
            ->orderBy('created_at', 'desc')
            ->paginate(perPage(15));

        $tomasEnProcesoCount = ConteoInventario::where('estado', 'en_proceso')->count();

        return view('inventario.conteos.index', compact('conteos', 'tomasEnProcesoCount'));
    }

    /**
     * Formulario para nueva toma de inventario con filtros de alcance
     */
    public function create()
    {
        $totalLotesActivos = Lote::where('activo', true)->where('stock_actual', '>', 0)->count();
        $totalMedicamentosActivos = Producto::where('activo', true)
            ->whereHas('lotes', function ($q) {
                $q->where('activo', true)->where('stock_actual', '>', 0);
            })->count();

        $tomasEnProceso = ConteoInventario::where('estado', 'en_proceso')->get(['id', 'nombre', 'created_at']);

        return view('inventario.conteos.create', compact(
            'totalLotesActivos',
            'totalMedicamentosActivos',
            'tomasEnProceso'
        ));
    }

    /**
     * Endpoint AJAX para conteo en vivo según filtros de alcance seleccionados
     */
    public function conteoPrevio(Request $request)
    {
        $laboratoriosIds = $request->input('laboratorios_ids', []);
        $categoriasIds   = $request->input('categorias_ids', []);
        $regimenVenta    = $request->input('regimen_venta', 'todos');

        // Limpiar arrays si vienen como strings vacíos
        if (!is_array($laboratoriosIds)) {
            $laboratoriosIds = array_filter(explode(',', (string)$laboratoriosIds));
        }
        if (!is_array($categoriasIds)) {
            $categoriasIds = array_filter(explode(',', (string)$categoriasIds));
        }

        $query = Lote::where('lotes.activo', true)
            ->where('lotes.stock_actual', '>', 0)
            ->join('productos', 'lotes.producto_id', '=', 'productos.id')
            ->where('productos.activo', true);

        if (!empty($laboratoriosIds)) {
            $query->whereIn('productos.laboratorio_id', $laboratoriosIds);
        }

        if (!empty($categoriasIds)) {
            $query->whereIn('productos.categoria_id', $categoriasIds);
        }

        if ($regimenVenta === 'venta_libre') {
            $query->where(function ($q) {
                $q->where(function ($sub) {
                    $sub->where('productos.tipo_control', 'libre')
                        ->orWhereNull('productos.tipo_control');
                })->where('productos.requiere_receta', false);
            });
        } elseif ($regimenVenta === 'controlados') {
            $query->where(function ($q) {
                $q->where('productos.tipo_control', 'controlado')
                  ->orWhere('productos.requiere_receta', true);
            });
        }

        $totalLotes = (clone $query)->count('lotes.id');
        $totalMedicamentos = (clone $query)->distinct('lotes.producto_id')->count('lotes.producto_id');

        return response()->json([
            'total_lotes'        => $totalLotes,
            'total_medicamentos' => $totalMedicamentos,
        ]);
    }

    /**
     * Crear sesión de toma y generar snapshot de lotes según alcance
     */
    public function store(Request $request)
    {
        // 1. Verificación de Idempotencia y Protección de doble clic
        $idempotencyKey = $request->input('idempotency_key');
        if ($idempotencyKey) {
            $existente = ConteoInventario::where('idempotency_key', $idempotencyKey)->first();
            if ($existente) {
                return redirect()->route('inventario.conteos.show', $existente)
                    ->with('success', "Toma de inventario '{$existente->nombre}' ya iniciada.");
            }
        }

        $validated = $request->validate([
            'nombre'            => ['required', 'string', 'max:200'],
            'notas'             => ['nullable', 'string', 'max:1000'],
            'laboratorios_ids'  => ['nullable', 'array'],
            'laboratorios_ids.*'=> ['integer', 'exists:laboratorios,id'],
            'categorias_ids'    => ['nullable', 'array'],
            'categorias_ids.*'  => ['integer', 'exists:categorias,id'],
            'regimen_venta'     => ['nullable', 'string', 'in:todos,venta_libre,controlados'],
            'alcance_resumen'   => ['nullable', 'array'],
            'idempotency_key'   => ['nullable', 'string', 'max:100'],
        ], [
            'nombre.required' => 'El nombre del conteo es obligatorio.',
        ]);

        $laboratoriosIds = $validated['laboratorios_ids'] ?? [];
        $categoriasIds   = $validated['categorias_ids'] ?? [];
        $regimenVenta    = $validated['regimen_venta'] ?? 'todos';

        try {
            $conteo = DB::transaction(function () use ($validated, $laboratoriosIds, $categoriasIds, $regimenVenta) {
                // Snapshot de lotes activos con stock que cumplen el alcance
                $query = Lote::with(['producto.laboratorio', 'producto.categoria'])
                    ->where('activo', true)
                    ->where('stock_actual', '>', 0)
                    ->whereHas('producto', function ($qp) use ($laboratoriosIds, $categoriasIds, $regimenVenta) {
                        $qp->where('activo', true);

                        if (!empty($laboratoriosIds)) {
                            $qp->whereIn('laboratorio_id', $laboratoriosIds);
                        }

                        if (!empty($categoriasIds)) {
                            $qp->whereIn('categoria_id', $categoriasIds);
                        }

                        if ($regimenVenta === 'venta_libre') {
                            $qp->where(function ($sub) {
                                $sub->where(function ($s) {
                                    $s->where('tipo_control', 'libre')
                                      ->orWhereNull('tipo_control');
                                })->where('requiere_receta', false);
                            });
                        } elseif ($regimenVenta === 'controlados') {
                            $qp->where(function ($sub) {
                                $sub->where('tipo_control', 'controlado')
                                    ->orWhere('requiere_receta', true);
                            });
                        }
                    })
                    ->orderBy('producto_id')
                    ->orderBy('fecha_vencimiento')
                    ->lockForUpdate();

                $lotes = $query->get();

                if ($lotes->isEmpty()) {
                    throw new Exception('No hay lotes activos con stock para el alcance seleccionado.');
                }

                // Generar chips descriptivos del alcance
                $chips = [];
                if (empty($laboratoriosIds) && empty($categoriasIds) && ($regimenVenta === 'todos' || empty($regimenVenta))) {
                    $chips[] = 'Todo el inventario';
                } else {
                    if (!empty($laboratoriosIds)) {
                        $labs = Laboratorio::whereIn('id', $laboratoriosIds)->pluck('nombre')->toArray();
                        foreach ($labs as $lab) {
                            $chips[] = "Lab: {$lab}";
                        }
                    }
                    if (!empty($categoriasIds)) {
                        $cats = Categoria::whereIn('id', $categoriasIds)->pluck('nombre')->toArray();
                        foreach ($cats as $cat) {
                            $chips[] = "Categoría: {$cat}";
                        }
                    }
                    if ($regimenVenta === 'venta_libre') {
                        $chips[] = 'Venta Libre';
                    } elseif ($regimenVenta === 'controlados') {
                        $chips[] = 'Controlados';
                    }
                }

                $conteo = ConteoInventario::create([
                    'nombre'           => $validated['nombre'],
                    'notas'            => $validated['notas'] ?? null,
                    'estado'           => 'en_proceso',
                    'usuario_id'       => auth()->id() ?? 1,
                    'laboratorios_ids' => !empty($laboratoriosIds) ? $laboratoriosIds : null,
                    'categorias_ids'   => !empty($categoriasIds) ? $categoriasIds : null,
                    'regimen_venta'    => $regimenVenta,
                    'alcance_resumen'  => $chips,
                    'idempotency_key'  => $validated['idempotency_key'] ?? null,
                    'total_lotes'      => $lotes->count(),
                    'lotes_contados'   => 0,
                    'diferencia_total_unidades' => 0,
                    'iniciado_en'      => now(),
                ]);

                $detalles = $lotes->map(fn ($lote) => [
                    'conteo_id'    => $conteo->id,
                    'lote_id'      => $lote->id,
                    'producto_id'  => $lote->producto_id,
                    'stock_sistema'=> $lote->stock_actual,
                    'stock_fisico' => null,
                    'diferencia'   => 0,
                    'ajustado'     => false,
                    'created_at'   => now(),
                    'updated_at'   => now(),
                ])->toArray();

                DetalleConteo::insert($detalles);

                return $conteo;
            });

            AuditLog::log('inventario', 'conteo_iniciado', "Toma de inventario iniciada: {$conteo->nombre}", [
                'conteo_id'   => $conteo->id,
                'total_lotes' => $conteo->total_lotes,
                'alcance'     => $conteo->alcance_resumen,
            ]);

            return redirect()->route('inventario.conteos.show', $conteo)
                ->with('success', "Toma de inventario '{$conteo->nombre}' iniciada con {$conteo->total_lotes} lotes en el snapshot.");

        } catch (Exception $e) {
            Log::error('Error al crear conteo de inventario', [
                'user_id' => auth()->id(),
                'message' => $e->getMessage(),
            ]);
            return back()->withInput()->with('error', 'Error al iniciar el conteo: ' . $e->getMessage());
        }
    }

    /**
     * Vista de conteo físico con filtros dinámicos, guardado en tiempo real y modal de aprobación
     */
    public function show(ConteoInventario $conteo)
    {
        $conteo->load(['usuario', 'aprobadoPor']);

        $detalles = DetalleConteo::with([
            'lote',
            'producto.laboratorio:id,nombre',
            'producto.categoria:id,nombre'
        ])
            ->where('conteo_id', $conteo->id)
            ->orderBy('producto_id')
            ->orderBy('lote_id')
            ->get();

        $contados = $detalles->whereNotNull('stock_fisico')->count();
        $conDiferencia = $detalles->where('diferencia', '!=', 0)->whereNotNull('stock_fisico')->count();
        $diferenciaNeta = $detalles->whereNotNull('stock_fisico')->sum('diferencia');
        $progresoPct = $conteo->total_lotes > 0
            ? round(($contados / $conteo->total_lotes) * 100)
            : 0;

        // Lista de laboratorios y categorías presentes en este conteo para los filtros de la barra
        $laboratoriosEnConteo = $detalles->pluck('producto.laboratorio')->filter()->unique('id')->values();
        $categoriasEnConteo   = $detalles->pluck('producto.categoria')->filter()->unique('id')->values();

        return view('inventario.conteos.show', compact(
            'conteo',
            'detalles',
            'contados',
            'conDiferencia',
            'diferenciaNeta',
            'progresoPct',
            'laboratoriosEnConteo',
            'categoriasEnConteo'
        ));
    }

    /**
     * Guardar una sola fila por AJAX (auto-guardado en blur/change)
     */
    public function guardarFila(Request $request, ConteoInventario $conteo)
    {
        if ($conteo->estado !== 'en_proceso') {
            return response()->json(['error' => 'La toma ya no está en proceso.'], 422);
        }

        $validated = $request->validate([
            'detalle_id'   => ['required', 'integer', 'exists:detalles_conteo,id'],
            'stock_fisico' => ['nullable', 'integer', 'min:0'],
        ]);

        $detalle = DetalleConteo::where('id', $validated['detalle_id'])
            ->where('conteo_id', $conteo->id)
            ->firstOrFail();

        $stockFisico = $validated['stock_fisico'] !== null && $validated['stock_fisico'] !== ''
            ? (int)$validated['stock_fisico']
            : null;

        $detalle->stock_fisico = $stockFisico;
        $detalle->diferencia   = $stockFisico !== null ? ($stockFisico - $detalle->stock_sistema) : 0;
        $detalle->save();

        // Recalcular métricas generales del conteo
        $contados = DetalleConteo::where('conteo_id', $conteo->id)->whereNotNull('stock_fisico')->count();
        $difNeta  = DetalleConteo::where('conteo_id', $conteo->id)->whereNotNull('stock_fisico')->sum('diferencia');
        $conDif   = DetalleConteo::where('conteo_id', $conteo->id)->whereNotNull('stock_fisico')->where('diferencia', '!=', 0)->count();

        $conteo->update([
            'lotes_contados'            => $contados,
            'diferencia_total_unidades' => $difNeta,
        ]);

        $faltantes = $conteo->total_lotes - $contados;
        $progresoPct = $conteo->total_lotes > 0 ? round(($contados / $conteo->total_lotes) * 100) : 0;

        return response()->json([
            'success'          => true,
            'detalle_id'       => $detalle->id,
            'stock_fisico'     => $detalle->stock_fisico,
            'diferencia'       => $detalle->stock_fisico !== null ? $detalle->diferencia : null,
            'contados'         => $contados,
            'total_lotes'      => $conteo->total_lotes,
            'faltantes'        => $faltantes,
            'con_diferencia'   => $conDif,
            'diferencia_neta'  => $difNeta,
            'progreso_pct'     => $progresoPct,
            'listo_aprobar'    => ($contados === $conteo->total_lotes && $conteo->total_lotes > 0),
        ]);
    }

    /**
     * Guardar avance manual de cantidades físicas en lote
     */
    public function guardarConteo(Request $request, ConteoInventario $conteo)
    {
        if ($conteo->estado === 'completado') {
            return back()->with('error', 'Este conteo ya fue cerrado y aprobado.');
        }
        if ($conteo->estado === 'cancelado') {
            return back()->with('error', 'Este conteo fue cancelado.');
        }

        $request->validate([
            'cantidades'   => ['required', 'array'],
            'cantidades.*' => ['nullable', 'integer', 'min:0'],
        ]);

        try {
            DB::transaction(function () use ($request, $conteo) {
                $cantidades = $request->input('cantidades', []);

                foreach ($cantidades as $detalleId => $fisico) {
                    $detalle = DetalleConteo::where('id', $detalleId)
                        ->where('conteo_id', $conteo->id)
                        ->first();

                    if (!$detalle) continue;

                    if ($fisico === null || $fisico === '') {
                        $detalle->stock_fisico = null;
                        $detalle->diferencia   = 0;
                    } else {
                        $val = (int)$fisico;
                        $detalle->stock_fisico = $val;
                        $detalle->diferencia   = $val - $detalle->stock_sistema;
                    }
                    $detalle->save();
                }

                $contados = DetalleConteo::where('conteo_id', $conteo->id)
                    ->whereNotNull('stock_fisico')
                    ->count();

                $difTotal = DetalleConteo::where('conteo_id', $conteo->id)
                    ->whereNotNull('stock_fisico')
                    ->sum('diferencia');

                $conteo->update([
                    'lotes_contados'            => $contados,
                    'diferencia_total_unidades' => $difTotal,
                ]);
            });

            return redirect()->route('inventario.conteos.show', $conteo)
                ->with('success', 'Avance guardado correctamente.');

        } catch (Exception $e) {
            Log::error('Error al guardar conteo', [
                'conteo_id' => $conteo->id,
                'user_id'   => auth()->id(),
                'message'   => $e->getMessage(),
            ]);
            return back()->withInput()->with('error', 'Error al guardar: ' . $e->getMessage());
        }
    }

    /**
     * Aprobar toma: en una sola transacción aplica ajustes a cada lote con diferencia y genera Kardex
     */
    public function aprobar(ConteoInventario $conteo)
    {
        if ($conteo->estado !== 'en_proceso') {
            return back()->with('error', 'Solo se puede aprobar un conteo en proceso.');
        }

        $sinContar = DetalleConteo::where('conteo_id', $conteo->id)
            ->whereNull('stock_fisico')
            ->count();

        if ($sinContar > 0) {
            return back()->with('error', "Faltan {$sinContar} lote(s) por contar. Completa todos antes de aprobar.");
        }

        try {
            DB::transaction(function () use ($conteo) {
                $detalles = DetalleConteo::with(['lote', 'producto'])
                    ->where('conteo_id', $conteo->id)
                    ->where('diferencia', '!=', 0)
                    ->where('ajustado', false)
                    ->lockForUpdate()
                    ->get();

                foreach ($detalles as $det) {
                    $lote = Lote::where('id', $det->lote_id)->lockForUpdate()->first();
                    if (!$lote) continue;

                    $tipo = $det->diferencia > 0 ? 'entrada' : 'salida';
                    $userId = auth()->id() ?? 1;

                    $costoUnitario = (float) $lote->precio_compra;
                    $costoTotal = round(abs($det->diferencia) * $costoUnitario, 2);

                    $movimiento = MovimientoInventario::create([
                        'producto_id'      => $det->producto_id,
                        'lote_id'          => $det->lote_id,
                        'user_id'          => $userId,
                        'tipo'             => $tipo,
                        'subtipo'          => 'ajuste_manual',
                        'cantidad'         => $det->diferencia,
                        'stock_anterior'   => $lote->stock_actual,
                        'stock_posterior'  => $det->stock_fisico,
                        'costo_unitario'   => $costoUnitario,
                        'costo_total'      => $costoTotal,
                        'origen'           => 'toma_inventario',
                        'origen_id'        => $conteo->id,
                        'motivo'           => "Ajuste por toma física: {$conteo->nombre}",
                        'fecha_movimiento' => now(),
                    ]);

                    // Asentar en Libro Oficial MINSA si el producto es controlado
                    if ($det->producto && $det->producto->esControlado()) {
                        $tipoMovCtrl = $det->diferencia > 0
                            ? \App\Models\RegistroVentaControlado::TIPO_AJUSTE_INGRESO
                            : \App\Models\RegistroVentaControlado::TIPO_AJUSTE_EGRESO;

                        \App\Models\RegistroVentaControlado::create([
                            'tipo_movimiento'          => $tipoMovCtrl,
                            'movimiento_inventario_id' => $movimiento->id,
                            'producto_id'              => $det->producto_id,
                            'lote_id'                  => $det->lote_id,
                            'nivel_controlado'         => 1,
                            'paciente_nombre'          => 'Regencia Farmacéutica / Auditoría',
                            'motivo_omision'           => "Ajuste por toma física: {$conteo->nombre}",
                            'cantidad'                 => abs($det->diferencia),
                            'unidad'                   => 'unidad',
                            'user_id'                  => $userId,
                        ]);
                    }

                    $lote->stock_actual = $det->stock_fisico;
                    if ($det->stock_fisico === 0) {
                        $lote->activo = false;
                    }
                    $lote->save();

                    $det->ajustado = true;
                    $det->save();
                }

                $conteo->update([
                    'estado'          => 'completado',
                    'aprobado_por_id' => auth()->id(),
                    'completado_en'   => now(),
                ]);

                \Illuminate\Support\Facades\Cache::forget('inventario_valorizacion');
                \App\Services\NotificacionService::clearCache();
            });

            $conDif = DetalleConteo::where('conteo_id', $conteo->id)
                ->where('diferencia', '!=', 0)->count();

            AuditLog::log('inventario', 'conteo_aprobado', "Toma de inventario aprobada: {$conteo->nombre}", [
                'conteo_id'       => $conteo->id,
                'lotes_ajustados' => $conDif,
            ]);

            return redirect()->route('inventario.conteos.show', $conteo)
                ->with('success', "Toma '{$conteo->nombre}' aprobada exitosamente. Se aplicaron ajustes en Kardex a {$conDif} lote(s) con diferencia.");

        } catch (Exception $e) {
            Log::error('Error al aprobar conteo', [
                'conteo_id' => $conteo->id,
                'user_id'   => auth()->id(),
                'message'   => $e->getMessage(),
            ]);
            return back()->with('error', 'Error al aprobar la toma: ' . $e->getMessage());
        }
    }

    /**
     * Cancelar toma sin aplicar ajustes
     */
    public function cancelar(ConteoInventario $conteo)
    {
        if ($conteo->estado === 'completado') {
            return back()->with('error', 'No se puede cancelar una toma ya aprobada y completada.');
        }

        $conteo->update(['estado' => 'cancelado']);

        AuditLog::log('inventario', 'conteo_cancelado', "Toma cancelada: {$conteo->nombre}", [
            'conteo_id' => $conteo->id,
        ]);

        return redirect()->route('inventario.conteos.index')
            ->with('success', "Toma '{$conteo->nombre}' cancelada sin aplicar ajustes.");
    }
}
