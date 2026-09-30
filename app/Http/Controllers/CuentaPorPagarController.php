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
        $proveedores = Proveedor::activos()->orderBy('nombre_empresa')->get();

        return view('compras.cuentas-por-pagar.index', compact('compras', 'metricas', 'proveedores', 'filtroEstado'));
    }

    public function show(Compra $compra)
    {
        if ($compra->condicion_pago !== 'credito') {
            return redirect()->route('compras.show', $compra);
        }

        $compra->load(['proveedor', 'usuario', 'detalles.producto', 'detalles.lote', 'pagos.usuario']);

        return view('compras.cuentas-por-pagar.show', compact('compra'));
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

            return back()->with('success', "Abono {$pago->numero_pago} de " . formato_moneda($pago->monto) . " registrado correctamente a {$compra->proveedor->nombre_empresa}.");
        } catch (Exception $e) {
            Log::error("Error al registrar abono en CxP: " . $e->getMessage());
            return back()->withInput()->with('error', $e->getMessage());
        }
    }
}
