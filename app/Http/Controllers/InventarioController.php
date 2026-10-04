<?php

namespace App\Http\Controllers;

use App\Models\Producto;
use App\Models\Lote;
use App\Models\Proveedor;
use App\Models\Laboratorio;
use App\Models\Categoria;
use App\Models\MovimientoInventario;
use App\Models\AuditLog;
use App\Services\InventarioService;
use App\Http\Requests\AjusteInventarioRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Database\QueryException;
use Exception;

class InventarioController extends Controller
{
    protected InventarioService $inventarioService;

    public function __construct(InventarioService $inventarioService)
    {
        $this->inventarioService = $inventarioService;
        $this->middleware('permission:ver movimientos inventario')->only(['index', 'movimientos', 'lotes', 'kardexProducto', 'alertas']);
        $this->middleware('permission:ajustar inventario')->only(['ajustar', 'storeAjuste', 'bajaVencidos', 'createLote', 'storeLote']);
        $this->middleware('permission:editar lotes|ajustar inventario')->only(['updateLote']);
    }

    public function index()
    {
        // Valorización del inventario completa (SUM sobre lotes) — cacheada 60s
        // Se invalida con Cache::forget('inventario_valorizacion') en movimientos de stock
        $valorizacion = Cache::remember('inventario_valorizacion', 60, function () {
            return $this->inventarioService->valorizacionInventario(true);
        });
        $productosBajoStock = $this->inventarioService->productosConStockBajo();
        $lotesPorVencer    = $this->inventarioService->lotesProximosVencer(60);
        $lotesVencidos     = $this->inventarioService->lotesVencidos();

        return view('inventario.index', compact('valorizacion', 'productosBajoStock', 'lotesPorVencer', 'lotesVencidos'));
    }

    public function movimientos(Request $request)
    {
        $query = MovimientoInventario::with([
            'producto:id,nombre,principio_activo,laboratorio_id',
            'producto.laboratorio:id,nombre',
            'lote:id,numero_lote,fecha_vencimiento',
            'usuario:id,name',
        ]);


        if ($request->filled('producto_id')) {
            $query->where('producto_id', $request->input('producto_id'));
        }

        if ($request->filled('tipo')) {
            $query->where('tipo', $request->input('tipo'));
        }

        if ($request->filled('subtipo')) {
            $query->where('subtipo', $request->input('subtipo'));
        }

        if ($request->filled('fecha_desde')) {
            $query->whereDate('fecha_movimiento', '>=', $request->input('fecha_desde'));
        }

        if ($request->filled('fecha_hasta')) {
            $query->whereDate('fecha_movimiento', '<=', $request->input('fecha_hasta'));
        }

        $movimientos = $query->orderBy('fecha_movimiento', 'desc')
            ->orderBy('id', 'desc')
            ->paginate(perPage(20))
            ->withQueryString();

        $productos = Producto::activos()->orderBy('nombre')->get(['id', 'nombre']);

        return view('inventario.movimientos', compact('movimientos', 'productos'));
    }

    public function lotes(Request $request)
    {
        $query = Lote::with([
            'producto:id,nombre,principio_activo,categoria_id,laboratorio_id',
            'producto.categoria:id,nombre',
            'producto.laboratorio:id,nombre',
            'proveedor:id,nombre',
        ])->where('activo', true);


        if ($request->filled('buscar')) {
            $buscar = trim($request->input('buscar'));
            $query->where(function ($q) use ($buscar) {
                $q->where('numero_lote', 'like', "%{$buscar}%")
                  ->orWhereHas('producto', function ($qp) use ($buscar) {
                      $qp->where('nombre', 'like', "%{$buscar}%")
                         ->orWhere('principio_activo', 'like', "%{$buscar}%");
                  });
            });
        }

        if ($request->filled('filtro_vencimiento')) {
            if ($request->input('filtro_vencimiento') === 'vencidos') {
                $query->vencidos();
            } elseif ($request->input('filtro_vencimiento') === 'proximos_30') {
                $query->proximosVencer(30);
            } elseif ($request->input('filtro_vencimiento') === 'proximos_60') {
                $query->proximosVencer(60);
            }
        }

        $orden = $request->input('orden', 'vencimiento_asc');
        switch ($orden) {
            case 'vencimiento_desc':
                $query->orderBy('fecha_vencimiento', 'desc');
                break;
            case 'ingreso_desc':
                $query->orderBy('created_at', 'desc')->orderBy('id', 'desc');
                break;
            case 'ingreso_asc':
                $query->orderBy('created_at', 'asc')->orderBy('id', 'asc');
                break;
            case 'stock_desc':
                $query->orderBy('stock_actual', 'desc');
                break;
            case 'stock_asc':
                $query->orderBy('stock_actual', 'asc');
                break;
            case 'vencimiento_asc':
            default:
                $query->orderBy('fecha_vencimiento', 'asc');
                break;
        }

        $lotes = $query->paginate(perPage(15))->withQueryString();
        $proveedores = Proveedor::where('activo', true)->orderBy('nombre')->get(['id', 'nombre']);

        return view('inventario.lotes', compact('lotes', 'proveedores'));
    }

    /**
     * Actualizar metadatos de un lote (número de lote, fecha de vencimiento, proveedor)
     */
    public function updateLote(Request $request, Lote $lote)
    {
        $validated = $request->validate([
            'numero_lote'       => 'required|string|max:100',
            'fecha_vencimiento' => 'required|date',
            'proveedor_id'      => 'nullable|exists:proveedores,id',
            'motivo_cambio'     => 'required|string|min:5|max:255',
        ], [
            'numero_lote.required'       => 'El número de lote es obligatorio.',
            'fecha_vencimiento.required' => 'La fecha de vencimiento es obligatoria.',
            'motivo_cambio.required'     => 'El motivo de la modificación es obligatorio para auditoría regulatoria.',
            'motivo_cambio.min'          => 'El motivo debe tener al menos 5 caracteres.',
        ]);

        try {
            $this->inventarioService->actualizarMetadatosLote($lote, $validated);

            return redirect()->route('inventario.lotes')
                ->with('success', "Los metadatos del lote '{$lote->numero_lote}' han sido actualizados exitosamente con auditoría.");
        } catch (Exception $e) {
            Log::error('Error al actualizar metadatos del lote', [
                'lote_id' => $lote->id,
                'user_id' => auth()->id(),
                'error'   => $e->getMessage(),
            ]);

            return back()->withInput()->with('error', 'Error al actualizar metadatos del lote: ' . $e->getMessage());
        }
    }

    public function kardexProducto(Producto $producto, Request $request)
    {
        $fechaDesde = $request->input('fecha_desde');
        $fechaHasta = $request->input('fecha_hasta');

        $movimientos = $this->inventarioService->kardexProducto($producto->id, $fechaDesde, $fechaHasta);
        $producto->load(['categoria', 'laboratorio', 'presentacionesActivas', 'lotes']);

        return view('inventario.kardex-producto', compact('producto', 'movimientos'));
    }

    public function alertas()
    {
        $productosBajoStock = $this->inventarioService->productosConStockBajo();
        $lotesPorVencer = $this->inventarioService->lotesProximosVencer(60);
        $lotesVencidos = $this->inventarioService->lotesVencidos();

        return view('inventario.alertas', compact('productosBajoStock', 'lotesPorVencer', 'lotesVencidos'));
    }

    public function ajustar()
    {
        $lotes = Lote::select(['id', 'producto_id', 'numero_lote', 'stock_actual', 'fecha_vencimiento'])
            ->with(['producto:id,nombre,principio_activo'])
            ->where('activo', true)
            ->where('stock_actual', '>', 0)
            ->orderBy('numero_lote', 'asc')
            ->get();

        return view('inventario.ajustar', compact('lotes'));
    }

    public function storeAjuste(AjusteInventarioRequest $request)
    {
        try {
            $movimiento = $this->inventarioService->ajustarInventario($request->validated());

            AuditLog::log('inventario', 'ajuste', "Ajuste de inventario en lote {$movimiento->lote->numero_lote} ({$movimiento->producto->nombre})", [
                'movimiento_id' => $movimiento->id,
                'tipo' => $movimiento->tipo,
                'cantidad' => $movimiento->cantidad,
                'motivo' => $movimiento->motivo,
            ]);

            return redirect()->route('inventario.movimientos')
                ->with('success', "Ajuste de inventario aplicado exitosamente en el Kardex para el lote '{$movimiento->lote->numero_lote}' ({$movimiento->producto->nombre}).");
        } catch (QueryException $qe) {
            Log::error('Error de base de datos en ajuste de inventario', [
                'user_id' => auth()->id(),
                'payload' => $request->except(['_token']),
                'message' => $qe->getMessage(),
            ]);

            return back()->withInput()->with('error', 'Error al procesar el ajuste en la base de datos debido a un conflicto de concurrencia o integridad.');
        } catch (Exception $e) {
            Log::error('Excepción en ajuste de inventario', [
                'user_id' => auth()->id(),
                'payload' => $request->except(['_token']),
                'message' => $e->getMessage(),
            ]);

            return back()->withInput()->with('error', $e->getMessage());
        }
    }

    /**
     * Dar de baja automática a lotes vencidos
     */
    public function bajaVencidos()
    {
        try {
            $totalBajas = $this->inventarioService->desactivarLotesVencidos();

            AuditLog::log('inventario', 'baja_vencidos', "Baja automática de {$totalBajas} lote(s) vencido(s)", [
                'total_bajas' => $totalBajas,
            ]);

            return redirect()->route('inventario.alertas')
                ->with('success', "Se procesó la baja automática de {$totalBajas} lote(s) vencido(s) con registro en Kardex.");
        } catch (Exception $e) {
            Log::error('Error al ejecutar baja de lotes vencidos', [
                'user_id' => auth()->id(),
                'message' => $e->getMessage(),
            ]);

            return back()->with('error', 'Error al procesar la baja de lotes vencidos: ' . $e->getMessage());
        }
    }

    /**
     * Formulario para crear un lote manualmente (stock de apertura, migración, donaciones, etc.)
     */
    public function createLote(Request $request)
    {
        $productos = Producto::activos()->orderBy('nombre')->get(['id', 'nombre', 'principio_activo']);
        $proveedores = Proveedor::where('activo', true)->orderBy('nombre')->get(['id', 'nombre']);
        $productoPreseleccionado = $request->filled('producto_id')
            ? Producto::find($request->input('producto_id'))
            : null;

        return view('inventario.lote-crear', compact('productos', 'proveedores', 'productoPreseleccionado'));
    }

    /**
     * Guardar lote manual con movimiento de entrada en Kardex
     */
    public function storeLote(Request $request)
    {
        $validated = $request->validate([
            'producto_id'       => ['required', 'integer', 'exists:productos,id'],
            'numero_lote'       => ['required', 'string', 'max:100'],
            'fecha_vencimiento' => ['required', 'date', 'after:today'],
            'cantidad'          => ['required', 'integer', 'min:1'],
            'precio_compra'     => ['nullable', 'numeric', 'min:0'],
            'proveedor_id'      => ['nullable', 'integer', 'exists:proveedores,id'],
            'motivo'            => ['required', 'string', 'min:5', 'max:500'],
        ], [
            'producto_id.required'       => 'Selecciona el medicamento.',
            'producto_id.exists'         => 'El medicamento no existe.',
            'numero_lote.required'       => 'El número de lote es obligatorio.',
            'fecha_vencimiento.required' => 'La fecha de vencimiento es obligatoria.',
            'fecha_vencimiento.after'    => 'La fecha de vencimiento debe ser una fecha futura.',
            'cantidad.required'          => 'La cantidad inicial es obligatoria.',
            'cantidad.min'               => 'La cantidad debe ser al menos 1 unidad.',
            'motivo.required'            => 'El motivo es obligatorio para auditoría regulatoria.',
            'motivo.min'                 => 'El motivo debe tener al menos 5 caracteres.',
        ]);

        try {
            $lote = DB::transaction(function () use ($validated) {
                $lote = Lote::create([
                    'producto_id'       => $validated['producto_id'],
                    'compra_id'         => null,
                    'proveedor_id'      => $validated['proveedor_id'] ?? null,
                    'numero_lote'       => $validated['numero_lote'],
                    'fecha_vencimiento' => $validated['fecha_vencimiento'],
                    'stock_inicial'     => $validated['cantidad'],
                    'stock_actual'      => $validated['cantidad'],
                    'precio_compra'     => $validated['precio_compra'] ?? 0,
                    'activo'            => true,
                ]);

                MovimientoInventario::create([
                    'producto_id'       => $lote->producto_id,
                    'lote_id'           => $lote->id,
                    'user_id'           => auth()->id() ?? 1,
                    'tipo'              => 'entrada',
                    'subtipo'           => 'ajuste_manual',
                    'cantidad'          => $validated['cantidad'],
                    'stock_anterior'    => 0,
                    'stock_posterior'   => $validated['cantidad'],
                    'motivo'            => 'Lote manual: ' . $validated['motivo'],
                    'fecha_movimiento'  => now(),
                ]);

                return $lote;
            });

            AuditLog::log('inventario', 'lote_manual', "Lote manual creado: {$lote->numero_lote} ({$lote->producto->nombre})", [
                'lote_id'    => $lote->id,
                'producto_id'=> $lote->producto_id,
                'cantidad'   => $validated['cantidad'],
                'motivo'     => $validated['motivo'],
            ]);

            return redirect()->route('inventario.lotes')
                ->with('success', "Lote '{$lote->numero_lote}' de {$lote->producto->nombre} creado con {$validated['cantidad']} unidades en inventario.");

        } catch (Exception $e) {
            Log::error('Error al crear lote manual', [
                'user_id' => auth()->id(),
                'payload' => $request->except(['_token']),
                'message' => $e->getMessage(),
            ]);

            return back()->withInput()->with('error', 'Error al crear el lote: ' . $e->getMessage());
        }
    }

    /**
     * Búsqueda AJAX de Medicamentos (Componente C)
     */
    public function buscarMedicamentosAjax(Request $request)
    {
        $q = trim($request->input('q', ''));
        if (strlen($q) < 2) {
            return response()->json([]);
        }

        $medicamentos = Producto::with(['laboratorio:id,nombre'])
            ->where('activo', true)
            ->where(function ($query) use ($q) {
                $query->where('nombre', 'like', "%{$q}%")
                      ->orWhere('principio_activo', 'like', "%{$q}%")
                      ->orWhere('codigo_barras', 'like', "%{$q}%");
            })
            ->limit(10)
            ->get(['id', 'nombre', 'presentacion', 'principio_activo', 'laboratorio_id', 'codigo_barras', 'tipo_control', 'requiere_receta', 'precio_venta'])
            ->map(function ($med) {
                return [
                    'id' => $med->id,
                    'nombre' => $med->nombre . ($med->presentacion ? " - {$med->presentacion}" : ''),
                    'nombre_simple' => $med->nombre,
                    'presentacion' => $med->presentacion,
                    'principio_activo' => $med->principio_activo,
                    'laboratorio' => $med->laboratorio->nombre ?? 'Sin laboratorio',
                    'codigo_barras' => $med->codigo_barras ?? 'S/C',
                    'tipo_control' => $med->tipo_control,
                    'requiere_receta' => $med->requiere_receta,
                    'precio_venta' => $med->precio_venta,
                ];
            });

        return response()->json($medicamentos);
    }

    /**
     * Búsqueda AJAX de Proveedores (Componente C)
     */
    public function buscarProveedoresAjax(Request $request)
    {
        $q = trim($request->input('q', ''));
        $query = Proveedor::where('activo', true);

        if (strlen($q) >= 2) {
            $query->where(function ($sub) use ($q) {
                $sub->where('nombre', 'like', "%{$q}%")
                    ->orWhere('ruc', 'like', "%{$q}%")
                    ->orWhere('contacto', 'like', "%{$q}%")
                    ->orWhere('ciudad', 'like', "%{$q}%");
            });
        }

        $proveedores = $query->limit(10)
            ->get(['id', 'nombre', 'contacto', 'ciudad', 'ruc', 'telefono'])
            ->map(function ($prov) {
                return [
                    'id' => $prov->id,
                    'nombre' => $prov->nombre,
                    'contacto' => $prov->contacto ?: ($prov->ciudad ?: 'Proveedor Nacional'),
                    'ruc' => $prov->ruc ? "RUC: {$prov->ruc}" : ($prov->telefono ? "Tel: {$prov->telefono}" : 'S/RUC'),
                ];
            });

        return response()->json($proveedores);
    }

    /**
     * Búsqueda AJAX de Laboratorios (Componente C)
     */
    public function buscarLaboratoriosAjax(Request $request)
    {
        $q = trim($request->input('q', ''));
        $query = Laboratorio::where('activo', true);

        if (strlen($q) > 0) {
            $query->where('nombre', 'like', "%{$q}%");
        }

        $labs = $query->orderBy('nombre')
            ->limit(15)
            ->get(['id', 'nombre']);

        return response()->json($labs);
    }

    /**
     * Búsqueda AJAX de Categorías (Componente C)
     */
    public function buscarCategoriasAjax(Request $request)
    {
        $q = trim($request->input('q', ''));
        $query = Categoria::where('activa', true);

        if (strlen($q) > 0) {
            $query->where('nombre', 'like', "%{$q}%");
        }

        $categorias = $query->orderBy('nombre')
            ->limit(15)
            ->get(['id', 'nombre']);

        return response()->json($categorias);
    }
}
