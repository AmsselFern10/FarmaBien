<?php

namespace App\Http\Controllers;

use App\Models\OrdenCompra;
use App\Models\Proveedor;
use App\Models\Producto;
use App\Services\CompraService;
use App\Services\OrdenCompraService;
use App\Http\Requests\StoreOrdenCompraRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Exception;

class OrdenCompraController extends Controller
{
    protected CompraService $compraService;
    protected OrdenCompraService $ordenCompraService;

    public function __construct(CompraService $compraService, OrdenCompraService $ordenCompraService)
    {
        $this->compraService = $compraService;
        $this->ordenCompraService = $ordenCompraService;

        $this->middleware('permission:ver compras')->only(['index', 'show', 'imprimir']);
        $this->middleware('permission:registrar compras')->only(['create', 'store', 'recibirMercancia']);
        $this->middleware('permission:anular compras')->only(['cancelar']);
    }

    public function index(Request $request)
    {
        $baseQuery = OrdenCompra::query();

        if ($request->filled('buscar')) {
            $buscar = trim($request->input('buscar'));
            $baseQuery->where(function ($q) use ($buscar) {
                $q->where('numero_orden', 'like', "%{$buscar}%")
                  ->orWhereHas('proveedor', function ($qp) use ($buscar) {
                      $qp->where('nombre', 'like', "%{$buscar}%");
                  });
            });
        }

        if ($request->filled('estado')) {
            $baseQuery->where('estado', $request->input('estado'));
        }

        if ($request->filled('proveedor_id')) {
            $baseQuery->where('proveedor_id', $request->input('proveedor_id'));
        }

        $totales = (clone $baseQuery)
            ->selectRaw("COUNT(*) as total_ordenes, COALESCE(SUM(CASE WHEN estado != 'cancelada' THEN total ELSE 0 END), 0) as total_monto")
            ->first();
        $totalOrdenes = (int) ($totales->total_ordenes ?? 0);
        $totalMonto = (float) ($totales->total_monto ?? 0);

        $ordenes = (clone $baseQuery)
            ->with([
                'proveedor:id,nombre,ruc,contacto',
                'usuario:id,name',
                'detalles',
                'compra:id,numero_comprobante,estado',
            ])
            ->withCount('detalles')
            ->orderBy('created_at', 'desc')
            ->paginate(perPage(15))
            ->withQueryString();

        $proveedores = Proveedor::getCachedActivos();

        return view('compras.ordenes.index', compact('ordenes', 'totalOrdenes', 'totalMonto', 'proveedores'));
    }

    public function create(Request $request)
    {
        $proveedores = Proveedor::getCachedActivos();
        $productos = Producto::activos()
            ->with(['laboratorio:id,nombre'])
            ->select(['id', 'nombre', 'principio_activo', 'laboratorio_id', 'codigo_barra', 'precio_compra', 'precio_venta'])
            ->orderBy('nombre')
            ->get();

        // Si viene desde sugerencias de reorden (múltiples ítems o individual)
        $preloadedItems = [];
        $proveedorId = $request->input('proveedor_id');

        if ($request->filled('items')) {
            $rawItems = $request->input('items');
            $decoded = is_string($rawItems) ? json_decode($rawItems, true) : $rawItems;
            if (is_array($decoded)) {
                $itemMap = [];
                foreach ($decoded as $item) {
                    $pId = (int) ($item['producto_id'] ?? $item['id'] ?? 0);
                    if ($pId > 0) {
                        $itemMap[$pId] = $item;
                    }
                }

                if (!empty($itemMap)) {
                    $foundProds = Producto::with(['laboratorio:id,nombre'])
                        ->whereIn('id', array_keys($itemMap))
                        ->get();

                    foreach ($foundProds as $prod) {
                        $itemData = $itemMap[$prod->id];
                        $preloadedItems[] = [
                            'producto_id'     => $prod->id,
                            'nombre'          => $prod->nombre,
                            'laboratorio'     => $prod->laboratorio->nombre ?? 'Sin Lab',
                            'cantidad'        => max(1, (int) ($itemData['cantidad'] ?? 10)),
                            'precio_estimado' => (float) ($itemData['precio_unitario'] ?? ($prod->precio_compra > 0 ? $prod->precio_compra : $prod->precio_venta * 0.7)),
                        ];
                    }
                }
            }
        } elseif ($request->filled('producto_id')) {
            $prod = Producto::with(['laboratorio:id,nombre'])->find($request->input('producto_id'));
            if ($prod) {
                $preloadedItems[] = [
                    'producto_id'       => $prod->id,
                    'nombre'            => $prod->nombre,
                    'laboratorio'       => $prod->laboratorio->nombre ?? 'Sin Lab',
                    'cantidad'          => (int) $request->input('cantidad', 10),
                    'precio_estimado'   => (float) ($prod->precio_compra > 0 ? $prod->precio_compra : $prod->precio_venta * 0.7),
                ];
            }
        }

        return view('compras.ordenes.create', compact('proveedores', 'productos', 'preloadedItems', 'proveedorId'));
    }

    public function store(StoreOrdenCompraRequest $request)
    {
        try {
            $orden = $this->ordenCompraService->crearOrden($request->validated(), Auth::id() ?? 1);

            return redirect()->route('ordenes-compras.show', $orden)
                ->with('success', "Orden de compra {$orden->numero_orden} generada exitosamente.");
        } catch (Exception $e) {
            Log::error("Error al crear Orden de Compra: " . $e->getMessage(), [
                'user_id' => Auth::id(),
                'payload' => $request->except(['_token']),
            ]);

            return back()->withInput()->with('error', 'Error al generar la orden de compra: ' . $e->getMessage());
        }
    }

    public function show(OrdenCompra $ordenes_compra)
    {
        $orden = $ordenes_compra;
        $orden->load([
            'proveedor',
            'usuario',
            'cerradaPor',
            'detalles.producto.laboratorio',
            'compras.usuario',
            'compras.detalles.producto',
        ]);

        // Generar texto para enviar por WhatsApp
        $lineas = [];
        $lineas[] = "📦 *ORDEN DE COMPRA: {$orden->numero_orden}*";
        $lineas[] = "🏢 *Proveedor:* " . ($orden->proveedor->nombre ?? $orden->proveedor->nombre_empresa ?? 'Proveedor');
        $lineas[] = "📅 *Fecha:* {$orden->fecha_emision->format('d/m/Y')}";
        $lineas[] = "💳 *Condición:* " . ucfirst($orden->condicion_pago) . ($orden->condicion_pago === 'credito' ? " ({$orden->dias_credito} días)" : "");
        $lineas[] = "";
        $lineas[] = "*DETALLE DE PRODUCTOS:*";
        foreach ($orden->detalles as $d) {
            $lineas[] = "• {$d->cantidad_solicitada}x {$d->producto->nombre} @ " . formato_moneda($d->precio_unitario_estimado) . " = " . formato_moneda($d->subtotal);
        }
        $lineas[] = "";
        $lineas[] = "💰 *TOTAL ESTIMADO:* " . formato_moneda($orden->total);
        if ($orden->observaciones) {
            $lineas[] = "📝 *Nota:* {$orden->observaciones}";
        }
        $lineas[] = "";
        $lineas[] = "Solicitado por Farmacia FarmaBien.";

        $whatsappMensaje = urlencode(implode("\n", $lineas));
        $telefonoProv = preg_replace('/[^0-9]/', '', $orden->proveedor->telefono ?? '');
        $whatsappUrl = $telefonoProv ? "https://wa.me/{$telefonoProv}?text={$whatsappMensaje}" : "https://wa.me/?text={$whatsappMensaje}";

        return view('compras.ordenes.show', compact('orden', 'whatsappUrl'));
    }

    public function imprimir(OrdenCompra $ordenes_compra)
    {
        $orden = $ordenes_compra;
        $orden->load(['proveedor', 'usuario', 'detalles.producto.laboratorio']);
        return view('compras.ordenes.imprimir', compact('orden'));
    }

    public function recibirMercancia(Request $request, OrdenCompra $ordenes_compra)
    {
        $orden = $ordenes_compra;
        if ($orden->estado === 'recibida_total') {
            return back()->with('error', 'Esta orden de compra ya fue recibida en su totalidad.');
        }
        if ($orden->estado === 'cancelada') {
            return back()->with('error', 'No se puede recepcionar una orden cancelada.');
        }

        // Redireccionar al formulario de compra pre-llenado con los datos de esta orden
        return redirect()->route('compras.create', [
            'orden_compra_id' => $orden->id,
            'proveedor_id'    => $orden->proveedor_id,
            'condicion_pago'  => $orden->condicion_pago,
            'dias_credito'    => $orden->dias_credito,
        ]);
    }

    public function cancelar(Request $request, OrdenCompra $ordenes_compra)
    {
        try {
            $motivo = $request->input('motivo_cancelacion');
            $orden = $this->ordenCompraService->cancelarOrden($ordenes_compra, $motivo);

            return back()->with('success', "Orden de compra {$orden->numero_orden} cancelada correctamente.");
        } catch (Exception $e) {
            Log::error("Error al cancelar orden de compra {$ordenes_compra->id}: " . $e->getMessage(), [
                'user_id' => Auth::id(),
            ]);

            return back()->with('error', $e->getMessage());
        }
    }
}
