<?php

namespace App\Services;

use App\Models\Venta;
use App\Models\DetalleVenta;
use App\Models\Lote;
use App\Models\Producto;
use App\Models\PresentacionProducto;
use App\Models\MovimientoInventario;
use App\Models\SesionCaja;
use App\Models\Receta;
use App\Models\RecetaDetalle;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Exception;

class VentaService
{
    protected RecetaService $recetaService;

    public function __construct(RecetaService $recetaService)
    {
        $this->recetaService = $recetaService;
    }

    /**
     * Procesar una venta en mostrador con soporte para presentaciones fraccionadas,
     * recetas médicas, bloqueo pesimista ACID y Kardex auditado.
     * 
     * @param array $data
     * @return Venta
     * @throws Exception
     */
    public function procesarVenta(array $data): Venta
    {
        return DB::transaction(function () use ($data) {
            $this->validarDatosVenta($data);

            $userId = Auth::id() ?? 1;

            // 0. Protección estricta contra duplicidad por parpadeo de red (Idempotency Key)
            $idempotencyKey = !empty($data['idempotency_key']) ? trim($data['idempotency_key']) : null;
            if ($idempotencyKey) {
                $ventaExistente = Venta::where('idempotency_key', $idempotencyKey)->first();
                if ($ventaExistente) {
                    return $ventaExistente->load([
                        'detalles.producto.laboratorio',
                        'detalles.lote',
                        'detalles.presentacion',
                        'detalles.recetaDetalle.receta',
                        'cliente',
                        'recetas'
                    ]);
                }
            }

            // 1. Obtener y bloquear la sesión de caja activa del usuario (con caja activa)
            $sesionActiva = SesionCaja::where('user_id', $userId)
                ->where('estado', 'abierta')
                ->whereHas('caja', function ($q) {
                    $q->where('activo', true);
                })
                ->lockForUpdate()
                ->latest('fecha_apertura')
                ->first();

            if (!$sesionActiva) {
                throw new Exception("Transacción rechazada: No tienes un turno de caja abierto o la caja asignada se encuentra inactiva. Debes abrir caja antes de realizar ventas.");
            }

            // 2. Gestionar recetas médicas si la modalidad es 'creada'
            $recetasAsociadas = [];
            $recetaModalidad = $data['receta_modalidad'] ?? 'sin_receta';
            $recetaOmisionMotivo = $data['receta_omision_motivo'] ?? null;

            if (!empty($data['recetas']) && is_array($data['recetas'])) {
                $recetasAsociadas = array_values(array_filter(array_unique($data['recetas'])));
            }

            if (!empty($data['receta_id']) && !in_array($data['receta_id'], $recetasAsociadas)) {
                $recetasAsociadas[] = (int) $data['receta_id'];
            }

            // Quick-Create de Receta Médica desde POS
            if ($recetaModalidad === 'creada' && !empty($data['receta_crear']) && is_array($data['receta_crear'])) {
                $rcData = $data['receta_crear'];
                $detallesReceta = [];

                foreach ($data['productos'] as $prodItem) {
                    $pModel = Producto::find($prodItem['producto_id']);
                    if ($pModel && ($pModel->requiere_receta || in_array($pModel->tipo_control, ['receta_medica', 'receta_retenida', 'psicotropico', 'estupefaciente']))) {
                        $factor = 1;
                        if (!empty($prodItem['presentacion_id'])) {
                            $pres = PresentacionProducto::find($prodItem['presentacion_id']);
                            if ($pres) {
                                $factor = max(1, (int) $pres->unidades_por_presentacion);
                            }
                        } elseif (!empty($prodItem['factor'])) {
                            $factor = max(1, (int) $prodItem['factor']);
                        }

                        $cantUnidades = max(1, (int) ($prodItem['cantidad'] ?? 1) * $factor);

                        if (isset($detallesReceta[$pModel->id])) {
                            $detallesReceta[$pModel->id]['cantidad_recetada'] += $cantUnidades;
                        } else {
                            $detallesReceta[$pModel->id] = [
                                'producto_id' => $pModel->id,
                                'cantidad_recetada' => $cantUnidades,
                                'posologia' => 'Según indicación médica en mostrador'
                            ];
                        }
                    }
                }

                if (!empty($detallesReceta)) {
                    $detallesReceta = array_values($detallesReceta);
                    $numeroReceta = !empty($rcData['numero_receta']) 
                        ? trim($rcData['numero_receta']) 
                        : 'RX-' . strtoupper(uniqid());

                    $nuevaReceta = $this->recetaService->registrarReceta([
                        'cliente_id'          => $data['cliente_id'] ?? null,
                        'paciente_nombre'     => !empty($rcData['paciente_nombre']) ? trim($rcData['paciente_nombre']) : ($data['cliente_nombre'] ?? 'Paciente Mostrador'),
                        'paciente_documento'  => $rcData['paciente_documento'] ?? null,
                        'medico_nombre'       => !empty($rcData['medico_nombre']) ? trim($rcData['medico_nombre']) : 'Dr. Médico Tratante',
                        'medico_colegiatura'  => !empty($rcData['medico_colegiatura']) ? trim($rcData['medico_colegiatura']) : 'CMP-GENERAL',
                        'medico_especialidad' => $rcData['medico_especialidad'] ?? 'Medicina General',
                        'numero_receta'       => $numeroReceta,
                        'fecha_emision'       => now()->toDateString(),
                        'fecha_vencimiento'   => now()->addDays(30)->toDateString(),
                        'tipo_receta'         => 'simple',
                        'observaciones'       => 'Emitida desde Terminal POS al momento de la venta',
                        'detalles'            => $detallesReceta
                    ]);

                    $recetasAsociadas[] = $nuevaReceta->id;

                    // Asignar receta_detalle_id a los ítems correspondientes para que se dispense
                    foreach ($nuevaReceta->detalles as $nrd) {
                        foreach ($data['productos'] as &$prodItemRef) {
                            if ($prodItemRef['producto_id'] == $nrd->producto_id && empty($prodItemRef['receta_detalle_id'])) {
                                $prodItemRef['receta_detalle_id'] = $nrd->id;
                            }
                        }
                    }
                    unset($prodItemRef);
                }
            }

            // 3. Crear cabecera inicial de la venta
            $tipoDescuento = $data['tipo_descuento'] ?? 'monto';
            $porcentajeDescuento = max(0, (float) ($data['porcentaje_descuento'] ?? 0));
            $metodoPago = $data['metodo_pago'] ?? 'efectivo';

            $venta = Venta::create([
                'cliente_id'            => $data['cliente_id'] ?? null,
                'user_id'               => $userId,
                'sesion_caja_id'        => $sesionActiva?->id,
                'tipo_comprobante'      => $data['tipo_comprobante'] ?? 'ticket',
                'serie'                 => $data['serie'] ?? null,
                'numero_comprobante'    => $data['numero_comprobante'] ?? null,
                'idempotency_key'       => $idempotencyKey,
                'subtotal'              => 0,
                'descuento'             => 0,
                'tipo_descuento'        => $tipoDescuento,
                'porcentaje_descuento'  => $porcentajeDescuento,
                'impuesto'              => 0,
                'total'                 => 0,
                'metodo_pago'           => $metodoPago,
                'monto_recibido'        => null,
                'cambio'                => 0,
                'receta_modalidad'      => $recetaModalidad,
                'receta_omision_motivo' => $recetaOmisionMotivo,
                'referencia_pago'       => $data['referencia_pago'] ?? null,
                'observaciones'         => $data['observaciones'] ?? null,
                'estado'                => 'completada',
                'fecha'                 => now(),
            ]);

            $totalAcumulado = 0;

            // 4. Procesar cada producto del carrito
            foreach ($data['productos'] as $item) {
                $subtotalItem = $this->procesarDetalleVenta($venta, $item, $recetaModalidad);
                $totalAcumulado += $subtotalItem;

                if (!empty($item['receta_detalle_id'])) {
                    $recetaDetalle = RecetaDetalle::find($item['receta_detalle_id']);
                    if ($recetaDetalle && !in_array($recetaDetalle->receta_id, $recetasAsociadas)) {
                        $recetasAsociadas[] = $recetaDetalle->receta_id;
                    }
                }
            }

            // 5. Calcular importes finales y descuentos
            $subtotalFinal = round($totalAcumulado, 2);

            if ($tipoDescuento === 'porcentaje' && $porcentajeDescuento > 0) {
                $descuentoCalculado = round($subtotalFinal * ($porcentajeDescuento / 100), 2);
            } else {
                $descuentoCalculado = max(0, (float) ($data['descuento'] ?? 0));
                if ($subtotalFinal > 0 && $descuentoCalculado > 0) {
                    $porcentajeDescuento = round(($descuentoCalculado / $subtotalFinal) * 100, 2);
                }
            }

            $descuentoFinal = min($subtotalFinal, $descuentoCalculado);
            $totalFinal = max(0, round($subtotalFinal - $descuentoFinal, 2));

            // 6. Validación Matemática Infalible de Efectivo y Vuelto
            $montoRecibido = null;
            $cambio = 0;

            if ($metodoPago === 'efectivo') {
                $montoRecibido = isset($data['monto_recibido']) ? round((float) $data['monto_recibido'], 2) : null;

                if ($montoRecibido === null) {
                    throw new Exception("En pagos en efectivo es obligatorio ingresar el 'Monto Recibido por el Cliente'.");
                }

                if ($montoRecibido < $totalFinal) {
                    $faltante = round($totalFinal - $montoRecibido, 2);
                    throw new Exception("Monto insuficiente: El dinero recibido ($" . number_format($montoRecibido, 2) . ") es menor al total a pagar ($" . number_format($totalFinal, 2) . "). Faltan $" . number_format($faltante, 2) . ".");
                }

                $cambio = max(0, round($montoRecibido - $totalFinal, 2));
            } else {
                $montoRecibido = $totalFinal;
                $cambio = 0;
            }

            // 7. Actualizar cabecera con importes finales exactos
            $venta->update([
                'subtotal'              => $subtotalFinal,
                'descuento'             => $descuentoFinal,
                'tipo_descuento'        => $tipoDescuento,
                'porcentaje_descuento'  => $porcentajeDescuento,
                'total'                 => $totalFinal,
                'monto_recibido'        => $montoRecibido,
                'cambio'                => $cambio,
            ]);

            // 8. Vincular recetas a la tabla pivot
            if (!empty($recetasAsociadas)) {
                $venta->recetas()->syncWithoutDetaching($recetasAsociadas);
            }

            // 9. Recalcular totales de la caja activa
            if ($sesionActiva) {
                app(CajaService::class)->recalcularTotales($sesionActiva);
            }

            return $venta->load([
                'detalles.producto.laboratorio',
                'detalles.lote',
                'detalles.presentacion',
                'detalles.recetaDetalle.receta',
                'cliente',
                'recetas'
            ]);
        });
    }

    /**
     * Procesar un ítem individual de la venta con bloqueo pesimista, control sanitario y Kardex.
     * 
     * @param Venta $venta
     * @param array $item
     * @return float Subtotal de la línea
     * @throws Exception
     */
    protected function procesarDetalleVenta(Venta $venta, array $item, string $recetaModalidad = 'sin_receta'): float
    {
        $productoId = (int) $item['producto_id'];
        $loteId = (int) $item['lote_id'];

        // 1. Obtener y bloquear el lote
        $lote = Lote::with('producto')
            ->where('id', $loteId)
            ->lockForUpdate()
            ->firstOrFail();

        $producto = $lote->producto;

        if (!$producto || $lote->producto_id !== $productoId) {
            throw new Exception("El lote '{$lote->numero_lote}' no corresponde al medicamento seleccionado.");
        }

        if (!$producto->activo) {
            throw new Exception("El medicamento '{$producto->nombre}' se encuentra inactivo.");
        }

        if (!$lote->activo) {
            throw new Exception("El lote '{$lote->numero_lote}' de '{$producto->nombre}' se encuentra inhabilitado.");
        }

        if ($lote->estaVencido()) {
            throw new Exception("No se puede vender: el lote '{$lote->numero_lote}' de '{$producto->nombre}' venció el {$lote->fecha_vencimiento->format('d/m/Y')}.");
        }

        // 2. Determinar presentación comercial y factor de conversión
        $presentacionId = !empty($item['presentacion_id']) ? (int) $item['presentacion_id'] : null;
        $cantidadPresentaciones = (int) $item['cantidad'];

        if ($cantidadPresentaciones <= 0) {
            throw new Exception("La cantidad solicitada para '{$producto->nombre}' debe ser mayor a 0.");
        }

        $presentacion = null;
        if ($presentacionId) {
            $presentacion = PresentacionProducto::where('producto_id', $producto->id)
                ->where('id', $presentacionId)
                ->firstOrFail();

            if (!$presentacion->activo) {
                throw new Exception("La presentación '{$presentacion->nombre}' de '{$producto->nombre}' está inactiva.");
            }

            $unidadesPorPresentacion = max(1, (int) $presentacion->unidades_por_presentacion);
            $precioUnitario = isset($item['precio_unitario']) && is_numeric($item['precio_unitario'])
                ? (float) $item['precio_unitario']
                : (float) ($presentacion->precio_venta ?? $producto->precio_venta);
        } else {
            $unidadesPorPresentacion = 1;
            $precioUnitario = isset($item['precio_unitario']) && is_numeric($item['precio_unitario'])
                ? (float) $item['precio_unitario']
                : (float) $producto->precio_venta;
        }

        $cantidadUnidadesBase = $cantidadPresentaciones * $unidadesPorPresentacion;

        // 3. Validar disponibilidad de stock en el lote
        if ($lote->stock_actual < $cantidadUnidadesBase) {
            throw new Exception(
                "Stock insuficiente para '{$producto->nombre}'. " .
                "Disponible en lote {$lote->numero_lote}: {$lote->stock_actual} unidades base. Requerido: {$cantidadUnidadesBase}."
            );
        }

        // 4. Validar Receta Médica y Sustancias Controladas
        $recetaDetalleId = !empty($item['receta_detalle_id']) ? (int) $item['receta_detalle_id'] : null;
        $esControlado = $producto->requiere_receta || in_array($producto->tipo_control, ['receta_medica', 'receta_retenida', 'psicotropico', 'estupefaciente']);

        if ($esControlado && $producto->tipo_control === 'receta_retenida' && !$recetaDetalleId && $recetaModalidad !== 'omitida') {
            throw new Exception("El medicamento '{$producto->nombre}' es de RECETA RETENIDA y requiere asociar obligatoriamente la prescripción médica o declarar omisión justificada.");
        }

        if ($recetaDetalleId) {
            $recetaDetalle = RecetaDetalle::where('id', $recetaDetalleId)->first();
            if ($recetaDetalle && $recetaDetalle->producto_id === $producto->id) {
                $this->recetaService->dispensarMedicamento($recetaDetalleId, $cantidadUnidadesBase);
            }
        }

        // 5. Descontar stock del lote de manera atómica
        $stockAnterior = (int) $lote->stock_actual;
        $stockPosterior = $stockAnterior - $cantidadUnidadesBase;
        
        if ($stockPosterior < 0) {
            throw new Exception("Inconsistencia crítica de inventario detectada: el stock resultante sería negativo.");
        }

        $lote->stock_actual = $stockPosterior;
        $lote->save();

        // 6. Calcular subtotal de la línea (incluyendo promoción automática o descuento manual)
        $subtotalBruto = round($cantidadPresentaciones * $precioUnitario, 2);
        $descuentoLinea = 0;

        // Verificar si existe una promoción activa aplicable
        $promo = $producto->promocion_vigente;
        if ($promo && $promo->esVigente() && $cantidadPresentaciones >= $promo->min_unidades) {
            $descuentoLinea = $promo->calcularDescuento($precioUnitario, $cantidadPresentaciones);
            $promo->increment('stock_consumido', $cantidadPresentaciones);
        } elseif (!empty($item['tipo_descuento']) && $item['tipo_descuento'] === 'porcentaje' && !empty($item['porcentaje_descuento'])) {
            $descuentoLinea = round($subtotalBruto * ((float) $item['porcentaje_descuento'] / 100), 2);
        } elseif (!empty($item['descuento'])) {
            $descuentoLinea = max(0, (float) $item['descuento']);
        }

        $subtotal = max(0, round($subtotalBruto - $descuentoLinea, 2));

        // 7. Crear Detalle de Venta
        DetalleVenta::create([
            'venta_id'                  => $venta->id,
            'producto_id'               => $producto->id,
            'lote_id'                   => $lote->id,
            'presentacion_id'           => $presentacionId,
            'receta_detalle_id'         => $recetaDetalleId,
            'cantidad'                  => $cantidadPresentaciones,
            'unidades_por_presentacion' => $unidadesPorPresentacion,
            'cantidad_unidades_base'    => $cantidadUnidadesBase,
            'precio_unitario'           => $precioUnitario,
            'subtotal'                  => $subtotal,
        ]);

        // 8. Registrar en Kardex de Auditoría (SALIDA por VENTA)
        $costoUnitarioBase = (float) $lote->precio_compra;
        $costoTotalMovimiento = round($cantidadUnidadesBase * $costoUnitarioBase, 2);

        MovimientoInventario::create([
            'producto_id'      => $producto->id,
            'lote_id'          => $lote->id,
            'user_id'          => Auth::id() ?? 1,
            'tipo'             => 'salida',
            'subtipo'          => 'venta',
            'cantidad'         => -$cantidadUnidadesBase,
            'stock_anterior'   => $stockAnterior,
            'stock_posterior'  => $stockPosterior,
            'costo_unitario'   => $costoUnitarioBase,
            'costo_total'      => $costoTotalMovimiento,
            'origen'           => 'venta',
            'origen_id'        => $venta->id,
            'motivo'           => "Venta #{$venta->id} - {$cantidadPresentaciones} x " . ($presentacion ? $presentacion->nombre : 'Unidad Base') . " (Lote: {$lote->numero_lote})",
            'fecha_movimiento' => now(),
        ]);

        return $subtotal;
    }

    /**
     * Modificar una venta existente revirtiendo atómicamente el inventario original
     * y generando la nueva venta vinculada con trazabilidad completa.
     * 
     * @param int $ventaId
     * @param array $data
     * @param string $motivo
     * @return Venta
     * @throws Exception
     */
    public function modificarVenta(int $ventaId, array $data, string $motivo): Venta
    {
        return DB::transaction(function () use ($ventaId, $data, $motivo) {
            $ventaOriginal = Venta::with(['detalles', 'sesionCaja'])
                ->where('id', $ventaId)
                ->lockForUpdate()
                ->firstOrFail();

            if (!$ventaOriginal->puedeModificarse()) {
                throw new Exception("La venta #{$ventaId} no puede ser modificada en su estado actual.");
            }

            // 1. Revertir cada detalle de la venta original al inventario y recetas
            foreach ($ventaOriginal->detalles as $detalle) {
                $lote = Lote::where('id', $detalle->lote_id)->lockForUpdate()->firstOrFail();

                $stockAnterior = (int) $lote->stock_actual;
                $stockPosterior = $stockAnterior + (int) $detalle->cantidad_unidades_base;

                $lote->stock_actual = $stockPosterior;
                $lote->save();

                $costoUnitarioBase = (float) $lote->precio_compra;
                $costoTotal = round($detalle->cantidad_unidades_base * $costoUnitarioBase, 2);

                MovimientoInventario::create([
                    'producto_id'      => $detalle->producto_id,
                    'lote_id'          => $lote->id,
                    'user_id'          => Auth::id() ?? 1,
                    'tipo'             => 'entrada',
                    'subtipo'          => 'modificacion_venta',
                    'cantidad'         => $detalle->cantidad_unidades_base,
                    'stock_anterior'   => $stockAnterior,
                    'stock_posterior'  => $stockPosterior,
                    'costo_unitario'   => $costoUnitarioBase,
                    'costo_total'      => $costoTotal,
                    'origen'           => 'modificacion_venta',
                    'origen_id'        => $ventaOriginal->id,
                    'motivo'           => "Reversión por modificación de Venta #{$ventaOriginal->id}: {$motivo}",
                    'fecha_movimiento' => now(),
                ]);

                if ($detalle->receta_detalle_id) {
                    $this->recetaService->revertirDispensacion(
                        $detalle->receta_detalle_id,
                        $detalle->cantidad_unidades_base
                    );
                }
            }

            // 2. Procesar la nueva venta
            $nuevaVenta = $this->procesarVenta($data);

            // 3. Vincular ambas ventas para trazabilidad histórica
            $nuevaVenta->update([
                'venta_original_id' => $ventaOriginal->id,
            ]);

            $ventaOriginal->update([
                'estado'           => 'anulada',
                'reemplazada_por'  => $nuevaVenta->id,
                'anulado_por'      => Auth::id() ?? 1,
                'fecha_anulacion'  => now(),
                'motivo_anulacion' => "Modificada y reemplazada por Venta #{$nuevaVenta->id}. Motivo: {$motivo}",
            ]);

            // 4. Recalcular la caja de la venta original si era distinta
            if ($ventaOriginal->sesionCaja && $ventaOriginal->sesion_caja_id !== $nuevaVenta->sesion_caja_id) {
                app(CajaService::class)->recalcularTotales($ventaOriginal->sesionCaja);
            }

            return $nuevaVenta;
        });
    }

    /**
     * Anular una venta con reversión exacta de inventario, recetas y arqueo de caja.
     * 
     * @param int $ventaId
     * @param string $motivo
     * @return Venta
     * @throws Exception
     */
    public function anularVenta(int $ventaId, string $motivo): Venta
    {
        return DB::transaction(function () use ($ventaId, $motivo) {
            $venta = Venta::with(['detalles', 'sesionCaja'])
                ->where('id', $ventaId)
                ->lockForUpdate()
                ->firstOrFail();

            if (!$venta->puedeAnularse()) {
                throw new Exception("La venta #{$ventaId} no puede ser anulada porque ya está anulada o fue modificada.");
            }

            // Revertir cada detalle de la venta
            foreach ($venta->detalles as $detalle) {
                $lote = Lote::where('id', $detalle->lote_id)->lockForUpdate()->firstOrFail();

                $stockAnterior = (int) $lote->stock_actual;
                $stockPosterior = $stockAnterior + (int) $detalle->cantidad_unidades_base;

                $lote->stock_actual = $stockPosterior;
                $lote->save();

                // Revertir Kardex con balance estricto
                $costoUnitarioBase = (float) $lote->precio_compra;
                $costoTotal = round($detalle->cantidad_unidades_base * $costoUnitarioBase, 2);

                MovimientoInventario::create([
                    'producto_id'      => $detalle->producto_id,
                    'lote_id'          => $lote->id,
                    'user_id'          => Auth::id() ?? 1,
                    'tipo'             => 'entrada',
                    'subtipo'          => 'anulacion_venta',
                    'cantidad'         => $detalle->cantidad_unidades_base,
                    'stock_anterior'   => $stockAnterior,
                    'stock_posterior'  => $stockPosterior,
                    'costo_unitario'   => $costoUnitarioBase,
                    'costo_total'      => $costoTotal,
                    'origen'           => 'anulacion_venta',
                    'origen_id'        => $venta->id,
                    'motivo'           => "Anulación de venta #{$venta->id}: {$motivo}",
                    'fecha_movimiento' => now(),
                ]);

                // Revertir dispensación de receta médica si aplica
                if ($detalle->receta_detalle_id) {
                    $this->recetaService->revertirDispensacion(
                        $detalle->receta_detalle_id,
                        $detalle->cantidad_unidades_base
                    );
                }
            }

            $venta->update([
                'estado'           => 'anulada',
                'anulado_por'      => Auth::id() ?? 1,
                'fecha_anulacion'  => now(),
                'motivo_anulacion' => $motivo,
            ]);

            // Recalcular sesión de caja si la venta estaba vinculada a una
            if ($venta->sesionCaja) {
                app(CajaService::class)->recalcularTotales($venta->sesionCaja);
            }

            return $venta->fresh(['detalles', 'anuladoPor', 'sesionCaja']);
        });
    }

    /**
     * Búsqueda ultra-optimizada de productos para el mostrador de ventas (FIFO/FEFO de lotes disponibles)
     * Limitada por defecto a los Top 20 resultados para no congelar el navegador ni saturar la memoria.
     * 
     * @param string $termino
     * @param int|null $categoriaId
     * @param int $limit
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function buscarProductosParaVenta(string $termino, ?int $categoriaId = null, int $limit = 20)
    {
        $query = Producto::query()
            ->select([
                'id',
                'codigo_barra',
                'nombre',
                'principio_activo',
                'concentracion',
                'forma_farmaceutica',
                'precio_venta',
                'precio_compra',
                'ubicacion',
                'requiere_receta',
                'tipo_control',
                'imagen',
                'categoria_id',
                'laboratorio_id',
                'activo'
            ])
            ->with([
                'categoria:id,nombre',
                'laboratorio:id,nombre',
                'presentacionesActivas',
                'lotes' => function ($q) {
                    $q->disponibles()
                      ->orderBy('fecha_vencimiento', 'asc')
                      ->select(['id', 'producto_id', 'numero_lote', 'fecha_vencimiento', 'stock_actual', 'precio_compra', 'activo']);
                }
            ])
            ->activos()
            ->whereHas('lotes', function ($q) {
                $q->disponibles();
            });

        if ($categoriaId) {
            $query->where('categoria_id', $categoriaId);
        }

        $termino = trim($termino);
        if ($termino !== '') {
            $query->where(function ($q) use ($termino) {
                $q->where('nombre', 'like', "%{$termino}%")
                  ->orWhere('principio_activo', 'like', "%{$termino}%")
                  ->orWhere('codigo_barra', 'like', "%{$termino}%");
            });
        }

        $productos = $query->orderBy('nombre', 'asc')
            ->limit($limit)
            ->get();

        // Optimización de promociones: 1 sola consulta en memoria o desde caché en lugar de N*4 queries (N+1)
        $promociones = \Illuminate\Support\Facades\Cache::remember('promociones_vigentes_pos', 60, function () {
            return \App\Models\Promocion::vigentes()->orderBy('id', 'desc')->get();
        });

        $productos->each(function ($prod) use ($promociones) {
            $promo = $promociones->first(function ($p) use ($prod) {
                return $p->alcance === 'producto' && (int)$p->producto_id === (int)$prod->id;
            }) ?? $promociones->first(function ($p) use ($prod) {
                return $p->alcance === 'categoria' && (int)$p->categoria_id === (int)$prod->categoria_id;
            }) ?? $promociones->first(function ($p) use ($prod) {
                return $p->alcance === 'laboratorio' && (int)$p->laboratorio_id === (int)$prod->laboratorio_id;
            }) ?? $promociones->first(function ($p) {
                return $p->alcance === 'general';
            });

            if ($promo) {
                $prod->promocion_activa = [
                    'id'            => $promo->id,
                    'nombre'        => $promo->nombre,
                    'tipo'          => $promo->tipo,
                    'valor'         => (float) $promo->valor,
                    'min_unidades'  => (int) $promo->min_unidades,
                    'badge'         => $promo->badge_texto,
                    'precio_oferta' => $promo->calcularPrecioUnitario((float) $prod->precio_venta),
                ];
            } else {
                $prod->promocion_activa = null;
            }
        });

        return $productos;
    }

    /**
     * Validar la estructura básica de los datos de venta antes de procesar
     */
    protected function validarDatosVenta(array $data): void
    {
        if (empty($data['productos']) || !is_array($data['productos'])) {
            throw new Exception("El carrito de venta no contiene ningún producto.");
        }

        foreach ($data['productos'] as $index => $item) {
            if (empty($item['producto_id'])) {
                throw new Exception("El ítem en la posición " . ($index + 1) . " no tiene un producto válido asignado.");
            }
            if (empty($item['lote_id'])) {
                throw new Exception("El ítem en la posición " . ($index + 1) . " no tiene un lote de inventario seleccionado.");
            }
            $cantidad = (int) ($item['cantidad'] ?? 0);
            if ($cantidad <= 0) {
                throw new Exception("La cantidad debe ser mayor a 0 en todos los productos del carrito.");
            }
        }
    }
}
