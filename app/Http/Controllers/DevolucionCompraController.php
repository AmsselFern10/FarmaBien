<?php

namespace App\Http\Controllers;

use App\Models\DevolucionCompra;
use App\Models\DetalleDevolucionCompra;
use App\Models\Lote;
use App\Models\Proveedor;
use App\Models\Compra;
use App\Models\MovimientoInventario;
use App\Models\RegistroVentaControlado;
use App\Models\AuditLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Exception;

class DevolucionCompraController extends Controller
{
    public function __construct()
    {
        $this->middleware('permission:ajustar inventario');
    }

    public function index(Request $request)
    {
        $query = DevolucionCompra::with(['proveedor', 'usuario']);

        if ($request->filled('buscar')) {
            $buscar = trim($request->buscar);
            $query->where(function ($q) use ($buscar) {
                $q->where('numero_devolucion', 'like', "%{$buscar}%")
                  ->orWhereHas('proveedor', fn($p) => $p->where('nombre', 'like', "%{$buscar}%")->orWhere('ruc', 'like', "%{$buscar}%"));
            });
        }

        if ($request->filled('estado')) {
            $query->where('estado', $request->estado);
        }

        if ($request->filled('fecha_desde')) {
            $query->whereDate('created_at', '>=', $request->fecha_desde);
        }

        if ($request->filled('fecha_hasta')) {
            $query->whereDate('created_at', '<=', $request->fecha_hasta);
        }

        $devoluciones = $query->orderBy('created_at', 'desc')->paginate(perPage(20))->withQueryString();

        return view('compras.devoluciones.index', compact('devoluciones'));
    }

    public function create(Request $request)
    {
        $proveedores = Proveedor::where('activo', true)->orderBy('nombre')->get(['id', 'nombre']);

        $compraId = $request->input('compra_id');
        $loteIds = $request->input('lote_ids');
        if (is_string($loteIds)) {
            $loteIds = array_filter(explode(',', $loteIds));
        }

        $compra = null;
        $detallesDisponibles = [];
        $proveedorSeleccionado = null;

        if ($compraId) {
            $compra = Compra::with(['proveedor', 'usuario', 'lotes.producto.laboratorio'])->find($compraId);
            if ($compra) {
                $proveedorSeleccionado = $compra->proveedor;
                foreach ($compra->lotes as $lote) {
                    $yaDevuelta = (int) DetalleDevolucionCompra::where('lote_id', $lote->id)->sum('cantidad');
                    $disponible = max(0, min($lote->stock_inicial - $yaDevuelta, $lote->stock_actual));

                    $detallesDisponibles[] = [
                        'lote'                 => $lote,
                        'cantidad_comprada'    => $lote->stock_inicial,
                        'cantidad_ya_devuelta' => $yaDevuelta,
                        'cantidad_disponible'  => $disponible,
                        'precio_unitario'      => (float) ($lote->precio_compra ?? 0),
                        'es_controlado'        => (bool) ($lote->producto?->esControlado()),
                    ];
                }
            }
        } elseif (!empty($loteIds)) {
            $lotesSel = Lote::with(['producto.laboratorio', 'proveedor', 'compra'])->whereIn('id', (array)$loteIds)->get();
            if ($lotesSel->isNotEmpty()) {
                $proveedorSeleccionado = $lotesSel->first()->proveedor;
                foreach ($lotesSel as $lote) {
                    $yaDevuelta = (int) DetalleDevolucionCompra::where('lote_id', $lote->id)->sum('cantidad');
                    $disponible = max(0, min($lote->stock_inicial - $yaDevuelta, $lote->stock_actual));

                    $detallesDisponibles[] = [
                        'lote'                 => $lote,
                        'cantidad_comprada'    => $lote->stock_inicial,
                        'cantidad_ya_devuelta' => $yaDevuelta,
                        'cantidad_disponible'  => $disponible,
                        'precio_unitario'      => (float) ($lote->precio_compra ?? 0),
                        'es_controlado'        => (bool) ($lote->producto?->esControlado()),
                    ];
                }
            }
        }

        // Búsqueda de compras recientes con lotes devolvibles
        $buscarCompra = $request->input('buscar_compra');
        $comprasRecientesQuery = Compra::with(['proveedor', 'usuario', 'lotes'])
            ->where('estado', 'recibida')
            ->whereHas('lotes', function($q) {
                $q->where('stock_actual', '>', 0);
            });

        if (!empty($buscarCompra)) {
            $comprasRecientesQuery->where(function($q) use ($buscarCompra) {
                $q->where('numero_comprobante', 'like', "%{$buscarCompra}%")
                  ->orWhereHas('proveedor', fn($p) => $p->where('nombre', 'like', "%{$buscarCompra}%")->orWhere('ruc', 'like', "%{$buscarCompra}%"))
                  ->orWhereHas('lotes.producto', fn($pr) => $pr->where('nombre', 'like', "%{$buscarCompra}%"));
            });
        }
        $comprasRecientes = $comprasRecientesQuery->orderBy('created_at', 'desc')->paginate(10, ['*'], 'page_compras')->withQueryString();

        // Lotes disponibles para el modo "Por Lote"
        $buscarLote = $request->input('buscar_lote');
        $lotesDisponiblesQuery = Lote::with(['producto.laboratorio', 'proveedor', 'compra'])
            ->where('activo', true)
            ->where('stock_actual', '>', 0);

        if (!empty($buscarLote)) {
            $lotesDisponiblesQuery->where(function($q) use ($buscarLote) {
                $q->where('numero_lote', 'like', "%{$buscarLote}%")
                  ->orWhereHas('producto', fn($p) => $p->where('nombre', 'like', "%{$buscarLote}%")->orWhere('codigo_barra', 'like', "%{$buscarLote}%"))
                  ->orWhereHas('proveedor', fn($pr) => $pr->where('nombre', 'like', "%{$buscarLote}%"));
            });
        }
        $lotesDisponibles = $lotesDisponiblesQuery->orderBy('fecha_vencimiento', 'asc')->paginate(15, ['*'], 'page_lotes')->withQueryString();

        // Catálogo para adición rápida
        $todosLotes = Lote::with(['producto', 'proveedor'])
            ->where('activo', true)
            ->where('stock_actual', '>', 0)
            ->orderBy('fecha_vencimiento')
            ->get();

        return view('compras.devoluciones.create', compact(
            'proveedores',
            'compra',
            'detallesDisponibles',
            'proveedorSeleccionado',
            'comprasRecientes',
            'lotesDisponibles',
            'todosLotes'
        ));
    }

    public function store(Request $request)
    {
        $request->validate([
            'proveedor_id'              => ['required', 'integer', 'exists:proveedores,id'],
            'motivo'                    => ['required', 'string', 'min:5', 'max:1000'],
            'compra_id'                 => ['nullable', 'integer', 'exists:compras,id'],
            'items'                     => ['required', 'array', 'min:1'],
            'items.*.lote_id'           => ['required', 'integer', 'exists:lotes,id'],
            'items.*.cantidad'          => ['required', 'integer', 'min:1'],
            'items.*.precio_unitario'   => ['nullable', 'numeric', 'min:0'],
            'items.*.motivo_detalle'    => ['nullable', 'string', 'max:300'],
        ], [
            'proveedor_id.required' => 'Selecciona el proveedor.',
            'motivo.required'       => 'El motivo es obligatorio.',
            'motivo.min'            => 'El motivo debe tener al menos 5 caracteres.',
            'items.required'        => 'Agrega al menos un lote a devolver.',
            'items.min'             => 'Agrega al menos un lote a devolver.',
            'items.*.lote_id.required'  => 'Selecciona el lote.',
            'items.*.cantidad.required' => 'La cantidad es obligatoria.',
            'items.*.cantidad.min'      => 'La cantidad debe ser al menos 1.',
        ]);

        try {
            $devolucion = DB::transaction(function () use ($request) {
                $userId = auth()->id() ?? 1;

                $devolucion = DevolucionCompra::create([
                    'numero_devolucion' => DevolucionCompra::generarNumero(),
                    'proveedor_id'      => $request->proveedor_id,
                    'compra_id'         => $request->compra_id ?: null,
                    'usuario_id'        => $userId,
                    'estado'            => 'pendiente',
                    'motivo'            => $request->motivo,
                    'total_devolucion'  => 0,
                ]);

                $total = 0;

                foreach ($request->items as $item) {
                    $lote = Lote::with('producto')->where('id', $item['lote_id'])->lockForUpdate()->firstOrFail();

                    $cantidad = (int) $item['cantidad'];
                    if ($cantidad <= 0) {
                        continue;
                    }

                    if ($cantidad > $lote->stock_actual) {
                        throw new Exception(
                            "Stock insuficiente para el lote {$lote->numero_lote} del producto {$lote->producto->nombre}. "
                          . "Stock actual: {$lote->stock_actual}, solicitado: {$cantidad}."
                        );
                    }

                    $precioUnitario = (float) ($item['precio_unitario'] ?? $lote->precio_compra ?? 0);
                    $subtotal       = $precioUnitario * $cantidad;
                    $total         += $subtotal;

                    // 1. Guardar detalle
                    DetalleDevolucionCompra::create([
                        'devolucion_compra_id' => $devolucion->id,
                        'lote_id'              => $lote->id,
                        'producto_id'          => $lote->producto_id,
                        'cantidad'             => $cantidad,
                        'precio_unitario'      => $precioUnitario,
                        'subtotal'             => $subtotal,
                        'motivo_detalle'       => $item['motivo_detalle'] ?? null,
                    ]);

                    // 2. Descontar stock del lote (salida)
                    $stockAntes = $lote->stock_actual;
                    $lote->stock_actual -= $cantidad;
                    if ($lote->stock_actual === 0) {
                        $lote->activo = false;
                    }
                    $lote->save();

                    // 3. Registrar movimiento Kardex
                    $mov = MovimientoInventario::create([
                        'producto_id'      => $lote->producto_id,
                        'lote_id'          => $lote->id,
                        'user_id'          => $userId,
                        'tipo'             => 'salida',
                        'subtipo'          => 'ajuste_manual',
                        'cantidad'         => $cantidad,
                        'stock_anterior'   => $stockAntes,
                        'stock_posterior'  => $lote->stock_actual,
                        'costo_unitario'   => $precioUnitario,
                        'costo_total'      => $subtotal,
                        'origen'           => 'devolucion_compra',
                        'origen_id'        => $devolucion->id,
                        'motivo'           => 'Dev. proveedor ' . $devolucion->numero_devolucion . ': ' . $request->motivo,
                        'fecha_movimiento' => now(),
                    ]);

                    // 4. Si el producto es controlado MINSA -> AJUSTE_EGRESO en libro de controlados
                    $producto = $lote->producto;
                    if ($producto && $producto->esControlado()) {
                        RegistroVentaControlado::create([
                            'tipo_movimiento'          => RegistroVentaControlado::TIPO_AJUSTE_EGRESO,
                            'movimiento_inventario_id' => $mov->id,
                            'producto_id'              => $producto->id,
                            'lote_id'                  => $lote->id,
                            'nivel_controlado'         => $producto->nivel_controlado ?? 1,
                            'cantidad'                 => $cantidad,
                            'unidad'                   => $producto->unidad_medida ?? 'unidad',
                            'motivo_omision'           => 'Devolucion a proveedor: ' . $devolucion->numero_devolucion,
                            'user_id'                  => $userId,
                        ]);
                    }
                }

                $devolucion->update(['total_devolucion' => $total]);

                return $devolucion;
            });

            AuditLog::log('compras', 'devolucion_compra', "Devolucion a proveedor: {$devolucion->numero_devolucion}", [
                'devolucion_id' => $devolucion->id,
                'proveedor_id'  => $devolucion->proveedor_id,
                'total'         => $devolucion->total_devolucion,
            ]);

            return redirect()->route('compras.devoluciones.show', $devolucion)
                ->with('success', "Devolucion {$devolucion->numero_devolucion} registrada. Stock descontado y Kardex actualizado.");

        } catch (Exception $e) {
            Log::error('Error al registrar devolucion a proveedor', [
                'user_id' => auth()->id(),
                'message' => $e->getMessage(),
            ]);
            return back()->withInput()->with('error', $e->getMessage());
        }
    }

    public function show(DevolucionCompra $devolucionCompra)
    {
        $devolucionCompra->load([
            'proveedor', 'usuario', 'compra',
            'detalles.lote', 'detalles.producto.laboratorio',
        ]);

        return view('compras.devoluciones.show', compact('devolucionCompra'));
    }

    /**
     * Marcar como enviada al proveedor
     */
    public function marcarEnviada(DevolucionCompra $devolucionCompra)
    {
        if ($devolucionCompra->estado !== 'pendiente') {
            return back()->with('error', 'Solo se pueden enviar devoluciones en estado pendiente.');
        }

        $devolucionCompra->update([
            'estado'      => 'enviada',
            'fecha_envio' => now(),
        ]);

        return back()->with('success', 'Devolucion marcada como enviada al proveedor.');
    }

    /**
     * Confirmar que el proveedor acepto
     */
    public function confirmar(DevolucionCompra $devolucionCompra)
    {
        if (!in_array($devolucionCompra->estado, ['pendiente', 'enviada'])) {
            return back()->with('error', 'Solo se pueden confirmar devoluciones pendientes o enviadas.');
        }

        $devolucionCompra->update(['estado' => 'confirmada']);
        AuditLog::log('compras', 'devolucion_confirmada', "Devolucion confirmada: {$devolucionCompra->numero_devolucion}", [
            'devolucion_id' => $devolucionCompra->id,
        ]);

        return back()->with('success', 'Devolucion confirmada por el proveedor.');
    }
}
