<?php

namespace App\Http\Controllers;

use App\Models\OrdenCompra;
use App\Models\DetalleOrdenCompra;
use App\Models\Proveedor;
use App\Models\Producto;
use App\Models\Compra;
use App\Services\CompraService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Exception;

class OrdenCompraController extends Controller
{
    protected CompraService $compraService;

    public function __construct(CompraService $compraService)
    {
        $this->compraService = $compraService;
        $this->middleware('permission:ver compras');
    }

    public function index(Request $request)
    {
        $query = OrdenCompra::with([
                'proveedor:id,nombre,ruc',
                'usuario:id,name',
                'compra:id,numero_comprobante,estado',
            ])
            ->withCount('detalles')
            ->orderBy('created_at', 'desc');

        if ($request->filled('buscar')) {
            $buscar = $request->input('buscar');
            $query->where(function ($q) use ($buscar) {
                $q->where('numero_orden', 'like', "%{$buscar}%")
                  ->orWhereHas('proveedor', function ($qp) use ($buscar) {
                      $qp->where('nombre', 'like', "%{$buscar}%");
                  });
            });
        }

        if ($request->filled('estado')) {
            $query->where('estado', $request->input('estado'));
        }

        if ($request->filled('proveedor_id')) {
            $query->where('proveedor_id', $request->input('proveedor_id'));
        }

        $totalOrdenes = (clone $query)->count();
        $totalMonto = (clone $query)->where('estado', '!=', 'cancelada')->sum('total');

        $ordenes = $query->paginate(perPage(15))->withQueryString();
        $proveedores = Proveedor::activos()->orderBy('nombre')->get();

        return view('compras.ordenes.index', compact('ordenes', 'totalOrdenes', 'totalMonto', 'proveedores'));
    }

    public function create(Request $request)
    {
        $proveedores = Proveedor::activos()->orderBy('nombre')->get(['id', 'nombre', 'ruc']);
        $productos = Producto::activos()
            ->select(['id', 'nombre', 'codigo_barra', 'precio_compra', 'precio_venta'])
            ->orderBy('nombre')
            ->get();

        // Si viene desde sugerencias de reorden
        $preloadedItems = [];
        $proveedorId = $request->input('proveedor_id');

        if ($request->filled('producto_id')) {
            $prod = Producto::find($request->input('producto_id'));
            if ($prod) {
                $preloadedItems[] = [
                    'producto_id'       => $prod->id,
                    'nombre'            => $prod->nombre,
                    'cantidad'          => (int) $request->input('cantidad', 10),
                    'precio_estimado'   => (float) ($prod->precio_compra > 0 ? $prod->precio_compra : $prod->precio_venta * 0.7),
                ];
            }
        }

        return view('compras.ordenes.create', compact('proveedores', 'productos', 'preloadedItems', 'proveedorId'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'proveedor_id'            => 'required|exists:proveedores,id',
            'fecha_emision'           => 'required|date',
            'fecha_esperada_entrega'  => 'nullable|date|after_or_equal:fecha_emision',
            'condicion_pago'          => 'required|in:contado,credito',
            'dias_credito'            => 'nullable|integer|min:0',
            'observaciones'           => 'nullable|string|max:500',
            'items'                   => 'required|array|min:1',
            'items.*.producto_id'     => 'required|exists:productos,id',
            'items.*.cantidad'        => 'required|integer|min:1',
            'items.*.precio_unitario' => 'required|numeric|min:0',
        ]);

        try {
            $orden = DB::transaction(function () use ($validated) {
                $consecutivo = OrdenCompra::whereYear('created_at', now()->year)->count() + 1;
                $numeroOrden = 'OC-' . now()->year . '-' . str_pad($consecutivo, 4, '0', STR_PAD_LEFT);

                $total = 0;
                $detalles = [];

                foreach ($validated['items'] as $item) {
                    $cant = (int) $item['cantidad'];
                    $precio = (float) $item['precio_unitario'];
                    $subtotal = round($cant * $precio, 2);
                    $total += $subtotal;

                    $detalles[] = [
                        'producto_id'              => $item['producto_id'],
                        'cantidad_solicitada'      => $cant,
                        'cantidad_recibida'        => 0,
                        'precio_unitario_estimado' => $precio,
                        'subtotal'                 => $subtotal,
                    ];
                }

                $orden = OrdenCompra::create([
                    'proveedor_id'           => $validated['proveedor_id'],
                    'user_id'                => Auth::id() ?? 1,
                    'numero_orden'           => $numeroOrden,
                    'fecha_emision'          => $validated['fecha_emision'],
                    'fecha_esperada_entrega' => $validated['fecha_esperada_entrega'] ?? null,
                    'estado'                 => 'enviada',
                    'condicion_pago'         => $validated['condicion_pago'],
                    'dias_credito'           => $validated['condicion_pago'] === 'credito' ? ($validated['dias_credito'] ?? 30) : 0,
                    'subtotal'               => $total,
                    'impuesto'               => 0,
                    'total'                  => $total,
                    'observaciones'          => $validated['observaciones'] ?? null,
                ]);

                foreach ($detalles as $det) {
                    $det['orden_compra_id'] = $orden->id;
                    DetalleOrdenCompra::create($det);
                }

                return $orden;
            });

            return redirect()->route('ordenes-compras.show', $orden)
                ->with('success', "Orden de compra {$orden->numero_orden} generada exitosamente.");
        } catch (Exception $e) {
            Log::error("Error al crear Orden de Compra: " . $e->getMessage());
            return back()->withInput()->with('error', $e->getMessage());
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
        $lineas[] = "🏢 *Proveedor:* {$orden->proveedor->nombre_empresa}";
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
        $orden = $ordenes_compra;
        if ($orden->estado === 'recibida_total' || $orden->estado === 'recibida_parcial' || $orden->compras()->exists()) {
            return back()->with('error', 'No se puede cancelar una orden que ya tiene recepciones registradas.');
        }

        $orden->update(['estado' => 'cancelada']);

        return back()->with('success', "Orden de compra {$orden->numero_orden} cancelada.");
    }
}
