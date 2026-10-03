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

    public function index()
    {
        $devoluciones = DevolucionCompra::with(['proveedor', 'usuario'])
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        return view('compras.devoluciones.index', compact('devoluciones'));
    }

    public function create(Request $request)
    {
        $proveedores = Proveedor::where('activo', true)->orderBy('nombre')->get(['id', 'nombre']);

        // Lotes disponibles: activos, con stock > 0
        $lotes = Lote::with(['producto.laboratorio', 'proveedor'])
            ->where('activo', true)
            ->where('stock_actual', '>', 0)
            ->orderBy('fecha_vencimiento')
            ->get();

        $compraId = $request->input('compra_id');
        $compra = $compraId ? Compra::with(['proveedor', 'detalles.producto', 'lotes.producto'])->find($compraId) : null;

        return view('compras.devoluciones.create', compact('proveedores', 'lotes', 'compra'));
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
                $devolucion = DevolucionCompra::create([
                    'numero_devolucion' => DevolucionCompra::generarNumero(),
                    'proveedor_id'      => $request->proveedor_id,
                    'compra_id'         => $request->compra_id ?: null,
                    'usuario_id'        => auth()->id(),
                    'estado'            => 'pendiente',
                    'motivo'            => $request->motivo,
                    'total_devolucion'  => 0,
                ]);

                $total = 0;

                foreach ($request->items as $item) {
                    $lote = Lote::with('producto')->where('id', $item['lote_id'])->lockForUpdate()->firstOrFail();

                    $cantidad = (int) $item['cantidad'];
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
                        'usuario_id'       => auth()->id(),
                        'tipo'             => 'salida',
                        'subtipo'          => 'ajuste_manual',
                        'cantidad'         => $cantidad,
                        'stock_anterior'   => $stockAntes,
                        'stock_nuevo'      => $lote->stock_actual,
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
                            'user_id'                  => auth()->id(),
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
