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

            // 1. Obtener y bloquear la sesión de caja activa del usuario
            $sesionActiva = SesionCaja::where('user_id', $userId)
                ->where('estado', 'abierta')
                ->lockForUpdate()
                ->latest('fecha_apertura')
                ->first();

            if (!$sesionActiva) {
                // Fallback: buscar cualquier sesión abierta si el usuario no tiene una asignada
                $sesionActiva = SesionCaja::where('estado', 'abierta')
                    ->lockForUpdate()
                    ->latest('fecha_apertura')
                    ->first();
            }

            if (configuracion('modulo_cajas_estricto', true) && !$sesionActiva) {
                throw new Exception("No hay ninguna sesión de caja abierta en el sistema. Debes abrir un turno de caja antes de realizar ventas.");
            }

            // 2. Crear cabecera inicial de la venta
            $descuento = max(0, (float) ($data['descuento'] ?? 0));

            $venta = Venta::create([
                'cliente_id'         => $data['cliente_id'] ?? null,
                'user_id'            => $userId,
                'sesion_caja_id'     => $sesionActiva?->id,
                'tipo_comprobante'   => $data['tipo_comprobante'] ?? 'ticket',
                'serie'              => $data['serie'] ?? null,
                'numero_comprobante' => $data['numero_comprobante'] ?? null,
                'subtotal'           => 0,
                'descuento'          => $descuento,
                'impuesto'           => 0,
                'total'              => 0,
                'metodo_pago'        => $data['metodo_pago'] ?? 'efectivo',
                'estado'             => 'completada',
                'fecha'              => now(),
            ]);

            $totalAcumulado = 0;
            $recetasAsociadas = [];

            if (!empty($data['recetas']) && is_array($data['recetas'])) {
                $recetasAsociadas = array_values(array_filter(array_unique($data['recetas'])));
            }

            // 3. Procesar cada producto del carrito
            foreach ($data['productos'] as $item) {
                $subtotalItem = $this->procesarDetalleVenta($venta, $item);
                $totalAcumulado += $subtotalItem;

                // Si el item vino con receta vinculada, agregarla al listado general de recetas de la venta
                if (!empty($item['receta_detalle_id'])) {
                    $recetaDetalle = RecetaDetalle::find($item['receta_detalle_id']);
                    if ($recetaDetalle && !in_array($recetaDetalle->receta_id, $recetasAsociadas)) {
                        $recetasAsociadas[] = $recetaDetalle->receta_id;
                    }
                }
            }

            // 4. Calcular importes finales y actualizar cabecera
            $subtotalFinal = round($totalAcumulado, 2);
            $totalFinal = max(0, round($subtotalFinal - $descuento, 2));

            $venta->update([
                'subtotal' => $subtotalFinal,
                'descuento' => $descuento,
                'total' => $totalFinal,
            ]);

            // 5. Vincular recetas a la tabla pivot
            if (!empty($recetasAsociadas)) {
                $venta->recetas()->syncWithoutDetaching($recetasAsociadas);
            }

            // 6. Recalcular totales de la caja activa
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
    protected function procesarDetalleVenta(Venta $venta, array $item): float
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

        if ($esControlado && $producto->tipo_control === 'receta_retenida' && !$recetaDetalleId) {
            throw new Exception("El medicamento '{$producto->nombre}' es de RECETA RETENIDA y requiere asociar obligatoriamente la prescripción médica.");
        }

        if ($recetaDetalleId) {
            $recetaDetalle = RecetaDetalle::where('id', $recetaDetalleId)->firstOrFail();
            if ($recetaDetalle->producto_id !== $producto->id) {
                throw new Exception("La receta médica seleccionada no corresponde al producto '{$producto->nombre}'.");
            }
            $this->recetaService->dispensarMedicamento($recetaDetalleId, $cantidadUnidadesBase);
        }

        // 5. Descontar stock del lote de manera atómica
        $stockAnterior = (int) $lote->stock_actual;
        $stockPosterior = $stockAnterior - $cantidadUnidadesBase;
        
        if ($stockPosterior < 0) {
            throw new Exception("Inconsistencia crítica de inventario detectada: el stock resultante sería negativo.");
        }

        $lote->stock_actual = $stockPosterior;
        $lote->save();

        // 6. Calcular subtotal de la línea
        $subtotal = round($cantidadPresentaciones * $precioUnitario, 2);

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
     * Búsqueda optimizada de productos para el mostrador de ventas (FIFO/FEFO de lotes disponibles)
     * 
     * @param string $termino
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function buscarProductosParaVenta(string $termino)
    {
        return Producto::with([
                'categoria',
                'laboratorio',
                'presentacionesActivas',
                'lotes' => function ($query) {
                    $query->disponibles()->orderBy('fecha_vencimiento', 'asc');
                }
            ])
            ->activos()
            ->where(function ($query) use ($termino) {
                $query->where('nombre', 'like', "%{$termino}%")
                    ->orWhere('principio_activo', 'like', "%{$termino}%")
                    ->orWhere('codigo_barra', 'like', "%{$termino}%");
            })
            ->get()
            ->filter(function ($producto) {
                return $producto->lotes->isNotEmpty();
            })
            ->values();
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
