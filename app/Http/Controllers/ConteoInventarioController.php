<?php

namespace App\Http\Controllers;

use App\Models\ConteoInventario;
use App\Models\DetalleConteo;
use App\Models\Lote;
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
     * Lista de sesiones de conteo
     */
    public function index()
    {
        $conteos = ConteoInventario::with(['usuario', 'aprobadoPor'])
            ->orderBy('created_at', 'desc')
            ->paginate(15);

        return view('inventario.conteos.index', compact('conteos'));
    }

    /**
     * Formulario para nueva sesión de conteo
     */
    public function create()
    {
        $totalLotesActivos = Lote::where('activo', true)->where('stock_actual', '>', 0)->count();
        return view('inventario.conteos.create', compact('totalLotesActivos'));
    }

    /**
     * Crear sesión y generar snapshot de todos los lotes activos con stock
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'nombre' => ['required', 'string', 'max:200'],
            'notas'  => ['nullable', 'string', 'max:1000'],
        ], [
            'nombre.required' => 'El nombre del conteo es obligatorio.',
        ]);

        try {
            $conteo = DB::transaction(function () use ($validated) {
                // Snapshot de lotes activos con stock
                $lotes = Lote::with('producto')
                    ->where('activo', true)
                    ->where('stock_actual', '>', 0)
                    ->orderBy('producto_id')
                    ->orderBy('fecha_vencimiento')
                    ->get();

                $conteo = ConteoInventario::create([
                    'nombre'      => $validated['nombre'],
                    'notas'       => $validated['notas'] ?? null,
                    'estado'      => 'en_proceso',
                    'usuario_id'  => auth()->id(),
                    'total_lotes' => $lotes->count(),
                    'lotes_contados' => 0,
                    'iniciado_en' => now(),
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

            return redirect()->route('inventario.conteos.show', $conteo)
                ->with('success', "Sesión de conteo '{$conteo->nombre}' iniciada con {$conteo->total_lotes} lotes.");

        } catch (Exception $e) {
            Log::error('Error al crear conteo de inventario', [
                'user_id' => auth()->id(),
                'message' => $e->getMessage(),
            ]);
            return back()->withInput()->with('error', 'Error al iniciar el conteo: ' . $e->getMessage());
        }
    }

    /**
     * Vista de conteo con formulario de cantidades físicas
     */
    public function show(ConteoInventario $conteo)
    {
        $conteo->load(['usuario', 'aprobadoPor']);

        $detalles = DetalleConteo::with(['lote', 'producto.laboratorio'])
            ->where('conteo_id', $conteo->id)
            ->orderBy('producto_id')
            ->orderBy('lote_id')
            ->get();

        $contados = $detalles->whereNotNull('stock_fisico')->count();
        $conDiferencia = $detalles->where('diferencia', '!=', 0)->whereNotNull('stock_fisico')->count();
        $progresoPct = $conteo->total_lotes > 0
            ? round(($contados / $conteo->total_lotes) * 100)
            : 0;

        return view('inventario.conteos.show', compact(
            'conteo', 'detalles', 'contados', 'conDiferencia', 'progresoPct'
        ));
    }

    /**
     * Guardar cantidades físicas ingresadas por el usuario
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
                    if ($fisico === null || $fisico === '') continue;

                    $detalle = DetalleConteo::where('id', $detalleId)
                        ->where('conteo_id', $conteo->id)
                        ->first();

                    if (!$detalle) continue;

                    $fisico = (int) $fisico;
                    $detalle->stock_fisico = $fisico;
                    $detalle->diferencia   = $fisico - $detalle->stock_sistema;
                    $detalle->save();
                }

                // Recalcular resumen
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
                ->with('success', 'Cantidades guardadas. Revisa las diferencias antes de aprobar.');

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
     * Aprobar conteo: aplicar diferencias como ajustes en Kardex
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
                $detalles = DetalleConteo::with('lote')
                    ->where('conteo_id', $conteo->id)
                    ->where('diferencia', '!=', 0)
                    ->where('ajustado', false)
                    ->lockForUpdate()
                    ->get();

                foreach ($detalles as $det) {
                    $lote = Lote::where('id', $det->lote_id)->lockForUpdate()->first();
                    if (!$lote) continue;

                    $tipo = $det->diferencia > 0 ? 'entrada' : 'salida';

                    MovimientoInventario::create([
                        'producto_id'      => $det->producto_id,
                        'lote_id'          => $det->lote_id,
                        'usuario_id'       => auth()->id(),
                        'tipo'             => $tipo,
                        'subtipo'          => 'ajuste_manual',
                        'cantidad'         => abs($det->diferencia),
                        'stock_anterior'   => $lote->stock_actual,
                        'stock_nuevo'      => $det->stock_fisico,
                        'motivo'           => "Toma de inventario: {$conteo->nombre}",
                        'fecha_movimiento' => now(),
                    ]);

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
            });

            $conDif = DetalleConteo::where('conteo_id', $conteo->id)
                ->where('diferencia', '!=', 0)->count();

            AuditLog::log('inventario', 'conteo_aprobado', "Toma de inventario aprobada: {$conteo->nombre}", [
                'conteo_id'       => $conteo->id,
                'lotes_ajustados' => $conDif,
            ]);

            return redirect()->route('inventario.conteos.show', $conteo)
                ->with('success', "Conteo '{$conteo->nombre}' aprobado. Se aplicaron ajustes a {$conDif} lote(s) con diferencia.");

        } catch (Exception $e) {
            Log::error('Error al aprobar conteo', [
                'conteo_id' => $conteo->id,
                'user_id'   => auth()->id(),
                'message'   => $e->getMessage(),
            ]);
            return back()->with('error', 'Error al aprobar el conteo: ' . $e->getMessage());
        }
    }

    /**
     * Cancelar conteo sin aplicar ajustes
     */
    public function cancelar(ConteoInventario $conteo)
    {
        if ($conteo->estado === 'completado') {
            return back()->with('error', 'No se puede cancelar un conteo ya completado.');
        }

        $conteo->update(['estado' => 'cancelado']);

        AuditLog::log('inventario', 'conteo_cancelado', "Conteo cancelado: {$conteo->nombre}", [
            'conteo_id' => $conteo->id,
        ]);

        return redirect()->route('inventario.conteos.index')
            ->with('success', "Conteo '{$conteo->nombre}' cancelado sin aplicar ajustes.");
    }
}
