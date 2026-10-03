<?php

namespace App\Http\Controllers;

use App\Models\Compra;
use App\Models\Proveedor;
use App\Models\Producto;
use App\Models\Lote;
use App\Models\AuditLog;
use App\Services\CompraService;
use App\Http\Requests\StoreCompraRequest;
use App\Http\Requests\UpdateCompraRequest;
use App\Http\Requests\AnularCompraRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Database\QueryException;
use Barryvdh\DomPDF\Facade\Pdf;
use Exception;

class CompraController extends Controller
{
    protected CompraService $compraService;

    public function __construct(CompraService $compraService)
    {
        $this->compraService = $compraService;
        $this->middleware('permission:ver compras')->only(['index', 'show', 'imprimir', 'generarPDF', 'imprimirTicket']);
        $this->middleware('permission:registrar compras')->only(['create', 'store', 'edit', 'update', 'verificarNumeroLote']);
        $this->middleware('permission:anular compras')->only(['anular']);
    }

    public function index(Request $request)
    {
        $query = Compra::with([
            'proveedor:id,nombre,ruc',
            'usuario:id,name',
        ])->withCount('detalles');


        if ($request->filled('buscar')) {
            $buscar = trim($request->input('buscar'));
            $query->where(function ($q) use ($buscar) {
                $q->where('numero_comprobante', 'like', "%{$buscar}%")
                  ->orWhereHas('proveedor', function ($qp) use ($buscar) {
                      $qp->where('nombre', 'like', "%{$buscar}%");
                  });
            });
        }

        if ($request->filled('estado')) {
            $query->where('estado', $request->input('estado'));
        }

        if ($request->filled('fecha_desde')) {
            $query->where('fecha', '>=', $request->input('fecha_desde') . ' 00:00:00');
        }

        if ($request->filled('fecha_hasta')) {
            $query->where('fecha', '<=', $request->input('fecha_hasta') . ' 23:59:59');
        }


        $orden = $request->input('orden', 'fecha_desc');
        switch ($orden) {
            case 'fecha_asc':
                $query->orderBy('fecha', 'asc')->orderBy('id', 'asc');
                break;
            case 'total_desc':
                $query->orderBy('total', 'desc');
                break;
            case 'total_asc':
                $query->orderBy('total', 'asc');
                break;
            case 'fecha_desc':
            default:
                $query->orderBy('fecha', 'desc')->orderBy('id', 'desc');
                break;
        }

        $compras = $query->paginate(perPage(15))->withQueryString();

        return view('compras.index', compact('compras'));
    }

    public function create(Request $request)
    {
        $proveedores = Proveedor::select(['id', 'nombre', 'ruc', 'telefono'])->activos()->orderBy('nombre')->get();
        $productos = Producto::select(['id', 'nombre', 'codigo_barra', 'principio_activo', 'laboratorio_id', 'precio_compra'])
            ->with([
                'presentacionesActivas:id,producto_id,nombre,unidades_por_presentacion,precio_compra',
                'laboratorio:id,nombre'
            ])
            ->activos()
            ->orderBy('nombre')
            ->get();

        $preloadedProveedorId = $request->input('proveedor_id', '');
        $preloadedOrdenCompraId = $request->input('orden_compra_id', '');
        $preloadedNumeroOrden = '';
        $preloadedCondicionPago = $request->input('condicion_pago', 'contado');
        $preloadedDiasCredito = $request->input('dias_credito', 0);
        $preloadedItems = [];

        if ($request->filled('orden_compra_id')) {
            $ordenCompra = \App\Models\OrdenCompra::with(['detalles.producto', 'proveedor'])->find($request->input('orden_compra_id'));
            if ($ordenCompra) {
                $preloadedProveedorId = $ordenCompra->proveedor_id;
                $preloadedNumeroOrden = $ordenCompra->numero_orden;
                $preloadedCondicionPago = $ordenCompra->condicion_pago;
                $preloadedDiasCredito = $ordenCompra->dias_credito;

                foreach ($ordenCompra->detalles as $det) {
                    $cantPendiente = max(0, (int)$det->cantidad_solicitada - (int)$det->cantidad_recibida);
                    if ($cantPendiente > 0) {
                        $preloadedItems[] = [
                            'producto_id'             => $det->producto_id,
                            'presentacion_id'         => '',
                            'detalle_orden_compra_id' => $det->id,
                            'cantidad'                => $cantPendiente,
                            'precio_unitario'         => (float) $det->precio_unitario_estimado,
                            'numero_lote'             => '',
                            'fecha_vencimiento'       => '',
                            'pedido'                  => (int) $det->cantidad_solicitada,
                            'recibido'                => (int) $det->cantidad_recibida,
                            'pendiente'               => $cantPendiente,
                        ];
                    }
                }
            }
        } elseif ($request->filled('items')) {
            $rawItems = $request->input('items');
            if (is_string($rawItems)) {
                $decoded = json_decode($rawItems, true);
                if (is_array($decoded)) {
                    $preloadedItems = $decoded;
                }
            } elseif (is_array($rawItems)) {
                $preloadedItems = $rawItems;
            }
        } elseif ($request->filled('producto_id')) {
            $preloadedItems = [
                [
                    'producto_id'     => $request->input('producto_id'),
                    'presentacion_id' => $request->input('presentacion_id', ''),
                    'cantidad'        => $request->input('cantidad', 10),
                    'precio_unitario' => $request->input('precio_unitario', ''),
                    'numero_lote'     => '',
                    'fecha_vencimiento' => '',
                ]
            ];
        }

        // Obtener los precios históricos más recientes agrupados por producto y proveedor
        $historialRaw = \App\Models\HistorialPrecio::with('proveedor:id,nombre')
            ->orderBy('fecha', 'desc')
            ->orderBy('id', 'desc')
            ->get();

        $historialMap = [];
        foreach ($historialRaw as $h) {
            $pId = $h->producto_id;
            $prId = $h->proveedor_id;
            if (!isset($historialMap[$pId])) {
                $historialMap[$pId] = [
                    'proveedores'      => [],
                    'ultimo_precio'    => (float)$h->precio_unitario_base,
                    'ultimo_proveedor' => $h->proveedor->nombre ?? 'N/A',
                    'ultima_fecha'     => $h->fecha->format('d/m/Y'),
                    'mejor_precio'     => (float)$h->precio_unitario_base,
                    'mejor_proveedor'  => $h->proveedor->nombre ?? 'N/A',
                ];
            }

            if (!isset($historialMap[$pId]['proveedores'][$prId])) {
                $historialMap[$pId]['proveedores'][$prId] = [
                    'precio_compra'        => (float)$h->precio_compra,
                    'precio_unitario_base' => (float)$h->precio_unitario_base,
                    'tipo_presentacion'    => $h->tipo_presentacion,
                    'fecha'                => $h->fecha->format('d/m/Y'),
                    'tipo'                 => $h->tipo,
                ];
            }

            if ((float)$h->precio_unitario_base < $historialMap[$pId]['mejor_precio']) {
                $historialMap[$pId]['mejor_precio'] = (float)$h->precio_unitario_base;
                $historialMap[$pId]['mejor_proveedor'] = $h->proveedor->nombre ?? 'N/A';
            }
        }

        return view('compras.create', compact(
            'proveedores', 
            'productos', 
            'preloadedProveedorId', 
            'preloadedOrdenCompraId',
            'preloadedNumeroOrden',
            'preloadedCondicionPago',
            'preloadedDiasCredito',
            'preloadedItems', 
            'historialMap'
        ));
    }

    public function store(StoreCompraRequest $request)
    {
        try {
            $compra = $this->compraService->registrarCompra($request->validated());

            AuditLog::log('compras', 'registrar', "Compra #{$compra->id} registrada por C\${$compra->total}", [
                'compra_id'           => $compra->id,
                'proveedor_id'        => $compra->proveedor_id,
                'numero_comprobante'  => $compra->numero_comprobante,
                'total'               => $compra->total,
                'items'               => $compra->detalles()->count(),
            ]);

            if ($request->boolean('crear_otro')) {
                return redirect()->route('compras.create')
                    ->with('success', "Compra #{$compra->id} registrada exitosamente. Listo para registrar la siguiente factura.");
            }

            return redirect()->route('compras.show', $compra)
                ->with('success', "Compra #{$compra->id} registrada exitosamente.");
        } catch (QueryException $e) {
            Log::error("Error de base de datos al registrar compra: " . $e->getMessage());
            return back()->withInput()->with('error', 'Error en la base de datos al procesar la compra. La transacción fue revertida.');
        } catch (Exception $e) {
            Log::warning("Excepción al registrar compra: " . $e->getMessage());
            return back()->withInput()->with('error', 'Error al procesar la compra: ' . $e->getMessage());
        }
    }

    public function show(Compra $compra)
    {
        $compra->load([
            'proveedor',
            'usuario',
            'anuladoPor',
            'detalles.producto.laboratorio',
            'detalles.presentacion',
            'lotes',
            'compraOriginal',
            'reemplazadaPor',
            'ordenCompra',
            'devoluciones.detalles.producto',
            'devoluciones.usuario',
            'devoluciones.proveedor',
        ]);

        return view('compras.show', compact('compra'));
    }

    public function edit(Compra $compra)
    {
        if (!$compra->puedeModificarse()) {
            return redirect()->route('compras.show', $compra)
                ->with('error', 'Esta compra no puede ser modificada en su estado actual.');
        }

        $compra->load(['detalles.producto.presentacionesActivas', 'proveedor', 'lotes']);
        $proveedores = Proveedor::select(['id', 'nombre'])->activos()->orderBy('nombre')->get();
        $productos = Producto::select(['id', 'nombre', 'codigo_barra', 'principio_activo', 'laboratorio_id', 'precio_compra'])
            ->with([
                'presentacionesActivas:id,producto_id,nombre,unidades_por_presentacion,precio_compra',
                'laboratorio:id,nombre'
            ])
            ->activos()
            ->orderBy('nombre')
            ->get();

        return view('compras.edit', compact('compra', 'proveedores', 'productos'));
    }

    public function update(UpdateCompraRequest $request, Compra $compra)
    {
        try {
            $data = $request->validated();
            $motivo = $data['motivo_modificacion'] ?? 'Corrección de datos';
            
            $nuevaCompra = $this->compraService->modificarCompra($compra->id, $data, $motivo);

            AuditLog::log('compras', 'modificar', "Compra #{$compra->id} modificada → nueva versión #{$nuevaCompra->id}", [
                'compra_original_id' => $compra->id,
                'compra_nueva_id'    => $nuevaCompra->id,
                'motivo'             => $motivo,
            ]);

            return redirect()->route('compras.show', $nuevaCompra)
                ->with('success', "Compra actualizada exitosamente. Se generó la nueva versión #{$nuevaCompra->id}.");
        } catch (QueryException $e) {
            Log::error("Error de base de datos al modificar compra #{$compra->id}: " . $e->getMessage());
            return back()->withInput()->with('error', 'Error en la base de datos al modificar la compra. Se revirtieron los cambios.');
        } catch (Exception $e) {
            Log::warning("Excepción al modificar compra #{$compra->id}: " . $e->getMessage());
            return back()->withInput()->with('error', 'Error al modificar la compra: ' . $e->getMessage());
        }
    }

    public function anular(AnularCompraRequest $request, Compra $compra)
    {
        try {
            $motivo = $request->input('motivo');
            $this->compraService->anularCompra($compra->id, $motivo);

            AuditLog::log('compras', 'anular', "Compra #{$compra->id} anulada — stock revertido", [
                'compra_id' => $compra->id,
                'motivo'    => $motivo,
            ]);

            return redirect()->route('compras.show', $compra)
                ->with('success', "Compra #{$compra->id} anulada correctamente y stock revertido.");
        } catch (QueryException $e) {
            Log::error("Error de base de datos al anular compra #{$compra->id}: " . $e->getMessage());
            return back()->with('error', 'Error en la base de datos al anular la compra.');
        } catch (Exception $e) {
            Log::warning("Excepción al anular compra #{$compra->id}: " . $e->getMessage());
            return back()->with('error', 'No se pudo anular la compra: ' . $e->getMessage());
        }
    }

    public function generarPDF(Compra $compra)
    {
        $compra->load(['proveedor', 'usuario', 'detalles.producto.laboratorio', 'detalles.presentacion', 'lotes']);
        $pdf = Pdf::loadView('compras.pdf', compact('compra'));

        return $pdf->download("compra-{$compra->id}.pdf");
    }

    public function imprimir(Compra $compra)
    {
        return $this->imprimirTicket($compra);
    }

    public function imprimirTicket(Compra $compra)
    {
        $compra->load(['proveedor', 'usuario', 'detalles.producto.laboratorio', 'detalles.presentacion', 'lotes']);
        return view('compras.ticket', compact('compra'));
    }

    public function verificarNumeroLote(Request $request)
    {
        $productoId = (int) $request->input('producto_id');
        $numeroLote = trim($request->input('numero_lote', ''));

        if (empty($numeroLote) || !$productoId) {
            return response()->json(['disponible' => false]);
        }

        $existe = Lote::where('producto_id', $productoId)
            ->where('numero_lote', $numeroLote)
            ->exists();

        return response()->json(['disponible' => !$existe]);
    }
}
