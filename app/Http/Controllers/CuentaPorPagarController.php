<?php

namespace App\Http\Controllers;

use App\Models\Compra;
use App\Models\Proveedor;
use App\Models\PagoCuentaPorPagar;
use App\Services\CuentaPorPagarService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Exception;

class CuentaPorPagarController extends Controller
{
    protected CuentaPorPagarService $cuentaPorPagarService;

    public function __construct(CuentaPorPagarService $cuentaPorPagarService)
    {
        $this->cuentaPorPagarService = $cuentaPorPagarService;
        $this->middleware('permission:ver compras');
    }

    public function index(Request $request)
    {
        $query = Compra::with(['proveedor', 'usuario', 'pagos'])
            ->where('condicion_pago', 'credito')
            ->where('estado', 'recibida')
            ->orderBy('fecha_vencimiento_pago', 'asc');

        if ($request->filled('buscar')) {
            $buscar = $request->input('buscar');
            $query->where(function ($q) use ($buscar) {
                $q->where('numero_comprobante', 'like', "%{$buscar}%")
                  ->orWhereHas('proveedor', function ($qp) use ($buscar) {
                      $qp->where('nombre_empresa', 'like', "%{$buscar}%")
                         ->orWhere('nombre_contacto', 'like', "%{$buscar}%");
                  });
            });
        }

        if ($request->filled('proveedor_id')) {
            $query->where('proveedor_id', $request->input('proveedor_id'));
        }

        $filtroEstado = $request->input('estado_pago', 'pendientes');
        if ($filtroEstado === 'pendientes') {
            $query->whereIn('estado_pago', ['pendiente', 'parcial', 'vencido'])->where('saldo_pendiente', '>', 0);
        } elseif ($filtroEstado === 'vencidas') {
            $query->where('saldo_pendiente', '>', 0)->whereDate('fecha_vencimiento_pago', '<', now()->toDateString());
        } elseif ($filtroEstado === 'pagadas') {
            $query->where('estado_pago', 'pagado');
        }

        if ($request->filled('fecha_desde')) {
            $query->whereDate('fecha', '>=', $request->input('fecha_desde'));
        }

        if ($request->filled('fecha_hasta')) {
            $query->whereDate('fecha', '<=', $request->input('fecha_hasta'));
        }

        $metricas = $this->cuentaPorPagarService->getMetricas();
        $compras = $query->paginate(15)->withQueryString();
        $proveedores = Proveedor::activos()->orderBy('nombre')->get();
        $sesionCaja = \App\Models\SesionCaja::with('caja')
            ->where('user_id', auth()->id())
            ->where('estado', 'abierta')
            ->first();

        return view('compras.cuentas-por-pagar.index', compact('compras', 'metricas', 'proveedores', 'filtroEstado', 'sesionCaja'));
    }

    public function show(Compra $compra)
    {
        if ($compra->condicion_pago !== 'credito') {
            return redirect()->route('compras.show', $compra);
        }

        $compra->load(['proveedor', 'usuario', 'detalles.producto', 'detalles.lote', 'pagos.usuario']);
        $sesionCaja = \App\Models\SesionCaja::with('caja')
            ->where('user_id', auth()->id())
            ->where('estado', 'abierta')
            ->first();

        return view('compras.cuentas-por-pagar.show', compact('compra', 'sesionCaja'));
    }

    public function storeAbono(Request $request, Compra $compra)
    {
        $validated = $request->validate([
            'monto'              => 'required|numeric|min:0.01|max:' . ($compra->saldo_pendiente + 0.01),
            'metodo_pago'        => 'required|in:efectivo,transferencia,cheque,otro',
            'banco'              => 'nullable|string|max:100',
            'numero_referencia'  => 'nullable|string|max:100',
            'fecha_pago'         => 'nullable|date',
            'observaciones'      => 'nullable|string|max:500',
            'registrar_en_caja'  => 'nullable|boolean',
        ]);

        $validated['compra_id'] = $compra->id;

        try {
            $pago = $this->cuentaPorPagarService->registrarAbono($validated);

            return back()->with('success', "Abono {$pago->numero_pago} de " . formato_moneda($pago->monto) . " registrado correctamente a {$compra->proveedor->nombre}.");
        } catch (Exception $e) {
            Log::error("Error al registrar abono en CxP: " . $e->getMessage());
            return back()->withInput()->with('error', $e->getMessage());
        }
    }

    public function storeDirecta(Request $request)
    {
        $validated = $request->validate([
            'proveedor_id'           => 'nullable',
            'proveedor_nombre'       => 'nullable|string|max:150',
            'numero_comprobante'     => 'required|string|max:50',
            'fecha'                  => 'required|date',
            'total'                  => 'required|numeric|min:0.01',
            'dias_credito'           => 'nullable|integer|min:1|max:365',
            'fecha_vencimiento_pago' => 'nullable|date',
            'concepto'               => 'nullable|string|max:100',
        ]);

        try {
            $proveedorId = $request->input('proveedor_id');
            if (empty($proveedorId)) {
                $nombreProv = trim($request->input('proveedor_nombre', ''));
                if (empty($nombreProv)) {
                    return back()->withInput()->with('error', 'Debes seleccionar o especificar un proveedor/acreedor para la factura.');
                }
                $proveedor = Proveedor::firstOrCreate(
                    ['nombre' => $nombreProv],
                    ['activo' => true]
                );
                $proveedorId = $proveedor->id;
            }

            $diasCredito = (int) ($request->input('dias_credito') ?: 30);
            $fecha = \Carbon\Carbon::parse($request->input('fecha'));
            $fechaVencimiento = $request->filled('fecha_vencimiento_pago')
                ? \Carbon\Carbon::parse($request->input('fecha_vencimiento_pago'))
                : (clone $fecha)->addDays($diasCredito);

            $numComp = $validated['numero_comprobante'];
            if (!empty($validated['concepto'])) {
                $numComp = substr($validated['numero_comprobante'] . ' (' . $validated['concepto'] . ')', 0, 50);
            }

            $total = round((float) $validated['total'], 2);

            $compra = Compra::create([
                'proveedor_id'           => $proveedorId,
                'user_id'                => auth()->id() ?? 1,
                'numero_comprobante'     => $numComp,
                'subtotal'               => $total,
                'impuesto'               => 0,
                'total'                  => $total,
                'condicion_pago'         => 'credito',
                'dias_credito'           => $diasCredito,
                'fecha_vencimiento_pago' => $fechaVencimiento,
                'saldo_pendiente'        => $total,
                'estado_pago'            => 'pendiente',
                'estado'                 => 'recibida',
                'fecha'                  => $fecha,
            ]);

            return back()->with('success', "Cuenta por pagar {$compra->numero_comprobante} por " . formato_moneda($compra->total) . " registrada exitosamente.");
        } catch (Exception $e) {
            Log::error("Error al registrar cuenta por pagar directa: " . $e->getMessage());
            return back()->withInput()->with('error', 'Error al procesar la factura: ' . $e->getMessage());
        }
    }
}
