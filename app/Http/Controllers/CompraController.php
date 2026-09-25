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

        $compras = $query->orderBy('fecha', 'desc')->orderBy('id', 'desc')->paginate(15)->withQueryString();

        return view('compras.index', compact('compras'));
    }

    public function create()
    {
        $proveedores = Proveedor::select(['id', 'nombre'])->activos()->orderBy('nombre')->get();
        $productos = Producto::select(['id', 'nombre', 'codigo_barra', 'principio_activo', 'laboratorio_id', 'precio_compra'])
            ->with([
                'presentacionesActivas:id,producto_id,nombre,unidades_por_presentacion,precio_compra',
                'laboratorio:id,nombre'
            ])
            ->activos()
            ->orderBy('nombre')
            ->get();

        return view('compras.create', compact('proveedores', 'productos'));
    }

    public function store(StoreCompraRequest $request)
    {
        try {
            $compra = $this->compraService->registrarCompra($request->validated());

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
            $this->compraService->anularCompra($compra->id, $request->input('motivo'));

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
