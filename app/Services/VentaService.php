<?php

namespace App\Services;

use App\Models\Venta;
use App\Models\DetalleVenta;
use App\Models\Lote;
use App\Models\Producto;
use App\Models\PresentacionProducto;
use App\Models\MovimientoInventario;
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
     * Procesar una venta en mostrador con soporte para presentaciones fraccionadas, recetas médicas y Kardex auditado
     * 
     * @param array $data
     * @return Venta
     * @throws Exception
     */
    public function procesarVenta(array $data): Venta
    {
        return DB::transaction(function () use ($data) {
            
            $this->validarDatosVenta($data);

            // 1. Crear cabecera de la venta
            $venta = Venta::create([
                'cliente_id' => $data['cliente_id'] ?? null,
                'user_id' => Auth::id() ?? 1,
                'tipo_comprobante' => $data['tipo_comprobante'] ?? 'ticket',
                'serie' => $data['serie'] ?? null,
                'numero_comprobante' => $data['numero_comprobante'] ?? null,
                'subtotal' => 0,
                'descuento' => $data['descuento'] ?? 0,
                'impuesto' => 0,
                'total' => 0,
                'metodo_pago' => $data['metodo_pago'] ?? 'efectivo',
                'estado' => 'completada',
                'fecha' => now(),
            ]);

            $totalAcumulado = 0;

            // 2. Procesar cada producto del carrito
            foreach ($data['productos'] as $item) {
                $subtotalItem = $this->procesarDetalleVenta($venta, $item);
                $totalAcumulado += $subtotalItem;
            }

            // 3. Aplicar descuento y actualizar total
            $descuento = (float) ($data['descuento'] ?? 0);
            $totalFinal = max(0, round($totalAcumulado - $descuento, 2));

            $venta->update([
                'subtotal' => $totalAcumulado,
                'total' => $totalFinal,
            ]);

            // 4. Asociar recetas generales a la venta si existen
            if (!empty($data['recetas'])) {
                $venta->recetas()->syncWithoutDetaching($data['recetas']);
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
     * Procesar un ítem individual de la venta con bloqueo pesimista y validación médica
     * 
     * @param Venta $venta
     * @param array $item
     * @return float
     * @throws Exception
     */
    protected function procesarDetalleVenta(Venta $venta, array $item): float
    {
        // 1. Obtener y bloquear el lote para prevenir concurrencia
        $lote = Lote::with('producto')
            ->where('id', $item['lote_id'])
            ->lockForUpdate()
            ->firstOrFail();

        $producto = $lote->producto;

        // 2. Validaciones sanitarias y de lote
        if (!$lote->activo) {
            throw new Exception("El lote '{$lote->numero_lote}' del producto '{$producto->nombre}' está inactivo.");
        }

        if ($lote->estaVencido()) {
            throw new Exception("No se puede vender: el lote '{$lote->numero_lote}' de '{$producto->nombre}' venció el {$lote->fecha_vencimiento->format('d/m/Y')}.");
        }

        // 3. Determinar presentación y factor de conversión
        $presentacionId = $item['presentacion_id'] ?? null;
        $cantidadPresentaciones = (int) $item['cantidad'];

        if ($cantidadPresentaciones <= 0) {
            throw new Exception("La cantidad solicitada para {$producto->nombre} debe ser mayor a 0.");
        }

        if ($presentacionId) {
            $presentacion = PresentacionProducto::where('producto_id', $producto->id)
                ->where('id', $presentacionId)
                ->firstOrFail();
            $unidadesPorPresentacion = $presentacion->unidades_por_presentacion;
            $precioUnitario = (float) ($item['precio_unitario'] ?? $presentacion->precio_venta ?? $producto->precio_venta);
        } else {
            $unidadesPorPresentacion = 1;
            $precioUnitario = (float) ($item['precio_unitario'] ?? $producto->precio_venta);
        }

        // Unidades base totales requeridas para descontar del lote
        $cantidadUnidadesBase = $cantidadPresentaciones * $unidadesPorPresentacion;

        // 4. Validar existencia de stock suficiente en el lote
        if ($lote->stock_actual < $cantidadUnidadesBase) {
            throw new Exception(
                "Stock insuficiente para '{$producto->nombre}'. " .
                "Disponible en lote {$lote->numero_lote}: {$lote->stock_actual} unidades base. Requerido: {$cantidadUnidadesBase}."
            );
        }

        // 5. Validar Receta Médica si el fármaco lo requiere
        $recetaDetalleId = $item['receta_detalle_id'] ?? null;
        if (($producto->requiere_receta || in_array($producto->tipo_control, ['receta_medica', 'receta_retenida'])) && !$recetaDetalleId) {
            // Si no viene vinculado a un detalle de receta específico, verificar si la venta tiene receta general
            // O emitir advertencia/validación según el tipo_control
            if ($producto->tipo_control === 'receta_retenida' && !$recetaDetalleId) {
                throw new Exception("El medicamento '{$producto->nombre}' es de RECETA RETENIDA y requiere asociar obligatoriamente la receta médica correspondiente.");
            }
        }

        // Si se vinculó a una prescripción médica, procesar la dispensación
        if ($recetaDetalleId) {
            $this->recetaService->dispensarMedicamento($recetaDetalleId, $cantidadUnidadesBase);
        }

        // 6. Descontar stock del lote
        $stockAnterior = $lote->stock_actual;
        $stockPosterior = $stockAnterior - $cantidadUnidadesBase;
        $lote->stock_actual = $stockPosterior;
        $lote->save();

        // 7. Calcular subtotal de la línea
        $subtotal = round($cantidadPresentaciones * $precioUnitario, 2);

        // 8. Crear Detalle de Venta
        DetalleVenta::create([
            'venta_id' => $venta->id,
            'producto_id' => $producto->id,
            'lote_id' => $lote->id,
            'presentacion_id' => $presentacionId,
            'receta_detalle_id' => $recetaDetalleId,
            'cantidad' => $cantidadPresentaciones,
            'unidades_por_presentacion' => $unidadesPorPresentacion,
            'cantidad_unidades_base' => $cantidadUnidadesBase,
            'precio_unitario' => $precioUnitario,
            'subtotal' => $subtotal,
        ]);

        // 9. Registrar en Kardex Auditoría (SALIDA por VENTA)
        $costoUnitarioBase = (float) $lote->precio_compra;
        $costoTotalMovimiento = round($cantidadUnidadesBase * $costoUnitarioBase, 2);

        MovimientoInventario::create([
            'producto_id' => $producto->id,
            'lote_id' => $lote->id,
            'user_id' => Auth::id() ?? 1,
            'tipo' => 'salida',
            'subtipo' => 'venta',
            'cantidad' => -$cantidadUnidadesBase,
            'stock_anterior' => $stockAnterior,
            'stock_posterior' => $stockPosterior,
            'costo_unitario' => $costoUnitarioBase,
            'costo_total' => $costoTotalMovimiento,
            'origen' => 'venta',
            'origen_id' => $venta->id,
            'motivo' => "Venta #{$venta->id} - {$cantidadPresentaciones} x " . ($presentacionId ? $presentacion->nombre : 'Unidad Base') . " (Lote: {$lote->numero_lote})",
            'fecha_movimiento' => now(),
        ]);

        return $subtotal;
    }

    /**
     * Validar datos generales de la venta
     * 
     * @param array $data
     * @throws Exception
     */
    protected function validarDatosVenta(array $data): void
    {
        if (empty($data['productos']) || !is_array($data['productos'])) {
            throw new Exception('Debe incluir al menos un producto en la venta.');
        }

        foreach ($data['productos'] as $index => $item) {
            if (empty($item['producto_id']) || empty($item['lote_id'])) {
                throw new Exception("El ítem #{$index} no cuenta con producto o lote asignado.");
            }
            if (($item['cantidad'] ?? 0) <= 0) {
                throw new Exception("La cantidad en el ítem #{$index} debe ser mayor a 0.");
            }
        }
    }

    /**
     * Anular una venta con reversión exacta de inventario y recetas
     * 
     * @param int $ventaId
     * @param string $motivo
     * @return Venta
     * @throws Exception
     */
    public function anularVenta(int $ventaId, string $motivo): Venta
    {
        return DB::transaction(function () use ($ventaId, $motivo) {
            
            $venta = Venta::with('detalles')
                ->where('id', $ventaId)
                ->lockForUpdate()
                ->firstOrFail();

            if (!$venta->puedeAnularse()) {
                throw new Exception("La venta #{$ventaId} no puede ser anulada porque ya está anulada o fue modificada.");
            }

            // Revertir cada detalle de la venta
            foreach ($venta->detalles as $detalle) {
                $lote = Lote::where('id', $detalle->lote_id)->lockForUpdate()->firstOrFail();
                
                $stockAnterior = $lote->stock_actual;
                $stockPosterior = $stockAnterior + $detalle->cantidad_unidades_base;
                
                $lote->stock_actual = $stockPosterior;
                $lote->save();

                // Revertir Kardex
                $costoUnitarioBase = (float) $lote->precio_compra;
                $costoTotal = round($detalle->cantidad_unidades_base * $costoUnitarioBase, 2);

                MovimientoInventario::create([
                    'producto_id' => $detalle->producto_id,
                    'lote_id' => $lote->id,
                    'user_id' => Auth::id() ?? 1,
                    'tipo' => 'entrada',
                    'subtipo' => 'anulacion_venta',
                    'cantidad' => $detalle->cantidad_unidades_base,
                    'stock_anterior' => $stockAnterior,
                    'stock_posterior' => $stockPosterior,
                    'costo_unitario' => $costoUnitarioBase,
                    'costo_total' => $costoTotal,
                    'origen' => 'anulacion_venta',
                    'origen_id' => $venta->id,
                    'motivo' => "Anulación de venta #{$venta->id}: {$motivo}",
                    'fecha_movimiento' => now(),
                ]);

                // Revertir dispensación de receta si aplica
                if ($detalle->receta_detalle_id) {
                    $this->recetaService->revertirDispensacion(
                        $detalle->receta_detalle_id,
                        $detalle->cantidad_unidades_base
                    );
                }
            }

            $venta->update([
                'estado' => 'anulada',
                'anulado_por' => Auth::id() ?? 1,
                'fecha_anulacion' => now(),
                'motivo_anulacion' => $motivo,
            ]);

            return $venta->fresh(['detalles', 'anuladoPor']);
        });
    }

    /**
     * Búsqueda optimizada de productos para el mostrador de ventas (FIFO de lotes)
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
                    $query->disponibles()->orderBy('fecha_vencimiento', 'asc'); // FIFO
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
}
