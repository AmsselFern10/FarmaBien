<?php

namespace App\Http\Controllers;

use App\Models\Compra;
use App\Models\Proveedor;
use App\Models\SesionCaja;
use App\Services\CuentaPorPagarService;
use App\Http\Requests\StoreAbonoRequest;
use App\Http\Requests\StoreCuentaPorPagarDirectaRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Exception;

class CuentaPorPagarController extends Controller
{
    protected CuentaPorPagarService $cuentaPorPagarService;

    public function __construct(CuentaPorPagarService $cuentaPorPagarService)
    {
        $this->cuentaPorPagarService = $cuentaPorPagarService;
        $this->middleware('permission:ver compras')->only(['index', 'show']);
        $this->middleware('permission:registrar compras')->only(['storeAbono', 'storeDirecta']);
    }

    public function index(Request $request)
    {
        $query = Compra::with([
                'proveedor:id,nombre,ruc,contacto,telefono',
                'usuario:id,name',
            ])
            ->withCount('pagos')
            ->withSum('pagos', 'monto')
            ->where('condicion_pago', 'credito')
            ->where('estado', 'recibida')
            ->orderBy('fecha_vencimiento_pago', 'asc');

        if ($request->filled('buscar')) {
            $buscar = trim($request->input('buscar'));
            $query->where(function ($q) use ($buscar) {
                $q->where('numero_comprobante', 'like', "%{$buscar}%")
                  ->orWhereHas('proveedor', function ($qp) use ($buscar) {
                      $qp->where('nombre', 'like', "%{$buscar}%")
                         ->orWhere('contacto', 'like', "%{$buscar}%")
                         ->orWhere('ruc', 'like', "%{$buscar}%");
                  });
            });
        }

        if ($request->filled('proveedor_id')) {
            $query->where('proveedor_id', $request->input('proveedor_id'));
        }

        $filtroEstado = $request->input('estado_pago', 'pendientes');
        $hoy = now()->toDateString();

        if ($filtroEstado === 'pendientes') {
            $query->where('saldo_pendiente', '>', 0);
        } elseif ($filtroEstado === 'vencidas') {
            $query->where('saldo_pendiente', '>', 0)->whereDate('fecha_vencimiento_pago', '<', $hoy);
        } elseif ($filtroEstado === 'pagadas') {
            $query->where(function ($q) {
                $q->where('estado_pago', 'pagado')->orWhere('saldo_pendiente', '<=', 0);
            });
        }

        if ($request->filled('fecha_desde')) {
            $query->whereDate('fecha', '>=', $request->input('fecha_desde'));
        }

        if ($request->filled('fecha_hasta')) {
            $query->whereDate('fecha', '<=', $request->input('fecha_hasta'));
        }

        $metricas = $this->cuentaPorPagarService->getMetricas();
        $compras = $query->paginate(perPage(15))->withQueryString();
        $proveedores = Proveedor::getCachedActivos();

        $sesionCaja = SesionCaja::with('caja')
            ->where('user_id', Auth::id())
            ->where('estado', 'abierta')
            ->first();

        return view('compras.cuentas-por-pagar.index', compact('compras', 'metricas', 'proveedores', 'filtroEstado', 'sesionCaja'));
    }

    public function show(Compra $compra)
    {
        if ($compra->condicion_pago !== 'credito') {
            return redirect()->route('compras.show', $compra);
        }

        $compra->load([
            'proveedor',
            'usuario',
            'detalles.producto.laboratorio',
            'detalles.lote',
            'pagos.usuario',
            'pagos.sesionCaja.caja',
        ]);

        $sesionCaja = SesionCaja::with('caja')
            ->where('user_id', Auth::id())
            ->where('estado', 'abierta')
            ->first();

        return view('compras.cuentas-por-pagar.show', compact('compra', 'sesionCaja'));
    }

    public function storeAbono(StoreAbonoRequest $request, Compra $compra)
    {
        try {
            $data = $request->validated();
            $data['compra_id'] = $compra->id;

            $pago = $this->cuentaPorPagarService->registrarAbono($data, Auth::id() ?? 1);
            $nombreProv = $compra->proveedor->nombre ?? 'Proveedor';

            return back()->with('success', "Abono {$pago->numero_pago} de " . formato_moneda($pago->monto) . " registrado correctamente a {$nombreProv}.");
        } catch (Exception $e) {
            Log::error("Error al registrar abono en CxP: " . $e->getMessage(), [
                'user_id'   => Auth::id(),
                'compra_id' => $compra->id,
            ]);

            return back()->withInput()->with('error', $e->getMessage());
        }
    }

    public function storeDirecta(StoreCuentaPorPagarDirectaRequest $request)
    {
        try {
            $compra = $this->cuentaPorPagarService->registrarDirecta($request->validated(), Auth::id() ?? 1);

            return back()->with('success', "Cuenta por pagar {$compra->numero_comprobante} por " . formato_moneda($compra->total) . " registrada exitosamente.");
        } catch (Exception $e) {
            Log::error("Error al registrar cuenta por pagar directa: " . $e->getMessage(), [
                'user_id' => Auth::id(),
                'payload' => $request->except(['_token']),
            ]);

            return back()->withInput()->with('error', 'Error al procesar la factura de gasto: ' . $e->getMessage());
        }
    }
}
