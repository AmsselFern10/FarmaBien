<?php

namespace App\Http\Controllers;

use App\Models\Compra;
use App\Models\Proveedor;
use App\Models\Producto;
use App\Models\Lote;
use App\Services\CompraService;
use App\Http\Requests\StoreCompraRequest;
use App\Http\Requests\UpdateCompraRequest;
use App\Http\Requests\AnularCompraRequest;
use Illuminate\Http\Request;
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
        $query = Compra::with(['proveedor', 'usuario'])->withCount('detalles');

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
            $query->whereDate('fecha', '>=', $request->input('fecha_desde'));
        }

        if ($request->filled('fecha_hasta')) {
            $query->whereDate('fecha', '<=', $request->input('fecha_hasta'));
        }

        $compras = $query->orderBy('fecha', 'desc')->paginate(15)->withQueryString();
        $proveedores = Proveedor::activos()->orderBy('nombre')->get();

        return view('compras.index', compact('compras', 'proveedores'));
    }

    public function create()
    {
        $proveedores = Proveedor::activos()->orderBy('nombre')->get();
        $productos = Producto::with(['presentacionesActivas', 'laboratorio'])->activos()->orderBy('nombre')->get();

        return view('compras.create', compact('proveedores', 'productos'));
    }

    public function store(StoreCompraRequest $request)
    {
        try {
            $compra = $this->compraService->registrarCompra($request->validated());

            return redirect()->route('compras.show', $compra)
                ->with('success', "Compra #{$compra->id} registrada exitosamente.");
        } catch (Exception $e) {
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
            'reemplazadaPor'
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
        $proveedores = Proveedor::activos()->orderBy('nombre')->get();
        $productos = Producto::with(['presentacionesActivas', 'laboratorio'])->activos()->orderBy('nombre')->get();

        return view('compras.edit', compact('compra', 'proveedores', 'productos'));
    }

    public function update(UpdateCompraRequest $request, Compra $compra)
    {
        try {
            $data = $request->validated();
            $motivo = $data['motivo_modificacion'] ?? 'Corrección de datos';
            
            $nuevaCompra = $this->compraService->modificarCompra($compra->id, $data, $motivo);

            return redirect()->route('compras.show', $nuevaCompra)
                ->with('success', "Compra actualizada exitosamente. Se generó la nueva versión #{$nuevaCompra->id}.");
        } catch (Exception $e) {
            return back()->withInput()->with('error', 'Error al modificar la compra: ' . $e->getMessage());
        }
    }

    public function anular(AnularCompraRequest $request, Compra $compra)
    {
        try {
            $this->compraService->anularCompra($compra->id, $request->input('motivo'));

            return redirect()->route('compras.show', $compra)
                ->with('success', "Compra #{$compra->id} anulada correctamente y stock revertido.");
        } catch (Exception $e) {
            return back()->with('error', 'No se pudo anular la compra: ' . $e->getMessage());
        }
    }

    public function generarPDF(Compra $compra)
    {
        $compra->load(['proveedor', 'usuario', 'detalles.producto.laboratorio', 'lotes']);
        $pdf = Pdf::loadView('compras.pdf', compact('compra'));

        return $pdf->download("compra-{$compra->id}.pdf");
    }

    public function imprimirTicket(Compra $compra)
    {
        $compra->load(['proveedor', 'usuario', 'detalles.producto', 'lotes']);
        return view('compras.ticket', compact('compra'));
    }

    public function verificarNumeroLote(Request $request)
    {
        $productoId = $request->input('producto_id');
        $numeroLote = trim($request->input('numero_lote'));

        $existe = Lote::where('producto_id', $productoId)
            ->where('numero_lote', $numeroLote)
            ->exists();

        return response()->json(['disponible' => !$existe]);
    }
}
