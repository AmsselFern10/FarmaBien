<?php

namespace App\Services;

use App\Models\Compra;
use App\Models\DetalleCompra;
use App\Models\Lote;
use App\Models\Producto;
use App\Models\PresentacionProducto;
use App\Models\MovimientoInventario;
use App\Models\Proveedor;
use App\Models\HistorialPrecio;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Exception;

class CompraService
{
    /**
     * Registrar una compra completa con soporte para presentaciones fraccionadas,
     * bloqueo pesimista ACID y Kardex auditado.
     * 
     * @param array $data
     * @return Compra
     * @throws Exception
     */
    public function registrarCompra(array $data): Compra
    {
        return DB::transaction(function () use ($data) {
            $this->validarDatosCompra($data);

            $proveedorId = (int) $data['proveedor_id'];
            $proveedor = Proveedor::findOrFail($proveedorId);
            if (!$proveedor->activo) {
                throw new Exception("El proveedor '{$proveedor->nombre}' se encuentra inactivo.");
            }

            $condicionPago = $data['condicion_pago'] ?? 'contado';
            $diasCredito = $condicionPago === 'credito' ? (int) ($data['dias_credito'] ?? 30) : 0;
            $fechaCompra = !empty($data['fecha']) ? \Carbon\Carbon::parse($data['fecha']) : now();
            $fechaVencimientoPago = $condicionPago === 'credito'
                ? (!empty($data['fecha_vencimiento_pago']) ? \Carbon\Carbon::parse($data['fecha_vencimiento_pago']) : (clone $fechaCompra)->addDays($diasCredito))
                : null;

            // 1. Crear cabecera de la compra
            $compra = Compra::create([
                'proveedor_id'           => $proveedor->id,
                'user_id'                => Auth::id() ?? 1,
                'orden_compra_id'        => !empty($data['orden_compra_id']) ? (int) $data['orden_compra_id'] : null,
                'numero_comprobante'     => $data['numero_comprobante'] ?? null,
                'subtotal'               => 0,
                'impuesto'               => 0,
                'total'                  => 0,
                'condicion_pago'         => $condicionPago,
                'dias_credito'           => $diasCredito,
                'fecha_vencimiento_pago' => $fechaVencimientoPago,
                'saldo_pendiente'        => 0,
                'estado_pago'            => $condicionPago === 'credito' ? 'pendiente' : 'pagado',
                'estado'                 => 'recibida',
                'fecha'                  => $fechaCompra,
            ]);

            $totalAcumulado = 0;

            // 2. Procesar cada producto adquirido con bloqueo pesimista
            foreach ($data['productos'] as $item) {
                $subtotalItem = $this->procesarDetalleCompra($compra, $item);
                $totalAcumulado += $subtotalItem;
            }

            // 3. Actualizar importes totales y saldo pendiente
            $totalFinal = round($totalAcumulado, 2);
            $compra->update([
                'subtotal'        => $totalFinal,
                'total'           => $totalFinal,
                'saldo_pendiente' => $condicionPago === 'credito' ? $totalFinal : 0,
            ]);

            // 4. Si proviene de una Orden de Compra, asociar y actualizar cantidades recibidas
            if (!empty($data['orden_compra_id'])) {
                $ordenCompra = \App\Models\OrdenCompra::with('detalles')->find($data['orden_compra_id']);
                if ($ordenCompra) {
                    $recibidosPorDetalleId = [];
                    $recibidosPorProducto = [];

                    foreach ($data['productos'] as $item) {
                        $pid = (int) $item['producto_id'];
                        $cantidadPresentaciones = (int) ($item['cantidad_presentaciones'] ?? $item['cantidad'] ?? 0);
                        $factor = 1;
                        if (!empty($item['presentacion_id'])) {
                            $pres = PresentacionProducto::find($item['presentacion_id']);
                            if ($pres) {
                                $factor = max(1, (int)$pres->unidades_por_presentacion);
                            }
                        }
                        $cantidadBase = $cantidadPresentaciones * $factor;

                        if (!empty($item['detalle_orden_compra_id'])) {
                            $detId = (int) $item['detalle_orden_compra_id'];
                            $recibidosPorDetalleId[$detId] = ($recibidosPorDetalleId[$detId] ?? 0) + $cantidadBase;
                        }
                        $recibidosPorProducto[$pid] = ($recibidosPorProducto[$pid] ?? 0) + $cantidadBase;
                    }

                    $faltanteTotal = 0;
                    $hayPendientes = false;

                    foreach ($ordenCompra->detalles as $ordDet) {
                        $recibido = 0;
                        if (isset($recibidosPorDetalleId[$ordDet->id])) {
                            $recibido = $recibidosPorDetalleId[$ordDet->id];
                        } elseif (isset($recibidosPorProducto[$ordDet->producto_id])) {
                            $recibido = $recibidosPorProducto[$ordDet->producto_id];
                            unset($recibidosPorProducto[$ordDet->producto_id]);
                        }

                        $totalRecibido = (int)$ordDet->cantidad_recibida + $recibido;
                        $ordDet->update(['cantidad_recibida' => $totalRecibido]);

                        $pendiente = max(0, (int)$ordDet->cantidad_solicitada - $totalRecibido);
                        if ($pendiente > 0) {
                            $hayPendientes = true;
                            $faltanteTotal += $pendiente;
                        }
                    }

                    $cerrarOrdenCompleta = !empty($data['cerrar_orden_completa']) && ($data['cerrar_orden_completa'] == '1' || $data['cerrar_orden_completa'] === true || $data['cerrar_orden_completa'] === 'true');

                    if (!$hayPendientes) {
                        $ordenCompra->update([
                            'compra_id' => $compra->id,
                            'estado'    => 'recibida_total',
                        ]);
                    } elseif ($cerrarOrdenCompleta) {
                        $ordenCompra->update([
                            'compra_id'            => $compra->id,
                            'estado'               => 'recibida_total',
                            'cerrada_con_faltante' => true,
                            'faltante_unidades'    => $faltanteTotal,
                            'cerrada_por_id'       => Auth::id() ?? 1,
                            'fecha_cierre'         => now(),
                            'motivo_faltante'      => !empty($data['motivo_faltante']) ? trim($data['motivo_faltante']) : 'Cierre de orden con faltante autorizado por el usuario',
                        ]);
                    } else {
                        $ordenCompra->update([
                            'compra_id' => $compra->id,
                            'estado'    => 'recibida_parcial',
                        ]);
                    }
                }
            }

            return $compra->load([
                'detalles.producto.laboratorio',
                'detalles.lote',
                'detalles.presentacion',
                'proveedor',
                'lotes'
            ]);
        });
    }

    /**
     * Procesar un ítem de detalle de compra creando el Lote y registrando el Kardex.
     * 
     * @param Compra $compra
     * @param array $item
     * @return float Subtotal del item
     * @throws Exception
     */
    protected function procesarDetalleCompra(Compra $compra, array $item): float
    {
        $productoId = (int) $item['producto_id'];

        // Bloquear el producto para prevenir condiciones de carrera en costos
        $producto = Producto::where('id', $productoId)
            ->lockForUpdate()
            ->firstOrFail();

        if (!$producto->activo) {
            throw new Exception("El producto '{$producto->nombre}' se encuentra inactivo.");
        }

        $presentacionId = !empty($item['presentacion_id']) ? (int) $item['presentacion_id'] : null;
        $cantidadPresentaciones = (int) ($item['cantidad_presentaciones'] ?? $item['cantidad'] ?? 1);
        $precioPresentacion = (float) $item['precio_unitario'];

        if ($cantidadPresentaciones <= 0) {
            throw new Exception("La cantidad para '{$producto->nombre}' debe ser mayor a 0.");
        }

        if ($precioPresentacion < 0) {
            throw new Exception("El precio de compra para '{$producto->nombre}' no puede ser negativo.");
        }

        // Determinar factor de conversión y presentación
        $presentacion = null;
        if ($presentacionId) {
            $presentacion = PresentacionProducto::where('producto_id', $producto->id)
                ->where('id', $presentacionId)
                ->firstOrFail();

            $unidadesPorPresentacion = max(1, (int) $presentacion->unidades_por_presentacion);
            $tipoPresentacion = $presentacion->nombre;
        } else {
            $unidadesPorPresentacion = 1;
            $tipoPresentacion = 'Unidad Base';
        }

        // Cálculos de unidades base y costos unitarios
        $cantidadUnidadesBase = $cantidadPresentaciones * $unidadesPorPresentacion;
        $subtotal = round($cantidadPresentaciones * $precioPresentacion, 2);
        $costoUnitarioBase = round($precioPresentacion / $unidadesPorPresentacion, 4);

        $numeroLote = trim($item['numero_lote']);
        $fechaVencimiento = $item['fecha_vencimiento'];

        // 1. Buscar lote existente (mismo producto + mismo número de lote) o preparar uno nuevo
        //    Esto evita duplicados cuando el mismo lote llega en múltiples compras o líneas.
        $lote = Lote::where('producto_id', $producto->id)
            ->where('numero_lote', $numeroLote)
            ->lockForUpdate()
            ->first();

        $esLoteNuevo = is_null($lote);
        $stockAnterior = $esLoteNuevo ? 0 : (int) $lote->stock_actual;

        if ($esLoteNuevo) {
            // ─── LOTE NUEVO ─────────────────────────────────────────────────
            $lote = Lote::create([
                'producto_id'       => $producto->id,
                'compra_id'         => $compra->id,
                'proveedor_id'      => $compra->proveedor_id,
                'numero_lote'       => $numeroLote,
                'fecha_vencimiento' => $fechaVencimiento,
                'stock_inicial'     => $cantidadUnidadesBase,
                'stock_actual'      => $cantidadUnidadesBase,
                'precio_compra'     => $costoUnitarioBase,
                'activo'            => true,
            ]);
        } else {
            // ─── LOTE EXISTENTE: acumular stock y actualizar costo/vencimiento ──
            // Recalcular stock_inicial acumulado y actualizar metadatos con los datos más recientes
            $nuevoStockInicial = (int) $lote->stock_inicial + $cantidadUnidadesBase;
            $nuevoStockActual  = $stockAnterior + $cantidadUnidadesBase;

            $lote->update([
                'stock_inicial'     => $nuevoStockInicial,
                'stock_actual'      => $nuevoStockActual,
                'precio_compra'     => $costoUnitarioBase,    // actualizar al costo más reciente
                'fecha_vencimiento' => $fechaVencimiento,     // actualizar al vencimiento del nuevo ingreso
                'activo'            => true,                  // reactivar si estaba desactivado
            ]);
        }

        $stockPosterior = (int) $lote->fresh()->stock_actual;

        // 2. Crear Detalle de Compra (siempre uno por línea de compra, aunque el lote sea existente)
        DetalleCompra::create([
            'compra_id'                 => $compra->id,
            'producto_id'               => $producto->id,
            'lote_id'                   => $lote->id,
            'presentacion_id'           => $presentacionId,
            'detalle_orden_compra_id'   => !empty($item['detalle_orden_compra_id']) ? (int) $item['detalle_orden_compra_id'] : null,
            'tipo_presentacion'         => $tipoPresentacion,
            'unidades_por_presentacion' => $unidadesPorPresentacion,
            'cantidad_presentaciones'   => $cantidadPresentaciones,
            'cantidad_unidades_base'    => $cantidadUnidadesBase,
            'precio_unitario'           => $precioPresentacion,
            'subtotal'                  => $subtotal,
        ]);

        // 3. Registrar en Kardex (ENTRADA individual por compra, con balance real pre/post)
        $motivoKardex = $esLoteNuevo
            ? "Ingreso por Compra #{$compra->id} (Doc: {$compra->numero_comprobante}) — Lote NUEVO: {$lote->numero_lote}"
            : "Ingreso por Compra #{$compra->id} (Doc: {$compra->numero_comprobante}) — Reingreso al Lote: {$lote->numero_lote} (Stock acumulado)";

        MovimientoInventario::create([
            'producto_id'      => $producto->id,
            'lote_id'          => $lote->id,
            'user_id'          => Auth::id() ?? 1,
            'tipo'             => 'entrada',
            'subtipo'          => 'compra',
            'cantidad'         => $cantidadUnidadesBase,
            'stock_anterior'   => $stockAnterior,
            'stock_posterior'  => $stockPosterior,
            'costo_unitario'   => $costoUnitarioBase,
            'costo_total'      => $subtotal,
            'origen'           => 'compra',
            'origen_id'        => $compra->id,
            'motivo'           => $motivoKardex,
            'fecha_movimiento' => now(),
        ]);

        // 4. Registrar en Historial de Precios por Proveedor
        HistorialPrecio::create([
            'producto_id'               => $producto->id,
            'proveedor_id'              => $compra->proveedor_id,
            'compra_id'                 => $compra->id,
            'presentacion_id'           => $presentacionId,
            'tipo_presentacion'         => $tipoPresentacion,
            'unidades_por_presentacion' => $unidadesPorPresentacion,
            'precio_compra'             => $precioPresentacion,
            'precio_unitario_base'      => $costoUnitarioBase,
            'tipo'                      => 'compra',
            'fecha'                     => $compra->fecha ?? now(),
            'observaciones'             => "Compra #{$compra->id} (Doc: {$compra->numero_comprobante}) - Lote {$numeroLote}",
        ]);

        // 5. Asentar en Libro Oficial MINSA si el producto es controlado
        if ($producto->esControlado()) {
            \App\Models\RegistroVentaControlado::create([
                'tipo_movimiento'     => \App\Models\RegistroVentaControlado::TIPO_COMPRA,
                'compra_id'           => $compra->id,
                'producto_id'         => $producto->id,
                'lote_id'             => $lote->id,
                'nivel_controlado'    => 1,
                'paciente_nombre'     => 'Proveedor: ' . ($compra->proveedor?->nombre ?? 'Distribuidora Farmacéutica'),
                'paciente_cedula'     => $compra->proveedor?->ruc ?? null,
                'motivo_omision'      => "Ingreso de Fármaco Controlado por Compra #{$compra->id} (Doc: {$compra->numero_comprobante}) - Lote: {$numeroLote}",
                'cantidad'            => $cantidadUnidadesBase,
                'unidad'              => 'unidad',
                'user_id'             => Auth::id() ?? $compra->user_id,
            ]);
        }

        // 5. Actualizar precio de compra de referencia en el producto
        $producto->update(['precio_compra' => $costoUnitarioBase]);

        return $subtotal;
    }

    /**
     * Validar integridad de datos de compra
     * 
     * @param array $data
     * @throws Exception
     */
    protected function validarDatosCompra(array $data): void
    {
        if (!isset($data['productos']) || !is_array($data['productos']) || empty($data['productos'])) {
            throw new Exception('Debe incluir al menos un producto en la compra.');
        }

        foreach ($data['productos'] as $index => $item) {
            if (empty($item['producto_id'])) {
                throw new Exception("El ítem #{$index} no tiene un producto seleccionado.");
            }
            if (empty($item['numero_lote'])) {
                throw new Exception("El ítem #{$index} requiere especificar el número de lote.");
            }
            if (empty($item['fecha_vencimiento'])) {
                throw new Exception("El ítem #{$index} requiere la fecha de vencimiento del lote.");
            }
            $cantidad = (int) ($item['cantidad_presentaciones'] ?? $item['cantidad'] ?? 0);
            if ($cantidad <= 0) {
                throw new Exception("La cantidad en el ítem #{$index} debe ser mayor a 0.");
            }
            $precio = (float) ($item['precio_unitario'] ?? 0);
            if ($precio < 0) {
                throw new Exception("El precio en el ítem #{$index} no puede ser negativo.");
            }
        }
    }

    /**
     * Anular una compra (validando que ningún lote haya sido vendido o dispensado)
     * 
     * @param int $compraId
     * @param string $motivo
     * @return Compra
     * @throws Exception
     */
    public function anularCompra(int $compraId, string $motivo): Compra
    {
        return DB::transaction(function () use ($compraId, $motivo) {
            $compra = Compra::with(['detalles.lote'])
                ->where('id', $compraId)
                ->lockForUpdate()
                ->firstOrFail();

            if (!$compra->puedeAnularse()) {
                throw new Exception("La compra #{$compraId} no puede ser anulada porque ya está anulada o fue modificada.");
            }

            // Validar por DETALLE: que las unidades aportadas por esta compra al lote
            // aún estén disponibles (no vendidas por otros medios).
            foreach ($compra->detalles as $detalle) {
                $loteBloqueado = Lote::where('id', $detalle->lote_id)
                    ->lockForUpdate()
                    ->firstOrFail();

                // Solo se puede anular si el stock actual permite absorber la reversión
                $cantidadARevertir = (int) $detalle->cantidad_unidades_base;
                if ($loteBloqueado->stock_actual < $cantidadARevertir) {
                    $yaVendidas = $cantidadARevertir - $loteBloqueado->stock_actual;
                    throw new Exception(
                        "No se puede anular la compra #{$compraId}: del lote '{$loteBloqueado->numero_lote}' " .
                        "ya se han vendido o dispensado {$yaVendidas} unidades de las {$cantidadARevertir} " .
                        "que ingresaron por esta compra."
                    );
                }

                if ($loteBloqueado->detallesVentas()->exists()) {
                    throw new Exception(
                        "No se puede anular la compra #{$compraId}: el lote '{$loteBloqueado->numero_lote}' ya tiene historial de ventas asociado."
                    );
                }
            }

            // Proceder con la reversión atómica por línea de detalle
            foreach ($compra->detalles as $detalle) {
                $loteBloqueado = Lote::where('id', $detalle->lote_id)
                    ->lockForUpdate()
                    ->firstOrFail();

                $cantidadARevertir = (int) $detalle->cantidad_unidades_base;
                $stockAnterior     = (int) $loteBloqueado->stock_actual;
                $stockPosterior    = max(0, $stockAnterior - $cantidadARevertir);
                $costoUnitario     = (float) $loteBloqueado->precio_compra;
                $costoTotal        = round($cantidadARevertir * $costoUnitario, 2);

                // Descontar del lote solo lo que esta compra aportó
                $loteBloqueado->stock_actual  = $stockPosterior;
                $loteBloqueado->stock_inicial = max(0, (int) $loteBloqueado->stock_inicial - $cantidadARevertir);

                // Desactivar el lote solo si se queda sin stock
                if ($stockPosterior <= 0) {
                    $loteBloqueado->activo = false;
                }

                $loteBloqueado->save();

                // Registrar salida por anulación en Kardex con balance real
                MovimientoInventario::create([
                    'producto_id'      => $loteBloqueado->producto_id,
                    'lote_id'          => $loteBloqueado->id,
                    'user_id'          => Auth::id() ?? 1,
                    'tipo'             => 'salida',
                    'subtipo'          => 'anulacion_compra',
                    'cantidad'         => -$cantidadARevertir,
                    'stock_anterior'   => $stockAnterior,
                    'stock_posterior'  => $stockPosterior,
                    'costo_unitario'   => $costoUnitario,
                    'costo_total'      => $costoTotal,
                    'origen'           => 'anulacion_compra',
                    'origen_id'        => $compra->id,
                    'motivo'           => "Anulación de compra #{$compra->id}: {$motivo} (Lote: {$loteBloqueado->numero_lote})",
                    'fecha_movimiento' => now(),
                ]);

                // Registrar salida/reversión en el Libro Oficial de Controlados MINSA si aplica
                if ($loteBloqueado->producto && $loteBloqueado->producto->esControlado()) {
                    \App\Models\RegistroVentaControlado::create([
                        'tipo_movimiento'     => \App\Models\RegistroVentaControlado::TIPO_ANULACION_COMPRA,
                        'compra_id'           => $compra->id,
                        'producto_id'         => $loteBloqueado->producto_id,
                        'lote_id'             => $loteBloqueado->id,
                        'nivel_controlado'    => 1,
                        'paciente_nombre'     => 'Proveedor: ' . ($compra->proveedor?->nombre ?? 'Distribuidora Farmacéutica'),
                        'paciente_cedula'     => $compra->proveedor?->ruc ?? null,
                        'motivo_omision'      => "Reversión por Anulación de Compra #{$compra->id}: {$motivo} - Lote: {$loteBloqueado->numero_lote}",
                        'cantidad'            => $cantidadARevertir,
                        'unidad'              => 'unidad',
                        'user_id'             => Auth::id() ?? 1,
                    ]);
                }
            }

            // Remover del historial de precios los registros generados por esta compra anulada
            HistorialPrecio::where('compra_id', $compra->id)->delete();

            $compra->update([
                'estado'           => 'anulada',
                'anulado_por'      => Auth::id() ?? 1,
                'fecha_anulacion'  => now(),
                'motivo_anulacion' => $motivo,
            ]);

            return $compra->fresh(['detalles', 'lotes', 'anuladoPor']);
        });
    }

    /**
     * Modificar una compra existente con trazabilidad completa y reversión controlada.
     * 
     * @param int $compraId
     * @param array $data
     * @param string $motivo
     * @return Compra
     * @throws Exception
     */
    public function modificarCompra(int $compraId, array $data, string $motivo): Compra
    {
        return DB::transaction(function () use ($compraId, $data, $motivo) {
            $compraOriginal = Compra::with('lotes')
                ->where('id', $compraId)
                ->lockForUpdate()
                ->firstOrFail();

            if (!$compraOriginal->puedeModificarse()) {
                throw new Exception("La compra #{$compraId} no puede modificarse en su estado actual.");
            }

            // Anular compra original (revirtiendo sus lotes si no han sido vendidos)
            $this->anularCompra($compraId, "Modificación: {$motivo}");

            // Crear nueva compra con los datos actualizados
            $nuevaCompra = $this->registrarCompra($data);

            // Establecer enlaces de trazabilidad bidireccional
            $compraOriginal->update([
                'reemplazada_por'  => $nuevaCompra->id,
                'motivo_anulacion' => "Modificada y reemplazada por Compra #{$nuevaCompra->id}. Motivo: {$motivo}",
            ]);

            $nuevaCompra->update([
                'compra_original_id' => $compraOriginal->id,
            ]);

            return $nuevaCompra->load(['detalles', 'proveedor', 'lotes', 'compraOriginal']);
        });
    }
}
