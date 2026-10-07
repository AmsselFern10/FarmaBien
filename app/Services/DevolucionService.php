<?php

namespace App\Services;

use App\Models\Venta;
use App\Models\DetalleVenta;
use App\Models\DevolucionVenta;
use App\Models\DetalleDevolucionVenta;
use App\Models\Lote;
use App\Models\MovimientoCaja;
use App\Models\SesionCaja;
use App\Models\MovimientoInventario;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Exception;

class DevolucionService
{
    protected InventarioService $inventarioService;

    public function __construct(InventarioService $inventarioService)
    {
        $this->inventarioService = $inventarioService;
    }

    /**
     * Procesar devolución de venta (parcial o total) con transacción ACID y Kardex
     *
     * @param array $data
     * @return DevolucionVenta
     * @throws Exception
     */
    public function procesarDevolucion(array $data): DevolucionVenta
    {
        return DB::transaction(function () use ($data) {
            $venta = Venta::with(['detalles.lote', 'detalles.producto', 'devoluciones.detalles'])
                ->lockForUpdate()
                ->findOrFail($data['venta_id']);

            if ($venta->estado === 'anulada') {
                throw new Exception("No se pueden procesar devoluciones sobre una venta anulada.");
            }

            $userId = Auth::id() ?? 1;
            
            // Buscar sesión de caja activa del usuario si el reembolso es en efectivo
            $sesionCaja = SesionCaja::where('user_id', $userId)
                ->where('estado', 'abierta')
                ->first();

            // Calcular acumulado de devoluciones previas por detalle_venta_id
            $devolucionesPrevias = [];
            foreach ($venta->devoluciones as $dev) {
                if ($dev->estado === 'completada') {
                    foreach ($dev->detalles as $dDev) {
                        $devolucionesPrevias[$dDev->detalle_venta_id] = ($devolucionesPrevias[$dDev->detalle_venta_id] ?? 0) + $dDev->cantidad;
                    }
                }
            }

            $itemsDevolver = $data['items'] ?? [];
            if (empty($itemsDevolver)) {
                throw new Exception("Debe seleccionar al menos un producto para devolver.");
            }

            $detallesParaCrear = [];
            $montoTotalDevolucion = 0;
            $totalPresentacionesDevueltas = 0;
            $totalPresentacionesOriginales = $venta->detalles->sum('cantidad');

            foreach ($itemsDevolver as $item) {
                $detalleId = (int) $item['detalle_venta_id'];
                $cantidadDevolver = (int) ($item['cantidad'] ?? 0);

                if ($cantidadDevolver <= 0) {
                    continue;
                }

                $detalleVenta = $venta->detalles->firstWhere('id', $detalleId);
                if (!$detalleVenta) {
                    throw new Exception("El detalle de venta #{$detalleId} no pertenece a esta venta.");
                }

                $yaDevuelto = $devolucionesPrevias[$detalleId] ?? 0;
                $disponibleParaDevolver = $detalleVenta->cantidad - $yaDevuelto;

                if ($cantidadDevolver > $disponibleParaDevolver) {
                    throw new Exception("No puede devolver {$cantidadDevolver} unidades de '{$detalleVenta->producto->nombre}'. Disponible para devolver: {$disponibleParaDevolver}.");
                }

                $precioUnitarioOriginal = (float) $detalleVenta->precio_unitario;
                $precioUnitarioEfectivo = $detalleVenta->cantidad > 0
                    ? round((float) $detalleVenta->subtotal / (float) $detalleVenta->cantidad, 2)
                    : $precioUnitarioOriginal;
                $subtotalItem = round($cantidadDevolver * $precioUnitarioEfectivo, 2);
                $unidadesBasePorPres = (int) ($detalleVenta->unidades_por_presentacion ?: 1);
                $unidadesBaseDevolver = $cantidadDevolver * $unidadesBasePorPres;

                $reingresaAStock = !empty($item['reingresa_a_stock']);
                $estadoProducto = $item['estado_producto'] ?? 'buen_estado';

                $detallesParaCrear[] = [
                    'detalle_venta_id'          => $detalleId,
                    'producto_id'               => $detalleVenta->producto_id,
                    'lote_id'                   => $detalleVenta->lote_id,
                    'cantidad'                  => $cantidadDevolver,
                    'unidades_por_presentacion' => $unidadesBasePorPres,
                    'cantidad_unidades_base'    => $unidadesBaseDevolver,
                    'precio_unitario'           => $precioUnitarioEfectivo,
                    'subtotal'                  => $subtotalItem,
                    'reingresa_a_stock'         => $reingresaAStock,
                    'estado_producto'           => $estadoProducto,
                ];

                $montoTotalDevolucion += $subtotalItem;
                $totalPresentacionesDevueltas += $cantidadDevolver;
            }

            if (empty($detallesParaCrear)) {
                throw new Exception("No se especificaron cantidades válidas para devolver.");
            }

            // Generar código único de devolución
            $consecutivo = DevolucionVenta::whereDate('created_at', now()->toDateString())->count() + 1;
            $numeroDevolucion = 'DEV-' . now()->format('Ymd') . '-' . str_pad($consecutivo, 4, '0', STR_PAD_LEFT);

            // Determinar si es parcial o total
            $totalPrevioDevuelto = array_sum($devolucionesPrevias);
            $esTotal = ($totalPrevioDevuelto + $totalPresentacionesDevueltas) >= $totalPresentacionesOriginales;
            $tipoDevolucion = $esTotal ? 'total' : 'parcial';

            // Crear registro principal de devolución
            $devolucion = DevolucionVenta::create([
                'venta_id'           => $venta->id,
                'user_id'            => $userId,
                'sesion_caja_id'     => $sesionCaja?->id,
                'numero_devolucion'  => $numeroDevolucion,
                'tipo'               => $tipoDevolucion,
                'motivo'             => $data['motivo'] ?? 'cliente_desiste',
                'observaciones'      => $data['observaciones'] ?? null,
                'monto_total'        => $montoTotalDevolucion,
                'metodo_reembolso'   => $data['metodo_reembolso'] ?? 'efectivo',
                'banco'              => $data['banco'] ?? null,
                'numero_transaccion' => $data['numero_transaccion'] ?? null,
                'estado'             => 'completada',
                'fecha'              => now(),
            ]);

            // Procesar cada detalle y actualizar inventario
            foreach ($detallesParaCrear as $itemData) {
                $itemData['devolucion_venta_id'] = $devolucion->id;
                $detDev = DetalleDevolucionVenta::create($itemData);

                $detalleVentaOriginal = $venta->detalles->firstWhere('id', $itemData['detalle_venta_id']);

                $lote = $detalleVentaOriginal?->lote ?? Lote::find($itemData['lote_id']);
                if ($lote) {
                    if ($itemData['reingresa_a_stock']) {
                        // Reingresar stock al lote
                        $this->inventarioService->revertirStockLote(
                            $lote,
                            $itemData['cantidad_unidades_base'],
                            'anulacion_venta',
                            'devolucion_venta',
                            $devolucion->id,
                            "Devolución de venta {$numeroDevolucion}"
                        );

                        // Revertir dispensación de receta si el detalle estaba vinculado
                        if ($detalleVentaOriginal && $detalleVentaOriginal->receta_detalle_id && class_exists(\App\Services\RecetaService::class)) {
                            try {
                                app(\App\Services\RecetaService::class)->revertirDispensacion(
                                    $detalleVentaOriginal->receta_detalle_id,
                                    $itemData['cantidad_unidades_base']
                                );
                            } catch (\Throwable $th) {
                                Log::warning("No se pudo revertir dispensación de receta en devolución: " . $th->getMessage());
                            }
                        }
                    } else {
                        // Si no reingresa a stock (dañado o vencido), registrar merma/descarte en Kardex
                        $costoUnitario = (float) $lote->precio_compra;
                        MovimientoInventario::create([
                            'producto_id'      => $itemData['producto_id'],
                            'lote_id'          => $lote->id,
                            'user_id'          => $userId,
                            'tipo'             => 'salida',
                            'subtipo'          => 'merma_danio',
                            'cantidad'         => 0, // No altera stock físico porque nunca reingresó
                            'stock_anterior'   => (int) $lote->stock_actual,
                            'stock_posterior'  => (int) $lote->stock_actual,
                            'costo_unitario'   => $costoUnitario,
                            'costo_total'      => round($itemData['cantidad_unidades_base'] * $costoUnitario, 2),
                            'origen'           => 'devolucion_danado',
                            'origen_id'        => $devolucion->id,
                            'motivo'           => "Producto devuelto no apto para venta ({$itemData['estado_producto']}) en {$numeroDevolucion}",
                            'fecha_movimiento' => now(),
                        ]);
                    }
                }

                // Si el producto es controlado, asentar en el Libro Oficial MINSA
                $productoModel = $detalleVentaOriginal?->producto ?? \App\Models\Producto::find($itemData['producto_id']);
                if ($productoModel && $productoModel->esControlado()) {
                    $tipoMovCtrl = $itemData['reingresa_a_stock']
                        ? \App\Models\RegistroVentaControlado::TIPO_DEVOLUCION_STOCK
                        : \App\Models\RegistroVentaControlado::TIPO_DEVOLUCION_MERMA;

                    $motivoTexto = $itemData['reingresa_a_stock']
                        ? "Reingreso por Devolución {$numeroDevolucion}: " . ($data['motivo'] ?? 'Devolución') . (!empty($data['observaciones']) ? " ({$data['observaciones']})" : '')
                        : "Baja por Devolución (Merma) {$numeroDevolucion} [{$itemData['estado_producto']}]: " . ($data['motivo'] ?? 'Devolución dañada/vencida') . (!empty($data['observaciones']) ? " ({$data['observaciones']})" : '');

                    \App\Models\RegistroVentaControlado::create([
                        'tipo_movimiento'     => $tipoMovCtrl,
                        'venta_id'            => $venta->id,
                        'devolucion_id'       => $devolucion->id,
                        'producto_id'         => $itemData['producto_id'],
                        'lote_id'             => $itemData['lote_id'],
                        'nivel_controlado'    => 1,
                        'paciente_nombre'     => $venta->cliente?->nombre ?? 'Público General',
                        'paciente_cedula'     => $venta->cliente?->documento,
                        'motivo_omision'      => $motivoTexto,
                        'cantidad'            => $itemData['cantidad_unidades_base'],
                        'unidad'              => 'unidad',
                        'user_id'             => $userId,
                    ]);
                }
            }

            // Si el reembolso es en efectivo y hay caja abierta, registrar egreso de caja y recalcular
            if ($devolucion->metodo_reembolso === 'efectivo' && $sesionCaja) {
                MovimientoCaja::create([
                    'sesion_caja_id'         => $sesionCaja->id,
                    'user_id'                => $userId,
                    'tipo'                   => 'egreso',
                    'monto'                  => $montoTotalDevolucion,
                    'concepto'               => "Reembolso por Devolución {$numeroDevolucion} (Venta #{$venta->numero_comprobante})",
                    'comprobante_referencia' => $numeroDevolucion,
                ]);

                if (class_exists(\App\Services\CajaService::class)) {
                    app(\App\Services\CajaService::class)->recalcularTotales($sesionCaja);
                }
            }

            \Illuminate\Support\Facades\Cache::forget('inventario_valorizacion');
            \App\Services\NotificacionService::clearCache();

            Log::info("Devolución {$numeroDevolucion} procesada exitosamente.", [
                'devolucion_id' => $devolucion->id,
                'venta_id'      => $venta->id,
                'monto_total'   => $montoTotalDevolucion,
                'tipo'          => $tipoDevolucion,
            ]);

            return $devolucion->load(['detalles.producto', 'detalles.lote', 'venta.cliente', 'usuario', 'sesionCaja']);
        });
    }
}
